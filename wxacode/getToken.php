<?php
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
function http_post($url,$param){
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
    curl_setopt($oCurl, CURLOPT_HEADER, 1);

    // $sContent = curl_exec($oCurl);
    // $aStatus  = curl_getinfo($oCurl);

    $sContent = execCURL($oCurl);
    curl_close($oCurl);

    return($sContent);
}
function getAccessToken($appid, $appsecret) {
    //TODO: access_token 应该全局存储与更新，以下代码以写入到文件中做示例
    //TODO: 每个应用的access_token应独立存储，此处用secret作为区分应用的标识
    $path = "./.cache/$appsecret.php";
    $data = json_decode(file_get_contents($path));
    if($data->expire_time < time()) {
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appid&secret=$appsecret";
        $res = json_decode(http_get($url)['content']);
        $access_token = $res->access_token;
        if($access_token) {
            $data->expire_time = time() + 7000;
            $data->access_token = $access_token;
            file_put_contents($path, json_encode($data));
        }
    } else {
        $access_token = $data->access_token;
    }
    return $access_token;
}

include("UserInfoConfig.php");

//$token = getAccessToken($appId,$appSecret);

?>