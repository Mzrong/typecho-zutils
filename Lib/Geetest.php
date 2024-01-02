<?php

namespace TypechoPlugin\ZUtils\Lib;

use Utils\Helper;

/**
 * 极验SDK
 *
 * @author zrong
 * @email zengrong27@gmail.com
 * @link https://zrong.life
 * @time 2024-01-02 12:38
 */
class Geetest
{
    private static string $verifyUrl = "http://gcaptcha4.geetest.com/validate";

    private static string $jsSdk = "https://static.geetest.com/v4/gt4.js";

    /**
     * 初始化极验验证SDK
     * @return string
     * @throws \Typecho\Plugin\Exception
     */
    public static function init(): string
    {
        $options = Helper::options()->plugin("ZUtils");

        $captchaId = $options->geeid;
        if ($options->commentVerify == "off" || empty($captchaId) || empty($options->geekey)) {
            $html = "";
        } else {
            $jsSdk = self::$jsSdk;

            $selector = $options->geesubmitbtn;

            $product = empty($options->geeproduct) ? "float" : $options->geeproduct;

            $nativeButton = [
                "width" => "260px",
                "height" => "50px"
            ];
            if (!empty($options->geenativebuttonh) && !empty($options->geenativebuttonw)) {
                $nativeButton = [
                    "width" => $options->geenativebuttonh,
                    "height" => $options->geenativebuttonw
                ];
            }
            $nativeButton = json_encode($nativeButton);

            $language = empty($options->geelanguage) ? "zho" : $options->geelanguage;

            $mask = [
                "outside" => $options->geemaskclose == "on",
                "bgColor" => empty($options->geemaskbgcolor) ? "#0000004d" : $options->geemaskbgcolor
            ];
            $mask = json_encode($mask);

            $submitUrl = empty($options->geecommenturl) ? "comment" : $options->geecommenturl;

            $html = <<<EOT
                <div id="geetestCaptcha" style="{$options->geeconstyle}"></div>
                <input type="hidden" name="gee_captcha_id" value=""/>
                <input type="hidden" name="gee_captcha_output" value=""/>
                <input type="hidden" name="gee_gen_time" value=""/>
                <input type="hidden" name="gee_lot_number" value=""/>
                <input type="hidden" name="gee_pass_token" value=""/>

                <script src="{$jsSdk}" type="text/javascript"></script>
                <script type="text/javascript">
                    let captchaObj = undefined;
                    const selector = "{$selector}";
                    const product = "{$product}";
                    const nativeButton = JSON.parse('{$nativeButton}');
                    const mask = JSON.parse('{$mask}');
                    const submitUrl = "{$submitUrl}";
                    const hideSuccess = "{$options->geehidesuccess}";
                                                                        
                    const captchaIdEle = document.querySelector("input[name='gee_captcha_id']");
                    const captchaOutputEle = document.querySelector("input[name='gee_captcha_output']");
                    const captchaGenTimeEle = document.querySelector("input[name='gee_gen_time']");
                    const captchaLotNumberEle = document.querySelector("input[name='gee_lot_number']");
                    const captchaPassTokenEle = document.querySelector("input[name='gee_pass_token']");
                                                                        
                    initGeetest4({
                        captchaId: "{$captchaId}",
                        product: product,
                        language: "{$language}",
                        nativeButton: nativeButton,
                        mask: mask,
                        hideSuccess: hideSuccess === "on"
                    }, function (captcha) {
                        captcha.appendTo("#geetestCaptcha");
                                            
                        captcha.onReady(() => {
                            captchaObj = captcha;
                        });
                        
                        captcha.onSuccess(() => {
                            const result = captcha.getValidate();
                            captchaIdEle.value = result.captcha_id;
                            captchaOutputEle.value = result.captcha_output;
                            captchaGenTimeEle.value = result.gen_time;
                            captchaLotNumberEle.value = result.lot_number;
                            captchaPassTokenEle.value = result.pass_token;
                            
                            if (product === "bind") {
                                document.querySelector(selector).click();
                            }
                        });
                    });
                    
                    function addClickListen() {
                        if (selector) {
                            if (!document.querySelector(selector)) {
                                console.error("没有找到表单提交按钮的选择器，请确认选择器是否有误");
                                return;
                            }
                            document.querySelector(selector).addEventListener("click", (e) => {
                                if (
                                    !captchaIdEle.value ||
                                    !captchaOutputEle.value ||
                                    !captchaGenTimeEle.value ||
                                    !captchaLotNumberEle.value ||
                                    !captchaPassTokenEle.value
                                ) {
                                    e.preventDefault();
                                    if (product === "bind") {
                                        if (captchaObj) {
                                            captchaObj.showCaptcha();
                                        }
                                    } else {
                                        alert("请先完成人机验证哦~~~");
                                        return false;
                                    }
                                }
                            });                        
                        } else {
                            console.error("没有配置表单提交按钮的选择器，请到插件内设置");
                        }
                    }
                    
                    if (document.querySelector(selector)) {
                        addClickListen();
                    } else {
                        document.addEventListener("DOMContentLoaded", () => {
                            addClickListen();
                        });
                    }
                    
                    function perfObserver(list) {
                        const entries = getNetworkRequest(list.getEntriesByType("resource"))
                        if (entries.length > 0) {
                            const entry = entries[0];
                            if (entry.name.indexOf(submitUrl) > -1) {
                                if (product !== "bind") {
                                    captchaObj.reset();
                                }
                                
                                captchaIdEle.value = "";
                                captchaOutputEle.value = "";
                                captchaGenTimeEle.value = "";
                                captchaLotNumberEle.value = "";
                                captchaPassTokenEle.value = "";
                            }
                        }
                    }
                    
                    function getNetworkRequest(entries) {
                        return entries.filter(entry => {
                            return entry.initiatorType === "xmlhttprequest";
                        })
                    }
                    
                    const observer = new PerformanceObserver(perfObserver);
                    observer.observe({entryTypes: ["resource"]}); 
                </script>
            EOT;
        }

        return $html;
    }

    /**
     * 极验二次校验
     * @param string $key
     * @return bool
     */
    public static function verify(string $key): bool
    {
        try {
            $param = $_POST;

            $captcha_id = $param["gee_captcha_id"];
            $captcha_output = $param["gee_captcha_output"];
            $gen_time = $param["gee_gen_time"];
            $lot_number = $param["gee_lot_number"];
            $pass_token = $param["gee_pass_token"];

            if (
                empty($captcha_id) ||
                empty($captcha_output) ||
                empty($gen_time) ||
                empty($lot_number) ||
                empty($pass_token)
            ) {
                throw new \Exception("参数不完整");
            }

            $requestData = [
                "captcha_id" => $captcha_id,
                "lot_number" => $lot_number,
                "captcha_output" => $captcha_output,
                "pass_token" => $pass_token,
                "gen_time" => $gen_time
            ];

            $signToken = hash_hmac("sha256", $lot_number, $key);
            $requestData["sign_token"] = $signToken;
            $result = Request::_post(self::$verifyUrl, $requestData);

            if ($result === false) {
                throw new \Exception("验证失败");
            }

            $result = json_decode($result, true);

            if ($result["result"] === "success" && $result["status"] === "success") {
                return true;
            }

            return false;
        } catch (\Exception $e) {
            return false;
        }
    }
}

