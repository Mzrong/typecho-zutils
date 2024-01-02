<?php

namespace TypechoPlugin\ZUtils\Config;

use Typecho\Widget\Helper\Form;
use Typecho\Widget\Helper\Layout;
use TypechoPlugin\ZUtils\Lib\QSdk;

/**
 * 一些静态资源动态注入
 *
 * @author zrong
 * @email zengrong27@gmail.com
 * @link https://zrong.life
 * @time 2024-01-02 12:51
 */
class Assets
{
    private static string $jqCdn = "https://cdn.bootcdn.net/ajax/libs/jquery/3.7.1/jquery.min.js";

    /**
     * 页面的js
     * @param Form $form
     * @param array $configData
     */
    public static function js(Form $form, array $configData)
    {
        $jq = new Layout("script", ["src" => self::$jqCdn, "type" => "text/javascript"]);
        $form->addItem($jq->html("console.log('ZUtils')"));

        $authUrl = "";
        if (!empty($configData["qqappid"]) && !empty($configData["qqappkey"]) && !empty($configData["qqredirect"])) {
            $config = [
                "client_id" => $configData["qqappid"],
                "redirect_uri" => $configData["qqredirect"],
            ];
            $authUrl = QSdk::getAuthUrl($config);
        }

        $jsCode = <<<EOT
            function openBindFrame() {
                let authUrl = '${authUrl}';
                if (!authUrl) { 
                    alert('请先填写相关配置'); 
                    return; 
                }
                
                window.open(authUrl, '_blank', 'width=700,height=900');
            }

            $(document).ready(function() {
                $(".--nav-item").click(function() {
                    let currenHookClass = $(".--nav-item.active").attr("data-hook") + "-hook";
                    $("."+currenHookClass).hide();
                    let hookClass = $(this).attr("data-hook") + "-hook";
                    $("."+hookClass).show();
                    $(this).addClass("active").siblings().removeClass("active");
                });
            });
        EOT;

        $scriptTag = new Layout("script");
        $scriptTag->html($jsCode);
        $form->addItem($scriptTag);
    }

    /**
     * 页面的css
     * @param Form $form
     */
    public static function css(Form $form)
    {
        $cssCode = <<<EOT
            .--nav {
                display: flex;
                flex-direction: row;
                margin-bottom: 30px;
                padding-bottom: 10px;
                border-bottom: 1px solid #cdcdcd;
            }

            .--nav-item {
                padding: 0 14px;
                font-size: 14px;
                color: #000;
                border-radius: 4px;
                line-height: 32px;
                cursor: pointer;
                margin-right: 15px;
            }

            .--nav-item:hover {
                background-color: #ccc;
            }

            .--nav-item.active {
                background-color: rgb(22, 93, 255);
                color: #fff;
            }

            .--success {
                color: rgb(0,180,42);
                font-size: 15px;
                font-weight: bold;
            }
            
            .--danger {
                color: rgb(245,63,63);
                font-size: 15px;
                font-weight: bold;
            } 
            
            .--group {
                display: flex;
                flex-direction: row;
                align-items: center;
            }
            
            .--primary-btn {
                display: inline-block; 
                cursor: pointer; 
                line-height: 32px; 
                padding: 0 15px; 
                font-size: 14px; 
                background-color: rgb(22, 93, 255);
                color: #fff;
                transition: all .1s cubic-bezier(0,0,1,1);
                margin-right: 20px;
                border: none;
                outline: none;
            }
            
            .--primary-btn:hover {
                background-color: rgb(64, 128, 255); 
            }
            
            .--primary-btn:active {
                background-color: rgb(14,66,210);
            }
        EOT;

        $styleTag = new Layout("style");
        $styleTag->html($cssCode);
        $form->addItem($styleTag);
    }
}
