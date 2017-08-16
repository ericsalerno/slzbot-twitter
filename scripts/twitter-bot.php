<?php
/**
 * The sample expects some command line arguments and will add all the commands available
 *
 * @package SlzBot
 * @subpackage IRC
 * @author Eric Salerno
 */
require_once(__DIR__ . '/../vendor/autoload.php');

if (!is_file(__DIR__ . '/../config/connect.json'))
{
    echo "You must copy the configuration files back and updated them before running this script. See README.md for more information.";
    exit();
}

$connectionInfo = json_decode(file_get_contents(__DIR__ . '/../config/connect.json'));

$autojoin = new \SlzBot\IRC\Events\Autojoin();
$autojoin->setAutoJoins($connectionInfo->autoJoin);

$bot = new \TwitterBot\TwitterBot();

$bot->setUser($connectionInfo->nick, $connectionInfo->realName)
    ->setServer($connectionInfo->server, $connectionInfo->port)
    ->addOpCodeEvent(\SlzBot\IRC\OpCodes::EVENT_READY, $autojoin)
    //->addCommand('join', new \SlzBot\IRC\Commands\Join())
    //->addCommand('part', new \SlzBot\IRC\Commands\Part())
    //->addCommand('quit', new \SlzBot\IRC\Commands\Quit())
    //->addCommand('say', new \SlzBot\IRC\Commands\Say())
    //->addCommand('hello', new \SlzBot\IRC\Commands\TestColors())
    ->addCommand('uptime', new \SlzBot\IRC\Commands\Uptime())
    ->addCommand('tweet', new \TwitterBot\Commands\Tweets())
    ->addCommand('tweets', new \TwitterBot\Commands\Tweets())
    ->addCommand('twitter', new \TwitterBot\Commands\Tweets())
    ->setDebug(true)
    ->connect();