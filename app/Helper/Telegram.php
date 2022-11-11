<?php

namespace App\Helper;

use Illuminate\Support\Facades\Log;

class Telegram
{

    static function getCommand($request): bool|string
    {
        $body = $request->all();
        $message = $body['message'];
        $text = $message['text'];
        $entities = $message['entities'] ?? null;
        if ($entities && $entities[0]['type'] == "bot_command")
            return $text;
        return false;
    }

    static function getText($request): bool|string
    {
        $body = $request->all();
        $message = $body['message'];
        return $message['text'];
    }

    static function getColumn($request)
    {
        $key = self::getText($request);

        $db = new \SQLite3(env("SQLITE_PATH"));

        $results = $db->query("select *
        from inbounds
        WHERE
        settings like '%$key%'")->fetchArray();

        $text = false;
        if (!isset($results['id']) || !$results['id']) {
            $results = $db->query("select *
        from inbounds
        WHERE
        remark = '$key'")->fetchArray();
        }

        if (!isset($results['id']) || !$results['id'])
            return false;

        $up = round(($results['up']) / 1024 / 1024 / 1024, 2);
        $down = round(($results['down']) / 1024 / 1024 / 1024, 2);
        $total = round(($results['total']) / 1024 / 1024 / 1024, 2);
        $expiryTime = $results['expiry_time'];
        $text .= "حجم آپلود = " . $up . " GB " . PHP_EOL
            . "حجم دانلود = " . $down . " GB " . PHP_EOL;
        if ($total == 0)
            $text .= "حجم نامحدود" . PHP_EOL;
        else
            $text .= "حجم = " . $total . " GB " . PHP_EOL;
        if (!$expiryTime)
            $text .= "مدت زمان نامحدود" . PHP_EOL;
        else {
            $expiryTime = floor($expiryTime / 1000);
            $now = time();
            $datediff = $expiryTime - $now;
            if ($datediff <= 0)
                $text .= "سرویس منقضی شده است" . PHP_EOL;
            else {
                $text .= "روز های باقی مانده تا اتمام سرویس = " . round($datediff / (60 * 60 * 24)) . PHP_EOL;
            }
        }

        return $text;
//        var_dump($text);
    }

    static function sendMessage($request, $text): bool|string
    {
        $body = $request->all();
        $message = $body['message'];
        $chat = $message['chat'];
        $chatId = $chat['id'];
        $url = env("TELEGRAM_BASE") . "/sendMessage";
        $params = ['chat_id' => $chatId, 'text' => $text];
        return self::send_replay($url, $params);
    }

    static private function send_replay($url, $postParam): bool|string
    {
        $cu = curl_init();
        curl_setopt($cu, CURLOPT_URL, $url);
        curl_setopt($cu, CURLOPT_POSTFIELDS, $postParam);
        curl_setopt($cu, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($cu);
        curl_close($cu);
        return $result;
    }


}
