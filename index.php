<?php
session_start();
// == نسخه نهایی فروشگاه موبایل حرفه‌ای با هوش مصنوعی ساده ==
if(empty($_SESSION['setup'])){
$_SESSION['DATA'] = [
'users'=>[['id'=>1,'name'=>'admin','password'=>password_hash('admin123',PASSWORD_DEFAULT),'role'=>'admin','banned'=>0]],
'products'=>[
['id'=>1,'title'=>'گوشی سامسونگ','price'=>3500000,'description'=>'گوشی جدید سامسونگ','image'=>'https://via.placeholder.com/150','tabs'=>[['title'=>'گارد موبایل','content'=>'گارد محافظ اصلی'],['title'=>'ایرپاد','content'=>'پشتیبانی ایرپاد']]],
['id'=>2,'title'=>'گلس نانو','price'=>120000,'description'=>'محافظ صفحه','image'=>'https://via.placeholder.com/150','tabs'=>[]]
],
'orders'=>[],
'about'=>['address'=>'سنندج، کردستان','phone'=>'09182433507']
];
$_SESSION['setup']=1;
}
$DATA = $_SESSION['DATA'];
$err='';
$action = $_GET['action']??'home';
function findId($arr,$id){ foreach($arr as $i=>$v) if($v['id']==$id) return [$i,$v]; return [null,null]; }
function save(){ global $DATA; $_SESSION['DATA']=$DATA; }
// ثبت نام
if($action=='register' && $_SERVER['REQUEST_METHOD']=='POST'){
$name=trim($_POST['name']??''); $pass=$_POST['password']??'';
if($name && $pass){ foreach($DATA['users'] as $u) if($u['name']==$name) $err='نام کاربری موجود است';
if(!$err){ $id=(count($DATA['users'])?end($DATA['users'])['id']:0)+1;
$DATA['users'][]=['id'=>$id,'name'=>$name,'password'=>password_hash($pass,PASSWORD_DEFAULT),'role'=>'user','banned'=>0];
save(); $_SESSION['user']=end($DATA['users']); header('Location: ?'); exit; }} else $err='همه فیلدها لازم است';
}
// ورود
if($action=='login' && $_SERVER['REQUEST_METHOD']=='POST'){
$name=$_POST['name']??''; $pass=$_POST['password']??'';
foreach($DATA['users'] as $u){ if($u['name']==$name && password_verify($pass,$u['password'])){
if($u['banned']) $err='اکانت شما بن شده'; else {$_SESSION['user']=$u; header('Location: ?'); exit;}}
}
$err='نام یا رمز اشتباه است';
}
// خروج
if($action=='logout'){ session_destroy(); header('Location: ?'); exit; }
// افزودن به سبد
if($action=='add_cart'){ $pid=intval($_POST['pid']??0); if($pid) $_SESSION['cart'][]=$pid; header('Location: ?'); exit; }
// ثبت سفارش
if($action=='checkout' && $_SERVER['REQUEST_METHOD']=='POST'){
if(empty($_SESSION['user']) || empty($_SESSION['cart'])) header('Location: ?');
$items=[]; $total=0;
foreach($_SESSION['cart'] as $pid){ list(,$p)=findId($DATA['products'],$pid); if($p){ $items[]=$p; $total+=$p['price'];}}
$delivery=100000;
$orderId=(count($DATA['orders'])?end($DATA['orders'])['id']:0)+1;
$info=[
'address'=>$_POST['address']??'','plaque'=>$_POST['plaque']??'','postal'=>$_POST['postal']??'',
'card_last4'=>$_POST['card_last4']??'','card_name'=>$_POST['card_name']??'','mobile'=>$_POST['mobile']??''
];
$DATA['orders'][]=['id'=>$orderId,'user_id'=>$_SESSION['user']['id'],'items'=>$items,'total'=>$total,'delivery'=>$delivery,'info'=>$info,'status'=>'pending','created_at'=>date('c')];
save(); unset($_SESSION['cart']);
echo "<div class='card'><h3>سفارش ثبت شد</h3><p>سفارش شما #$orderId ثبت شد.</p><p>پرداخت به شماره کارت: 6037-9917-0000-0000</p><a href='?'>بازگشت به فروشگاه</a></div>"; exit;
}
// ادمین
if(!empty($_SESSION['user']) && $_SESSION['user']['role']=='admin'){
if($action=='admin_add' && $_SERVER['REQUEST_METHOD']=='POST'){
$title=$_POST['title']??''; $price=intval($_POST['price']??0); $desc=$_POST['description']??''; $img=$_POST['image']??'';
$tabs_json=$_POST['tabs']??'[]'; $tabs=@json_decode($tabs_json,true); if(!$tabs) $tabs=[];
if($title && $price){ $id=(count($DATA['products'])?end($DATA['products'])['id']:0)+1;
$DATA['products'][]=['id'=>$id,'title'=>$title,'price'=>$price,'description'=>$desc,'image'=>$img,'tabs'=>$tabs]; save(); header('Location: ?action=admin'); exit;}
}
if($action=='admin_ban'){ $uid=intval($_GET['uid']); list($i,$u)=findId($DATA['users'],$uid); if($u){ $DATA['users'][$i]['banned']=$DATA['users'][$i]['banned']?0:1; save(); } header('Location: ?action=admin'); exit; }
if($action=='admin_about' && $_SERVER['REQUEST_METHOD']=='POST'){
$DATA['about']['address']=$_POST['address']??$DATA['about']['address'];
$DATA['about']['phone']=$_POST['phone']??$DATA['about']['phone'];
save(); header('Location: ?action=admin'); exit;
}
}
// جزئیات محصول
if($action=='product'){ $pid=intval($_GET['pid']); list(,$product)=findId($DATA['products'],$pid); if(!$product) {echo "<div class='card'>محصول یافت نشد</div>"; exit;} }
// هوش مصنوعی ساده
$ai_answer='';
if($action=='ai' && $_SERVER['REQUEST_METHOD']=='POST'){
$q=strtolower($_POST['question']??'');
$ai_answer='متاسفم، پاسخی برای این سوال ندارم.';
foreach($DATA['products'] as $p){
if(strpos(strtolower($p['title']),$q)!==false){ $ai_answer='شاید این محصول برای شما مناسب باشد: '.$p['title']; break; }
}
}
?>
<!doctype html>
</div></body></html>