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
        }

        $bot->sendMessage('Slzbot-Twitter, fork me on github! Try "!tweet @username/#hashtag" to search tweets from the last seven days.', $channel);
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
            $tweets = \TwitterBot\TwitterBot::$twitter->search_tweets('q=from:' . $name . '&count=' . $count, true);
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
            $bot->sendMessage($this->getPrintableTweet($status), $channel);
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
            $tweets = \TwitterBot\TwitterBot::$twitter->search_tweets('q=' . urlencode($hashTag) . '&count=' . $count . '&lang=en', true);
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
            $bot->sendMessage($this->getPrintableTweet($status), $channel);
        }
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
            $body = $this->replaceUrls($body, $status->retweeted_status);
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
}