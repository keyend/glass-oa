<?php
namespace app\common\model\crebo;
use think\Model;

class Wxpay extends Model{
 
    public $makesign = '';	// Your API支付的签名(在商户平台API安全按钮中获取)
    public $parameters=NULL;
    public $error = 0;
    public $orderid = null;
    public $openid = '';
	public $curl_timeout = 30;//设置curl超时时间
	public $data;//接收到的数据，类型为关联数组
	public $returnParameters;//返回参数，类型为关联数组
    /**
     * 用户注册方法
     * @param $username
     */
    //进行微信支付 
	//	skpan.apcms.cn/payment/wxpay
    public function wxpay($order){
 		$site_url = request()->domain(); 
        $trade_no =$order['trade_no'];// $this->randomkeys(6);  //生成随机数 以后可以当做 订单号
        $pays =$order['money'];                        //获取需要支付的价格·
        #插入语句书写的地方
		$order['trade_type']='JSAPI';
        $conf = $this->payconfig($order['trade_type'],$trade_no,$pays * 100,$order['subject']);

		
		
        if (!$conf || $conf['result_code'] == 'FAIL') exit("<script>alert('对不起，微信支付接口调用错误!" . $conf['err_code_des'] . "');history.go(-1);</script>");
		$this->orderid = $conf['prepay_id'];
        //微信相关配置如果不正的话，进入支付页面会出现错误信息
		
		
		
	   //生成页面调用参数
        $jsApiObj["appId"] = $conf['appid'];
        $timeStamp = time();
        $jsApiObj["timeStamp"] = "$timeStamp";
        $jsApiObj["nonceStr"] = $this->createNoncestr();
        $jsApiObj["package"] = "prepay_id=" . $conf['prepay_id'];
        $jsApiObj["signType"] = "MD5";
        $jsApiObj["paySign"] = $this->MakeSign($jsApiObj);
		
		if($order['trade_type']=='MWEB'){
		
		echo $conf['mweb_url'];	
			
		#echo'<pre>'; print_r($conf);exit;
		redirect($conf['mweb_url']);exit;
			
		}elseif($order['trade_type']=='JSAPI'){
        	$json = json_encode($jsApiObj);
               $jsApiObj['html'] = $order['trade_type'].'
<script type="text/javascript">
	/*调用微信JS api 支付*/
	function jsApiCall(){
		WeixinJSBridge.invoke(
			\'getBrandWCPayRequest\',
			' . $json . ',
			function(res){
			    if (res.err_msg == "get_brand_wcpay_request:ok") {
			        /*付款成功*/
					alert("付款成功");
					window.location.href = "' . $site_url . '";
 						
			    } else if (res.err_msg == "get_brand_wcpay_request:cancel") {
			        alert("付款取消");
			    } else {
			        alert(res.err_msg);
			    }
			}
		);
	}
	function callpay(){
 
		if (typeof WeixinJSBridge == "undefined"){
		    if( document.addEventListener ){
		        document.addEventListener(\'WeixinJSBridgeReady\', jsApiCall, false);
		    }else if (document.attachEvent){
		        document.attachEvent(\'WeixinJSBridgeReady\', jsApiCall); 
		        document.attachEvent(\'onWeixinJSBridgeReady\', jsApiCall);
		    }
		}else{
		    jsApiCall();
		}
	
	}
	</script>
	
	<div align="center">
		<button class="pay_button" style="width:210px; height:50px; border-radius: 15px;background-color:#FE6714; border:0px #FE6714 solid; cursor: pointer;  color:white;  font-size:16px;" type="button" onclick="callpay()" >a立即支付</button>
	</div>


	<!--div align="center" class="pay_info api-wrap">
		<p style="line-height:30px; font-size:18px;">支付单号： </p>
        <p style="margin-top:20%;line-height:40px; font-size:18px;">支付金额： </p>
		<p class="pay_button"><button class="fc-weixin-pay api-button api-button-large api-danger" style=" width:180px;margin:5px auto;" type="button" onclick="callpay()" >立即支付</button></p>
	</div-->
';
 	
		}
		return $jsApiObj;
	//return view('Pay',['parameters' => $json]);
    }


    //订单管理
    #微信JS支付参数获取#
    public function payconfig($trade_type,$no, $fee, $body){
		$site_url = request()->domain(); 
        $url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
        $data['appid'] = config('pay.weixin_appid');
        $data['mch_id'] = config('pay.weixin_mchid');//商户号
        $data['device_info'] = 'WEB';
        $data['body'] = $body;
        $data['out_trade_no'] = $no;                           //订单号
        $data['total_fee'] = $fee;                             //金额
        $data['spbill_create_ip'] = $_SERVER["REMOTE_ADDR"];   //ip地址
        $data['notify_url'] = $site_url.'/payment/weixin_notify'; //配置回调地址(给pays中转文件上传到根目录下面)
        $data['trade_type'] =$trade_type;
		
		$data['scene_info'] ='{"h5_info": {"type":"Wap","wap_url": "'.$site_url.'","wap_name": "会员充值"}}';
		if($trade_type=='JSAPI'){
        $data['openid'] =$this->GetOpenid(); // $_SESSION['openid'];                 //获取保存用户的openid
		}
        $data['nonce_str'] = $this->createNoncestr();
        $data['sign'] = $this->MakeSign($data);
//echo'<pre>'; print_r($data);exit;	
		
        $xml = $this->ToXml($data);
        $curl = curl_init(); // 启动一个CURL会话
		
        curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
		
        //设置header
        curl_setopt($curl, CURLOPT_HEADER, FALSE);
		
        //要求结果为字符串且输出到屏幕上
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_POST, TRUE); // 发送一个常规的Post请求
        curl_setopt($curl, CURLOPT_POSTFIELDS, $xml); // Post提交的数据包
        curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
        $tmpInfo = curl_exec($curl); // 执行操作
        curl_close($curl); // 关闭CURL会话
        $arr = $this->FromXml($tmpInfo);
        return $arr;
    }

    /**
     *    作用：产生随机字符串，不长于32位
     */
    public function createNoncestr($length = 32){
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    /**
     *    作用：产生随机字符串，不长于32位
     */
    public function randomkeys($length){
        $pattern = '1234567890123456789012345678905678901234';
        $key = null;
        for ($i = 0; $i < $length; $i++) {
            $key .= $pattern{mt_rand(0, 30)};    //生成php随机数
        }
        return $key;
    }

    /**
     * 将xml转为array
     * @param string $xml
     * @throws WxPayException
     */
    public function FromXml($xml){
        //将XML转为array
        return json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    }

    /**
     * 输出xml字符
     * @throws WxPayException
     **/
    public function ToXml($arr){
        $xml = "<xml>";
        foreach ($arr as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else {
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
            }
        }
        $xml .= "</xml>";
        return $xml;
    }

    /**
     * 生成签名
     * @return 签名，本函数不覆盖sign成员变量，如要设置签名需要调用SetSign方法赋值
     */
    public function MakeSign($arr){
        //签名步骤一：按字典序排序参数
        ksort($arr);
        $string = $this->ToUrlParams($arr);
        //签名步骤二：在string后加入KEY
        $string = $string . "&key=" . config('pay.weixin_key');
        //签名步骤三：MD5加密
        $string = md5($string);
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);
        return $result;
    }

    /**
     * 格式化参数格式化成url参数
     */
    public function ToUrlParams($arr){
        $buff = "";
        foreach ($arr as $k => $v) {
            if ($k != "sign" && $v != "" && !is_array($v)) {
                $buff .= $k . "=" . $v . "&";
            }
        }

        $buff = trim($buff, "&");
        return $buff;
    }
	
	
	
	
	
	
	
	
	
	
	
	
 
	
	/**
	 * 
	 * 通过跳转获取用户的openid，跳转流程如下：
	 * 1、设置自己需要调回的url及其其他参数，跳转到微信服务器https://open.weixin.qq.com/connect/oauth2/authorize
	 * 2、微信服务处理完成之后会跳转回用户redirect_uri地址，此时会带上一些参数，如：code
	 * 
	 * @return 用户的openid
	 */
	public function GetOpenid(){
		//通过code获得openid
		if (!isset($_GET['code'])){
			//触发微信返回code码
			$baseUrl = urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].$_SERVER['QUERY_STRING']);
			$url = $this->_CreateOauthUrlForCode($baseUrl);
			Header("Location: $url");
			exit();
		} else {
			//获取code码，以获取openid
		    $code = $_GET['code'];
			$openid = $this->getOpenidFromMp($code);
			return $openid;
		}
	}
	
	/**
	 * 
	 * 构造获取code的url连接
	 * @param string $redirectUrl 微信服务器回跳的url，需要url编码
	 * 
	 * @return 返回构造好的url
	 */
	public function _CreateOauthUrlForCode($redirectUrl){
 
		$urlObj["appid"] = config('pay.weixin_appid');
		$urlObj["redirect_uri"] = "$redirectUrl";
		$urlObj["response_type"] = "code";
		$urlObj["scope"] = "snsapi_base";
		$urlObj["state"] = "STATE"."#wechat_redirect";
		$bizString = $this->ToUrlParams($urlObj);
		return "https://open.weixin.qq.com/connect/oauth2/authorize?".$bizString;
	}
	/**
	 * 
	 * 通过code从工作平台获取openid机器access_token
	 * @param string $code 微信跳转回来带上的code
	 * 
	 * @return openid
	 */
	public function GetOpenidFromMp($code){
		$url = $this->__CreateOauthUrlForOpenid($code);

		//初始化curl
		$ch = curl_init();
		$curlVersion = curl_version();
 
		$ua = "WXPaySDK/3.0.9 (".PHP_OS.") PHP/".PHP_VERSION." CURL/".$curlVersion['version']." ".config('pay.weixin_mchid');

		//设置超时
		curl_setopt($ch, CURLOPT_TIMEOUT, $this->curl_timeout);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,FALSE);
		curl_setopt($ch, CURLOPT_USERAGENT, $ua);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

		$proxyHost = "0.0.0.0";
		$proxyPort = 0;
		$this->GetProxy($proxyHost, $proxyPort);
		if($proxyHost != "0.0.0.0" && $proxyPort != 0){
			curl_setopt($ch,CURLOPT_PROXY, $proxyHost);
			curl_setopt($ch,CURLOPT_PROXYPORT, $proxyPort);
		}
		//运行curl，结果以jason形式返回
		$res = curl_exec($ch);
		curl_close($ch);
		//取出openid
		$data = json_decode($res,true);
		$this->data = $data;
		$openid = $data['openid'];
		return $openid;
	}
	//=======【curl代理设置】===================================
	/**
	 * TODO：这里设置代理机器，只有需要代理的时候才设置，不需要代理，请设置为0.0.0.0和0
	 * 本例程通过curl使用HTTP POST方法，此处可修改代理服务器，
	 * 默认CURL_PROXY_HOST=0.0.0.0和CURL_PROXY_PORT=0，此时不开启代理（如有需要才设置）
	 * @var unknown_type
	 */
	public function GetProxy(&$proxyHost, &$proxyPort)
	{
		$proxyHost = "0.0.0.0";
		$proxyPort = 0;
	}
	/**
	 * 
	 * 构造获取open和access_toke的url地址
	 * @param string $code，微信跳转带回的code
	 * 
	 * @return 请求的url
	 */
	public function __CreateOauthUrlForOpenid($code){
 
		$urlObj["appid"] = config('pay.weixin_appid');
		$urlObj["secret"] = config('pay.weixin_appsecret');
		$urlObj["code"] = $code;
		$urlObj["grant_type"] = "authorization_code";
		$bizString = $this->ToUrlParams($urlObj);
		return "https://api.weixin.qq.com/sns/oauth2/access_token?".$bizString;
	}
//=======【微信回调函数】===================================
	/**
	 * 将微信的请求xml转换成关联数组，以方便数据处理
	 */
	public function saveData($xml){
		$this->data = $this->xmlToArray($xml);
		return $this->data;
	}
	function checkSign(){
		$tmpData = $this->data;
		unset($tmpData['sign']);
		$sign = $this->getSign($tmpData);//本地签名
		if ($this->data['sign'] == $sign) {
			return TRUE;
		}
		return FALSE;
	}
	/**
	 * 	作用：将xml转为array
	 */
	public function xmlToArray($xml){		
        //将XML转为array        
        $array_data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);		
		return $array_data;
	}
	/**
	 * 生成接口参数xml
	 */
	function createXml(){
		return $this->arrayToXml($this->returnParameters);
	}
	/**
	 * 将xml数据返回微信
	 */
	function returnXml(){
		$returnXml = $this->createXml();
		return $returnXml;
	}
	/**
	 * 	作用：生成签名
	 */
	public function getSign($Obj){
		foreach ($Obj as $k => $v){
			$Parameters[$k] = $v;
		}
		//签名步骤一：按字典序排序参数
		ksort($Parameters);
		$String = $this->formatBizQueryParaMap($Parameters, false);
		//echo '【string1】'.$String.'</br>';
		//签名步骤二：在string后加入KEY
		$String = $String."&key=".config('pay.weixin_key');
		//echo "【string2】".$String."</br>";
		//签名步骤三：MD5加密
		$String = md5($String);
		//echo "【string3】 ".$String."</br>";
		//签名步骤四：所有字符转为大写
		$result_ = strtoupper($String);
		//echo "【result】 ".$result_."</br>";
		return $result_;
	}
	/**
	 * 	作用：格式化参数，签名过程需要使用
	 */
	public function formatBizQueryParaMap($paraMap, $urlencode){
		$buff = "";
		@ksort($paraMap);
		if ($paraMap) {
			foreach ($paraMap as $k => $v){
				if($urlencode){
				   $v = urlencode($v);
				}
				//$buff .= strtolower($k) . "=" . $v . "&";
				$buff .= $k . "=" . $v . "&";
			}
		}
		$reqPar;
		if (strlen($buff) > 0) {
			$reqPar = substr($buff, 0, strlen($buff)-1);
		}
		return $reqPar;
	}	
	/**
	 * 	作用：array转xml
	 */
	function arrayToXml($arr){
        $xml = "<xml>";
        foreach ($arr as $key=>$val){
        	 if (is_numeric($val)){
        	 	$xml.="<".$key.">".$val."</".$key.">"; 

        	 }
        	 else
        	 	$xml.="<".$key."><![CDATA[".$val."]]></".$key.">";  
        }
        $xml.="</xml>";
        return $xml; 
    }
	
	/**
	 * 设置返回微信的xml数据
	 */
	public function setReturnParameter($parameter, $parameterValue){
		$this->returnParameters[$this->trimString($parameter)] = $this->trimString($parameterValue);
	}
	
	
	function trimString($value){
		$ret = null;
		if (null != $value) {
			$ret = $value;
			if (strlen($ret) == 0) {
				$ret = null;
			}
		}
		return $ret;
	}
	
	
}