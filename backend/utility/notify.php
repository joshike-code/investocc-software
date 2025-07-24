<?php

class Notify
{

    public static function log($msg)
    {
        $timestamp = "[" . date("Y-m-d H:i:s") . "] ";
        $entry = $timestamp . $msg . "\n";
        
        file_put_contents(__DIR__ . '/../error/admin-alerts.log', $entry, FILE_APPEND);

        // Send to Telegram
        // self::sendTelegramMessage($timestamp . $msg);
    }

    private static function sendTelegramMessage($text)
    {
        $keys = require __DIR__ . '/../config/keys.php';
        $telegramToken = $keys['telegram']['bot_token'];
        $chatId = $keys['telegram']['chat_id'];
        $url = "https://api.telegram.org/bot" . $telegramToken . "/sendMessage";

        $data = [
            'chat_id' => $chatId,
            'text'    => $text
        ];

        // Send the HTTP POST request
        $options = [
            'http' => [
                'method'  => 'POST',
                'header'  => "Content-Type: application/x-www-form-urlencoded",
                'content' => http_build_query($data)
            ]
        ];

        $context  = stream_context_create($options);
        file_get_contents($url, false, $context);
    }

}