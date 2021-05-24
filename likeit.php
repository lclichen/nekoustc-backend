<?php
header("content-type:text/html;charset=utf-8");
    $link = $_GET['link']; 
    if($link == ''){
        return;
    }
    //连接数据库
    include_once "common.php";
    $con = pdo_database();
    $sql = "SELECT likeit FROM images WHERE link = :link";
    $sth = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    $sth->execute(array(':link' => $link));
    $result = $sth->fetch(PDO::FETCH_ASSOC);
    $likenum = $result['likeit'];
    
    if($likenum === NULL){
        $con=null;
        return;
    }
    $likenum += 1;
    $sql = "UPDATE images SET likeit = :likenum WHERE link = :link";
    $sth = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    $sth->execute(array(':likenum' => $likenum,':link'=>$link));
    echo $likenum;
    $con = null;
?>