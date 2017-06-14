<?php

namespace Hanson\Vbot\Example\Handlers\Contact;

use Hanson\Vbot\Contact\Friends;
use Hanson\Vbot\Contact\Groups;
use Hanson\Vbot\Message\Text;
use Illuminate\Support\Collection;

class SldcGroup
{
    public static function messageHandler(Collection $message, Friends $friends, Groups $groups)
    {
        if ($message['from']['NickName'] === '胜乐大数据') {
            if ($message['type'] === 'group_change' && $message['action'] === 'ADD') {
                Text::send($message['from']['UserName'], '欢迎新人 '.$message['invited']);
            }

            if ($message['type'] === 'text') {
//                Text::send($message['from']['UserName'], 'good!');
                if ($message['sender']['NickName'] === 'HanSon') {
                    if (str_contains($message['content'], '加人')) {
                        $username = str_replace('加人', '', $message['content']);
                        $friends->add($username, 'xxxx');
                    }
                }

                if (str_contains($message['content'], '看')) {
                    Text::send($message['from']['UserName'], static::sldcReply($message['content'],$message['from']['UserName']));
                }

                if (str_contains($message['content'], 'help') || str_contains($message['content'], '帮助')) {
                    $msg = <<<EOF
1.看版本更新人数
2.##聊天内容
---more
EOF;
                    Text::send($message['from']['UserName'], $msg);
                }

                if (str_contains($message['content'], '##')) {
                    $content = str_replace('##', '', $message['content']);
                    Text::send($message['from']['UserName'], static::reply($content, $message['from']['UserName']));
                }
            }
        }
    }

    /**
     * 图灵123回复
     * @param $content
     * @param $id
     * @return string
     */
    private static function reply($content, $id)
    {
        try {
            $result = vbot('http')->post('http://www.tuling123.com/openapi/api', [
                'key'    => 'cc0b3a15ab26404a9e98c39347281c55',
                'info'   => $content,
                'userid' => $id,
            ], true);

            return isset($result['url']) ? $result['text'].$result['url'] : $result['text'];
        } catch (\Exception $e) {
            return '图灵API连不上了，再问问试试';
        }
    }


    /**
     * sldc回复
     * @param $content
     * @param $id
     * @return string
     */
    private static function sldcReply($content, $id)
    {
        try {
            $result = vbot('http')->post('https://api.shenglediancang.com/api/robot/run', [
                'key'    => 'cc0b3a15ab26404a9e98c39347281c55',
                'info'   => $content,
                'userid' => $id,
            ], true);
            return $result['data']['text'];
//            return isset($result['data']['url']) ? $result['text'].$result['url'] : $result['text'];
        } catch (\Exception $e) {
            return 'sldc online API连不上了，再问问试试';
        }
    }
}
