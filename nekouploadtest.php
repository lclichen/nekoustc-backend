<?php
header("content-type:text/html;charset=utf-8");
$postdata = json_encode($_POST,JSON_UNESCAPED_UNICODE); //获得POST请求提交的数据

$name = $_POST['name'];
$sex = $_POST['sex'];
$birth_y = $_POST['birth_y'];
$birth_m = $_POST['birth_m'];
$health = $_POST['health'];
$deathdate = $_POST['deathdate'];
$vac = $_POST['vac'];
$vacdate = $_POST['vacdate'];
$TNR = $_POST['tnr'];
$cutdate = $_POST['cutdate'];
$adopt = $_POST['adopt'];
$sch_area = $_POST['area'];
$description = $_POST['desc'];
$secret = $_POST['secret'];
$uploader = $_POST['uploader'];
$tag = $_POST['tag'];
$adopter = $_POST['adopter'];
$adoptdate = $_POST['adoptdate'];
$a_tel = $_POST['a_tel'];
$color = $_POST['color'];
$token = $_POST['token'];

if($name == '' || $uploader == ''){
    die("请输入姓名、描述及上传者。");
}

//打印日志 方便查看
$fp = fopen('.log.txt','a+') or die("Unable to open file!");
$D_T = date("Y-m-d h:i:sa");
fwrite($fp, $D_T."\n");
fwrite($fp,$postdata."\n");
fclose($fp);

//$location = array("weidu"=>$postdata['latitude'],"jingdu"=>$postdata['longitude']);//暂时不开发本功能；准备单独整个数据库放。
//连接数据库
include_once "common.php";
$con = pdo_database();
if($token){
    [$openid,$ctrl,$nickName] = pdo_check_token($con,$token);
}
$report = "";
$sql = 'SELECT id FROM catsinfo WHERE name = :name';
$sth = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
$sth->execute(array(':name' => $name));
$result = $sth->fetch(PDO::FETCH_ASSOC);
$matchid=$result['id'];

if($matchid !== null){//匹配到id,更新模式,tag应为add
    if($tag != 'add'){//防同名重复提交。
        $con = null;
        die("请勿提交重复数据");
    }
    $id=$matchid;
}
else{//匹配不到id,新增模式,tag应为new
    if($tag != 'new'){//防同名重复提交。
        $con = null;
        die("改名请联系管理员");
    }
    $hide = 0;
    $id = $con->query("SELECT MAX(id) FROM catsinfo;")->fetch(PDO::FETCH_ASSOC)['MAX(id)'] + 1;
    $sql = 'INSERT INTO catsinfo (id, name, color, hide, openid, sex, health, vac, TNR, sch_area ,adopt) VALUES ( :id, :name, "emp", :hide, :openid, "empty", "empty", "empty", "empty", "empty", "empty")';
    $sth = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    $result = $sth->execute(array(':id' => $id,':name' => $name,':hide'=>$hide,':openid'=>$openid));
    if($result){
        $report = "已新增 $id $name 的数据";
    }
    else{
        $report =  "新增 $id $name 失败！请联系<a href='mailto:NYANUSTC@hotmail.com'>NYANUSTC@hotmail.com</a>";
    }
    //echo "test $id | $ctrl | $openid \n";
}
if($openid != '' && $ctrl == 'u'){
    $ctrl = pdo_check_owner($con,$openid,$name);
    echo "《$id $name 资讯认证成功》\n";
}
if(!in_array($ctrl,["s","a","o"],true)){
    die("无权限，请登录后重试。");
}
if($sex && $sex !='empty'){
    $sql_update = $con->prepare('UPDATE catsinfo SET sex = ? WHERE id = ?');
    $sql_update->bindParam(1,$sex);
    $sql_update->bindParam(2,$id);
    $sql_update->execute();
}
if($health && $health !='empty'){
    $sql_update = $con->prepare('UPDATE catsinfo SET health = ? WHERE id = ?');
    $sql_update->bindParam(1,$health);
    $sql_update->bindParam(2,$id);
    $sql_update->execute();
}
if($deathdate && $deathdate !=''){
    $sql_update = $con->prepare('UPDATE catsinfo SET deathdate = ? WHERE id = ?');
    $sql_update->bindParam(1,$deathdate);
    $sql_update->bindParam(2,$id);
    $sql_update->execute();
}
if($vac && $vac !='empty'){
    $sql_update = $con->prepare('UPDATE catsinfo SET vac = ? WHERE id = ?');
    $sql_update->bindParam(1,$vac);
    $sql_update->bindParam(2,$id);
    $sql_update->execute();
}
/*
if($vac && $vac !='empty' && ( $vac =='cutting' || $vac =='cut')){
    $sql_update = $con->prepare('UPDATE catsinfo SET TNR = ? WHERE id = ?');
    $sql_update->bindParam(1,$vac);
    $sql_update->bindParam(2,$id);
    $sql_update->execute();
}*/
if($vacdate && $vacdate !=''){
    $sql_update = $con->prepare('UPDATE catsinfo SET vacdate = ? WHERE id = ?');
    $sql_update->bindParam(1,$vacdate);
    $sql_update->bindParam(2,$id);
    $sql_update->execute();
}
if($TNR && $TNR !='empty'){
    $sql_update = $con->prepare('UPDATE catsinfo SET TNR = ? WHERE id = ?');
    $sql_update->bindParam(1,$TNR);
    $sql_update->bindParam(2,$id);
    $sql_update->execute();
}
if($cutdate && $cutdate !=''){
    $sql_update = $con->prepare('UPDATE catsinfo SET cutdate = ? WHERE id = ?');
    $sql_update->bindParam(1,$cutdate);
    $sql_update->bindParam(2,$id);
    $sql_update->execute();
}
if($sch_area && $sch_area !='empty'){
    $sql_update = $con->prepare('UPDATE catsinfo SET sch_area = ? WHERE id = ?');
    $sql_update->bindParam(1,$sch_area);
    $sql_update->bindParam(2,$id);
    $sql_update->execute();
}
if($adopt && $adopt !='empty'){
    $sql_update = $con->prepare('UPDATE catsinfo SET adopt = ? WHERE id = ?');
    $sql_update->bindParam(1,$adopt);
    $sql_update->bindParam(2,$id);
    $sql_update->execute();
}
if ($birth_y != "year"){
    if($birth_m != "month"){
        $birthday = $birth_y."-".$birth_m."-00";
    }
    else{
        $birthday = $birth_y."-00-00";
    }
    $sql_update = $con->prepare('UPDATE catsinfo SET birthday = ? WHERE id = ?');
    $sql_update->bindParam(1,$birthday);
    $sql_update->bindParam(2,$id);
    $sql_update->execute();
}
if($description && $description !=''){
    $sql_update = $con->prepare('UPDATE catsinfo SET description = ? WHERE id = ?');
    $sql_update->bindParam(1,$description);
    $sql_update->bindParam(2,$id);
    $sql_update->execute();
}
if($secret && $secret !=''){
    $sql_update = $con->prepare('UPDATE catsinfo SET secret = ? WHERE id = ?');
    $sql_update->bindParam(1,$secret);
    $sql_update->bindParam(2,$id);
    $sql_update->execute();
}
if($uploader && $uploader !=''){
    $sql_update = $con->prepare('UPDATE catsinfo SET uploader = ? WHERE id = ?');
    $sql_update->bindParam(1,$uploader);
    $sql_update->bindParam(2,$id);
    $sql_update->execute();
}
if($adopter && $adopter !=''){
    $sql_update = $con->prepare('UPDATE catsinfo SET adopter = ? WHERE id = ?');
    $sql_update->bindParam(1,$adopter);
    $sql_update->bindParam(2,$id);
    $sql_update->execute();
}
if($adoptdate && $adoptdate !=''){
    $sql_update = $con->prepare('UPDATE catsinfo SET adoptdate = ? WHERE id = ?');
    $sql_update->bindParam(1,$adoptdate);
    $sql_update->bindParam(2,$id);
    $sql_update->execute();
}
if($a_tel && $a_tel !=''){
    $sql_update = $con->prepare('UPDATE catsinfo SET a_tel = ? WHERE id = ?');
    $sql_update->bindParam(1,$a_tel);
    $sql_update->bindParam(2,$id);
    $sql_update->execute();
}
if($color && $color !=''){
    $sql_update = $con->prepare('UPDATE catsinfo SET color = ? WHERE id = ?');
    $sql_update->bindParam(1,$color);
    $sql_update->bindParam(2,$id);
    $sql_update->execute();
}
if($openid && $openid != ''){
    $sql_update = $con->prepare('UPDATE catsinfo SET openid = ? WHERE id = ? AND openid IS NULL');
    $sql_update->bindParam(1,$openid);
    $sql_update->bindParam(2,$id);
    $sql_update->execute();
}
    
if($report == ""){
    $report = "已更新 $id $name 的数据";
}
sc_send("通知消息-USTCAT",$nickName.' '.$report, $IsJson = true);
echo $report;
$con=null;
//重构完成。