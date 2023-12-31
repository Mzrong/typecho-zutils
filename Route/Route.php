<?php
namespace TypechoPlugin\ZUtils\Route;

use TypechoPlugin\ZUtils\Lib\Methods;
use Utils\Helper;
use Typecho\Cookie;
use TypechoPlugin\ZUtils\Lib\QSdk;
use Widget\Base;

/**
 * 插件路由文件。插件内生成的路由交给Route这个类来管理
 *
 * @author zrong
 * @email zengrong27@gmail.com
 * @link https://zrong.life
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
                 * 是普通用户的话直接返回
                 */
                $this->response->redirect($this->options->index);
            } else {
                /**
                 * 更新用户绑定
                 */
                $status = $methods->updateBindQQOpenid($result["openid"], $this->user->uid);

                if (!$status) {
                    echo "绑定失败，请重试再试";
                } else {
                    echo "绑定成功";
                }
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
}
