<?php

require_once(__DIR__ . "/vendor/autoload.php");


// 1, 创建客户端
$client = \Elasticsearch\ClientBuilder::create()->build();

// 2, 创建搜索体(body)
$search = new \ONGR\ElasticsearchDSL\Search();

// 3, 布尔子查询
$boolQuery = new \ONGR\ElasticsearchDSL\Query\Compound\BoolQuery();

// 4, 增加bool -> must子句
$boolQuery->add(
    new \ONGR\ElasticsearchDSL\Query\FullText\MatchPhraseQuery("article_title", "西装"),
    \ONGR\ElasticsearchDSL\Query\Compound\BoolQuery::MUST
);

// 5, 增加bool -> filter子句
$boolQuery->add(
    new \ONGR\ElasticsearchDSL\Query\TermLevel\RangeQuery("publish_time", [\ONGR\ElasticsearchDSL\Query\TermLevel\RangeQuery::LTE => time()]),
    \ONGR\ElasticsearchDSL\Query\Compound\BoolQuery::FILTER
);

// 6, 增加bool -> must_not子句
$boolQuery->add(
    new \ONGR\ElasticsearchDSL\Query\TermLevel\TermsQuery('article_type', ['食品', '家居']),
    \ONGR\ElasticsearchDSL\Query\Compound\BoolQuery::MUST_NOT
);

// 7, 布尔查询添加到search
$search->addQuery($boolQuery);

// 8, 设置翻页
$search->setFrom(0);
$search->setSize(10);

// 9, 增加一个排序规则
$search->addSort(new \ONGR\ElasticsearchDSL\Sort\FieldSort('publish_time', 'desc', ['missing' => 0]));

// 10, 创建一个bucket filter agg（生成2个桶，匿名发布的文章anony_articles和实名发布的文章no_anony_articles）
$filterAgg = new \ONGR\ElasticsearchDSL\Aggregation\Bucketing\FiltersAggregation('anonymous_bucketing', [
    'anony_articles' => new \ONGR\ElasticsearchDSL\Query\TermLevel\TermQuery('is_anonymous', 1)
]);
$filterAgg->setParameters([
    'other_bucket_key' =>  'no_anony_articles']
);

// 11, 对每个桶执行top hits metrics agg
$metricsAgg = new ONGR\ElasticsearchDSL\Aggregation\Metric\TopHitsAggregation('latest_articles', 10, 0, new ONGR\ElasticsearchDSL\Sort\FieldSort('publish_time', 'desc'));
// top his agg返回的文档只包含article_title字段
$metricsAgg->addParameter('_source', ['includes' => ['article_title']]);

// 12, metrics agg添加到bucket  agg下面
$filterAgg->addAggregation($metricsAgg);

// 13, agg添加到search
$search->addAggregation($filterAgg);

// 14, 生成请求体body
$body = $search->toArray();

// 15, 生成完整请求
$request = [
    'index' => 'article',
    'type' => 'doc',
    'body' => $body,
];

$response = $client->search($request);

echo json_encode($request) . PHP_EOL;
echo json_encode($response) . PHP_EOL;