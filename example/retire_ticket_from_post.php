<?php
/**
 * 验证企业微信服务商回调POST推送ticket验签和解密
 */

use jjonline\WxBizSdk\WxBizMsgCrypt;
use jjonline\WxBizSdk\XmlParse;

/**
 * 假设应用设置的`指令回调URL`为：https://www.test.com/suit/ticket
 * 企业微信服务器会定时推送ticket到这个URL上，且是xml的post请求，完整URL如下示例：
 * ----
 * https://www.test.com/suit/ticket?msg_signature=5c45ff5e21c57e6ad56bac8758b79b1d9ac89fd3&timestamp=1409659589&nonce=263014780
 * 需要对这个post请求进行验签+解密才能得到ticket值
 */


/**
 * 实例化sdk验证类
 */
$token          = "token"; // 创建应用生成的Token
$encodingAesKey = "encodingAesKey"; // 创建应用生成的EncodingAESKey，必须是43个字符
$receiveId      = "use app suit_id"; // 场景值，不同场景含义不同 重要：此处验证POST回调时给你应用的suit_id
$srv            = new WxBizMsgCrypt($token, $encodingAesKey, $receiveId);

/**
 * 获取POST请求里的query-string变量
 */
$sVerifyMsgSig    = $_GET['msg_signature'];
$sVerifyTimeStamp = $_GET['timestamp'];
$sVerifyNonce     = $_GET['nonce'];

/**
 * 获取POST请求的body体，是一个xml字符串
 * 注意：这里示例使用php原生方法获取，各大框架都封装有获取这种body原始串的方法，也是可以使用的
 */
$postBody = file_get_contents('php://input');

/**
 * 调用decryptMsg方法进行验证和解密，验证并解密成功返回密文解密后的明文---仍然是一个xml字符串
 */
$decryptedXml = $srv->decryptMsg($sVerifyMsgSig, $sVerifyTimeStamp, $sVerifyNonce, $postBody);
if (empty($decryptedXml)) {
    return 'Post Verify Sign And Decrypted Ticket Error';
}

/**
 * 此时 $decryptedXml 变量就是密文解析出来的xml明文，里面包含了ticket值
 * 提供有xml快速转换为数组的功能
 */
list($status, $result) = XmlParse::transferXml2Array($decryptedXml);
if ($status !== 0) {
    return 'Transfer xml to array Error';
}

/**
 * 最后一路顺风的话，到这里$result数组结构如下：
 * [
 *      'SuiteId'     => 'ww36395ed27540e09f',
 *      'InfoType'    => 'suite_ticket',
 *      'TimeStamp'   => '1688305964',
 *      'SuiteTicket' => 'miUhRGMTH_WDMt15ekXryQBq8eZIv-io9X61XLLkgTq6uj1y5oZrOHYo-iJ1gQYX',
 * ]
 */
