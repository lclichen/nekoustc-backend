<?php
    header("content-type:text/html;charset=utf-8");
    $postdata = json_encode($_POST,JSON_UNESCAPED_UNICODE); //获得POST请求提交的数据
    
    $name = $_POST['name'];
    $imgname = $_POST['imgname']; 
    $token = $_POST['token'];
    if($name == ''){
        die("请输入猫的名字");
    }    
    //打印日志 方便查看
    $fp = fopen('.imglog.txt','a+') or die("Unable to open file!");
    $D_T = date("Y-m-d H:i:s");
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
    $sql_select = $con->prepare('SELECT id FROM catsinfo WHERE name = ?');
    $sql_select->bindParam(1,$name);
    $sql_select->execute();
    $matchid = $sql_select->fetch(PDO::FETCH_ASSOC)['id'];

    $sql_select = $con->prepare('SELECT link FROM images WHERE link = ?');
    $sql_select->bindParam(1,$imgname);
    $sql_select->execute();
    $matchlink = $sql_select->fetch(PDO::FETCH_ASSOC)['id'];
    if($matchlink){
        $con=null;
        die("同名文件已存在");
    }
    if($matchid === null){//上传图集图片时还没有档案的策略
        echo 'new';
        $hide = 0;
        $id = $con->query("SELECT MAX(id) FROM catsinfo;")->fetch(PDO::FETCH_ASSOC)['MAX(id)'] + 1;
        $sql_insert = $con->prepare('INSERT INTO catsinfo (id, name, openid, hide, sex, health, vac, TNR, sch_area ,adopt) VALUES (?, ?, ?, 0, "empty", "empty", "empty", "empty", "empty", "empty")');
        $sql_insert->bindParam(1,$id);
        $sql_insert->bindParam(2,$name);
        $sql_insert->bindParam(3,$openid);
        $sql_insert->execute();
    }
    else{
        $id=$matchid;
    }
    $hide = 0;
    $sql_insert = $con->prepare('INSERT INTO images (id,link,uploaddate,openid,likeit,hide) VALUES (?, ?, ?, ?, 0, 0)');
    $sql_insert->bindParam(1,$id);
    $sql_insert->bindParam(2,$imgname);
    $sql_insert->bindParam(3,$D_T);
    $sql_insert->bindParam(4,$openid);
    $result = $sql_insert->execute();
    if(!$result){
        echo "数据库记录失败！";
    }
    $con=null;
?>