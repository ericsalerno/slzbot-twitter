# slzbot-twitter

A slzbot irc bot that has a twitter implementation.

This is an example implementation of ericsalerno/slzbot-irc.

## Installation

Clone the repository.

Run composer install in the directory to install dependencies.

Copy the config/connect.json and config/twitter.json files into the /config parent directory and edit them. Add the required fields.

Optionally you can enable/disable commands if you edit the scripts/twitter-bot.php file.

Run the script

    /path/to/php scripts/twitter-bot.php

Then when the bot joins your channel, you can send it !tweet @username to activate it.

    !tweet @salernolabs 3