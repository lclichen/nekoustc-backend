<?php

class UserInfoDB
{
    private $openid;
	private $sessionKey;
    private $data;

    //保存登录用户信息
    public function __construct($data,$sessionKey,$openid)
	{
		$this->openid = $openid;
		$this->sessionKey = $sessionKey;
		$this->data = $data;
	}

    public function addUserInfo($data,$sessionKey,$openid)
    {
        //连接数据库
        include_once "UserInfoConfig.php";

        $con = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname); //连接mysql服务并选择数据库
        if ($con->connect_error) {
            die("连接错误: " . $con->connect_error); //连接失败 打印错误日志
        }
        // 修改数据库连接字符集为 utf8
        mysqli_set_charset($con, "utf8mb4");
        $matchnum = $con->query("SELECT openid FROM userinfo WHERE openid = '".$openid."';");
        $matchid = $matchnum->fetch_assoc()['openid'];
        $token = sha1($openid.$data['watermark']['timestamp']);
        if($matchid !== null){//数据库中已有该用户
            $SCondition = "UPDATE userinfo SET login_sessionkey = '".$sessionKey."' WHERE openid = '".$openid."';";
            $con->query($SCondition);
            $SCondition = "UPDATE userinfo SET login_timestamp = ".$data['watermark']['timestamp']." WHERE openid = '".$openid."';";
            $con->query($SCondition);
            $SCondition = "UPDATE userinfo SET login_token = '".$token."' WHERE openid = '".$openid."';";
            $con->query($SCondition);
    
        }
        else{//数据库中没有该用户
            $words = '"'.$data['avatarUrl'].'","'.$data['nickName'].'",'.$data['gender'].',"'.$data['province'].'","'.$data['city'].'","'.$data['country'].'","'.$data['openId'].'","'.$sessionKey.'",'.$data['watermark']['timestamp'].',"'.$token.'","u"';
            $SCondition = "INSERT INTO userinfo (avatarUrl,nickName,gender,province,city,country,openid,login_sessionkey,login_timestamp,login_token,admin) VAlUES (".$words.");";
            $result = $con->query($SCondition);
        }
        mysqli_free_result($result);
        mysqli_close($con);
        return $token;
    }
}

