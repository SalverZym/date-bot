<?php

return [
    'menu_keyboard'=>[
        'inline_keyboard' => [
            [['text' => 'Найти пару', 'callback_data'=>'showUser'],],
            [['text'=> 'Посмотреть лайки', 'callback_data'=> 'showLikes'],],
            [['text'=> 'Посмотреть взаимные лайки', 'callback_data'=> 'showMutual'],],
        ],
    ],
];
