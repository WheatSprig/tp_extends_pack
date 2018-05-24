<?php
/**
 * Created by PhpStorm.
 * User: Mikkle
 * QQ:776329498
 * Date: 2017/12/12
 * Time: 19:37
 */

namespace mikkle\tp_aliyun;


class Sign
{
    static function create(array $apiParams,$accessKeySecret,$type="GET"){
        ksort($params);
            $sortedQueryStringTmp = "";
            foreach ($apiParams as $key => $value) {
                $sortedQueryStringTmp .= "&" . self::encode($key) . "=" . self::encode($value);
            }
        $type = (strtoupper($type )=="POST" ) ? "POST" :"GET";
            $stringToSign = "$type&%2F&" . self::encode(substr($sortedQueryStringTmp, 1));
            $sign = base64_encode(hash_hmac("sha1", $stringToSign, $accessKeySecret . "&",true));
            $signature = self::encode($sign);
            return $signature;

    }

    static protected function encode($url)
    {
        $url = urlencode($url);
        $url = preg_replace('/\+/', '%20', $url);
        $url = preg_replace('/\*/', '%2A', $url);
        $url = preg_replace('/%7E/', '~', $url);
        return $url;
    }

}