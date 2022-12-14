<?php

namespace App\Helper;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Console\Input\Input;

class Telegram
{

    static function getCommand($request): bool|string
    {
        try {
            $body = $request->all();
            $message = $body['message'];
            $text = $message['text'];
            $entities = $message['entities'] ?? null;
            if ($entities && $entities[0]['type'] == "bot_command")
                return $text;
            return false;
        } catch (\Exception $exception) {
            return false;
        }
    }

    static function getText($request): bool|string
    {
        try {
            $body = $request->all();
            $message = $body['message'];
            return $message['text'];
        } catch (\Exception $exception) {
            return false;
        }
    }

    static function getColumn($request)
    {
        try {

            $key = self::getText($request);
            $db = new \SQLite3(env("SQLITE_PATH"));
            $results = $db->query("select *
        from inbounds
        WHERE
        settings like '%$key%'")->fetchArray();

            if (!isset($results['id']) || !$results['id']) {
                $results = $db->query("select *
        from inbounds
        WHERE
        remark = '$key'")->fetchArray();
            }

            if (!isset($results['id']) || !$results['id'])
                return false;

            return self::getColumnInfo($results);
        } catch (\Exception $exception) {
            return false;
        }
    }

    static private function getColumnInfo($column)
    {
        try {
            $text = false;
            $up = round(($column['up']) / 1024 / 1024 / 1024, 2);
            $enable = (bool)$column['enable'];
            $down = round(($column['down']) / 1024 / 1024 / 1024, 2);
            $total = round(($column['total']) / 1024 / 1024 / 1024, 2);
            $expiryTime = $column['expiry_time'];
            $text .= "?????? ?????????? = " . $up . " GB " . PHP_EOL
                . "?????? ???????????? = " . $down . " GB " . PHP_EOL;
            if ($total == 0)
                $text .= "?????? ??????????????" . PHP_EOL;
            else
                $text .= "?????? = " . $total . " GB " . PHP_EOL;
            if (!$expiryTime)
                $text .= "?????? ???????? ??????????????" . PHP_EOL;
            else {
                $expiryTime = floor($expiryTime / 1000);
                $now = time();
                $datediff = $expiryTime - $now;
                if ($datediff <= 0)
                    $text .= "?????????? ?????????? ?????? ??????" . PHP_EOL;
                else {
                    $text .= "?????? ?????? ???????? ?????????? ???? ?????????? ?????????? = " . round($datediff / (60 * 60 * 24)) . PHP_EOL;
                }
            }

            if ($enable)
                $text .= "?????????????? ???????? ???? ????????." . PHP_EOL;
            else
                $text .= "?????????????? ?????? ???????? ???? ????????." . PHP_EOL;

            $text .= "
???????????? ???????????????? ???????? ???? ???????? ?????? ?????? ???????? ????????.
@vpnxzn @vpn_fm_admin
???????? ?????????? :
 @vpn2vray";

            return $text;
        } catch (\Exception $exception) {
            return false;
        }
    }

    static function sendMessage($request, $text)
    {
        try {
            $body = $request->all();
            $message = $body['message'];
            $chat = $message['chat'];
            $chatId = $chat['id'];
            $url = env("TELEGRAM_BASE") . "/sendMessage";
            $params = [
                'multipart' => [
                    [
                        'name' => 'chat_id',
                        'contents' => $chatId,
                    ],
                    [
                        'name' => 'text',
                        'contents' => $text,
                    ]
                ]
            ];
            return self::send_replay($url, $params);
        } catch (\Exception $exception) {
            return false;
        }
    }

    static function sendImage($request, $image, $description = '')
    {
        try {
            $body = $request->all();
            $message = $body['message'];
            $chat = $message['chat'];
            $chatId = $chat['id'];
            $url = env("TELEGRAM_BASE") . "/sendPhoto";
            $params = [
                'multipart' => [
                    [
                        'name' => 'chat_id',
                        'contents' => $chatId,
                    ],
                    [
                        'name' => 'photo',
                        'contents' => $image,
                        'filename' => 'image.png'
                    ],
                    [
                        'name' => 'caption',
                        'contents' => $description,
                    ]
                ]
            ];
            return self::send_replay($url, $params);
        } catch (\Exception $exception) {
            return false;
        }
    }

    static private function send_replay($url, $postParam)
    {
        try {
            $client = new Client();
            $client->post($url, $postParam);
            return true;
        } catch (\Exception $exception) {
            return false;
        }

    }


}
