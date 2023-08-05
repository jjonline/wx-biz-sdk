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
     * @param string $receiveId 场景值，不同应用场景传不同的corp_id|suit_id，消息回调解密使用可给空不验证（不影响安全），对发送消息加密则需要给
     */
    public function __construct(string $token, string $encodingAesKey, string $receiveId = '')
    {
        $this->token          = $token;
        $this->encodingAesKey = $encodingAesKey;
        $this->receiveId      = $receiveId;
    }

    /**
     * GET回调签名验证并解密
     * @param string $msg_signature 签名串，对应URL参数的msg_signature
     * @param string $timestamp 时间戳，对应URL参数的timestamp
     * @param string $nonce 随机串，对应URL参数的nonce
     * @param string $echostr 随机串，对应URL参数的echostr
     * @return false|string 验证解密成功返回解密成功后字符串，否则返回false
     */
    public function verifySignature(string $msg_signature, string $timestamp, string $nonce, string $echostr)
    {
        // ugly 企业微信应用配置的EncodingAESKey长度必须是43个字符的长度
        if (strlen($this->encodingAesKey) != 43) {
            return false;
        }

        $echostr = empty($echostr) ? '' : $echostr;
        return $this->extracted($msg_signature, $timestamp, $nonce, $echostr);
    }

    /**
     * 将公众平台回复用户的消息加密打包
     * @param string $sReplyMsg 公众平台待回复用户的消息，xml格式的字符串
     * @param string $sTimeStamp 时间戳，可以自己生成，也可以用URL参数的timestamp
     * @param string $sNonce 随机串，可以自己生成，也可以用URL参数的nonce
     * @return bool|string
     */
    public function encryptMsg(string $sReplyMsg, string $sTimeStamp, string $sNonce)
    {
        $pc = new MsgCrypt($this->encodingAesKey);

        // 加密
        list($status, $encrypt) = $pc->encrypt($sReplyMsg, $this->receiveId);
        if ($status != ErrorCode::$OK) {
            return false;
        }

        if (empty($sTimeStamp)) {
            $sTimeStamp = strval(time());
        }

        // 生成安全签名
        list($sStatus, $signature) = VerifySignature::sign($this->token, $sTimeStamp, $sNonce, $encrypt);
        if ($sStatus != ErrorCode::$OK) {
            return false;
        }

        // 生成发送的xml
        return XmlParse::generate($encrypt, $signature, $sTimeStamp, $sNonce);
    }

    /**
     * 解密post提交的数据
     * @param string $msg_signature 签名串，对应URL参数的msg_signature
     * @param string $timestamp 时间戳 对应URL参数的timestamp
     * @param string $nonce 随机串，对应URL参数的nonce
     * @param string $postBody 对应POST请求的数据body体原始内容
     * @return false|string 验证并解密成功返回xml标签Encrypt中的值的解密后的明文
     */
    public function decryptMsg(string $msg_signature, string $timestamp, string $nonce, string $postBody)
    {
        if (strlen($this->encodingAesKey) != 43) {
            return false;
        }

        // ① 提取body体的密文--密文正确解析出来后还是xml
        // ② 参与验签的是这个密文而不是整个xml的body体
        list($status, $encrypt) = XmlParse::extractEncryptTagValue($postBody);

        if ($status != ErrorCode::$OK) {
            return false;
        }

        if (empty($timestamp)) {
            $timestamp = strval(time());
        }

        // 验证安全签名
        return $this->extracted($msg_signature, $timestamp, $nonce, $encrypt);
    }

    /**
     * 公用方法：验签并解密加密字符串中的message
     * @param string $msg_signature 回调申明的签名值
     * @param string $timestamp 回调query中的timestamp
     * @param string $nonce 回调query中的nonce
     * @param string $encrypt 被加密的字符串原始值
     * @return false|string
     */
    protected function extracted(string $msg_signature, string $timestamp, string $nonce, string $encrypt)
    {
        // ① 自己依据参数计算签名值
        list($status, $calcSignature) = VerifySignature::sign($this->token, $timestamp, $nonce, $encrypt); // verify msg_signature
        if ($status != ErrorCode::$OK) {
            return false;
        }

        // ② 比对自主计算的签名值与回调过来的申明的签名值是否一致
        if ($calcSignature != $msg_signature) {
            return false;
        }

        // ③ 解密aes消息message：内部隐含还有一层场景值的验证，所以依赖 $this->receiveId
        $pc = new MsgCrypt($this->encodingAesKey);
        list($deStatus, $decrypted) = $pc->decrypt($encrypt, $this->receiveId);
        if ($deStatus != ErrorCode::$OK) {
            return false;
        }

        return $decrypted;
    }
}
