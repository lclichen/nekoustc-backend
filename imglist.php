<?php
header("content-type:text/html;charset=utf-8");

$id = 0;
//$id = (int)$_GET['id'];

//连接数据库
include_once "common.php";
$data = initPostData();
$id = (int)$data['id'];
$token = $data['token'];

$con = pdo_database();

if(strlen($id)>0 && $id != 0){ #根据id筛选
    $SCondition = "SELECT link,likeit,uploaddate FROM `images` WHERE id = $id AND hide = 0;";
}else{
    die();
}
//1-随机一张
//2-最近一张
//3-高赞一张

//$result = $con->query($SCondition) or die(mysqli_error($con));
//$rows = $result->fetch_all(1);
$sth = $con->prepare($SCondition, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
$sth->execute($arr);

$rows = $sth->fetchAll(PDO::FETCH_ASSOC);
$len=count($rows);
$ran=rand(0,2);
if($len>3){
    $row1 = $rows[array_rand($rows)];
    $i=0;
    while(($row2 == null || $row2['link']==$row1['link'] )&& ($len-1-$ran-$i>=0)){
        $row2 = $rows[(string)($len-1-$ran-$i)];
        $i+=1;
    }
    array_multisort(array_column($rows,'likeit'),SORT_DESC,$rows); 
    $i=0;
    while(($row3 == null || $row3['link']==$row1['link'] || $row3['link']==$row2['link'] ) && ($ran+$i<=$len-1)){
        $row3 = $rows[(string)($ran+$i)];
        $i+=1;
    }
    $t1 = '","likeit":"';
    $t2 = '","uploaddate":"';
    echo '[{"link":"' . $row1['link'] . $t1 . $row1['likeit'] . $t2 . $row1['uploaddate'] . '"},
    {"link":"' . $row2['link'] . $t1 . $row2['likeit'] . $t2 . $row2['uploaddate'] . '"},
    {"link":"' . $row3['link'] . $t1 . $row3['likeit'] . $t2 . $row3['uploaddate'] . '"}]';
}
else{
    echo json_encode($rows,JSON_UNESCAPED_UNICODE);
}


mysqli_free_result($result);
mysqli_close($con);
?>