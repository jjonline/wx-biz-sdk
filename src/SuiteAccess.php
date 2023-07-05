<?php
/**
 * 服务商认证相关接口api封装
 */

namespace jjonline\WxBizSdk;

use Exception;

class SuiteAccess
{
    const RespErrCodeOk      = 0; // 调用api响应json中errcode字段值成功标量
    const GetSuitToken       = 'https://qyapi.weixin.qq.com/cgi-bin/service/get_suite_token';
    const GetPreAuthCode     = 'https://qyapi.weixin.qq.com/cgi-bin/service/get_pre_auth_code';
    const SetSessionInfo     = 'https://qyapi.weixin.qq.com/cgi-bin/service/set_session_info';
    const GetPermanentCode   = 'https://qyapi.weixin.qq.com/cgi-bin/service/get_permanent_code';
    const GetAuthInfo        = 'https://qyapi.weixin.qq.com/cgi-bin/service/get_auth_info';
    const GetCorpToken       = 'https://qyapi.weixin.qq.com/cgi-bin/service/get_corp_token';
    const GetAdminList       = 'https://qyapi.weixin.qq.com/cgi-bin/service/get_admin_list';
    const GetAppQrcode       = 'https://qyapi.weixin.qq.com/cgi-bin/service/get_app_qrcode';
    const CorpIdToOpenCorpId = 'https://qyapi.weixin.qq.com/cgi-bin/service/corpid_to_opencorpid';
    const GetPermissions     = 'https://qyapi.weixin.qq.com/cgi-bin/agent/get_permissions';

    protected $suite_id;
    protected $suite_secret;

    public function __construct($suite_id, $suite_secret)
    {
        $this->suite_id     = $suite_id;
        $this->suite_secret = $suite_secret;
    }

    /**
     * suite_ticket换suite_access_token
     * https://developer.work.weixin.qq.com/document/path/90600
     * @param string $suite_ticket
     * @return array 不抛异常则一定能返回数组，关键结构['suite_access_token' => '', 'expires_in' => '']，调用失败或错误抛出Exception异常
     * @throws Exception
     */
    public function getSuiteToken(string $suite_ticket): array
    {
        if (empty($suite_ticket)) {
            throw new Exception('suite_ticket不得为空');
        }
        $body   = [
            'suite_id'     => $this->suite_id,
            'suite_secret' => $this->suite_secret,
            'suite_ticket' => $suite_ticket,
        ];
        $result = HttpHelper::postJson(self::GetSuitToken, [], [], $body);
        return $this->parseResponse($result, true);
    }

    /**
     * suite_access_token获取预授权码pre_auth_code
     * https://developer.work.weixin.qq.com/document/path/90601
     * @param string $suite_access_token
     * @return array 不抛异常则一定能返回数组，关键结构['pre_auth_code' => '', 'expires_in' => '']，调用失败或错误抛出Exception异常
     * @throws Exception
     */
    public function getPreAuthCode(string $suite_access_token): array
    {
        if (empty($suite_access_token)) {
            throw new Exception('suite_access_token不得为空');
        }
        $query  = [
            'suite_access_token' => $suite_access_token,
        ];
        $result = HttpHelper::get(self::GetPreAuthCode, $query);
        return $this->parseResponse($result, true);
    }

    /**
     * 设置授权配置
     * https://developer.work.weixin.qq.com/document/path/90601
     * @param string $pre_auth_code 本类 getPreAuthCode 方法获取到的pre_auth_code
     * @param array $session_info 结构 ['appid' => [1,2,3], 'auth_type' => 1]
     * @return array 不抛异常则一定能返回数组，调用失败或错误抛出Exception异常
     * @throws Exception
     */
    public function setSessionInfo(string $pre_auth_code, array $session_info): array
    {
        if (empty($pre_auth_code)) {
            throw new Exception('pre_auth_code不得为空');
        }
        if (empty($session_info)) {
            throw new Exception('session_info不得为空');
        }
        $body   = [
            'pre_auth_code' => $pre_auth_code,
            'session_info'  => $session_info,
        ];
        $result = HttpHelper::postJson(self::SetSessionInfo, [], [], $body);
        return $this->parseResponse($result, true);
    }

    /**
     * suite_access_token和auth_code获取企业永久授权码
     * https://developer.work.weixin.qq.com/document/path/90603
     * 注意：获取到的permanent_code值可以永久存起来
     * @param string $suite_access_token 本类 getSuiteToken 方法获取到的suite_access_token令牌
     * @param string $auth_code 临时授权码，会在授权成功时附加在redirect_uri中跳转回第三方服务商网站，或通过授权成功通知回调推送给服务商
     * @return array 不抛异常则一定能返回数组，数组结构请dump或参照文档链接查看，调用失败或错误抛出Exception异常
     * @throws Exception
     */
    public function getPermanentCode(string $suite_access_token, string $auth_code): array
    {
        if (empty($suite_access_token)) {
            throw new Exception('suite_access_token不得为空');
        }
        if (empty($auth_code)) {
            throw new Exception('auth_code不得为空');
        }
        $query  = [
            'suite_access_token' => $suite_access_token,
        ];
        $body   = [
            'auth_code' => $auth_code,
        ];
        $result = HttpHelper::postJson(self::GetPermanentCode, $query, [], $body);
        return $this->parseResponse($result, true);
    }

    /**
     * 获取企业授权信息
     * https://developer.work.weixin.qq.com/document/path/90604
     * @param string $suite_access_token 本类 getSuiteToken 方法获取到的suite_access_token令牌
     * @param string $permanent_code 本类 getPermanentCode 方法获取到的permanent_code，该值可以永久存起来
     * @param string $corp_id 授权企业的企业id
     * @return array 不抛异常则一定能返回数组，数组结构请dump或参照文档链接查看，调用失败或错误抛出Exception异常
     * @throws Exception
     */
    public function getAuthInfo(string $suite_access_token, string $permanent_code, string $corp_id): array
    {
        if (empty($suite_access_token)) {
            throw new Exception('suite_access_token不得为空');
        }
        if (empty($permanent_code)) {
            throw new Exception('permanent_code不得为空');
        }
        if (empty($corp_id)) {
            throw new Exception('corp_id不得为空');
        }
        $query  = [
            'suite_access_token' => $suite_access_token,
        ];
        $body   = [
            'auth_corpid'    => $corp_id,
            'permanent_code' => $permanent_code,
        ];
        $result = HttpHelper::postJson(self::GetAuthInfo, $query, [], $body);
        return $this->parseResponse($result, true);
    }

    /**
     * 获取企业凭证
     * https://developer.work.weixin.qq.com/document/path/90605
     * @param string $suite_access_token 本类 getSuiteToken 方法获取到的suite_access_token令牌
     * @param string $permanent_code 本类 getPermanentCode 方法获取到的permanent_code，该值可以永久存起来
     * @param string $corp_id 授权企业的企业id
     * @return array 不抛异常则一定能返回数组，关键结构['access_token' => '', 'expires_in' => '']，调用失败或错误抛出Exception异常
     * @throws Exception
     */
    public function getCorpToken(string $suite_access_token, string $permanent_code, string $corp_id): array
    {
        if (empty($suite_access_token)) {
            throw new Exception('suite_access_token不得为空');
        }
        if (empty($permanent_code)) {
            throw new Exception('permanent_code不得为空');
        }
        if (empty($corp_id)) {
            throw new Exception('corp_id不得为空');
        }
        $query  = [
            'suite_access_token' => $suite_access_token,
        ];
        $body   = [
            'auth_corpid'    => $corp_id,
            'permanent_code' => $permanent_code,
        ];
        $result = HttpHelper::postJson(self::GetCorpToken, $query, [], $body);
        return $this->parseResponse($result, true);
    }

    /**
     * 获取应用的管理员列表
     * https://developer.work.weixin.qq.com/document/path/90606
     * @param string $suite_access_token 本类 getSuiteToken 方法获取到的suite_access_token令牌
     * @param string $agent_id 授权企业的应用id
     * @param string $corp_id 授权企业的企业id
     * @return array
     * @throws Exception
     */
    public function getAdminList(string $suite_access_token, string $agent_id, string $corp_id): array
    {
        if (empty($suite_access_token)) {
            throw new Exception('suite_access_token不得为空');
        }
        if (empty($agent_id)) {
            throw new Exception('agent_id不得为空');
        }
        if (empty($corp_id)) {
            throw new Exception('corp_id不得为空');
        }
        $query  = [
            'suite_access_token' => $suite_access_token,
        ];
        $body   = [
            'auth_corpid' => $corp_id,
            'agentid'     => $agent_id,
        ];
        $result = HttpHelper::postJson(self::GetAdminList, $query, [], $body);
        return $this->parseResponse($result, true);
    }

    /**
     * 获取应用二维码
     * https://developer.work.weixin.qq.com/document/path/95430
     * @param string $suite_access_token 本类 getSuiteToken 方法获取到的suite_access_token令牌
     * @param int $style 0：带说明外框的二维码，适合于实体物料，1：带说明外框的二维码，适合于屏幕类，2：不带说明外框（小尺寸），3：不带说明外框（中尺寸），4：不带说明外框（大尺寸）
     * @return array
     * @throws Exception
     */
    public function getAppQrcode(string $suite_access_token, int $style = 4): array
    {
        if (empty($suite_access_token)) {
            throw new Exception('suite_access_token不得为空');
        }
        $query  = [
            'suite_access_token' => $suite_access_token,
        ];
        $body   = [
            'suite_id'    => $this->suite_id,
            'result_type' => 2, // 固定返回二维码图片URL
            'style'       => $style, // 固定返回二维码图片URL
        ];
        $result = HttpHelper::postJson(self::GetAppQrcode, $query, [], $body);
        return $this->parseResponse($result, true);
    }

    /**
     * 明文corpId转换为加密corpId
     * https://developer.work.weixin.qq.com/document/path/95604
     * @param string $provider_access_token 服务商凭证，不是suite_access_token，注意区分
     * @param string $corp_id 待转换的企业id
     * @return array
     * @throws Exception
     */
    public function corpIdToOpenCorpId(string $provider_access_token, string $corp_id): array
    {
        if (empty($provider_access_token)) {
            throw new Exception('provider_access_token不得为空');
        }
        $query  = [
            'provider_access_token' => $provider_access_token,
        ];
        $body   = [
            'corpid' => $corp_id,
        ];
        $result = HttpHelper::postJson(self::CorpIdToOpenCorpId, $query, [], $body);
        return $this->parseResponse($result, true);
    }

    /**
     * 获取授权应用权限详情
     * https://developer.work.weixin.qq.com/document/path/99052
     * @param string $access_token 本类 getCorpToken 方法获取到的access_token令牌，不是suite_access_token，注意区分
     * @return array
     * @throws Exception
     */
    public function getPermissions(string $access_token): array
    {
        if (empty($access_token)) {
            throw new Exception('access_token不得为空');
        }
        $query  = [
            'access_token' => $access_token,
        ];
        $result = HttpHelper::postJson(self::GetPermissions, $query);
        return $this->parseResponse($result, true);
    }

    /**
     * 统一解析调用api的响应
     * @param array $result
     * @param bool $needRespOk
     * @return array
     * @throws Exception
     */
    protected function parseResponse(array $result, bool $needRespOk = false): array
    {
        if ($result['code'] != HttpHelper::StatusOK) {
            throw new Exception('调用api响应http_status状态码异常：' . $result['code']);
        }
        $jsonData = json_decode($result['body'], true);
        if (empty($jsonData)) {
            throw new Exception('调用api响应异常：' . $result['body']);
        }

        // 如果需要深层次解析响应errcode|errmsg则进一步统一判断
        if (!empty($needRespOk)) {
            if (isset($jsonData['errcode']) && $jsonData['errcode'] != self::RespErrCodeOk) {
                $msg = empty($jsonData['errmsg']) ? '' : $jsonData['errmsg'];
                throw new Exception(
                    '调用api响应异常，errcode=' . $jsonData['errcode'] . '，errmsg=' . $msg . '，Body=' . $result['body']
                );
            }
        }

        // array
        return $jsonData;
    }
}
