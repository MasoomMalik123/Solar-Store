<?php
include 'components/connect.php';
session_start();
$user_id = $_SESSION['user_id'] ?? '';
include 'components/wishlist_cart.php';
$products = json_decode(file_get_contents(__DIR__.'/data/solar-products.json'), true) ?: [];
$accessories = json_decode(file_get_contents(__DIR__.'/data/solar-accessories.json'), true) ?: [];
$load_kw = max(0, (float)($_GET['load_kw'] ?? 0));
$daily_kwh = max(0, (float)($_GET['daily_kwh'] ?? 0));
$battery_kwh = max(0, (float)($_GET['battery_kwh'] ?? 0));
$pv_kw = max(0, (float)($_GET['pv_kw'] ?? 0));
$requested_inverter = urldecode((string)($_GET['inverter'] ?? ''));
$inverters = array_values(array_filter($products, fn($p)=>($p['type']??'')==='inverter'));
$batteries = array_values(array_filter($products, fn($p)=>($p['type']??'')==='battery'));
$panels = array_values(array_filter($products, fn($p)=>($p['type']??'')==='panel'));
$inverter = null;
foreach($inverters as $p){ if(($p['company']??'').'|'.($p['model']??'') === $requested_inverter){$inverter=$p;break;} }
if(!$inverter){ usort($inverters, fn($a,$b)=>(($a['output_max_kw']??$a['output_kw'])<=>($b['output_max_kw']??$b['output_kw']))); foreach($inverters as $p){if(($p['output_max_kw']??$p['output_kw']) >= $load_kw*1.25){$inverter=$p;break;}} }
usort($batteries, fn($a,$b)=>abs(($a['capacity_kwh']??0)-$battery_kwh)<=>abs(($b['capacity_kwh']??0)-$battery_kwh));
usort($panels, fn($a,$b)=>($b['panel_w']??0)<=>($a['panel_w']??0));
$battery = $batteries[0] ?? null;
$panel = $panels[0] ?? null;
$panel_count = $panel && $pv_kw>0 ? max(1, (int)ceil($pv_kw*1000/($panel['panel_w']??400))) : 0;
$package = [];
if($inverter) $package[]=['name'=>$inverter['company'].' '.$inverter['model'],'price'=>0,'quantity'=>1,'image'=>$inverter['image_url']??'images/category-inverter.jpg','kind'=>'Inverter'];
if($battery) $package[]=['name'=>$battery['company'].' '.$battery['model'],'price'=>0,'quantity'=>1,'image'=>$battery['image_url']??'images/category-battery.jpg','kind'=>'Battery'];
if($panel && $panel_count) $package[]=['name'=>$panel['company'].' '.$panel['model'],'price'=>0,'quantity'=>$panel_count,'image'=>$panel['image_url']??'images/category-panels.jpg','kind'=>'Panels'];
foreach($accessories as $a) $package[]=['name'=>$a['name'],'price'=>$a['price_pkr']??0,'quantity'=>1,'image'=>$a['image'],'kind'=>$a['category']];
function h($v){return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Complete Solar System Design</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css"><link rel="stylesheet" href="css/style.css">
<style>
.design-shell{max-width:1200px;margin:0 auto;padding:6rem 3rem}.design-head{margin-bottom:2rem}.design-head h1{font-family:'Barlow Condensed',sans-serif;font-size:5.5rem;line-height:.95}.design-head p{color:var(--light-color);font-size:1.35rem;line-height:1.6}.design-summary,.design-table-wrap{padding:1.8rem;background:var(--white);border:var(--border);margin-bottom:1.5rem}.design-summary{display:grid;grid-template-columns:repeat(4,1fr);gap:1rem}.design-stat{padding:1rem;background:var(--light-bg)}.design-stat span{display:block;color:var(--light-color);font-size:1.05rem;text-transform:uppercase}.design-stat strong{font-size:1.7rem}.design-table{width:100%;border-collapse:collapse}.design-table th,.design-table td{padding:1rem .7rem;border-bottom:var(--border);text-align:left;font-size:1.25rem}.design-table img{width:5.5rem;height:4rem;object-fit:contain}.design-table small{display:block;color:var(--light-color)}.design-warning{padding:1rem;margin-bottom:1.5rem;color:#7a2e20;background:#fff1ed;border:1px solid #e45a3b;font-size:1.25rem}.design-actions{display:flex;gap:1rem;flex-wrap:wrap}@media(max-width:700px){.design-shell{padding:4rem 1.5rem}.design-head h1{font-size:4rem}.design-summary{grid-template-columns:1fr 1fr}.design-table-wrap{overflow:auto}.design-table{min-width:700px}}@media print{.header,.footer,.design-actions{display:none!important}.design-shell{padding:0}.design-table-wrap,.design-summary{border:0}}
</style>
</head>
<body>
<?php include 'components/user_header.php'; ?>
<main class="design-shell" id="designReport">
<header class="design-head"><p class="section-kicker">SYSTEM DESIGN / 01</p><h1>Complete solar package.</h1><p>Recommended from the calculated load. Protection, cable and DB-box items are included for quotation; final electrical sizing must be confirmed by a qualified installer.</p></header>
<section class="design-summary"><div class="design-stat"><span>Connected load</span><strong><?= h(number_format($load_kw,2)); ?> kW</strong></div><div class="design-stat"><span>Estimated energy</span><strong><?= h(number_format($daily_kwh,2)); ?> kWh/day</strong></div><div class="design-stat"><span>Suggested PV</span><strong><?= h(number_format($pv_kw,2)); ?> kW</strong></div><div class="design-stat"><span>Battery target</span><strong><?= h(number_format($battery_kwh,2)); ?> kWh</strong></div></section>
<div class="design-warning">Prices marked “On request” are not invented. Add current retail prices in the catalog before accepting payment or issuing a final customer invoice.</div>
<section class="design-table-wrap"><table class="design-table"><thead><tr><th>Include</th><th>Picture</th><th>Recommended item</th><th>Qty</th><th>Retail price</th></tr></thead><tbody><?php foreach($package as $i=>$item): ?><tr><td><input type="checkbox" class="package-check" data-index="<?= $i; ?>" checked></td><td><img src="<?= h($item['image']); ?>" alt="<?= h($item['name']); ?>"></td><td><strong><?= h($item['name']); ?></strong><small><?= h($item['kind']); ?></small></td><td><?= h($item['quantity']); ?></td><td><?= $item['price']>0 ? 'PKR '.number_format($item['price']) : 'On request'; ?></td></tr><?php endforeach; ?></tbody></table></section>
<div class="design-actions"><button class="btn" id="downloadInvoice" type="button"><i class="fas fa-file-pdf"></i> Download quotation</button><button class="btn" id="addSelected" type="button"><i class="fas fa-cart-plus"></i> Add selected to cart</button><button class="option-btn" id="addAll" type="button">Add complete package</button><a class="option-btn" href="calculator.php">Back to calculator</a></div>
</main>
<?php include 'components/footer.php'; ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script><script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script><script src="js/script.js"></script>
<script>
const packageItems=<?= json_encode($package,JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT); ?>;
function selectedItems(all=false){return packageItems.filter((item,i)=>all||document.querySelector(`.package-check[data-index="${i}"]`)?.checked);}
async function addCart(all){const response=await fetch('add_package_to_cart.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({items:selectedItems(all)})});const data=await response.json();if(data.redirect){window.location.href=data.redirect;}else{alert(data.error||'Unable to add package.');}}
document.getElementById('addSelected').addEventListener('click',()=>addCart(false));document.getElementById('addAll').addEventListener('click',()=>addCart(true));document.getElementById('downloadInvoice').addEventListener('click',async()=>{if(!window.jspdf?.jsPDF||!window.html2canvas){window.print();return;}const canvas=await html2canvas(document.getElementById('designReport'),{scale:1.5});const pdf=new window.jspdf.jsPDF('p','mm','a4');const width=pdf.internal.pageSize.getWidth();pdf.addImage(canvas.toDataURL('image/jpeg',.95),'JPEG',0,0,width,canvas.height*width/canvas.width);pdf.save('solar-package-quotation.pdf');});
</script>
</body></html>
