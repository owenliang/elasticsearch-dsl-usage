<?php

require_once(__DIR__ . "/vendor/autoload.php");

use ONGR\ElasticsearchDSL\Search;
use ONGR\ElasticsearchDSL\Query\Compound\BoolQuery;
use ONGR\ElasticsearchDSL\Query\TermLevel\TermQuery;
use ONGR\ElasticsearchDSL\Query\TermLevel\RangeQuery;
use Elasticsearch\ClientBuilder;
use ONGR\ElasticsearchDSL\Query\FullText\MatchPhraseQuery;

// 1, 创建客户端
$client = ClientBuilder::create()->build();

// 2, 创建搜索请求
$search = new Search();

// 3, 布尔子查询
$boolQuery = new BoolQuery();

// 4, 增加bool -> must子句
$boolQuery->add(new MatchPhraseQuery("baoliao_title", "西装"), BoolQuery::MUST);

// 5, 增加bool -> filter子句
$boolQuery->add(new RangeQuery("baoliao_time", [RangeQuery::GTE, time()]), BoolQuery::FILTER);

// 6, 增加1个排序字段
$search->addSort(new \ONGR\ElasticsearchDSL\Sort\FieldSort("baoliao_time", "desc", ['missing' => 0]));

// 7, dsl转json请求
$search->addQuery($boolQuery);

print_r($search->toArray());