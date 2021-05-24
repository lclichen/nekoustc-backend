<?php
header("content-type:text/html;charset=utf-8");
$id = (int)$_GET['id'];
$token = $_GET['token'];

//连接数据库
include_once "common.php";
$con = pdo_database();
if($token){
    [$openid,$ctrl,$nickName] = pdo_check_token($con,$token);
}
if($openid && $ctrl == 'u'){
    $ctrl = pdo_check_owner($con,$openid,$id);
}

if($ctrl == 'a' || $ctrl == 'o'){
    $sql = "SELECT id,name,birthday,color,health,TNR,cutdate,sch_area,uploader,adopt,adopter,sex,description,adoptdate,deathdate,vacdate,vac,uploader,a_tel,secret FROM `catsinfo` WHERE id = :id ;";
    $isA = 1;
}
elseif($ctrl == 's'){
    $sql = "SELECT * FROM `catsinfo` WHERE id = :id ;";
    $isA = 's';
}
else{
    $sql = "SELECT id,name,birthday,color,health,TNR,cutdate,sch_area,uploader,adopt,sex,description,adoptdate,deathdate,vacdate,vac FROM `catsinfo` WHERE id = :id ;";
    $isA = 0;
}

$sth = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
$sth->execute(array(':id' => $id));
if($result = $sth->fetch(PDO::FETCH_ASSOC)){
    $result['isAdmin']=$isA;
    echo json_encode($result,JSON_UNESCAPED_UNICODE);
}
$con=null;
//重构完成。