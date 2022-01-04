<?php
use skrtdev\NovaGram\Bot;
use skrtdev\Telegram\Message;

require_once 'utils.php';

function telegramBotRouter() {
    if(defined("BASE_PATH")){
        $base_path = "/".BASE_PATH."api/bot/telegram";
    } else {
        $base_path = "/api/bot/telegram";
    }
    $_SERVER['SCRIPT_URL'] = $base_path;

    //TODO: fix NovaGram ip check and enable it again
    $Bot = new Bot(BOT_TELEGRAM_API_KEY, ["disable_ip_check" => true]);

    $Bot->addErrorHandler(function ($e) {
        print('Caught '.get_class($e).' exception from general handler'.PHP_EOL);
        print($e.PHP_EOL);
    });
    
    $Bot->onCommand('start', function (Message $message, array $args = []) {
        var_dump($message, $args);
        $message->reply('Hey! Nice to meet you. Use /info to know more about me.');
    });
    
    $Bot->onCommand('info', function (Message $message) {
        $message->reply('Well, I\'m just an example, but you can learn more about NovaGram at docs.novagram.ga');
    });
    
    $Bot->start();
}