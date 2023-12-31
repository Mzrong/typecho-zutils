<?php

namespace TypechoPlugin\ZUtils\Lib;

use Typecho\Db;

if (phpversion() < 7.4) {
    exit(sprintf("Plugin `Utils` require PHP lgt 7.4. but your PHP version was (%s)", phpversion()));
}

if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

/**
 * 一些通用的方法
 *
 * @author zrong
 * @email zengrong27@gmail.com
 * @link https://zrong.life
 * @time 2023-12-31 08:46
 */
class Methods
{
    /**
     * 数据库实例
     * @var Db
     */
    private Db $db;

    /**
     * 数据库表前缀
     * @var string
     */
    private string $prefix;

    /**
     * 配置表
     * @var string
     */
    private string $confTable = "z_utils_conf";

    public function __construct()
    {
        try {
            $this->db = Db::get();
            $this->prefix = $this->db->getPrefix();
        } catch (Db\Exception $e) {
            exit($e->getMessage());
        }
    }

    /**
     * 检查配置表是否存在
     * @return bool
     */
    public function checkTable(): bool
    {
        try {
            return (bool) $this->db->fetchRow(
                $this->db->query("SHOW TABLES LIKE '{$this->prefix}{$this->confTable}'")
            );
        } catch (\Exception) {
            // 打印一些错误信息
            return false;
        }
    }

    /**
     * 创建配置表
     * @return bool
     */
    public function createTable(): bool
    {
        try {
            $script = <<<EOT
                CREATE TABLE `{$this->prefix}{$this->confTable}` (
                    `key` VARCHAR (255) NOT NULL COMMENT '配置项，全局唯一',
                    `uid` INT (10) NOT NULL COMMENT '所属用户',
                    `content` TEXT DEFAULT NULL COMMENT '配置内容，json格式。为了兼容数据库，这里使用了TEXT',
                    `time`  INT (10) DEFAULT 0 COMMENT '时间',
                    PRIMARY KEY (`key`),
                    UNIQUE KEY `key` (`key`) USING BTREE
                ) COMMENT = 'ZUtils 插件配置表'
            EOT;

            $this->db->query($script);

            return true;
        } catch (\Exception) {
            // 打印一些错误信息
            return false;
        }
    }

    /**
     * 删除配置表
     * @return bool
     */
    public function dropTable(): bool
    {
        try {
            $this->db->query(
                "DROP TABLE `{$this->prefix}{$this->confTable}`",
            );
            return true;
        } catch (\Exception) {
            return false;
        }
    }

    /**
     * 检查插件配置文件
     * @param $key
     * @return bool|array|null
     */
    public function getPluginConfigData($key): bool|array|null
    {
        try {
            return $this->db->fetchRow(
                $this->db->sql()->from("table.options")->select("*")->where("name = %s", $key)->limit(1)
            );
        } catch (\Exception) {
            return false;
        }
    }

    /**
     * 绑定QQ的openid
     * @param string $openid
     * @param int $uid
     * @return bool
     */
    public function updateBindQQOpenid(string $openid, int $uid): bool
    {
        try {
            // 查询当前openid的绑定情况
            $row = $this->db->fetchRow(
                $this->db->sql()->from("table.{$this->confTable}")->select("*")->where("key = %s", $openid)->limit(1)
            );

            if ($row) {
                // 存在记录则更新一次，会将openid绑定到当前登录的用户
                $this->db->sql()->where("key = %s", $openid)->rows(
                    [
                        "time"      =>  time(),
                        "uid"       =>  $uid,
                        "content"   =>  json_encode(["platform"=>"qq"])
                    ]
                )->update("table.{$this->confTable}");
            } else {
                // 将token更新到数据库
                $this->db->sql()->rows(
                    [
                        "key"   =>  $openid,
                        "uid"   =>  $uid,
                        "content"   =>  json_encode(["platform"=>"qq"]),
                        "time"  =>  time()
                    ]
                )->insert("table.{$this->confTable}");
            }

            return true;
        } catch (\Exception) {
            return false;
        }
    }

    /**
     * 检测用户QQ的绑定情况
     * @param int $uid
     * @return bool
     */
    public function checkQQBindStatus(int $uid): bool
    {
        try {
            $row = $this->db->fetchRow(
                $this->db->sql()->from("table.{$this->confTable}")->select("*")->where("uid = ?", $uid)->limit(1)
            );

            return (bool) $row;
        } catch (\Exception) {
            return false;
        }
    }

    /**
     * 根据openid获取绑定的用户信息
     * @param string $openid
     * @return array|bool
     */
    public function getQQBindData(string $openid): array|bool
    {
        try {
            // 查询当前openid的绑定情况
            $row = $this->db->fetchRow(
                $this->db->sql()->from("table.{$this->confTable}")->select("*")->where("key = %s", $openid)->limit(1)
            );

            if (empty($row)) {
                return false;
            }

            $user = $this->db->fetchRow(
                $this->db->sql()->from("table.users")->select("*")->where("uid = ?", $row["uid"])->limit(1)
            );

            if (empty($user)) {
                return false;
            }

            return $user;
        } catch (\Exception) {
            return false;
        }
    }
}
