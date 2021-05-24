<?php
header("content-type:text/html;charset=utf-8");
$name = $_GET['name'];
$token = $_GET['token'];

include_once "common.php";
$con = pdo_database();
if($token){
    [$openid,$ctrl,$nickName] = pdo_check_token($con,$token);
}
if($openid && $ctrl == 'u'){
    $ctrl = pdo_check_owner($con,$openid,$name);
}

if($ctrl == 's' || $ctrl == 'a' || $ctrl == 'o'){
    $sql = "SELECT id,name,color,birthday,health,TNR,cutdate,sch_area,uploader,adopt,adopter,sex,description,adoptdate,deathdate,vacdate,vac,uploader,a_tel,secret FROM `catsinfo` WHERE name = :name";
    $isA = 1;
}
else{
    $sql = "SELECT id,name,color,birthday,health,TNR,cutdate,sch_area,uploader,adopt,adopter,sex,description,adoptdate,deathdate,vacdate,vac,uploader FROM `catsinfo` WHERE name = :name";
    $isA = 0;
}
$sth = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
$sth->execute(array(':name' => $name));
$result = $sth->fetch(PDO::FETCH_ASSOC);
echo json_encode($result,JSON_UNESCAPED_UNICODE);
$con = null;
//重构完成。
?>
