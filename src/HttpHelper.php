<?php
/**
 * Guzzle请求方法简单封装
 */

namespace jjonline\WxBizSdk;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\Exception\GuzzleException;
use Exception;

class HttpHelper
{
    const StatusContinue           = 100; // RFC 9110, 15.2.1
    const StatusSwitchingProtocols = 101; // RFC 9110, 15.2.2
    const StatusProcessing         = 102; // RFC 2518, 10.1
    const StatusEarlyHints         = 103; // RFC 8297

    const StatusOK                   = 200; // RFC 9110, 15.3.1
    const StatusCreated              = 201; // RFC 9110, 15.3.2
    const StatusAccepted             = 202; // RFC 9110, 15.3.3
    const StatusNonAuthoritativeInfo = 203; // RFC 9110, 15.3.4
    const StatusNoContent            = 204; // RFC 9110, 15.3.5
    const StatusResetContent         = 205; // RFC 9110, 15.3.6
    const StatusPartialContent       = 206; // RFC 9110, 15.3.7
    const StatusMultiStatus          = 207; // RFC 4918, 11.1
    const StatusAlreadyReported      = 208; // RFC 5842, 7.1
    const StatusIMUsed               = 226; // RFC 3229, 10.4.1

    const StatusMultipleChoices   = 300; // RFC 9110, 15.4.1
    const StatusMovedPermanently  = 301; // RFC 9110, 15.4.2
    const StatusFound             = 302; // RFC 9110, 15.4.3
    const StatusSeeOther          = 303; // RFC 9110, 15.4.4
    const StatusNotModified       = 304; // RFC 9110, 15.4.5
    const StatusUseProxy          = 305; // RFC 9110, 15.4.6
    const _                       = 306; // RFC 9110, 15.4.7 (Unused)
    const StatusTemporaryRedirect = 307; // RFC 9110, 15.4.8
    const StatusPermanentRedirect = 308; // RFC 9110, 15.4.9

    const StatusBadRequest                   = 400; // RFC 9110, 15.5.1
    const StatusUnauthorized                 = 401; // RFC 9110, 15.5.2
    const StatusPaymentRequired              = 402; // RFC 9110, 15.5.3
    const StatusForbidden                    = 403; // RFC 9110, 15.5.4
    const StatusNotFound                     = 404; // RFC 9110, 15.5.5
    const StatusMethodNotAllowed             = 405; // RFC 9110, 15.5.6
    const StatusNotAcceptable                = 406; // RFC 9110, 15.5.7
    const StatusProxyAuthRequired            = 407; // RFC 9110, 15.5.8
    const StatusRequestTimeout               = 408; // RFC 9110, 15.5.9
    const StatusConflict                     = 409; // RFC 9110, 15.5.10
    const StatusGone                         = 410; // RFC 9110, 15.5.11
    const StatusLengthRequired               = 411; // RFC 9110, 15.5.12
    const StatusPreconditionFailed           = 412; // RFC 9110, 15.5.13
    const StatusRequestEntityTooLarge        = 413; // RFC 9110, 15.5.14
    const StatusRequestURITooLong            = 414; // RFC 9110, 15.5.15
    const StatusUnsupportedMediaType         = 415; // RFC 9110, 15.5.16
    const StatusRequestedRangeNotSatisfiable = 416; // RFC 9110, 15.5.17
    const StatusExpectationFailed            = 417; // RFC 9110, 15.5.18
    const StatusTeapot                       = 418; // RFC 9110, 15.5.19 (Unused)
    const StatusMisdirectedRequest           = 421; // RFC 9110, 15.5.20
    const StatusUnprocessableEntity          = 422; // RFC 9110, 15.5.21
    const StatusLocked                       = 423; // RFC 4918, 11.3
    const StatusFailedDependency             = 424; // RFC 4918, 11.4
    const StatusTooEarly                     = 425; // RFC 8470, 5.2.
    const StatusUpgradeRequired              = 426; // RFC 9110, 15.5.22
    const StatusPreconditionRequired         = 428; // RFC 6585, 3
    const StatusTooManyRequests              = 429; // RFC 6585, 4
    const StatusRequestHeaderFieldsTooLarge  = 431; // RFC 6585, 5
    const StatusUnavailableForLegalReasons   = 451; // RFC 7725, 3

    const StatusInternalServerError           = 500; // RFC 9110, 15.6.1
    const StatusNotImplemented                = 501; // RFC 9110, 15.6.2
    const StatusBadGateway                    = 502; // RFC 9110, 15.6.3
    const StatusServiceUnavailable            = 503; // RFC 9110, 15.6.4
    const StatusGatewayTimeout                = 504; // RFC 9110, 15.6.5
    const StatusHTTPVersionNotSupported       = 505; // RFC 9110, 15.6.6
    const StatusVariantAlsoNegotiates         = 506; // RFC 2295, 8.1
    const StatusInsufficientStorage           = 507; // RFC 4918, 11.5
    const StatusLoopDetected                  = 508; // RFC 5842, 7.2
    const StatusNotExtended                   = 510; // RFC 2774, 7
    const StatusNetworkAuthenticationRequired = 511; // RFC 6585, 6

    /**
     * @var Client
     */
    protected static $guzzleHttpClient;
    protected static $ua = 'Mozilla/5.0 AppleWebKit/537.00 Composer/wx-biz-sdk';

    /**
     * 执行guzzleHttp的请求方法，与guzzleHttp参数非常类似
     * @param string $method 请求方式
     * @param string $api 请求的api|guzzleHttp原生传入请求的URL，因为继承类需要优先设置baseUrl固api即可
     * @param array $options [guzzleHttp数组形式的参数]
     * @return array
     * @throws Exception
     */
    public static function request(string $method, string $api = '', array $options = [])
    {
        if (is_null(self::$guzzleHttpClient)) {
            self::$guzzleHttpClient = new Client(
                [
                    'base_uri'         => '',
                    'timeout'          => 30,    // 建立起连接后等待数据返回的会超时时间--单位：秒
                    'connect_timeout'  => 5,     // 建立连接超时时间--单位：秒
                    'force_ip_resolve' => 'v4',  // 强制使用ipV4协议
                    'http_errors'      => false, // http非200状态不抛出异常
                    'allow_redirects'  => false, // http重定向不执行
                    'decode_content'   => false, // 是否解码结果集
                    'headers'          => [
                        'user-agent' => $_SERVER['HTTP_USER_AGENT'] ?? static::$ua,
                        'Referer'    => 'https://packagist.org/packages/jjonline/wx-biz-sdk',
                    ],
                ]
            );
        }
        // 将不标准的GuzzleException转换为Exception
        try {
            $response = self::$guzzleHttpClient->request($method, ltrim($api, '/'), $options);
        } catch (GuzzleException $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
        // 处理结果集
        $result           = [];
        $result['code']   = $response->getStatusCode();
        $result['header'] = $response->getHeaders();
        $result['body']   = $response->getBody()->getContents();

        // 返回统一的数组结果集
        return $result;
    }

    /**
     * 执行get请求
     * @param string $api 请求的api
     * @param array $query Query的数组键值对，即用于url中的get变量
     * @param array $header 发送请求中的header数组键值对
     * @return array  ['code'=>,'header'=>,'body'=>]
     * @throws Exception
     */
    public static function get(string $api, array $query = [], array $header = []): array
    {
        return self::request('GET', $api, ['headers' => $header, 'query' => $query]);
    }

    /**
     * 默认post请求用于发送application/x-www-form-urlencoded形式的表单数据-即post方法为postFormFiled方法的别名
     * @param string $api 请求地址，完整的url
     * @param array $query 需附带在url中的键值对
     * @param array $header post提交时需附带在header中的键值对
     * @param array $body post提交的键值对数组
     * @return array
     * @throws Exception
     */
    public static function post(string $api, array $query = [], array $header = [], array $body = []): array
    {
        return self::postFormFiled($api, $query, $header, $body);
    }

    /**
     * 执行post发送application/x-www-form-urlencoded形式的表单数据
     * @param string $api 请求的api
     * @param array $query Query的数组键值对，即用于url中的get变量
     * @param array $header 发送请求中的header数组键值对
     * @param array $body 发送的body参数数组
     * body参数的格式：key-value形式即可
     * [
     *     'field_name1'=>'field_value1',
     *     'field_name2'=>'field_value2',
     * ]
     * @return array  ['code'=>,'header'=>,'body'=>]
     * @throws Exception
     */
    public static function postFormFiled(string $api, array $query = [], array $header = [], array $body = []): array
    {
        return self::request('POST', $api, [
            'headers'     => $header,
            'query'       => $query,
            'form_params' => $body
        ]);
    }

    /**
     * put发送表单、putFormFiled的别名
     * @param string $api 请求的api
     * @param array $query Query的数组键值对，即用于url中的get变量
     * @param array $header 发送请求中的header数组键值对
     * @param array $body 发送的body参数数组
     * body参数的格式：key-value形式即可
     * [
     *     'field_name1'=>'field_value1',
     *     'field_name2'=>'field_value2',
     * ]
     * @return array  ['code'=>,'header'=>,'body'=>]
     * @throws Exception
     */
    public static function put(string $api, array $query = [], array $header = [], array $body = []): array
    {
        return self::putFormFiled($api, $query, $header, $body);
    }

    /**
     * 执行put发送application/x-www-form-urlencoded形式的表单数据
     * @param string $api 请求的api
     * @param array $query Query的数组键值对，即用于url中的get变量
     * @param array $header 发送请求中的header数组键值对
     * @param array $body 发送的body参数数组
     * body参数的格式：key-value形式即可
     * [
     *     'field_name1'=>'field_value1',
     *     'field_name2'=>'field_value2',
     * ]
     * @return array  ['code'=>,'header'=>,'body'=>]
     * @throws Exception
     */
    public static function putFormFiled(string $api, array $query = [], array $header = [], array $body = []): array
    {
        return self::request('PUT', $api, [
            'headers'     => $header,
            'query'       => $query,
            'form_params' => $body
        ]);
    }

    /**
     * 执行post发送multipart/form-data形式文件
     * @param string $api 请求的api
     * @param array $query Query的数组键值对，即用于url中的get变量
     * @param array $header 发送请求中的header数组键值对
     * @param array $body 发送的body参数数组
     * body参数必须是以下数组单元构成的多维数组：
     * [
     *  [
     *     'name'     => 'other_file',//必须
     *     'contents' => 'hello',//必须
     *     'filename' => 'filename.txt',//可选
     *     'headers'  => [
     *       'X-Foo' => 'this is an extra header to include'
     *     ]//可选
     *  ]
     * ]
     * @return array  ['code'=>,'header'=>,'body'=>]
     * @throws Exception
     */
    public static function postFormData(string $api, array $query = [], array $header = [], array $body = []): array
    {
        return self::request('POST', $api, [
            'headers'   => $header,
            'query'     => $query,
            'multipart' => $body
        ]);
    }

    /**
     * 执行post发送json字符串body的请求
     * @param string $api 请求的api
     * @param array $query Query的数组键值对，即用于url中的get变量
     * @param array $header 发送请求中的header数组键值对
     * @param array|string $body 发送的body参数数组|json字面量
     * @return array  ['code'=>,'header'=>,'body'=>]
     * @throws Exception
     */
    public static function postJson(string $api, array $query = [], array $header = [], $body = []): array
    {
        // 如果没有设置任何header，则强制设置请求体类型为application/json
        if (empty($header)) {
            $header = [
                'Content-Type' => 'application/json'
            ];
        }
        $param = [
            'headers' => $header,
            'query'   => $query,
        ];
        if (is_array($body)) {
            $param['json'] = $body;
        } else {
            $param['body'] = $body; // 可自定义json编码 例如 JSON_UNESCAPED_UNICODE
        }
        return self::request('POST', $api, $param);
    }

    /**
     * put方式提交json
     * @param string $api 请求的api
     * @param array $query Query的数组键值对，即用于url中的get变量
     * @param array $header 发送请求中的header数组键值对
     * @param array|string $body 发送的body参数数组|json字面量
     * @return array  ['code'=>,'header'=>,'body'=>]
     * @throws Exception
     */
    public static function putJson(string $api, array $query = [], array $header = [], $body = []): array
    {
        $param = [
            'headers' => $header,
            'query'   => $query,
        ];
        if (is_array($body)) {
            $param['json'] = $body;
        } else {
            $param['body'] = $body; // 可自定义json编码 例如 JSON_UNESCAPED_UNICODE
        }
        return self::request('PUT', $api, $param);
    }

    /**
     * guzzle流式下载保存文件
     * @param string $url 待下载的URL
     * @param string $filePath 本地存储文件绝对路径
     * @return bool
     */
    public static function download(string $url, string $filePath): bool
    {
        try {
            $resource = fopen($filePath, 'w+');
            $client   = new Client(
                [
                    'timeout'          => 600,   // 建立起连接后等待数据返回的会超时时间--单位：秒
                    'connect_timeout'  => 5,     // 建立连接超时时间--单位：秒
                    'force_ip_resolve' => 'v4',  // 强制使用ipV4协议
                    'http_errors'      => false, // http非200状态不抛出异常
                    'allow_redirects'  => false, // http重定向不执行
                    'decode_content'   => false, // 是否解码结果集
                ]
            );
            $response = $client->get($url, [RequestOptions::SINK => $resource]);

            return $response->getStatusCode() == static::StatusOK;
        } catch (GuzzleException $e) {
            return false;
        } catch (Exception $e) {
            return false;
        }
    }
}
