<?php
header('Content-Type: application/json; charset=utf-8');
$products = json_decode(file_get_contents(__DIR__ . '/../data/solar-products.json'), true) ?: [];
$query = trim((string)($_POST['query'] ?? $_GET['q'] ?? ''));
if($query === ''){ echo json_encode(['answer'=>'Apna brand, model, load, battery size ya error code likhein. Example: “GoodWe 5kW hybrid battery compatibility” ya “Solis error settings”.','products'=>[]]); exit; }
$lower = strtolower($query);
$tokens = preg_split('/[^a-z0-9.]+/i', $lower, -1, PREG_SPLIT_NO_EMPTY);
$matches = array_values(array_filter($products, function($product) use ($lower, $tokens){
   $haystack = strtolower(implode(' ', array_map('strval', [$product['company']??'', $product['name']??'', $product['model']??'', $product['category']??'', $product['subtype']??'', $product['details']??''])));
   $strong = array_filter($tokens, fn($token) => strlen($token) > 2 && str_contains($haystack, $token));
   return count($strong) >= min(2, max(1, count($tokens)));
}));
$knowledge = [
  ['keys'=>['error','fault','alarm','code'], 'answer'=>'Error diagnosis ke liye exact model, error code, screen photo/text, battery voltage/type, grid status aur wiring/communication status share karein. Codes brand aur model ke hisaab se different hote hain; guess karke setting change na karein.'],
  ['keys'=>['battery','can','rs485','communication','compatibility'], 'answer'=>'Battery connect karne se pehle inverter ke official compatibility list, nominal voltage, maximum charge/discharge current, CAN/RS485 protocol aur firmware requirement match karein. Battery BMS protocol mismatch ho to system start na bhi ho sakta hai.'],
  ['keys'=>['grid','ongrid','on-grid','hybrid','offgrid','off-grid','setting'], 'answer'=>'Grid code, country standard, battery type, charge/discharge limits aur CT direction installer-level settings hain. Exact model aur local utility requirement ke baghair values change na karein; manufacturer manual follow karein.'],
  ['keys'=>['mppt','panel','pv','string'], 'answer'=>'PV strings banate waqt panel Voc cold-temperature limit, Vmp/MPPT range, Isc/current limit aur MPPT-wise equal module count check karein. Sirf total watts dekh kar strings configure na karein.'],
  ['keys'=>['overload','load','surge','trip'], 'answer'=>'Overload ya trip ke liye connected load, motor start-up surge, inverter continuous output, battery discharge current aur breaker/RCD rating compare karein. AC aur DC protection ko bypass na karein.']
];
$provider = strtolower((string)(getenv('AI_PROVIDER') ?: 'local'));
$openai_key = trim((string)(getenv('OPENAI_API_KEY') ?: ''));
function ask_openai($query, $matches, $key){
  $base = rtrim((string)(getenv('OPENAI_BASE_URL') ?: 'https://api.openai.com/v1'), '/');
  $model = (string)(getenv('OPENAI_MODEL') ?: 'gpt-4.1-mini');
  $context = array_map(fn($p)=>['company'=>$p['company']??'', 'model'=>$p['model']??'', 'type'=>$p['type']??'', 'details'=>$p['details']??'', 'official_url'=>$p['official_url']??''], array_slice($matches,0,8));
  $payload = json_encode([
    'model'=>$model,
    'instructions'=>'You are Solar Store technical assistant. Answer in the user language, be concise and practical. Use the supplied catalog context. Never invent model specifications, prices, error-code meanings, wiring sizes, or safety settings. For electrical work, ask for exact model, country/grid code, voltage, error code and photos when needed, and recommend a qualified installer. If catalog data is insufficient, say so and link to the official source.',
    'input'=>"Catalog context:\n".json_encode($context, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)."\n\nUser query:\n".$query
  ], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
  $ch = curl_init($base.'/responses');
  curl_setopt_array($ch,[CURLOPT_POST=>true,CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>20,CURLOPT_HTTPHEADER=>['Content-Type: application/json','Authorization: Bearer '.$key],CURLOPT_POSTFIELDS=>$payload]);
  $raw=curl_exec($ch); $status=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE); curl_close($ch);
  if($raw===false || $status<200 || $status>=300) return null;
  $data=json_decode($raw,true); if(!is_array($data)) return null;
  if(!empty($data['output_text'])) return trim((string)$data['output_text']);
  $text=''; foreach(($data['output']??[]) as $item){foreach(($item['content']??[]) as $content){if(($content['type']??'')==='output_text') $text.=(string)($content['text']??'');}}
  return trim($text) ?: null;
}
$answer = 'Mujhe is query ke liye exact product match nahi mila. Brand/model, inverter type, system voltage aur aapka exact sawal ya error code bhejein.';
foreach($knowledge as $item){ foreach($item['keys'] as $key){ if(str_contains($lower, $key)){ $answer=$item['answer']; break 2; } } }
if($matches){
  $names = array_map(fn($p)=>($p['company']??'').' — '.($p['model']??''), array_slice($matches,0,5));
  $answer .= ' Related verified catalog items: '.implode('; ', $names).'. Product detail khol kar official source aur compatibility document bhi check karein.';
}
if(in_array($provider,['openai','auto'],true) && $openai_key !== ''){
  $ai_answer=ask_openai($query,$matches,$openai_key);
  if($ai_answer) $answer=$ai_answer;
}
echo json_encode(['answer'=>$answer,'products'=>array_slice($matches,0,6),'nextQuestions'=>['Exact model kya hai?','System voltage 48 V hai ya high-voltage?','Error code ya screen message kya aa raha hai?']], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
