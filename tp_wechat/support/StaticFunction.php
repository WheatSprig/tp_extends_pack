<?php
/**
 * Created by PhpStorm.
 * Power By Mikkle
 * Email：776329498@qq.com
 * Date: 2017/7/21
 * Time: 13:55
 */

namespace mikkle\tp_wechat\support;

/**
 * 静态方法类库
 * Power: Mikkle
 * Email：776329498@qq.com
 * Class BaseSupport
 * @package mikkle\tp_wechat\base
 */
class StaticFunction
{
    static public function parseJSON($json){
        if (empty($json)) {
            return false;
        }
        $json = self::invalidJSON($json);
        $contents = json_decode($json, true);
        if (JSON_ERROR_NONE !== json_last_error()){
            Log::write("parseJson error:[$json]");
            return false;
        }
        return $contents;
    }

    static private function invalidJSON($invalidJSON)
    {
        return preg_replace('/[\x00-\x1F\x80-\x9F]/u', '', trim($invalidJSON));
    }


    /**
     * 产生随机字符串
     * @param int $length
     * @param string $str
     * @return string
     */
    static public function createRandStr($length = 32, $str = "")
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    /**
     * 获取签名
     * @param array $arr_data 签名数组
     * @param string $method 签名方法
     * @return bool|string 签名值
     */
    static public function getSignature($arr_data, $method = "sha1")
    {
        if (!function_exists($method)) {
            return false;
        }
        ksort($arr_data);
        $params = array();
        foreach ($arr_data as $key => $value) {
            $params[] = "{$key}={$value}";
        }
        return $method(join('&', $params));
    }

    /**
     * 生成支付签名
     * @param array $option
     * @param string $partnerKey
     * @return string
     */
    static public function getPaySign($option, $partnerKey)
    {
        ksort($option);
        $buff = '';
        foreach ($option as $k => $v) {
            $buff .= "{$k}={$v}&";
        }
        return strtoupper(md5("{$buff}key={$partnerKey}"));
    }

    /**
     * XML编码
     * @param mixed $data 数据
     * @param string $root 根节点名
     * @param string $item 数字索引的子节点名
     * @param string $id 数字索引子节点key转换的属性名
     * @return string
     */
    static public function arr2xml($data, $root = 'xml', $item = 'item', $id = 'id')
    {
        return "<{$root}>" . self::_data_to_xml($data, $item, $id) . "</{$root}>";
    }

    /**
     * XML内容生成
     * @param array $data 数据
     * @param string $item 子节点
     * @param string $id 节点ID
     * @param string $content 节点内容
     * @return string
     */
    static private function _data_to_xml($data, $item = 'item', $id = 'id', $content = '')
    {
        foreach ($data as $key => $val) {
            is_numeric($key) && $key = "{$item} {$id}=\"{$key}\"";
            $content .= "<{$key}>";
            if (is_array($val) || is_object($val)) {
                $content .= self::_data_to_xml($val);
            } elseif (is_numeric($val)) {
                $content .= $val;
            } else {
                $content .= '<![CDATA[' . preg_replace("/[\\x00-\\x08\\x0b-\\x0c\\x0e-\\x1f]/", '', $val) . ']]>';
            }
            list($_key,) = explode(' ', $key . ' ');
            $content .= "</$_key>";
        }
        return $content;
    }


    /**
     * 将xml转为array
     * @param string $xml
     * @return array
     */
    static public function xml2arr($xml)
    {
        return json_decode(self::jsonEncode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    }


    /**
     * 生成安全JSON数据
     * @param array $array
     * @return string
     */
    static public function jsonEncode($array)
    {
        return preg_replace_callback('/\\\\u([0-9a-f]{4})/i', create_function('$matches', 'return mb_convert_encoding(pack("H*", $matches[1]), "UTF-8", "UCS-2BE");'), json_encode($array));
    }



}