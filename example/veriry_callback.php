<?php
/**
 * 验证企业微信第三方开发回调签名示例
 */

use jjonline\WxBizSdk\WxBizMsgCrypt;

$token          = "token"; // 第三方接入应用Token
$encodingAesKey = "encodingAesKey"; // 第三方接入应用EncodingAESKey
$receiveId      = "receiveId"; // 第三方接入应用id
$srv            = new WxBizMsgCrypt($token, $encodingAesKey, $receiveId);

$sVerifyMsgSig    = $_GET['msg_signature'];
$sVerifyTimeStamp = $_GET['timestamp'];
$sVerifyNonce     = $_GET['nonce'];
$sVerifyEchoStr   = $_GET['echostr'];

$sEchoStr = "";

// call verify function
$signatureResult = $srv->verifySignature($sVerifyMsgSig, $sVerifyTimeStamp, $sVerifyNonce, $sVerifyEchoStr);
if (empty($signatureResult)) {
    return 'verifyCheckSignature Error';
}
echo $signatureResult;
