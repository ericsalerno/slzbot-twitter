<?php
/**
 * Twitter Bot Adds Twitter Extensions
 *
 * @package TwitterBot
 * @subpackage IRC
 * @author Eric
 */
namespace TwitterBot;

class TwitterBot extends \SlzBot\IRC\Bot
{
    /**
     * @var \Codebird\Codebird
     */
    static public $twitter;

    /**
     * TwitterBot constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $twitterInfo = json_decode(file_get_contents(__DIR__ . '/../config/twitter.json'));

        \Codebird\Codebird::setConsumerKey($twitterInfo->consumerKey, $twitterInfo->consumerSecret);
        static::$twitter = \Codebird\Codebird::getInstance();
        static::$twitter->setToken($twitterInfo->accessToken, $twitterInfo->accessTokenSecret);
    }

}