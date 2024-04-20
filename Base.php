<?php

class Base
{
    public $chat_id;
    public $db;
    public $telegram;
    private $key_board_menu=[
            'inline_keyboard' => [
                [['text' => 'Найти пару', 'callback_data'=>'showUser'],],
                [['text'=> 'Посмотреть лайки', 'callback_data'=> 'showLikes'],],
                [['text'=> 'Посмотреть взаимные лайки', 'callback_data'=> 'showMutual'],],
            ],];

    public function __construct($chat_id, $db, $telegram)
    {
        $this->chat_id=$chat_id;
        $this->db=$db;
        $this->telegram=$telegram;
    }

    public function showMenu(){
        sendBoard($this->telegram, $this->chat_id, $this->key_board_menu, 'Меню');
    }

    public function check(){

    }

}