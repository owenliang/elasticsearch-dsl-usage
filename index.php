<?php

require_once(__DIR__ . "/vendor/autoload.php");

// 1, 创建客户端
$client = \Elasticsearch\ClientBuilder::create()->build();

$request = [
    'index' => 'article',
    'type' => 'doc',
    'body' => [
        'article_title' => '只卖88元的高级西装',
        'publish_time' => time(),
        'article_type' => '西装',
        'is_anonymous' => 1,
    ]
];

$client->index($request);

$request = [
    'index' => 'article',
    'type' => 'doc',
    'body' => [
        'article_title' => '只卖2000元的辣鸡西装',
        'publish_time' => time(),
        'article_type' => '高级西装',
        'is_anonymous' => 0,
    ]
];

$client->index($request);