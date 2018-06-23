<?php
return [
    'tempPath' => '../backups/',
    'storageEngines' => [
        'GoogleCloud' => [//not supported yet
            'type' => 'googleDrive',
            'path' => '',
            'active' => false
        ],
        'DropBox' => [
            'type' => 'dropBox',
            'path' => 'alpha-backup',
            'clientId' => '########',
            'clientSecret' => '########',
            'accessToken' => '#################################',
            'active' => true
        ],
        'Amazon S3' => [
            'type' => 'AWS3',
            'bucket' => 'bucket',
            'key' => 'key',
            'secret' => 'secret',
            'version' => 'latest',
            'region' => 'us-west-2',
            'active' => false
        ]
    ]
];

/*

types => googleDrive, dropBox
path => path to cloud/server

*/
?>