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
        if (empty($parameters[0])) return;

        if ($parameters[0][0] != '@')
        {
            $bot->sendMessage('Try !tweet @username', $channel);
            return;
        }

        $name = mb_substr($parameters[0], 1);
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
            $when = new \DateTime($status->created_at, new \DateTimeZone('UTC'));
            $when->setTimezone(new \DateTimeZone('America/New_York'));
            $text = $status->text;
            $bot->sendMessage($parameters[0] . ': ' . html_entity_decode($text) . ' ' . $when->format('m/d/Y g:iA T'), $channel);
        }
    }
}