<?php

use Lvmama\Cas\Service\CommentDataService;
use Lvmama\Cas\Service\PageviewsDataService;
use Lvmama\Cas\Service\TripStatisticsDataService;
use Lvmama\Cas\Service\RedisDataService;
use Lvmama\Cas\Service\BeanstalkDataService;
use Lvmama\Common\Utils\Misc;

/**
 *  module
 *
 * @author xnw
 *
 */
class RecommendController extends ControllerBase {

    private $redis;
    private $mo_module;

    public function initialize() {
        parent::initialize();
        $this->mo_module = $this->di->get('cas')->get('module-data-service');
        $this->redis = $this->di->get('cas')->getRedis();
    }

    /**
     * 查询
     *
     * @author
     *
     */
    public function selectAction() {
        if($this->table) {
            $data['table'] = $this->table;
        }
        if($this->select) {
            $data['select'] = $this->select;
        }
        if($this->where) {
            $data['where'] = unserialize($this->where);
        }
        if($this->order) {
            $data['order'] = $this->order;
        }
        if($this->group) {
            $data['group'] = $this->group;
        }
        if($this->limit) {
            $data['limit'] = $this->limit;
        }
        if($this->page) {
            $data['page'] = unserialize($this->page);
        }
        $res=$this->mo_module->select($data);
        $this->_successResponse($res);
    }

    /**
     * 原生SQL
     *
     * @author
     *
     */
    public function queryAction() {
        if($this->sql) {
            $res=$this->querySql($this->sql);
        }
        $this->_successResponse($res);
    }

    /**
     * 执行SQL语句
     * @param $sql
     * @return mixed
     */
    private function querySql($sql){
        return $this->mo_module->querySql($sql);
    }

    /**
     * 游记推荐广告位
     *
     * @author
     *
     */
    public function travelRecommendAction() {
        $block_sql = "SELECT `block_id` FROM `mo_recommend_block` WHERE `object_type`='trip' AND `object_id`='index'  AND `identity`='trip_rec_notice' AND `status`='99'";
        $block_id = $this->querySql($block_sql)['list'][0]['block_id'];
        $sql = "SELECT `id`,`title`,`url`,`status`,`seq` FROM `mo_recommend` WHERE `block_id`={$block_id} AND `status`='99' ORDER BY seq ASC,block_id DESC";
        $res = $this->querySql($sql);
        $this->_successResponse($res);
    }
}
