<?php
function initPostData(){
    $data = array();
    if(!empty($_GET)){
        $data = array_merge($data,$_GET);
        //print_r("GET-".$data);
        return $data;
    }
    if(!empty($_POST) && $_SERVER["CONTENT_TYPE"]!='application/json'){
        $data = array_merge($data,$_POST);
        //print_r("POST-".$_SERVER["CONTENT_TYPE"].$data);
        return $data;
    }
    $content = file_get_contents('php://input');
    //print_r($content);
    $data = array_merge($data,json_decode($content, true));
    if(empty($data)){
        die("Empty!");
    }
    return $data;
}

function con_database(){
    include_once "config.php";
    //连接数据库
    $con = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);//连接mysql服务并选择数据库

    if ($con->connect_error) {
        die("连接错误: " . $con->connect_error);//连接失败 打印错误日志
    }
    // 修改数据库连接字符集为 utf8
    mysqli_set_charset($con,"utf8mb4");
    return $con;
}
function pdo_database(){
    include_once "config.php";
    $dsn="$dbms:host=$dbhost;dbname=$dbname;charset=utf8;";
    $dbh = new PDO($dsn, $dbuser, $dbpass); //初始化一个PDO对象
    //,array(PDO::ATTR_PERSISTENT => true)
    return $dbh;
}
function execCURL($ch){
    $response = curl_exec($ch);
    $error    = curl_error($ch);
    $result   = array( 'header' => '', 
                     'content' => '', 
                     'curl_error' => '', 
                     'http_code' => '',
                     'last_url' => '');
    
    if ($error != ""){
        $result['curl_error'] = $error;
        return $result;
    }

    $header_size = curl_getinfo($ch,CURLINFO_HEADER_SIZE);
    $result['header'] = str_replace(array("\r\n", "\r", "\n"), "<br/>", substr($response, 0, $header_size));
    $result['content'] = substr( $response, $header_size );
    $result['http_code'] = curl_getinfo($ch,CURLINFO_HTTP_CODE);
    $result['last_url'] = curl_getinfo($ch,CURLINFO_EFFECTIVE_URL);
    $result["base_resp"] = array();
    $result["base_resp"]["ret"] = $result['http_code'] == 200 ? 0 : $result['http_code'];
    $result["base_resp"]["err_msg"] = $result['http_code'] == 200 ? "ok" : $result["curl_error"];

    return $result;
}
function http_get($url){
    $oCurl = curl_init();
    if(stripos($url,"https://")!==FALSE){
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
    }
    curl_setopt($oCurl, CURLOPT_URL, $url);
    curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
    curl_setopt($oCurl, CURLOPT_VERBOSE, 1);
    curl_setopt($oCurl, CURLOPT_HEADER, 1);

    // $sContent = curl_exec($oCurl);
    // $aStatus = curl_getinfo($oCurl);
    $sContent = execCURL($oCurl);
    curl_close($oCurl);

    return $sContent;
}
function http_post($url,$param,$IsJson = false){
    $oCurl = curl_init();

    if(stripos($url,"https://")!==FALSE){
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
    }
    $strPOST = json_encode($param);
    
    curl_setopt($oCurl, CURLOPT_URL, $url);
    curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
    curl_setopt($oCurl, CURLOPT_POST,true);
    curl_setopt($oCurl, CURLOPT_POSTFIELDS,$strPOST);
    curl_setopt($oCurl, CURLOPT_VERBOSE, 1);
    if($IsJson == true){
        $header = array(
            "Content-Type: application/json",
            "Content-Length: " . strlen($strPOST) . "",
            "Accept: application/json"
        );
    }
    else{
        $header = null;
    }
    if ( !empty($header) ) {
        curl_setopt($oCurl, CURLOPT_HTTPHEADER, $header);
    }
    curl_setopt($oCurl, CURLOPT_HEADER, 1);

    // $sContent = curl_exec($oCurl);
    // $aStatus  = curl_getinfo($oCurl);

    $sContent = execCURL($oCurl);
    curl_close($oCurl);

    return($sContent);
}
function sc_send( $text , $desp = '',$IsJson = false)
{
    include_once "config.php";
    $content = array(
        'title'=>$text,
        'content'=>$desp,
        'key'=>$weixin_key,
        'to'=>"@all"
    );
    return $result = http_post('https://send.4c43.work/sendMsg.php', $content, $IsJson);
}
function check_token($con,$token){
    $sql_select = $con->prepare("SELECT openid,nickName,admin FROM `userinfo` WHERE login_token = ?");
    $sql_select->bind_param("s",$token);
    $sql_select->execute();
    $sql_select->bind_result($openid,$nickname,$ctrl);
    $sql_select->fetch();
    $sql_select->close();

    return [$openid,$ctrl,$nickname];
}
function pdo_check_token($con,$token){
    $sql = 'SELECT openid,admin,nickName FROM `userinfo` WHERE login_token = :token';
    $sth = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    $sth->execute(array(':token' => $token));
    $result = $sth->fetch(PDO::FETCH_ASSOC);
    return [$result['openid'],$result['admin'],$result['nickName']];
}
function check_owner($con,$openid,$key){
    if(is_int($key)){
        $sql_select = $con->prepare("SELECT openid FROM `catsinfo` WHERE id = ?");
        $sql_select->bind_param("i",$key);
    }
    else{
        $sql_select = $con->prepare("SELECT openid FROM `catsinfo` WHERE name = ?");
        $sql_select->bind_param("s",$key);
    }
    $sql_select->execute();
    $sql_select->bind_result($openid_in_table);
    $sql_select->fetch();
    $sql_select->close();
    if($openid == $openid_in_table){
        return 'o';
    }
    else{
        return 'u';
    }
}
function pdo_check_owner($con,$openid,$key){
    if(is_int($key)){
        $sql = "SELECT openid FROM `catsinfo` WHERE id = :key";
    }
    else{
        $sql = "SELECT openid FROM `catsinfo` WHERE name = :key";
    }
    $sth = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    $sth->execute(array(':key' => $key));
    $result = $sth->fetch(PDO::FETCH_ASSOC);
    //echo $result['openid'];
    if($openid == $result['openid'] ){
        return 'o';
    }
    else{
        return 'u';
    }
}
function pdo_check_imgowner($con,$openid,$key){
    $sql = "SELECT openid FROM `images` WHERE link = :key";
    $sth = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    $sth->execute(array(':key' => $key));
    $result = $sth->fetch(PDO::FETCH_ASSOC);
    if($openid == $result['openid'] ){
        return 'o';
    }
    else{
        return 'u';
    }
}