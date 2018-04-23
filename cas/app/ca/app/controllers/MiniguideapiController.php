<?php

class MiniguideapiController extends ControllerBase
{
    private $newGuideDataService;
    private $imagePrefix = '';

    public function initialize()
    {
        parent::initialize();

        $this->newGuideDataService = $this->di->get("cas")->get("new_guide_data_service");
    }

    /**
     * 获取微攻略列表数据
     * @author jianghu
     */
    public function getBriefListAction()
    {
        $params = array(
            'table' => "article",
            'select' => "`id`,`title`,`thumb`",
            'where' => array('status' => '1'),
            'order' => "`publish_time` DESC",
            'page' => array(
                'page' => $this->request->get('page'),
                'pageSize' => $this->request->get('pageSize'),
            ),
        );

        $dataList = $this->newGuideDataService->select($params);

        if($dataList['list']) {
            $guide_id_arr = array();
            foreach ($dataList['list'] as $key => $row) {
                $guide_id_arr[] = $row['id'];
                $dataList['list'][$key]['thumb'] = $row['thumb'] ? $this->imagePrefix . $row['thumb'] : "";
            }
            if ($guide_id_arr) {
                $guide_id_str = implode("','", $guide_id_arr);
                $params = array(
                    'table' => 'article_dest_rel',
                    'select' => '`guide_id`,`dest_id`',
                    'where' => array('guide_id' => array('IN',"('" . $guide_id_str . "')"),'is_main' => '1'),
                );
            }
            $guide_dest_datas = $this->newGuideDataService->select($params);
            $guide_dest_arr = array();
            foreach ($guide_dest_datas['list'] as $dest_data) {
                $guide_dest_arr[$dest_data['guide_id']] = $dest_data['dest_id'];
            }

            foreach ($dataList['list'] as $key => $row) {
                $dataList['list'][$key]['main_dest_id'] = isset($guide_dest_arr[$row['id']]) ? $guide_dest_arr[$row['id']] : 0;
            }
        }else
            $dataList = array();
        $this->_successResponse($dataList);
    }

    /**
     * 获取微攻略详细数据
     * @author jianghu
     */
    public function getDetailInfoAction()
    {
        $guideId = $this->request->get('guideId');

        //主表数据
        $params = array(
            'table' => "article",
            'select' => "`id`,`title`,`thumb`,`summary`,`start_time`,`update_time`",
            'where' => array('id' => $guideId,'status' => 1),
            'limit' => '1',
        );
        $articleData = $this->newGuideDataService->select($params);
        foreach ($articleData['list'] as $key => $row) {
            $articleData['list'][$key]['thumb'] = $row['thumb'] ? $this->imagePrefix . $row['thumb'] : "";
        }

        if(!$articleData || !$articleData['list'])
            $this->_errorResponse('404', '文章未找到或不显示');

        $articleData['list']['0']['update_time'] = date('Y-m-d', $articleData['list']['0']['update_time']);

        //获取主目的地
        $params = array(
            'table' => 'article_dest_rel',
            'select' => '`dest_id`',
            'where' => array('guide_id' => $guideId,'is_main' => '1'),
        );

        $guideMainDestInfo = $this->newGuideDataService->select($params);

        $articleData['list']['0']['main_dest_id'] = $guideMainDestInfo['list'] ? $guideMainDestInfo['list']['0']['dest_id'] : "";

        //内容表数据
        $params = array(
            'table' => "article_content",
            'select' => "`id`,`title` AS `contentTitle`,`content`",
            'where' => array('guide_id' => $guideId),
            'order' => "`order_num` ASC",
        );
        $articleContentData = $this->newGuideDataService->select($params);

        $articleData['list']['0']['contentData'] = $articleContentData;

        $this->_successResponse($articleData);
    }

    /**
     * 根据多个ID查询列表数据
     */
    public function getInfosByIdsAction()
    {
        $idJson = $this->request->get('idArr');
        $idArr = json_decode($idJson,true);
        $ids = implode("','", $idArr);
        $dataList = array();

        if($ids) {
            $params = array(
                'table' => 'article',
                'select' => '`id`,`title`,`thumb`',
                'where' => array('id' => array('IN', "('{$ids}')"), 'status' => 1),
            );

            $dataList = $this->newGuideDataService->select($params);

            $params = array(
                'table' => 'article_dest_rel',
                'select' => '`guide_id`,`dest_id`',
                'where' => array('guide_id' => array('IN',"('" . $ids . "')"),'is_main' => '1'),
            );

            $guide_dest_datas = $this->newGuideDataService->select($params);
            $guide_dest_arr = array();
            foreach ($guide_dest_datas['list'] as $dest_data) {
                $guide_dest_arr[$dest_data['guide_id']] = $dest_data['dest_id'];
            }

            foreach ($dataList['list'] as $key => $row) {
                $dataList['list'][$key]['thumb'] = $row['thumb'] ? $this->imagePrefix . $row['thumb'] : "";
                $dataList['list'][$key]['main_dest_id'] = isset($guide_dest_arr[$row['id']]) ? $guide_dest_arr[$row['id']] : 0;
            }
        }
        $this->_successResponse($dataList);
    }

}