<?php

use Lvmama\Cas\Component\DaemonServiceInterface;

class Subject1DataWorkerService implements DaemonServiceInterface{
    private $temp_sub;
    private $temp_sub_var;
    private $seo_tpl_var_svc;
    private $place;
    private $temp_zt1_block;
    private $temp_zt1_data;
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
        $this->temp_zt1_block  = $di->get('cas')->get('pp_temp_zt1_block');
        $this->temp_zt1_block->setReconnect(true);
        $this->temp_zt1_data   = $di->get('cas')->get('pp_temp_zt1_data');
        $this->temp_zt1_data->setReconnect(true);
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
     * @example sudo php ts.php subject1data comment start 224 218135
     */
    private function getBlock(){
        // 测试
        $data[] = array('sct'=>'340','vst'=>'202660');  //特卖会香港

        //线上

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

                $tempzt1block_sql="SELECT * FROM temp_zt1_block WHERE FATHER_ID = {$flag[1]} AND TEMPLATE_ID IN (4,41)";
                $tempzt1block = $this->temp_zt1_data->query($tempzt1block_sql);

                if(empty($tempzt1block)) {
                    echo "vst not found";exit;
                }
                $this->getTempSubjectOne(array('sct'=>$flag[0],'vst'=>$flag[1]));
                echo "success 1";exit;
            }
        }else{//print_r($this->getTempZt1Data("202660","酒景更自由"));exit;
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
        echo (implode(',', $uniquemidArr))."\n\r";
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
                //1.0数据
                $zt1DataList = $this->getTempZt1Data($block['vst'],$varrow['variable_content']);
                //table pp_place
                $where_condition['key_id'] = "=".$subjectId;
                $where_condition['position'] = "=".$varrow['variable_id'];
                $placeList = $this->place->getList($where_condition,"pp_place");

                if(!empty($placeList) and !empty($zt1DataList)) {
                    foreach($placeList as $vark=>$varr) {
                        if($zt1DataList[$vark]){
                            $ppProuctId = $this->getProductId($zt1DataList[$vark]["OBJECT_ID"]);
                            if(!empty($ppProuctId)) {
                                $data['product_id']             = $ppProuctId;                                                  //产品ID
                                $data['supp_goods_id']          = ($zt1DataList[$vark]["BRANCH_TYPE"]=="BRANCH")?$zt1DataList[$vark]["SUPP_GOODS_ID"]:0; //供应商商品ID
                                $data['product_name']           = $zt1DataList[$vark]["TITLE"];                                 //产品显示名称
                                $data['product_img']            = $zt1DataList[$vark]["IMG_URL"];                               //产品显示图片
                                $data['product_url']            = $zt1DataList[$vark]["URL"];                                   //产品URL
                                $this->place->operateDataById("pp_place", $data, $varr['id']);
                                // kafka 推送
                                $this->kafka->sendMsg($ppProuctId);
                            }
                            echo "subjectId={$subjectId}--{$varrow['variable_content']}------place_product_id={$ppProuctId}------RECOMM_OBJECT_ID={$zt1DataList[$vark]["OBJECT_ID"]}\n\r";
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
    * 获得1.0 一个block 产品列表
    */
    private function getTempZt1Data($block,$name) {
        $tempzt1block_sql="SELECT * FROM temp_zt1_block WHERE FATHER_ID = {$block} AND TEMPLATE_ID IN (4,41) AND TOPIC_NAME= '{$name}'";
        $tempzt1block = $this->temp_zt1_data->query($tempzt1block_sql);
        if(!empty($tempzt1block)) {
            $wherezt1data['TOPIC_MANAGER_ID'] = "=" . $tempzt1block['TOPIC_ID'];
            $tempzt1data = $this->temp_zt1_data->getDataList($wherezt1data,NULL,NULL,"SORTVALUE DESC");
        }
        return $tempzt1data;
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