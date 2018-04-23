<?php

use Lvmama\Cas\Service\BeanstalkDataService;
use Lvmama\Common\Utils\UCommon;
use Lvmama\Cas\Service\RedisDataService;

/**
 * 新游记 控制器
 *
 * @author zhta
 *
 */
class NewguideController extends ControllerBase
{

    private $new_guide_svc;

    private $trip_data_svc;

    public function initialize()
    {
        $this->new_guide_svc = $this->di->get('cas')->get('new_guide_data_service');

        $this->trip_data_svc = $this->di->get('cas')->get('trip-data-service');

        $this->es = $this->di->get('cas')->get('es-data-service');

        $this->redis_svc = $this->di->get('cas')->getRedis();

        return parent::initialize();
    }

    /**
     * 游记主表数据增改
     *
     * @author zhta
     *
     * @example curl -i -X POST -d "uid=1&title=test" http://ca.lvmama.com/newtrip/info-update/json/2/3/4
     */
    public function createInfoAction()
    {
        $now = time();
        $data = array();
        $ext_data = array();
        $data['update_time'] = $now;
        $ext_data['update_time'] = $now;
        $trip_flag = 1;
        if ($this->uid) {
            $data['uid'] = $this->uid;
            $trip_flag = 2;
        }
        if ($this->username) {
            $data['username'] = $this->username;
            $trip_flag = 2;
        }
        if ($this->title) {
            $data['title'] = $this->title;
            $trip_flag = 2;
        }
        if ($this->seo_title) {
            $data['seo_title'] = $this->seo_title;
            $trip_flag = 2;
        }
        if ($this->summary) {
            $data['summary'] = $this->summary;
            $trip_flag = 2;
        }
        if ($this->thumb) {
            $data['thumb'] = $this->thumb;
            $trip_flag = 2;
        }
        if ($this->start_time) {
            $data['start_time'] = $this->start_time;
            $trip_flag = 2;
        }
        if ($this->publish_time) {
            $data['publish_time'] = $this->publish_time;
            $trip_flag = 2;
        }
        if ($this->order_num) {
            $data['order_num'] = $this->order_num;
            $trip_flag = 2;
        }
        if ($this->losc_inner) {
            $data['losc_inner'] = $this->losc_inner;
            $trip_flag = 2;
        }
        if ($this->losc_outer) {
            $data['losc_outer'] = $this->losc_outer;
            $trip_flag = 2;
        }
        if ($this->status || $this->status === "0") {
            $data['status'] = $this->status;
            $trip_flag = 2;
        }
        if ($this->recommend_status) {
            $data['recommend_status'] = $this->recommend_status;
            $trip_flag = 2;
        }
        if ($this->trip_id) {
            if ($trip_flag == 2) {
                $where = "id=" . $this->trip_id;
                $this->new_guide_svc->update(array("table" => "article", "where" => $where, "data" => $data));
            }
        } else {
            $data["create_time"] = $now;
            $trip_data['table'] = "article";
            $trip_data['data'] = $data;
            $res = $this->new_guide_svc->insert($trip_data);
            if ($res["error"] == 0) {
                $this->trip_id = $res["result"];
            }
        }
        $trip_ext = $this->new_guide_svc->select(array(
            "table" => "article_ext",
            'select' => 'id',
            'where' => array('guide_id' => $this->trip_id)
        ));
        if ($this->order_id) {
            $ext_data['order_id'] = $this->order_id;
            $trip_flag = 3;
        }
        if ($this->product_id) {
            $ext_data['product_id'] = $this->product_id;
            $trip_flag = 3;
        }
        if ($this->source || $this->source === "0") {
            $ext_data['source'] = $this->source;
            $trip_flag = 3;
        }
        if ($this->platform || $this->platform === "0") {
            $ext_data['platform'] = $this->platform;
            $trip_flag = 3;
        }
        if ($this->device_no) {
            $ext_data['device_no'] = $this->device_no;
            $trip_flag = 3;
        }
        if ($this->port) {
            $ext_data['port'] = $this->port;
            $trip_flag = 3;
        }
        if ($this->commit_time) {
            $ext_data['commit_time'] = $this->commit_time;
            $trip_flag = 3;
        }
        if ($this->main_status || $this->main_status === "0") {
            $ext_data['main_status'] = $this->main_status;
            $trip_flag = 3;
        }
        if ($this->del_status || $this->del_status === "0") {
            $ext_data['del_status'] = $this->del_status;
            $trip_flag = 3;
        }
        if ($this->fanli_status || $this->fanli_status === "0") {
            $ext_data['fanli_status'] = $this->fanli_status;
            $trip_flag = 3;
        }
        if ($trip_ext["list"]) {
            if ($trip_flag == 3) {
                $ext_where = "id=" . $trip_ext["list"][0]["id"];
                $this->new_guide_svc->update(array("table" => "article_ext", "where" => $ext_where, "data" => $ext_data));
            }
        } else {
            $ext_data["create_time"] = $now;
            $ext_data["guide_id"] = $this->trip_id;
            $tripext_data['table'] = "article_ext";
            $tripext_data['data'] = $ext_data;
            $this->new_guide_svc->insert($tripext_data);
        }
        $content = array(
            'tripid' => $this->trip_id,
        );
        $this->_successResponse($content);
    }

    /**
     * 游记章节增改
     *
     * @author zhta
     *
     */
    public function createContentAction()
    {
        $now = time();
        $data = array();
        $data['update_time'] = $now;
        $dest_data['update_time'] = $now;
        $content_flag = 1;
        if ($this->title) {
            $data['title'] = $this->title;
            $content_flag = 2;
        }
        if ($this->trip_id) {
            $data['guide_id'] = $this->trip_id;
            $content_flag = 2;
        }
        if ($this->content) {
            $data['content'] = $this->content;
            $content_flag = 2;
        }
        if ($this->order_num) {
            $data['order_num'] = $this->order_num;
            $content_flag = 2;
        }
        if ($this->sync_status || $this->sync_status === "0") {
            $data['sync_status'] = $this->sync_status;
            $content_flag = 2;
        }
        if ($this->content_id) {
            if ($content_flag == 2) {
                $where = "id=" . $this->content_id;
                $this->new_guide_svc->update(array("table" => "article_content", "where" => $where, "data" => $data));
            }
        } else {
            $data["create_time"] = $now;
            $content_data['table'] = "article_content";
            $content_data['data'] = $data;
            $res = $this->new_guide_svc->insert($content_data);
            if ($res["error"] == 0) {
                $this->content_id = $res["result"];
            }
        }
        if ($this->dest_id || $this->dest_id === "0") {
            $dest_data['dest_id'] = $this->dest_id;
            $content_flag = 3;
        }
        if ($this->dest_type || $this->dest_type === "0") {
            if ($this->dest_type === "0") {
                $dest_data['dest_type'] = "";
            } else {
                $dest_data['dest_type'] = $this->dest_type;
            }
            $content_flag = 3;
        }
        if ($this->is_main || $this->is_main === "0") {
            $dest_data['is_main'] = $this->is_main;
            $content_flag = 3;
        }
        $dest_data['article_content_id'] = $this->content_id;
        if ($this->travel_content_id) {
            if ($content_flag == 3) {
                $content_where = "id=" . $this->travel_content_id;
                $this->new_guide_svc->update(array("table" => "article_content_dest_rel", "where" => $content_where, "data" => $dest_data));
            }
        } else {
            $dest_data["create_time"] = $now;
            $dest_data["guide_id"] = $this->trip_id;
            $content_dest_data['table'] = "article_content_dest_rel";
            $content_dest_data['data'] = $dest_data;
            $this->new_guide_svc->insert($content_dest_data);
        }
        $content = array(
            'contentid' => $this->content_id,
        );
        $this->_successResponse($content);
    }

    /**
     * 游记图片增改
     *
     * @author zhta
     *
     */
    public function createImageAction()
    {
        $now = time();
        $data = array();
        $data['update_time'] = $now;
        $image_rel_data["update_time"] = $now;
        $img_flag = 1;
        if ($this->dest_id || $this->dest_id === "0") {
            $data['dest_id'] = $this->dest_id;
            $img_flag = 2;
        }
        if ($this->width) {
            $data['width'] = $this->width;
            $img_flag = 2;
        }
        $data['url'] = $this->imgurl;
        $img_data = $this->new_guide_svc->select(array(
            "table" => "image",
            'select' => 'id',
            'where' => array('url' => $data['url'])
        ));
        if ($img_data["list"]) {
            $img_id = $img_data["list"][0]["id"];
            if ($img_flag == 2) {
                $where = "id=" . $img_id;
                $this->new_guide_svc->update(array("table" => "image", "where" => $where, "data" => $data));
            }
        } else {
            $data["create_time"] = $now;
            $image_data['table'] = "image";
            $image_data['data'] = $data;
            $res = $this->new_guide_svc->insert($image_data);
            if ($res["error"] == 0) {
                $img_id = $res["result"];
            }
        }
        $trip_image = $this->new_guide_svc->select(array(
            "table" => "article_image_rel",
            'select' => 'id',
            'where' => array('image_id' => $img_id)
        ));
        if ($this->trip_id) {
            $image_rel_data['guide_id'] = $this->trip_id;
            $img_flag = 3;
        }
        if ($trip_image["list"]) {
            if ($img_flag == 3) {
                $image_where = "id=" . $trip_image["list"][0]["id"];
                $this->new_guide_svc->update(array("table" => "article_image_rel", "where" => $image_where, "data" => $image_rel_data));
            }
        } else {
            $image_rel_data["create_time"] = $now;
            $image_rel_data["image_id"] = $img_id;
            $trip_image_data['table'] = "article_image_rel";
            $trip_image_data['data'] = $image_rel_data;
            $this->new_guide_svc->insert($trip_image_data);
        }
        $content = array(
            'imgid' => $img_id,
        );
        $this->_successResponse($content);
    }

    /**
     * 游记查询
     *
     * @author zhta
     *
     */
    public function selectTripAction()
    {
        if ($this->table) {
            $data['table'] = $this->table;
        }
        if ($this->select) {
            $data['select'] = $this->select;
        }
        if ($this->where) {
            $data['where'] = unserialize($this->where);
        }
        if ($this->order) {
            $data['order'] = $this->order;
        }
        if ($this->group) {
            $data['group'] = $this->group;
        }
        if ($this->limit) {
            $data['limit'] = $this->limit;
        }
        if ($this->page) {
            $data['page'] = unserialize($this->page);
        }
        $res = $this->new_guide_svc->select($data);
        $this->_successResponse($res);
    }

    /**
     * 游记删除(物理删除)
     *
     * @author zhta
     *
     */
    public function deleteTripAction()
    {
        if ($this->table) {
            $data['table'] = $this->table;
        }
        if ($this->where) {
            $data['where'] = unserialize($this->where);
        }
        $res = $this->new_guide_svc->delete($data);
        $this->_successResponse($res);
    }

    /**
     * 游记删除(逻辑删除：修改状态)
     */
    public function deleteTravelAction()
    {
        if (!$this->guide_id)
            $this->_errorResponse(100010, '缺少参数');

        $where_arr = array('id' => $this->guide_id);
        $where_str = "`id` = {$this->guide_id}";
        if ($this->uid != 'admin') {
            $where_arr['uid'] = $this->uid;
            $where_str .= " AND `uid` = {$this->uid}";
        }

        $select_res = $this->new_guide_svc->select(array(
            'table' => 'article',
            'select' => 'id',
            'where' => $where_arr,
            'limit' => '1',
        ));

        if ($select_res['list']) {
            $this->new_guide_svc->update(array(
                'table' => 'article',
                'where' => $where_str,
                'data' => array('status' => '0'),
            ));
            $this->new_guide_svc->update(array(
                'table' => 'article_ext',
                'where' => "`guide_id` = '{$this->guide_id}'",
                'data' => array('del_status' => '2'),
            ));
        }
        //TODO
        $this->_successResponse(array('删除成功'));
    }

    /**
     * 原生SQL
     *
     * @author zhta
     *
     */
    public function queryTripAction()
    {
        if ($this->sql) {
            $res = $this->querySql($this->sql);
        }
        $this->_successResponse($res);
    }

    /**
     * 执行SQL语句
     * @param $sql
     * @return mixed
     */
    private function querySql($sql)
    {
        return $this->new_guide_svc->querySql($sql);
    }

    /**
     * gu_article_content_dest_rel表新增数据
     */
    public function insertContentDestRelAction()
    {
        if ($this->article_content_id)
            $data['article_content_id'] = $this->article_content_id;

        if ($this->dest_id)
            $data['dest_id'] = $this->dest_id;

        if ($this->dest_type)
            $data['dest_type'] = $this->dest_type;

        if ($this->guide_id)
            $data['guide_id'] = $this->guide_id;

        if ($this->is_main)
            $data['is_main'] = $this->is_main;

        $data['create_time'] = $data['update_time'] = time();

        $params = array(
            'table' => 'article_content_dest_rel',
            'data' => $data,
        );
        $this->new_guide_svc->insert($params);
    }

    /**
     * gu_article_content_dest_rel表更新数据
     */
    public function updateContentDestRelAction()
    {
        if ($this->article_content_id)
            $data['article_content_id'] = $this->article_content_id;

        if ($this->dest_id)
            $data['dest_id'] = $this->dest_id;

        if ($this->dest_type)
            $data['dest_type'] = $this->dest_type;

        if ($this->guide_id)
            $data['guide_id'] = $this->guide_id;

        if ($this->is_main || $this->is_main === '0')
            $data['is_main'] = $this->is_main;

        $where = '';
        if ($this->where)
            $where = $this->where;

        $data['update_time'] = time();

        $params = array(
            'table' => 'article_content_dest_rel',
            'where' => $where,
            'data' => $data,
        );
        $this->_successResponse($this->new_guide_svc->update($params));
    }

    /**
     * gu_image表更新数据
     */
    public function updateImageAction()
    {
        if ($this->dest_id)
            $data['dest_id'] = $this->dest_id;

        if ($this->imgurl)
            $data['url'] = $this->imgurl;

        if ($this->dest_type)
            $data['dest_type'] = $this->dest_type;

        if ($this->pic_url)
            $data['pic_url'] = $this->pic_url;

        if ($this->width)
            $data['width'] = $this->width;

        $where = '';
        if ($this->where)
            $where = $this->where;

        $data['update_time'] = time();

        $params = array(
            'table' => 'image',
            'where' => $where,
            'data' => $data,
        );
        $this->_successResponse($this->new_guide_svc->update($params));
    }

    /**
     * 获取REDIS数据（需继续更新）
     */
    public function getRedisDataAction()
    {
        if (!$this->redis_key)
            $this->_errorResponse(10001, '缺少键');

        $redis_data = '';
        switch (strtoupper($this->key_type)) {
            case 'HASH' :
                $redis_data = $this->redis_svc->dataHgetall($this->redis_key);
                break;
            case 'SORTEDSET':
                $redis_data = $this->redis_svc->getZRange($this->redis_key);
                break;
            case 'STRING':
                $redis_data = $this->redis_svc->dataGet($this->redis_key);
                break;
            default:
                $this->_errorResponse(10001, '未知的键类型');
        }
        $this->_successResponse($redis_data);
    }

    /**
     * 删除REDIS数据（需继续更新）
     */
    public function delRedisDataAction()
    {
        if (!$this->redis_key)
            $this->_errorResponse(10001, '缺少键');

        $redis_data = '';
        switch (strtoupper($this->key_type)) {
            case 'HASH' :
                break;
            case 'SORTEDSET':
                break;
            case 'ALL':
                $redis_data = $this->redis_svc->dataDelete($this->redis_key);
                break;
            default:
                $this->_errorResponse(10001, '未知的键类型');
        }
        $this->_successResponse($redis_data);
    }

    /**
     * 保存配置信息
     */
    public function saveConfigureDataAction()
    {
        $data = array();
        if ($this->name)
            $data['name'] = $this->name;

        if ($this->key)
            $data['key'] = $this->key;

        if ($this->key_value)
            $data['key_value'] = $this->key_value;

        if ($this->channel)
            $data['channel'] = $this->channel;

        $res = array();
        if ($this->id)
            $res = $this->new_guide_svc->update(array(
                'table' => 'configure',
                'where' => "id = '{$this->id}'",
                'data' => $data,
            ));
        else
            $res = $this->new_guide_svc->insert(array(
                'table' => 'configure',
                'data' => $data,
            ));

        $this->_successResponse($res);
    }

    /**
     * 删除配置
     */
    public function delConfigureDataAction()
    {
        $res = array();
        if ($this->id)
            $res = $this->new_guide_svc->delete(array(
                'table' => 'configure',
                'where' => array('id' => $this->id),
            ));

        $this->_successResponse($res);
    }

    /**
     * 根据目的地名称获取目的地信息
     */
    public function getDestInfoByNameAction()
    {
        if (!$this->dest_name)
            $this->_errorResponse(100010, '缺少参数');
        $params = array(
            'table' => 'ly_destination',
            'select' => 'dest_id,dest_name,dest_type,dest_type_name,parent_name',
            'where' => array('dest_name' => $this->dest_name, 'cancel_flag' => 'Y', 'showed' => 'Y', 'dest_type' => array('IN', "('TOWN','COUNTRY','CITY,SCENIC','SPAN_PROVINCE','PROVINCE')")),
        );
        $res = $this->trip_data_svc->select($params);
        $this->_successResponse($res);
    }

    /**
     * 根据目的地ID查询目的地类型
     */
    public function getDestTypeByIdAction()
    {
        if (!$this->dest_id)
            $this->_errorResponse(100010, '缺少参数');
        $params = array(
            'table' => 'ly_destination',
            'select' => 'dest_type',
            'where' => array('dest_id' => $this->dest_id, 'cancel_flag' => 'Y', 'showed' => 'Y'),
        );
        $res = $this->trip_data_svc->select($params);
        $this->_successResponse($res);
    }

    /**
     * 获取微攻略中每个章节关联的城市
     * @return array
     */
    public function getRelCityByGuideIdAction()
    {
        if (!$this->guide_id || !$this->chapter_id_str)
            $this->_errorResponse(100010, '缺少参数');
        $guide_id = $this->guide_id;
        $chapter_id_str = $this->chapter_id_str;
        $chapter_id_arr = explode(',', $chapter_id_str);
        $result = array();
        foreach ($chapter_id_arr as $chapter_id) {
            //查看redis
            $redis_key = "guide:" . $guide_id . ":content:" . $chapter_id . ":recommend-dest";
            $redis_data = $this->redis_svc->getZRange($redis_key);

            if ($redis_data) {
                $dest_name_list = implode(',', $redis_data);
                $result[$chapter_id]['redis'] = 1;

                $params = array(
                    'dest_names' => $dest_name_list,
                    'fields' => 'dest_id,dest_name,dest_type,parent_name',
                );
                $dest_is = $this->getDestIdsByNames($params);
                if (is_array($dest_is)) {
                    $result[$chapter_id]['dest'] = array_values($dest_is);
                }
            } else {
                $result[$chapter_id]['redis'] = 0;

                $params = array(
                    'table' => 'article_content_dest_rel',
                    'select' => 'article_content_id,dest_id,dest_type,guide_id,is_main',
                    'where' => array("article_content_id" => $chapter_id),
                );

                $cont_dest_list = $this->new_guide_svc->select($params);

                $array_dest = array();
                if ($cont_dest_list['list']) {
                    $array_dest = $cont_dest_list['list'];
                }

                foreach ($array_dest as $key => $v) {
                    $params = array(
                        'table' => 'ly_destination',
                        'select' => 'dest_id,dest_name,dest_type_name,parent_name',
                        'where' => array('dest_id' => $v['dest_id'], 'cancel_flag' => 'Y', 'showed' => 'Y'),
                    );
                    $dest_is = $this->trip_data_svc->select($params);
                    if ($dest_is['list']) {
                        $result[$chapter_id]['dest'][] = array_merge($array_dest[$key], $dest_is['list']['0']);
                    } else {
                        $params = array(
                            'table' => 'ly_district_sign',
                            'select' => 'dest_id,dest_name,dest_type_name,parent_name',
                            'where' => array('dest_id' => $v['dest_id'], 'cancel_flag' => 'Y', 'showed' => 'Y'),
                        );
                        $dest_new_is = $this->trip_data_svc->select($params);
                        if ($dest_new_is['list'])
                            $result[$chapter_id]['dest'][] = array_merge($array_dest[$key], $dest_new_is['list']['0']);
                    }
                }
            }
        }
        $this->_successResponse($result);
    }

    /**
     * 根据指定的点到点名称精确查询相应的目的地ID
     * @param array $params
     * @return mixed
     */
    private function getDestIdsByNames($params = array())
    {
        $dest_names = isset($params['dest_names']) ? addslashes(urldecode($params['dest_names'])) : '';
        $fields = isset($params['fields']) && $params['fields'] ? $params['fields'] : '';
        if (!$dest_names) {
            $this->_errorResponse(10002, '请传入需要搜查询的名称');
        }
        $names = explode(',', $dest_names);
        if (count($names) > 1000) {
            $this->_errorResponse(10003, '传入的目的地名称一次不能超过1000个');
        }
        return $this->es->getIdsByNames($names, explode(',', $fields));
    }

    /**
     * 返回微攻略列表
     */
    public function getGuideListAction()
    {
        $page = $this->page;
        $page_size = $this->page_size;
        $username = $this->username;
        $guide_id = $this->guide_id;
        $guide_dest = $this->guide_dest;
        $chapter_dest = $this->chapter_dest;
        $publish_time = $this->publish_time;
        $status = $this->status;

        $offset = $offset = (max(1, $page) - 1) * $page_size;
        $start_time = $end_time = 0;

        $column = 'DISTINCT(art.`id`),art.`username`,art.`title`,art.`thumb`,art.`publish_time`,art.`create_time`';
        if ($publish_time) {
            $time_arr = explode(' - ', $publish_time);
            $start_time = strtotime($time_arr['0'] . '00:00:00');
            $end_time = strtotime('+1 day',strtotime($time_arr['1'] . '00:00:00'));
        }
        $left = $where = array();
        $left[] = '`gu_article_ext` art_ext ON art.id = art_ext.guide_id';
        $where[] = "art_ext.del_status = '0'";
        if ($username)
            $where[] = "art.username = '{$username}'";

        if ($guide_id) {
            $where[] = "art.id = '{$guide_id}'";
        }

        if ($guide_dest) {
            $params = array(
                'dest_names' => $guide_dest,
                'fields' => 'dest_id,dest_name',
            );
            $dest_info = $this->getDestIdsByNames($params);
            $where[] = "art_dest.dest_id = {$dest_info[$guide_dest]['dest_id']}";
            $left[] = '`gu_article_dest_rel` art_dest ON art.id = art_dest.guide_id';
        }

        if ($chapter_dest) {
            $params = array(
                'dest_names' => $chapter_dest,
                'fields' => 'dest_id,dest_name',
            );
            $dest_info = $this->getDestIdsByNames($params);
            $where[] = "art_con_dest.dest_id = '{$dest_info[$chapter_dest]['dest_id']}'";
            $left[] = '`gu_article_content_dest_rel` art_con_dest ON art.id = art_con_dest.guide_id';
        }

        if ($publish_time) {
            $where[] = "art.publish_time >= {$start_time} AND art.publish_time < {$end_time}";
        }

        $where[] = "art.status = {$status}";
        if ($left)
            $left_str = ' LEFT JOIN' . implode(' LEFT JOIN ', $left);
        $where_str = implode(' AND ', $where);
        $guide_list = array('list' => array(), 'pages' => array());

        $count_sql = "SELECT COUNT(*) AS itemCount FROM `gu_article` art {$left_str} WHERE {$where_str}";
        $guide_count = $this->new_guide_svc->querySql($count_sql);
        $itemCount = $guide_count['list'] ? $guide_count['list']['0']['itemCount'] : '0';

        if ($itemCount) {
            $guide_list['pages'] = array(
                'itemCount' => $itemCount,
                'pageCount' => ceil($itemCount / $page_size),
                'page' => $page,
                'pageSize' => $page_size
            );
        }

        $sql = "SELECT {$column} FROM `gu_article` art {$left_str} WHERE {$where_str} ORDER BY art.order_num ASC,art.id DESC LIMIT {$offset},{$page_size}";
        $guide_data = $this->new_guide_svc->querySql($sql);

        $guide_list['list'] = $guide_data['list'] ? $guide_data['list'] : array();

        $this->_successResponse($guide_list);
    }

    /**
     * 根据目的地ID取关联的微攻略
     * 用于微攻略详情页相关微攻略
     */
    public function getRelationGuideByDestIdAction()
    {
        $guide_id = $this->guide_id;
        $dest_id = $this->dest_id;
        if (!$guide_id || !$dest_id)
            $this->_errorResponse(10001, "参数guide:{$guide_id},dest_id:{$dest_id}错误");

        $sql = "SELECT a.`id`,a.`title`,a.`thumb` FROM `gu_article` a LEFT JOIN `gu_article_dest_rel` adr ON a.id = adr.`guide_id` WHERE a.`status` = '1' AND adr.`dest_id` IN ({$dest_id}) AND a.`id` != {$guide_id} ORDER BY a.`publish_time` DESC LIMIT 3";
        $guide_data = $this->new_guide_svc->querySql($sql);
        $this->_successResponse($guide_data);
    }

    /**
     * 关联到目的地的微攻略接口
     * 用于目的地页面调取微攻略数据
     */
    public function getGuideByDestIdAction()
    {
        $dest_id = $this->request->get('dest_id');
        $num = $this->request->get('num') ? $this->request->get('num') : '3';
        $pic_size = $this->request->get('pic_size') ? $this->request->get('pic_size') : '';
        if (!$dest_id)
            $this->_errorResponse(10001, "参数dest_id:{$dest_id}错误");

        $sql = "SELECT a.`id`,a.`title`,a.`thumb`,a.`update_time` FROM `gu_article` a LEFT JOIN `gu_article_dest_rel` adr ON a.id = adr.`guide_id` WHERE a.`status` = '1' AND adr.`dest_id` = '{$dest_id}' ORDER BY a.`update_time` DESC LIMIT {$num}";
        $guide_data = $this->new_guide_svc->querySql($sql);
        foreach ($guide_data['list'] as $key => $row) {
            $guide_data['list'][$key]['url'] = "http://www.lvmama.com/lvyou/guide/mini-{$row['id']}.html";
            $guide_data['list'][$key]['username'] = "驴妈妈攻略编辑";
            $guide_data['list'][$key]['update_time'] = date('Y-m-d H:i:s', $row['update_time']);
            $thumb_url = $row['thumb'];
            if($pic_size)
                $thumb_url = UCommon::makePicSize($row['thumb'], $pic_size);
            $guide_data['list'][$key]['thumb'] = $thumb_url ? "http://pic.lvmama.com/" . $thumb_url : '';
        }
        $this->_successResponse($guide_data);
    }
}
