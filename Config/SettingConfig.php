<?php

namespace TypechoPlugin\ZUtils\Config;

use Widget\User;
use Typecho\Widget\Helper\Form;
use Typecho\Widget\Helper\Layout;
use Typecho\Widget\Helper\Form\Element\Radio;
use TypechoPlugin\ZUtils\Lib\Methods;

/**
 * 配置开关表单
 *
 * @author zrong
 * @email zengrong27@gmail.com
 * @link https://zrong.site
 * @time 2024-01-02 12:38
 */
class SettingConfig
{
    private static string $className = "setting-config-hook";

    public static function render(Form $form): void
    {
        $user = User::alloc();
        $methods = new Methods();

        $doc = new Layout("p", ["class" => self::$className, "style" => "font-size: 15px"]);
        $doc->html("插件使用文档：<a href='https://zrong.site/archives/cz1877.html' target='_blank'>https://zrong.site/archives/cz1877.html</a>");
        $form->addItem($doc);

        $label = new Layout("h2", ["class" => self::$className]);
        $label->html("后台登录设置");
        $form->addItem($label);

        $radio = new Radio('qq_login', [
            'on' => '开启',
            'off' => '关闭'
        ], 'off', _t("使用QQ登录后台"), null);
        $radio->setAttribute("class", self::$className . " typecho-option");
        $form->addInput($radio);

        $layout = new Layout("div", ["class" => self::$className . " --item"]);

        $label = new Layout("h3");
        $label->setAttribute("style", "font-size: 14px");
        $label->html("QQ绑定状态");
        $label->appendTo($layout);

        $bindStatus = $methods->checkQQBindStatus($user->uid);

        $subLayout = new Layout("div", ["class" => "--group"]);

        $bindBtn = new Layout("div");
        $bindBtn->html($bindStatus ? "重新绑定" : "绑定QQ");
        $bindBtn->setAttribute("class", "--primary-btn");
        $bindBtn->setAttribute("onclick", "openBindFrame()");
        $bindBtn->appendTo($subLayout);

        $bindStatusLabel = new Layout("span");
        $bindStatusLabel->setAttribute("class", $bindStatus ? "--success" : "--danger");
        $bindStatusLabel->html($bindStatus ? "已绑定" : "未绑定");
        $bindStatusLabel->appendTo($subLayout);

        $subLayout->appendTo($layout);

        $form->addItem($layout);

        $radio = new Radio('google_verify', [
            'on' => '开启',
            'off' => '关闭'
        ], 'off', _t("开启谷歌验证"), "账号密码登录将会使用你的谷歌令牌");
        $radio->setAttribute("class", self::$className . " typecho-option");
        $form->addInput($radio);

        $label = new Layout("h2", ["class" => self::$className]);
        $label->html("评论安全设置");
        $form->addItem($label);

        $radio = new Radio("commentVerify", ["on" => "开启", "off" => "禁用"], "off", "开启评论安全验证", "目前仅支持极验验证");
        $radio->setAttribute("class", self::$className . " typecho-option");
        $form->addInput($radio);
    }
}
