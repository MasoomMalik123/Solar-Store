<?php
include 'components/connect.php';
session_start();
header('Content-Type: application/json; charset=utf-8');
$user_id = $_SESSION['user_id'] ?? '';
if($user_id === ''){ http_response_code(401); echo json_encode(['ok'=>false,'redirect'=>'user_login.php']); exit; }
$payload = json_decode(file_get_contents('php://input'), true);
$items = is_array($payload['items'] ?? null) ? $payload['items'] : [];
if(!$items){ http_response_code(400); echo json_encode(['ok'=>false,'error'=>'No package items supplied.']); exit; }
$insert = $conn->prepare("INSERT INTO cart (user_id, pid, name, price, quantity, image) VALUES (?, ?, ?, ?, ?, ?)");
$added = 0;
foreach($items as $item){
   $name = trim((string)($item['name'] ?? ''));
   $price = is_numeric($item['price'] ?? null) ? max(0, (float)$item['price']) : 0;
   $qty = is_numeric($item['quantity'] ?? null) ? min(99, max(1, (int)$item['quantity'])) : 1;
   $image = trim((string)($item['image'] ?? 'images/category-inverter.jpg'));
   if($name === '') continue;
   $exists = $conn->prepare("SELECT id FROM cart WHERE user_id = ? AND name = ? LIMIT 1");
   $exists->execute([$user_id, $name]);
   if($exists->fetch()) continue;
   $insert->execute([$user_id, 0, $name, $price, $qty, $image]);
   $added++;
}
echo json_encode(['ok'=>true,'added'=>$added,'redirect'=>'cart.php']);
