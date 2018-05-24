<?php
/**
 * Created by PhpStorm.
 * User: Mikkle
 * QQ:776329498
 * Date: 2017/06/11
 * Time: 11:58
 */

namespace mikkle\tp_aliyun;

use mikkle\tp_master\Exception;
use mikkle\tp_master\Config;
use mikkle\tp_master\Log;
use mikkle\tp_master\Validate;
use mikkle\tp_tools\ShowCode;

class SendSms
{
    static protected $instance;
    protected  $accessKeyId;
    protected  $accessSecret;
    protected  $templateCode;
    protected $signName;
    protected  $requestHost = "http://dysmsapi.aliyuncs.com";
    protected  $requestUrl;
    protected  $signature;
    protected  $requestParas;
    protected $error;
    public function __construct($options=[])
    {
        ini_set('date.timezone','Asia/Shanghai');
        date_default_timezone_set("GMT");
        isset($options["AccessKeyId"]) && $this->accessKeyId = $options["AccessKeyId"];
        isset($options["AccessSecret"]) && $this->accessSecret = $options["AccessSecret"];
        isset($options["TemplateCode"]) && $this->templateCode = $options["TemplateCode"];
        isset($options["SignName"]) && $this->signName = $options["SignName"];
    }

    static public function instance($options=[]){
        $options =self::getOptions($options);
        $sn = self::getSn($options);
        if(isset(self::$instance[$sn])){
            return self::$instance[$sn];
        }else{
            return self::$instance[$sn]= new static($options);
        }
    }

    /**
     * @title setAccessKeyId
     * @description 设置AccessKey
     * @author Mikkle
     * @param string $accessKeyId
     * @return $this
     */
    public  function  setAccessKeyId($accessKeyId=""){
        if (!empty($accessKeyId) && is_string($accessKeyId) ){
            $this->accessKeyId = $accessKeyId;
        }
        return $this;
    }

    /**
     * @title setAppSecret
     * @description
     * @author Mikkle
     * @param string $accessSecret
     * @return $this
     */
    public  function  setAppSecret($accessSecret=""){
        if (!empty($accessSecret) && is_string($accessSecret) ){
            $this->accessSecret = $accessSecret;
        }
        return $this;
    }

    public function setTemplateCode($templateCode=""){
        if (!empty($templateCode) && is_string($templateCode) ){
            $this->templateCode = $templateCode;
        }
        return $this;
    }

    public function setSignName($signName=""){
        if (!empty($signName) && is_string($signName) ){
            $this->signName = $signName;
        }
        return $this;
    }


    /**
     * @title send
     * @description 发送
     * @author Mikkle
     * @param string $phone
     * @param string $code
     * @return array|mixed
     */
    public function send($phone="",$code=""){
        try{
            if (!$this->checkParams($phone , $code )) {

                throw  new  Exception($this->error);
            }
            if ($this->createRequestUrl( $phone , $code  )&&$this->signature) {
               $url="{$this->requestHost}/?Signature={$this->signature}{$this->requestUrl}";
                $content =  $this->fetchContent($url);
                return json_decode($content);
            }else{
                throw  new  Exception("参数缺失");
            }
        }catch (Exception $e){
            Log::error($e->getMessage());
            return ShowCode::codeWithoutData(1008,$e->getMessage());
        }
    }


    protected function createRequestUrl($phone = "", $code = "")
    {
        try {
            $requestParams = [
                'RegionId' => 'cn-hangzhou', // API支持的RegionID，如短信API的值为：cn-hangzhou
                'AccessKeyId' => $this->accessKeyId, // 访问密钥，在阿里云的密钥管理页面创建
                "Format" => 'JSON', // 返回值类型，没传默认为JSON，可选填值：XML
                "SignatureMethod" => 'HMAC-SHA1', // 编码(固定值不用改)
                "SignatureVersion" => '1.0', // 版本(固定值不用改)
                'SignatureNonce' => uniqid(mt_rand(0, 0xffff), true), // 用于请求的防重放攻击的唯一加密盐
                'Timestamp' => date('Y-m-d\TH:i:s\Z'), // 格式为：yyyy-MM-dd’T’HH:mm:ss’Z’；时区为：GMT
                'Action' => 'SendSms', // API的命名，固定值，如发送短信API的值为：SendSms
                'Version' => '2017-05-25', // API的版本，固定值，如短信API的值为：2017-05-25
                'PhoneNumbers' => $phone, // 短信接收号码
                'SignName' => $this->signName, // 短信签名
                'TemplateCode' => $this->templateCode, // 短信模板ID
                'TemplateParam' => json_encode([ // 短信模板变量替换JSON串
                    "code" => $code,
                ]),
            ];
            ksort($requestParams);
            $requestUrl = "";
            foreach ($requestParams as $key => $value) {
                $requestUrl .=  "&" . $this->encode($key) . "=" . $this->encode($value);
            }
            $this->requestUrl = $requestUrl ;

            $stringToSign = "GET&%2F&" . $this->encode(substr($requestUrl, 1));
            // 清除最后一个&
            $this->signature = base64_encode(hash_hmac('sha1', $stringToSign, $this->accessSecret . '&', true));
            $this->requestParas["Signature"]=$this->signature ;
            if (empty($this->signature)){
                throw  new  Exception("URL加密错误");
            }
            return true;
        } catch (Exception $e) {
            Log::error($e->getMessage());
            $this->error = $e->getMessage();
            return false;
        }
    }

    protected function checkParams($phone="",$code=""){
        if (empty($this->accessKeyId) || empty($this->accessSecret) ||empty($this->templateCode)  ){
            $this->error = "获取短息发送接口参数缺失";
            return false;
        }
        $validate = new Validate([
            ['phone','require|regex:/1[34578]{1}\d{9}$/','手机号不能为空|手机号错误'],
            ['code','require','验证码不存在'],
        ]);
        $data=[
            "phone"=>$phone,
            "code"=>$code,
        ];
        if (!$validate->check($data)) {
            $this->error = $validate->getError();
            return false;
        }
        return true;
    }

    protected function encode($url)
    {
        $url = urlencode($url);
        $url = preg_replace('/\+/', '%20', $url);
        $url = preg_replace('/\*/', '%2A', $url);
        $url = preg_replace('/%7E/', '~', $url);
        return $url;
    }


    static protected function getOptions($options=[]){

        if (empty($options)&& !empty( Config::get("alisms.default_options_name"))){
            $name = "alisms".".".Config::get("alisms.default_options_name");
            $options = Config::get("$name");
        }elseif(is_string($options)&&!empty( Config::get("alisms.$options"))){
            $options = Config::get("alisms.$options");
        }
        if (empty($options)) {
            $error[]="获取短息发送接口参数缺失";
            throw new Exception("获取短息发送接口参数缺失");
        }elseif(isset($options["AccessKeyId"])&&isset($options["AccessSecret"])){
            return $options ;
        }else{
            throw new Exception("短息发送接口参数不完整");
        }
    }

    static protected function getSn($options){
        return md5("{$options["AccessKeyId"]}{$options["AccessSecret"]}");
    }


    private function fetchContent($url) {
        if(function_exists("curl_init")) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "x-sdk-client" => "php/2.0.0"
            ));
            $rtn = curl_exec($ch);
            if($rtn === false) {
                trigger_error("[CURL_" . curl_errno($ch) . "]: " . curl_error($ch), E_USER_ERROR);
            }
            curl_close($ch);
            return $rtn;
        }

        $context = stream_context_create(array(
            "http" => array(
                "method" => "GET",
                "header" => array("x-sdk-client: php/2.0.0"),
            )
        ));
        return file_get_contents($url, false, $context);
    }



}