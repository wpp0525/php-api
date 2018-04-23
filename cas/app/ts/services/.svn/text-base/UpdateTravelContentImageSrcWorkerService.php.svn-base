<?php

use Lvmama\Cas\Component\DaemonServiceInterface;

class UpdateTravelContentImageSrcWorkerService implements DaemonServiceInterface
{

    private $traveldatasvc;
    private $flag_id;


    public function __construct($di)
    {
        $this->traveldatasvc = $di->get('cas')->get('travel_data_service');
        $this->traveldatasvc->setReconnect(true);
    }

    /**
     * @see \Lvmama\Cas\Component\DaemonServiceInterface::process()
     */
    public function process($timestamp = null, $flag = null)
    {
        $this->flag_id = $flag;
        $this->updateData();
    }

    /**
     * @see \Lvmama\Cas\Component\DaemonServiceInterface::shutdown()
     */
    public function shutdown($timestamp = null, $flag = null)
    {
        // nothing to do
    }

    /**
     * 更新内容表数据
     * @param int $start
     */
    private function updateData()
    {
        $content_ids_str = $image_ids_str = '';
        $content_res = $image_res = $content_image_res = array();
        $image_ids_arr = $content_ids_arr = $content_image_arr = array();
        $image_data = array();

        $content_res = $this->getSyncContentId();
        if (!isset($content_res['list']) || !$content_res['list']) die('done');

        foreach ($this->getRows($content_res['list']) as $row) {
            $content_ids_arr[] = $row['id'];
            if (!isset($content_image_arr[$row['id']]))
                $content_image_arr[$row['id']] = $row;
        }
        $content_ids_str = implode('","', $content_ids_arr);
        unset($content_res);

        $content_image_res = $this->getImageIdsByContentId($content_ids_str);
        foreach ($this->getRows($content_image_res['list']) as $row) {
            $image_ids_arr[] = $row['image_id'];
            $content_image_arr[$row['travel_content_id']]['image_id'][] = $row['image_id'];
        }
        $image_ids_str = implode('","', $image_ids_arr);
        unset($content_image_res);

        $image_res = $this->getImageDataByImageId($image_ids_str);
        foreach ($this->getRows($image_res['list']) as $row) {
            if (!isset($image_data[$row['id']]))
                $image_data[$row['id']] = $row;
        }
        unset($image_res);

        foreach ($this->getRows($content_image_arr) as $row) {
            $tmp = true;
            $new_content = '';
            $old_url = $new_url = array();
            foreach ($this->getRows($row['image_id']) as $image_id) {
                if (!$image_data[$image_id]['pic_url']) {
                    $tmp = false;
                    break;
                }
                $old_url[] = 'http://iguide.lvmama.com' . $image_data[$image_id]['url'];
                $new_url[] = 'http://pic.lvmama.com' . $image_data[$image_id]['pic_url'];
            }
            if (!$tmp) continue;
            $new_content = str_replace($old_url, $new_url, $row['content']);

            $curr_content = $this->getContentByContentId($row['id']);
            $curr_content = $curr_content['list'] ? $curr_content['list'][0]['content'] : '';
            if (md5($curr_content) === md5($row['content'])) {
                $this->traveldatasvc->update(array(
                    'table' => 'travel_content',
                    'where' => "`id` = {$row['id']}",
                    'data' => array(
                        'content' => $new_content,
                        'sync_status' => '99',
                    ),
                ));
            }
        }
    }

    /**
     * 获取未更新图片路径的章节数据
     * @return mixed
     */
    private function getSyncContentId()
    {
        return $this->traveldatasvc->select(array(
            'table' => 'travel_content',
            'select' => '`id`,`travel_id`,`content`',
            'where' => array('sync_status' => 0),
        ));
    }

    /**
     * 根据章节ID获取章节中的图片的图片ID
     * @param string $content_ids
     * @return mixed
     */
    private function getImageIdsByContentId($content_ids)
    {
        return $this->traveldatasvc->select(array(
            'table' => 'travel_image_rel',
            'select' => '`travel_content_id`,`image_id`',
            'where' => array('travel_content_id' => array('IN', '("' . $content_ids . '")')),
        ));
    }

    /**
     * 根据图片ID返回图片数据
     * @param string $image_ids
     * @return mixed
     */
    private function getImageDataByImageId($image_ids)
    {
        return $this->traveldatasvc->select(array(
            'table' => 'image',
            'select' => '`id`,`url`,`pic_url`',
            'where' => array('id' => array('IN', '("' . $image_ids . '")')),
        ));
    }

    /**
     * 根据内容 ID 获取内容
     * @param int $content_id
     * @return mixed
     */
    private function getContentByContentId($content_id = 0)
    {
        return $this->traveldatasvc->select(array(
            'table' => 'travel_content',
            'select' => '`content`',
            'where' => array('id' => $content_id),
        ));
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