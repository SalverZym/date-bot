<?php

require_once __DIR__.'/functions.php';
require_once __DIR__.'/keyboards.php';
require_once __DIR__.'/Base.php';



class Registration extends Base
{
    private $inline_keyboard_reg1 = [
        'inline_keyboard' => [
            [['text' => 'Мужчин', 'callback_data'=>'taste_M'],],
            [['text'=> 'Женщин', 'callback_data'=> 'taste_W'],],
            [['text'=> 'Оба', 'callback_data'=> 'taste_B'],]
        ],
    ];

    public function sex($sex)
    {
        $this->update($this->chat_id, $sex, 'sex',"taste");
        sendBoard($this->telegram, $this->chat_id, $this->inline_keyboard_reg1, 'Ваш вкус');
    }


    public function taste($taste)
    {
        $this->update($this->chat_id, $taste, 'taste',"firstName");
        sendText($this->telegram, $this->chat_id, "Введите ваше имя");

    }

    public function firstName($name)
    {
        $this->update($this->chat_id, $name, 'firstName',"desc");
        sendText($this->telegram, $this->chat_id, "Расскажите о себе");

    }

    public function desc($desc)
    {
        $this->update($this->chat_id, $desc, 'desc',"");
        $this->db->cash->deleteOne(['chat_id'=>$this->chat_id]);
        sendText($this->telegram, $this->chat_id, "Готово, вы зарегестрированы");
        $this->showMenu();
    }


    private function update($chat_id, $update, $parametr,$last_messege)
    {
        $this->db->users->updateOne(
            ['chat_id' => $chat_id],
            ['$set' => ["{$parametr}" => "{$update}"]]
        );
        $this->db->cash->updateOne(
            ['chat_id' => $this->chat_id],
            ['$set' => ['last_messege' => "{$last_messege}"]]
        );
    }
}