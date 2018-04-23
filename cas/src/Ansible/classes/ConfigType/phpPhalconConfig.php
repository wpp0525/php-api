<?php
/**
 * @author dirc.wang
 */

namespace Ansible\classes\ConfigType;

/**
 * Provides basic utility to config build the file system.
 *
 * @author dirc.wang
 */
class phpPhalconConfig extends ConfigTypeFormat
{

  protected function setTypeName(){
    return 'php.Phalcon';
  }

  public function setConfigData(Array $data = array()){
    $this->Config_Data = $data;
  }

  public function getConfigData(){
    return $this->Config_Data;
  }

/**
 * 将数据库数据格式化为文件数组
 * @param  array $tabledata 数据库列表数据
 * @return array            结果
 */
  public function tableDataToConfigData($tabledata){
    if(!is_array($tabledata) || count($tabledata) <= 0)
      return array();
    $list = array();
    $keyout = $this->getIdKeyAarray($tabledata);
    $this->buildConfigArray($list, 0, $keyout);
    return $list;
  }

/**
 * 递归生成configdata
 * @param  array  $list   修改的list
 * @param  ini $id     父类id
 * @param  array   $keyout 元数据
 * @return array          返回描述
 */
  public function buildConfigArray(&$list, $id = 0, Array $keyout = array()){
// 父类type_id
    $parent_type_id = -10;
    if($id != 0){
      if(isset($keyout[$id])){
        if(isset($keyout[$id]['type_id']) && $keyout[$id]['type_id'] !== null && $keyout[$id]['type_id'] !== '' ){
          $parent_type_id = $keyout[$id]['type_id'];
        }
      }
    }
    foreach ($keyout as $x => $x_val) {
      if(array_key_exists('params_parent_id', $x_val) && $x_val['params_parent_id'] == $id){
        $cfg_key = isset($x_val['cfg_key']) ? $x_val['cfg_key'] : '';
        $cfg_val = isset($x_val['cfg_val']) ? $x_val['cfg_val'] : '';
        $type_id = isset($x_val['type_id']) ? $x_val['type_id'] : '';
//如果parent_type_id=-10则当前为key-array(key=>val)数组
        if($parent_type_id == -10){
          if($type_id == -10 || $type_id == -11){
            $list[$cfg_key] = array();
            $this->buildConfigArray($list[$cfg_key], $x, $keyout);
          }
          if($type_id >= -1){
            $list[$cfg_key] = $cfg_val;
          }
        }
//如果parent_type_id=-10则当前为key-array(val)数组
        if($parent_type_id == -11){
          if($type_id == -10 || $type_id == -11){
            $new = array();
            $this->buildConfigArray($new, $x, $keyout);
            array_push($list, $new);
          }
          if($type_id >= -1){
            array_push($list, $cfg_val);
          }
        }
      }
    }
  }

  protected function replaceRedirectData(){
    $this->buildOutData();
    $this->replaceOutDataSignString();
    $this->replaceArrowSpace();
  }

  public function replaceOutDataString($old, $new){
    if(substr_count($old, '@@@') >= 2){
      if($new === '')
        $new = "''";
      $this->Config_OutData = str_replace($old, $new, $this->Config_OutData);
    }
  }

  public function replaceOutDataSignString(){
    preg_match_all("/\'@@@([^\n]*)@@@\'/", $this->Config_OutData, $Config, PREG_SET_ORDER);
    foreach($Config as $n){
//替换@@@标注的特殊字符
      if(isset($n['0']) && isset($n['1'])){
        $this->replaceOutDataString($n['0'], $this->replaceQuotation($n['1']));
      }
    }
  }

  public function replaceArrowSpace(){
    $this->Config_OutData = preg_replace('/=>(\s*)array/', "=> array", $this->Config_OutData);
  }

  public function buildOutData(){
    ob_start();
    echo "<?php\n";
    echo "return new \Phalcon\Config (";
    var_export($this->Config_Data);
    echo ");";
    $string = ob_get_contents();
    ob_end_clean();
    $this->Config_OutData = $string;
  }

/**
 * 将数据库数据数据格式化为前端数据
 * @param  array $tabledata 数据库数据
 * @return array            返回格式化数据
 */
  public function getformatTableData($tabledata){
    if(!is_array($tabledata) || count($tabledata) <= 0)
      return array();
    $list = array();
    $keyout = $this->getIdKeyAarray($tabledata);
    $this->buildCfgArray($list, 0, $keyout);
    return $list;
  }

  public function buildCfgArray(&$list, $id = 0, Array $keyout = array()){
    if($id != 0 && !isset($list['son'])){
      $list['son'] = array();
    }
    foreach ($keyout as $x => $x_val) {
      $son = array();
      if(array_key_exists('params_parent_id', $x_val) && $x_val['params_parent_id'] == $id){
        $son['data'] = $x_val;
        $this->buildCfgArray($son, $x, $keyout);
        if(array_key_exists('son', $list)){
          array_push($list['son'], $son);
        }else{
          array_push($list, $son);
        }
      }
    }
    return $list;
  }

  public function getIdKeyAarray($in){
    $keyout  = array();
    if(!is_array($in) || count($in) <= 0 || !isset($in['0']))
      return $keyout;
    foreach($in as $x){
      if(isset($x['id'])){
        $keyout[$x['id']] = $x;
      }
    }
    return $keyout;
  }

// **************************** 以下为生成页面html逻辑 *****************************************************
  public function getConfigOutHtmlCode($data, $cfg_id, $file_info){
    if(!is_array($data))
      return '';
    $change_status = '未修改';
    if($file_info['change_status'] == 2){
      $change_status = '已经修改';
    }
    $content = <<< EOF
<div class="dirc-codeinfo">
  <p>
    <b>最新版本:</b>&nbsp;&nbsp;<span name="cfg_last_version">{$file_info['cfg_last_version']}</span>&nbsp;&nbsp;&nbsp;&nbsp;
    <b>当前版本:</b>&nbsp;&nbsp;<span name="cfg_current_version">{$file_info['cfg_current_version']}</span>&nbsp;&nbsp;&nbsp;&nbsp;
    <b>文件状态:</b>&nbsp;&nbsp;<span name="change_status{$file_info['change_status']}">{$change_status}</span>&nbsp;&nbsp;&nbsp;&nbsp;
    <button class="pull-right btn btn-primary btn-sm button-updateversion" cfg-id="{$cfg_id}">更新版本</button>
  </p>
</div>
<div class="dirc-config-file  dirc-config-phalcon">
<div><span class="dirc-code-function">&lt;?php<span></div>
<div class="dirc-key-val" type-valid="-1" type-keyid="-1" type-id="-10" cfg-id="{$cfg_id}" param-id="0" cfg-type="phpPhalcon">
  <span class="dirc-code-flow">return</span>&nbsp;<span class="dirc-code-flow">new</span>&nbsp;<span class="dirc-code-object">\Phalcon\Config</span> (<span class="dirc-code-flow">array</span> (
  <i class="fa fa-plus-square"></i>
</div>
<div class="dirc-next-level">
EOF;
    $this->buildArrayHtml($content, $data, -10);
    $content .= '</div><div>));</div></div>';
    $content .= '<div class="dirc-codesubmit"></div>';
    // $content .= '<script>dircconfigedit.onload();</script>';
    return $content;
  }

  public function buildArrayHtml(&$content, $data, $parent_type_id = -10){
    foreach($data as $x => $x_val){
      if(array_key_exists('data', $x_val)){
        $type_id = isset($x_val['data']['type_id']) ? $x_val['data']['type_id'] : -10;
        $cfg_key = isset($x_val['data']['cfg_key']) ? $x_val['data']['cfg_key'] : '';
        $cfg_val = isset($x_val['data']['cfg_val']) ? $x_val['data']['cfg_val'] : '';
        $cfg_val_type = isset($x_val['data']['cfg_val_type']) ? $x_val['data']['cfg_val_type'] : '1';
        $cfg_val_show = $this->dealValType($cfg_val, $cfg_val_type);
// 以父类type_id=0确定当前数组能输出什么
        if($parent_type_id == -10){
// 以当前type_id确定输出当前
          if($type_id == -10 || $type_id == -11){
            $content .= '<div class="dirc-key-val" cfg-val-type="' . $cfg_val_type . '" type-valid="' . $x_val['data']['type_val_id'] . '" type-keyid="' . $x_val['data']['type_key_id'] . '" cfg-type="phpPhalcon" param-id="' . $x_val['data']['id'] . '" type-id="' . $x_val['data']['type_id'] . '" cfg-id="' . $x_val['data']['cfg_id'] . '" keyval="' . $cfg_key . '" cfg-val="' . $x_val['data']['cfg_val']  . '"><span class="dirc-code-param">' . "'" . $cfg_key . "'" . '</span>&nbsp;&nbsp;=&gt;&nbsp;&nbsp;<span class="dirc-code-flow">array</span> (<i class="fa fa-edit"></i><i class="fa fa-plus-square"></i><i class="fa fa-times-circle"></i></div>';
            if(isset($x_val['son']) && is_array($x_val['son']) && $x_val['son']){
              $content .= '<div class="dirc-next-level">';
              $this->buildArrayHtml($content, $x_val['son'], $type_id);
              $content .= '</div>';
            }
            $content .= '<div>),</div>';
          }
          if($type_id >= -1){
            $content .= '<div class="dirc-key-val" cfg-val-type="' . $cfg_val_type . '" type-valid="' . $x_val['data']['type_val_id'] . '" type-keyid="' . $x_val['data']['type_key_id'] . '" cfg-type="phpPhalcon" param-id="' . $x_val['data']['id'] . '" type-id="' . $x_val['data']['type_id'] . '"  cfg-id="' . $x_val['data']['cfg_id'] . '" keyval="' . $cfg_key . '" cfg-val="' . $x_val['data']['cfg_val'] . '"><span class="dirc-code-param">' . "'{$cfg_key}'" . '</span>&nbsp;&nbsp;=&gt;&nbsp;&nbsp;<span class="dirc-code-param' . $cfg_val_type . '">' .  $cfg_val_show . '</span>,<i class="fa fa-edit"></i><i class="fa fa-times-circle"></i></div>';
          }
        }
// 当$parent_type_id == -11 子队列只有val
        if($parent_type_id == -11){
          if($type_id == -10 || $type_id == -11){
            $content .= '<div class="dirc-key-val" cfg-val-type="' . $cfg_val_type . '" type-valid="' . $x_val['data']['type_val_id'] . '" type-keyid="' . $x_val['data']['type_key_id'] . '" cfg-type="phpPhalcon" param-id="' . $x_val['data']['id'] . '" type-id="' . $x_val['data']['type_id'] . '" cfg-id="' . $x_val['data']['cfg_id'] . '" keyval="' . $cfg_key . '" cfg-val="' . $x_val['data']['cfg_val']  . '"><span class="dirc-code-flow">array</span> (<i class="fa fa-edit"></i><i class="fa fa-plus-square"></i><i class="fa fa-times-circle"></i></div>';
            if(isset($x_val['son']) && is_array($x_val['son']) && $x_val['son']){
              $content .= '<div class="dirc-next-level">';
              $this->buildArrayHtml($content, $x_val['son'], $type_id);
              $content .= '</div>';
            }
            $content .= '<div>),</div>';
          }
          if($type_id >= -1){
            $content .= '<div class="dirc-key-val" cfg-val-type="' . $cfg_val_type . '" type-valid="' . $x_val['data']['type_val_id'] . '" type-keyid="' . $x_val['data']['type_key_id'] . '" cfg-type="phpPhalcon" param-id="' . $x_val['data']['id'] . '" type-id="' . $x_val['data']['type_id'] . '" cfg-id="' . $x_val['data']['cfg_id'] . '" keyval="' . $cfg_key . '" cfg-val="' . $x_val['data']['cfg_val']  . '"><span class="dirc-code-param' . $cfg_val_type . '">' .  $cfg_val_show . '</span>,<i class="fa fa-edit"></i><i class="fa fa-times-circle"></i></div>';
          }
        }

      }
    }
  }

/**
 * 生成typeoption
 * @param  array $data    当前节点的相关参数
 * @param  array $options 各种值的类型
 * @return string          option html 代码
 */
  public function buildTypeOption($data, $options){
    $types = isset($options['types']) ? $options['types'] : array();
    $type_id = isset($data['type_id']) ? $data['type_id'] : '';
    $content = '';
    if($type_id == -10){
      $content .= '<option value="-10" selected="selected">key => array(key=>val)</option>';
    }else{
      $content .= '<option value="-10" >key => array(key=>val)</option>';
    }

    if($type_id == -11){
      $content .= '<option value="-11" selected="selected">key => array(val)</option>';
    }else{
      $content .= '<option value="-11" >key => array(val)</option>';
    }

    if($type_id == -1){
      $content .= '<option value="-1" selected="selected">key => val</option>';
    }else{
      $content .= '<option value="-1" >key => val</option>';
    }

    foreach($types as $x){
      $sel = '';
      if($type_id == $x['id']){
        $sel = 'selected="selected"';
      }
      $content .= "<option {$sel} value='{$x['id']}' >{$x['type_name']}</option>";
    }
    return $content;
  }

/**
 * 生成类新keys选项
 * @param  array $data    当前节点相关参数
 * @param  array $options 类型输入参数
 * @param  array $change  更改传入c的参数
 * @return string          keys option html代码
 */
  public function buildTypeKeysOption($data, $options, &$change){
    $keys = isset($options['keys']) ? $options['keys'] : array();
    $type_key_id = isset($data['type_key_id']) ? $data['type_key_id'] : '';
    $content = '';
    if($type_key_id == -1){
      $content .= '<option value="-1" selected="selected">默认</option>';
    }else{
      $content .= '<option value="-1" >默认</option>';
    }
    foreach($keys as $x){
      $sel = '';
      if($type_key_id == $x['id']){
        $sel = 'selected="selected"';
        $change['key_change_type'] = $x['key_change_type'];
        $change['val_change_type'] = $x['val_change_type'];
      }
      $content .= "<option {$sel} value='{$x['id']}' type-key='{$x['type_key']}' key-ctype='{$x['key_change_type']}' val-ctype='{$x['val_change_type']}'>{$x['desc']}</option>";
    }
    return $content;
  }

/**
 * 建立修改valsoption
 * @param  array $data    输入相关参数
 * @param  array $options 参数表数据
 * @param  array $change  改变参数
 * @return string          输出option-html
 */
  public function buildTypeKeyValsOption($data, $options, $change){
    $content = '';
    $vals = isset($options['vals']) ? $options['vals'] : array();
    $type_key_id = isset($data['type_key_id']) ? $data['type_key_id'] : '';
    $type_val_id = isset($data['type_val_id']) ? $data['type_val_id'] : '';
    $parent_type_id = isset($data['parent_type_id']) ? $data['parent_type_id'] : '';
    $type_id = isset($data['type_id']) ? $data['type_id'] : '';
    $key_val = isset($data['key_val']) ? $data['key_val'] : '';
    $val_val = isset($data['val_val']) ? $data['val_val'] : '';
    $cfg_val_type = isset($data['cfg_val_type']) ? $data['cfg_val_type'] : '1';
// **************************************生成key部分 start*******************************************
    $keyshow = '';
    if($parent_type_id != -10){
      $keyshow = 'style="display:none"';
    }
    $disabled = '';
    if($change['key_change_type'] == 2){
      $disabled = 'disabled="disabled"';
    }
    $content .= <<<EOF
    <div class="col-xs-5 dirc-params-key" {$keyshow}>
      <input type="text" class="form-control" value="{$key_val}" name="key-val" key-id="{$type_key_id}" {$disabled}>
    </div>
    <div class="col-xs-1 dirc-vector-font" {$keyshow}>
      =&gt;
    </div>
EOF;
// ******************************************生成key html部分 end****************************************
// ******************************************生成val html 部分 start ************************************
// 判断当前类型
    if($type_id <-1){
      $content .= <<<EOF
      <div class="col-xs-5 dirc-params-vals">
        <span style="font-size:20px;font-weight:bold;line-height:34px">Array (</span>
      </div>
EOF;
    }else{
      $content .= <<<EOF
      <div class="col-xs-5 dirc-params-vals">
        <div class="btn-group dirc-edit-selectgroup dirc-val-selectgroup{$cfg_val_type}">
            <button type="button" class="dropdown-toggle dirc-edit-select" data-toggle="dropdown">
EOF;
      if($change['val_change_type'] == 2){
        $content .= '<span name="val_val" val-id="' . $type_val_id . '" val-type="' . $cfg_val_type . '" keyval="' . $val_val . '">' . $this->dealValType($val_val, $cfg_val_type, FALSE) . '</span><span class="caret pull-right" style="margin-top:15px"></span>';
      }else{
        $content .= '<span name="val_val" val-id="' . $type_val_id . '" val-type="' . $cfg_val_type . '"><input type="text" class="form-control" value="' . $this->dealValType($val_val, $cfg_val_type, FALSE) . '" placeholder="自定义"></span><span class="caret pull-right" style="margin-top:15px"></span>';
      }
      $content .= '</button><ul class="dropdown-menu" role="menu">';
      if($change['val_change_type'] != 2){
        $content .= '<li><a href="javascript:;" val-id="-1" val-type="1">自定义</a></li>';
        $content .= '<li><a href="javascript:;" val-id="-1" val-type="2">自定义的密码</a></li>';
        $content .= '<li><a href="javascript:;" val-id="-1" val-type="3">自定义的函数</a></li>';
      }
      foreach($vals as $v){
        $content .= '<li><a href="javascript:;" val-type="' . $v['cfg_val_type'] . '" val-id="' . $v['id'] . '" keyval="' . $v['type_key_val'] .'">' . $this->dealValType($v['type_key_val'], $v['cfg_val_type'], FALSE) . '</a></li>';
      }
      $content .= <<<EOF
      </ul>
  </div>
  <span class="help-block help-block-password">密码类型</span>
  <span class="help-block help-block-function">函数类型:填写的值若包括文本，请加双引号</span>
</div>
EOF;
    }
// ******************************************生成val html 部分 start ************************************
      return $content;
  }

/**
 * 添加参数节点弹出框
 * @param  array $data    输入参数
 * @param  array $options 数据库选项参数
 * @return html          添加框html代码
 */
  public function buildAddPopUpHtml($data, $options){
    $content = <<<EOF
<div class="modal fade in popup-addparams dirc-cfgedit-phalcon"  tabindex="-1" role="dialog" aria-labelledby="myModalLabel" style="display:block"
 p-params-id="{$data['params_parent_id']}" p_type_id="{$data['parent_type_id']}"
 type-valid="{$data['type_val_id']}" type-keyid="{$data['type_key_id']}" param-id="{$data['params_id']}" type-id="{$data['type_id']}" cfg-id="{$data['cfg_id']}" keyval="{$data['key_val']}" cfg-val="{$data['val_val']}"
>
  <div class="modal-dialog" style="width:800px;top:50px">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button"  class="close" data-dismiss="myModal" aria-hidden="true" onclick="closemodle(this)">
          <span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">添加参数节点</h4>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label>选择参数类型</label>
          <select class="form-control" name="type_id">
EOF;
    $content .= $this->buildTypeOption($data, $options);
    $content .= <<< EOF
        </select>
        <div class="form-group">
          <label>选择可用参数</label>
          <select class="form-control" name="type_key_id">
EOF;
    $change = array(
      'key_change_type' => 1,
      'val_change_type' => 1,
    );
    $content .= $this->buildTypeKeysOption($data, $options, $change);
    $content .= <<< EOF
        </select>
      </div>
      <div class="form-group">
        <label for="examplelinkname">参数详情</label>
        <div class="row dirc-params">
EOF;
      $content .= $this->buildTypeKeyValsOption($data, $options, $change);
      $content .= <<<EOF
      </div>
    </div>
  </div>
  <div class="modal-footer">
    <button type="button" class="btn btn-default pull-left" onclick="closemodletwo(this)">取消</button>
    <button type="button" class="btn btn-primary button-submit-add">提交</button>
  </div>
  </div>
</div>
</div>
EOF;
      return $content;
  }

// 建立配置文件弹出框html
  public function buildEditPopUpHtml($data, $options){
    $content = <<<EOF
<div class="modal fade in popup-addparams dirc-cfgedit-phalcon"  tabindex="-1" role="dialog" aria-labelledby="myModalLabel" style="display:block"
 p-params-id="{$data['params_parent_id']}" p_type_id="{$data['parent_type_id']}"
 type-valid="{$data['type_val_id']}" type-keyid="{$data['type_key_id']}" param-id="{$data['params_id']}" type-id="{$data['type_id']}" cfg-id="{$data['cfg_id']}" keyval="{$data['key_val']}" cfg-val="{$data['val_val']}"
>
  <div class="modal-dialog" style="width:800px;top:50px">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button"  class="close" data-dismiss="myModal" aria-hidden="true" onclick="closemodle(this)">
          <span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">编辑参数节点</h4>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label>选择参数类型</label>
          <select class="form-control" name="type_id">
EOF;
    $content .= $this->buildTypeOption($data, $options);
    $content .= <<< EOF
        </select>
        <div class="form-group">
          <label>选择可用参数</label>
          <select class="form-control" name="type_key_id">
EOF;
    $change = array(
      'key_change_type' => 1,
      'val_change_type' => 1,
    );
    $content .= $this->buildTypeKeysOption($data, $options, $change);
    $content .= <<< EOF
        </select>
      </div>
      <div class="form-group">
        <label for="examplelinkname">参数详情</label>
        <div class="row dirc-params">
EOF;
      $content .= $this->buildTypeKeyValsOption($data, $options, $change);
      $content .= <<<EOF
      </div>
    </div>
  </div>
  <div class="modal-footer">
    <button type="button" class="btn btn-default pull-left" onclick="closemodletwo(this)">取消</button>
    <button type="button" class="btn btn-primary button-submit">提交</button>
  </div>
  </div>
</div>
</div>
EOF;
      return $content;
  }

  public function buildPriviewHtml($data, $cfg_id, $file_info){
    $content = <<< EOF
<div class="dirc-config-file  dirc-config-phalcon">
<div><span class="dirc-code-function">&lt;?php<span></div>
<div class="dirc-key-val" type-valid="-1" type-keyid="-1" type-id="-10" cfg-id="{$cfg_id}" param-id="0" cfg-type="phpPhalcon">
  <span class="dirc-code-flow">return</span>&nbsp;<span class="dirc-code-flow">new</span>&nbsp;<span class="dirc-code-object">\Phalcon\Config</span> (<span class="dirc-code-flow">array</span> (
</div>
<div class="dirc-next-level">
EOF;
    $this->buildPriviewArrayHtml($content, $data, -10);
    $content .= '</div><div>));</div></div>';
    $content .= '<div class="dirc-codesubmit"></div>';
    return $content;
  }

  public function buildPriviewArrayHtml(&$content, $data, $parent_type_id = -10){
    foreach($data as $x => $x_val){
      if(array_key_exists('data', $x_val)){
        $type_id = isset($x_val['data']['type_id']) ? $x_val['data']['type_id'] : -10;
        $cfg_key = isset($x_val['data']['cfg_key']) ? $x_val['data']['cfg_key'] : '';
        $cfg_val = isset($x_val['data']['cfg_val']) ? $x_val['data']['cfg_val'] : '';
        $cfg_val_type = isset($x_val['data']['cfg_val_type']) ? $x_val['data']['cfg_val_type'] : '1';
        $cfg_val_show = $this->dealValType($cfg_val, $cfg_val_type);
// 以父类type_id=0确定当前数组能输出什么
        if($parent_type_id == -10){
// 以当前type_id确定输出当前
          if($type_id == -10 || $type_id == -11){
            $content .= '<div class="dirc-key-val" type-valid="' . $x_val['data']['type_val_id'] . '" type-keyid="' . $x_val['data']['type_key_id'] . '" cfg-type="phpPhalcon" param-id="' . $x_val['data']['id'] . '" type-id="' . $x_val['data']['type_id'] . '" cfg-id="' . $x_val['data']['cfg_id'] . '" keyval="' . $cfg_key . '" cfg-val="' . $x_val['data']['cfg_val']  . '"><span class="dirc-code-param">' . "'" . $cfg_key . "'" . '</span>&nbsp;&nbsp;=&gt;&nbsp;&nbsp;<span class="dirc-code-flow">array</span> (</div>';
            if(isset($x_val['son']) && is_array($x_val['son']) && $x_val['son']){
              $content .= '<div class="dirc-next-level">';
              $this->buildPreviewArrayHtml($content, $x_val['son'], $type_id);
              $content .= '</div>';
            }
            $content .= '<div>),</div>';
          }
          if($type_id >= -1){
            $content .= '<div class="dirc-key-val" type-valid="' . $x_val['data']['type_val_id'] . '" type-keyid="' . $x_val['data']['type_key_id'] . '" cfg-type="phpPhalcon" param-id="' . $x_val['data']['id'] . '" type-id="' . $x_val['data']['type_id'] . '"  cfg-id="' . $x_val['data']['cfg_id'] . '" keyval="' . $cfg_key . '" cfg-val="' . $x_val['data']['cfg_val'] . '"><span class="dirc-code-param">' . "'{$cfg_key}'" . '</span>&nbsp;&nbsp;=&gt;&nbsp;&nbsp;<span class="dirc-code-param' . $cfg_val_type . '">' .  $cfg_val_show . '</span>,</div>';
          }
        }
// 当$parent_type_id == -11 子队列只有val
        if($parent_type_id == -11){
          if($type_id == -10 || $type_id == -11){
            $content .= '<div class="dirc-key-val" type-valid="' . $x_val['data']['type_val_id'] . '" type-keyid="' . $x_val['data']['type_key_id'] . '" cfg-type="phpPhalcon" param-id="' . $x_val['data']['id'] . '" type-id="' . $x_val['data']['type_id'] . '" cfg-id="' . $x_val['data']['cfg_id'] . '" keyval="' . $cfg_key . '" cfg-val="' . $x_val['data']['cfg_val']  . '"><span class="dirc-code-flow">array</span> (</div>';
            if(isset($x_val['son']) && is_array($x_val['son']) && $x_val['son']){
              $content .= '<div class="dirc-next-level">';
              $this->buildPreviewArrayHtml($content, $x_val['son'], $type_id);
              $content .= '</div>';
            }
            $content .= '<div>),</div>';
          }
          if($type_id >= -1){
            $content .= '<div class="dirc-key-val" type-valid="' . $x_val['data']['type_val_id'] . '" type-keyid="' . $x_val['data']['type_key_id'] . '" cfg-type="phpPhalcon" param-id="' . $x_val['data']['id'] . '" type-id="' . $x_val['data']['type_id'] . '" cfg-id="' . $x_val['data']['cfg_id'] . '" keyval="' . $cfg_key . '" cfg-val="' . $x_val['data']['cfg_val']  . '"><span class="dirc-code-param' . $cfg_val_type . '">' .  $cfg_val_show . '</span>,</div>';
          }
        }

      }
    }
  }

  public function buildPreviewArrayHtml(&$content, $data, $parent_type_id = -10){
    foreach($data as $x => $x_val){
      if(array_key_exists('data', $x_val)){
        $type_id = isset($x_val['data']['type_id']) ? $x_val['data']['type_id'] : -10;
        $cfg_key = isset($x_val['data']['cfg_key']) ? $x_val['data']['cfg_key'] : '';
        $cfg_val = isset($x_val['data']['cfg_val']) ? $x_val['data']['cfg_val'] : '';
        $cfg_val_type = isset($x_val['data']['cfg_val_type']) ? $x_val['data']['cfg_val_type'] : '1';
        $cfg_val_show = $this->dealValType($cfg_val, $cfg_val_type);
// 以父类type_id=0确定当前数组能输出什么
        if($parent_type_id == -10){
// 以当前type_id确定输出当前
          if($type_id == -10 || $type_id == -11){
            $content .= '<div class="dirc-key-val" cfg-val-type="' . $cfg_val_type . '" type-valid="' . $x_val['data']['type_val_id'] . '" type-keyid="' . $x_val['data']['type_key_id'] . '" cfg-type="phpPhalcon" param-id="' . $x_val['data']['id'] . '" type-id="' . $x_val['data']['type_id'] . '" cfg-id="' . $x_val['data']['cfg_id'] . '" keyval="' . $cfg_key . '" cfg-val="' . $x_val['data']['cfg_val']  . '"><span class="dirc-code-param">' . "'" . $cfg_key . "'" . '</span>&nbsp;&nbsp;=&gt;&nbsp;&nbsp;<span class="dirc-code-flow">array</span> (</div>';
            if(isset($x_val['son']) && is_array($x_val['son']) && $x_val['son']){
              $content .= '<div class="dirc-next-level">';
              $this->buildPreviewArrayHtml($content, $x_val['son'], $type_id);
              $content .= '</div>';
            }
            $content .= '<div>),</div>';
          }
          if($type_id >= -1){
            $content .= '<div class="dirc-key-val" cfg-val-type="' . $cfg_val_type . '" type-valid="' . $x_val['data']['type_val_id'] . '" type-keyid="' . $x_val['data']['type_key_id'] . '" cfg-type="phpPhalcon" param-id="' . $x_val['data']['id'] . '" type-id="' . $x_val['data']['type_id'] . '"  cfg-id="' . $x_val['data']['cfg_id'] . '" keyval="' . $cfg_key . '" cfg-val="' . $x_val['data']['cfg_val'] . '"><span class="dirc-code-param">' . "'{$cfg_key}'" . '</span>&nbsp;&nbsp;=&gt;&nbsp;&nbsp;<span class="dirc-code-param' . $cfg_val_type . '">' .  $cfg_val_show . '</span>,</div>';
          }
        }
// 当$parent_type_id == -11 子队列只有val
        if($parent_type_id == -11){
          if($type_id == -10 || $type_id == -11){
            $content .= '<div class="dirc-key-val" cfg-val-type="' . $cfg_val_type . '" type-valid="' . $x_val['data']['type_val_id'] . '" type-keyid="' . $x_val['data']['type_key_id'] . '" cfg-type="phpPhalcon" param-id="' . $x_val['data']['id'] . '" type-id="' . $x_val['data']['type_id'] . '" cfg-id="' . $x_val['data']['cfg_id'] . '" keyval="' . $cfg_key . '" cfg-val="' . $x_val['data']['cfg_val']  . '"><span class="dirc-code-flow">array</span> (</div>';
            if(isset($x_val['son']) && is_array($x_val['son']) && $x_val['son']){
              $content .= '<div class="dirc-next-level">';
              $this->buildPreviewArrayHtml($content, $x_val['son'], $type_id);
              $content .= '</div>';
            }
            $content .= '<div>),</div>';
          }
          if($type_id >= -1){
            $content .= '<div class="dirc-key-val" cfg-val-type="' . $cfg_val_type . '" type-valid="' . $x_val['data']['type_val_id'] . '" type-keyid="' . $x_val['data']['type_key_id'] . '" cfg-type="phpPhalcon" param-id="' . $x_val['data']['id'] . '" type-id="' . $x_val['data']['type_id'] . '" cfg-id="' . $x_val['data']['cfg_id'] . '" keyval="' . $cfg_key . '" cfg-val="' . $x_val['data']['cfg_val']  . '"><span class="dirc-code-param' . $cfg_val_type . '">' .  $cfg_val_show . '</span>,</div>';
          }
        }

      }
    }
  }


}
