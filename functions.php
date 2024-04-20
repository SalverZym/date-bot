<?php

function debug($data, $log=true):void
{
    if($log){
        file_put_contents(__DIR__.'/logs.txt', print_r($data, true), FILE_APPEND);
    }else{
        echo "<pre>".print_r($data, 1)."</pre>>";
    }
}

function sendBoard(\Telegram\Bot\Api $telegram, $chat_id, $keyboard, $text)
{
    $telegram->sendMessage([
        'chat_id' => $chat_id,
        'text' => "{$text}",
        'reply_markup' => new Telegram\Bot\Keyboard\Keyboard($keyboard),
    ]);
}

function sendText(\Telegram\Bot\Api $telegram, $chat_id, $text)
{
    $telegram->sendMessage([
        'chat_id' => $chat_id,
        'text' => "{$text}",
    ]);
}