<?php

define("TG_TOKEN", "6826432245:AAGGTo3VFt-JozfieMYIp4ML-VT9qgboGJo");
define("TG_USER_ID", 755447193);


class BotsTimeTelegram {
    private $token = "";

    private $chatLogQuery = 1424646511;
    private $logFilePath = __DIR__ . "/file_log.txt";

    public function __construct($token) {
        $this->token = $token;
    }

    /* ЛОГИРОВАНИЕ ДАННЫХ И ОТПРАВКА В TELEGRAM */
    public function sendLogTelegram($logData, $reasonLog = false) {
        file_put_contents($this->logFilePath, $logData);
        $textMessage = date("d.m.Y H:i") . " Логирование данных";

        if($reasonLog) {
            $textMessage .= "\n" . "Причина: " . $reasonLog;
        }

        $arrQuery = [
            "chat_id" 	=> $this->chatLogQuery,
            "caption" 	=> $textMessage,
            "document" 	=> new CURLFile($this->logFilePath)
        ];

        $this->sendQueryTelegram("sendDocument", $arrQuery);

        unlink($this->logFilePath);
    }
    /* ================================== */


    /* ПАРСЕР ДЛЯ ОТПРАВКИ ЗАПРОСОВ */
    public function sendQueryTelegram($method, $arrayQuery = "", $resultJSON = false) {
        $ch = curl_init("https://api.telegram.org/bot{$this->token}/{$method}");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $arrayQuery);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type:multipart/form-data"]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $result = curl_exec($ch);
        curl_close($ch);

        if(!isset($result)){
            throw new Exception(curl_error($curl));
        }

        if(isset($arrResult["ok"]) && $arrResult["ok"] == false) {

            $arrResult = json_decode($result, true);
            $arrDataLog = [
                "method" => $method,
                "arrayQuery" => $arrayQuery,
                "arrResult" => $arrResult
            ];

            $arrDataLogJSON = json_encode($arrDataLog);
            throw new Exception($arrDataLogJSON);
        }

        if($resultJSON == true) {
            return $result;
        } else {
            return json_decode($result, true);
        }

    }
    /* ================================== */

}

try {
    $bot = new BotsTimeTelegram(TG_TOKEN);

    $arrayQuery = array(
        'chat_id' => 1424646511,
        'text'	=> "Тестовое сообщение",
        'parse_mode' => "html",
    );
    $resultQuery = $bot->sendQueryTelegram("sendMessage", $arrayQuery);

} catch (Exception $e) {
    $errorMessage = $e->getMessage();
    $bot->sendLogTelegram($errorMessage);
}