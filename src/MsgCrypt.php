<?php
/**
 * 消息AES加解密类
 */

namespace jjonline\WxBizSdk;

use Exception;

class MsgCrypt
{
    public $key = null;
    public $iv  = null;

    /**
     * @param string $k 企业微信应用配置的EncodingAESKey值 必须是43个字符
     */
    public function __construct(string $k)
    {
        $this->key = base64_decode($k . '=');
        $this->iv  = substr($this->key, 0, 16);
    }

    /**
     * 加密
     * @param string $text
     * @param string $receiveId
     * @return array 返回长度为2的数组，第一个下标值为0则成功第二个下标志返回加密后的值
     */
    public function encrypt(string $text, string $receiveId): array
    {
        try {
            $text      = $this->getRandomStr() . pack('N', strlen($text)) . $text . $receiveId;              // 拼接
            $text      = PKCS7Encoder::encode($text);                                                        // 添加PKCS#7填充
            $encrypted = openssl_encrypt($text, 'AES-256-CBC', $this->key, OPENSSL_ZERO_PADDING, $this->iv); // 加密
            return array(ErrorCode::$OK, $encrypted);
        } catch (Exception $e) {
            return array(ErrorCode::$EncryptAESError, null);
        }
    }

    /**
     * 解密aes密文
     * @param string $encrypted 密文
     * @param string $receiveId 场景值-callback则是corp_id
     * @return array 返回长度为2的数组，第一个下标值为0则成功第二个下标志返回解密后的值
     */
    public function decrypt(string $encrypted, string $receiveId): array
    {
        try {
            $decrypted = openssl_decrypt($encrypted, 'AES-256-CBC', $this->key, OPENSSL_ZERO_PADDING, $this->iv); // 解密
        } catch (Exception $e) {
            return array(ErrorCode::$DecryptAESError, null);
        }
        try {
            $result = PKCS7Encoder::decode($decrypted); // 删除PKCS#7填充
            if (strlen($result) < 16) {
                return array(ErrorCode::$EncodeBase64Error, null);
            }
            $content        = substr($result, 16, strlen($result));
            $len_list       = unpack('N', substr($content, 0, 4));
            $xml_len        = $len_list[1];
            $xml_content    = substr($content, 4, $xml_len);
            $from_receiveId = substr($content, $xml_len + 4);
        } catch (Exception $e) {
            return array(ErrorCode::$IllegalBuffer, null);
        }

        // 验证场景值，GET请求验证URL的是企业id--corp_id
        if ($from_receiveId != $receiveId) {
            return array(ErrorCode::$ValidateCorpIdError, null);
        }

        return array(ErrorCode::$OK, $xml_content);
    }

    /**
     * 生成随机字符串
     * @return string
     */
    private function getRandomStr(): string
    {
        $str     = '';
        $str_pol = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyl';
        $max     = strlen($str_pol) - 1;
        for ($i = 0; $i < 16; $i++) {
            $str .= $str_pol[mt_rand(0, $max)];
        }
        return $str;
    }
}
