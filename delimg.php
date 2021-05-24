<?php
header("content-type:text/html;charset=utf-8");
include_once "common.php";
$data = initPostData();
$link = $data['link'];
$token = $data['token'];

//连接数据库

$con = pdo_database();
//echo($token);
if($token){
    [$openid,$ctrl,$nickName] = pdo_check_token($con,$token);
}
//echo($ctrl);
if($openid && $ctrl == 'u'){
    $ctrl = pdo_check_imgowner($con,$openid,$link);
}
if(($ctrl == 's' || $ctrl == 'o') && strlen($link) > 4 ){
    $sql = "UPDATE `images` SET hide = 1 WHERE link = :link;";
}
else{
    $con = null;
    die("无权限");
}
$sth = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
$result = $sth->execute(array(':link' => $link));
echo("已删除");
$con=null;
//重构完成。