<?php
/**
 * 验证企业微信第三方开发回调签名示例
 * ---
 * 当新建第三方应用提交保存 或 编辑回调地址再提交保存时会触发企业微信服务器主动 GET 请求回调地址，该示例演示处理这个GET请求的响应
 */

use jjonline\WxBizSdk\WxBizMsgCrypt;

/**
 * 假设应用设置的`数据回调UR`为：https://www.test.com/suit/message
 * 假设应用设置的`指令回调URL`为：https://www.test.com/suit/ticket
 * 当提交保存创建应用 或 编辑应用`回调配置`点击保存时这两个URL会被企业微信服务器各自推送一个GET请求过来，请求完整URL如下示例：
 * ----
 * ① `数据回调`
 * https://www.test.com/suit/message?msg_signature=5c45ff5e21c57e6ad56bac8758b79b1d9ac89fd3&timestamp=1409659589&nonce=263014780&echostr=P9nAzCzyDtyTWESHep1vC5X9xho%2FqYX3Zpb4yKa9SKld1DsH3Iyt3tP3zNdtp%2B4RPcs8TgAE7OaBO%2BFZXvnaqQ%3D%3D
 * ② `指令回调`
 * https://www.test.com/suit/ticket?msg_signature=5c45ff5e21c57e6ad56bac8758b79b1d9ac89fd3&timestamp=1409659589&nonce=263014780&echostr=P9nAzCzyDtyTWESHep1vC5X9xho%2FqYX3Zpb4yKa9SKld1DsH3Iyt3tP3zNdtp%2B4RPcs8TgAE7OaBO%2BFZXvnaqQ%3D%3D
 * 需要对这两个GET请求的回调进行验证，下方就是在这两个URL下使用本sdk的进行签名验证和正确的响应值获取的具体过程
 */

/**
 * 实例化sdk验证类
 */
$token          = "token"; // 创建应用生成的Token
$encodingAesKey = "encodingAesKey"; // 创建应用生成的EncodingAESKey，必须是43个字符
$receiveId      = "use corp_id"; // 场景值，不同场景含义不同 重要：此处验证GET回调时给你自己企业微信的corp_id
$srv            = new WxBizMsgCrypt($token, $encodingAesKey, $receiveId);

/**
 * 获取GET请求里的query-string变量
 */
$sVerifyMsgSig    = $_GET['msg_signature'];
$sVerifyTimeStamp = $_GET['timestamp'];
$sVerifyNonce     = $_GET['nonce'];
$sVerifyEchoStr   = $_GET['echostr'];

/**
 * 调用verifySignature方法进行验证，验证成功返回解密后的响应字符串，验证失败返回false
 */
$signatureResult = $srv->verifySignature($sVerifyMsgSig, $sVerifyTimeStamp, $sVerifyNonce, $sVerifyEchoStr);
if (empty($signatureResult)) {
    return 'verifyCheckSignature Error';
}
echo $signatureResult;
