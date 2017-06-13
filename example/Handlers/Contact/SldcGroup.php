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
                Text::send($message['from']['UserName'], 'good!');
                if ($message['sender']['NickName'] === 'HanSon') {
                    if (str_contains($message['content'], '加人')) {
                        $username = str_replace('加人', '', $message['content']);
                        $friends->add($username, '我是你儿子');
                    }
                }

                if (str_contains($message['content'], '搜人')) {
                    $nickname = str_replace('搜人', '', $message['content']);
                    $members = $groups->getMembersByNickname($message['from']['UserName'], $nickname, true);
                    $result = '搜索结果 数量：'.count($members)."\n";
                    foreach ($members as $member) {
                        $result .= $member['NickName'].' '.$member['UserName']."\n";
                    }
                    Text::send($message['from']['UserName'], $result);
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
}
