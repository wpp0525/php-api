<?php

namespace Lvmama\Cas\Service\Message;

use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;
use Lvmama\Cas\Service\RedisDataService;

class MessageDataService extends DataServiceBase {

    const TABLE_USER = 'ms_message_user';
    const TABLE_CONTENT = 'ms_message_content';
    const TABLE_CHANNEL = 'ms_message_channel';
    const TABLE_TYPE = 'ms_message_type';

    const EXPIRE_TIME = 86400;

    /**
     * 根据用户uid获取未读消息数
     * @param $uid string
     * @auth zhaiyuansen
     * @return string 未读消息数
     */
    public function getUnreadCount($uid){
        $channels_sql = "select count(*) as count from ".self::TABLE_CHANNEL;
        $channels = $this->getAdapter()->fetchOne($channels_sql, \PDO::FETCH_ASSOC);
        $chan_user_sql = "select count(*) as count from ".self::TABLE_USER." where `uid` = :uid AND `channel` > 0";
        $channel_user = $this->getAdapter()->fetchOne($chan_user_sql, \PDO::FETCH_ASSOC, array(':uid' => $uid));
        if($channels['count'] > $channel_user['count']) $this->broadChannels($uid);
        $sql = "select count(*) as Count from ".self::TABLE_USER." where `uid` = '".$uid."' AND `status` = 0";
        return $this->getAdapter()->fetchOne($sql, \PDO::FETCH_ASSOC);
    }

    /**
     * 根据用户uid，补全公告消息
     * @param $uid string
     * @auth zhaiyuansen
     * @return void
     */
    public function broadChannels($uid){
        $channels_sql = "select `channel_id`,`subject`,`content` from ".self::TABLE_CHANNEL;
        $channel_ids = $this->getAdapter()->fetchAll($channels_sql, \PDO::FETCH_ASSOC);
        $chan_user_sql = "select `channel` from ".self::TABLE_USER." where `uid` = :uid AND `channel` > 0";
        $chan_user_id = $this->getAdapter()->fetchAll($chan_user_sql, \PDO::FETCH_ASSOC, array(':uid' => $uid));
        $chan_user = array();
        foreach($chan_user_id as $m => $n){
            $chan_user[] = $n['channel'];
        }
        foreach($channel_ids as $k => $v){
            if(in_array($v['channel_id'], $chan_user)) continue;
            $mid = $this->getAdapter()->insert(self::TABLE_USER, array($uid, $v['subject'], $v['channel_id'], time(),'CHANNEL'), array("uid","title","channel","create_time","type"));
            if($mid == false) continue;
            $this->getAdapter()->insert(self::TABLE_CONTENT, array($v['content'], $mid), array('content','m_id'));
        }
    }

    /**
     * 根据用户uid，类型type查询用户消息总数
     * @param $uid
     * @param $type
     * @return array
     */
    public function getTypeUnreadCount($uid, $type){
        $sql = "select count(*) as Count from ".self::TABLE_USER." where `uid` = '".$uid."' AND `status` = 0 AND `type` IN (".$type.")";
        return $this->getAdapter()->fetchOne($sql, \PDO::FETCH_ASSOC);
    }

    /**
     * 根据mid查询消息具体内容
     * @param $mid
     * @return array
     */
    public function getMsgDetail($mid){
        $this->getAdapter()->update(self::TABLE_USER, array('status','read_time'), array("1",time()), "mid = ".$mid);
        $sql = "select `content` from ".self::TABLE_CONTENT." where `m_id` = ".$mid;
        return $this->getAdapter()->fetchOne($sql, \PDO::FETCH_ASSOC);
    }

    /**
     * 消息阅读，根据mid，更新status=1
     * @param $mid
     */
    public function msgRead($mid){
        return $this->getAdapter()->update(self::TABLE_USER, array('status','read_time'), array("1",time()), "mid = ".$mid);
    }

    /**
     * 消息删除，根据mid，更新status=2
     * @param $mid
     */
    public function msgDelete($mid){
        return $this->getAdapter()->update(self::TABLE_USER, array('status'), array("2"), "mid = ".$mid);
    }

    /**
     * 获取用户消息概况(无消息详情)
     * @param $uid 用户32位userid
     * @param string $type 消息类型 SYSTEM-系统消息 ASSET-资产消息 ACTIVE-活动消息 SOCIAL-互动消息，若不传，则返回所有消息
     * @param int $unread 是否未读，1-仅返回未读消息，默认0-已读未读都返回
     * @param int $page 页数
     * @param int $pageSize 每页数据条数
     * @return array
     */
    public function getMsgByUid($uid, $type = '', $unread = 0, $page = 1, $pageSize = 10){
        $condition = "`uid` = :uid";
        if($unread == 0) $condition .= " AND `status` < 2";
        else $condition .= " AND `status` = 0";
        if($type != '') $condition .= " AND `type` IN (".$type.") ";
        $condition .= " order by `create_time` DESC, `mid` DESC limit ".($page-1)*$pageSize.",".$pageSize;
        $sql = "select `mid`,`pid`,`uid`,`title`,FROM_UNIXTIME(`create_time`) as create_time,`status`,`type`,`channel` from ".self::TABLE_USER." where ".$condition;
        return $this->getAdapter()->fetchAll($sql, \PDO::FETCH_ASSOC, array(':uid' => $uid));
    }

    /**
     * 获取用户所有消息(未读+已读+已删除)
     * @param $uid 用户32位userid
     * @param int $page 页数
     * @param int $pageSize 每页数据条数
     * @return mixed
     */
    public function getAllMsgByUid($uid, $page = 1, $pageSize = 10){
        $condition = "`uid` = :uid order by `create_time` DESC, `mid` DESC limit ".($page-1)*$pageSize.",".$pageSize;
        $sql = "select `mid`,`pid`,`uid`,`title`,FROM_UNIXTIME(`create_time`) as create_time,`status`,`type` from ".self::TABLE_USER." where ".$condition;
        $sqlCount = "select count(*) as count from ".self::TABLE_USER." where `uid` = '".$uid."'";
        $ret['data'] = $this->getAdapter()->fetchAll($sql, \PDO::FETCH_ASSOC, array(':uid' => $uid));
        $ret['count'] = $this->getAdapter()->fetchOne($sqlCount, \PDO::FETCH_ASSOC);
        return $ret;
    }

    /**
     * 获取消息类型对应的组类型
     * @return array|mixed
     */
    public function getTypeGroup(){
        $key = RedisDataService::REDIS_MSG_TYPE_GROUP_ARR;
        $ret = json_decode($this->redis->GET($key));
        if(!$ret){
            $sql = "select `type_code`,`type_name`,`group_id` from ".self::TABLE_TYPE;
            $group = $this->getAdapter()->fetchAll($sql, \PDO::FETCH_ASSOC);
            $ret = array();
            foreach($group as $k => $v){
                $ret[$v['type_code']] = $v['group_id'];
            }
            $this->redis->setex($key, self::EXPIRE_TIME, json_encode($ret));
        }
        return $ret;
    }

    /**
     * 获取用户消息总数
     * @param $uid 用户32位userid
     * @param string $type 消息类型 SYSTEM-系统消息 ASSET-资产消息 ACTIVE-活动消息 SOCIAL-互动消息，若不传，则返回所有消息数
     * @param int $unread 是否未读，1-仅返回未读消息，默认0-已读未读都返回
     * @return array
     */
    public function getMsgNumByUid($uid, $type= '', $unread = 0){
        $sql = "select count(*) as Count from ".self::TABLE_USER." where `uid` = '".$uid."'";
        if($unread == 0) $sql .= " AND `status` < 2";
        else $sql .= " AND `status` = 0";
        if($type != '') $sql .= " AND `type` IN (".$type.") ";
        return $this->getAdapter()->fetchOne($sql, \PDO::FETCH_ASSOC);
    }

    /**
     * 获取用户各类消息未读数
     * @param $uid 用户32位userid
     * @return array SYSTEM-系统消息 ASSET-资产消息 ACTIVE-活动消息 SOCIAL-互动消息
     */
    public function getGroupCountByUid($uid){
        $sql = "select `type`, count(*) as count from ".self::TABLE_USER." where `uid` = '".$uid."' AND `status` = 0 GROUP BY `type`";
        $typeCount = $this->getAdapter()->fetchAll($sql, \PDO::FETCH_ASSOC);
        $groupCount = array('SYSTEM' => 0, 'ASSET' => 0, 'ACTIVE' => 0, 'SOCIAL' => 0);
        $key = RedisDataService::REDIS_MSG_GROUP_CODE_ARR;
        $group = json_decode($this->redis->GET($key));
        if(!$group){
            $sqltype = "select `type_code`,`group_id` from ".self::TABLE_TYPE;
            $types = $this->getAdapter()->fetchAll($sqltype, \PDO::FETCH_ASSOC);
            foreach($types as $m => $n){
                if($n['group_id'] == 1) $group['SYSTEM'][] = $n['type_code'];
                if($n['group_id'] == 2) $group['ASSET'][] = $n['type_code'];
                if($n['group_id'] == 3) $group['ACTIVE'][] = $n['type_code'];
                if($n['group_id'] == 4) $group['SOCIAL'][] = $n['type_code'];
            }
            $this->redis->setex($key, self::EXPIRE_TIME, json_encode($group));
        }
        foreach($typeCount as $k => $v){
            if(in_array($v['type'], $group->SYSTEM)) $groupCount['SYSTEM'] += $v['count'];
            if(in_array($v['type'], $group->ASSET)) $groupCount['ASSET'] += $v['count'];
            if(in_array($v['type'], $group->ACTIVE)) $groupCount['ACTIVE'] += $v['count'];
            if(in_array($v['type'], $group->SOCIAL)) $groupCount['SOCIAL'] += $v['count'];
        }
        return $groupCount;
    }

    /**
     * 获取消息组
     * @auth zhaiyuansen
     * @return string | json 返回所有消息组类型数据
     */
    public function getGroupCode(){
        $key = RedisDataService::REDIS_MSG_GROUP_CODE;
        $ret = $this->redis->GET($key);
        if($ret) return json_decode($ret);
        $sql = "select `type_code`,`group_id` from ".self::TABLE_TYPE;
        $types = $this->getAdapter()->fetchAll($sql, \PDO::FETCH_ASSOC);
        $typecode = array('SYSTEM' => "", 'ASSET' => "", 'ACTIVE' => "", 'SOCIAL' => "");
        foreach($types as $k => $v){
            if($v['group_id'] == 1) $typecode['SYSTEM'] .= "'".$v['type_code']."',";
            if($v['group_id'] == 2) $typecode['ASSET'] .= "'".$v['type_code']."',";
            if($v['group_id'] == 3) $typecode['ACTIVE'] .= "'".$v['type_code']."',";
            if($v['group_id'] == 4) $typecode['SOCIAL'] .= "'".$v['type_code']."',";
        }
        $typecode['SYSTEM'] = substr($typecode['SYSTEM'], 0, -1);
        $typecode['ASSET'] = substr($typecode['ASSET'], 0, -1);
        $typecode['ACTIVE'] = substr($typecode['ACTIVE'], 0, -1);
        $typecode['SOCIAL'] = substr($typecode['SOCIAL'], 0, -1);
        $this->redis->setex($key, self::EXPIRE_TIME, json_encode($typecode));
        return $typecode;
    }

}