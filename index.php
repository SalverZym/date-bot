<?php

error_reporting(-1);
ini_set('display_errors', 0);
ini_set('log_errors', 'on');
ini_set('error_log', __DIR__ . '/errors.log');

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';
$phrases = require_once __DIR__ . '/phrases.php';
$keyboards_for_chat=__DIR__. '/keyboards_for_chat.php';
require_once __DIR__ . '/keyboards.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__.'/Chat.php';
require_once __DIR__.'/Registration.php';

/**
 * @var array $inline_keyboard_reg1
 * @var array $inline_keyboard_reg2
 */

$client=new \MongoDB\Client(DB);
$users=$client->chat->users;
$cash=$client->chat->cash;

$telegram = new \Telegram\Bot\Api(TOKEN);
$update = $telegram->getWebhookUpdate();
debug($update);

$name = $update['message']['from']['first_name'] ?? 'Guest';

if (isset($update['message']['chat']['id'])) {
    $chat_id = $update['message']['chat']['id'];
    $text=$update['message']['text'];
} elseif (isset($update['callback_query']['message']['chat']['id'])) {
    $chat_id = $update['callback_query']['message']['chat']['id'];
    $text=$update['callback_query']['data'];
}else{
    $chat_id=null;
    $text=null;
}

if($cash->findOne(['chat_id'=>$chat_id])){
    $chat=new Registration($chat_id, $client->chat, $telegram);
}else{
    $chat=new Chat($chat_id, $users, $telegram, $keyboards_for_chat);
}

if($text=='/start'){
    if($chat->check()){
        $chat->showMenu();
        die;
    }else{
        $update_id = (int)$update['update_id'];
        $users->insertOne(['chat_id'=>$chat_id]);
        $users->updateOne(
            ['chat_id'=>$chat_id],
            ['$set' => ["username" => "{$update['message']['from']['username']}"]]
        );
        $cash->insertOne(['chat_id'=>$chat_id]);
        sendBoard($telegram, $chat_id, $inline_keyboard_reg1, 'Ваш пол');
        die;
    }
}

if(isset($update['message'])){
    $last_messege=$cash->findOne(['chat_id'=>$chat_id], ["typeMap" => ['root' => 'array', 'document' => 'array']]);
    if($last_messege){
        if(array_key_exists('last_messege', $last_messege)){
            if(method_exists($chat, $last_messege['last_messege'])){
                $chat->{$last_messege['last_messege']}($text);
            }
        }
    }else{
        sendText($telegram, $chat_id, "Воспользуйтесь клавиатурой");
    }
}elseif (isset($update['callback_query'])){
    $callback_query=explode('_', $text);
    if(method_exists($chat, $callback_query[0])){
        /*$telegram->answerCallbackQuery([
            'callback_query_id'=>$update['callback_query']['id'],
        ]);*/
        if(count($callback_query)==1){
            $chat->{$callback_query[0]}();
        }else{
            call_user_func_array(array($chat, "{$callback_query[0]}"), array_slice($callback_query, 1));
        }
    }
}









