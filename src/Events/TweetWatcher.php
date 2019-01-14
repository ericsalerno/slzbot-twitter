<?php
/**
 * Tweet Watcher Event
 *
 * @package TwitterBot
 * @subpackage Events
 * @author Eric
 */
namespace TwitterBot\Events;

class TweetWatcher implements \SlzBot\IRC\Events\EventInterface
{
    /**
     * @var string
     */
    private $lastTweetId;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $channel;

    /**
     * TweetWatcher constructor.
     * @param $parameters
     */
    public function __construct($parameters)
    {
        if (empty($parameters->name))
        {
            throw new \Exception('Missing required parameter name for TweetWatcher event.');
        }

        if (empty($parameters->channel))
        {
            throw new \Exception('Missing required parameter name for TweetWatcher event.');
        }

        $this->name = $parameters->name;

        $this->channel = $parameters->channel;
    }

    /**
     * Execute event
     *
     * @param \SlzBot\IRC\Bot $bot
     * @param array $parameters
     */
    public function execute(\SlzBot\IRC\Bot $bot, $parameters = [])
    {
        try
        {
            echo 'Automatically requesting tweets from ' . $this->name . '.' . PHP_EOL;

            if (empty($this->lastTweetId))
            {
                $q = 'q=from:' . $this->name . '&count=1';
            }
            else
            {
                $q = 'q=from:' . $this->name . '&since_id=' . $this->lastTweetId.  '&count=5';
            }

            $q .= '&tweet_mode=extended';

            $tweets = \TwitterBot\TwitterBot::$twitter->search_tweets($q, true);
        }
        catch (\Exception $exception)
        {
            $bot->sendMessage('Codebird request failed!', $this->channel);
            echo $exception->getMessage() . PHP_EOL;
            return;
        }

        if (empty($tweets->statuses))
        {
            return;
        }

        $this->lastTweetId = $tweets->statuses[0]->id;

        $tweets->statuses = array_reverse($tweets->statuses);
        foreach ($tweets->statuses as $status)
        {
            if (method_exists($bot, 'printTweet'))
            {
                $bot->printTweet($status, $this->channel);
            }
        }
    }
}
