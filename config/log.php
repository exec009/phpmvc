<?php
return[
    'channels' => [
        'main' => [
              'handlers' => ['mainStream']
        ],
        'database' => [
              'handlers' => ['databaseStream']
        ],
        'cron' => [
              'handlers' => ['cronStream']
        ],
        'taskRunner' => [
              'handlers' => ['taskRunnerStream']
        ],
        'traffic' => [
              'handlers' => ['trafficStream']
        ]
    ],
    'handlers' => [
        'pushOver1' => [
            'type' => 'pushOver',
            'token' => 'auwnqcnpsnc4dx49bb1sivutxofn5i',
            'users' => ['ug5q67fgo2wspaczhqrq7jdtu6axqr'],
            'active' => true
        ],
        'loggly#1' => [
            'type' => 'loggly',
            'token' => '2a898bbc-540c-4c7a-816f-c90625f4d7a8',
            'active' => false
        ],
        'slackLog' => [
            'type' => 'slack',
            'token' => 'cglpL7KJ1VfR53JjIwV5JDzI',//username logger
            'slackTeam' => 'zechinc',
            'channel' => '#log',
            'user' => 'logger',
            'webHookUrl' => 'https://hooks.slack.com/services/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',
            'active' => false
        ],
        'mysqllog'=> [
            'type' => 'mysql',
            'host' => 'localhost',
            'username' => 'admin_default',
            'password' => 'alpha123',
            'database' => 'admin_default',
            'tableName' => 'logger',
            'active' => true
        ],
        'mainStream' => [
            'path' => root()."log/main/log.log",
            'type' => 'stream',
            'active' => true
        ],
        'databaseStream' => [
            'path' => root()."log/database/database.log",
            'type' => 'stream',
            'level' => 'emergency',
            'active' => true
        ],
        'cronStream' => [
            'path' => root()."log/cron/cron.log",
            'type' => 'stream',
            'active' => true
        ],
        'taskRunnerStream' => [
            'path' => root()."log/taskrunner/taskrunner.log",
            'type' => 'stream',
            'active' => true
        ],
        'trafficStream' => [
            'path' => root()."log/traffic/traffic.log",
            'type' => 'stream',
            'active' => true
        ],
    ]
];
/*
Pramaters
    levels => logging_levels


Handler Types
    stream
    pushOver
    loggly
    slack
    mysql

Logging Levels
    debug : Detailed debug information.
    info : Interesting events. Examples: User logs in, SQL logs.
    notice : Normal but significant events.
    warning : Exceptional occurrences that are not errors. Examples: Use of deprecated APIs, poor use of an API, undesirable things that are not necessarily wrong.
    error : Runtime errors that do not require immediate action but should typically be logged and monitored.
    critical : Critical conditions. Example: Application component unavailable, unexpected exception.
    alert : Action must be taken immediately. Example: Entire website down, database unavailable, etc. This should trigger the SMS alerts and wake you up.
    emerygency : Emergency: system is unusable.

*/
?>