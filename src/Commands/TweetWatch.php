<?php
/**
 * Adds a twitter user to the watch list
 *
 * @package SlzBot
 * @subpackage IRC
 * @author Eric
 */
namespace TwitterBot\Commands;

class TweetWatch implements \SlzBot\IRC\Commands\CommandInterface
{
    /**
     * @param \SlzBot\IRC\Bot $bot
     * @param \SlzBot\IRC\User $user
     * @param $channel
     * @param $parameters
     * @return mixed|void
     */
    public function execute(\SlzBot\IRC\Bot $bot, \SlzBot\IRC\User $user, $channel, $parameters)
    {
        if (method_exists($bot, 'IsAdmin'))
        {
            if (!$bot->isAdmin($user))
            {
                $bot->sendMessage("You're not my supervisor!", $channel);
                return;
            }
        }
        else
        {
            return;
        }

        $nickname = $parameters[0];

        if (strlen($nickname) < 2 || $nickname[0] != '@')
        {
            $bot->sendMessage('Please enter a valid nickname to watch.', $channel);
            return;
        }

        $timeout = !empty($parameters[1]) ? intval($parameters[1]) : 5;

        if ($timeout < 2) $timeout = 2;

        $tweetInfo = new \stdClass();
        $tweetInfo->name = substr($nickname, 1);
        $tweetInfo->channel = $channel;
        $tweetInfo->timeout = $timeout;

        try {
            $bot->addScheduledEvent(
                $timeout * 60 * 1000, /* 10 minutes */
                new \TwitterBot\Events\TweetWatcher($tweetInfo),
                0 /* zero seconds */
            );

            $bot->sendMessage("Now requesting tweets from @" .$tweetInfo->name . " every " . $tweetInfo->timeout . " minutes.", $channel);
        } catch (\Exception $exception) {
            $bot->sendMessage('Sorry there was a problem setting up that tweetWatch: ' . $exception->getMessage(), $channel);
        }
    }
}