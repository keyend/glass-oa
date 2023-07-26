<?php
namespace app\common\model\crebo;

use think\Model;

class Alipay extends Model{
	
	private $alipay_config;
	private $sign_type;
	private $keys;
	private $input_charset;
	private $partner;
 
	/**
	 *支付宝网关地址（新）
	 */
	public $alipay_gateway_new = 'https://mapi.alipay.com/gateway.do?';
	//public $alipay_gateway_new = 'https://openapi.alipay.com/gateway.do';
	
    /**
     * HTTPS形式消息验证地址
     */
	var $https_verify_url = 'https://mapi.alipay.com/gateway.do?service=notify_verify&';
	/**
     * HTTP形式消息验证地址
     */
	var $http_verify_url = 'http://notify.alipay.com/trade/notify_query.do?';
	
 

/*
	function __construct($alipay_config){
		$this->alipay_config = $alipay_config;
	}
*/	
 
    public function int($order) {
		$site_url = request()->domain(); 
		
		$return_url = $site_url.'/payment/alipay_call';
		$notify_url = $site_url.'/payment/alipay_notify';
 
		$alipay_config['partner'] =config('pay.alipay_parterid');//'2088121146057067';//                             // Your PID
		$alipay_config['key'] = config('pay.alipay_key');//'qv2a3q67n0sbvojug1wrh2qbm6sns872';                                        // Your Key
		$alipay_config['seller_id']	= $alipay_config['partner'];
		$alipay_config['notify_url'] = $notify_url;               // 支付成功回调地址。阿里后台会向该地址后发送支付订单信息，让你后台进行确认
		$alipay_config['sign_type']    = strtoupper('MD5');
		$alipay_config['input_charset']= strtolower('utf-8');
		#$alipay_config['cacert']    = getcwd().'\\cacert.pem';
		$alipay_config['transport']    = 'http';
		$alipay_config['payment_type'] = "1"; 
		$alipay_config['return_url'] = $return_url;                 // 支付成功跳转地址
		#$alipay_config['service'] = "alipay.wap.trade.create.direct";
	if(ismobile()){	
		$alipay_config['service'] = "alipay.wap.create.direct.pay.by.user";             // WAP支付方法
	}else{
		$alipay_config['service'] = "create_direct_pay_by_user";                      // PC即时到账支付方法
	}
		$alipay_config['anti_phishing_key'] = "";                                       // 防钓鱼时间戳  若要使用请调用类文件submit中的query_timestamp函数
		$alipay_config['exter_invoke_ip'] = $_SERVER["REMOTE_ADDR"];                    // 客户端的IP地址 非局域网的外网IP地址，如：221.0.0.1
		
		//订单信息生成
		$total_fee = $order['money'];								// 支付金额，以元为单位。$total_fee='100'代表100元
		$body = $order['body'];								// 商品描述
		$subject = $order['subject'];							// 商品名称，不要使用充值、支付宝等字眼，会报错。
		$out_trade_no = $order['trade_no'];						// 订单号，自己生成
		// WAP版支付才需要配置该参数
		$show_url = "http://".$_SERVER["HTTP_HOST"];	// 商品展示的超链接
		
		// 构造请求的参数数组
		$parameter = array(
		"service"       => $alipay_config['service'],
		"partner"       => $alipay_config['partner'],
		"seller_id"  => $alipay_config['seller_id'],
		"payment_type"	=> $alipay_config['payment_type'],
		"notify_url"	=> $notify_url,
		"return_url"	=> $return_url,
		"anti_phishing_key"=>$alipay_config['anti_phishing_key'],
		"exter_invoke_ip"=>$alipay_config['exter_invoke_ip'],
		"out_trade_no"	=> $out_trade_no,
		"subject"	=> $subject,
		"total_fee"	=> $total_fee,
		"show_url"	=> $show_url,
		"body"	=> $body,
		"app_pay"	=>'Y',
		"_input_charset"	=> trim(strtolower($alipay_config['input_charset']))
		);
		
		
		
    //	$this->__construct($alipay_config);
		$this->sign_type		=$alipay_config['sign_type'];
		$this->keys				=$alipay_config['key'];
		$this->input_charset	=$alipay_config['input_charset'];
		$this->partner			=$alipay_config['partner'];	
 
		return $parameter;	
    }		
 
	/**
	 * 生成签名结果
	 * @param $para_sort 已排序要签名的数组
	 * return 签名结果字符串
	 */
	public function buildRequestMysign($para_sort) {
		//把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
		$prestr = createLinkstring($para_sort);
		
		$mysign = "";
		switch (strtoupper(trim($this->sign_type))) {
			case "MD5" :
				$mysign = md5Sign($prestr, $this->keys);
				break;
			default :
				$mysign = "";
		}
		
		return $mysign;
	}

	/**
     * 生成要请求给支付宝的参数数组
     * @param $para_temp 请求前的参数数组
     * @return 要请求的参数数组
     */
	public function buildRequestPara($para_temp) {
		//除去待签名参数数组中的空值和签名参数
		$para_filter = paraFilter($para_temp);

		//对待签名参数数组排序
		$para_sort = argSort($para_filter);

		//生成签名结果
		$mysign = $this->buildRequestMysign($para_sort);
		
		//签名结果与签名方式加入请求提交参数组中
		$para_sort['sign'] = $mysign;
		
		$para_sort['sign_type'] = strtoupper(trim($this->sign_type));
		
		return $para_sort;
	}

	/**
     * 生成要请求给支付宝的参数数组
     * @param $para_temp 请求前的参数数组
     * @return 要请求的参数数组字符串
     */
	public function buildRequestParaToString($para_temp) {
		//待请求参数数组
		$para = $this->buildRequestPara($para_temp);
		
		//把参数组中所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串，并对字符串做urlencode编码
		$request_data = createLinkstringUrlencode($para);
		
		return $request_data;
	}
	
    /**
     * 建立请求，以表单HTML形式构造（默认）
     * @param $para_temp 请求参数数组
     * @param $method 提交方式。两个值可选：post、get
     * @param $button_name 确认按钮显示文字
     * @return 提交表单HTML文本
     */
	public function buildRequestForm($para_temp, $method, $button_name) {
		//待请求参数数组
		$para = $this->buildRequestPara($para_temp);
		
//echo ' input_charset:';print_r($this->keys);exit;
		
		$sHtml = "<form id='alipaysubmit' name='alipaysubmit' action='".$this->alipay_gateway_new."_input_charset=".trim(strtolower($this->input_charset))."' method='".$method."'>";
		while (list ($key, $val) = each ($para)) {
            $sHtml.= "<input type='hidden' name='".$key."' value='".$val."'/>";
        }

		//submit按钮控件请不要含有name属性
        $sHtml = $sHtml."<input type='submit'  value='".$button_name."' style='display:none;'></form>";
		$sHtml = $sHtml."<script>document.forms['alipaysubmit'].submit();</script>";
		
		return $sHtml;
	}
	
	/**
	 * 建立请求，以url形式构造（默认）
	 * @param $para_temp 请求参数数组
	 */
	public function buildRequestURL($para_temp) {
	    //待请求参数数组
	    $para = $this->buildRequestPara($para_temp);
	    $para['notify_url'] = urlencode($para['notify_url']);
	    $para['return_url'] = urlencode($para['return_url']);
	    $para['show_url'] = urlencode($para['show_url']);
	    $para['subject'] = urlencode($para['subject']);
	    $url = $this->alipay_gateway_new;
	    $k = 0;
	    foreach($para as $key => $val)
	    {
	        if($k==14) 
	            $url .= $key.'='.$val;
	        else
	            $url .= $key.'='.$val.'&';
	        $k++;
	    } 
	    return $url;
	}
	
	/**
     * 用于防钓鱼，调用接口query_timestamp来获取时间戳的处理函数
	 * 注意：该功能PHP5环境及以上支持，因此必须服务器、本地电脑中装有支持DOMDocument、SSL的PHP配置环境。建议本地调试时使用PHP开发软件
     * return 时间戳字符串
	 */
	public function query_timestamp() {
		$url = $this->alipay_gateway_new."service=query_timestamp&partner=".trim(strtolower($this->partner))."&_input_charset=".trim(strtolower($this->input_charset));
		$encrypt_key = "";		

		$doc = new DOMDocument();
		$doc->load($url);
		$itemEncrypt_key = $doc->getElementsByTagName( "encrypt_key" );
		$encrypt_key = $itemEncrypt_key->item(0)->nodeValue;
		
		return $encrypt_key;
	}
	
	

	
	
	
	
/************************  回调  ***********************************/
    public function Int_return($aconfig) {
		$site_url = request()->domain(); 
		
		$return_url = $site_url.'/payment/alipay_call';
		$notify_url = $site_url.'/payment/alipay_notify';
 
		$this->keys				= config('pay.alipay_key');
		$this->partner			= config('pay.alipay_parterid');;
		$this->sign_type		= 'MD5';
		$this->transport		= 'http';
		$this->return_url		= $return_url;
		$this->notify_url		= $notify_url;
		$this->seller_email		= '';
		$this->input_charset	= 'utf-8';
		$this->cacert			= '';
	}
	
    /**
     * 针对notify_url验证消息是否是支付宝发出的合法消息
     * @return 验证结果
     * /
	public function verifyNotify(){
		if(empty($_POST)) {//判断POST来的数组是否为空
			return false;
		}
		else {
			//生成签名结果
			$isSign = $this->getSignVeryfy($_POST, $_POST["sign"]);
			//获取支付宝远程服务器ATN结果（验证是否是支付宝发来的消息）
			$responseTxt = 'false';
			if (! empty($_POST["notify_id"])) {$responseTxt = $this->getResponse($_POST["notify_id"]);}
			
			//写日志记录
			//if ($isSign) {
			//	$isSignStr = 'true';
			//}
			//else {
			//	$isSignStr = 'false';
			//}
			//$log_text = "responseTxt=".$responseTxt."\n notify_url_log:isSign=".$isSignStr.",";
			//$log_text = $log_text.createLinkString($_POST);
			//logResult($log_text);
			
			//验证
			//$responsetTxt的结果不是true，与服务器设置问题、合作身份者ID、notify_id一分钟失效有关
			//isSign的结果不是true，与安全校验码、请求时的参数格式（如：带自定义参数等）、编码格式有关
			if (preg_match("/true$/i",$responseTxt) && $isSign) {
				return true;
			} else {
				return false;
			}
		}
	}
	
    /**
     * 针对return_url验证消息是否是支付宝发出的合法消息
     * @return 验证结果
     * /
	public function verifyReturn(){
		if(empty($_GET)) {//判断POST来的数组是否为空
			return false;
		}
		else {
			//生成签名结果
			$isSign = $this->getSignVeryfy($_GET, $_GET["sign"]);
			//获取支付宝远程服务器ATN结果（验证是否是支付宝发来的消息）
			$responseTxt = 'false';
			if (! empty($_GET["notify_id"])) {$responseTxt = $this->getResponse($_GET["notify_id"]);}
			
			//写日志记录
			//if ($isSign) {
			//	$isSignStr = 'true';
			//}
			//else {
			//	$isSignStr = 'false';
			//}
			//$log_text = "responseTxt=".$responseTxt."\n return_url_log:isSign=".$isSignStr.",";
			//$log_text = $log_text.createLinkString($_GET);
			//logResult($log_text);
			
			//验证
			//$responsetTxt的结果不是true，与服务器设置问题、合作身份者ID、notify_id一分钟失效有关
			//isSign的结果不是true，与安全校验码、请求时的参数格式（如：带自定义参数等）、编码格式有关
			if (preg_match("/true$/i",$responseTxt) && $isSign) {
				return true;
			} else {
				return false;
			}
		}
	}
	
    /**
     * 获取返回时的签名验证结果
     * @param $para_temp 通知返回来的参数数组
     * @param $sign 返回的签名结果
     * @return 签名验证结果
     * /
	public function getSignVeryfy($para_temp, $sign) {
		//除去待签名参数数组中的空值和签名参数
		$para_filter = paraFilter($para_temp);
		
		//对待签名参数数组排序
		$para_sort = argSort($para_filter);
		
		//把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
		$prestr = createLinkstring($para_sort);
		
		$isSgin = false;
		switch (strtoupper(trim($this->sign_type))) {
			case "MD5" :
				$isSgin = md5Verify($prestr, $sign, $this->keys);
				break;
			default :
				$isSgin = false;
		}
		
		return $isSgin;
	}

    /**
     * 获取远程服务器ATN结果,验证返回URL
     * @param $notify_id 通知校验ID
     * @return 服务器ATN结果
     * 验证结果集：
     * invalid命令参数不对 出现这个错误，请检测返回处理中partner和key是否为空 
     * true 返回正确信息
     * false 请检查防火墙或者是服务器阻止端口问题以及验证时间是否超过一分钟
     * /
	public function getResponse($notify_id) {
		$transport = strtolower(trim($this->transport)); 
		$partner = trim($this->partner);
		$veryfy_url = '';
		if($transport == 'https') {
			$veryfy_url = $this->https_verify_url;
		}
		else {
			$veryfy_url = $this->http_verify_url;
		}
		$veryfy_url = $veryfy_url."partner=" . $partner . "&notify_id=" . $notify_id;
		
		return $veryfy_url;
		
		$responseTxt = getHttpResponseGET($veryfy_url, $this->cacert);
		
		return $responseTxt;
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
    /**
     * 针对notify_url验证消息是否是支付宝发出的合法消息
     * @return 验证结果
     */
	function verifyNotify(){
		if(empty($_POST)) {//判断POST来的数组是否为空
			return false;
		}
		else {
			//生成签名结果
			$mysign = $this->getMysign($_POST);
			//获取支付宝远程服务器ATN结果（验证是否是支付宝发来的消息）
			$responseTxt = 'false';
			if (! empty($_POST["notify_id"])) {$responseTxt = $this->getResponse($_POST["notify_id"]);}
			
			//写日志记录
			//$log_text = "responseTxt=".$responseTxt."\n notify_url_log:sign=".$_POST["sign"]."&mysign=".$mysign.",";
			//$log_text = $log_text.createLinkString($_POST);
			//logResult($log_text);
			
			//验证
			//$responsetTxt的结果不是true，与服务器设置问题、合作身份者ID、notify_id一分钟失效有关
			//mysign与sign不等，与安全校验码、请求时的参数格式（如：带自定义参数等）、编码格式有关
			if (preg_match("/true$/i",$responseTxt) && $mysign) {
				return true;
			} else {
				return false;
			}
		}
	}
	
 
    /**
     * 针对return_url验证消息是否是支付宝发出的合法消息
     * @return 验证结果
     */
	public function verifyReturn(){
		if(empty($_GET)) {//判断POST来的数组是否为空
			return false;
		}else {
			//生成签名结果
			$mysign = $this->getMysign($_GET);
//return var_export($_GET);		
			//获取支付宝远程服务器ATN结果（验证是否是支付宝发来的消息）
			$responseTxt = 'false';
			if (! empty($_GET["notify_id"])) {$responseTxt = $this->getResponse($_GET["notify_id"]);}
			
			//写日志记录
			//$log_text = "responseTxt=".$responseTxt."\n notify_url_log:sign=".$_GET["sign"]."&mysign=".$mysign.",";
			//$log_text = $log_text.createLinkString($_GET);
			//logResult($log_text);
			
			//验证
			//$responsetTxt的结果不是true，与服务器设置问题、合作身份者ID、notify_id一分钟失效有关
			//mysign与sign不等，与安全校验码、请求时的参数格式（如：带自定义参数等）、编码格式有关
			if (preg_match("/true$/i",$responseTxt) && $mysign) {
				return true;
			} else {
				return false;
			}
		}
	}
	
    /**
     * 根据反馈回来的信息，生成签名结果
     * @param $para_temp 通知返回来的参数数组
     * @return 生成的签名结果
     */
	public function getMysign($para_temp) {
		//除去待签名参数数组中的空值和签名参数
		$para_filter = paraFilter($para_temp);
		
		//对待签名参数数组排序
		$para_sort = argSort($para_filter);
		
		//生成签名结果
		$mysign = buildMysign($para_sort, trim($this->keys), strtoupper(trim($this->sign_type)));
		
		return $mysign;
	}

    /**
     * 获取远程服务器ATN结果,验证返回URL
     * @param $notify_id 通知校验ID
     * @return 服务器ATN结果
     * 验证结果集：
     * invalid命令参数不对 出现这个错误，请检测返回处理中partner和key是否为空 
     * true 返回正确信息
     * false 请检查防火墙或者是服务器阻止端口问题以及验证时间是否超过一分钟
     */
	public function getResponse($notify_id) {
		$transport = strtolower(trim($this->transport));
		$partner = trim($this->partner);
		$veryfy_url = '';
		if($transport == 'https') {
			$veryfy_url = $this->https_verify_url;
		}
		else {
			$veryfy_url = $this->http_verify_url;
		}
		$veryfy_url = $veryfy_url."partner=" . $partner . "&notify_id=" . $notify_id;
		$responseTxt = getHttpResponse($veryfy_url);
		
		return $responseTxt;
	}	

	/**/
	
	
}






 