<?php
use skrtdev\NovaGram\Bot;
use skrtdev\Telegram\Message;

require_once 'core.php';
init_class();

$Bot = new Bot(BOT_TELEGRAM_API_KEY);

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
