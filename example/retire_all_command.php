<?php
/**
 * 处理所有指令回调示例
 */

use jjonline\WxBizSdk\WxBizMsgCrypt;
use jjonline\WxBizSdk\XmlParse;

/**
 * 假设应用设置的`指令回调URL`为：https://www.test.com/suit/ticket
 * 企业微信服务器会推送所有指令回调到这个URL上，完整URL如下示例：
 * ----
 * https://www.test.com/suit/ticket?msg_signature=5c45ff5e21c57e6ad56bac8758b79b1d9ac89fd3&timestamp=1409659589&nonce=263014780
 * 需要对这个post请求进行验签+解密才能得到ticket值
 */

/**
 * 实例化sdk验证类
 */
$token          = "token";           // 创建应用生成的Token
$encodingAesKey = "encodingAesKey";  // 创建应用生成的EncodingAESKey，必须是43个字符
$receiveId      = "use app suit_id"; // 场景值，不同场景含义不同 重要：此处验证指令回调时给你应用的suit_id
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
 * 此时 $decryptedXml 变量就是密文解析出来的xml明文，里面包含了各种指令回调的具体数据
 * 提供有xml快速转换为数组的功能
 */
list($status, $cmdInfo) = XmlParse::transferXml2Array($decryptedXml);
if ($status !== 0) {
    return 'Transfer xml to array Error';
}

/**
 * 指令事件类型：只有一个没有InfoType字段用的Event，合并处理
 */
switch ($cmdInfo['InfoType'] ?? $cmdInfo['Event']) {
    case 'suite_ticket':
        // 推送suite_ticket
        // https://developer.work.weixin.qq.com/document/path/90628
        break;
    case 'create_auth':
        // 首次授权通知推送
        // https://developer.work.weixin.qq.com/document/path/90642
        break;
    case 'change_auth':
        // 变更授权通知推送
        // https://developer.work.weixin.qq.com/document/path/90642
        break;
    case 'cancel_auth':
        // 取消授权通知推送
        // https://developer.work.weixin.qq.com/document/path/90642
        break;
    case 'change_contact':
        // 两个字段区分
        switch ($cmdInfo['ChangeType']) {
            /**
             * +++++++++++++++++++
             * +++++++++++++++++++
             */
            case 'create_user':
                // 新增成员
                // https://developer.work.weixin.qq.com/document/path/90639
                break;
            case 'update_user':
                // 更新成员事件
                // https://developer.work.weixin.qq.com/document/path/90639
                break;
            case 'delete_user':
                // 删除成员事件
                // https://developer.work.weixin.qq.com/document/path/90639
                break;
            /**
             * +++++++++++++++++++
             * +++++++++++++++++++
             */
            case 'create_party':
                // 新增部门事件
                // https://developer.work.weixin.qq.com/document/path/90640
                break;
            case 'update_party':
                // 更新部门事件
                // https://developer.work.weixin.qq.com/document/path/90640
                break;
            case 'delete_party':
                // 删除部门事件
                // https://developer.work.weixin.qq.com/document/path/90640
                break;
            /**
             * +++++++++++++++++++
             * +++++++++++++++++++
             */
            case 'update_tag':
                // 标签通知事件
                // https://developer.work.weixin.qq.com/document/path/90641
                break;
        }
        break;
    case 'share_agent_change':
        // 共享应用事件回调
        // https://developer.work.weixin.qq.com/document/path/93373
        break;
    case 'reset_permanent_code':
        // 重置永久授权码通知
        // https://developer.work.weixin.qq.com/document/path/94758
        break;
    case 'change_app_admin':
        // 应用管理员变更通知，注意：这里是Event字段
        // https://developer.work.weixin.qq.com/document/path/95038
        break;
    case 'corp_arch_auth':
        // 授权组织架构权限通知
        // https://developer.work.weixin.qq.com/document/path/97378
        break;
    case 'approve_special_auth':
        // 获客助手权限确认事件
        // https://developer.work.weixin.qq.com/document/path/98959
        break;
    case 'cancel_special_auth':
        // 获客助手权限取消事件
        // https://developer.work.weixin.qq.com/document/path/98959
        break;
}
