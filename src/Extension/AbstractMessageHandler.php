<?php

namespace Hanson\Vbot\Extension;

use Hanson\Vbot\Exceptions\ExtensionException;
use Hanson\Vbot\Message\Text;
use Illuminate\Support\Collection;

abstract class AbstractMessageHandler
{
    public $version = '1.0';

    public $author = 'HanSon';

    public $name;

    public $zhName;

    public static $status = true;

    public static $admin;

    /**
     * 拓展配置.
     *
     * @var
     */
    public static $config;

    /**
     * 初始化拓展.
     */
    public function init()
    {
        static::$config = vbot('config')->get('extension.'.$this->name);

        $this->admin();

        $this->register();
    }

    /**
     * 注册拓展时的操作.
     */
    abstract public function register();

    /**
     * 开发者需要实现的方法.
     *
     * @param Collection $collection
     *
     * @return mixed
     */
    abstract public function handler(Collection $collection);

    /**
     * 消息处理器.
     *
     * @param Collection $collection
     */
    final public function messageHandler(Collection $collection)
    {
        if ($collection['type'] === 'text' && $this->isAdmin($collection['username'])) {
            if (starts_with($collection['content'], $this->name.' ')) {
                $content = str_replace($this->name.' ', '', $collection['content']);

                switch ($content) {
                    case  'info':
                        $this->applicationInfo($collection);
                        break;
                    case 'on':
                        $this->setStatus(true, $collection);
                        break;
                    case 'off':
                        $this->setStatus(false, $collection);
                        break;
                    default:
                        break;
                }
            }
        }

        if (!static::$status) {
            return;
        }

        $this->handler($collection);
    }

    final public function applicationInfo($collection)
    {
        $status = static::$status ? '开' : '关';

        $admin = static::$admin;

        Text::send($collection['from']['UserName'], "当前应用名称：{$this->zhName}\n名称：{$this->name}\n状态：{$status}\n版本：{$this->version}\n作者：{$this->author}\n管理员 Username：{$admin}");
    }

    /**
     * 设置拓展开关.
     *
     * @param bool $boolean
     * @param $collection
     */
    final public function setStatus(bool $boolean, $collection)
    {
        static::$status = $boolean;

        $status = static::$status ? '开' : '关';

        Text::send($collection['from']['UserName'], "应用：{$this->zhName} 状态已更改为：{$status}");
    }

    /**
     * 设置管理员.
     *
     * @throws ExtensionException
     */
    final public function admin()
    {
        $remark = vbot('config')->get('extension.admin.remark');

        if ($remark) {
            static::$admin = vbot('friends')->getUsernameByRemarkName($remark);
        }

        if (!$remark && ($nickname = vbot('config')->get('extension.admin.nickname'))) {
            static::$admin = vbot('friends')->getUsernameByNickname($nickname);
        }

        if (!static::$admin) {
            throw new ExtensionException('extension admin invalid.');
        }
    }

    /**
     * 判断是否管理员.
     *
     * @param $username
     *
     * @return bool
     */
    private function isAdmin($username)
    {
        return $username === static::$admin || $username === vbot('myself')->username;
    }
}
