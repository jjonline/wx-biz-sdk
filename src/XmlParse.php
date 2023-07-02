<?php
/**
 * xml消息处理类
 */

namespace jjonline\WxBizSdk;

use Exception;

class XmlParse
{
    /**
     * 提取出xml数据包中的加密标签Encrypt的密文值
     * @param string $xmlText 待提取的xml字符串
     * @return array 返回长度为2的数组，第一个下标值为0则成功再取第二个下标返回解析xml成功后的值
     * ++++
     * 用法示例：
     * list($status, $xml2Array) = XmlParse::extractEncryptTagValue($xmlBody);
     * ++++
     */
    public static function extractEncryptTagValue($xmlText)
    {
        try {
            list($status, $result) = self::transferXml2Array($xmlText);
            if ($status == ErrorCode::$OK && isset($result['Encrypt'])) {
                return [ErrorCode::$OK, $result['Encrypt']]; // 只需要返回Encrypt键的值
            }
            return array(ErrorCode::$ParseXmlError, null);
        } catch (\Exception $e) {
            return array(ErrorCode::$ParseXmlError, null);
        }
    }

    /**
     * 将推送响应过来的xml字符串转换为数组 或 密文解密后的xml字符串转换为数组
     * @param string $xmlText
     * @return array 返回长度为2的数组，第一个下标值为0则成功再取第二个下标返回解析xml成功后的值
     */
    public static function transferXml2Array($xmlText)
    {
        try {
            $xmlObject = simplexml_load_string($xmlText, 'SimpleXMLElement', LIBXML_NOCDATA);
            if (empty($xmlObject)) {
                throw new Exception('SimpleXMLElement Parse Error'); // 基本不会出现
            }
            return array(ErrorCode::$OK, json_decode(json_encode($xmlObject), true));
        } catch (\Exception $e) {
            return array(ErrorCode::$ParseXmlError, null);
        }
    }

    /**
     * 生成推送出去的xml消息
     * @param string $encrypt 加密后的消息密文
     * @param string $signature 安全签名
     * @param string $timestamp 时间戳
     * @param string $nonce 随机字符串
     */
    public static function generate($encrypt, $signature, $timestamp, $nonce)
    {
        $format = "<xml>
<Encrypt><![CDATA[%s]]></Encrypt>
<MsgSignature><![CDATA[%s]]></MsgSignature>
<TimeStamp>%s</TimeStamp>
<Nonce><![CDATA[%s]]></Nonce>
</xml>";
        return sprintf($format, $encrypt, $signature, $timestamp, $nonce);
    }
}
