
//if IE
function IEVersion() {
	var userAgent = navigator.userAgent; //取得浏览器的userAgent字符串
	var isIE = userAgent.indexOf("compatible") > -1 && userAgent.indexOf("MSIE") > -1; //判断是否IE<11浏览器
	var isEdge = userAgent.indexOf("Edge") > -1 && !isIE; //判断是否IE的Edge浏览器
	var isIE11 = userAgent.indexOf('Trident') > -1 && userAgent.indexOf("rv:11.0") > -1;
	if(isIE) {
		var reIE = new RegExp("MSIE (\\d+\\.\\d+);");
		reIE.test(userAgent);
		var fIEVersion = parseFloat(RegExp["$1"]);
		if(fIEVersion == 7) {
			return 7;
		} else if(fIEVersion == 8) {
			return 8;
		} else if(fIEVersion == 9) {
			return 9;
		} else if(fIEVersion == 10) {
			return 10;
		} else {
			return 6;//IE版本<=7
		}
	} else if(isIE11) {
		return 11; //IE11
	} else if(isEdge) {
		return 12;//edge
	}else{
		return 13;//不是ie浏览器
	}
}

//if browser supports placeholder
function placeholder(e){
	
	
	var type = e.getAttribute("type");
	if (type == "hidden") {
		return;
	}
	if (type == "button") {
		var html = e.innerHTML;
		e.value = html;
		return ;
	}
	
	e.value = e.getAttribute('placeholder');
	e.style.color="gray";
	//input in IE support placeholder ,display onfocus content
	if ( IEVersion() === 10 || IEVersion() === 11 ) {
		e.type="text";
		e.onfocus = function(){
			this.value = this.getAttribute('onfocus').toString().split('\'')[1];
			this.style.color="";
			if(e.getAttribute("mark")){
				this.type="password";
			}
		};
		e.onblur = function(){
			if (this.value === this.getAttribute('onfocus').toString().split('\'')[1] || this.value===""){
				this.value = this.getAttribute('onblur').toString().split('\'')[1];
				this.style.color="gray";
				if(e.getAttribute("mark")){
					this.type="text";
				}
			}
		};
	}
	//input in IE don't support placeholder ,display placeholder/onfocus/onblur content.
	else if (IEVersion() < 10){
		// the blow line doesn't work for < IE8
		
		if (IEVersion() < 9) {
			e.outerHTML = e.outerHTML.replace('type="password"','type="text"');
		} else {
			e.setAttribute("type","text");
		}
		var focus = e.getAttribute('onfocus');
		var a = "";
		
		if (focus!=null) {
			a = focus.toString().split('\'')[1];
		}
		
		var blur = e.getAttribute('onblur');
		var b = "";
		if (blur!=null) {
			b = blur.toString().split('\'')[1];
		}
		if(e.value === "") {
			e.value = e.getAttribute('placeholder');
			e.style.color="gray";
		}
		e.onfocus = function(){
			this.value = a;
			this.style.color="";
			if(this.getAttribute("mark") === "password"){
				if(IEVersion() < 9){
					// the blow line doesn't work for < IE8
					e.outerHTML = e.outerHTML.replace('type="text"','type="password"');
				}else {
					this.setAttribute("type","password");
				}
			}
		};
		e.onblur = function(){
			if (this.value === a || this.value === ""){
				this.value = b;
				this.style.color="gray";
				if(this.getAttribute("mark") === "password"){
					if(IEVersion() < 9){
						// the blow line doesn't work for < IE8
						e.outerHTML = e.outerHTML.replace('type="text"','type="password"');
					}else {
						this.setAttribute("type","text");
					}
				}
			}
		};
	}
}

//xmlHttp
function xmlHttpRequest(){
	var xmlHttp = null;
	if (window.XMLHttpRequest) {// code for all new browsers
		xmlHttp = new XMLHttpRequest();
	}else if (window.ActiveXObject) {// code for IE5 and IE6
		xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
	}
	return xmlHttp;
}
//get file name
function getFileName(){
	var str=location.pathname;

	var strStart=str.lastIndexOf('/') + 1;
	var strEnd=str.indexOf(".jsp");
	if(strEnd == "-1"){
		strEnd = str.length;
	}
	var fileName = str.substr(strStart,strEnd-strStart);
	var search = location.search;
	if(fileName=="logout"){
		fileName = "login";
	}
	if(fileName=="authorize"){
		fileName = "login";
	}
//	if (search!=null && search.indexOf("?url")!=-1) {
//		fileName = "failed";
//	}
	return fileName;
}
//load json data by ajax
function getJsonContent(clientLanguage,flag){
	var basePath = $("#basePath").val();
	var xmlHttp = xmlHttpRequest();
	var filePath = basePath + "i18n/" + clientLanguage + ".json";
	
	if (clientLanguage == "zh") {
		filePath = basePath + "i18n/" + clientLanguage + ".json?v="+zhJson;
	} else if (clientLanguage == "en") {
		filePath = basePath + "i18n/" + clientLanguage + ".json?v="+enJson;
	}
	
	xmlHttp.onreadystatechange = function(){
		if ( xmlHttp.readyState === 4 && xmlHttp.status === 200){
			var obj = JSON.parse(xmlHttp.responseText);
			var fileName = getFileName();
			//common node in json is the shared content for all headers and footers in pages.
			var common = "common";
			for (var e in obj[common]){
				if(obj[common].hasOwnProperty(e)){
					if(typeof(obj[common][e]) === "string") {
						try{
							document.getElementById(e).innerHTML = obj[common][e];
						} catch (err) {
						}
					} else if (typeof(obj[common][e]) === "object") {
						for (var f in obj[common][e]) {
							if(obj[common][e].hasOwnProperty(f)){
								document.getElementById(e).setAttribute(f,obj[common][e][f]);
							}
						}

					}
				}
			}
			// locale configs for different pages(fileName) saves in different nodes(obj.fileName)
			for (var el in obj[fileName]){
				if(obj[fileName].hasOwnProperty(el)){
					if(typeof(obj[fileName][el]) === "string") {
						try{
							document.getElementById(el).innerHTML = obj[fileName][el];
						} catch (err) {
						}
					} else if (typeof(obj[fileName][el]) === "object") {
						for (var f in obj[fileName][el]) {
							if(obj[fileName][el].hasOwnProperty(f)){
								if (document.getElementById(el) != null) {
									document.getElementById(el).setAttribute(f,obj[fileName][el][f]);
								}
							}
						}
					}
				}

			}
			//IE placeholder
//			if ( IEVersion() < 12 ) {
//				for( var i=0 ; i < document.getElementsByTagName('input').length; i++){
//					placeholder(document.getElementsByTagName('input')[i]);
//				}
//			}

			if (document.getElementById(clientLanguage)) {
				document.getElementById(clientLanguage).selected = true;
			}
		}
		
		if(flag && IEVersion() < 12){
			JPlaceHolder.change();
		}
	};
	xmlHttp.open("get",filePath,true);
	xmlHttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	xmlHttp.send();
}

// set the language cookie
/**
 @cookieName
 @cookieValue
 @expired
 **/
function setCookie(cookieName,cookieValue,lifeTime){
	var expireTime = new Date();
	var offsetTime = expireTime.getTimezoneOffset() * 60 * 1000 ;
	expireTime.setTime( expireTime.getTime() + lifeTime + offsetTime);
	document.cookie = cookieName + "=" + encodeURIComponent(cookieValue) + ((lifeTime == null) ? "" : ";expires=" + expireTime.toString());
}
//get cookie
function getCookie(cookieName){
	if (document.cookie.length > 0)	{
		var arr = document.cookie.match(new RegExp("(^| )"+cookieName+"=([^;]*)(;|$)"));
		if (arr != null) {
			return unescape(arr[2]);
		}
	}
	return ""
}
// check if there is customized language cookie, display cookie language if yes and default language if no, then update the cookie life to 90 days in both cases.
function checkCookie() {
	var clientLanguage = getCookie('lang');
	if ( clientLanguage == null || clientLanguage === "" || ( clientLanguage !=="en" && clientLanguage !=="zh" ) ) {
		clientLanguage = "zh";
	}
	setCookie("lang",clientLanguage,90*24*60*60*1000);
	return clientLanguage;
}
//
/*function changeLang(){
	var basePath = $("#basePath").val();
	var obj = document.getElementById('selector');
	var index = obj.selectedIndex;
	var selectedLanguage = obj.options[index].value;
	setCookie("lang",selectedLanguage,90*24*60*60*1000);
	getJsonContent(selectedLanguage,true);
	
	if (IEVersion()<12) {
		$("#header-img").attr("src",basePath + "images/ucas/logo.png");
		if (selectedLanguage == 'zh') {
			$("#des-img").attr("src",basePath + "images/ucas/des.png");
		} else {
			$("#des-img").attr("src",basePath + "images/ucas/des-en.png");
		}
	} else {
		$("#header-img").attr("src",basePath + "images/ucas/logo.svg");
		if (selectedLanguage == 'zh') {
			$("#des-img").attr("src",basePath + "images/ucas/des.svg");
		} else {
			$("#des-img").attr("src",basePath + "images/ucas/des-en.svg");
		}
	}
}

//微信扫码/账号密码登录 切换
$(function(){
	$("#scanTitle").hide();
	$(".input_login").click(function() {
		scanLogin=false;
		outTimeFlag=false;
		
		$(".input_login").hide();
		$(".qrcode").show();
		
		$(".card").fadeOut(360,function(){
			$("#divQrCode").hide();
			$(".loginForm").show();
			$("#header").show();
			$("#footer").show();
			$("#footMsg").remove();
			$("#scanTitle").hide();
		});
		$(".card").fadeIn(360);
	});
	
	$(".qrcode").click(function(){
		$("#QrCodeImg").css("margin-top", "0px");
		scanLogin=true;
		interval = window.setInterval("isOutTime()", 1000);//微信扫码  开启定时器
		createTime = (new Date()).getTime();
		outTimeFlag=false;
		$(".qrcode").hide();
		$(".input_login").show();
		$(".card").fadeOut(360,function(){
			$(".loginForm").hide();
			$("#divQrCode").show();
			qrCodeShow();
			$("#footer").css("margin-top","");
			$("#header").hide();
			$("#footer").hide();
			var lang=$("#selector").val();
			$("#scanTitle").html(lang != "en" ? '微信扫码登录':'Scan to Login');
			var flag1280=is1280();
			var html='';
				html='<div>'
				html+=	'<div id="footMsg">'
				if(flag1280==true){
					html+=	lang != "en"? '<p>使用微信扫码登录需要关注“中国科学技术大学信息服务”企业号并完成认证</p>':'<p>Follow the Official Account of USTC Information Service in Wechat before you want to try "Scan to login".</p>';
				}else{
					html+=	lang != "en"? '<p>使用微信扫码登录需要关注“中国科学技术大学信息服务”企业号并完成认证</p><p><a href="javascript:void(0);" onclick="goPcHelp()" style="color: blue;">现在关注</a></p>':'<p>Follow the Official Account of USTC Information Service in Wechat before you want to try "Scan to login".</p><p><a href="javascript:void(0);" onclick="goPcHelp()" style="color: blue;">Try Now</a><p>';
				}	
				html+=	'</div>'
				html+='</div>'	
			$("#footer").before(html);
			$("#scanTitle").show();
		});
		$(".card").fadeIn(360);
	});
})
var service = "";
var basePath = "";
var uuid;
var validateInterval;
function qrCodeShow() {
	service = $("#service").val();
	basePath = $("#basePath").val();
	var language=$('#selector').val();//选择语言
	$.get(basePath + "CodeServlet?service=" + service+"&language="+language, function(data, status) {
		var arr = data.split("&&");
		//存储UUID
		uuid = arr[0];
		//显示二维码
		$("#QrCodeImg").attr("src", "data:image/png;base64," + arr[1]);
		//开始验证登录--->定时器
		validateInterval = window.setInterval("validateLogin()",1000);
	});
}

//二维码未失效
function validateLogin() {
	var lang = $("#selector").val();
	$.ajax({
			type : "GET",
			url : basePath + "LongConnectionCheckServlet",
			cache : false,
			async : false,
			data : {'uuid' : uuid,'service' : service},
			dataType : "json",
			success : function(data) {
				if (data != "" && data.isFollow != 'null') {
					var result = data;
					if (result.isFollow == 'false') {
						if (lang != "en") {
							var html = '<a href="#" style="color: blue;" onclick="goHelp()">帮助</a>';
							html += ('<p>请完成关注和认证</p></br><p> </p></br>');
						} else {
							var html = '<a href="#" style="color: blue;" onclick="goHelp()">help</a>';
							html += ('<p>Please complete your attention and certification</p></br><p> </p></br>');
						}
						$("#footMsg").empty();
						$("#footer").empty();
						$("#footMsg").html(html);
						return;
					}
					if (result.isFollow == 'true') {
						$("#footer").empty();
						if (lang != "en") {
							$("#footMsg").empty().html('<p>请在手机完成授权</p></br><p> </p></br>');
						} else {
							$("#footMsg").empty().html('<p>Please complete the authorization on your mobile phone</p></br><p> </p></br>');
						}
					}
					if (result.ticket != null && result.ticket != undefined && result.ticket != '') {
						window.clearInterval(validateInterval);// 清除定时器
						if (service != "") {
							if (service.indexOf("?") != -1) {
								window.location.href = service + '&ticket='+ result.ticket;
							} else {
								window.location.href = service + '?ticket='+ result.ticket;
							}
						} else {
							location.href = "success.jsp";
						}
						
						return;
					}
				}
			}
		});
}

function goPcHelp(){
	basePath = $("#basePath").val();
	window.location.href = basePath + 'weChatScanLoginPCHelp.jsp';
}

function is1280(){
	var url=window.location.pathname;
	return url.indexOf('1280') !=-1;
}*/
