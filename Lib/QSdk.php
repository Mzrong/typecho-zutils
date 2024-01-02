<?php

namespace TypechoPlugin\ZUtils\Lib;

use Typecho\Cookie;

/**
 * QQ互联的SDK
 *
 * @author zrong
 * @email zengrong27@gmail.com
 * @link https://zrong.life
 * @time 2023-12-31 09:31
 */
class QSdk
{
    /**
     * 获取授权URL
     * @param array $config
     * @return string
     */
    public static function getAuthUrl(array $config): string
    {
        $state = Utils::random();

        Cookie::set("stateStr", $state);

        $config = array_merge(
            $config,
            [
                "response_type" => "code",
                "state" => $state
            ]
        );
        return "https://graph.qq.com/oauth2.0/authorize?" . http_build_query($config);
    }

    /**
     * 获取QQ用户的openid
     * @throws \Exception
     */
    public static function getQQOpenId(array $config = [])
    {
        $url = "https://graph.qq.com/oauth2.0/token?" . http_build_query($config);

        $result = Request::_get($url);

        if ($result === false) {
            throw new \Exception("出现错误");
        }

        $result = json_decode($result, true);

        if (isset($result["code"])) {
            throw new \Exception($result["msg"]);
        }

        return $result;
    }

    /**
     * 进行QQ授权登录
     * @param string $code
     */
    public static function doQQAuth(string $code, mixed $options)
    {
        try {
            $config = [
                "grant_type" => "authorization_code",
                "client_id" => $options->qqappid,
                "client_secret" => $options->qqappkey,
                "code" => $code,
                "redirect_uri" => $options->qqredirect,
                "fmt" => "json",
                "need_openid" => 1
            ];

            return self::getQQOpenId($config);
        } catch (\Exception $e) {
            return false;
        }
    }
}
