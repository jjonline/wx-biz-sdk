<?php
/**
 * 验证企业微信第三方开发应用获取授权和应用授权
 * ---
 * ① suite_ticket换suite_access_token
 * ② create_auth换备用该第三方应用的企业永久授权码和应用信息
 */

use jjonline\WxBizSdk\SuiteAccess;

$suit_id      = ''; // 创建应用分配的SuiteID
$suite_secret = ''; // 创建应用分配的SuiteSecret，服务商应用位置处叫Secret
$srv          = new SuiteAccess($suit_id, $suite_secret);

$suite_ticket = ''; // `指令回调URL`接收到的suite_ticket

/**
 * 当该第三方应用配置妥当后，企业微信会定时推送suite_ticket到`指令回调URL`
 * 获取到在这个suite_ticket后要及时去换后续调用其他api的suite_access_token
 * suite_access_token要给缓存起来，调用其他api时从缓存获取
 * 因为suite_ticket会定时推送过来，可以每次推送过来主动触发获取，缓存给存起来，缓存有效期就是返回的expires_in
 */
$resp               = $srv->getSuiteToken($suite_secret); // 获取suite_access_token，获取成功返回数组，获取失败抛出Exception异常终止，业务层要捕获处理
$suite_access_token = $resp['suite_access_token'];
$expires_in         = $resp['expires_in']; // suite_access_token从获取那一刻开始的有效期时长，单位：秒

/**
 * 当该第三方应用被其他企业安装使用后
 * `指令回调URL`会收到create_auth回调，调用相应api可以获取到被授权应用的信息
 */
$auth_code = ''; //  `指令回调URL`接收到的auth_code
$resp      = $srv->getPermanentCode($suite_access_token, $auth_code); // 调用成功返回数组，获取失败抛出Exception异常终止，业务层要捕获处理

/**
 * SuiteAccess还封装有其他api，自取
 */
