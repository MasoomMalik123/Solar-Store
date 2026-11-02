<?php
   if(isset($message)){
      foreach($message as $message){
         echo '
         <div class="message">
            <span>'.$message.'</span>
            <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
         </div>
         ';
      }
   }
?>

<header class="header">

   <section class="flex">

   <?php $current_page = basename($_SERVER['PHP_SELF']);
      // load social links if present
      $social_links = ['linkedin'=>'#','github'=>'#','download'=>'#'];
      if(file_exists(__DIR__ . '/social_links.php')){
         $cfg = include __DIR__ . '/social_links.php';
         if(is_array($cfg)) $social_links = array_merge($social_links, $cfg);
      }

      // prefetch user profile for header avatar
      $fetch_profile = null;
      if(isset($conn, $user_id) && $user_id !== ''){
         try{
            $sp = $conn->prepare("SELECT * FROM `users` WHERE id = ?");
            $sp->execute([$user_id]);
            if($sp->rowCount()>0) $fetch_profile = $sp->fetch(PDO::FETCH_ASSOC);
         }catch(Exception $e){ }
      }
      $is_logged_in = isset($user_id) && $user_id !== '' && is_array($fetch_profile);
      $catalog_by_brand = [];
      if(isset($conn)){
         try{
            $catalog_rows = $conn->query("SELECT DISTINCT brand, category FROM `products` WHERE brand <> '' AND category <> '' ORDER BY brand, category")->fetchAll(PDO::FETCH_ASSOC);
            foreach($catalog_rows as $catalog_row){
               $catalog_by_brand[$catalog_row['brand']][] = $catalog_row['category'];
            }
         }catch(Exception $e){ }
      }
      // The verified manufacturer catalogue is JSON-backed and must appear in
      // the same brand/category selector as database shop products.
      $verified_catalog_file = __DIR__ . '/../data/solar-products.json';
      $verified_accessories_file = __DIR__ . '/../data/solar-accessories.json';
      $verified_catalog = is_file($verified_catalog_file) ? (json_decode(file_get_contents($verified_catalog_file), true) ?: []) : [];
      $verified_accessories = is_file($verified_accessories_file) ? (json_decode(file_get_contents($verified_accessories_file), true) ?: []) : [];
      foreach($verified_catalog as $catalog_item){
         $verified_brand = trim((string)($catalog_item['company'] ?? ''));
         $verified_category = trim((string)($catalog_item['category'] ?? ''));
         if($verified_brand !== '' && $verified_category !== '') $catalog_by_brand[$verified_brand][] = $verified_category;
      }
      foreach($verified_accessories as $accessory_item){
         $accessory_category = trim((string)($accessory_item['category'] ?? ''));
         if($accessory_category !== '') $catalog_by_brand['Solar Store'][] = $accessory_category;
      }
      foreach($catalog_by_brand as $catalog_brand => $catalog_categories){
         $catalog_by_brand[$catalog_brand] = array_values(array_unique($catalog_categories));
         sort($catalog_by_brand[$catalog_brand]);
      }
   ?>

         <a href="home.php" class="logo">
             <span class="logo-thumb-svg" aria-hidden="true">
                  <!-- Inline solar SVG: panel + sun with subtle animation -->
                  <svg viewBox="0 0 64 64" width="56" height="56" xmlns="http://www.w3.org/2000/svg">
                     <defs>
                        <linearGradient id="g1" x1="0" x2="1">
                           <stop offset="0" stop-color="#ffd24a" />
                           <stop offset="1" stop-color="#ffb347" />
                        </linearGradient>
                     </defs>
                     <g transform="translate(2,2)">
                        <rect x="0" y="18" width="56" height="28" rx="3" fill="#22183a" />
                        <g fill="url(#g1)">
                           <rect x="4" y="22" width="9" height="22" rx="1"/>
                           <rect x="15" y="22" width="9" height="22" rx="1"/>
                           <rect x="26" y="22" width="9" height="22" rx="1"/>
                           <rect x="37" y="22" width="9" height="22" rx="1"/>
                        </g>
                        <circle class="sun-core" cx="44" cy="10" r="7" fill="url(#g1)" />
                        <g class="sun-rays" stroke="url(#g1)" stroke-width="2" stroke-linecap="round">
                           <line x1="44" y1="-2" x2="44" y2="-8"/>
                           <line x1="44" y1="26" x2="44" y2="32"/>
                           <line x1="32" y1="10" x2="26" y2="10"/>
                           <line x1="56" y1="10" x2="62" y2="10"/>
                           <line x1="36" y1="0" x2="31" y2="-4"/>
                           <line x1="52" y1="20" x2="56" y2="24"/>
                        </g>
                     </g>
                  </svg>
             </span>
             <div>
                  <span class="logo-text">Solar Store<span class="dot">.</span></span>
                  <span class="logo-sub">Complete Solar Solutions</span>
             </div>
         </a>

      <?php
         $total_wishlist_counts = 0;
         $total_cart_counts = 0;
         if(isset($conn, $user_id) && $user_id !== ''){
            $count_wishlist_items = $conn->prepare("SELECT * FROM `wishlist` WHERE user_id = ?");
            $count_wishlist_items->execute([$user_id]);
            $total_wishlist_counts = $count_wishlist_items->rowCount();

            $count_cart_items = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
            $count_cart_items->execute([$user_id]);
            $total_cart_counts = $count_cart_items->rowCount();
         }
      ?>

      <nav class="nav-wrapper">
         <div class="nav-pill">
            <div class="nav-primary">
               <a href="home.php" class="nav-item <?php if($current_page=='home.php') echo 'active'; ?>">Home</a>
               <a href="shop.php" class="nav-item <?php if($current_page=='shop.php') echo 'active'; ?>">Shop</a>
               <a href="brands.php" class="nav-item <?php if($current_page=='brands.php') echo 'active'; ?>">Brands</a>
               <a href="hv-systems.php" class="nav-item <?php if($current_page=='hv-systems.php') echo 'active'; ?>">HV Systems</a>
               <div class="nav-category">
                  <button id="category-menu-btn" class="nav-item nav-category-trigger" type="button" aria-controls="category-menu" aria-expanded="false"><span>Category</span><i class="fas fa-chevron-down category-chevron" aria-hidden="true"></i></button>
                  <div id="category-menu" class="category-mega" hidden>
                     <div class="category-mega-intro"><span>SHOP BY BRAND</span><h3>Find your system.</h3><p>Select a brand first, then choose the equipment you need.</p></div>
                     <form action="category.php" method="get" class="category-filter-form">
                        <label for="category-brand-select">Brand</label>
                        <select id="category-brand-select" name="brand">
                           <option value="">All brands</option>
                           <?php foreach(array_keys($catalog_by_brand) as $brand_name): ?>
                              <option value="<?= htmlspecialchars($brand_name, ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($brand_name, ENT_QUOTES, 'UTF-8'); ?></option>
                           <?php endforeach; ?>
                        </select>
                        <label for="category-type-select">Equipment</label>
                        <select id="category-type-select" name="category">
                           <option value="">Choose equipment</option>
                        </select>
                        <button type="submit" class="category-submit"><span>View products</span><i class="fas fa-arrow-right" aria-hidden="true"></i></button>
                        <?php if(empty($catalog_by_brand)): ?>
                           <p class="category-menu-empty">No branded products yet. Add a brand and equipment type to a product in Admin.</p>
                        <?php endif; ?>
                     </form>
                  </div>
               </div>
                <a href="calculator.php" class="nav-item <?php if($current_page=='calculator.php') echo 'active'; ?>">Calculator</a>
                <a href="assistant.php" class="nav-item <?php if($current_page=='assistant.php') echo 'active'; ?>">Solar Assistant</a>
            </div>
            <div class="nav-tools">
               <form action="search_page.php" method="post" class="nav-search-form">
                  <input type="search" name="search_box" maxlength="100" placeholder="Search solar products" aria-label="Search products" required>
                  <button type="submit" name="search_btn" aria-label="Search"><i class="fas fa-search" aria-hidden="true"></i></button>
               </form>
               <a href="wishlist.php" class="nav-item nav-action <?php if($current_page=='wishlist.php') echo 'active'; ?>" aria-label="Wishlist" title="Wishlist"><span class="icon-wrap"><i class="fas fa-heart"></i></span><span class="nav-label">Wishlist <b class="nav-count"><?= $total_wishlist_counts; ?></b></span></a>
               <a href="cart.php" class="nav-item nav-action <?php if($current_page=='cart.php') echo 'active'; ?>" aria-label="Cart" title="Cart"><span class="icon-wrap"><i class="fas fa-shopping-cart"></i></span><span class="nav-label">Cart <b class="nav-count"><?= $total_cart_counts; ?></b></span></a>
               <button id="user-btn" class="nav-item nav-account" type="button" aria-label="Open account menu" aria-controls="account-menu" aria-expanded="false" title="Account"><span class="icon-wrap"><i class="fas fa-user" aria-hidden="true"></i></span><span class="nav-label">Account</span></button>
               <?php if($is_logged_in): ?>
                  <a href="update_user.php" class="nav-item nav-auth nav-profile <?php if($current_page=='update_user.php') echo 'active'; ?>"><i class="fas fa-user-edit" aria-hidden="true"></i><span>Manage Profile</span></a>
               <?php endif; ?>
            </div>
         </div>
      </nav>

      <div class="header-right">
         <div class="icons">
            <button id="menu-btn" class="fas fa-bars" type="button" aria-label="Open navigation menu" aria-expanded="false"></button>
         </div>
      </div>

      <div class="profile account-menu" id="account-menu" aria-label="Account menu">
         <?php if($is_logged_in): ?>
         <p class="account-greeting"><?= htmlspecialchars($fetch_profile["name"], ENT_QUOTES, 'UTF-8'); ?></p>
         <a href="update_user.php" class="account-menu-link"><i class="fas fa-user-edit" aria-hidden="true"></i> Manage profile</a>
         <a href="orders.php" class="account-menu-link <?php if($current_page=='orders.php') echo 'active'; ?>"><i class="fas fa-box" aria-hidden="true"></i> Orders</a>
         <a href="about.php" class="account-menu-link <?php if($current_page=='about.php') echo 'active'; ?>"><i class="fas fa-info-circle" aria-hidden="true"></i> About</a>
         <a href="contact_us.php" class="account-menu-link <?php if($current_page=='contact_us.php') echo 'active'; ?>"><i class="fas fa-envelope" aria-hidden="true"></i> Contact Us</a>
         <button id="theme-toggle" class="account-menu-link theme-menu-button" type="button" title="Toggle theme" aria-label="Toggle theme"><i class="fas fa-sun" aria-hidden="true"></i><span>Change theme</span></button>
         <a href="components/user_logout.php" class="account-menu-link logout-link" onclick="return confirm('logout from the website?');"><i class="fas fa-sign-out-alt" aria-hidden="true"></i> Log out</a>
         <?php else: ?>
         <p class="account-greeting">Welcome to Solar Store</p>
         <a href="about.php" class="account-menu-link <?php if($current_page=='about.php') echo 'active'; ?>"><i class="fas fa-info-circle" aria-hidden="true"></i> About</a>
         <a href="contact_us.php" class="account-menu-link <?php if($current_page=='contact_us.php') echo 'active'; ?>"><i class="fas fa-envelope" aria-hidden="true"></i> Contact Us</a>
         <button id="theme-toggle" class="account-menu-link theme-menu-button" type="button" title="Toggle theme" aria-label="Toggle theme"><i class="fas fa-sun" aria-hidden="true"></i><span>Change theme</span></button>
         <div class="account-auth-actions">
            <a href="user_register.php" class="account-auth-link"><i class="fas fa-user-plus" aria-hidden="true"></i> Register</a>
            <a href="user_login.php" class="account-auth-link login-link"><i class="fas fa-sign-in-alt" aria-hidden="true"></i> Login</a>
         </div>
         <?php endif; ?>
         
         
      </div>

   </section>

</header>

<!-- modal removed: contact requests moved to contact_us.php -->
