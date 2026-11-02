<?php

include 'components/connect.php';

session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
};

include 'components/wishlist_cart.php';

$verified_products = json_decode(file_get_contents(__DIR__ . '/data/solar-products.json'), true) ?: [];
$solar_accessories = json_decode(file_get_contents(__DIR__ . '/data/solar-accessories.json'), true) ?: [];
$home_catalog = array_merge($verified_products, array_map(function($item){ return array_merge($item, ['company'=>'Solar Store','type'=>'accessory','model'=>$item['name'] ?? '']); }, $solar_accessories));
$brand_priority = ['Knox','ZIEWNIC','Inverex','Luminey','Primax','itel Energy','T-Solar / Tesla Industries','SolarMax','Solis','GoodWe','Growatt','Huawei','Sungrow','SMA','Fronius','BYD'];
usort($home_catalog, function($a,$b) use ($brand_priority){ $ai=array_search($a['company']??'', $brand_priority, true); $bi=array_search($b['company']??'', $brand_priority, true); $ai=$ai===false?999:$ai; $bi=$bi===false?999:$bi; return $ai===$bi ? strcasecmp((string)($a['name']??''),(string)($b['name']??'')) : $ai<=>$bi; });
$home_carousels = [
   'Inverters' => array_values(array_filter($home_catalog, fn($item)=>($item['category'] ?? '') === 'Inverters')),
   'Batteries' => array_values(array_filter($home_catalog, fn($item)=>($item['category'] ?? '') === 'Batteries')),
   'Panels' => array_values(array_filter($home_catalog, fn($item)=>($item['category'] ?? '') === 'Panels')),
   'Protection, DB boxes & cables' => array_values(array_filter($home_catalog, fn($item)=>in_array(($item['category'] ?? ''), ['Protection','DB Boxes','Cables','Accessories','Monitoring'], true))),
];

?>
<!-- html -->
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Home</title>
   <link rel="stylesheet" href="https://unpkg.com/swiper@8/swiper-bundle.min.css" />
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
   <style>.verified-catalog{max-width:1440px;padding:6rem 3rem;margin:0 auto}.verified-catalog-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:1.2rem}.verified-card{display:flex;min-height:30rem;flex-direction:column;padding:1.3rem;color:var(--black);background:var(--white);border:var(--border)}.verified-card img{width:100%;height:12rem;object-fit:contain;background:var(--light-bg)}.verified-card .product-brand{margin-top:1rem}.verified-card .name{font-size:1.5rem;font-weight:700}.verified-card small{margin:.6rem 0;color:var(--light-color);line-height:1.5}.verified-card .price{margin-top:auto;padding-top:1rem;color:var(--main-color);font-weight:700}@media(max-width:950px){.verified-catalog-grid{grid-template-columns:repeat(3,minmax(0,1fr))}}@media(max-width:650px){.verified-catalog{padding:4rem 1.5rem}.verified-catalog-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
   </style>

</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<div class="home-bg">
   <section class="home-hero" aria-label="Solar power collection">
      <img class="home-hero-image" src="images/solar-field-hero.jpg" alt="Rows of solar panels beneath an open sky">
      <div class="home-hero-content">
         <div class="hero-meta"><span>FIELD NOTES / 01</span><span>SOLAR SYSTEMS / 2026</span></div>
         <p class="hero-overline">Energy for the way you live</p>
         <h1>Power your<br><em>own horizon.</em></h1>
         <p class="hero-description">Panels, storage and the components to build a more independent energy setup.</p>
         <div class="hero-actions">
            <a href="shop.php" class="hero-primary">Shop the collection <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
            <a href="calculator.php" class="hero-secondary"><i class="fas fa-calculator" aria-hidden="true"></i> Size your system</a>
         </div>
      </div>
      <div class="hero-caption"><span>01 — GENERATE</span><span>Make every ray count</span></div>
   </section>
</div>

<div class="collection-ribbon" aria-label="Solar equipment categories">
   <span>GENERATE</span><i class="fas fa-sun" aria-hidden="true"></i>
   <span>STORE</span><i class="fas fa-battery-full" aria-hidden="true"></i>
   <span>POWER</span><i class="fas fa-bolt" aria-hidden="true"></i>
   <span>REPEAT</span><i class="fas fa-sun" aria-hidden="true"></i>
</div>

<section class="category category-section">
   <div class="section-heading">
      <div><p class="section-kicker">01 / COMPONENTS</p><h2 class="heading">Build your setup.</h2></div>
      <a href="shop.php" class="section-link">Explore all equipment <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
   </div>
   <div class="category-grid">
      <a href="category.php?category=Panels" class="category-card">
         <span class="category-index">01</span><img class="category-photo" src="images/category-panels.jpg" alt="Photovoltaic solar panels in an open field">
         <h3>Solar panels</h3><span class="category-cta">Explore panels <i class="fas fa-arrow-right" aria-hidden="true"></i></span>
      </a>
      <a href="category.php?category=Inverters" class="category-card">
         <span class="category-index">02</span><img class="category-illustration" src="uploaded_img/inverter-1.svg" alt="Hybrid solar inverter illustration">
         <h3>Inverters</h3><span class="category-cta">Explore inverters <i class="fas fa-arrow-right" aria-hidden="true"></i></span>
      </a>
      <a href="category.php?category=Batteries" class="category-card">
         <span class="category-index">03</span><img class="category-photo" src="images/category-battery.jpg" alt="Wall-mounted solar power and energy storage system">
         <h3>Storage</h3><span class="category-cta">Explore batteries <i class="fas fa-arrow-right" aria-hidden="true"></i></span>
      </a>
      <a href="category.php?category=Breakers" class="category-card">
         <span class="category-index">04</span><img class="category-photo" src="images/category-breakers.jpg" alt="Installed circuit breakers and electrical protection equipment">
         <h3>Breakers</h3><span class="category-cta">Explore breakers <i class="fas fa-arrow-right" aria-hidden="true"></i></span>
      </a>
      <a href="category.php?category=Mounting" class="category-card">
         <span class="category-index">05</span><img class="category-illustration" src="uploaded_img/mounting-1.svg" alt="Solar panel mounting rack illustration">
         <h3>Mounting</h3><span class="category-cta">Explore mounting <i class="fas fa-arrow-right" aria-hidden="true"></i></span>
      </a>
   </div>
</section>

<section class="home-products">
   <div class="section-heading">
      <div><p class="section-kicker">02 / AVAILABLE NOW</p><h2 class="heading">Latest additions.</h2></div>
      <a href="shop.php" class="section-link">View the store <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
   </div>

   <div class="swiper products-slider">

   <div class="swiper-wrapper">
   <!-- fetching data from user by using pdo which replaces the mysqli youtube sy learn kia H  -->
   <?php
   $select_products = $conn->prepare("SELECT * FROM `products` WHERE brand <> '' ORDER BY id DESC LIMIT 6");
     $select_products->execute();
     if($select_products->rowCount() > 0){
      while($fetch_product = $select_products->fetch(PDO::FETCH_ASSOC)){
   ?>
   <form action="" method="post" class="swiper-slide slide">
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
      echo '<p class="empty">no products added yet!</p>';
   }
   ?>

   </div>

   <div class="swiper-pagination"></div>

   </div>

</section>

<section class="verified-catalog">
   <div class="section-heading"><div><p class="section-kicker">03 / COMPLETE CATALOG</p><h2 class="heading">Every system component.</h2></div><a href="assistant.php" class="section-link">Ask the assistant <i class="fas fa-arrow-right" aria-hidden="true"></i></a></div>
   <?php foreach($home_carousels as $carousel_name => $carousel_items): if(!$carousel_items) continue; ?>
   <section class="brand-carousel-section" aria-label="<?= htmlspecialchars($carousel_name, ENT_QUOTES, 'UTF-8'); ?> catalog">
      <div class="brand-carousel-heading"><h3><?= htmlspecialchars($carousel_name, ENT_QUOTES, 'UTF-8'); ?></h3><a href="category.php?category=<?= rawurlencode(strpos($carousel_name, 'Protection') === 0 ? 'Breakers' : $carousel_name); ?>">View all <i class="fas fa-arrow-right" aria-hidden="true"></i></a></div>
      <div class="brand-carousel-viewport"><div class="brand-carousel-track">
      <?php for($copy=0; $copy<2; $copy++): foreach($carousel_items as $carousel_item):
         $carousel_name_item = (string)($carousel_item['name'] ?? $carousel_item['model'] ?? 'Solar product');
         $carousel_model = (string)($carousel_item['model'] ?? '');
         $carousel_company = (string)($carousel_item['company'] ?? '');
         $carousel_image = (string)($carousel_item['image_url'] ?? $carousel_item['image'] ?? 'images/category-inverter.jpg');
         $carousel_key = $carousel_company . '|' . ($carousel_model !== '' ? $carousel_model : $carousel_name_item);
         $carousel_link = ($carousel_item['type'] ?? '') === 'accessory' ? 'assistant.php?q=' . rawurlencode($carousel_name_item) : 'solar_product.php?product=' . rawurlencode($carousel_key);
      ?>
      <a class="brand-carousel-card" href="<?= $carousel_link; ?>">
         <img src="<?= htmlspecialchars($carousel_image, ENT_QUOTES, 'UTF-8'); ?>" alt="<?= htmlspecialchars($carousel_company . ' ' . $carousel_name_item, ENT_QUOTES, 'UTF-8'); ?>" loading="lazy" onerror="this.onerror=null;this.src='images/category-inverter.jpg';">
         <strong><?= htmlspecialchars($carousel_company, ENT_QUOTES, 'UTF-8'); ?></strong>
         <span><?= htmlspecialchars($carousel_name_item, ENT_QUOTES, 'UTF-8'); ?></span>
         <?php if($carousel_model !== '' && $carousel_model !== $carousel_name_item): ?><small><?= htmlspecialchars($carousel_model, ENT_QUOTES, 'UTF-8'); ?></small><?php endif; ?>
         <em><?= !empty($carousel_item['price_pkr']) ? 'PKR ' . number_format((float)$carousel_item['price_pkr']) : 'PKR price on request'; ?></em>
      </a>
      <?php endforeach; endfor; ?>
      </div></div>
   </section>
   <?php endforeach; ?>
   <div class="verified-catalog-grid">
   <?php foreach(array_merge($verified_products, $solar_accessories) as $catalog_item): ?>
      <?php $catalog_key=($catalog_item['company']??'Solar Store').'|'.($catalog_item['model']??$catalog_item['name']); $catalog_image=$catalog_item['image_url']??$catalog_item['image']??'images/category-inverter.jpg'; ?>
      <a class="verified-card" href="<?= isset($catalog_item['model']) ? 'solar_product.php?product='.rawurlencode($catalog_key) : 'assistant.php?q='.rawurlencode($catalog_item['name']); ?>">
         <img src="<?= htmlspecialchars($catalog_image,ENT_QUOTES,'UTF-8'); ?>" alt="<?= htmlspecialchars(($catalog_item['company']??'Solar Store').' '.$catalog_item['name'],ENT_QUOTES,'UTF-8'); ?>">
         <div class="product-brand"><?= htmlspecialchars(($catalog_item['company']??'Solar Store').' / '.($catalog_item['category']??'Equipment'),ENT_QUOTES,'UTF-8'); ?></div>
         <div class="name"><?= htmlspecialchars($catalog_item['name'],ENT_QUOTES,'UTF-8'); ?></div>
         <small><?= htmlspecialchars($catalog_item['details']??'',ENT_QUOTES,'UTF-8'); ?></small>
         <div class="price"><?= isset($catalog_item['price_pkr']) && $catalog_item['price_pkr'] ? 'PKR '.number_format($catalog_item['price_pkr']) : 'PKR price on request'; ?></div>
      </a>
   <?php endforeach; ?>
   </div>
</section>

<section class="store-guide">
   <div class="guide-image-wrap">
      <img src="images/solar-field-hero.jpg" alt="Solar array in the open landscape">
      <span>FIELD NOTES / 02</span>
   </div>
   <div class="guide-copy">
      <p class="section-kicker">03 / START WITH YOUR LOAD</p>
      <h2>Know what you need<br>before you buy.</h2>
      <p>Estimate your daily usage, explore a practical system size, then compare the components that fit your setup.</p>
      <a href="calculator.php" class="hero-primary">Open the solar calculator <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
      <a href="contact_us.php" class="guide-contact">Questions about a system? Contact us</a>
   </div>
</section>

<section class="store-steps">
   <article><span>01 / PLAN</span><h3>Start with your energy use.</h3><p>Use the load calculator to estimate what your home or workspace needs.</p></article>
   <article><span>02 / BUILD</span><h3>Choose the right components.</h3><p>Compare panels, inverters, storage and mounting in one place.</p></article>
   <article><span>03 / ASK</span><h3>Get help with the details.</h3><p>Send your system questions to the Solar Store team.</p></article>
</section>

<?php include 'components/footer.php'; ?>

<script src="https://unpkg.com/swiper@8/swiper-bundle.min.js"></script>

<script src="js/script.js"></script>

<script>
// using for swiping the items
var swiper = new Swiper(".products-slider", {
   loop:true,
   spaceBetween: 20,
   pagination: {
      el: ".swiper-pagination",
      clickable:true,
   },
   breakpoints: {
      550: {
        slidesPerView: 2,
      },
      768: {
        slidesPerView: 2,
      },
      1024: {
        slidesPerView: 3,
      },
   },
});

</script>
</body>
</html>
