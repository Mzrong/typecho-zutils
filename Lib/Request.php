<?php

namespace TypechoPlugin\ZUtils\Lib;

/**
 * 一个简易的curl请求封装
 *
 * @author zrong
 * @email zengrong27@gmail.com
 * @link https://zrong.life
 * @time 2023-12-31 09:31
 */
class Request
{
    /**
     * 简易的HTTP请求
     * @param string $url
     * @param array $data
     * @param string $method
     * @return bool|string
     */
    private static function _request(string $url, array $data = [], string $method = "get"): bool|string
    {
        try {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            if ($method == "post") {
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            }
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

            $result = curl_exec($ch);

            curl_close($ch);

            if (curl_errno($ch)) {
                throw new \Exception(curl_error($ch));
            }

            return $result;
        } catch (\Exception) {
            return false;
        }
    }

    /**
     * 简易的get请求
     * @param string $url
     * @return bool|string
     */
    public static function _get(string $url): bool|string
    {
        return self::_request($url);
    }
}
