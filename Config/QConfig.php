<?php

namespace TypechoPlugin\ZUtils\Config;

use Utils\Helper;
use Typecho\Common;
use Typecho\Widget\Helper\Form;
use Typecho\Widget\Helper\Layout;
use Typecho\Widget\Helper\Form\Element\Text;


/**
 * QQ互联配置表单
 *
 * @author zrong
 * @email zengrong27@gmail.com
 * @link https://zrong.site
 * @time 2024-01-02 12:37
 */
class QConfig
{
    private static string $className = "qq-config-hook";

    public static function render(Form $form): void
    {
        $label = new Layout("h2", ["class" => self::$className, "style" => "display: none"]);
        $label->html("QQ互联相关配置");
        $form->addItem($label);

        $doc = new Layout("p", ["class" => self::$className, "style" => "display: none; font-size: 15px"]);
        $doc->html("QQ互联文档：<a href='https://connect.qq.com/index.html' target='_blank'>https://connect.qq.com/index.html</a>");
        $form->addItem($doc);

        $input = new Text("qqappid", null, null, _t("APP ID"));
        $input->setAttribute("class", self::$className . " typecho-option");
        $input->setAttribute("style", "display: none");
        $input->input->setAttribute("placeholder", "QQ互联中网站应用的APP ID");
        $input->addRule("alphaNumeric", "APP ID只能包含数字和字母");
        $form->addInput($input);

        $input = new Text("qqappkey", null, null, _t("APP Key"));
        $input->setAttribute("class", self::$className . " typecho-option");
        $input->setAttribute("style", "display: none");
        $input->addRule("alphaNumeric", "APP KEY只能包含数字和字母");
        $input->input->setAttribute("placeholder", "QQ互联中网站应用的APP Key");
        $form->addInput($input);

        $input = new Text("qqredirect", null, null, _t("授权回调地址"), "填写完授权回调地址后需要手动点击下方【注册回调路由】按钮向系统注册路由。<br />建议先保存设置再点击【注册回调路由】按钮");
        $input->setAttribute("class", self::$className . " typecho-option");
        $input->setAttribute("style", "display: none");
        $input->input->setAttribute("placeholder", "授权回调地址");
        $input->addRule("url", "回调地址不是一个正确的url地址");
        $form->addInput($input);


        $layout = new Layout("div", ["class" => self::$className . " --group", "style" => "display: none; margin-bottom: 40px"]);
        $regRouteBtn = new Layout("button");
        $regRouteBtn->html("注册回调路由");
        $regRouteBtn->setAttribute("type", "submit");
        $regRouteBtn->setAttribute("class", "--primary-btn");
        $regRouteBtn->setAttribute("formaction", Common::url('/options-plugin.php?config=ZUtils&action=addQqCallbackRoute', Helper::options()->adminUrl));
        $regRouteBtn->appendTo($layout);
        $form->addItem($layout);
    }
}
