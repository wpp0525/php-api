<?php

namespace Lvmama\Cas\Service;

use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Components\ApiClient;

/**
 * 推广报告信息 服务类
 *
 * @author flash.guo
 *
 */
class SemReportEsDataService extends DataServiceBase
{

    protected $baseUri = 'http://172.20.4.178:9200/';
    private $ttl       = 1800;

    public function __construct($di, $redis = null, $beanstalk = null)
    {
        $this->di        = $di;
        $this->redis     = $redis;
        $this->beanstalk = $beanstalk;
        if ($di->get('config')->elasticsearch->toArray()) {
            $this->baseUri = 'http://' . $di->get('config')->elasticsearch->toArray()['host'] . ':' . $di->get('config')->elasticsearch->toArray()['port'];
        }
        $this->client = new ApiClient($this->baseUri);
    }

    /**
     * @purpose 根据条件获取推广报告信息
     * @param $terms 查询条件
     * @param $range 查询范围
     * @param $limit 查询条数
     * @param $group 查询分组
     * @param $sort 查询排序
     * @param $interval 时间分段
     * @param $platform 所属平台
     * @return array|mixed
     */
    public function getReportList($terms, $range, $limit, $group, $sort, $interval, $platform)
    {
        $must = $must_not = array();
        foreach ($terms as $key => $term) {
            if (!is_array($term) && strstr($term, "!=")) {
                $must_not[] = array("term" => array($key => trim(str_replace("!=", "", $term))));
            } else {
                $must[] = array((is_array($term) ? "terms" : "term") => array($key => $term));
            }
        }
        //筛选条件
        $query = array(
            "query" => array(
                "filtered" => array(
                    "query"  => array(
                        "bool" => array("must" => $must, "must_not" => $must_not),
                    ),
                    "filter" => empty($range) ? array() : array("range" => $range),
                ),
            ),
        );
        if ($group) {
            $query["size"] = 0;
            //统计聚合数据
            $query["aggs"] = array(
                "group_" . $group => array(
                    "aggs" => array(
                        "impression" => array(
                            "sum" => array("field" => "impression"),
                        ),
                        "click"      => array(
                            "sum" => array("field" => "click"),
                        ),
                        "cost"       => array(
                            "sum" => array("field" => "cost"),
                        ),
                        "orderNum"   => array(
                            "sum" => array("field" => "orderNum"),
                        ),
                        "amount"     => array(
                            "sum" => array("field" => "amount"),
                        ),
                    ),
                ),
            );
            if ($group == 'date_histogram') {
//按天分组
                $query["aggs"]["group_" . $group]["date_histogram"] = array(
                    "field"     => "date",
                    "format"    => "yyyy-MM-dd",
                    "time_zone" => "+08:00",
                    "interval"  => empty($interval) ? "day" : $interval,
                );
                //获取对losc分组后订单数据，目前仅适用于选定部分计划、单元或关键词的情况
                if (!empty($terms['campaignName']) || !empty($terms['adgroupName']) || !empty($terms['keywordId'])) {
                    $query["aggs"]["group_" . $group]["aggs"]["group_date"] = array(
                        "aggs"  => array(
                            "group_losc" => array(
                                "aggs"  => array(
                                    "orderNum" => array(
                                        "min" => array("field" => "orderNum"),
                                    ),
                                    "amount"   => array(
                                        "min" => array("field" => "amount"),
                                    ),
                                ),
                                "terms" => array(
                                    "field"   => "losc",
                                    "exclude" => "",
                                    "order"   => array("_term" => "asc"),
                                    "size"    => 0,
                                ),
                            ),
                            "orderNum"   => array(
                                "sum_bucket" => array("buckets_path" => "group_losc>orderNum"),
                            ),
                            "amount"     => array(
                                "sum_bucket" => array("buckets_path" => "group_losc>amount"),
                            ),
                        ),
                        "terms" => array(
                            "field" => "date",
                            "order" => array("_term" => "asc"),
                            "size"  => 0,
                        ),
                    );
                    $query["aggs"]["group_" . $group]["aggs"]["orderNum"] = array(
                        "sum_bucket" => array("buckets_path" => "group_date>orderNum"),
                    );
                    $query["aggs"]["group_" . $group]["aggs"]["amount"] = array(
                        "sum_bucket" => array("buckets_path" => "group_date>amount"),
                    );
                }
            } else {
                $query["aggs"]["group_" . $group]["terms"] = array(
                    "field" => $group,
                    "order" => empty($sort) ? array("_term" => "asc") : $sort,
                    "size"  => empty($limit['page_size']) ? 10000 : $limit['page_size'],
                );
            }
            if ($group == 'userName' || $group == 'campaignName' || $group == 'adgroupName' || $group == 'keywordId') {
//获取关键词父级信息
                $include = array("userName", "campaignName", "adgroupName", "keyword", "losc");
                array_search($group, $include) !== false && array_splice($include, array_search($group, $include) + 1);
                $query["aggs"]["group_" . $group]["aggs"]["top_hits"] = array(
                    "top_hits" => array(
                        "_source" => array(
                            "include" => $include,
                        ),
                        "size"    => 1,
                    ),
                );
            }
        } else {
            $query["size"] = empty($limit['page_size']) ? 10 : $limit['page_size'];
            $query["from"] = empty($limit['page_num']) ? 0 : ($limit['page_num'] - 1) * $limit['page_size'];
            $query["sort"] = empty($sort) ? array(array("click" => "desc"), array("impression" => "desc")) : $sort;
        }
        $result = array('list' => array(), 'pages' => array('itemCount' => 0, 'pageCount' => 0, 'page' => $limit['page_num'], 'pageSize' => $query["size"]));
        $query  = json_encode($query);
        $estype = $platform == 4 ? "sem_report_sogou" : ($platform == 3 ? "sem_report_smcn" : ($platform == 2 ? "sem_report_socom" : "sem_report")); //所属平台（1：百度 2：360 3：神马4：搜狗）
        $res    = $this->client->exec('lmm_sem/' . $estype . '/_search?preference=_primary_first', $query, '', 'post');
        if ($group) {
            $itemCount = 0;
            $result    = array('list' => array());
            if (isset($res['aggregations']['group_' . $group]['buckets'])) {
                foreach ($res['aggregations']['group_' . $group]['buckets'] as $val) {
                    $val['impression'] = $val['impression']['value'];
                    $val['click']      = $val['click']['value'];
                    $val['cost']       = $val['cost']['value'];
                    $val['orderNum']   = $val['orderNum']['value'];
                    $val['amount']     = $val['amount']['value'];
                    $val['ctr']        = $val['impression'] ? round($val['click'] / $val['impression'], 4) : 0;
                    $val['cpc']        = $val['click'] ? round($val['cost'] / $val['click'], 2) : 0;
                    $val['rate']       = $val['click'] ? round($val['orderNum'] / $val['click'], 4) : 0;
                    $val['roi']        = $val['cost'] ? round($val['amount'] / $val['cost'], 4) : 0;
                    $val[$group]       = ($group == 'date' || $group == 'date_histogram') ? date('Y-m-d H:i:s', intval($val['key'] / 1000)) : $val['key'];
                    !empty($val['top_hits']['hits']['hits'][0]['_source']) && $val += $val['top_hits']['hits']['hits'][0]['_source'];
                    unset($val['top_hits']);
                    unset($val['key_as_string']);
                    $val['cost']                  = round($val['cost'], 2);
                    $val['amount']                = round($val['amount'], 2);
                    $result['list'][$val[$group]] = $val;
                    unset($result['list'][$val[$group]]['key']);
                    unset($result['list'][$val[$group]]['group_date']);
                    $itemCount++;
                }
            }
            $pageCount       = intval(($itemCount - 1) / (empty($limit['page_size']) ? 10 : $limit['page_size']) + 1);
            $result['pages'] = array(
                'itemCount' => $itemCount,
                'pageCount' => $pageCount,
                'page'      => $pageCount > 0 && $limit['page_num'] > $pageCount ? $pageCount : $limit['page_num'],
                'pageSize'  => $query["size"],
            );
        } else {
            if (isset($res['hits'])) {
                $itemCount       = intval($res['hits']['total']);
                $itemCount       = $itemCount > 10000 ? 10000 : $itemCount;
                $pageCount       = intval(($itemCount - 1) / (empty($limit['page_size']) ? 10 : $limit['page_size']) + 1);
                $result['pages'] = array(
                    'itemCount' => $itemCount,
                    'pageCount' => $pageCount,
                    'page'      => $pageCount > 0 && $limit['page_num'] > $pageCount ? $pageCount : $limit['page_num'],
                    'pageSize'  => $result['pages']["pageSize"],
                );
                foreach ($res['hits']['hits'] as $val) {
                    $val['_source']['_id']      = $val['_id'];
                    $val['_source']['orderNum'] = empty($val['_source']['orderNum']) ? 0 : $val['_source']['orderNum'];
                    $val['_source']['amount']   = empty($val['_source']['amount']) ? 0 : $val['_source']['amount'];
                    $val['_source']['date']     = date('Y-m-d H:i:s', intval($val['_source']['date'] / 1000));
                    $result['list'][]           = $val['_source'];
                }
            }
        }
        $res = $val = $query = null;
        return $result;
    }

    /**
     * @purpose 根据条件获取hive推广报告信息
     * @param $terms 查询条件
     * @param $range 查询范围
     * @param $size 查询条数
     * @param $group 查询分组
     * @param $sort 查询排序
     * @param $interval 时间分段
     * @param $platform 所属平台
     * @param $nocache 是否缓存
     * @return array|mixed
     */
    public function getHiveReport($terms, $range, $limit, $group, $sort, $interval, $platform, $nocache)
    {
        $table = isset($range['date']['gte']) && $range['date']['gte'] / 1000 == strtotime(date("Y-m-d")) ? "sem_realtime_report" : "sem_report";
        $table = $platform == 4 ? "hive.sogou." . $table : ($platform == 3 ? "hive.smcn." . $table : ($platform == 2 ? "hive.socom." . $table : "hive.default." . $table)); //所属平台（1：百度 2：360 3：神马 4：搜狗）
        $where = "1 = 1";
        $where .= isset($range['date']['gte']) ? " and dateTime >= timestamp '" . date("Y-m-d H:i:s", $range['date']['gte'] / 1000) . "'" : "";
        $where .= isset($range['date']['lt']) ? " and dateTime < timestamp '" . date("Y-m-d H:i:s", $range['date']['lt'] / 1000) . "'" : "";
        foreach ($terms as $key => $term) {
            if (is_array($term)) {
                $where .= " and " . $key . " in(" . (in_array($key, array("device", "unitOfTime", "keywordId")) ? implode(",", $term) : "'" . implode("','", $term) . "'") . ")";
            } else {
                $where .= " and " . $key . " = " . (in_array($key, array("device", "unitOfTime", "keywordId")) ? $term : "'" . $term . "'");
            }
        }
        $grouparr = array("userName", "campaignName", "adgroupName", "losc", "keyword", "keywordId");
        array_search($group, $grouparr) !== false && array_splice($grouparr, array_search($group, $grouparr) + 1);
        $groups = implode(",", $grouparr);
        switch ($group) {
            case 'keywordId': //关键词不需要对losc去重
                $sum = "sum(impression) as impression,sum(click) as click,sum(cost) as cost,sum(orderNum) as orderNum,sum(amount) as amount";
                $sum = $sum . ",keywordId";
                $sql = "select " . $sum . " FROM " . $table . " where " . $where . " group by " . $group;
                $sql .= " limit " . $limit['page_size'];
                $field = "r.impression,r.click,r.cost,r.orderNum,r.amount,r2." . str_replace(",", ",r2.", $groups);
                $join  = "(select a.userName,a.campaignName,a.adgroupName,a.losc,a.keyword,a.keywordId from " . $table . " a right join ";
                $join .= "(select keywordId,max(dateTime) as dateTime from " . $table . " where " . $where . " group by keywordId) b ";
                $join .= "on b.keywordId = a.keywordId and a.dateTime = b.dateTime where " . str_replace(" and ", " and a.", $where) . ")"; //注意关键词从属关系会变动，取最新一天的数据
                $sql = "select " . $field . " FROM (" . $sql . ") r left join " . $join . " r2 on r2.keywordId = r.keywordId";
                break;
            case 'date':
            case 'date_histogram':
                if (empty($terms['campaignName']) && empty($terms['adgroupName']) && empty($terms['keywordId'])) {
                    return true;
                }
//后面直接查mysql就可以了
                $sum = "sum(impression) as impression,sum(click) as click,sum(cost) as cost,";
                $sum .= "sum(case when losc is null or orderNum is null then 0 else orderNum end) as orderNum,";
                $sum .= "sum(case when losc is null or amount is null then 0 else amount end) as amount,";
                $interval && $sum .= $interval == 'day' ? "min(day) as day," : $interval . ",";
                $sum .= "min(dateTime) as dateTime";
                $groupby  = $interval && $interval != 'day' ? $interval . "(dateTime)," : "";
                $submin   = "losc,sum(impression) as impression,sum(click) as click,sum(cost) as cost,min(orderNum) as orderNum,min(amount) as amount,min(device) as device,dateTime";
                $submin   = $interval ? $interval . "(dateTime) as " . $interval . "," . $submin : $submin;
                $sub      = "(select " . $submin . " FROM " . $table . " where " . $where . " group by dateTime," . $groupby . "losc)";
                $newgroup = $interval ? ($interval != 'day' ? $interval : "dateTime") : "dateTime";
                $sql      = "select " . $sum . " FROM " . $sub . " group by " . $newgroup . " order by dateTime";
                break;
            case "losc":
                $grouparr = array("losc");
                $sum      = "sum(impression) as impression,sum(click) as click,sum(cost) as cost,";
                $sum .= "sum(case when losc is null or orderNum is null then 0 else orderNum end) as orderNum,";
                $sum .= "sum(case when losc is null or amount is null then 0 else amount end) as amount";
                $sum    = $sum . ",losc";
                $submin = "losc,sum(impression) as impression,sum(click) as click,sum(cost) as cost,min(orderNum) as orderNum,min(amount) as amount";
                $sub    = "(select " . $submin . " FROM " . $table . " where " . $where . " group by dateTime,losc)";
                $sql    = "select " . $sum . " FROM " . $sub . " group by losc";
                $sql .= " limit " . $limit['page_size'];
                break;
            case "userName":
            case "campaignName":
            case "adgroupName":
                $sum = "sum(impression) as impression,sum(click) as click,sum(cost) as cost,";
                $sum .= "sum(case when losc is null or orderNum is null then 0 else orderNum end) as orderNum,";
                $sum .= "sum(case when losc is null or amount is null then 0 else amount end) as amount";
                $sum    = $sum . "," . $groups;
                $submin = $groups . ",losc,sum(impression) as impression,sum(click) as click,sum(cost) as cost,min(orderNum) as orderNum,min(amount) as amount";
                $sub    = "(select " . $submin . " FROM " . $table . " where " . $where . " group by dateTime," . $groups . ",losc)";
                $subsum = $groups . ",losc,sum(impression) as impression,sum(click) as click,sum(cost) as cost,sum(orderNum) as orderNum,sum(amount) as amount";
                $sub    = "(select " . $subsum . " FROM " . $sub . " group by " . $groups . ",losc)";
                $sql    = "select " . $sum . " FROM " . $sub . " group by " . $groups;
                $sql .= " limit " . $limit['page_size'];
                break;
            default:
                return true; //后面直接查mysql就可以了
        }

        $res    = $this->di->get('cas')->getRedis()->get("sem-presto:" . md5($sql));
        $expire = isset($range['date']['gte']) && $range['date']['gte'] / 1000 == strtotime(date("Y-m-d")) ? 3600 : 86400; //当天报告仅缓存1小时
        if ($res === false || !empty($nocache)) {
            $this->di->get('presto')->Query($sql);
            $this->di->get('presto')->WaitQueryExec();
            $res = $this->di->get('presto')->GetData();
            $this->di->get('cas')->getRedis()->setex(
                "sem-presto:" . md5($sql),
                $expire,
                json_encode($res)
            );
        } else {
            $res = json_decode($res, true);
        }

        $result = array('list' => array(), 'pages' => array('itemCount' => 0, 'pageCount' => 0, 'page' => $limit['page_num'], 'pageSize' => $limit["page_size"]));
        foreach ($res as $val) {
            $count                                                        = count($val);
            ($group == 'date' || $group == 'date_histogram') && $dateTime = strtotime($val[$count - 1]);
            $interval && $val[$interval]                                  = $val[$count - 2];
            switch ($interval) {
                case "week":
                    $weekNum  = date("w", $dateTime) ? date("w", $dateTime) : 7; //星期天为0需转换
                    $dateTime = mktime(0, 0, 0, date("m", $dateTime), date("d", $dateTime) + 1 - $weekNum, date("Y", $dateTime));
                    break;
                case "month":
                    $dateTime = mktime(0, 0, 0, date("m", $dateTime), 1, date("Y", $dateTime));
                    break;
                default:;
            }
            $val['impression'] = intval($val[0]);
            $val['click']      = intval($val[1]);
            $val['cost']       = $val[2];
            $val['orderNum']   = intval($val[3]);
            $val['amount']     = $val[4];
            $val['ctr']        = $val['impression'] ? round($val['click'] / $val['impression'], 4) : 0;
            $val['cpc']        = $val['click'] ? round($val['cost'] / $val['click'], 2) : 0;
            $val['rate']       = $val['click'] ? round($val['orderNum'] / $val['click'], 4) : 0;
            $val['roi']        = $val['cost'] ? round($val['amount'] / $val['cost'], 4) : 0;
            $val[$group]       = ($group == 'date' || $group == 'date_histogram') ? date('Y-m-d H:i:s', $dateTime) : $val[$count - 1];
            $val['cost']       = round($val['cost'], 2);
            $val['amount']     = round($val['amount'], 2);
            foreach ($grouparr as $k => $g) {
                if (array_search($group, $grouparr) !== false && isset($val[$k + 5])) {
                    $val[$g] = $val[$k + 5];
                }

                unset($val[$k + 5]);
            }
            unset($val[0], $val[1], $val[2], $val[3], $val[4]);
            $result['list'][$val[$group]] = $val;
        }
        $res = null;
        return $result;
    }

    /**
     * @purpose 根据条件获取热销产品信息
     * @param $terms 查询条件
     * @param $limit 查询条数
     * @param $sort 查询排序
     * @return array|mixed
     */
    public function getProductList($terms, $limit, $sort)
    {
        $must = $must_not = array();
        foreach ($terms as $key => $term) {
            if (!is_array($term) && strstr($term, "!=")) {
                $must_not[] = array("term" => array($key => trim(str_replace("!=", "", $term))));
            } else {
                $must[] = array((is_array($term) ? "terms" : "term") => array($key => $term));
            }
        }
        //筛选条件
        $query = array(
            "query" => array(
                "filtered" => array(
                    "query" => array(
                        "bool" => array("must" => $must, "must_not" => $must_not),
                    ),
                ),
            ),
        );
        $query["size"] = empty($limit['page_size']) ? 10 : $limit['page_size'];
        $query["from"] = empty($limit['page_num']) ? 0 : ($limit['page_num'] - 1) * $limit['page_size'];
        $query["sort"] = empty($sort) ? array(array("product_id" => "asc")) : $sort;
        $result        = array('list' => array(), 'pages' => array('itemCount' => 0, 'pageCount' => 0, 'page' => $limit['page_num'], 'pageSize' => $query["size"]));
        $query         = json_encode($query);
        $res           = $this->client->exec('lmm_destination/ly_hotsale_product/_search?preference=_primary_first', $query, '', 'post');
        if (isset($res['hits'])) {
            $itemCount       = intval($res['hits']['total']);
            $itemCount       = $itemCount > 10000 ? 10000 : $itemCount;
            $pageCount       = intval(($itemCount - 1) / (empty($limit['page_size']) ? 10 : $limit['page_size']) + 1);
            $result['pages'] = array(
                'itemCount' => $itemCount,
                'pageCount' => $pageCount,
                'page'      => $pageCount > 0 && $limit['page_num'] > $pageCount ? $pageCount : $limit['page_num'],
                'pageSize'  => $result['pages']["pageSize"],
            );
            foreach ($res['hits']['hits'] as $val) {
                $val['_source']['_id']         = $val['_id'];
                $val['_source']['update_time'] = date('Y-m-d H:i:s', strtotime($val['_source']['update_time']));
                $val['_source']['create_time'] = date('Y-m-d H:i:s', strtotime($val['_source']['create_time']));
                $result['list'][]              = $val['_source'];
            }
        }
        $res = $val = $query = null;
        return $result;
    }
    /**
     *专题订单数统计
     */
    public function getSubjectOrder($terms, $range, $limit, $sort)
    {
        $must = $must_not = array();
        foreach ($terms as $key => $term) {
            if (!is_array($term) && strstr($term, "!=")) {
                $must_not[] = array("term" => array($key => trim(str_replace("!=", "", $term))));
            } else {
                $must[] = array((is_array($term) ? "terms" : "term") => array($key => $term));
            }
        }
        //筛选条件
        $query = array(
            "query" => array(
                "filtered" => array(
                    "query"  => array(
                        "bool" => array("must" => $must, "must_not" => $must_not),
                    ),
                    "filter" => empty($range) ? array() : array("range" => $range),
                ),
            ),
        );

        $query["size"] = empty($limit['page_size']) ? 10 : $limit['page_size'];
        $query["from"] = empty($limit['page_num']) ? 0 : ($limit['page_num'] - 1) * $limit['page_size'];
        $query["sort"] = empty($sort) ? array(array("losc" => "asc")) : $sort;

        $result = array('list' => array(), 'pages' => array('itemCount' => 0, 'pageCount' => 0, 'page' => $limit['page_num'], 'pageSize' => $query["size"]));
        $query  = json_encode($query);
        $res    = $this->client->exec('lmm_losc/subject_order/_search?preference=_primary_first', $query, '', 'post');

        if (isset($res['hits'])) {
            $itemCount       = intval($res['hits']['total']);
            $itemCount       = $itemCount > 10000 ? 10000 : $itemCount;
            $pageCount       = intval(($itemCount - 1) / (empty($limit['page_size']) ? 10 : $limit['page_size']) + 1);
            $result['pages'] = array(
                'itemCount' => $itemCount,
                'pageCount' => $pageCount,
                'page'      => $pageCount > 0 && $limit['page_num'] > $pageCount ? $pageCount : $limit['page_num'],
                'pageSize'  => $result['pages']["pageSize"],
            );
            foreach ($res['hits']['hits'] as $val) {
                $val['_source']['date'] = date('Y-m-d H:i:s', strtotime($val['_source']['date']));
                $result['list'][]       = $val['_source'];
            }
        }

        $res = $val = $query = null;
        return $result;
    }
    /**
     * @purpose 根据条件获取losc订单信息
     * @param $terms 查询条件
     * @param $range 查询范围
     * @param $limit 查询条数
     * @param $group 查询分组
     * @param $sort 查询排序
     * @param $interval 时间分段
     * @return array|mixed
     */
    public function getLoscOrder($terms, $range, $limit, $group, $sort, $interval)
    {
        $must = $must_not = array();
        foreach ($terms as $key => $term) {
            if (!is_array($term) && strstr($term, "!=")) {
                $must_not[] = array("term" => array($key => trim(str_replace("!=", "", $term))));
            } else {
                $must[] = array((is_array($term) ? "terms" : "term") => array($key => $term));
            }
        }
        //筛选条件
        $query = array(
            "query" => array(
                "filtered" => array(
                    "query"  => array(
                        "bool" => array("must" => $must, "must_not" => $must_not),
                    ),
                    "filter" => empty($range) ? array() : array("range" => $range),
                ),
            ),
        );
        if ($group) {
            $query["size"] = 0;
            //统计聚合数据
            $query["aggs"] = array(
                "group_" . $group => array(
                    "aggs" => array(
                        "orderNum" => array(
                            "sum" => array("field" => "orderNum"),
                        ),
                        "amount"   => array(
                            "sum" => array("field" => "amount"),
                        ),
                    ),
                ),
            );
            if ($group == 'date_histogram') {
                $query["aggs"]["group_" . $group]["date_histogram"] = array(
                    "field"     => "date",
                    "format"    => "yyyy-MM-dd",
                    "time_zone" => "+08:00",
                    "interval"  => empty($interval) ? "day" : $interval,
                );
                if (!empty($terms['losc'])) {
                    $query["aggs"]["group_" . $group]["aggs"]["group_losc"] = array(
                        "aggs"  => array(
                            "orderNum" => array(
                                "sum" => array("field" => "orderNum"),
                            ),
                            "amount"   => array(
                                "sum" => array("field" => "amount"),
                            ),
                        ),
                        "terms" => array(
                            "field" => "losc",
                            "order" => array("_term" => "asc"),
                            "size"  => 0,
                        ),
                    );
                    $query["aggs"]["group_" . $group]["aggs"]["orderNum"] = array(
                        "sum_bucket" => array("buckets_path" => "group_losc>orderNum"),
                    );
                    $query["aggs"]["group_" . $group]["aggs"]["amount"] = array(
                        "sum_bucket" => array("buckets_path" => "group_losc>amount"),
                    );
                }
            } else {
                $query["aggs"]["group_" . $group]["terms"] = array(
                    "field" => $group,
                    "order" => empty($sort) ? array("_term" => "asc") : $sort,
                    "size"  => empty($limit['page_size']) ? 1000 : $limit['page_size'],
                );
            }
        } else {
            $query["size"] = empty($limit['page_size']) ? 10 : $limit['page_size'];
            $query["from"] = empty($limit['page_num']) ? 0 : ($limit['page_num'] - 1) * $limit['page_size'];
            $query["sort"] = empty($sort) ? array(array("losc" => "asc")) : $sort;
        }
        $result = array('list' => array(), 'pages' => array('itemCount' => 0, 'pageCount' => 0, 'page' => $limit['page_num'], 'pageSize' => $query["size"]));
        $query  = json_encode($query);
        $res    = $this->client->exec('lmm_losc/losc_order/_search?preference=_primary_first', $query, '', 'post');
        if ($group) {
            $itemCount = 0;
            $result    = array('list' => array());
            if (isset($res['aggregations']['group_' . $group]['buckets'])) {
                foreach ($res['aggregations']['group_' . $group]['buckets'] as $val) {
                    $val['orderNum'] = $val['orderNum']['value'];
                    $val['amount']   = $val['amount']['value'];
                    $val[$group]     = ($group == 'date' || $group == 'date_histogram') ? date('Y-m-d H:i:s', intval($val['key'] / 1000)) : $val['key'];
                    unset($val['key_as_string']);
                    if (isset($val['group_losc']) && !empty($val['group_losc']['buckets'])) {
                        foreach ($val['group_losc']['buckets'] as $losc) {
                            $val[$losc['key']]['orderNum'] = $losc['orderNum']['value'];
                            $val[$losc['key']]['amount']   = $losc['amount']['value'];
                        }
                        unset($val['group_losc']);
                    }
                    $val['amount']                = round($val['amount'], 2);
                    $result['list'][$val[$group]] = $val;
                    unset($result['list'][$val[$group]]['key']);
                    $itemCount++;
                }
            }
            $pageCount       = intval(($itemCount - 1) / (empty($limit['page_size']) ? 10 : $limit['page_size']) + 1);
            $result['pages'] = array(
                'itemCount' => $itemCount,
                'pageCount' => $pageCount,
                'page'      => $pageCount > 0 && $limit['page_num'] > $pageCount ? $pageCount : $limit['page_num'],
                'pageSize'  => $query["size"],
            );
        } else {
            if (isset($res['hits'])) {
                $itemCount       = intval($res['hits']['total']);
                $itemCount       = $itemCount > 10000 ? 10000 : $itemCount;
                $pageCount       = intval(($itemCount - 1) / (empty($limit['page_size']) ? 10 : $limit['page_size']) + 1);
                $result['pages'] = array(
                    'itemCount' => $itemCount,
                    'pageCount' => $pageCount,
                    'page'      => $pageCount > 0 && $limit['page_num'] > $pageCount ? $pageCount : $limit['page_num'],
                    'pageSize'  => $result['pages']["pageSize"],
                );
                foreach ($res['hits']['hits'] as $val) {
                    $val['_source']['date'] = date('Y-m-d H:i:s', strtotime($val['_source']['date']));
                    $result['list'][]       = $val['_source'];
                }
            }
        }
        $res = $val = $query = null;
        return $result;
    }

    /**
     * @purpose 根据条件获取促销及优惠券报告信息
     * @param $terms 查询条件
     * @param $range 查询范围
     * @param $limit 查询条数
     * @param $group 查询分组
     * @param $sort 查询排序
     * @param $interval 时间分段
     * @return array|mixed
     */
    public function getPromCoupon($terms, $range, $limit, $group, $sort, $interval)
    {
        $must = $must_not = array();
        foreach ($terms as $key => $term) {
            if (!is_array($term) && strstr($term, "!=")) {
                $must_not[] = array("term" => array($key => trim(str_replace("!=", "", $term))));
            } else {
                $must[] = array((is_array($term) ? "terms" : "term") => array($key => $term));
            }
        }
        //筛选条件
        $query = array(
            "query" => array(
                "filtered" => array(
                    "query"  => array(
                        "bool" => array("must" => $must, "must_not" => $must_not),
                    ),
                    "filter" => empty($range) ? array() : array("range" => $range),
                ),
            ),
        );
        if ($group) {
            $query["size"] = 0;
            //统计聚合数据
            $query["aggs"] = array(
                "group_" . $group => array(
                    "aggs" => array(
                        "promotionChargeAmount" => array(
                            "sum" => array("field" => "promotionChargeAmount"),
                        ),
                        "promotionOrderAmount"  => array(
                            "sum" => array("field" => "promotionOrderAmount"),
                        ),
                        "couponChargeAmount"    => array(
                            "sum" => array("field" => "couponChargeAmount"),
                        ),
                        "couponOrderAmount"     => array(
                            "sum" => array("field" => "couponOrderAmount"),
                        ),
                    ),
                ),
            );
            if ($group == 'date_histogram') {
                $query["aggs"]["group_" . $group]["date_histogram"] = array(
                    "field"     => "date",
                    "format"    => "yyyy-MM-dd",
                    "time_zone" => "+08:00",
                    "interval"  => empty($interval) ? "day" : $interval,
                );
            } else {
                $query["aggs"]["group_" . $group]["terms"] = array(
                    "field" => $group,
                    "order" => empty($sort) ? array("_term" => "asc") : $sort,
                    "size"  => empty($limit['page_size']) ? 1000 : $limit['page_size'],
                );
            }
            if ($limit['page_num'] < 0) {
                unset($query["aggs"]["group_" . $group]["aggs"]);
            }
//只返回key
        } elseif ($limit['page_num'] < 0) {
            $query["size"] = 0;
            $query["aggs"] = array(
                "promotionChargeAmount" => array(
                    "sum" => array("field" => "promotionChargeAmount"),
                ),
                "promotionOrderAmount"  => array(
                    "sum" => array("field" => "promotionOrderAmount"),
                ),
                "couponChargeAmount"    => array(
                    "sum" => array("field" => "couponChargeAmount"),
                ),
                "couponOrderAmount"     => array(
                    "sum" => array("field" => "couponOrderAmount"),
                ),
            );
        } else {
            $query["size"] = empty($limit['page_size']) ? 10 : $limit['page_size'];
            $query["from"] = empty($limit['page_num']) ? 0 : ($limit['page_num'] - 1) * $limit['page_size'];
            $query["sort"] = empty($sort) ? array(array("date" => "desc")) : $sort;
        }
        $result  = array('list' => array(), 'pages' => array('itemCount' => 0, 'pageCount' => 0, 'page' => $limit['page_num'], 'pageSize' => $query["size"]));
        $query   = json_encode($query);
        $res     = $this->client->exec('lmm_promotion/promotion_coupon/_search?preference=_primary_first', $query, '', 'post');
        $buNames = array("LOCAL_BU" => "国内游事业部", "OUTBOUND_BU" => "出境游事业部", "DESTINATION_BU" => "目的地事业部", "TICKET_BU" => "景区玩乐事业部", "BUSINESS_BU" => "商旅定制事业部", "O2OWUXI_BU" => "O2O无锡子公司"); //bu中文名
        if ($group) {
            $itemCount = 0;
            $result    = array('list' => array());
            if (isset($res['aggregations']['group_' . $group]['buckets'])) {
                foreach ($res['aggregations']['group_' . $group]['buckets'] as $val) {
                    isset($val['promotionChargeAmount']) && $val['promotionChargeAmount'] = round($val['promotionChargeAmount']['value'], 2);
                    isset($val['promotionOrderAmount']) && $val['promotionOrderAmount']   = round($val['promotionOrderAmount']['value'], 2);
                    isset($val['couponChargeAmount']) && $val['couponChargeAmount']       = round($val['couponChargeAmount']['value'], 2);
                    isset($val['couponOrderAmount']) && $val['couponOrderAmount']         = round($val['couponOrderAmount']['value'], 2);
                    $val[$group]                                                          = ($group == 'date' || $group == 'date_histogram') ? date('Y-m-d H:i:s', intval($val['key'] / 1000)) : $val['key'];
                    isset($val['bu']) && $val['buName']                                   = isset($buNames[$val['bu']]) ? $buNames[$val['bu']] : "";
                    unset($val['key'], $val['key_as_string']);
                    $result['list'][$val[$group]] = $val;
                    $itemCount++;
                }
            }
            $pageCount       = intval(($itemCount - 1) / (empty($limit['page_size']) ? 10 : $limit['page_size']) + 1);
            $result['pages'] = array(
                'itemCount' => $itemCount,
                'pageCount' => $pageCount,
            );
        } elseif ($limit['page_num'] < 0) {
            $itemCount = 0;
            $result    = array('list' => array());
            if (isset($res['aggregations'])) {
                $result['list']['promotionChargeAmount'] = round($res['aggregations']['promotionChargeAmount']['value'], 2);
                $result['list']['promotionOrderAmount']  = round($res['aggregations']['promotionOrderAmount']['value'], 2);
                $result['list']['couponChargeAmount']    = round($res['aggregations']['couponChargeAmount']['value'], 2);
                $result['list']['couponOrderAmount']     = round($res['aggregations']['couponOrderAmount']['value'], 2);
                $itemCount                               = 4;
            }
            $result['pages'] = array(
                'itemCount' => $itemCount,
                'pageCount' => 1,
            );
        } else {
            if (isset($res['hits'])) {
                $itemCount       = intval($res['hits']['total']);
                $itemCount       = $itemCount > 10000 ? 10000 : $itemCount;
                $pageCount       = intval(($itemCount - 1) / (empty($limit['page_size']) ? 10 : $limit['page_size']) + 1);
                $result['pages'] = array(
                    'itemCount' => $itemCount,
                    'pageCount' => $pageCount,
                );
                foreach ($res['hits']['hits'] as $val) {
                    $val['_source']['promotionChargeAmount'] = round($val['_source']['promotionChargeAmount'], 2);
                    $val['_source']['promotionOrderAmount']  = round($val['_source']['promotionOrderAmount'], 2);
                    $val['_source']['couponChargeAmount']    = round($val['_source']['couponChargeAmount'], 2);
                    $val['_source']['couponOrderAmount']     = round($val['_source']['couponOrderAmount'], 2);
                    $val['_source']['buName']                = isset($buNames[$val['_source']['bu']]) ? $buNames[$val['_source']['bu']] : "";
                    $val['_source']['date']                  = date('Y-m-d H:i:s', strtotime($val['_source']['date']));
                    $result['list'][]                        = $val['_source'];
                }
            }
        }
        $res = $val = $query = null;
        return $result;
    }

    /**
     * @purpose 保存一条推广报告信息
     * @param $_id 报告信息索引id
     * @param $_data 报告信息数据
     * @param $_index 索引名称
     * @return array|mixed
     */
    public function saveReport($_id, $_data, $_index)
    {
        if (empty($_id) || empty($_data) || !is_array($_data)) {
            return false;
        }

        $result = $this->client->exec($_index . $_id, json_encode($_data), '', 'put');
        return $result;
    }

    /**
     * @purpose 删除一条推广报告信息
     * @param $_id 报告信息索引id
     * @param $_index 索引名称
     * @return array|mixed
     */
    public function delReport($_id, $_index = 'lmm_sem/sem_report/')
    {
        if (empty($_id)) {
            return false;
        }

        $result = $this->client->exec($_index . $_id, '', '', 'delete');
        return $result;
    }

    /**
     * 获取sem用户的所有消费数据,从mysql中直接获取
     * @author lixiumeng
     * @datetime 2018-01-02T17:49:20+0800
     * @param    [type]                   $platform  [description]
     * @param    [type]                   $condition [description]
     * @return   [type]                              [description]
     */
    public function getSemAccountCost($platform = 0, $condition = [])
    {
        $userCost = [];
        $arr      = ["sem_account_report", "sem_account_report_smcn", "sem_account_report_sogou", "sem_account_report_socom"];

        $this->adapter = $this->di->get('cas')->getDbServer('dbsem');

        $userInfo = $this->getUserInfo();

        foreach ($arr as $table) {
            $sql = "select userName,cost,dateTime from {$table} where `unitOfTime` = 5 and `device` = 0 and `dateTime` = '{$condition['date']}'";
            $rs  = $this->adapter->fetchAll($sql);
            if (!empty($rs)) {
                foreach ($rs as $v) {
                    $userMapInfo = !empty($userInfo[$v['userName']]) ? $userInfo[$v['userName']] : [];
                    $userCost[]  = [
                        'userName'     => $v['userName'],
                        'cost'         => $v['cost'],
                        'dateTime'     => $v['dateTime'],
                        'platform'     => !empty($userMapInfo['platform']) ? $userMapInfo['platform'] : '',
                        'category'     => !empty($userMapInfo['category']) ? $userMapInfo['category'] : '',
                        'media'        => !empty($userMapInfo['media']) ? $userMapInfo['media'] : '',
                        'adtype'       => !empty($userMapInfo['adtype']) ? $userMapInfo['adtype'] : '',
                        'pushtype'     => !empty($userMapInfo['pushtype']) ? $userMapInfo['pushtype'] : '',
                        'cateplatform' => !empty($userMapInfo['cateplatform']) ? $userMapInfo['cateplatform'] : '',
                    ];
                }
            }
        }
        return ['error' => 0, 'result' => $userCost];
    }

    /**
     * 获取根据账户获取的消费信息
     * @author lixiumeng
     * @datetime 2018-01-05T17:44:19+0800
     * @return   [type]                   [description]
     */
    public function getAccountCostByAcccount($date = '', $padding = false)
    {
        $this->adapter = $this->di->get('cas')->getDbServer('dbsem');
        $table         = "sem_account_report_all";
        $sql           = "select userName,cost,dateTime from {$table} where `unitOfTime` = 5 and `device` = 0 and `dateTime` = '" . $date . "'";
        $rs            = $this->adapter->fetchAll($sql);
        $userInfo      = $this->getUserInfo(); // 共81条
        $rt            = []; // 返回的数据信息
        $costs         = [];
        if ($padding) {
            // 无消费信息的账户补0
            foreach ($rs as $v) {
                $costs[$v['userName']] = $v;
            }
            foreach ($userInfo as $x => $y) {
                $userInfo[$x]['userName'] = $x;
                $userInfo[$x]['cost']     = !empty($costs[$x]) ? $costs[$x]['cost'] : 0;
                $userInfo[$x]['dateTime'] = !empty($costs[$x]) ? $costs[$x]['dateTime'] : $date . " 00:00:00";
                // 获取不到的状态标记为0
                if (empty($costs[$x])) {
                    $userInfo[$x]['state'] = 0;
                } else {
                    $userInfo[$x]['state'] = 1;
                }
            }
            $rt = $userInfo;
        } else {
            // 无消费信息的账户不补0,不出数据
            foreach ($rs as $k => $v) {
                $userMapInfo = !empty($userInfo[$v['userName']]) ? $userInfo[$v['userName']] : [];
                $rt[]        = [
                    'userName'     => $v['userName'],
                    'cost'         => $v['cost'],
                    'dateTime'     => $v['dateTime'],
                    'platform'     => !empty($userMapInfo['platform']) ? $userMapInfo['platform'] : '',
                    'category'     => !empty($userMapInfo['category']) ? $userMapInfo['category'] : '',
                    'media'        => !empty($userMapInfo['media']) ? $userMapInfo['media'] : '',
                    'adtype'       => !empty($userMapInfo['adtype']) ? $userMapInfo['adtype'] : '',
                    'pushtype'     => !empty($userMapInfo['pushtype']) ? $userMapInfo['pushtype'] : '',
                    'cateplatform' => !empty($userMapInfo['cateplatform']) ? $userMapInfo['cateplatform'] : '',
                ];
            }
        }
        return ['error' => 0, 'result' => $rt];
    }

    public function getUserInfo()
    {

        $str = '{
    "baidu-驴妈妈2111452-1": {
        "category": "国内门票",
        "platform": "百度",
        "cateplatform": "百度pc",
        "media": "百度pc",
        "adtype": "搜索",
        "pushtype": "产品页/列表页"
    },
    "baidu-无线bc1驴妈妈8151496": {
        "category": "国内门票",
        "platform": "百度",
        "cateplatform": "百度wap",
        "media": "百度wap",
        "adtype": "搜索",
        "pushtype": "产品页/列表页"
    },
    "驴妈妈001": {
        "category": "国内门票",
        "platform": "360",
        "cateplatform": "360pc",
        "media": "360pc",
        "adtype": "搜索",
        "pushtype": "产品页/列表页"
    },
    "驴妈妈011": {
        "category": "国内门票",
        "platform": "360",
        "cateplatform": "360wap",
        "media": "360wap",
        "adtype": "搜索",
        "pushtype": "产品页/列表页"
    },
    "shlvmama@sogou.com": {
        "category": "国内门票",
        "platform": "搜狗",
        "cateplatform": "搜狗pc",
        "media": "搜狗pc",
        "adtype": "搜索",
        "pushtype": "产品页/列表页"
    },
    "lvmamawap5@sohu.com": {
        "category": "国内门票",
        "platform": "搜狗",
        "cateplatform": "搜狗wap",
        "media": "搜狗wap",
        "adtype": "搜索",
        "pushtype": "产品页/列表页"
    },
    "驴妈妈01": {
        "category": "国内门票",
        "platform": "神马",
        "cateplatform": "神马",
        "media": "神马wap",
        "adtype": "搜索",
        "pushtype": "产品页/列表页"
    },
    "baidu-驴妈妈2111452-9": {
        "category": "境外门票",
        "platform": "百度",
        "cateplatform": "百度pc",
        "media": "百度pc",
        "adtype": "搜索",
        "pushtype": "产品页/列表页"
    },
    "baidu-无线bc11驴妈妈8164781": {
        "category": "境外门票",
        "platform": "百度",
        "cateplatform": "百度wap",
        "media": "百度wap",
        "adtype": "搜索",
        "pushtype": "产品页/列表页"
    },
    "驴妈妈019": {
        "category": "境外门票",
        "platform": "360",
        "cateplatform": "360pc",
        "media": "360pc",
        "adtype": "搜索",
        "pushtype": "产品页/列表页"
    },
    "驴妈妈013": {
        "category": "境外门票",
        "platform": "360",
        "cateplatform": "360wap",
        "media": "360wap",
        "adtype": "搜索",
        "pushtype": "产品页/列表页"
    },
    "lvmama09@sohu.com": {
        "category": "境外门票",
        "platform": "搜狗",
        "cateplatform": "搜狗pc",
        "media": "搜狗pc",
        "adtype": "搜索",
        "pushtype": "产品页/列表页"
    },
    "lvmama15@sohu.com": {
        "category": "境外门票",
        "platform": "搜狗",
        "cateplatform": "搜狗wap",
        "media": "搜狗wap",
        "adtype": "搜索",
        "pushtype": "产品页/列表页"
    },
    "驴妈妈08": {
        "category": "境外门票",
        "platform": "神马",
        "cateplatform": "神马",
        "media": "神马wap",
        "adtype": "搜索",
        "pushtype": "产品页/列表页"
    },
    "baidu-驴妈妈2111452-7": {
        "category": "单酒",
        "platform": "百度",
        "cateplatform": "百度pc",
        "media": "百度pc",
        "adtype": "搜索",
        "pushtype": "产品页/列表页"
    },
    "baidu-无线bc10驴妈妈8151496": {
        "category": "单酒",
        "platform": "百度",
        "cateplatform": "百度wap",
        "media": "百度wap",
        "adtype": "搜索",
        "pushtype": "产品页/列表页"
    },
    "驴妈妈018": {
        "category": "单酒",
        "platform": "360",
        "cateplatform": "360pc",
        "media": "360pc",
        "adtype": "搜索",
        "pushtype": "产品页/列表页"
    },
    "驴妈妈020": {
        "category": "单酒",
        "platform": "360",
        "cateplatform": "360wap",
        "media": "360wap",
        "adtype": "搜索",
        "pushtype": "产品页/列表页"
    },
    "lvmama11@sohu.com": {
        "category": "单酒",
        "platform": "搜狗",
        "cateplatform": "搜狗pc",
        "media": "搜狗pc",
        "adtype": "搜索",
        "pushtype": "产品页/列表页"
    },
    "lvmama16@sohu.com": {
        "category": "单酒",
        "platform": "搜狗",
        "cateplatform": "搜狗wap",
        "media": "搜狗wap",
        "adtype": "搜索",
        "pushtype": "产品页/列表页"
    },
    "驴妈妈10": {
        "category": "单酒",
        "platform": "神马",
        "cateplatform": "神马",
        "media": "神马wap",
        "adtype": "搜索",
        "pushtype": "产品页/列表页"
    },
    "baidu-驴妈妈2111452-6": {
        "category": "景酒",
        "platform": "百度",
        "cateplatform": "百度pc",
        "media": "百度pc",
        "adtype": "搜索",
        "pushtype": "产品页/列表页"
    },
    "baidu-无线bc7驴妈妈8151496": {
        "category": "景酒",
        "platform": "百度",
        "cateplatform": "百度wap",
        "media": "百度wap",
        "adtype": "搜索",
        "pushtype": "产品页/列表页"
    },
    "驴妈妈021": {
        "category": "景酒",
        "platform": "360",
        "cateplatform": "360pc",
        "media": "360pc",
        "adtype": "搜索",
        "pushtype": "产品页/列表页"
    },
    "驴妈妈016": {
        "category": "景酒",
        "platform": "360",
        "cateplatform": "360wap",
        "media": "360wap",
        "adtype": "搜索",
        "pushtype": "产品页/列表页"
    },
    "lvmama3@sogou.com": {
        "category": "景酒",
        "platform": "搜狗",
        "cateplatform": "搜狗pc",
        "media": "搜狗pc",
        "adtype": "搜索",
        "pushtype": "产品页/列表页"
    },
    "lvmamawap2@sohu.com": {
        "category": "景酒",
        "platform": "搜狗",
        "cateplatform": "搜狗wap",
        "media": "搜狗wap",
        "adtype": "搜索",
        "pushtype": "产品页/列表页"
    },
    "驴妈妈06": {
        "category": "景酒",
        "platform": "神马",
        "cateplatform": "神马",
        "media": "神马wap",
        "adtype": "搜索",
        "pushtype": "产品页/列表页"
    },
    "baidu-驴妈妈2111452-8": {
        "category": "长线跟团",
        "platform": "百度",
        "cateplatform": "百度pc",
        "media": "百度pc",
        "adtype": "搜索",
        "pushtype": "产品页/列表页"
    },
    "baidu-无线bc8驴妈妈8151496": {
        "category": "长线跟团",
        "platform": "百度",
        "cateplatform": "百度wap",
        "media": "百度wap",
        "adtype": "搜索",
        "pushtype": "产品页/列表页"
    },
    "驴妈妈006": {
        "category": "长线跟团",
        "platform": "360",
        "cateplatform": "360pc",
        "media": "360pc",
        "adtype": "搜索",
        "pushtype": "产品页/列表页"
    },
    "驴妈妈009": {
        "category": "长线跟团",
        "platform": "360",
        "cateplatform": "360wap",
        "media": "360wap",
        "adtype": "搜索",
        "pushtype": "产品页/列表页"
    },
    "lvmama2@sogou.com": {
        "category": "长线跟团",
        "platform": "搜狗",
        "cateplatform": "搜狗pc",
        "media": "搜狗pc",
        "adtype": "搜索",
        "pushtype": "产品页/列表页"
    },
    "lvmamawap3@sohu.com": {
        "category": "长线跟团",
        "platform": "搜狗",
        "cateplatform": "搜狗wap",
        "media": "搜狗wap",
        "adtype": "搜索",
        "pushtype": "产品页/列表页"
    },
    "驴妈妈07": {
        "category": "长线跟团",
        "platform": "神马",
        "cateplatform": "神马",
        "media": "神马wap",
        "adtype": "搜索",
        "pushtype": "产品页/列表页"
    },
    "baidu-驴妈妈2111452-2": {
        "category": "周边跟团",
        "platform": "百度",
        "cateplatform": "百度pc",
        "media": "百度pc",
        "adtype": "搜索",
        "pushtype": "产品页/列表页"
    },
    "baidu-无线bc2驴妈妈8151496": {
        "category": "周边跟团",
        "platform": "百度",
        "cateplatform": "百度wap",
        "media": "百度wap",
        "adtype": "搜索",
        "pushtype": "产品页/列表页"
    },
    "驴妈妈005": {
        "category": "周边跟团",
        "platform": "360",
        "cateplatform": "360pc",
        "media": "360pc",
        "adtype": "搜索",
        "pushtype": "产品页/列表页"
    },
    "驴妈妈015": {
        "category": "周边跟团",
        "platform": "360",
        "cateplatform": "360wap",
        "media": "360wap",
        "adtype": "搜索",
        "pushtype": "产品页/列表页"
    },
    "lvmama5@sogou.com": {
        "category": "周边跟团",
        "platform": "搜狗",
        "cateplatform": "搜狗pc",
        "media": "搜狗pc",
        "adtype": "搜索",
        "pushtype": "产品页/列表页"
    },
    "lvmamawap@sogou.com": {
        "category": "周边跟团",
        "platform": "搜狗",
        "cateplatform": "搜狗wap",
        "media": "搜狗wap",
        "adtype": "搜索",
        "pushtype": "产品页/列表页"
    },
    "驴妈妈02": {
        "category": "周边跟团",
        "platform": "神马",
        "cateplatform": "神马",
        "media": "神马wap",
        "adtype": "搜索",
        "pushtype": "产品页/列表页"
    },
    "baidu-驴妈妈2111452-3": {
        "category": "机酒自由行",
        "platform": "百度",
        "cateplatform": "百度pc",
        "media": "百度pc",
        "adtype": "搜索",
        "pushtype": "产品页/列表页"
    },
    "baidu-无线bc3驴妈妈8151496": {
        "category": "机酒自由行",
        "platform": "百度",
        "cateplatform": "百度wap",
        "media": "百度wap",
        "adtype": "搜索",
        "pushtype": "产品页/列表页"
    },
    "lvmama2.com": {
        "category": "机酒自由行",
        "platform": "360",
        "cateplatform": "360pc",
        "media": "360pc",
        "adtype": "搜索",
        "pushtype": "产品页/列表页"
    },
    "lvmama08@sohu.com": {
        "category": "机酒自由行",
        "platform": "搜狗",
        "cateplatform": "搜狗pc",
        "media": "搜狗pc",
        "adtype": "搜索",
        "pushtype": "产品页/列表页"
    },
    "lvmama12@sohu.com": {
        "category": "机酒自由行",
        "platform": "搜狗",
        "cateplatform": "搜狗wap",
        "media": "搜狗wap",
        "adtype": "搜索",
        "pushtype": "产品页/列表页"
    },
    "驴妈妈03": {
        "category": "机酒自由行",
        "platform": "神马",
        "cateplatform": "神马",
        "media": "神马wap",
        "adtype": "搜索",
        "pushtype": "产品页/列表页"
    },
    "baidu-驴妈妈2111452-12": {
        "category": "出境跟团",
        "platform": "百度",
        "cateplatform": "百度pc",
        "media": "百度pc",
        "adtype": "搜索",
        "pushtype": "产品页/列表页"
    },
    "baidu-无线bc9驴妈妈8151496": {
        "category": "出境跟团",
        "platform": "百度",
        "cateplatform": "百度wap",
        "media": "百度wap",
        "adtype": "搜索",
        "pushtype": "产品页/列表页"
    },
    "驴妈妈007": {
        "category": "出境跟团",
        "platform": "360",
        "cateplatform": "360pc",
        "media": "360pc",
        "adtype": "搜索",
        "pushtype": "产品页/列表页"
    },
    "驴妈妈008": {
        "category": "出境跟团",
        "platform": "360",
        "cateplatform": "360wap",
        "media": "360wap",
        "adtype": "搜索",
        "pushtype": "产品页/列表页"
    },
    "lvmama4@sogou.com": {
        "category": "出境跟团",
        "platform": "搜狗",
        "cateplatform": "搜狗pc",
        "media": "搜狗pc",
        "adtype": "搜索",
        "pushtype": "产品页/列表页"
    },
    "lvmamawap4@sohu.com": {
        "category": "出境跟团",
        "platform": "搜狗",
        "cateplatform": "搜狗wap",
        "media": "搜狗wap",
        "adtype": "搜索",
        "pushtype": "产品页/列表页"
    },
    "驴妈妈11": {
        "category": "出境跟团",
        "platform": "神马",
        "cateplatform": "神马",
        "media": "神马wap",
        "adtype": "搜索",
        "pushtype": "产品页/列表页"
    },
    "baidu-驴妈妈2111452-4": {
        "category": "出境自由行",
        "platform": "百度",
        "cateplatform": "百度pc",
        "media": "百度pc",
        "adtype": "搜索",
        "pushtype": "产品页/列表页"
    },
    "baidu-无线bc4驴妈妈8151496": {
        "category": "出境自由行",
        "platform": "百度",
        "cateplatform": "百度wap",
        "media": "百度wap",
        "adtype": "搜索",
        "pushtype": "产品页/列表页"
    },
    "驴妈妈002": {
        "category": "出境自由行",
        "platform": "360",
        "cateplatform": "360pc",
        "media": "360pc",
        "adtype": "搜索",
        "pushtype": "产品页/列表页"
    },
    "驴妈妈012": {
        "category": "出境自由行",
        "platform": "360",
        "cateplatform": "360wap",
        "media": "360wap",
        "adtype": "搜索",
        "pushtype": "产品页/列表页"
    },
    "lvmama06@sohu.com": {
        "category": "出境自由行",
        "platform": "搜狗",
        "cateplatform": "搜狗pc",
        "media": "搜狗pc",
        "adtype": "搜索",
        "pushtype": "产品页/列表页"
    },
    "lvmama13@sohu.com": {
        "category": "出境自由行",
        "platform": "搜狗",
        "cateplatform": "搜狗wap",
        "media": "搜狗wap",
        "adtype": "搜索",
        "pushtype": "产品页/列表页"
    },
    "驴妈妈04": {
        "category": "出境自由行",
        "platform": "神马",
        "cateplatform": "神马",
        "media": "神马wap",
        "adtype": "搜索",
        "pushtype": "产品页/列表页"
    },
    "baidu-驴妈妈2111452-5": {
        "category": "邮轮",
        "platform": "百度",
        "cateplatform": "百度pc",
        "media": "百度pc",
        "adtype": "搜索",
        "pushtype": "产品页/列表页"
    },
    "baidu-无线bc5驴妈妈8151496": {
        "category": "邮轮",
        "platform": "百度",
        "cateplatform": "百度wap",
        "media": "百度wap",
        "adtype": "搜索",
        "pushtype": "产品页/列表页"
    },
    "驴妈妈004": {
        "category": "邮轮",
        "platform": "360",
        "cateplatform": "360pc",
        "media": "360pc",
        "adtype": "搜索",
        "pushtype": "产品页/列表页"
    },
    "驴妈妈014": {
        "category": "邮轮",
        "platform": "360",
        "cateplatform": "360wap",
        "media": "360wap",
        "adtype": "搜索",
        "pushtype": "产品页/列表页"
    },
    "lvmama07@sohu.com": {
        "category": "邮轮",
        "platform": "搜狗",
        "cateplatform": "搜狗pc",
        "media": "搜狗pc",
        "adtype": "搜索",
        "pushtype": "产品页/列表页"
    },
    "lvmama14@sohu.com": {
        "category": "邮轮",
        "platform": "搜狗",
        "cateplatform": "搜狗wap",
        "media": "搜狗wap",
        "adtype": "搜索",
        "pushtype": "产品页/列表页"
    },
    "驴妈妈05": {
        "category": "邮轮",
        "platform": "神马",
        "cateplatform": "神马",
        "media": "神马wap",
        "adtype": "搜索",
        "pushtype": "产品页/列表页"
    },
    "baidu-驴妈妈8173124-15": {
        "category": "国际机票",
        "platform": "百度",
        "cateplatform": "百度pc",
        "media": "百度pc",
        "adtype": "搜索",
        "pushtype": "产品页/列表页"
    },
    "驴妈妈022": {
        "category": "国际机票",
        "platform": "360",
        "cateplatform": "360pc",
        "media": "360pc",
        "adtype": "搜索",
        "pushtype": "产品页/列表页"
    },
    "lvmama19@sohu.com": {
        "category": "国际机票",
        "platform": "搜狗",
        "cateplatform": "搜狗pc",
        "media": "搜狗pc",
        "adtype": "搜索",
        "pushtype": "产品页/列表页"
    },
    "驴妈妈机票": {
        "category": "国际机票",
        "platform": "神马",
        "cateplatform": "神马",
        "media": "神马wap",
        "adtype": "搜索",
        "pushtype": "产品页/列表页"
    },
    "baidu-驴妈妈2111452-10": {
        "category": "品牌",
        "platform": "百度",
        "cateplatform": "百度pc",
        "media": "品牌",
        "adtype": "搜索",
        "pushtype": "专题/首页/频道页"
    },
    "baidu-无线bc6驴妈妈8151496": {
        "category": "品牌",
        "platform": "百度",
        "cateplatform": "百度wap",
        "media": "品牌",
        "adtype": "搜索",
        "pushtype": "专题/首页/频道页"
    },
    "lvmama.com": {
        "category": "品牌",
        "platform": "360",
        "cateplatform": "360pc",
        "media": "品牌",
        "adtype": "搜索",
        "pushtype": "专题/首页/频道页"
    },
    "驴妈妈010": {
        "category": "品牌",
        "platform": "360",
        "cateplatform": "360wap",
        "media": "品牌",
        "adtype": "搜索",
        "pushtype": "专题/首页/频道页"
    },
    "lvmama@sohu.com": {
        "category": "品牌",
        "platform": "搜狗",
        "cateplatform": "搜狗pc",
        "media": "品牌",
        "adtype": "搜索",
        "pushtype": "专题/首页/频道页"
    },
    "lvmama10@sohu.com": {
        "category": "品牌",
        "platform": "搜狗",
        "cateplatform": "搜狗wap",
        "media": "品牌",
        "adtype": "搜索",
        "pushtype": "专题/首页/频道页"
    },
    "驴妈妈09": {
        "category": "品牌",
        "platform": "神马",
        "cateplatform": "神马",
        "media": "品牌",
        "adtype": "搜索",
        "pushtype": "专题/首页/频道页"
    }
}';
        return json_decode($str, true);

    }
}
