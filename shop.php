<?php

include 'components/connect.php';

session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
};

include 'components/wishlist_cart.php';

$brand_filter = trim($_GET['brand'] ?? '');
$category_filter = trim($_GET['category'] ?? '');
$type_filter = strtolower(trim($_GET['type'] ?? ''));
$category_aliases = ['panel'=>'Panels','panels'=>'Panels','inverter'=>'Inverters','inverters'=>'Inverters','battery'=>'Batteries','batteries'=>'Batteries','breaker'=>'Breakers','breakers'=>'Breakers','mounting'=>'Mounting'];
$category_filter = $category_aliases[strtolower($category_filter)] ?? $category_filter;
$verified_catalog_file = __DIR__ . '/data/solar-products.json';
$verified_accessories_file = __DIR__ . '/data/solar-accessories.json';
$verified_catalog = is_file($verified_catalog_file) ? (json_decode(file_get_contents($verified_catalog_file), true) ?: []) : [];
$verified_accessories = is_file($verified_accessories_file) ? (json_decode(file_get_contents($verified_accessories_file), true) ?: []) : [];
$verified_items = [];
foreach($verified_catalog as $item){
   if($brand_filter !== '' && strcasecmp((string)($item['company'] ?? ''), $brand_filter) !== 0) continue;
   if($category_filter !== '' && strcasecmp((string)($item['category'] ?? ''), $category_filter) !== 0) continue;
   if($type_filter !== '' && strcasecmp((string)($item['subtype'] ?? $item['type'] ?? ''), $type_filter) !== 0) continue;
   $verified_items[] = $item;
}
if($brand_filter === '' || strcasecmp($brand_filter, 'Solar Store') === 0){
   foreach($verified_accessories as $item){
      $accessory_category = (string)($item['category'] ?? '');
      $matches_category = $category_filter === '' || strcasecmp($category_filter, 'Breakers') === 0 && in_array($accessory_category, ['Protection','DB Boxes'], true) || strcasecmp($accessory_category, $category_filter) === 0;
      if($matches_category && ($type_filter === '' || $type_filter === 'accessory')) $verified_items[] = array_merge($item, ['company'=>'Solar Store','type'=>'accessory','category'=>$accessory_category,'model'=>$item['name'] ?? '']);
   }
}
$brand_priority = ['Knox','ZIEWNIC','Inverex','Luminey','Primax','itel Energy','T-Solar / Tesla Industries','SolarMax','Solis','GoodWe','Growatt','Huawei','Sungrow','SMA','Fronius','BYD'];
usort($verified_items, function($a,$b) use ($brand_priority){ $ai=array_search($a['company']??'', $brand_priority, true); $bi=array_search($b['company']??'', $brand_priority, true); $ai=$ai===false?999:$ai; $bi=$bi===false?999:$bi; return $ai===$bi ? strcasecmp((string)($a['name']??''),(string)($b['name']??'')) : $ai<=>$bi; });
$show_catalog = true;
$db_filters = ['brand <> ?'];
$db_values = [''];
if($brand_filter !== ''){ $db_filters[] = 'brand = ?'; $db_values[] = $brand_filter; }
if($category_filter !== ''){ $db_filters[] = 'category = ?'; $db_values[] = $category_filter; }
$db_sql = 'SELECT * FROM `products` WHERE ' . implode(' AND ', $db_filters) . ' ORDER BY category, name';

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>shop</title>
   
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<section class="products">

   <div class="section-heading category-results-heading">
      <div>
         <p class="section-kicker"><?= htmlspecialchars(($brand_filter !== '' ? $brand_filter . ' / ' : '') . ($category_filter !== '' ? $category_filter : 'ALL EQUIPMENT') . ($type_filter !== '' ? ' / ' . $type_filter : ''), ENT_QUOTES, 'UTF-8'); ?></p>
         <h1 class="heading">Products and models</h1>
      </div>
      <?php if($brand_filter !== '' || $category_filter !== '' || $type_filter !== ''): ?><a href="shop.php" class="section-link">Clear filters <i class="fas fa-rotate-left" aria-hidden="true"></i></a><?php endif; ?>
   </div>

   <div class="box-container">

   <?php
   $select_products = $conn->prepare($db_sql);
     $select_products->execute($db_values);
     if($select_products->rowCount() > 0){
      while($fetch_product = $select_products->fetch(PDO::FETCH_ASSOC)){
   ?>
   <form action="" method="post" class="box">
      <input type="hidden" name="pid" value="<?= $fetch_product['id']; ?>">
      <input type="hidden" name="name" value="<?= $fetch_product['name']; ?>">
      <input type="hidden" name="price" value="<?= $fetch_product['price']; ?>">
      <input type="hidden" name="image" value="<?= $fetch_product['image_01']; ?>">
      <button class="fas fa-heart" type="submit" name="add_to_wishlist"></button>
      <a href="quick_view.php?pid=<?= $fetch_product['id']; ?>" class="fas fa-eye"></a>
      <img src="uploaded_img/<?= $fetch_product['image_01']; ?>" alt="">
      <div class="product-brand"><?= htmlspecialchars($fetch_product['brand'] . ' / ' . $fetch_product['category'], ENT_QUOTES, 'UTF-8'); ?></div>
      <div class="name"><?= $fetch_product['name']; ?></div>
      <div class="flex">
         <div class="price"><span>PKR </span><?= number_format((float)$fetch_product['price']); ?><span>/-</span></div>
         <input type="number" name="qty" class="qty" min="1" max="99" onkeypress="if(this.value.length == 2) return false;" value="1">
      </div>
      <input type="submit" value="add to cart" class="btn" name="add_to_cart">
   </form>
   <?php
      }
   }else{
      echo '<p class="empty">no products found!</p>';
   }
   ?>

   </div>

   <?php if($verified_items): ?>
   <div class="section-heading category-results-heading" style="margin-top:4rem;">
      <div><p class="section-kicker">VERIFIED MANUFACTURER CATALOG</p><h2 class="heading">Brands, models and details</h2></div>
   </div>
   <div class="verified-catalog-grid">
      <?php foreach($verified_items as $item):
         $item_name = (string)($item['name'] ?? $item['model'] ?? 'Solar product');
         $item_model = (string)($item['model'] ?? '');
         $item_company = (string)($item['company'] ?? '');
         $item_category = (string)($item['category'] ?? '');
         $item_image = (string)($item['image_url'] ?? $item['image'] ?? 'images/category-inverter.jpg');
         $item_key = $item_company . '|' . ($item_model !== '' ? $item_model : $item_name);
         $item_detail_url = !empty($item['official_url']) ? $item['official_url'] : 'assistant.php?q=' . rawurlencode($item_name);
         $item_price = $item['price_pkr'] ?? null;
      ?>
      <article class="verified-catalog-card">
         <img src="<?= htmlspecialchars($item_image, ENT_QUOTES, 'UTF-8'); ?>" alt="<?= htmlspecialchars($item_name, ENT_QUOTES, 'UTF-8'); ?>" loading="lazy" onerror="this.onerror=null;this.src='images/category-inverter.jpg';">
         <div class="catalog-meta"><?= htmlspecialchars($item_company . ' / ' . $item_category, ENT_QUOTES, 'UTF-8'); ?></div>
         <h3><?= htmlspecialchars($item_name, ENT_QUOTES, 'UTF-8'); ?></h3>
         <?php if($item_model !== '' && $item_model !== $item_name): ?><div class="product-brand">Model: <?= htmlspecialchars($item_model, ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
         <p><?= htmlspecialchars((string)($item['details'] ?? 'Verified product information and official source available.'), ENT_QUOTES, 'UTF-8'); ?></p>
         <div class="catalog-price"><?= $item_price !== null && $item_price !== '' ? 'PKR ' . number_format((float)$item_price) : 'PKR price on request'; ?></div>
         <div class="catalog-actions">
            <a class="primary" href="solar_product.php?product=<?= rawurlencode($item_key); ?>"><i class="fas fa-circle-info"></i> Full details</a>
            <a href="<?= htmlspecialchars($item_detail_url, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener"><i class="fas fa-arrow-up-right-from-square"></i> Official source</a>
         </div>
      </article>
      <?php endforeach; ?>
   </div>
   <?php endif; ?>

</section>













<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>
