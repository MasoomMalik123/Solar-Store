<?php
include '../components/connect.php';
session_start();
$admin_id = $_SESSION['admin_id'] ?? '';
if(!$admin_id){ header('location:index.php'); exit; }
$message = [];
$product_file = __DIR__ . '/../data/solar-products.json';
$accessory_file = __DIR__ . '/../data/solar-accessories.json';
$brand_file = __DIR__ . '/../data/solar-brands.json';
$products = json_decode(file_get_contents($product_file), true) ?: [];
$accessories = json_decode(file_get_contents($accessory_file), true) ?: [];
$brands = json_decode(file_get_contents($brand_file), true) ?: [];
$write_json = function($file, $data) use (&$message){
   $json = json_encode(array_values($data), JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
   if(file_put_contents($file, $json . PHP_EOL, LOCK_EX) === false) $message[] = 'Could not write catalog file. Check file permissions.';
};
if(isset($_POST['save_catalog_item'])){
   $kind = $_POST['kind'] === 'accessory' ? 'accessory' : 'product';
   $id = trim((string)($_POST['id'] ?? ''));
   $name = trim((string)($_POST['name'] ?? ''));
   $company = trim((string)($_POST['company'] ?? ''));
   $category = trim((string)($_POST['category'] ?? ''));
   if($id === '' || $name === '' || $company === '' || $category === '') $message[] = 'ID, name, company and category are required.';
   else {
      $row = ['id'=>$id,'name'=>$name,'company'=>$company,'category'=>$category,'details'=>trim((string)($_POST['details'] ?? '')),'price_pkr'=>($_POST['price_pkr'] ?? '') === '' ? null : (float)$_POST['price_pkr']];
      if($kind === 'product') {
         $row = array_merge($row, ['type'=>trim((string)($_POST['type'] ?? 'inverter')),'subtype'=>trim((string)($_POST['subtype'] ?? '')),'model'=>trim((string)($_POST['model'] ?? '')),'image_url'=>trim((string)($_POST['image_url'] ?? '')),'official_url'=>trim((string)($_POST['official_url'] ?? ''))]);
         $found = false; foreach($products as $i=>$old){ if(($old['id']??'') === $id){ $products[$i] = array_merge($old,$row); $found=true; break; } }
         if(!$found) $products[]=$row; $write_json($product_file,$products);
      } else {
         $row = array_merge($row, ['image'=>trim((string)($_POST['image_url'] ?? '')),'official_url'=>trim((string)($_POST['official_url'] ?? ''))]);
         $found = false; foreach($accessories as $i=>$old){ if(($old['id']??'') === $id){ $accessories[$i] = array_merge($old,$row); $found=true; break; } }
         if(!$found) $accessories[]=$row; $write_json($accessory_file,$accessories);
      }
      $message[] = 'Catalog item saved.';
   }
}
if(isset($_POST['save_brand'])){
   $brand_name=trim((string)($_POST['brand_name']??''));
   $brand_market=trim((string)($_POST['brand_market']??''));
   $brand_availability=trim((string)($_POST['brand_availability']??''));
   $brand_categories=array_values(array_filter(array_map('trim',explode(',',(string)($_POST['brand_categories']??'')))));
   $brand_url=trim((string)($_POST['brand_url']??'')); $brand_source=trim((string)($_POST['brand_source']??$brand_url));
   if($brand_name===''||$brand_url==='') $message[]='Brand name and official URL are required.';
   else { $brand_row=['name'=>$brand_name,'market'=>$brand_market,'availability'=>$brand_availability,'categories'=>$brand_categories,'official_url'=>$brand_url,'source_url'=>$brand_source]; $found=false; foreach($brands as $i=>$old){if(strcasecmp((string)($old['name']??''),$brand_name)===0){$brands[$i]=$brand_row;$found=true;break;}} if(!$found)$brands[]=$brand_row; $write_json($brand_file,$brands); $message[]='Brand directory entry saved.'; }
}
if(isset($_GET['delete_brand'])){$brand_id=(string)$_GET['delete_brand'];$brands=array_values(array_filter($brands,fn($b)=>(string)($b['name']??'')!==$brand_id));$write_json($brand_file,$brands);header('location:catalog.php');exit;}
if(isset($_GET['delete'], $_GET['kind'])){
   $id=(string)$_GET['delete']; $kind=$_GET['kind']==='accessory'?'accessory':'product';
   if($kind==='product'){$products=array_values(array_filter($products,fn($p)=>($p['id']??'')!==$id));$write_json($product_file,$products);}
   else{$accessories=array_values(array_filter($accessories,fn($p)=>($p['id']??'')!==$id));$write_json($accessory_file,$accessories);}
   header('location:catalog.php'); exit;
}
$edit_id = (string)($_GET['edit'] ?? ''); $edit_kind = ($_GET['kind'] ?? '') === 'accessory' ? 'accessory' : 'product';
$editing = null; foreach($edit_kind==='product'?$products:$accessories as $row){if(($row['id']??'')===$edit_id){$editing=$row;break;}}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Catalog manager</title><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css"><link rel="stylesheet" href="../css/admin_style.css"></head><body>
<?php include '../components/admin_header.php'; ?>
<section class="add-products"><h1 class="heading">verified catalog manager</h1><p class="empty">Manage manufacturer models, official images, source links, categories and PKR prices. Use exact model/SKU data; leave price empty when stock price is not verified.</p>
<?php foreach($message as $m): ?><div class="message"><span><?= htmlspecialchars($m,ENT_QUOTES,'UTF-8'); ?></span></div><?php endforeach; ?>
<form method="post"><input type="hidden" name="save_catalog_item" value="1"><div class="flex">
<div class="inputBox"><span>catalog type</span><select name="kind" class="box"><option value="product" <?= $edit_kind==='product'?'selected':''; ?>>Product model</option><option value="accessory" <?= $edit_kind==='accessory'?'selected':''; ?>>Accessory</option></select></div>
<div class="inputBox"><span>unique ID</span><input class="box" name="id" required value="<?= htmlspecialchars($editing['id']??'',ENT_QUOTES,'UTF-8'); ?>" placeholder="goodwe-et-lv"></div>
<div class="inputBox"><span>company / brand</span><input class="box" name="company" required value="<?= htmlspecialchars($editing['company']??'',ENT_QUOTES,'UTF-8'); ?>" placeholder="KNOX, ZIEWNIC, Huawei"></div>
<div class="inputBox"><span>product name</span><input class="box" name="name" required value="<?= htmlspecialchars($editing['name']??'',ENT_QUOTES,'UTF-8'); ?>"></div>
<div class="inputBox"><span>category</span><input class="box" name="category" required value="<?= htmlspecialchars($editing['category']??'Inverters',ENT_QUOTES,'UTF-8'); ?>" placeholder="Inverters / Batteries / Panels / HV Systems"></div>
<div class="inputBox"><span>type</span><input class="box" name="type" value="<?= htmlspecialchars($editing['type']??'inverter',ENT_QUOTES,'UTF-8'); ?>" placeholder="inverter, battery, panel, system"></div>
<div class="inputBox"><span>model / SKU</span><input class="box" name="model" value="<?= htmlspecialchars($editing['model']??'',ENT_QUOTES,'UTF-8'); ?>"></div>
<div class="inputBox"><span>PKR price (optional)</span><input class="box" type="number" min="0" name="price_pkr" value="<?= htmlspecialchars((string)($editing['price_pkr']??''),ENT_QUOTES,'UTF-8'); ?>"></div>
<div class="inputBox"><span>official image URL</span><input class="box" type="url" name="image_url" value="<?= htmlspecialchars($editing['image_url']??$editing['image']??'',ENT_QUOTES,'UTF-8'); ?>" placeholder="https://manufacturer.com/product.jpg"></div>
<div class="inputBox"><span>official product/source URL</span><input class="box" type="url" name="official_url" value="<?= htmlspecialchars($editing['official_url']??'',ENT_QUOTES,'UTF-8'); ?>"></div>
<div class="inputBox"><span>technical details</span><textarea class="box" name="details" required rows="5"><?= htmlspecialchars($editing['details']??'',ENT_QUOTES,'UTF-8'); ?></textarea></div>
</div><input type="submit" class="btn" value="save catalog item"></form></section>
<section class="show-products"><h1 class="heading">manufacturer models</h1><div class="box-container"><?php foreach(array_merge(array_map(fn($p)=>array_merge($p,['_kind'=>'product']),$products),array_map(fn($p)=>array_merge($p,['_kind'=>'accessory']),$accessories)) as $row): ?><div class="box"><img src="<?= htmlspecialchars($row['image_url']??$row['image']??'../images/category-inverter.jpg',ENT_QUOTES,'UTF-8'); ?>" alt=""><div class="name"><?= htmlspecialchars(($row['company']??'').' · '.($row['name']??''),ENT_QUOTES,'UTF-8'); ?></div><div class="details"><?= htmlspecialchars(($row['category']??'').' / '.($row['model']??''),ENT_QUOTES,'UTF-8'); ?></div><div class="price"><?= isset($row['price_pkr'])&&$row['price_pkr']!==null?'PKR '.number_format((float)$row['price_pkr']):'PKR on request'; ?></div><div class="flex-btn"><a class="option-btn" href="catalog.php?edit=<?= rawurlencode($row['id']); ?>&kind=<?= $row['_kind']; ?>">edit</a><a class="delete-btn" href="catalog.php?delete=<?= rawurlencode($row['id']); ?>&kind=<?= $row['_kind']; ?>" onclick="return confirm('Delete this catalog item?');">delete</a></div></div><?php endforeach; ?></div></section>
<section class="show-products"><h1 class="heading">Pakistan brand directory</h1><div class="box-container"><?php foreach($brands as $brand): ?><div class="box"><div class="name"><?= htmlspecialchars($brand['name'],ENT_QUOTES,'UTF-8'); ?></div><div class="details"><?= htmlspecialchars($brand['market'].' · '.$brand['availability'],ENT_QUOTES,'UTF-8'); ?></div><div class="details"><?= htmlspecialchars(implode(', ',$brand['categories']),ENT_QUOTES,'UTF-8'); ?></div><div class="flex-btn"><a class="option-btn" target="_blank" rel="noopener" href="<?= htmlspecialchars($brand['official_url'],ENT_QUOTES,'UTF-8'); ?>">official site</a><a class="delete-btn" href="catalog.php?delete_brand=<?= rawurlencode($brand['name']); ?>" onclick="return confirm('Delete this brand?');">delete</a></div></div><?php endforeach; ?></div></section>
<section class="add-products"><h1 class="heading">add or update brand</h1><form method="post"><input type="hidden" name="save_brand" value="1"><div class="flex"><div class="inputBox"><span>brand name</span><input class="box" name="brand_name" required placeholder="ZIEWNIC"></div><div class="inputBox"><span>market label</span><input class="box" name="brand_market" placeholder="Pakistan local"></div><div class="inputBox"><span>availability note</span><input class="box" name="brand_availability" placeholder="Verify current stock and warranty"></div><div class="inputBox"><span>categories, comma separated</span><input class="box" name="brand_categories" placeholder="Hybrid, High-voltage storage"></div><div class="inputBox"><span>official URL</span><input class="box" type="url" name="brand_url" required></div><div class="inputBox"><span>verification/source URL</span><input class="box" type="url" name="brand_source"></div></div><input class="btn" type="submit" value="save brand"></form></section>
<script src="../js/admin_script.js"></script></body></html>
