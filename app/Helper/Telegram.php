<?php

namespace App\Helper;

use Illuminate\Support\Facades\Log;

class Telegram
{

    static function getCommand($request): bool|string
    {
        Log::debug($body = json_decode($request));
        $message = $body->message;
        $text = $message->text;
        Log::debug($text);
        $entities = $message?->entities;
        if ($entities && $entities[0]->type == "bot_command")
            return $text;
        return false;
    }

    static function getText($request): bool|string
    {
        $body = json_decode($request);
        $message = $body->message;
        return $message->text;
    }

    static function sendMessage($request, $text): mixed
    {
        $body = json_decode($request);
        $message = $body->message;
        $chat = $message->chat;
        $chatId = $chat->id;
        $url = env("TELEGRAM_BASE") . "sendMessage";
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


    static private function isJson($string): bool
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

}
