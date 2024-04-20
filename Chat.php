<?php

require_once __DIR__. '/keyboards.php';
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';
require_once __DIR__.'/Base.php';

class Chat extends Base
{

    private $key_boards;

    public function __construct($chat_id, $db, $telegram, $key_boards)
    {
        parent::__construct($chat_id, $db, $telegram);
        $this->key_boards=$key_boards;
    }


    public function check()
    {
        if($this->db->findOne(['chat_id'=>$this->chat_id])){
            return true;
        }
        return false;
    }


    public function showUser($like=null)
    {
        if($like){
            $this->db->updateOne(
                ['chat_id'=>(int)$like],
                ['$push'=>['likes'=>$this->chat_id]],
                ['multiple' => false]
            );
        }

        $count=$this->db->countDocuments();
        $randomNumber = rand(0, $count - 1);

        $sex=$this->db->findOne(['chat_id'=>$this->chat_id], ["typeMap" => ['root' => 'array', 'document' => 'array'],]);
        $user=$this->db->findOne( ["sex"=>"{$sex}"], ['skip' => $randomNumber]);
        $key_board=[
            'inline_keyboard'=>[
                [['text' => 'Лайк', 'callback_data'=>"showUser_{$user['chat_id']}"],['text' => 'Дальше', 'callback_data'=>'showUser'],],
                [['text'=> 'Вернуться в меню', 'callback_data'=> 'showMenu'],],
            ],
        ];
        sendBoard($this->telegram, $this->chat_id, $key_board, "Имя:{$user['name']}, Увлечения:{$user['desc']}");
    }

    public function showLikes($mutual=null)
    {
        if($mutual){
            $this->db->updateOne(
                ['chat_id'=>(int)$mutual],
                ['$push'=>['mutual'=>$this->chat_id]],
                ['multiple' => false]
            );
            $this->db->updateOne(
                ['chat_id'=>$this->chat_id],
                ['$push'=>['mutual'=>(int)$mutual]],
                ['multiple' => false]
            );
        }
        

        $options = [
            "typeMap" => ['root' => 'array', 'document' => 'array'],
        ];
        $likes=$this->db->findOne(['chat_id'=>$this->chat_id], $options)['likes'][0];
        $user=$this->db->findOne(['chat_id'=>$likes], $options);

        $this->db->updateOne(
            ['chat_id'=>$this->chat_id],
            ['$pull' => ['likes' => ['$exists' => true],]],
            ['multiple' => false]
        );


        $key_board=[
            'inline_keyboard'=>[
                [['text' => 'Взаимный лайк', 'callback_data'=>"showLikes_{$user['chat_id']}"],['text' => 'Дальше', 'callback_data'=>'showLikes'],],
                [['text'=> 'Вернуться в меню', 'callback_data'=> 'showMenu'],],
            ],
        ];

        if($user){
            sendBoard($this->telegram, $this->chat_id, $key_board, "Имя:{$user['name']}, Увлечения:{$user['desc']}");
        }else{
            sendText($this->telegram, $this->chat_id, "Вас еще никто не лайкнул");
        }

    }

    public function showMutual($count=0)
    {

        $mutual_user_id=$this->db->findOne(['chat_id'=>$this->chat_id], ["typeMap" => ['root' => 'array', 'document' => 'array'],])['mutual'][(int)$count];
        $mutual_user=$this->db->findOne(['chat_id'=>(int)$mutual_user_id]);

        $next=++$count;
        $key_board=[
            'inline_keyboard'=>[
                [['text' => 'Удалить', 'callback_data'=>"deleteMutual_{$count}"],['text' => 'Дальше', 'callback_data'=>"showMutual_{$next}"],],
                [['text'=> 'Написать в чат', 'callback_data'=> "startChat_{$count}"],],
                [['text'=> 'Вернуться в меню', 'callback_data'=> 'showMenu'],],
            ],
        ];

        if($mutual_user){
            sendBoard($this->telegram, $this->chat_id, $key_board, "Имя:{$mutual_user['name']}, Увлечения:{$mutual_user['desc']}");
        }else {
            sendText($this->telegram, $this->chat_id, "У вас нет взаимных лайков");
        }
    }

    public function deleteMutual($count)
    {

        $this->db->users->updateOne(
            ['chat_id'=>$this->chat_id],
            ['$pull' => ['mutual' => ['$exists' => true], ['$position' => (int)$count]]],
            ['multiple' => false]
        );

        $this->showMutual(++$count);
    }

    public function startChat($count)
    {
        $mutual_user_id=$this->db->findOne(['chat_id'=>$this->chat_id], ["typeMap" => ['root' => 'array', 'document' => 'array'],])['mutual'][$count];
        $username=$this->db->findOne(['chat_id'=>$mutual_user_id], ["typeMap" => ['root' => 'array', 'document' => 'array'],])['username'];;

        sendText($this->telegram, $this->chat_id, "Вы можете написать пользователю но его нику @{$username}");
    }
}