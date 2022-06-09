<?php
    /*
    * FILE: index.php
    * DATE: 06/08/2292
    * DESCRIPTION: main script file for twitter bot
    * AUTHOR: stucombs at icloud dot com
    */
    require_once( './required/configuration.php' );
    require_once('./classes/Tweeter.class.php');
    DEFINE('READFILE', 'assets/apickle.txt');

    global $_APPLICATION;
    $settings = array(
        'ACCOUNT_ID'            => $_APPLICATION['KEYS']['ACCOUNT_ID'],
        'CONSUMER_KEY'          => $_APPLICATION['KEYS']['CONSUMER_KEY'],
        'CONSUMER_SECRET'       => $_APPLICATION['KEYS']['CONSUMER_SECRET'],
        'ACCESS_TOKEN'          => $_APPLICATION['KEYS']['ACCESS_TOKEN'],
        'ACCESS_TOKEN_SECRET'   => $_APPLICATION['KEYS']['ACCESS_TOKEN_SECRET']
    );

    $tweeter = new Tweeter($settings, READFILE);
    $tweet = $tweeter->getTweetText();
    $tweeter->postTweet($tweet);
?>