<?php
header("content-type:image/jpeg");
header('Access-Control-Allow-Headers:x-requested-with,content-type');
include_once("getToken.php");

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
function getUnlimitedWxacode($token,$scene,$page,$width,$auto_color,$line_color,$is_hyaline)
{
    $msg = array(
        'scene'=>$scene,
        'page'=>$page,
        'width'=>$width,
        'auto_color'=>$auto_color,
        'line_color'=>$line_color,
        'is_hyaline'=>$is_hyaline
    );
    $url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=$token";
    
    return http_post($url,$msg)['content'];
}
$data = initPostData();

$Scene = $data['scene']; //最大32个可见字符，只支持数字，大小写英文以及部分特殊字符：!#$&'()*+,/:;=?@-._~
$Page = $data['page']; //默认主页 //必须是已经发布的小程序存在的页面（否则报错），例如 pages/index/index, 根路径前不要填加 /,不能携带参数（参数请放在scene字段里），如果不填写这个字段，默认跳主页面
$Width = $data['width']; //默认430 //二维码的宽度，单位 px，最小 280px，最大 1280px
$Auto_color = boolval($data['auto_color']); //false //自动配置线条颜色，如果颜色依然是黑色，则说明不建议配置主色调，默认 false
$Line_color = json_decode($data['line_color']); //{"r":0,"g":0,"b":0} //auto_color 为 false 时生效，使用 rgb 设置颜色 例如 {"r":"xxx","g":"xxx","b":"xxx"} 十进制表示
$Is_hyaline = boolval($data['is_hyaline']); //false //是否需要透明底色，为 true 时，生成透明底色的小程序
$filename = "./acode/".urlencode("$Page-$Scene-$Width-$Line_color");
if($Auto_color){
    $filename .= '1';
}
if($Is_hyaline){
    $filename .= '1';
}
$filename .= '.jpg';
if(is_file($filename)){
    $f = fopen($filename,"rb");
    $resp = fread($f,filesize($filename));
    fclose($f);
    //$resp = base64_decode(file_get_contents("./acode/$filename"));
}
else{
    $Token = getAccessToken($appId,$appSecret);

    $resp = getUnlimitedWxacode($Token,$Scene,$Page,$Width,$Auto_color,$Line_color,$Is_hyaline);
    
    $f = fopen($filename,"wb");
    fwrite($f,$resp);
    fclose($f);
    //file_put_contents($filename, base64_encode($resp));
}

echo $resp;
?>