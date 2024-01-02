<?php

namespace TypechoPlugin\ZUtils\Config;

use Typecho\Widget\Helper\Form;
use Typecho\Widget\Helper\Layout;
use Typecho\Widget\Helper\Form\Element\Text;
use Typecho\Widget\Helper\Form\Element\Radio;
use Typecho\Widget\Helper\Form\Element\Select;

/**
 * 极验配置表单
 *
 * @author zrong
 * @email zengrong27@gmail.com
 * @link https://zrong.life
 * @time 2024-01-02 12:36
 */
class GeeTestConfig
{
    private static string $className = "geetest-config-hook";

    public static function render(Form $form): void
    {
        $label = new Layout("h2", ["class" => self::$className, "style" => "display: none"]);
        $label->html("极验验证相关配置");
        $form->addItem($label);

        $doc = new Layout("p", ["class" => self::$className, "style" => "display: none; font-size: 15px"]);
        $doc->html("官方文档：<a href='https://docs.geetest.com/gt4/apirefer/api/web' target='_blank'>https://docs.geetest.com/gt4/apirefer/api/web</a><br />这里只配置了一些常用的配置项，如有更多需求，请按照官方文档修改");
        $form->addItem($doc);

        $input = new Text("geeid", null, null, "geetest ID");
        $input->input->setAttribute("placeholder", "极验ID");
        $input->setAttribute("class", "typecho-option " . self::$className);
        $input->setAttribute("style", "display: none");
        $form->addInput($input);

        $input = new Text("geekey", null, null, "geetest KEY");
        $input->input->setAttribute("placeholder", "极验KEY");
        $input->setAttribute("class", "typecho-option " . self::$className);
        $input->setAttribute("style", "display: none");
        $form->addInput($input);

        $radio = new Radio("geeproduct", ["float" => "浮动式(float)", "popup" => "弹出式(popup)", "bind" => "隐藏按钮类型(bind)"], "float", "验证展示形式");
        $radio->setAttribute("class", "typecho-option " . self::$className);
        $radio->setAttribute("style", "display: none");
        $form->addInput($radio);

        $input = new Text("geesubmitbtn", null, "", "表单提交按钮的选择器");
        $input->description("需要填写提交表单提交按钮的css选择器来拦截点击事件！最好为ID选择器，若没有，请尽可能填写能保证全局唯一的选择器");
        $input->input->setAttribute("placeholder", "提交按钮css选择器");
        $input->setAttribute("class", "typecho-option " . self::$className);
        $input->setAttribute("style", "display: none");
        $form->addInput($input);

        $input = new Text("geecommenturl", null, "comment", "评论提交的url");
        $input->input->setAttribute("placeholder", "评论提交的url");
        $input->description("<span style='color: red; font-weight: bold'>重要！！！如果您的站点使用了ajax，axios等异步提交评论的技术。请配置该项！</span>");
        $input->setAttribute("class", "typecho-option " . self::$className);
        $input->setAttribute("style", "display: none");
        $form->addInput($input);

        $input = new Text("geenativebuttonh", null, "260px", "极验按钮高度");
        $input->description("单位可以是 px，%，em，rem，pt。注意：按钮高度和按钮宽度同时配置了才会生效");
        $input->input->setAttribute("placeholder", "极验按钮高度");
        $input->setAttribute("class", "typecho-option " . self::$className);
        $input->setAttribute("style", "display: none");
        $form->addInput($input);

        $input = new Text("geenativebuttonw", null, "50px", "极验按钮宽度");
        $input->description("单位可以是 px，%，em，rem，pt。注意：按钮高度和按钮宽度同时配置了才会生效");
        $input->input->setAttribute("placeholder", "极验按钮宽度");
        $input->setAttribute("class", "typecho-option " . self::$className);
        $input->setAttribute("style", "display: none");
        $form->addInput($input);

        $input = new Text("geeconstyle", null, null, "验证按钮父元素的样式");
        $input->description("填写标准的css。例如: margin-bottom: 20px");
        $input->input->setAttribute("placeholder", "验证按钮父元素的样式");
        $input->setAttribute("class", "typecho-option " . self::$className);
        $input->setAttribute("style", "display: none");
        $form->addInput($input);

        $languages = [
            "zho" => "简体中文",
            "eng" => "英文",
            "zho-tw" => "繁体中文（台湾）",
            "zho-hk" => "繁体中文（香港）",
            "udm" => "维吾尔语",
            "jpn" => "日语",
            "ind" => "印尼语",
            "kor" => "韩语",
            "rus" => "俄语",
            "ara" => "阿拉伯语",
            "spa" => "西班牙语",
            "pon" => "巴西葡语",
            "por" => "欧洲葡语",
            "fra" => "法语",
            "deu" => "德语"
        ];
        $select = new Select("geelanguage", $languages, "zho", "多语言配置");
        $select->setAttribute("class", "typecho-option " . self::$className);
        $select->setAttribute("style", "display: none");
        $form->addInput($select);

        $radio = new Radio("geemaskclose", ["on" => "是", "off" => "否"], "on", "点击验证码区域外是否关闭验证");
        $radio->setAttribute("class", "typecho-option " . self::$className);
        $radio->setAttribute("style", "display: none");
        $form->addInput($radio);

        $input = new Text("geemaskbgcolor", null, "#0000004d", "弹窗背景色");
        $input->description("任意合法的css颜色单位都可以");
        $input->input->setAttribute("placeholder", "弹窗背景色");
        $input->setAttribute("class", "typecho-option " . self::$className);
        $input->setAttribute("style", "display: none");
        $form->addInput($input);

        $radio = new Radio("geehidesuccess", ["on" => "是", "off" => "否"], "off", "隐藏bind展现形式下的验证成功弹窗");
        $radio->description("注：仅product参数值为bind情况下生效");
        $radio->setAttribute("class", "typecho-option " . self::$className);
        $radio->setAttribute("style", "display: none");
        $form->addInput($radio);
    }
}
