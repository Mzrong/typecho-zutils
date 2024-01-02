<?php

namespace TypechoPlugin\ZUtils;

use TypechoPlugin\ZUtils\Config\Assets;
use Utils\Helper;
use Typecho\Widget;
use Typecho\Widget\Helper\Form;
use Typecho\Widget\Helper\Layout;
use Typecho\Plugin\PluginInterface;
use Typecho\Plugin as TypechoPlugin;
use TypechoPlugin\ZUtils\Lib\Methods;
use TypechoPlugin\ZUtils\Lib\Geetest;
use TypechoPlugin\ZUtils\Lib\QSdk;
use TypechoPlugin\ZUtils\Config\QConfig;
use TypechoPlugin\ZUtils\Config\GeeTestConfig;
use TypechoPlugin\ZUtils\Config\SettingConfig;

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
        // 注入方法
        TypechoPlugin::factory("admin/login.php")->qqLogin = [__CLASS__, "qqLogin"];
        TypechoPlugin::factory("admin/login.php")->googleVerify = [__CLASS__, "googleVerify"];
        TypechoPlugin::factory("Widget\Feedback")->comment = [__CLASS__, "addCommentVerify"];
        TypechoPlugin::factory("Widget\Archive")->___geetest = [__CLASS__, "geetest"];

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
        $param = $_GET;

        if (isset($param["action"])) {
            $action = $param["action"];
            if ($action == "addQqCallbackRoute") {
                self::addQqCallbackRoute();
            }
        }

        $methods = new Methods();
        $configData = $methods->getPluginConfigData("plugin:ZUtils");
        if (!empty($configData)) {
            $configData = unserialize($configData["value"]);
        } else {
            $configData = [];
        }

        Assets::js($form, $configData);
        Assets::css($form);

        $navbar = new Layout("div", ["class" => "--nav"]);

        $configPanel = new Layout("div", ["class" => "--nav-item active", "data-hook" => "setting-config"]);
        $configPanel->html("设置");
        $configPanel->appendTo($navbar);

        $qqConfigPanel = new Layout("div", ["class" => "--nav-item", "data-hook" => "qq-config"]);
        $qqConfigPanel->html("QQ互联配置");
        $qqConfigPanel->appendTo($navbar);

        $qqConfigPanel = new Layout("div", ["class" => "--nav-item", "data-hook" => "geetest-config"]);
        $qqConfigPanel->html("极验验证配置");
        $qqConfigPanel->appendTo($navbar);

        $form->addItem($navbar);

        QConfig::render($form);
        GeeTestConfig::render($form);
        SettingConfig::render($form);

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
    public static function qqLogin()
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
    public static function googleVerify()
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
            Widget::widget("Widget\Notice")->set(_t("回调地址不能为空"), "error");
            return;
        }

        if (substr($param["qqredirect"], 0, 4) != "http") {
            Widget::widget("Widget\Notice")->set(_t("回调地址格式不正确"), "error");
            return;
        }

        $uri = parse_url($param["qqredirect"]);

        // 注入QQ登录回调的路由
        Helper::addRoute("qqLoginResponse", $uri["path"], "TypechoPlugin\ZUtils\Route\Route", "qqLoginResponse");

        Widget::widget("Widget\Notice")->set(_t("注入成功！"), "success");
    }

    /**
     * 增加提交评论时的验证
     * @throws \Exception
     */
    public static function addCommentVerify(array $comment): array
    {
        $options = Helper::options()->plugin("ZUtils");
        $status = $options->commentVerify;

        if ($status == "on") {
            if (!Geetest::verify($options->geekey)) {
                throw new \Exception("人机验证不通过");
            }
        }

        return $comment;
    }

    /**
     * 渲染极验按钮
     */
    public static function geetest(): string
    {
        try {
            return Geetest::init();
        } catch (TypechoPlugin\Exception $e) {
            return "<div style='display: none'>". $e->getMessage() ."</div>";
        }
    }
}
