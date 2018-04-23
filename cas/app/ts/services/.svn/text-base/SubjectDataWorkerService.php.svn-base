<?php

use Lvmama\Cas\Component\DaemonServiceInterface;

class SubjectDataWorkerService implements DaemonServiceInterface{
    private $temp_sub;
    private $temp_sub_var;
    private $seo_tpl_var_svc;
    private $place;
    private $temp_zt2_block;
    private $temp_zt2_data;
    private $product;
    private $goods;
    private $kafka;

    private $block = array();

    public function __construct($di) {
        $this->block           = $this->getBlock();
        $this->temp_sub        = $di->get('cas')->get('temp_subject');
        $this->temp_sub->setReconnect(true);
        $this->temp_sub_var    = $di->get('cas')->get('temp_subject_variable');
        $this->temp_sub_var->setReconnect(true);
        $this->seo_tpl_var_svc = $di->get('cas')->get('seo_template_variable_service');
        $this->seo_tpl_var_svc->setReconnect(true);
        $this->place           = $di->get('cas')->get('product_pool_data');
        $this->place->setReconnect(true);
        $this->temp_zt2_block  = $di->get('cas')->get('pp_temp_zt2_block');
        $this->temp_zt2_block->setReconnect(true);
        $this->temp_zt2_data   = $di->get('cas')->get('pp_temp_zt2_data');
        $this->temp_zt2_data->setReconnect(true);
        $this->product         = $di->get('cas')->get('product_pool_product');
        $this->product->setReconnect(true);
        $this->goods           = $di->get('cas')->get('product_pool_goods');
        $this->goods->setReconnect(true);
        // kafka 推送
        $this->kafka = new \Lvmama\Cas\Component\Kafka\Producer(
            $di->get('config')->kafka->toArray()['msgProducer']
        );

    }

    /**
     * @see \Lvmama\Cas\Component\DaemonServiceInterface::process()
     */
    public function process($timestamp = null, $flag = null) {
        $this->datamove($flag);
    }

    /**
     * @see \Lvmama\Cas\Component\DaemonServiceInterface::shutdown()
     */
    public function shutdown($timestamp = null, $flag = null) {
        // nothing to do
    }

    /**
     *  block
     * @example sudo php ts.php subjectdata comment start 224 218135
     */
    private function getBlock(){
        //暑期产品第二波 223    测试
//        $data[] = array('sct'=>'224','vst'=>'218135');  //上海
//        $data[] = array('sct'=>'225','vst'=>'218194');  //北京
//        $data[] = array('sct'=>'228','vst'=>'218141');  //南京
//        $data[] = array('sct'=>'227','vst'=>'218138');  //杭州
//        $data[] = array('sct'=>'229','vst'=>'218195');  //天津
//        $data[] = array('sct'=>'230','vst'=>'218196');  //广州
//        $data[] = array('sct'=>'231','vst'=>'218197');  //深圳
//        $data[] = array('sct'=>'232','vst'=>'218198');  //成都
//        $data[] = array('sct'=>'233','vst'=>'218199');  //重庆
//        $data[] = array('sct'=>'234','vst'=>'218200');  //武汉
//        $data[] = array('sct'=>'226','vst'=>'218137');  //无锡


        //暑期产品第二波     线上 ---以上线
//        $data[] = array('sct'=>'329','vst'=>'218135');  //上海
//        $data[] = array('sct'=>'330','vst'=>'218194');  //北京
//        $data[] = array('sct'=>'331','vst'=>'218141');  //南京
//        $data[] = array('sct'=>'332','vst'=>'218138');  //杭州
//        $data[] = array('sct'=>'333','vst'=>'218195');  //天津
//        $data[] = array('sct'=>'335','vst'=>'218196');  //广州
//        $data[] = array('sct'=>'336','vst'=>'218197');  //深圳
//        $data[] = array('sct'=>'337','vst'=>'218198');  //成都
//        $data[] = array('sct'=>'338','vst'=>'218199');  //重庆
//        $data[] = array('sct'=>'339','vst'=>'218200');  //武汉
//        $data[] = array('sct'=>'340','vst'=>'218137');  //无锡

        //撒欢长隆     线上
        $data[] = array('sct'=>'517','vst'=>'220493');  //上海
        $data[] = array('sct'=>'518','vst'=>'220580');  //北京
        $data[] = array('sct'=>'520','vst'=>'220584');  //南京
        $data[] = array('sct'=>'519','vst'=>'220585');  //杭州

        // 三亚暑期旅游节    线上
        $data[] = array('sct'=>'556','vst'=>'217415');  //上海出发
        $data[] = array('sct'=>'557','vst'=>'217417');  //北京出发
        $data[] = array('sct'=>'558','vst'=>'217419');  //成都出发
        $data[] = array('sct'=>'559','vst'=>'217418');  //广州出发
        $data[] = array('sct'=>'560','vst'=>'217421');  //南京出发
        $data[] = array('sct'=>'561','vst'=>'217420');  //杭州出发
//        $data[] = array('sct'=>'562','vst'=>'');  //无锡出发
        $data[] = array('sct'=>'563','vst'=>'217422');  //天津出发
        $data[] = array('sct'=>'564','vst'=>'217456');  //重庆出发
        $data[] = array('sct'=>'565','vst'=>'217413');  //深圳出发
        $data[] = array('sct'=>'566','vst'=>'217424');  //武汉出发


        // 华北避暑    线上
        $data[] = array('sct'=>'493','vst'=>'220639');  //上海
        $data[] = array('sct'=>'494','vst'=>'220643');  //杭州
        $data[] = array('sct'=>'495','vst'=>'220644');  //南京
        $data[] = array('sct'=>'498','vst'=>'220645');  //无锡
        $data[] = array('sct'=>'499','vst'=>'220642');  //武汉
        $data[] = array('sct'=>'500','vst'=>'220647');  //广州
        $data[] = array('sct'=>'501','vst'=>'220646');  //深圳
        $data[] = array('sct'=>'502','vst'=>'220648');  //成都
        $data[] = array('sct'=>'503','vst'=>'220649');  //重庆
//        $data[] = array('sct'=>'','vst'=>'220641');  //天津
//        $data[] = array('sct'=>'','vst'=>'220640');  //北京

        // 劲爆周三    线上
        $data[] = array('sct'=>'535','vst'=>'207128');  //上海

        return $data?$data:array();
    }

    /**
     *数据迁移
     */
    public function datamove($flag){
        if(!empty($flag)){
            if (!preg_match("/^[0-9]+$/", $flag[0]) && !preg_match("/^[0-9]+$/", $flag[1])) {
                echo 0;exit;
            }else{
                $where['subject_id'] = "=" . $flag[0];
                $tempsubOne = $this->temp_sub->getDataOne($where);
                if(empty($tempsubOne)){
                    echo "sct not found";exit;
                }

                $wherezt2block['PARENT_RECOMMEND_BLOCK_ID'] = "=" . $flag[1];
                $wherezt2block['MODE_TYPE'] = "=3";
                $tempzt2block = $this->temp_zt2_block->getDataOne($wherezt2block);

                if(empty($tempzt2block)) {
                    echo "vst not found";exit;
                }
                $this->getTempSubjectOne(array('sct'=>$flag[0],'vst'=>$flag[1]));
                echo "success 1";exit;
            }
        }else{
            if(!empty($this->block)) {
                foreach($this->block as $row) {
                    $this->getTempSubjectOne($row);
                }
            }
            echo "success 2";exit;
        }
    }

    /**
     * 一级
     */
    private function getTempSubjectOne($block){
        $subjectId = $block['sct'];
        //table sj_template_subject
        $where['subject_id'] = "=" . $subjectId;
        $tempsubOne = $this->temp_sub->getDataOne($where);
        //table seo_template_variable
        if($tempsubOne){
            $tplvarcondition['template_id'] = "=".$tempsubOne['template_id'];
            $tplvarcondition['group_type'] = "='product'";
            $tplvarList = $this->seo_tpl_var_svc->getVarList($tplvarcondition);
        }
        //去重
        $midArr = $variablenameList = $seotemvarList = array();
        if($tplvarList) {
            foreach ($tplvarList as $key => $row) {
                $explode_var_name = explode("_", $row['variable_name']);
                array_pop($explode_var_name);
                $midArr[$key] = implode("_", $explode_var_name);
                $variablenameList[$row['variable_name']] = $row;
            }
        }
        $uniquemidArr = array_unique($midArr);  //去重
        if($tempsubOne) {
            echo (implode(',', $uniquemidArr)) . "\n\r";
        }else{
            echo ($subjectId ." not found") . "\n\r";
        }
        //对应名字
        if(!empty($uniquemidArr)) {
            foreach($uniquemidArr as $item){
                $tempsubNames  =$this->getName($variablenameList,$item,$subjectId);
                $seotemvarList = array_merge($seotemvarList,$tempsubNames);
                if(empty($tempsubNames)){
                    $tempsubNamesPublic  =$this->getName($variablenameList,$item);
                    $seotemvarList = array_merge($seotemvarList,$tempsubNamesPublic);
                }
            }
        }

        //导入数据
        if(!empty($seotemvarList)) {
            foreach($seotemvarList as $varkey=>$varrow){
                //2.0数据
                $zt2DataList = $this->getTempZt2Data($block['vst'],$varrow['variable_content']);
                //table pp_place
                $where_condition['key_id'] = "=".$subjectId;
                $where_condition['position'] = "=".$varrow['variable_id'];
                $placeList = $this->place->getList($where_condition,"pp_place");

                if(!empty($placeList) and !empty($zt2DataList)) {
                    foreach($placeList as $vark=>$varr) {
                        if($zt2DataList[$vark]){
                            $ppProuctId = $this->getProductId($zt2DataList[$vark]["RECOMM_OBJECT_ID"]);
                            if(!empty($ppProuctId)) {
                                $data['product_id']             = $ppProuctId;                                                  //产品ID
                                $data['supp_goods_id']          = ($zt2DataList[$vark]["BRANCH_TYPE"]=="BRANCH")?$zt2DataList[$vark]["SUPP_GOODS_ID"]:0; //供应商商品ID
                                $data['product_name']           = $zt2DataList[$vark]["TITLE"];                                 //产品显示名称
                                $data['product_img']            = $zt2DataList[$vark]["IMG_URL"];                               //产品显示图片
                                $data['product_tips']           = $zt2DataList[$vark]["BAK_WORD2"];                             //产品标签
                                $data['product_url']            = $zt2DataList[$vark]["URL"];                                   //产品URL
                                $data['product_promotionTitle'] = $zt2DataList[$vark]["BAK_WORD3"];                             //产品促销信息
                                $this->place->operateDataById("pp_place", $data, $varr['id']);
                                // kafka 推送
                                $this->kafka->sendMsg($ppProuctId);
                            }
                            echo "subjectId={$subjectId}--{$varrow['variable_content']}------place_product_id={$ppProuctId}------RECOMM_OBJECT_ID={$zt2DataList[$vark]["RECOMM_OBJECT_ID"]}\n\r";
                        }
                    }
                }
            }
        }


    }

    private function getName($variablenameList,$item,$subjectId='') {
        $seotemvarList =array();
        $implode_tabList =$item."_tabList";
        $implode_lcName  =$item."_lcName";
        $conditiontabList['variable_name'] = "='".$implode_tabList."'";
        if(!empty($subjectId)) $conditiontabList['subject_id'] = "=".$subjectId;
        $tempsubtabList = $this->temp_sub_var->getOneVar($conditiontabList);
        if(!empty($tempsubtabList)){
            $tabList = json_decode($tempsubtabList['variable_content'],true);
            foreach($tabList as $tabkey => $tabitem) {
                $variablenameList[$item.'_productData'.($tabkey+1)]['variable_content'] = $tabitem["name"];
                $seotemvarList[] = $variablenameList[$item.'_productData'.($tabkey+1)];
            }
        }else{
            $conditionlcName['variable_name'] = "='".$implode_lcName."'";
            if(!empty($subjectId)) $conditionlcName['subject_id'] = "=".$subjectId;
            $tempsublcName = $this->temp_sub_var->getOneVar($conditionlcName);
            if(!empty($tempsublcName)) {
                $lcName = json_decode($tempsublcName['variable_content'],true);
                $variablenameList[$item.'_productData1']['variable_content'] = $lcName["text"];
                $seotemvarList[] = $variablenameList[$item.'_productData1'];
            }
        }
        return $seotemvarList;
    }

    /*
     * 获得2.0 一个block 产品列表
     */
    private function getTempZt2Data($block,$name) {
        $wherezt2block['PARENT_RECOMMEND_BLOCK_ID'] = "=" . $block;
        $wherezt2block['MODE_TYPE'] = "=3";
        $wherezt2block['NAME'] = "='" . $name ."'";
        $tempzt2block = $this->temp_zt2_block->getDataOne($wherezt2block);
        if(!empty($tempzt2block)) {
            $wherezt2data['PARENT_RECOMMEND_BLOCK_ID'] = "=" . $block;
            $wherezt2data['RECOMMEND_BLOCK_ID'] = "=" . $tempzt2block['RECOMMEND_BLOCK_ID'];
            $tempzt2data = $this->temp_zt2_data->getDataList($wherezt2data,NULL,NULL,"SEQ_NUM DESC");
        }
        return $tempzt2data;
    }

    /*
     * 产品id
     */
    private function getProductId($productId) {
        $where['PRODUCT_ID'] = "=" . $productId;
        $productOne = $this->product->getOne($where,"pp_product");
        if(!empty($productOne)){
            $typeF = $this->productIdMap($productOne['CATEGORY_ID']);
            $placeproductId = str_pad($typeF,3,'0',STR_PAD_LEFT).str_pad($productId,10,'0',STR_PAD_LEFT);
        }
        return $placeproductId?$placeproductId:"";
    }

    private function productIdMap($id){
        $map = array(
            '11' => '5',
            '12' => '5',
            '13' => '5',
            '8' => '6',
            '9' => '7',
            '10' => '7',
            '15' => '14',
            '16' => '14',
            '17' => '14',
            '18' => '14',
            '29' => '14',
            '32' => '14',
            '42' => '14',
            '181' => '14',
            '182' => '14',
            '183' => '14',
        );
        return isset($map[$id])?$map[$id]:$id;
    }

}