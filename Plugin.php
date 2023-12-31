<?php

namespace TypechoPlugin\ZUtils;

use Typecho\Common;
use Typecho\Widget;
use Typecho\Plugin\PluginInterface;
use Typecho\Plugin as TypechoPlugin;
use TypechoPlugin\ZUtils\Lib\Methods;
use Utils\Helper;
use Typecho\Widget\Helper\Form;
use Typecho\Widget\Helper\Form\Element\Text;
use Typecho\Widget\Helper\Form\Element\Radio;
use Typecho\Widget\Helper\Layout;
use TypechoPlugin\ZUtils\Lib\QSdk;
use Widget\User;

if (phpversion() < 7.4) {
    exit(sprintf("Plugin `Utils` require PHP lgt 7.4. but your PHP version was (%s)", phpversion()));
}

if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

/**
 * <span style="color: red">一些花里胡哨的小玩意</span>
 *
 * @package ZUtils
 * @author zrong
 * @version 0.0.1
 * @link https://zrong.life
 * @time 2023-12-31 08:44
 */
class Plugin implements PluginInterface
{
    /**
     * 启用插件方法,如果启用失败,直接抛出异常
     *
     * @static
     * @access public
     * @return void
     */
    public static function activate()
    {
        TypechoPlugin::factory("admin/login.php")->QQLogin = [__CLASS__, "renderQLoginBtn"];
        TypechoPlugin::factory("admin/login.php")->googleVerify = [__CLASS__, "renderGoogleVerify"];

        // 创建配置表
        $methods = new Methods();
        if (!$methods->checkTable()) {
            $methods->createTable();
        }
    }

    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     *
     * @static
     * @access public
     * @return void
     */
    public static function deactivate()
    {
        // 删除QQ登录回调的路由
        Helper::removeRoute("qqLoginResponse");

        // 删除配置表
        $methods = new Methods();
        $methods->dropTable();
    }

    /**
     * 获取插件配置面板
     *
     * @param Form $form 配置面板
     */
    public static function config(Form $form)
    {
        $user = User::alloc();
        $param = $_GET;

        if (isset($param["action"])) {
            $action = $param["action"];
            switch ($action) {
                case "addQqCallbackRoute":
                    self::addQqCallbackRoute();
                    break;
            }
        }

        /**
         * 真的是出此下策，这屌毛 $form->getValues() 毛都没有返回！操！
         * 这屌毛Helper::options()->plugin("ZUtils")。没有配置文件就直接报错！没有配置好歹你抛出错误也好啊，直接500的
         */
        $methods = new Methods();
        $configData = $methods->getPluginConfigData("plugin:ZUtils");
        if (!empty($configData)) {
            $configData = unserialize($configData["value"]);
        }

        /**
         * 这里向页面注入CSS和js
         */
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
        EOT;
        $scriptTag = new Layout("script");
        $scriptTag->html($jsCode);
        $form->addItem($scriptTag);

        $cssCode = <<<EOT
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
                flex-decoration: row;
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

        $label = new Layout("h2");
        $label->html("QQ互联相关配置");
        $form->addItem($label);

        $doc = new Layout("p");
        $doc->setAttribute("style", "font-size: 15px");
        $doc->html("插件使用文档：<a href='https://zrong.life/archives/1901.html' target='_blank'>https://zrong.life/archives/1901.html</a>");
        $form->addItem($doc);

        $doc = new Layout("p");
        $doc->setAttribute("style", "font-size: 15px");
        $doc->html("QQ互联文档：<a href='https://connect.qq.com/index.html' target='_blank'>https://connect.qq.com/index.html</a>");
        $form->addItem($doc);

        $input = new Text("qqappid", null, null, _t("APP ID"), "QQ互联中网站应用的APP ID");
        $form->addInput($input);

        $input = new Text("qqappkey", null, null, _t("APP Key"), "QQ互联中网站应用的APP Key");
        $form->addInput($input);

        $input = new Text("qqredirect", null, null, _t("授权回调地址"), "授权回调地址。填写完授权回调地址后需要手动点击下方【注册回调路由】按钮向系统注册路由。<br />建议先保存设置再点击【注册回调路由】按钮");
        $form->addInput($input);

        $layout = new Layout("div");
        $layout->setAttribute("class", "--group");

        $regRouteBtn = new Layout("button");
        $regRouteBtn->html("注册回调路由");
        $regRouteBtn->setAttribute("type", "submit");
        $regRouteBtn->setAttribute("class", "--primary-btn");
        $regRouteBtn->setAttribute("formaction", Common::url('/options-plugin.php?config=ZUtils&action=addQqCallbackRoute', Helper::options()->adminUrl));
        $regRouteBtn->appendTo($layout);

        $form->addItem($layout);

        $label = new Layout("h2");
        $label->html("后台登录设置");
        $form->addItem($label);

        $radio = new Radio('qq_login', [
            'on' => '开启',
            'off' => '关闭'
        ], 'off', _t("使用QQ登录后台"), null);
        $form->addInput($radio);

        $label = new Layout("h3");
        $label->setAttribute("style", "font-size: 14px");
        $label->html("QQ绑定状态");
        $form->addItem($label);

        $layout = new Layout("div");
        $layout->setAttribute("class", "--group");

        $bindStatus = $methods->checkQQBindStatus($user->uid);

        $bindBtn = new Layout("div");
        $bindBtn->html($bindStatus ? "重新绑定" : "绑定QQ");
        $bindBtn->setAttribute("class", "--primary-btn");
        $bindBtn->setAttribute("onclick", "openBindFrame()");
        $bindBtn->appendTo($layout);

        $bindStatusLabel = new Layout("span");
        $bindStatusLabel->setAttribute("class", $bindStatus ? "--success" : "--danger");
        $bindStatusLabel->html($bindStatus ? "已绑定" : "未绑定");
        $bindStatusLabel->appendTo($layout);

        $form->addItem($layout);

        $radio = new Radio('google_verify', [
            'on' => '开启',
            'off' => '关闭'
        ], 'off', _t("开启谷歌验证"), "账号密码登录将会使用你的谷歌令牌");
        $form->addInput($radio);
    }

    /**
     * 个人用户的配置面板
     *
     * @param Form $form
     */
    public static function personalConfig(Form $form)
    {

    }

    /**
     * 渲染QQ登录按钮
     * @throws TypechoPlugin\Exception
     */
    public static function renderQLoginBtn()
    {
        $options = Helper::options()->plugin("ZUtils");
        if ($options->qq_login == "on") {

            if (empty($options->qqappid) || empty($options->qqappkey) || empty($options->qqredirect)) {
                echo "<p style='color: red; margin-top: 15px'>启用QQ登录失败。参数配置不完整，请至插件内配置正确的参数</p>";
            } else {
                $config = [
                    "client_id" => $options->qqappid,
                    "redirect_uri" => $options->qqredirect,
                ];
                $url = QSdk::getAuthUrl($config);

                echo <<<EOT
                        <style rel="stylesheet">
                            .--btn-group { display: flex; flex-direction: row; align-items: center; justify-content: center; margin-top: 25px }
                            .--btn { cursor: pointer; display: flex; flex-direction: column; align-items: center; justify-content: center }
                            .--btn > div { background-image: url('https://cdn.zrong.life/2023/12/31/c0c6c6eb3e8206189b2bf538af4757f0.png'); width: 24px; height: 24px; background-size: 100% }
                            .--btn > span { font-size: 13px; color: #999; margin-top: 4px }
                            .--btn:hover { text-decoration: none; }
                        </style>
                        <div class="--btn-group">
                            <a class="--btn" title="QQ登录" href="{$url}">
                                <div></div>
                                <span>QQ登录</span>
                            </a>
                        </div>
                    EOT;
            }
        }
    }

    /**
     * 渲染谷歌令牌输入框
     * @throws TypechoPlugin\Exception
     */
    public static function renderGoogleVerify()
    {
        $status = Helper::options()->plugin("ZUtils")->google_verify;
        $placeholder = _t("谷歌令牌");
        if ($status == "on") {
            echo <<<EOT
                <p>
                    <label for="password" class="sr-only">{$placeholder}</label>
                    <input type="text" id="google_code" name="google_code" class="text-l w-100" placeholder="{$placeholder}" />
                </p>
            EOT;
        }
    }

    /**
     * 注入QQ登录回调的路由
     */
    private static function addQqCallbackRoute()
    {
        $param = $_POST;

        if (empty($param['qqredirect'])) {
            Widget::widget("Widget_Notice")->set(_t("回调地址不能为空"), "error");
            return;
        }

        if (!str_starts_with($param["qqredirect"], "http")) {
            Widget::widget("Widget_Notice")->set(_t("回调地址格式不正确"), "error");
            return;
        }

        $uri = parse_url($param["qqredirect"]);

        // 注入QQ登录回调的路由
        Helper::addRoute("qqLoginResponse", $uri["path"], "TypechoPlugin\ZUtils\Route\Route", "qqLoginResponse");

        Widget::widget("Widget_Notice")->set(_t("注入成功！"), "success");
    }
}
