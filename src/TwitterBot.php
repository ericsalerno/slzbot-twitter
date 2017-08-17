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
     * @var bool
     */
    static public $twitterDebug = false;

    /**
     * TwitterBot constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $filename = __DIR__ . '/../config/twitter.json';
        $twitterInfo = json_decode(file_get_contents($filename));

        if (empty($twitterInfo))
        {
            echo "Can not setup twitter! Can't read contents of the json file!\n";
            return;
        }

        static::$twitter = \Codebird\Codebird::getInstance();

        static::$twitter->setConsumerKey($twitterInfo->consumerKey, $twitterInfo->consumerSecret);

        if (!empty($twitterInfo->accessToken) && !empty($twitterInfo->accessTokenSecret))
        {
            static::$twitter->setToken($twitterInfo->accessToken, $twitterInfo->accessTokenSecret);
        }
        else
        {
            if (!empty($twitterInfo->bearerToken))
            {
                static::$twitter->setBearerToken($twitterInfo->bearerToken);
            }
            else
            {
                $reply = static::$twitter->oauth2_token();
                $bearerToken = $reply->access_token;

                $twitterInfo->bearerToken = $bearerToken;
                if (!file_put_contents($filename, json_encode($twitterInfo, JSON_PRETTY_PRINT)))
                {
                    echo 'Can\'t save the token. Your bearer token is "' . $bearerToken . '" you should put this in your config file as bearerToken.' . " \n";
                }

                static::$twitter->setBearerToken($bearerToken);
            }
        }

        static::$twitterDebug = $twitterInfo->debug;
    }

}