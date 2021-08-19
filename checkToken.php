<?php
header("content-type:text/html;charset=utf-8");
$postdata = json_encode($_POST,JSON_UNESCAPED_UNICODE); //获得POST请求提交的数据

include_once "common.php";
$data = initPostData();
$token = $data['token'];

$con = pdo_database();
//echo($token);
if($token){
    $sql = 'SELECT openid FROM `userinfo` WHERE login_token = :token';
    $sth = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    $sth->execute(array(':token' => $token));
    $redata = $sth->fetch(PDO::FETCH_ASSOC);
    $openid = $redata['openid'];
}
else{
    die('0');
}
//echo($ctrl);
if($openid){
    echo '1';
}
else{
    echo '0';
}
$con = null;
