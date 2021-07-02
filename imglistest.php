<?php
header("content-type:text/html;charset=utf-8");

$id = 0;
//$id = (int)$_GET['id'];

//连接数据库
include_once "common.php";
$data = initPostData();
$id = (int)$data['id'];
$token = $data['token'];
$openid = 'public_openid';

$con = pdo_database();
if($token){
    [$openid,$ctrl,$nickName] = pdo_check_token($con,$token);
}

if(strlen($id)>0 && $id != 0){ #根据id筛选
    $SCondition = "SELECT link,likeit,uploaddate,openid FROM `images` WHERE id = $id AND hide = 0;";
}else{
    die();
}

$sth = $con->prepare($SCondition, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
$sth->execute($arr);

$rows = $sth->fetchAll(PDO::FETCH_ASSOC);
$len=count($rows);
if($len>5){
    $rows_val = array_rand($rows,5);
    $rows_out = [];
    for($i = 0; $i < 5; $i ++){
        $rows_out[$i] = $rows[$rows_val[$i]];
    }
}
else{
    $rows_out = $rows;
}
// var_dump($rows_out);
$len2=count($rows_out);

$outtext = '[';
$i = 0;
foreach($rows_out as $row){
    if($ctrl == "s" or $openid == $row['openid']){
        $admin = '1';
    }
    else{
        $admin = '0';
    }
    $outtext .= '{"link":"' . $row['link'] . '","likeit":"' . $row['likeit'] . '","uploaddate":"' . $row['uploaddate'] . '","admin":' . $admin . '}';
    $i++;
    if($i<$len2){
        $outtext .= ',';
    }
}
$outtext .= ']';

echo $outtext; // json_encode($rows,JSON_UNESCAPED_UNICODE);

$con=null;
?>