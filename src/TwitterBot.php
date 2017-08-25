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
     * @var array
     */
    protected $adminList = [];

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

    /**
     * Print a tweet somewhere
     *
     * @param $tweet
     * @param $channel
     */
    public function printTweet($tweet, $channel)
    {
        $this->sendMessage($this->getPrintableTweet($tweet), $channel);
    }

    /**
     * Get printable tweet
     *
     * @param $status
     * @return string
     */
    private function getPrintableTweet($status)
    {
        $when = new \DateTime($status->created_at, new \DateTimeZone('UTC'));
        $when->setTimezone(new \DateTimeZone('America/New_York'));
        $text = str_replace(["\n", "\t", "\r"], '', $status->text);

        $name = '@' . $status->user->screen_name;
        $body = html_entity_decode($text);
        $date = $when->format('m/d/Y g:iA T');

        $body = $this->replaceUrls($body, $status);

        if (!empty($status->retweeted_status))
        {
            $body = $this->replaceUrls($body, $status->retweeted_status);
        }

        if (!empty($status->quoted_status))
        {
            $body = $this->replaceUrls($body, $status->quoted_status);
        }

        return $name . ': ' . $body . ' ' . $date;
    }

    /**
     * Replace urls
     *
     * @param $body
     * @param $source
     * @return mixed
     */
    private function replaceUrls($body, $source)
    {
        if (!empty($source->entities->url->urls))
        {
            foreach ($source->entities->url->urls as $url)
            {
                $body = str_replace($url->url, $url->expanded_url, $body);
            }
        }

        if (!empty($source->entities->urls))
        {
            foreach ($source->entities->urls as $url)
            {
                $body = str_replace($url->url, $url->expanded_url, $body);
            }
        }

        if (!empty($source->extended_entities->media))
        {
            foreach ($source->extended_entities->media as $media)
            {
                $body = str_replace($media->url, $media->media_url_https, $body);
            }
        }

        return $body;
    }

    /**
     * Admin nick
     *
     * @param $nick
     */
    public function addAdminNick($nick)
    {
        $this->adminList[$nick] = true;
    }

    /**
     * Is admin
     *
     * @param \SlzBot\IRC\User $user
     * @return bool
     */
    public function isAdmin(\SlzBot\IRC\User $user)
    {
        if (!empty($this->adminList[$user->nickName])) return true;

        return false;
    }

}