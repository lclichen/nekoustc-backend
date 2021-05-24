<?php
header("content-type:text/html;charset=utf-8");

$name = $sex = $health = $TNR = $keyword = $sch_area = $adopt = '';
$name = $_GET['name'];//~~~
$sex = $_GET['sex'];//empty/male/female
$health = $_GET['health'];//empty/healthy/sick/death
$TNR = $_GET['tnr'];//empty/cut/cutting
$keyword = $_GET['keyword'];//~~~
$sch_area = $_GET['area'];//empty/west/east/north/south/middle/ustciat/island/other
$adopt = $_GET['adopt'];//empty/yes/no
$color = $_GET['color'];
$tag = $_GET['tag'];

include_once "common.php";
$con = pdo_database();
$arr = array();
$SCondition = "SELECT id,name,sch_area FROM `catsinfo` WHERE ";
if(strlen($sch_area)>0 && $sch_area != 'all'){ #根据校区进行筛选
    $SCondition .= "sch_area = :sch_area AND ";
    $arr[':sch_area'] = $sch_area;
}
if(strlen($adopt)>0){ #根据领养情况进行筛选
    $SCondition .= "adopt = :adopt AND ";
    $arr[':adopt']=$adopt;
}
if(strlen($sex)>0){ #根据性别进行筛选
    $SCondition .= "sex = :sex AND ";
    $arr[':sex'] = $sex;
}
if(strlen($name)>0){ #根据名字进行筛选
    $SCondition .= "name LIKE :name AND ";
    $arr[':name'] = "%$name%";
}
if(strlen($TNR)>0){ #根据绝育情况进行筛选
    $SCondition .= "TNR = :tnr AND ";
    $arr[':tnr'] = $TNR;
}
if(strlen($health)>0){ #根据健康情况进行筛选
    $SCondition .= "health = :health AND ";
    $arr[':health'] = $health;
}
if(strlen($color)>0){ #根据健康情况进行筛选
    $SCondition .= "color = :color AND ";
    $arr[':color'] = $color;
}
if(strlen($keyword)>0){ #根据关键词进行筛选
    $SCondition .= "(name LIKE :keyword OR description LIKE :keyword ) AND ";
    $arr[':keyword'] = "%$keyword%";
}
$SCondition .= "hide = 0;";


$sth = $con->prepare($SCondition, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
$sth->execute($arr);

$row = $sth->fetch(PDO::FETCH_ASSOC);

if($row && $tag == 'wx'){
    echo '[';
        while($row){
            echo '{"text":"' . $row['name'] . '","value":' . $row['id'] . '}';
            $row = $sth->fetch(PDO::FETCH_ASSOC);
            if($row){
                echo ",";
            }
        }
    echo ']';
}
if($row && $tag != 'wx'){
    echo '{';
        while($row){
            echo '"' . $row['id'] . '":{"id":"' . $row['id'] . '","name":"' . $row['name'] . '","sch_area":"' . $row['sch_area'] . '"}';
            $row = $sth->fetch(PDO::FETCH_ASSOC);
            if($row){
                echo ",";
            }
        }
    echo '}';
}
$con = null;
?>
