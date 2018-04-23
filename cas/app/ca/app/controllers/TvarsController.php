<?php

/**
 * 关键字存储
 *
 * @author dirc.wang
 *
 */
class TvarsController extends ControllerBase {

    private $seo_vartypenavigation_service;

    public function initialize() {
        parent::initialize();
        $this->seo_vartypenavigation_service = $this->di->get('cas')->get('seo_vartypenavigation_service');
    }

    public function getNavgationInfoAction(){
      if(!$this->request->getPost("keyword_id") || !$this->request->getPost("var_name"))
        return $this->jsonResponse(array(
          'code' => '4002',
          'msg' => 'var_name or  keyword_id 不存在'
        ));
      return $this->jsonResponse(array(
        'code' => '4000',
        'msg' => 'success',
        'result' => $this->seo_vartypenavigation_service->info(array(
                  'keyword_id' => $this->request->getPost("keyword_id"),
                  'var_name' => $this->request->getPost("var_name"),
                  'belong_id' => $this->request->getPost("belong_id", null, 0),
                ), array(), 0, false, 'sort'
              )
            )
        );
    }

    public function navgationMainaddAction(){
      if(!$this->request->getPost("keyword_id", "absint") || !$this->request->getPost("var_name") || !$this->request->getPost("name") || !$this->request->getPost("limit", "absint"))
        return $this->jsonResponse(array(
          'code' => '4002',
          'msg' => 'var_name or  keyword_id 错误'
        ));
      $info = $this->seo_vartypenavigation_service->info(array(
                'keyword_id' => $this->request->getPost("keyword_id"),
                'var_name' => $this->request->getPost("var_name"),
                'belong_id' => $this->request->getPost("belong_id", null, 0),
              ), array()
            );
      $sort = 1;
      if($info)
        $sort = count($info) + 1;
      $result = $this->seo_vartypenavigation_service->insert(array(
        'name' => $this->request->getPost("name"),
        'limit' => $this->request->getPost("limit"),
        'sort' => $sort,
        'keyword_id' => $this->request->getPost("keyword_id"),
        'var_name' => $this->request->getPost("var_name"),
        'belong_id' => $this->request->getPost("belong_id", null, 0),
      ));
      if($result === FALSE)
        return $this->jsonResponse(array(
          'code' => '4001',
          'msg' => 'add error',
        ));
      return $this->jsonResponse(array(
        'code' => '4000',
        'msg' => 'success',
        'result' => array(
          'id' => $result,
        )
      ));
    }

    /**
     * 删除单条数据
     * @return json delete information
     */
    public function navgationMaindelAction(){
      if(!$this->request->getPost("keyword_id", "absint") || !$this->request->getPost("var_name")|| !$this->request->getPost("id", 'absint'))
        return $this->jsonResponse(array(
          'code' => '4002',
          'msg' => 'var_name or  keyword_id or id 输入错误'
        ));
      if(!$this->seo_vartypenavigation_service->del(array(
        'keyword_id' => $this->request->getPost("keyword_id"),
        'var_name' => $this->request->getPost("var_name"),
        'belong_id' => $this->request->getPost("id"),
      )))
        return $this->jsonResponse(array(
          'code' => '4003',
          'msg' => '删除子导航错错误'
        ));
      if(!$this->seo_vartypenavigation_service->del(array(
        'keyword_id' => $this->request->getPost("keyword_id"),
        'var_name' => $this->request->getPost("var_name"),
        'id' => $this->request->getPost("id"),
      )))
        return $this->jsonResponse(array(
          'code' => '4003',
          'msg' => '删除导航错错误'
        ));
      return $this->jsonResponse(array(
        'code' => '4000',
        'msg' => '删除导航成功'
      ));
    }

    /**
     * 删除导航数据
     * @return json  delete infomation
     */
    public function navgationDelallAction(){
      if(!$this->request->getPost("keyword_id", "absint") || !$this->request->getPost("var_name"))
        return $this->jsonResponse(array(
          'code' => '4002',
          'msg' => 'var_name or  keyword_id 输入错误'
        ));
        $this->seo_vartypenavigation_service->del(array(
          'keyword_id' => $this->request->getPost("keyword_id"),
          'var_name' => $this->request->getPost("var_name")
        ));
        return $this->jsonResponse(array(
          'code' => '4000',
          'msg' => '删除导航成功'
        ));
    }

    public function navgationImportAction(){
      if(!$this->request->getPost("keyword_id", "absint") || !$this->request->getPost("var_name") || !$this->request->getPost("var_content"))
        return $this->jsonResponse(array(
          'code' => '4002',
          'msg' => 'var_name or  keyword_id or keyword_id 输入错误'
        ));
      $this->seo_vartypenavigation_service->del(array(
        'keyword_id' => $this->request->getPost("keyword_id"),
        'var_name' => $this->request->getPost("var_name")
      ));
      $var_content = json_decode($this->request->getPost("var_content"), true);
      foreach($var_content as $var=>$var_val ){
        $result = $this->seo_vartypenavigation_service->insert(array(
          'name' => isset($var_val['name']) ? $var_val['name'] : '',
          'limit' => isset($var_val['limit']) ? $var_val['limit'] : '',
          'sort' => intval($var + 1),
          'links' => (isset($var_val['links']) ? json_encode($var_val['links'], JSON_UNESCAPED_UNICODE) : ''),
          'keyword_id' => $this->request->getPost("keyword_id"),
          'var_name' => $this->request->getPost("var_name"),
          'belong_id' => 0,
        ));
        if($result && isset($var_val['S']) && is_array($var_val['S'])){
          foreach ($var_val['S'] as $key => $value) {
            $this->seo_vartypenavigation_service->insert(array(
              'name' => isset($value['name']) ? $value['name'] : '',
              'limit' => isset($value['limit']) ? $value['limit'] : '',
              'sort' => intval($key + 1),
              'links' => (isset($value['links']) ? json_encode($value['links'], JSON_UNESCAPED_UNICODE) : ''),
              'keyword_id' => $this->request->getPost("keyword_id"),
              'var_name' => $this->request->getPost("var_name"),
              'belong_id' => $result,
            ));
          }
        }
      }
      return $this->jsonResponse(array(
        'code' => '4000',
        'msg' => '导入成功'
      ));
    }

    public function navgationLinkinfoAction(){
      if(!$this->request->getPost("keyword_id", "absint") || !$this->request->getPost("var_name")|| !$this->request->getPost("id", 'absint'))
        return $this->jsonResponse(array(
          'code' => '4002',
          'msg' => 'var_name or  keyword_id or id 输入错误'
        ));
      $info = $this->seo_vartypenavigation_service->info(array(
                        'keyword_id' => $this->request->getPost("keyword_id"),
                        'var_name' => $this->request->getPost("var_name"),
                        'id' => $this->request->getPost("id"),
                      ), array('links')
                    );
      if($info && isset($info['0']) && array_key_exists("links", $info['0']))
        return $this->jsonResponse(array(
          'code' => '4000',
          'msg' => '获取成功',
          'result' => json_decode($info['0']['links'], true)
        ));
      return $this->jsonResponse(array(
        'code' => '4004',
        'msg' => '该链接组不存在',
      ));
    }

    public function navgationLinkeditAction(){
      if(!$this->request->getPost("keyword_id", "absint") || !$this->request->getPost("var_name")|| !$this->request->getPost("id", 'absint')|| !$this->request->getPost("links"))
        return $this->jsonResponse(array(
          'code' => '4002',
          'msg' => 'var_name or  keyword_id or id 输入错误'
        ));
      $result = $this->seo_vartypenavigation_service->update(array(
            'links' => $this->request->getPost("links")
          ),
          array(
            'keyword_id' => $this->request->getPost("keyword_id"),
            'var_name' => $this->request->getPost("var_name"),
            'id' => $this->request->getPost("id"),
          )
      );
      if($result)
        return $this->jsonResponse(array(
          'code' => '4000',
          'msg' => 'success'
        ));
      return $this->jsonResponse(array(
        'code' => '4005',
        'msg' => '修改链接组错误'
      ));
    }

    public function navgationResortAction(){
      if(!$this->request->getPost("keyword_id", "absint") || !$this->request->getPost("var_name")|| !$this->request->hasPost("belong_id") || !$this->request->hasPost("sort"))
        return $this->jsonResponse(array(
          'code' => '4002',
          'msg' => 'var_name or  keyword_id or id 输入错误'
        ));
      $sort = json_decode($this->request->getPost("sort"), true);
      if(is_array($sort)){
        $num = 1;
        //update sort
        foreach($sort as $x){
          $this->seo_vartypenavigation_service->update(array(
                'sort' => $num,
              ),
              array(
                'keyword_id' => $this->request->getPost("keyword_id"),
                'var_name' => $this->request->getPost("var_name"),
                'id' => $x,
              )
          );
          $num++;
        }
        return $this->jsonResponse(array(
          'code' => '4000',
          'msg' => 'resort success'
        ));
      }
      return $this->jsonResponse(array(
        'code' => '4002',
        'msg' => 'sort 输入错误'
      ));
    }

  /**
   * 获取导航数据
   * @return json 导航数据
   */
    public function navgationDataAction(){
      if(!$this->request->getPost("keyword_id") || !$this->request->getPost("var_name"))
        return $this->jsonResponse(array(
          'code' => '4002',
          'msg' => 'var_name or  keyword_id 输入错误'
        ));
      $minfo = $this->seo_vartypenavigation_service->info(array(
                    'keyword_id' => $this->request->getPost("keyword_id"),
                    'var_name' => $this->request->getPost("var_name"),
                    'belong_id' => 0,
                  ), array(), 0, false, 'sort'
                );
      $mids = array();
      foreach($minfo as $x){
        $mids[] = $x['id'];
      }
      $subinfo = $this->seo_vartypenavigation_service->getsubnav($mids);
      $sublist = array();
      foreach($subinfo as $xx){
        if(!isset($sublist[$xx['belong_id']])){
          $sublist[$xx['belong_id']] = array();
        }
        $links = json_decode($xx['links'], true);
        $xx['links'] = ($links) ? $links : array();
        array_push($sublist[$xx['belong_id']], $xx);
      }
      foreach($minfo as $xxx => $xxx_val){
        $links = json_decode($minfo[$xxx]['links'], true);
        $minfo[$xxx]['links'] = ($links) ? $links : array();
        $minfo[$xxx]['S'] = isset($sublist[$xxx_val['id']]) ? $sublist[$xxx_val['id']] : array();
      }
      return $this->jsonResponse(array(
        'code' => '4000',
        'msg' => 'success',
        'result' => $minfo,
      ));
    }
/**
 * 导航数据修改
 * @return array code and msg
 */
  public function navgationChangeAction(){
    if(!$this->request->getPost("keyword_id", "absint") || !$this->request->getPost("var_name")|| !$this->request->getPost("id", 'absint'))
      return $this->jsonResponse(array(
        'code' => '4002',
        'msg' => 'var_name or  keyword_id or id 输入错误'
      ));
      $result = $this->seo_vartypenavigation_service->update(array(
            'name' => $this->request->getPost("name"),
            'limit' =>  $this->request->getPost("limit"),
          ),
          array(
            'keyword_id' => $this->request->getPost("keyword_id"),
            'var_name' => $this->request->getPost("var_name"),
            'id' => $this->request->getPost("id"),
          )
      );
      if($result)
        return $this->jsonResponse(array(
          'code' => '4000',
          'msg' => 'success'
        ));
      return $this->jsonResponse(array(
        'code' => '4005',
        'msg' => '修改错误'
      ));
  }

}
