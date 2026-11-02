<?php
include 'components/connect.php';
session_start();
$user_id = $_SESSION['user_id'] ?? '';
include 'components/wishlist_cart.php';
$products = json_decode(file_get_contents(__DIR__ . '/data/solar-products.json'), true) ?: [];
$requested = (string)($_GET['product'] ?? '');
$product = null;
foreach($products as $candidate){
   $key = ($candidate['company'] ?? '') . '|' . ($candidate['model'] ?? '');
   if(hash_equals($key, urldecode($requested))){ $product = $candidate; break; }
}
$current_page = basename($_SERVER['PHP_SELF']);
function product_html($value){ return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
$spec_labels = [
   'subtype'=>'System type','output_kw'=>'Rated output (kW)','output_max_kw'=>'Maximum output (kW)',
   'capacity_kwh'=>'Capacity (kWh)','battery_kwh'=>'Battery reference (kWh)','panel_w'=>'Panel power (W)',
   'efficiency'=>'Efficiency (%)','voltage'=>'Nominal voltage','mppt_range'=>'MPPT range',
   'max_pv_input'=>'Maximum PV input','max_charge_current'=>'Maximum charge current','ip_rating'=>'Ingress protection',
   'phase'=>'Phase','chemistry'=>'Battery chemistry','usable_energy_kwh'=>'Usable energy (kWh)',
   'warranty'=>'Warranty','availability'=>'Pakistan availability'
];
$technical_specs = [];
if($product){
   foreach($product as $field=>$value){
      if(in_array($field, ['company','type','category','name','model','details','image_url','official_url','price_pkr'], true) || $value === '' || $value === null) continue;
      if(is_scalar($value)) $technical_specs[$field] = [$spec_labels[$field] ?? ucwords(str_replace(['_','-'], ' ', $field)), $value];
   }
}
$local_recommendations = $product ? (strcasecmp((string)($product['category'] ?? ''), 'Inverters') === 0
   ? ['Battery bank matched to the inverter voltage and communication protocol','PV panels sized within the inverter MPPT voltage/current limits','DC isolator, DC Type 2 SPD, AC breaker, AC SPD and labelled AC/DC DB box','Correct-size PV cable, AC cable, MC4-compatible connectors and earthing kit']
   : (strcasecmp((string)($product['category'] ?? ''), 'Batteries') === 0
      ? ['Compatible inverter with the same battery voltage and approved communication protocol','Battery DC breaker/fuse, isolator and appropriately rated battery cable','Rack/wall mounting, ventilation/clearance and earthing accessories','Commissioning, BMS configuration and warranty registration']
      : ['Correctly rated mounting, cable and protection accessories','Site-specific electrical design and installation by a qualified electrician','Datasheet, warranty and serial-number verification before purchase'])) : [];
?>
<!doctype html>
<html lang="en">
<head>
   <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
   <title><?= $product ? product_html($product['company'].' '.$product['name']) : 'Solar product'; ?></title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
   <style>
      .solar-detail{max-width:1100px;margin:0 auto;padding:7rem 3rem}.solar-detail-card{display:grid;grid-template-columns:minmax(25rem,.85fr) minmax(0,1.15fr);gap:3rem;padding:2rem;background:var(--white);border:var(--border)}.solar-product-visual img{width:100%;height:30rem;object-fit:contain;background:var(--light-bg)}.image-note,.local-data-note{color:var(--light-color);font-size:1.15rem;line-height:1.5}.solar-detail h1{margin:.5rem 0 1rem;font-family:'Barlow Condensed',sans-serif;font-size:4.8rem;line-height:1}.solar-detail .eyebrow{color:var(--main-color);font-weight:700;letter-spacing:.1rem;text-transform:uppercase}.solar-detail .model{color:var(--light-color);font-size:1.5rem}.solar-detail .detail-copy{margin:1.5rem 0;color:var(--light-color);font-size:1.45rem;line-height:1.7}.local-section-title{font-size:2rem;margin:2rem 0 .8rem}.solar-specs{display:grid;grid-template-columns:repeat(2,1fr);gap:.8rem;margin:1.5rem 0}.solar-spec{padding:1rem;background:var(--light-bg);border:var(--border)}.solar-spec span{display:block;color:var(--light-color);font-size:1.05rem;text-transform:uppercase}.solar-spec strong{display:block;margin-top:.3rem;font-size:1.35rem}.local-checklist{margin:0 0 1.5rem;padding-left:2rem;color:var(--light-color);font-size:1.35rem;line-height:1.8}.solar-actions{display:flex;flex-wrap:wrap;gap:1rem;margin:1.5rem 0}.solar-source{color:var(--main-color);font-size:1.3rem}.local-data-note{margin:1.5rem 0}@media(max-width:760px){.solar-detail{padding:4rem 1.5rem}.solar-detail-card{grid-template-columns:1fr}.solar-detail h1{font-size:4rem}.solar-specs{grid-template-columns:1fr}}
   </style>
</head>
<body>
<?php include 'components/user_header.php'; ?>
<main class="solar-detail">
<?php if(!$product): ?>
   <div class="empty"><h1>Product not found</h1><p>Use the calculator or Solar Assistant to find a verified product.</p><a class="btn" href="assistant.php">Open Solar Assistant</a></div>
<?php else: ?>
   <article class="solar-detail-card">
      <div class="solar-product-visual">
         <img src="<?= product_html($product['image_url'] ?? 'images/category-inverter.jpg'); ?>" alt="<?= product_html($product['company'].' '.$product['name']); ?> product image">
         <p class="image-note">Manufacturer product image. Exact appearance may vary by regional SKU.</p>
      </div>
      <div>
         <p class="eyebrow"><?= product_html($product['company'].' / '.($product['category'] ?? 'Solar equipment')); ?></p>
         <h1><?= product_html($product['name']); ?></h1>
         <p class="model"><?= product_html($product['model'] ?? ''); ?></p>
         <p class="detail-copy"><?= product_html($product['details'] ?? ''); ?></p>
         <?php if($technical_specs): ?>
            <h2 class="local-section-title">Technical specifications</h2>
            <div class="solar-specs">
               <?php foreach($technical_specs as [$label,$value]): ?><div class="solar-spec"><span><?= product_html($label); ?></span><strong><?= product_html($value); ?></strong></div><?php endforeach; ?>
            </div>
         <?php endif; ?>
         <?php if($local_recommendations): ?>
            <h2 class="local-section-title">Complete solution checklist</h2>
            <ul class="local-checklist"><?php foreach($local_recommendations as $recommendation): ?><li><?= product_html($recommendation); ?></li><?php endforeach; ?></ul>
         <?php endif; ?>
         <div class="solar-actions">
            <a class="btn" href="assistant.php?q=<?= rawurlencode($product['company'].' '.$product['model']); ?>">Ask about this product</a>
            <a class="option-btn" href="shop.php?brand=<?= rawurlencode($product['company']); ?>&category=<?= rawurlencode($product['category'] ?? ''); ?>">See compatible catalog</a>
         </div>
         <p class="local-data-note">The information above is available on this site. Confirm the exact SKU, current PKR price, stock, warranty and installation requirements with the seller before ordering.</p>
         <?php if(!empty($product['official_url'])): ?><a class="solar-source" href="<?= product_html($product['official_url']); ?>" target="_blank" rel="noopener">Optional: verify manufacturer source</a><?php endif; ?>
      </div>
   </article>
<?php endif; ?>
</main>
<?php include 'components/footer.php'; ?>
<script src="js/script.js"></script>
</body>
</html>
