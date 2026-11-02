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
?>
<!doctype html>
<html lang="en">
<head>
   <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
   <title><?= $product ? product_html($product['company'].' '.$product['name']) : 'Solar product'; ?></title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
   <style>
      .solar-detail{max-width:1100px;margin:0 auto;padding:7rem 3rem}.solar-detail-card{display:grid;grid-template-columns:minmax(25rem,.85fr) minmax(0,1.15fr);gap:3rem;padding:2rem;background:var(--white);border:var(--border)}.solar-detail-card img{width:100%;height:30rem;object-fit:contain;background:var(--light-bg)}.solar-detail h1{margin:.5rem 0 1rem;font-family:'Barlow Condensed',sans-serif;font-size:4.8rem;line-height:1}.solar-detail .eyebrow{color:var(--main-color);font-weight:700;letter-spacing:.1rem;text-transform:uppercase}.solar-detail .model{color:var(--light-color);font-size:1.5rem}.solar-detail .detail-copy{margin:1.5rem 0;color:var(--light-color);font-size:1.45rem;line-height:1.7}.solar-specs{display:grid;grid-template-columns:repeat(2,1fr);gap:.8rem;margin:1.5rem 0}.solar-spec{padding:1rem;background:var(--light-bg);border:var(--border)}.solar-spec span{display:block;color:var(--light-color);font-size:1.05rem;text-transform:uppercase}.solar-spec strong{display:block;margin-top:.3rem;font-size:1.35rem}.solar-source{color:var(--main-color);font-size:1.3rem}@media(max-width:760px){.solar-detail{padding:4rem 1.5rem}.solar-detail-card{grid-template-columns:1fr}.solar-detail h1{font-size:4rem}}
   </style>
</head>
<body>
<?php include 'components/user_header.php'; ?>
<main class="solar-detail">
<?php if(!$product): ?>
   <div class="empty"><h1>Product not found</h1><p>Use the calculator or Solar Assistant to find a verified product.</p><a class="btn" href="assistant.php">Open Solar Assistant</a></div>
<?php else: ?>
   <article class="solar-detail-card">
      <img src="<?= product_html($product['image_url'] ?? 'images/category-inverter.jpg'); ?>" alt="<?= product_html($product['company'].' '.$product['name']); ?> product image">
      <div>
         <p class="eyebrow"><?= product_html($product['company'].' / '.($product['category'] ?? 'Solar equipment')); ?></p>
         <h1><?= product_html($product['name']); ?></h1>
         <p class="model"><?= product_html($product['model'] ?? ''); ?></p>
         <p class="detail-copy"><?= product_html($product['details'] ?? ''); ?></p>
         <div class="solar-specs">
            <?php foreach(['subtype'=>'Type','output_kw'=>'Minimum output (kW)','output_max_kw'=>'Maximum output (kW)','capacity_kwh'=>'Capacity (kWh)','panel_w'=>'Panel power (W)','efficiency'=>'Efficiency (%)','voltage'=>'Nominal voltage'] as $field=>$label): ?>
               <?php if(isset($product[$field])): ?><div class="solar-spec"><span><?= $label; ?></span><strong><?= product_html($product[$field]); ?></strong></div><?php endif; ?>
            <?php endforeach; ?>
         </div>
         <a class="btn" href="<?= product_html($product['official_url'] ?? '#'); ?>" target="_blank" rel="noopener">Open official source</a>
         <a class="option-btn" href="assistant.php?q=<?= rawurlencode($product['company'].' '.$product['model']); ?>">Ask Solar Assistant</a>
      </div>
   </article>
<?php endif; ?>
</main>
<?php include 'components/footer.php'; ?>
<script src="js/script.js"></script>
</body>
</html>
