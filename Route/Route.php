<?php

namespace TypechoPlugin\ZUtils\Route;

use Widget\Base;
use Utils\Helper;
use Typecho\Cookie;
use TypechoPlugin\ZUtils\Lib\Methods;
use TypechoPlugin\ZUtils\Lib\QSdk;

/**
 * 插件路由文件。插件内生成的路由交给Route这个类来管理
 *
 * @author zrong
 * @email zengrong27@gmail.com
 * @link https://zrong.site
 * @time 2023-12-31 08:44
 */
class Route extends Base
{
    /**
     * QQ授权回调
     */
    public function qqLoginResponse()
    {
        $param = $_GET;
        $state = Cookie::get("stateStr");

        if (empty($param["code"]) || empty($param["state"]) || $state != $param["state"]) {
            $this->response->redirect($this->options->index);
        }

        $options = Helper::options()->plugin("ZUtils");

        $result = QSdk::doQQAuth($param["code"], $options);

        Cookie::delete("stateStr");

        if ($result === false) {
            $this->response->redirect($this->options->index);
        }

        $methods = new Methods();

        /**
         * 如果当前是登录状态的话，可以进行绑定操作
         */
        if ($this->user->hasLogin()) {
            if ($this->user->pass("contributor", true)) {
                /**
                 * 更新用户绑定
                 */
                $status = $methods->updateBindQQOpenid($result["openid"], $this->user->uid);

                $this->renderHtml($status);
            } else {
                /**
                 * 是普通用户的话直接返回
                 */
                $this->response->redirect($this->options->index);
            }
        } else {
            // 执行登录操作
            $row = $methods->getQQBindData($result["openid"]);

            if ($row === false) {
                /**
                 * 没有绑定信息，返回首页
                 */
                $this->response->redirect($this->options->index);
            } else {
                if ($this->user->pass("contributor", true)) {
                    /**
                     * 是普通用户的话直接返回
                     */
                    $this->response->redirect($this->options->index);
                } else {
                    $this->user->commitLogin($row);

                    $this->user->hasLogin = true;
                    $this->user->push($row);
                    $this->user->currentUser = $row;

                    $this->response->redirect($this->options->adminUrl);
                }
            }
        }

    }

    /**
     * 输出QQ绑定状态的html
     * @param bool $status
     */
    private function renderHtml(bool $status)
    {
        $css = <<<EOT
            <style rel="stylesheet">
                * { 
                    margin: 0; 
                    padding:0; 
                }
                
                body { 
                    width: 100%; 
                    height: 100vh; 
                    display: flex; 
                    flex-direction: column; 
                    align-items: center; 
                    box-sizing: border-box; 
                    padding-top: 100px;
                }
                
                h2 { 
                    margin-top: 20px; 
                }
                
                p { 
                    font-size: 14px; 
                    color: #666; 
                    margin-top: 20px; 
                }
                
                button {
                    display: inline-block; 
                    cursor: pointer; 
                    height: 44px;
                    display:flex;
                    align-items:center;
                    justify-content:center;
                    padding: 0 30px; 
                    font-size: 14px; 
                    background-color: rgb(22, 93, 255);
                    color: #fff;
                    transition: all .1s cubic-bezier(0,0,1,1);
                    border: none;
                    outline: none;
                    margin-top: 60px;
                    border-radius: 8px;    
                }
                
                button:hover {
                    background-color: rgb(64, 128, 255); 
                }
                
                button:active {
                    background-color: rgb(14,66,210);
                }
            </style>
        EOT;

        $js = <<<EOT
            <script type="text/javascript">
                function closeModal () {
                    window.close();
                }
            </script>
        EOT;

        $tips = $status ? "您已成功绑定QQ账户。您可以使用你的QQ账户来登录你的网站管理后台！" : "操作失败！请重试。如果无法解决，请联系插件作者";
        $statusLabel = $status ? "绑定成功" : "绑定失败";
        $icon = $status ? '<svg t="1704019443132" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="3484" width="100" height="100"><path d="M512 512m-448 0a448 448 0 1 0 896 0 448 448 0 1 0-896 0Z" fill="#07C160" p-id="3485"></path><path d="M466.7 679.8c-8.5 0-16.6-3.4-22.6-9.4l-181-181.1c-12.5-12.5-12.5-32.8 0-45.3s32.8-12.5 45.3 0l158.4 158.5 249-249c12.5-12.5 32.8-12.5 45.3 0s12.5 32.8 0 45.3L489.3 670.4c-6 6-14.1 9.4-22.6 9.4z" fill="#FFFFFF" p-id="3486"></path></svg>' : '<svg t="1704021531341" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="5384" width="100" height="100"><path d="M512 512m-448 0a448 448 0 1 0 896 0 448 448 0 1 0-896 0Z" fill="#FA5151" p-id="5385"></path><path d="M557.3 512l113.1-113.1c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L512 466.7 398.9 353.6c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L466.7 512 353.6 625.1c-12.5 12.5-12.5 32.8 0 45.3 6.2 6.2 14.4 9.4 22.6 9.4s16.4-3.1 22.6-9.4L512 557.3l113.1 113.1c6.2 6.2 14.4 9.4 22.6 9.4s16.4-3.1 22.6-9.4c12.5-12.5 12.5-32.8 0-45.3L557.3 512z" fill="#FFFFFF" p-id="5386"></path></svg>';

        $html = <<<EOT
            <!DOCTYPE html>
            <html lang="en">
                <head>
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>绑定QQ账号</title>
                    {$css}
                    {$js}
                </head>
                <body>
                    {$icon}
                    <h2>{$statusLabel}</h2>
                    <p>{$tips}</p>
                    <button onclick="closeModal()">关闭窗口</button>
                </body>
            </html>
        EOT;

        echo $html;

    }
}
