<?php
/**
 * query参数签名验证计算方法实现
 */

namespace jjonline\WxBizSdk;

use Exception;

class VerifySignature
{
    /**
     * 计算签名
     * @param string $token 创建第三方应用时填写的token
     * @param string $timestamp query-string键名为timestamp的值，时间戳
     * @param string $nonce query-string键名为nonce的值，随机字符串
     * @param string $encrypt_msg 密文消息，例如 query-string键名为echostr的值，当然不局限于这个键名
     * @return array 返回长度为2的数组，第一个下标值为0则成功再取第二个下标返回的计算出的签名只去与回调请求的签名值比较
     */
    public static function sign(string $token, string $timestamp, string $nonce, string $encrypt_msg): array
    {
        try {
            $array = array($encrypt_msg, $token, $timestamp, $nonce);
            sort($array, SORT_STRING);
            $str = implode('', $array);
            return array(ErrorCode::$OK, sha1($str));
        } catch (Exception $e) {
            return array(ErrorCode::$ComputeSignatureError, null);
        }
    }
}
