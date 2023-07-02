<?php
/**
 * 企业微信消息加解密暴露方法
 */

namespace jjonline\WxBizSdk;

class WxBizMsgCrypt
{
    private $token;
    private $encodingAesKey;
    private $receiveId;

    /**
     * 构造函数
     * @param string $token 开发者设置的token
     * @param string $encodingAesKey 开发者设置的EncodingAESKey
     * @param string $receiveId 不同应用场景传不同的app_id|suit_id
     */
    public function __construct($token, $encodingAesKey, $receiveId)
    {
        $this->token          = $token;
        $this->encodingAesKey = $encodingAesKey;
        $this->receiveId      = $receiveId;
    }

    /**
     * 回调签名验证并解密
     * @param string $msg_signature 签名串，对应URL参数的msg_signature
     * @param string $timestamp 时间戳，对应URL参数的timestamp
     * @param string $nonce 随机串，对应URL参数的nonce
     * @param string $echostr 随机串，对应URL参数的echostr
     * @return false|string 验证解密成功返回解密成功后字符串，否则返回false
     */
    public function verifySignature($msg_signature, $timestamp, $nonce, $echostr)
    {
        if (strlen($this->encodingAesKey) != 43) {
            return false;
        }

        $pc = new MsgCrypt($this->encodingAesKey);
        return $this->extracted($msg_signature, $timestamp, $nonce, $echostr, $pc);
    }

    /**
     * 将公众平台回复用户的消息加密打包
     * @param string $sReplyMsg 公众平台待回复用户的消息，xml格式的字符串
     * @param string $sTimeStamp 时间戳，可以自己生成，也可以用URL参数的timestamp
     * @param string $sNonce 随机串，可以自己生成，也可以用URL参数的nonce
     * @return bool|string
     */
    public function encryptMsg($sReplyMsg, $sTimeStamp, $sNonce, &$sEncryptMsg)
    {
        $pc = new MsgCrypt($this->encodingAesKey);

        // 加密
        list($status, $encrypt) = $pc->encrypt($sReplyMsg, $this->receiveId);
        if ($status != ErrorCode::$OK) {
            return false;
        }

        if (empty($sTimeStamp)) {
            $sTimeStamp = time();
        }

        // 生成安全签名
        list($sStatus, $signature) = VerifySignature::sign($this->token, $sTimeStamp, $sNonce, $encrypt);
        if ($sStatus != ErrorCode::$OK) {
            return false;
        }

        // 生成发送的xml
        $xmlParse = new XmlParse;
        return $xmlParse->generate($encrypt, $signature, $sTimeStamp, $sNonce);
    }

    /**
     * @param string $sMsgSignature 签名串，对应URL参数的msg_signature
     * @param string $sTimeStamp 时间戳 对应URL参数的timestamp
     * @param string $sNonce 随机串，对应URL参数的nonce
     * @param string $sPostData 密文，对应POST请求的数据
     * @return false|string
     */
    public function decryptMsg($sMsgSignature, $sTimeStamp, $sNonce, $sPostData)
    {
        if (strlen($this->encodingAesKey) != 43) {
            return false;
        }

        $pc = new MsgCrypt($this->encodingAesKey);

        //提取密文
        $xmlParse = new XmlParse;
        list($status, $encrypt) = $xmlParse->extract($sPostData);

        if ($status != ErrorCode::$OK) {
            return false;
        }

        if (empty($sTimeStamp)) {
            $sTimeStamp = time();
        }

        // 验证安全签名
        return $this->extracted($sTimeStamp, $sNonce, $encrypt, $sMsgSignature, $pc);
    }

    /**
     * 公用方法
     * @param string $sTimeStamp
     * @param string $sNonce
     * @param string $sEchoStr
     * @param string $sMsgSignature
     * @param MsgCrypt $pc
     * @return false|string
     */
    protected function extracted($sTimeStamp, $sNonce, $sEchoStr, $sMsgSignature, MsgCrypt $pc)
    {
        list($status, $text) = VerifySignature::sign($this->token, $sTimeStamp, $sNonce, $sEchoStr); // verify msg_signature
        if ($status != ErrorCode::$OK) {
            return false;
        }

        if ($text != $sMsgSignature) {
            return false;
        }

        list($deStatus, $decrypted) = $pc->decrypt($sEchoStr, $this->receiveId);
        if ($deStatus != ErrorCode::$OK) {
            return false;
        }
        return $decrypted;
    }
}
