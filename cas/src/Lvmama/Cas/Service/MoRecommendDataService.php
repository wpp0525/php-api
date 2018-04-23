<?php
namespace Lvmama\Cas\Service;

use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;

/**
 * 推荐目的地 服务类
 * @author libiying
 */
class MoRecommendDataService extends DataServiceBase{


    const TABLE_NAME = 'mo_recommend';

    public function getRecommendDestIds($identity, $recommend_name = array(), $limit = null){
        if(!$identity || !is_string($identity) || !is_array($recommend_name)){
            return false;
        }

        $sql = "
        SELECT
            r.object_id,
            rb.`name`,
            rb.identity
        FROM
            mo_recommend AS r
        INNER JOIN mo_recommend_block AS rb ON rb.block_id = r.block_id
        WHERE
            rb.identity = '" . $identity . "'
        ";
        if(!empty($recommend_name)){
            $sql .= " AND rb.name in (";
            foreach ($recommend_name as $name){
                $sql .= "'$name',";
            }
            $sql = substr($sql, 0, -1);
            $sql .= ")";
        }
        if($limit){
            $sql .= " LIMIT " . $limit;
        }

        return $this->query($sql,'All');
    }

    public function getRecommendBlocks($conditions, $limit = null, $order = null){
        return $this->getList($conditions, 'mo_recommend_block', null, null, $order);
    }
    /**
     * 根据目的地ID获取焦点图
     */
    public function getDestFocusByDestid($dest_id){
        $block_id = $this->query("SELECT block_id FROM mo_recommend_block WHERE object_type = 'lvyou' AND `status`=99 AND object_id='dest_{$dest_id}' AND `identity`='destfocus'");
        $dest_focus = array();
        if($block_id['block_id']){
            $dest_focus = $this->query("SELECT image,intro FROM ".self::TABLE_NAME." WHERE `status`=99  AND  `block_id`={$block_id['block_id']} ORDER BY seq ASC LIMIT 5",'All');
        }
        return $dest_focus;
    }


    // 获取当月热推
    public function getDestRecomSeason($dest_id, $month = 0){
        // 获取block_id
        if(!$month){
            $month = intval(date("m", time()));
        }
        $sql = "SELECT b.block_id, b.`name` FROM mo_recommend_block a
                      LEFT JOIN  mo_recommend_block b ON a.block_id = b.parent_id
                      WHERE a.identity = 'lvyou_recom_season' AND a.parent_id = 0 AND b.`name` IN ('{$month}月', '".($month+1)."月', '".($month+2)."月')";

        $block = $this->query($sql, 'All');

        $return = array();
        foreach($block as $block_id){

            $sql2 = "SELECT `object_id`,`title`,`url` FROM `mo_recommend` WHERE `block_id` = {$block_id['block_id']} AND `object_id` != {$dest_id}  LIMIT 4";

            $res = $this->query($sql2, 'All');

            if($res && is_array($res)){
                $return = array_merge($return, $res);
            }

        }

        $return = array_slice($return, 0, 10);
        return $return;

    }







}