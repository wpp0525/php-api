<?php

use Lvmama\Cas\Component\DaemonServiceInterface,
    Lvmama\Common\Components\ApiClient,
    Lvmama\Common\Utils\UCommon;

class TravelToGuideWorkerService implements DaemonServiceInterface
{

    private $traveldatasvc;
    private $newguidedatasvc;
    private $flag_id;

    public function __construct($di)
    {
        $this->traveldatasvc = $di->get('cas')->get('travel_data_service');
        $this->traveldatasvc->setReconnect(true);

        $this->newguidedatasvc = $di->get('cas')->get('new_guide_data_service');
        $this->newguidedatasvc->setReconnect(true);
    }

    /**
     * @see \Lvmama\Cas\Component\DaemonServiceInterface::process()
     */
    public function process($timestamp = null, $flag = null)
    {
        $this->flag_id = $flag;
        $this->insertData();
    }

    public function processUpdateUserId($timestamp = null, $flag = null)
    {
        $sql = "UPDATE `gu_article` SET `uid` = '2c9486ef5cef78c6015d0738605e418c',`username` = 'xinren01' WHERE `uid` = '2c9486e5596a6a780159820fa8570d45'";
        $res = $this->newguidedatasvc->querySql($sql);
        if($res == 'success')
            die('done');
        die('faild');
    }

    /**
     * @see \Lvmama\Cas\Component\DaemonServiceInterface::shutdown()
     */
    public function shutdown($timestamp = null, $flag = null)
    {
        // nothing to do
    }

    private function insertData()
    {
        $allow_id = $this->getAllowId();

        foreach ($this->getRows($allow_id) as $travel_id) {
            echo '开始迁移，id：', $travel_id, "\n";
            $travel_data = $this->getTravelData($travel_id);
            if (!$travel_data)
                continue;

            $guide_id = $this->insertDataToGuide($travel_data);
            if (!$guide_id)
                continue;

            $travel_content_data = $this->getTravelContentData($travel_id);
            foreach ($travel_content_data as $row) {
                $article_content_id = $this->insertDataToArticleContent($row, $guide_id);

                $travel_content_dest_del_data = $this->getTravelContentDestRelData($travel_id, $row['id']);
                if($travel_content_dest_del_data)
                    $this->insertDataToArticleContentDestRel($guide_id, $article_content_id, $travel_content_dest_del_data);

                $travel_image_rel = $this->getTravelImageRelData($travel_id, $row['id']);

                if ($travel_image_rel) {
                    $image_id_arr = array();
                    foreach ($this->getRows($travel_image_rel) as $item) {
                        $image_id_arr[] = $item['image_id'];
                    }

                    $image_data = $this->getImageDataByImageId($image_id_arr);

                    $insert_image_id_arr = array();
                    foreach ($this->getRows($image_data) as $data) {
                        $insert_image_id_arr[] = $this->insertDataToImage($data);
                    }

                    $this->insertDataToArticleImageRel($guide_id, $article_content_id, $insert_image_id_arr);
                }
            }

            $travel_dest_rel_data = $this->getTravelDestRelData($travel_id);
            $this->insertDataToArticleDestRel($travel_dest_rel_data, $guide_id);

            $travel_ext_data = $this->getTravelExtData($travel_id);
            $this->insertDataToArticleExt($travel_ext_data, $guide_id);
            echo '迁移完成，id：', $travel_id, "\n";
        }

        die('done');
    }

    private function getAllowId()
    {
        if ($this->flag_id)
            $allow_id_arr = array($this->flag_id);
        else
            $allow_id_arr = array('267864','268014', '268448', '268466', '268019', '268999', '269095', '269083', '269109', '269115', '269719', '271530', '271628', '271855', '271758', '271652', '272181', '272600', '272818', '272680', '272178', '272595', '272969', '272360', '272826', '272825', '273079', '273769', '273902', '273341', '273774', '273881', '273987', '274169', '274784', '274082', '272695', '274907', '274407', '274681', '275545', '275544', '274845', '275059', '275693', '275682', '274969', '275931', '275484', '275485', '275681', '276593', '276541', '276539', '276546', '276930', '276924', '277821', '277829', '277063', '277884', '276928', '277860', '278210', '271603', '278339', '278148', '277994', '278273', '278262', '278845', '278328', '279006', '278751', '278733', '279005', '279175', '279295', '279504', '278937', '279123', '279113', '279152', '279590', '280458', '280036', '280846', '277967', '280351', '280935', '280952', '279452', '281085', '281296', '281011', '281434', '281316', '280100', '280122', '281793', '282118', '281809', '281409', '280533', '283285', '283281', '283114', '282214', '282021', '281803', '281800', '281964', '283271', '283708', '283340', '284553', '284879', '284955', '283070', '285039', '283456', '284972', '284777', '285533', '285147', '285754', '285551', '285613', '286265', '286007', '285757', '286570', '286264', '286399', '286718', '286800', '286572', '286769', '287356', '287684', '287803', '287989', '288408', '286585', '289424', '289431', '288049', '288535', '288417', '289892', '289853', '289561', '290763', '290741', '289810', '291645', '291623', '289816', '291657', '292197', '292245', '292276', '292198', '292932', '293516', '293374', '293537', '293536', '293252', '293361', '293363', '293680', '294723', '294596', '294509', '294763', '294455', '293559', '294657', '294865', '295524', '294769', '295466', '295763', '285869', '295607', '295530', '294409', '296379', '295658', '295590', '296766', '295885', '296331', '297181', '297076', '297071', '297048', '296480', '296692', '297372', '297081', '297148', '298022', '297741', '298033', '298303', '298000', '298561', '298139', '298154', '298504', '298946', '299026', '299390', '299159', '299173', '299556', '299619', '299646', '299095', '301551', '299586', '300566', '300221', '300453', '299610', '300930', '300281', '299501', '300564', '301106', '300962');

        return $allow_id_arr;
    }

    /**
     * 查询游记主表数据
     * @param $travel_id
     * @return array
     */
    private function getTravelData($travel_id)
    {
        $params = array(
            'table' => 'travel',
            'select' => '*',
            'where' => array('id' => $travel_id),
            'limit' => 1
        );

        $travel_info = $this->traveldatasvc->select($params);
        if ($travel_info['list'])
            return $travel_info['list']['0'];

        echo "游记主表中未找到ID为{$travel_id}相关数据", "\n", 'end', "\n";
        return array();
    }

    /**
     * 微攻略主表数据写入
     * @param $travel_data
     */
    private function insertDataToGuide($travel_data)
    {
        unset($travel_data['id']);
        $params = array(
            'table' => 'article',
            'data' => $travel_data,
        );
        $res = $this->newguidedatasvc->insert($params);
        if ($res['error'])
            echo '插入微攻略主表失败', json_encode($params), "\n", 'end', "\n";

        echo "插入微攻略主表成功，ID：{$res['result']}", "\n";
        return $res['result'];
    }

    /**
     * 获取游记内容表数据
     * @param $travel_id
     * @return array
     */
    private function getTravelContentData($travel_id)
    {
        $params = array(
            'table' => 'travel_content',
            'select' => '`id`,`title`,`content`,`order_num`,`sync_status`,`create_time`,`update_time`',
            'where' => array('travel_id' => $travel_id),
        );

        $travel_content_info = $this->traveldatasvc->select($params);
        if ($travel_content_info['list'])
            return $travel_content_info['list'];

        echo "游记内容表中未找到ID为{$travel_id}相关数据", "\n";
        return array();
    }

    /**
     * 迁移章节数据
     * @param $travel_content_data
     * @param $guide_id
     */
    private function insertDataToArticleContent($travel_content_data, $guide_id)
    {
        unset($travel_content_data['id']);
        $travel_content_data['guide_id'] = $guide_id;
        $params = array(
            'table' => 'article_content',
            'data' => $travel_content_data,
        );

        $res = $this->newguidedatasvc->insert($params);
        if ($res['error'])
            echo '插入微攻略章节表失败', json_encode($params), "\n";

        echo "插入微攻略章节表成功，ID：{$res['result']}", "\n";
        return $res['result'];
    }

    /**
     * 获取章节关联数据
     * @param $travel_id
     * @param $travel_content_id
     * @return array
     */
    private function getTravelContentDestRelData($travel_id, $travel_content_id)
    {
        $params = array(
            'table' => 'travel_content_dest_rel',
            'select' => '`dest_id`,`dest_type`,`is_main`,`create_time`,`update_time`',
            'where' => array('travel_content_id' => $travel_content_id, 'travel_id' => $travel_id),
        );

        $travel_content_dest_del_info = $this->traveldatasvc->select($params);
        if ($travel_content_dest_del_info['list'])
            return $travel_content_dest_del_info['list'];

        echo "游记内容与目的地关联表中未找到游记ID：{$travel_id}、内容ID为：{$travel_content_id}相关数据", "\n";
        return array();
    }

    /**
     * 迁移章节关联目的地数据
     * @param $guide_id
     * @param $article_content_id
     * @param $travel_content_dest_del_data
     * @return mixed
     */
    private function insertDataToArticleContentDestRel($guide_id, $article_content_id, $travel_content_dest_del_data)
    {
        $insert_arr = array();
        foreach ($this->getRows($travel_content_dest_del_data) as $row) {
            $value_str = implode("','", array_values($row));
            $insert_arr[] = "(NULL,'{$guide_id}','{$article_content_id}','{$value_str}')";
        }
        $insert_str = implode(',', $insert_arr);

        $insert_sql = "INSERT INTO `gu_article_content_dest_rel` VALUES {$insert_str}";
        $this->newguidedatasvc->querySql($insert_sql);
        echo "插入微攻略文章目的地关联表成功", "\n";
    }

    /**
     * 获取游记关联目的地数据
     * @param $travel_id
     * @return array
     */
    private function getTravelDestRelData($travel_id)
    {
        $params = array(
            'table' => 'travel_dest_rel',
            'select' => '`dest_id`,`is_main`,`create_time`,`update_time`',
            'where' => array('travel_id' => $travel_id),
        );

        $travel_dest_del_info = $this->traveldatasvc->select($params);
        if ($travel_dest_del_info['list'])
            return $travel_dest_del_info['list'];

        echo "游记与目的地关联表中未找到游记ID：{$travel_id}相关数据", "\n";
        return array();
    }

    /**
     * 迁移文章目的地数据
     * @param $data
     * @param $guide_id
     */
    private function insertDataToArticleDestRel($travel_dest_rel_data, $guide_id)
    {
        $insert_arr = array();
        foreach ($this->getRows($travel_dest_rel_data) as $row) {
            $value_str = implode("','", array_values($row));
            $insert_arr[] = "(NULL,'{$guide_id}','{$value_str}')";
        }
        $insert_str = implode(',', $insert_arr);

        $insert_sql = "INSERT INTO `gu_article_dest_rel` VALUES {$insert_str}";
        $this->newguidedatasvc->querySql($insert_sql);
        echo "插入微攻略文章目的地关联表成功", "\n";
    }

    /**
     * 获取游记扩展表数据
     * @param $travel_id
     * @return array
     */
    private function getTravelExtData($travel_id)
    {
        $params = array(
            'table' => 'travel_ext',
            'select' => '*',
            'where' => array('travel_id' => $travel_id),
            'limit' => '1',
        );
        $travel_ext_info = $this->traveldatasvc->select($params);
        if ($travel_ext_info['list'])
            return $travel_ext_info['list']['0'];

        echo "游记扩展表中未找到游记ID：{$travel_id}相关数据", "\n";
        return array();
    }

    /**
     * 迁移扩展表数据
     * @param $travel_ext_data
     * @param $guide_id
     * @return mixed
     */
    private function insertDataToArticleExt($travel_ext_data, $guide_id)
    {
        unset($travel_ext_data['id']);
        unset($travel_ext_data['travel_id']);
        $travel_ext_data['guide_id'] = $guide_id;

        $params = array(
            'table' => 'article_ext',
            'data' => $travel_ext_data,
        );

        $res = $this->newguidedatasvc->insert($params);
        if ($res['error'])
            echo '插入微攻略文章扩展表失败', json_encode($params), "\n";

        echo "插入微攻略文章扩展表成功，ID：{$res['result']}", "\n";
        return $res['result'];
    }

    /**
     * 获取游记图片关联表数据
     * @param $travel_id
     * @param $travel_content_id
     * @return array
     */
    private function getTravelImageRelData($travel_id, $travel_content_id)
    {
        $params = array(
            'table' => 'travel_image_rel',
            'select' => '`image_id`',
            'where' => array('travel_id' => $travel_id, 'travel_content_id' => $travel_content_id),
        );

        $travel_image_rel_info = $this->traveldatasvc->select($params);
        if ($travel_image_rel_info['list'])
            return $travel_image_rel_info['list'];

        echo "游记图片关联表中未找到游记ID：{$travel_id},内容ID：{$travel_content_id}相关数据", "\n";
        return array();
    }

    /**
     * 获取图片表数据
     * @param array $image_id_arr
     * @return array
     */
    private function getImageDataByImageId($image_id_arr = array())
    {
        $image_id_str = implode("','", $image_id_arr);
        $params = array(
            'table' => 'image',
            'select' => '*',
            'where' => array('id' => array('IN', "('{$image_id_str}')")),
        );

        $image_info = $this->traveldatasvc->select($params);
        if ($image_info['list'])
            return $image_info['list'];

        echo "游记图片表中未找到相关数据", "\n";
        return array();
    }

    /**
     * 迁移图片表数据
     * @param $data
     * @return mixed
     */
    private function insertDataToImage($data)
    {
        unset($data['id']);
        $params = array(
            'table' => 'image',
            'data' => $data,
        );

        $res = $this->newguidedatasvc->insert($params);
        if ($res['error'])
            echo '插入微攻略图片表失败', json_encode($params), "\n";

        echo "插入微攻略图片表成功，ID：{$res['result']}", "\n";
        return $res['result'];
    }

    /**
     * 迁移图片关联表数据
     * @param $guide_id
     * @param $article_content_id
     * @param $insert_image_id_arr
     */
    private function insertDataToArticleImageRel($guide_id, $article_content_id, $insert_image_id_arr)
    {
        $insert_arr = array();
        $curr_time = time();
        foreach ($this->getRows($insert_image_id_arr) as $value) {
            $insert_arr[] = "(NULL,'{$guide_id}','{$article_content_id}','{$value}','{$curr_time}','{$curr_time}')";
        }
        $insert_str = implode(',', $insert_arr);

        $insert_sql = "INSERT INTO `gu_article_image_rel` VALUES {$insert_str}";
        $this->newguidedatasvc->querySql($insert_sql);
        echo "插入微攻略图片关联表成功", "\n";
    }

    /**
     * 生成器
     * @param array $data
     * @return Generator
     */
    private function getRows(array $data)
    {
        foreach ($data as $item) {
            yield $item;
        }
    }

}