<?php
include 'components/connect.php';
session_start();

$catalog_data = json_decode(file_get_contents(__DIR__ . '/data/knox-inverters.json'), true);
$knox_inverters = is_array($catalog_data['models'] ?? null) ? $catalog_data['models'] : [];
$solar_products = json_decode(file_get_contents(__DIR__ . '/data/solar-products.json'), true);
$solar_products = is_array($solar_products) ? $solar_products : [];
$inverter_products = [];
$battery_products = [];
try{
   $stmt = $conn->prepare("SELECT id, name, price, image_01 FROM `products` WHERE category = ? ORDER BY price");
   $stmt->execute(['Inverters']);
   $inverter_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
   $stmt = $conn->prepare("SELECT id, name, price, image_01 FROM `products` WHERE category = ? ORDER BY price");
   $stmt->execute(['Batteries']);
   $battery_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
}catch(Exception $e){
   error_log('Calculator product suggestions unavailable: ' . $e->getMessage());
}

$user_id = $_SESSION['user_id'] ?? '';
include 'components/wishlist_cart.php';
?>
<!doctype html>
<html lang="en">
<head>
   <meta charset="utf-8">
   <meta name="viewport" content="width=device-width, initial-scale=1">
   <title>Solar Load Calculator</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
   <style>
      .load-calc-shell{max-width:1440px;padding:6rem 3rem;margin:0 auto}
      .calc-heading{max-width:82rem;margin-bottom:3rem}
      .calc-heading .section-kicker{color:var(--main-color);font-size:1.25rem;font-weight:700;text-transform:uppercase}
      .calc-heading .heading{margin:0 0 1.4rem;font-size:6rem;line-height:.95}
      .calc-heading .heading em{color:var(--accent);font-style:normal}
      .calc-intro{max-width:65rem;color:var(--light-color);font-size:1.6rem;line-height:1.65}
      .calc-grid{display:grid;grid-template-columns:minmax(0,1.65fr) minmax(30rem,.75fr);gap:2rem;align-items:start;min-width:0}
      .calc-workspace,.calc-sidebar{min-width:0}
      .calc-toolbar{display:flex;gap:.8rem;align-items:center;flex-wrap:wrap;margin-bottom:1.4rem}
      .calc-toolbar .btn{width:auto;margin:0;padding:.9rem 1.3rem;font-size:1.3rem;border-radius:.2rem}
      .calc-toolbar .box{width:auto;min-width:15rem;margin:0;padding:.9rem 1rem;background:var(--white);color:var(--black);border:var(--border)}
      .appliance-table-wrap{width:100%;min-width:0;overflow-x:auto;border:var(--border);background:var(--white)}
      .appliance-table{width:100%;min-width:760px;border-collapse:collapse}
      .appliance-table th{padding:1.2rem .9rem;color:var(--light-color);background:rgba(255,255,255,.035);font-size:1.1rem;text-align:left;text-transform:uppercase}
      .appliance-table td{padding:.8rem .7rem;border-top:var(--border);vertical-align:middle}
      .appliance-table input.box,.appliance-table select.box{width:100%;min-width:0;margin:0;padding:.8rem;color:var(--black);background:var(--light-bg);border:var(--border);font-size:1.3rem}
      .appliance-table td:first-child{min-width:16rem}
      .appliance-table td:nth-child(2){min-width:13rem}
      .appliance-table td:nth-child(3){min-width:12rem}
      .appliance-table td:nth-child(4){min-width:9rem}
      .qty-control{display:flex;align-items:center;gap:.35rem}
      .qty-control .qty{width:5rem!important;text-align:center}
      .qty-control button{width:3rem;height:3rem;flex:0 0 3rem;color:var(--black);background:rgba(255,255,255,.08);border:var(--border);font-size:1.8rem;cursor:pointer}
      .qty-control button:hover{background:var(--accent);color:#151713}
      .row-total{color:var(--accent);font-size:1.35rem;font-weight:700;white-space:nowrap}
      .delete-row{width:auto;margin:0;padding:.65rem .9rem;font-size:1.15rem}
      .calc-tip{margin-top:1rem;color:var(--light-color);font-size:1.3rem;line-height:1.6}
      .calc-sidebar{position:sticky;top:10rem;display:grid;gap:1.2rem}
      .calc-assumptions,.calc-result-card{padding:1.8rem;background:var(--white);border:var(--border)}
      .calc-assumptions{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
      .calc-assumptions label{display:grid;gap:.6rem;color:var(--light-color);font-size:1.2rem}
      .calc-assumptions input{width:100%;padding:1rem;color:var(--black);background:var(--light-bg);border:var(--border);font-size:1.5rem}
      .calc-result-header{display:flex;justify-content:space-between;gap:1rem;align-items:flex-start;margin-bottom:1.3rem;padding-bottom:1.2rem;border-bottom:var(--border)}
      .calc-result-header h2{font-family:'Barlow Condensed',sans-serif;font-size:2.4rem}
      .calc-load-visual{display:grid;grid-template-columns:17rem minmax(0,1fr);align-items:center;gap:1rem;margin-bottom:1.2rem}
      .calc-dial-wrap{position:relative;width:17rem;height:17rem;margin:0 auto}
      .calc-dial{width:100%;height:100%;transform:rotate(-90deg)}
      .calc-dial-track{fill:none;stroke:rgba(255,255,255,.09);stroke-width:10}
      .calc-dial-progress{fill:none;stroke:url(#calcDialGradient);stroke-width:10;stroke-linecap:round;stroke-dasharray:540.35;stroke-dashoffset:540.35;transition:stroke-dashoffset .45s cubic-bezier(.4,0,.2,1)}
      .calc-dial-center{position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:.2rem;text-align:center}
      .calc-dial-value{color:var(--black);font-family:'DM Sans',sans-serif;font-size:2.6rem;font-weight:700;line-height:1}
      .calc-dial-unit{color:var(--light-color);font-size:1.05rem;text-transform:uppercase;letter-spacing:.08rem}
      .calc-dial-kw{margin-top:.4rem;color:var(--light-color);font-family:'DM Sans',sans-serif;font-size:1.2rem}
      .calc-live-stats{display:grid;gap:.8rem}
      .calc-live-stat{display:flex;flex-direction:column;gap:.3rem;padding:.8rem 0;border-bottom:var(--border)}
      .calc-live-stat span{color:var(--light-color);font-size:1.1rem;text-transform:uppercase}
      .calc-live-stat strong{color:var(--black);font-size:1.8rem}
      .calc-metric{display:flex;justify-content:space-between;gap:1.2rem;padding:.8rem 0;border-bottom:var(--border);font-size:1.3rem}
      .calc-metric>span{color:var(--light-color)}
      .calc-metric>strong{text-align:right}
      .calc-metric small{color:var(--light-color);font-size:1.1rem;font-weight:400}
      .calc-actions{display:flex;gap:.8rem;margin-top:1.3rem}
      .calc-actions .btn{margin:0;font-size:1.3rem;border-radius:.2rem}
      .calc-run-btn{width:100%;margin:.5rem 0 1rem;padding:1.2rem;font-size:1.5rem;border-radius:.2rem;background:var(--accent);color:#151713}
      .calc-recommendations{display:grid;gap:.8rem;margin:1rem 0}
      .calc-recommendations:empty{display:none}
       .calc-recommendations .reco-card{padding:1.2rem;background:var(--light-bg);border:var(--border)}
       .calc-recommendations .reco-card>img{display:block;width:100%;height:9rem;margin-bottom:1rem;object-fit:contain;background:var(--white)}
      .calc-recommendations .reco-card.best{border-color:var(--accent)}
      .calc-recommendations .reco-card.expansion{border-style:dashed}
      .calc-recommendations .reco-title{display:flex;justify-content:space-between;gap:.8rem;color:var(--black);font-size:1.25rem;font-weight:700}
      .calc-recommendations .reco-tag{color:var(--accent);font-size:1.05rem;text-transform:uppercase}
      .calc-recommendations .reco-detail{margin-top:.4rem;color:var(--light-color);font-size:1.15rem}
      .calc-product-suggestion{display:flex;align-items:center;gap:.8rem;width:100%;min-width:0;margin:.6rem 0;color:var(--black);font-size:1.2rem}
      .calc-product-suggestion img{width:4.8rem;height:4.8rem;flex:0 0 4.8rem;object-fit:cover}
      .calc-product-suggestion span{min-width:0;overflow-wrap:anywhere}
      .calc-product-suggestion small{display:block;color:var(--light-color)}
      .inverter-catalog{margin-top:1.4rem;border-top:var(--border);padding-top:1rem}
      .inverter-catalog summary{color:var(--light-color);font-size:1.25rem;font-weight:700;cursor:pointer}
      .catalog-table-wrap{max-height:32rem;overflow:auto;margin-top:.8rem}
      .catalog-table{width:100%;border-collapse:collapse;font-size:1.1rem}
      .catalog-table th,.catalog-table td{padding:.65rem .45rem;border-bottom:var(--border);text-align:left}
      .catalog-table th{position:sticky;top:0;color:var(--light-color);background:var(--white);font-size:1rem;text-transform:uppercase}
      .catalog-table td{color:var(--light-color)}
      .catalog-table tr.row-fits td{color:var(--black);background:rgba(223,243,106,.08)}
      .catalog-table tr.row-expansion td{background:rgba(223,243,106,.035)}
      .small{font-size:1.3rem}
      .muted{color:var(--light-color);font-size:1.4rem}
      #suggestions img{flex:0 0 4.8rem}
      @media(max-width:900px){.calc-grid{grid-template-columns:minmax(0,1fr)}.calc-sidebar{position:static}.calc-heading .heading{font-size:5rem}}
      @media(max-width:600px){.load-calc-shell{padding:4rem 1.5rem}.calc-heading .heading{font-size:4.2rem}.calc-assumptions{grid-template-columns:1fr 1fr}.calc-toolbar .box{flex:1}.calc-load-visual{grid-template-columns:1fr 1fr;gap:.4rem}.calc-dial-wrap{width:14rem;height:14rem}.calc-dial-value{font-size:2.2rem}}
      @media print{.header,.footer,.calc-toolbar,.calc-actions{display:none!important}.load-calc-shell{max-width:none;padding:0}.calc-grid{grid-template-columns:1fr 1fr}.calc-sidebar{position:static}}
   </style>
</head>
<body>

<?php include 'components/user_header.php'; ?>

<section class="load-calc-shell">
   <header class="calc-heading">
      <p class="section-kicker">SOLAR PLANNING / 01</p>
      <h1 class="heading">Accurate load calculation.<br><em>Confident inverter selection.</em></h1>
      <p class="calc-intro">Add the appliances running on your circuit. Watch connected load update live and see a Knox inverter size with 25% start-up headroom.</p>
   </header>

   <div class="calc-grid">
      <div class="calc-workspace">
         <div class="calc-toolbar">
            <button id="addRow" class="btn" type="button"><i class="fas fa-plus" aria-hidden="true"></i> Other appliance</button>
            <button id="loadPreset" class="btn option-btn" type="button"><i class="fas fa-list-check" aria-hidden="true"></i> Load home presets</button>
            <button id="exportCsv" class="btn option-btn" type="button"><i class="fas fa-download" aria-hidden="true"></i> CSV</button>
            <button id="saveProject" class="btn" type="button"><i class="fas fa-bookmark" aria-hidden="true"></i> Save</button>
            <select id="savedList" class="box" aria-label="Saved projects"></select>
            <button id="loadProject" class="btn option-btn" type="button">Load</button>
            <button id="deleteProject" class="btn delete-btn" type="button">Delete</button>
         </div>
         <div class="appliance-table-wrap">
            <table class="appliance-table" id="applianceTable">
               <thead><tr><th>Appliance</th><th>Average power</th><th>Quantity</th><th>Hours/day</th><th>Connected</th><th></th></tr></thead>
               <tbody id="applBody"></tbody>
            </table>
         </div>
         <p class="calc-tip">Connected load is the sum of each appliance wattage multiplied by its quantity. Hours/day is used for the additional PV and battery estimates.</p>
      </div>
      <aside class="calc-sidebar">
         <div class="calc-assumptions">
            <label>Peak sun hours / day<input id="sunHours" type="number" value="5" step="0.1" min="0.1" max="12"></label>
            <label>System efficiency<input id="efficiency" type="number" value="0.75" step="0.01" min="0.1" max="1"></label>
         </div>
         <div class="calc-result-card">
            <div class="calc-result-header"><h2>Connected load</h2></div>
            <div class="calc-load-visual">
               <div class="calc-dial-wrap" role="img" aria-label="Connected load dial, scaled to a 20 kilowatt display range">
                  <svg class="calc-dial" viewBox="0 0 200 200" aria-hidden="true">
                     <defs><linearGradient id="calcDialGradient" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" stop-color="#e45a3b"/><stop offset="55%" stop-color="#dff36a"/><stop offset="100%" stop-color="#45a89a"/></linearGradient></defs>
                     <circle class="calc-dial-track" cx="100" cy="100" r="86"></circle>
                     <circle class="calc-dial-progress" id="calcDialProgress" cx="100" cy="100" r="86"></circle>
                  </svg>
                  <div class="calc-dial-center"><strong id="liveLoadW" class="calc-dial-value">0</strong><span class="calc-dial-unit">Watts</span><span id="liveLoadKW" class="calc-dial-kw">0.00 kW</span></div>
               </div>
               <div class="calc-live-stats"><div class="calc-live-stat"><span>Appliances added</span><strong id="liveApplianceCount">0</strong></div><div class="calc-live-stat"><span>Recommended sizing*</span><strong id="liveRecommendedKW">0.00 kW</strong></div></div>
            </div>
            <button id="calcBtn" class="btn calc-run-btn" type="button"><i class="fas fa-bolt" aria-hidden="true"></i> Calculate load</button>
            <div id="resultsArea"></div>
            <div id="recommendationArea" class="calc-recommendations"><p class="muted">Add appliance quantities, then calculate to find matching Knox models.</p></div>
       <details class="inverter-catalog"><summary>Verified inverter catalogue</summary><div class="catalog-table-wrap"><table class="catalog-table" id="catalogTable"><thead><tr><th>Company</th><th>Model</th><th>Config</th><th>Rated output</th><th>Max PV</th></tr></thead><tbody></tbody></table></div></details>
            <div id="suggestions" style="margin-top:1rem"></div>
            <div id="detailed" class="muted" style="margin-top:1rem"></div>
            <div class="calc-actions"><button id="printReport" class="btn" type="button"><i class="fas fa-print" aria-hidden="true"></i> Print</button><button id="exportPdf" class="btn option-btn" type="button"><i class="fas fa-file-pdf" aria-hidden="true"></i> PDF report</button></div>
         </div>
      </aside>
   </div>
</section>

<?php include 'components/footer.php'; ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script>
const productCandidates = {inverters: <?= json_encode($inverter_products ?? []); ?>, batteries: <?= json_encode($battery_products ?? []); ?>};
const INVERTERS = <?= json_encode($knox_inverters, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;
const SOLAR_PRODUCTS = <?= json_encode($solar_products, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;
const dialProgress = document.getElementById('calcDialProgress');
const catalogBody = document.querySelector('#catalogTable tbody');
const recommendationArea = document.getElementById('recommendationArea');
const resultsArea = document.getElementById('resultsArea');
const DIAL_CIRCUMFERENCE = 540.35;
const MODEL_EPSILON = 0.001;
const ALTERNATE_WINDOW_KW = 2;
const MAX_EXPANSION_TIERS = 2;
const applBody = document.getElementById('applBody');
const loadPresetBtn = document.getElementById('loadPreset');
let calculated = false;

const appliancePresets = [
   {name:'Ceiling Fans', options:[{label:'AC Fan - 110 W',w:110},{label:'DC Fan - 35 W',w:35},{label:'Inverter Fan - 55 W',w:55}]},
   {name:'Tube Lights', options:[{label:'LED Tube - 18 W',w:18},{label:'LED Tube - 20 W',w:20},{label:'Fluorescent - 36 W',w:36},{label:'Fluorescent - 40 W',w:40}]},
   {name:'LED Bulbs', options:[5,7,9,12,15,18].map(w=>({label:`${w} W`,w}))},
   {name:'LED / LCD Television', options:[[32,60],[40,80],[42,90],[50,110],[55,130],[65,150],[75,200]].map(([size,w])=>({label:`${size} inch - ${w} W`,w}))},
   {name:'Refrigerator', options:[{label:'Conventional - 200 W',w:200},{label:'Inverter - 100 W',w:100}]},
   {name:'Washing Machine', options:[{label:'Semi-automatic - 300 W',w:300},{label:'Top-load automatic - 500 W',w:500},{label:'Front-load automatic - 800 W',w:800}]},
   {name:'Electric Iron', options:[{label:'Plastic body - 1000 W',w:1000},{label:'Metal body - 1500 W',w:1500}]},
   {name:'Air Conditioner', options:[{label:'Conventional - 1 Ton - 1200 W',w:1200},{label:'Conventional - 1.5 Ton - 1800 W',w:1800},{label:'Conventional - 2 Ton - 2400 W',w:2400},{label:'Conventional - 4 Ton - 4800 W',w:4800},{label:'Inverter - 1 Ton - 600 W',w:600},{label:'Inverter - 1.5 Ton - 900 W',w:900},{label:'Inverter - 2 Ton - 1200 W',w:1200},{label:'Inverter - 4 Ton - 2400 W',w:2400}]},
   {name:'Water Pump', options:[[0.5,375],[1,750],[1.5,1100],[2,1500],[3,2200]].map(([hp,w])=>({label:`${hp} HP - ${w} W`,w}))},
   {name:'Microwave Oven', options:[{label:'1200 W',w:1200}]},
   {name:'Desktop Computer', options:[{label:'200 W',w:200}]},
   {name:'Laptop', options:[{label:'65 W',w:65}]}
];

function addRow(desc='Other appliance', watt=0, qty=0, hours=0){
   const row = document.createElement('tr');
   row.innerHTML = `<td><input class="box name" aria-label="Appliance name"></td><td><input class="box watt" type="number" min="0" step="1" aria-label="Average power"></td><td class="qty-control"><button class="qty-minus" type="button" aria-label="Decrease quantity">-</button><input class="box qty" type="number" min="0" value="${qty}"><button class="qty-plus" type="button" aria-label="Increase quantity">+</button></td><td><input class="box hrs" type="number" min="0" step="0.1" value="${hours}" aria-label="Hours per day"></td><td class="row-total">0 W</td><td><button class="btn delete-row" type="button">Remove</button></td>`;
   applBody.appendChild(row);
   row.querySelector('.name').value = desc;
   row.querySelector('.watt').value = watt;
   attachRowEvents(row);
}

function addPresetRow(preset){
   const row = document.createElement('tr');
   const options = preset.options.map(option=>`<option value="${option.w}">${option.label}</option>`).join('');
   row.innerHTML = `<td><input class="box name" aria-label="Appliance name"></td><td><select class="box watt-select" aria-label="Appliance variant">${options}</select></td><td class="qty-control"><button class="qty-minus" type="button" aria-label="Decrease quantity">-</button><input class="box qty" type="number" min="0" value="0"><button class="qty-plus" type="button" aria-label="Increase quantity">+</button></td><td><input class="box hrs" type="number" min="0" step="0.1" value="0" aria-label="Hours per day"></td><td class="row-total">0 W</td><td><button class="btn delete-row" type="button">Remove</button></td>`;
   applBody.appendChild(row);
   row.querySelector('.name').value = preset.name;
   attachRowEvents(row);
}

function attachRowEvents(row){
   const quantity = row.querySelector('.qty');
   const update = ()=>updateLiveLoad();
   row.querySelector('.qty-minus').addEventListener('click', ()=>{quantity.value=Math.max(0,(parseInt(quantity.value,10)||0)-1);update();});
   row.querySelector('.qty-plus').addEventListener('click', ()=>{quantity.value=(parseInt(quantity.value,10)||0)+1;update();});
   row.querySelector('.delete-row').addEventListener('click', ()=>{row.remove();update();});
   row.querySelectorAll('input,select').forEach(control=>control.addEventListener('input',update));
   update();
}

document.getElementById('addRow').addEventListener('click', ()=>addRow());
loadPresetBtn.addEventListener('click', ()=>{
   applBody.replaceChildren();
   appliancePresets.forEach(addPresetRow);
   addRow();
   updateLiveLoad();
});

document.getElementById('exportCsv').addEventListener('click', ()=>{
   const csvRows = [['Appliance','Average watts','Quantity','Hours per day','Connected watts']];
   applBody.querySelectorAll('tr').forEach(row=>{
      const watt = row.querySelector('.watt-select') || row.querySelector('.watt');
      const qty = row.querySelector('.qty');
      const hours = row.querySelector('.hrs');
      csvRows.push([row.querySelector('.name').value,watt.value,qty.value,hours.value,(Number(watt.value)||0)*(Number(qty.value)||0)]);
   });
   const csv = csvRows.map(line=>line.map(value=>`"${String(value).replaceAll('"','""')}"`).join(',')).join('\n');
   const link = document.createElement('a');
   link.href = URL.createObjectURL(new Blob([csv],{type:'text/csv'}));
   link.download = 'solar-load.csv';link.click();URL.revokeObjectURL(link.href);
});

const saveBtn=document.getElementById('saveProject');
const savedList=document.getElementById('savedList');
const loadBtn=document.getElementById('loadProject');
const deleteBtn=document.getElementById('deleteProject');
function getProjects(){try{return JSON.parse(localStorage.getItem('pv_projects')||'{}')}catch(error){return {}}}
function populateProjects(){
   const projects=getProjects();savedList.replaceChildren();
   Object.keys(projects).forEach(name=>savedList.add(new Option(name,name)));
}
function getRowsForSave(){
   return [...applBody.querySelectorAll('tr')].map(row=>({
      desc:row.querySelector('.name').value,
      watt:row.querySelector('.watt-select')?.value ?? row.querySelector('.watt')?.value ?? 0,
      qty:row.querySelector('.qty').value,
      hrs:row.querySelector('.hrs').value,
      label:row.querySelector('.watt-select')?.selectedOptions[0]?.textContent ?? ''
   }));
}
saveBtn.addEventListener('click', ()=>{
   const name=prompt('Save project as:');if(!name)return;
   const projects=getProjects();projects[name]={rows:getRowsForSave(),sun:document.getElementById('sunHours').value,eff:document.getElementById('efficiency').value};
   localStorage.setItem('pv_projects',JSON.stringify(projects));populateProjects();savedList.value=name;
});
loadBtn.addEventListener('click', ()=>{
   const project=getProjects()[savedList.value];if(!project)return;
   applBody.replaceChildren();
   project.rows.forEach(item=>addRow(item.desc,item.watt,item.qty,item.hrs));
   document.getElementById('sunHours').value=project.sun||5;
   document.getElementById('efficiency').value=project.eff||.75;
   updateLiveLoad();
});
deleteBtn.addEventListener('click', ()=>{
   if(!savedList.value)return;
   const projects=getProjects();delete projects[savedList.value];localStorage.setItem('pv_projects',JSON.stringify(projects));populateProjects();
});
populateProjects();

document.getElementById('printReport').addEventListener('click', ()=>window.print());
document.getElementById('exportPdf').addEventListener('click', async ()=>{
   if(!window.jspdf?.jsPDF || !window.html2canvas)return;
   const canvas=await html2canvas(document.querySelector('.load-calc-shell'),{scale:1.5});
   const pdf=new window.jspdf.jsPDF('p','mm','a4');
   const width=pdf.internal.pageSize.getWidth();
   pdf.addImage(canvas.toDataURL('image/jpeg',.95),'JPEG',0,0,width,canvas.height*width/canvas.width);
   pdf.save('solar-load-report.pdf');
});

function updateLiveLoad(){
   const totalW=[...applBody.querySelectorAll('tr')].reduce((sum,row)=>{
      const control=row.querySelector('.watt-select')||row.querySelector('.watt');
      const watt=Number(control?.value)||0;const qty=Number(row.querySelector('.qty').value)||0;
      const rowLoad=watt*qty;row.querySelector('.row-total').textContent=`${Math.round(rowLoad).toLocaleString()} W`;return sum+rowLoad;
   },0);
   const totalKw=totalW/1000;
   document.getElementById('liveLoadW').textContent=Math.round(totalW).toLocaleString();
   document.getElementById('liveLoadKW').textContent=`${totalKw.toFixed(2)} kW`;
   const activeCount=[...applBody.querySelectorAll('tr')].filter(row=>(Number(row.querySelector('.qty').value)||0)>0).length;
   document.getElementById('liveApplianceCount').textContent=activeCount;
   document.getElementById('liveRecommendedKW').textContent=`${(totalKw*1.25).toFixed(2)} kW`;
   dialProgress.style.strokeDashoffset=String(540.35*(1-Math.min(totalKw/20,1)));
   calculate();
}

function escapeHtml(value){return String(value).replace(/[&<>"']/g,char=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[char]));}
function renderCatalog(recommendedModels, expansionModels){
   const recommended=new Set(recommendedModels.map(model=>`${model.company||'Knox'}|${model.model}`));
   const expansion=new Set(expansionModels.map(model=>`${model.company||'Knox'}|${model.model}`));
   catalogBody.innerHTML=getInverterCatalog().map(model=>{
      const key=`${model.company||'Knox'}|${model.model}`;
      const style=recommended.has(key)?'row-fits':expansion.has(key)?'row-expansion':'';
      const pvInput=model.pv_w == null ? 'Not listed' : `${model.pv_w.toLocaleString()} W`;
      const output=model.output_max_kw ? `${model.output_kw.toFixed(1)}–${model.output_max_kw.toFixed(1)} kW` : `${model.output_kw.toFixed(1)} kW`;
      return `<tr class="${style}"><td>${escapeHtml(model.company||'Knox')}</td><td>${escapeHtml(model.name||model.model)}</td><td>${escapeHtml(model.code||'Official family')}</td><td>${output}</td><td>${pvInput}</td></tr>`;
   }).join('');
}
function getInverterCatalog(){
   return [...INVERTERS.map(model=>({...model,company:'Knox',name:model.model})), ...SOLAR_PRODUCTS.filter(product=>product.type==='inverter')];
}
function renderRecommendation(model,type,requiredKw,design={}){
   const badge=type==='recommended'?'Recommended':'Future expansion';
   const headroom=(model.output_max_kw||model.output_kw)>=requiredKw?'Meets 25% surge sizing':'Covers connected load';
   const pvInput=model.pv_w == null ? 'PV input not listed' : `${model.pv_w.toLocaleString()} W max PV`;
   const output=model.output_max_kw ? `${model.output_kw.toFixed(1)}–${model.output_max_kw.toFixed(1)} kW range` : `${model.output_kw.toFixed(1)} kW output`;
   const source=model.official_url ? `<a href="${escapeHtml(model.official_url)}" target="_blank" rel="noopener">Official product data</a>` : '';
   const detailUrl=design.load_kw===undefined?`solar_product.php?product=${encodeURIComponent(`${model.company||'Knox'}|${model.model}`)}`:`system_design.php?load_kw=${design.load_kw.toFixed(2)}&daily_kwh=${design.daily_kwh.toFixed(2)}&battery_kwh=${design.battery_kwh.toFixed(2)}&pv_kw=${design.pv_kw.toFixed(2)}&inverter=${encodeURIComponent(`${model.company||'Knox'}|${model.model}`)}`;
   const image=model.image_url ? `<img src="${escapeHtml(model.image_url)}" alt="${escapeHtml(model.company||'Solar')} ${escapeHtml(model.name||model.model)} product image" loading="lazy">` : '';
   return `<article class="reco-card ${type==='recommended'?'best':'expansion'}">${image}<div class="reco-title"><span><a href="${detailUrl}">${escapeHtml(model.company||'Knox')} · ${escapeHtml(model.name||model.model)}</a></span><span class="reco-tag">${badge}</span></div><div class="reco-detail">${escapeHtml(model.subtype||'hybrid')} · ${escapeHtml(model.code||model.model)} · ${output} · ${pvInput}</div><div class="reco-detail">${headroom} · ${source}</div></article>`;
}
function renderBatterySuggestion(product){
   const image=product.image_url ? `<img src="${escapeHtml(product.image_url)}" alt="${escapeHtml(product.company)} ${escapeHtml(product.name)} product image" loading="lazy">` : '';
   return `<a class="calc-product-suggestion" href="${escapeHtml(product.official_url)}" target="_blank" rel="noopener">${image}<span><strong>${escapeHtml(product.company)} · ${escapeHtml(product.name)}</strong><small>${escapeHtml(product.model)} · ${product.capacity_kwh.toFixed(2)} kWh · Official data</small></span></a>`;
}
function showSuggestions(inverterModels,batteryKwh){
   const target=document.getElementById('suggestions');
   const allBatteries=SOLAR_PRODUCTS.filter(product=>product.type==='battery');
   const matchingBatteries=allBatteries.filter(product=>product.capacity_kwh+0.001>=batteryKwh);
   const batteryCatalog=(matchingBatteries.length?matchingBatteries:allBatteries).sort((a,b)=>Math.abs(a.capacity_kwh-batteryKwh)-Math.abs(b.capacity_kwh-batteryKwh)).slice(0,3);
   const batteryHtml=batteryCatalog.map(renderBatterySuggestion).join('');
   const shopHtml=productCandidates.inverters.slice(0,2).map(product=>`<a class="calc-product-suggestion" href="quick_view.php?pid=${Number(product.id)}"><img src="uploaded_img/${encodeURIComponent(product.image_01)}" alt="${escapeHtml(product.name)}" loading="lazy"><span>${escapeHtml(product.name)}<small>Shop listing · PKR ${Number(product.price).toLocaleString()}</small></span></a>`).join('');
   target.innerHTML=`<h3 class="small">Recommended batteries · target ${batteryKwh.toFixed(2)} kWh</h3>${batteryHtml||'<p class="muted">No verified battery match is currently in the catalog.</p>'}${shopHtml?`<h4>Solar Store listings</h4>${shopHtml}`:''}`;
}
function calculate(){
   const totalW=[...applBody.querySelectorAll('tr')].reduce((sum,row)=>sum+((Number((row.querySelector('.watt-select')||row.querySelector('.watt'))?.value)||0)*(Number(row.querySelector('.qty').value)||0)),0);
   const totalKw=totalW/1000;
   const requiredKw=totalKw*1.25;
   const rows=[...applBody.querySelectorAll('tr')];
   const dailyWh=rows.reduce((sum,row)=>sum+(Number((row.querySelector('.watt-select')||row.querySelector('.watt'))?.value)||0)*(Number(row.querySelector('.qty').value)||0)*(Number(row.querySelector('.hrs').value)||0),0);
   const sunHours=Math.max(Number(document.getElementById('sunHours').value)||5,.1);
   const efficiency=Math.min(Math.max(Number(document.getElementById('efficiency').value)||.75,.1),1);
   const dailyKwh=dailyWh/1000;
   const pvKw=dailyWh/(sunHours*efficiency)/1000;
   const batteryKwh=dailyKwh/.8;
   const batteryAh=Math.ceil(batteryKwh*1000/48);
   resultsArea.innerHTML=`<div class="calc-metric"><span>Total connected load</span><strong>${Math.round(totalW).toLocaleString()} W <small>(${totalKw.toFixed(2)} kW)</small></strong></div><div class="calc-metric"><span>25% surge headroom sizing</span><strong>${requiredKw.toFixed(2)} kW</strong></div><div class="calc-metric"><span>Estimated daily energy</span><strong>${dailyKwh.toFixed(2)} kWh</strong></div><div class="calc-metric"><span>Suggested PV capacity</span><strong>${pvKw.toFixed(2)} kW</strong></div><div class="calc-metric"><span>Battery · 1 day autonomy</span><strong>${batteryKwh.toFixed(2)} kWh <small>(${batteryAh} Ah @ 48 V)</small></strong></div>`;
   document.getElementById('detailed').innerHTML=`Appliances with quantities: <strong>${rows.filter(row=>(Number(row.querySelector('.qty').value)||0)>0).length}</strong>. PV estimate uses ${sunHours} peak-sun hours and ${Math.round(efficiency*100)}% system efficiency.`;
}
function calculateLoad(){
   const totalW=[...applBody.querySelectorAll('tr')].reduce((sum,row)=>sum+((Number((row.querySelector('.watt-select')||row.querySelector('.watt'))?.value)||0)*(Number(row.querySelector('.qty').value)||0)),0);
   const totalKw=totalW/1000;
   const requiredKw=totalKw*1.25;
   const dailyWh=[...applBody.querySelectorAll('tr')].reduce((sum,row)=>sum+((Number((row.querySelector('.watt-select')||row.querySelector('.watt'))?.value)||0)*(Number(row.querySelector('.qty').value)||0)*(Number(row.querySelector('.hrs').value)||0)),0);
   const batteryKwh=dailyWh/1000/.8;
   const sorted=getInverterCatalog().sort((a,b)=>(a.output_max_kw||a.output_kw)-(b.output_max_kw||b.output_kw)||a.model.localeCompare(b.model));
   const compatible=sorted.filter(model=>(model.output_max_kw||model.output_kw)+0.001>=totalKw);
   document.getElementById('suggestions').replaceChildren();
   if(totalW===0){recommendationArea.innerHTML='<div class="warning-card"><h4>No load entered yet</h4><p>Add appliance quantities to calculate a connected load and receive inverter recommendations.</p></div>';renderCatalog([],[]);return;}
   const design={load_kw:totalKw,daily_kwh:dailyWh/1000,battery_kwh:batteryKwh,pv_kw:dailyWh/(Math.max(Number(document.getElementById('sunHours').value)||5,.1)*Math.min(Math.max(Number(document.getElementById('efficiency').value)||.75,.1),1))/1000};
   if(!compatible.length){const largest=sorted.slice(-3).reverse();recommendationArea.innerHTML=`<div class="warning-card"><h4>Load exceeds a single catalog inverter</h4><p>${totalKw.toFixed(2)} kW is above the largest verified catalog model. Consider a parallel setup or contact an installer.</p></div>${largest.map(model=>renderRecommendation(model,'recommended',requiredKw,design)).join('')}`;renderCatalog(largest,[]);return;}
   const smallestCapacity=compatible[0].output_kw;
   const recommended=compatible.filter(model=>Math.abs(model.output_kw-smallestCapacity)<.001);
   const expansionCapacities=[...new Set(compatible.filter(model=>model.output_kw>smallestCapacity+.001&&model.output_kw<=totalKw+2+.001).map(model=>model.output_kw))].sort((a,b)=>a-b).slice(0,2);
   const expansion=compatible.filter(model=>expansionCapacities.some(capacity=>Math.abs(model.output_kw-capacity)<.001));
   recommendationArea.innerHTML=`<h3 class="reco-heading">Recommended inverter</h3><p class="reco-subtext">Click a recommendation to build a complete package with batteries, panels, protection, DB box and cables.</p>${recommended.map(model=>renderRecommendation(model,'recommended',requiredKw,design)).join('')}${expansion.length?`<h3 class="reco-heading reco-heading-expansion">Future expansion options</h3><p class="reco-subtext">The next two distinct output tiers within 2 kW above this load.</p>${expansion.map(model=>renderRecommendation(model,'expansion',requiredKw,design)).join('')}`:''}`;
   renderCatalog(recommended,expansion);
   showSuggestions(recommended,batteryKwh);
}

document.getElementById('calcBtn').addEventListener('click',calculateLoad);
document.getElementById('sunHours').addEventListener('input',calculate);
document.getElementById('efficiency').addEventListener('input',calculate);
renderCatalog([],[]);
loadPresetBtn.click();
calculateLoad();
</script>
</body>
</html>
