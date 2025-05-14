<?php
/**
 * 消息发送器：发送消息、模板消息方法封装
 * 文本/文本卡片/图文/图文（mpnews）/任务卡片/小程序通知/模版消息/模板卡片消息这八种消息类型的部分字段支持
 * https://developer.work.weixin.qq.com/document/path/90372
 * https://developer.work.weixin.qq.com/document/path/94515
 */

namespace jjonline\WxBizSdk;

use Exception;

class MessageManager
{
    const RespErrCodeOk = 0; // 调用api响应json中errcode字段值成功标量
    const MessageSend   = 'https://qyapi.weixin.qq.com/cgi-bin/message/send';

    /**
     * 发送模板消息
     * https://developer.work.weixin.qq.com/document/path/94515
     * @param string $template_id 模板ID
     * @param string $access_token 第三方应用accessToken，SuiteAccess类getCorpToken方法获取
     * @param array $params 模板消息内容数组，结构与官方接口文档一致
     * @return array
     * @throws Exception
     */
    public function sendTemplateMsg(string $template_id, string $access_token, array $params): array
    {
        if (empty($template_id)) {
            throw new Exception('template_id不得为空');
        }
        if (empty($access_token)) {
            throw new Exception('access_token不得为空');
        }
        if (empty($params)) {
            throw new Exception('params不得为空');
        }

        $params['msgtype']                     = 'template_msg'; // 固定值
        $params['template_msg']['template_id'] = $template_id; // 覆盖替换

        $query  = [
            'access_token' => $access_token,
        ];
        $result = HttpHelper::postJson(self::MessageSend, $query, [], json_encode($params, JSON_UNESCAPED_UNICODE));
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
