<?php
/**
 * Perform the Tweets Command
 *
 * @package SlzBot
 * @subpackage IRC
 * @author Eric
 */
namespace TwitterBot\Commands;

class Tweets implements \SlzBot\IRC\Commands\CommandInterface
{
    /**
     * Perform the command
     *
     * @param \SlzBot\IRC\Bot $bot
     * @param \SlzBot\IRC\User $user
     * @param $channel
     * @param $parameters
     */
    public function execute(\SlzBot\IRC\Bot $bot, \SlzBot\IRC\User $user, $channel, $parameters)
    {
        if (!empty($parameters[0]))
        {
            if ($parameters[0][0] == '@')
            {
                $this->executeUsernameSearch($bot, $user, $channel, $parameters);
                return;
            }
            else if ($parameters[0][0] == '#')
            {
                $this->executeHashtagSearch($bot, $user, $channel, $parameters);
                return;
            }
            else
            {
                $this->executeSearch($bot, $user, $channel, $parameters);
                return;
            }
        }

        $bot->sendMessage('Slzbot-Twitter, try "!tweet @username/#hashtag/search terms" to search tweets from the last seven days. Fork me on github! https://github.com/ericsalerno/slzbot-twitter', $channel);
    }

    /**
     * Execute username search
     *
     * @param \SlzBot\IRC\Bot $bot
     * @param \SlzBot\IRC\User $user
     * @param $channel
     * @param $parameters
     */
    private function executeUsernameSearch(\SlzBot\IRC\Bot $bot, \SlzBot\IRC\User $user, $channel, $parameters)
    {
        $name = mb_substr($parameters[0], 1);

        if (!preg_match('#^[a-zA-Z0-9_-]+$#', $name))
        {
            $bot->sendMessage("The username specified doesn't seem to match our filters.", $channel);
            return;
        }

        $count = (!empty($parameters[1]) ? intval($parameters[1]) : 1);
        if (empty($count))
        {
            $bot->sendMessage('Try !tweet @username <count from 1 to 5>', $channel);
            return;
        }

        if ($count > 5) $count = 5;

        try
        {
            $tweets = \TwitterBot\TwitterBot::$twitter->search_tweets('q=from:' . $name . '&count=' . $count . '&tweet_mode=extended', true);
        }
        catch (\Exception $exception)
        {
            $bot->sendMessage('Codebird request failed!', $channel);
            echo $exception->getMessage() . PHP_EOL;
            return;
        }

        if (\TwitterBot\TwitterBot::$twitterDebug === 'true')
        {
            print_r($tweets);
        }

        if (empty($tweets->statuses))
        {
            $bot->sendMessage('No results found!', $channel);
            return;
        }

        $tweets->statuses = array_reverse($tweets->statuses);
        foreach ($tweets->statuses as $status)
        {
            if (method_exists($bot, 'printTweet'))
            {
                $bot->printTweet($status, $channel);
            }
        }
    }

    /**
     * Execute hashtag search
     *
     * @param \SlzBot\IRC\Bot $bot
     * @param \SlzBot\IRC\User $user
     * @param $channel
     * @param $parameters
     */
    private function executeHashtagSearch(\SlzBot\IRC\Bot $bot, \SlzBot\IRC\User $user, $channel, $parameters)
    {
        $hashTag = $parameters[0];

        if (!preg_match('#^\#[a-zA-Z0-9_-]+$#', $hashTag))
        {
            $bot->sendMessage("Invalid hashtag, brochacho.", $channel);
            return;
        }

        $count = (!empty($parameters[1]) ? intval($parameters[1]) : 3);
        if (empty($count))
        {
            $bot->sendMessage('Try !tweet #hashtag <count from 1 to 5>', $channel);
            return;
        }

        if ($count > 5) $count = 5;

        try
        {
            $tweets = \TwitterBot\TwitterBot::$twitter->search_tweets('q=' . urlencode($hashTag) . '&count=' . $count . '&lang=en&tweet_mode=extended', true);
        }
        catch (\Exception $exception)
        {
            $bot->sendMessage('Codebird request failed!', $channel);
            echo $exception->getMessage() . PHP_EOL;
            return;
        }

        if (\TwitterBot\TwitterBot::$twitterDebug === 'true')
        {
            print_r($tweets);
        }

        if (empty($tweets->statuses))
        {
            $bot->sendMessage('No results found!', $channel);
            return;
        }

        $tweets->statuses = array_reverse($tweets->statuses);
        foreach ($tweets->statuses as $status)
        {
            if (method_exists($bot, 'printTweet'))
            {
                $bot->printTweet($status, $channel);
            }
        }
    }

    /**
     * Do a regular keyword search
     *
     * @param \SlzBot\IRC\Bot $bot
     * @param \SlzBot\IRC\User $user
     * @param $channel
     * @param $parameters
     */
    private function executeSearch(\SlzBot\IRC\Bot $bot, \SlzBot\IRC\User $user, $channel, $parameters)
    {
        $searchString = "";

        $parameterCount = count($parameters);
        $count = 3;
        foreach ($parameters as $index => $parameter)
        {
            if ($index == ($parameterCount - 1) && is_numeric($parameter))
            {
                $count = intval($parameter);
            }
            else
            {
                if (!empty($searchString)) $searchString .= ' ';
                $searchString .= $parameter;
            }
        }

        if ($count > 5) $count = 5;

        try
        {
            $tweets = \TwitterBot\TwitterBot::$twitter->search_tweets('q=' . urlencode($searchString) . '&count=' . $count . '&lang=en&tweet_mode=extended', true);
        }
        catch (\Exception $exception)
        {
            $bot->sendMessage('Codebird request failed!', $channel);
            echo $exception->getMessage() . PHP_EOL;
            return;
        }

        if (\TwitterBot\TwitterBot::$twitterDebug === 'true')
        {
            print_r($tweets);
        }

        if (empty($tweets->statuses))
        {
            $bot->sendMessage('No results found!', $channel);
            return;
        }

        $tweets->statuses = array_reverse($tweets->statuses);
        foreach ($tweets->statuses as $status)
        {
            if (method_exists($bot, 'printTweet'))
            {
                $bot->printTweet($status, $channel);
            }
        }
    }

}