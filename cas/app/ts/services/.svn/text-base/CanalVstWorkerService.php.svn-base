<?php
use Lvmama\Common\Components\ApiClient;

/**
 * kafka消息队列 Worker服务类
 *
 * @author libiying
 *
 */
class CanalVstWorkerService implements \Lvmama\Cas\Component\Kafka\ClientInterface
{
    public function __construct($di)
    {
        $es                 = $di->get('config')->get('elasticsearch');
        $this->host         = $es->host;
        $this->port         = $es->port;
        $log                = $di->get('config')->get('db2es');
        $this->log_index    = $log->index;
        $this->log_mappings = $log->mappings;
        $this->log_type     = $log->type;
        $this->client       = new ApiClient('http://' . $this->host . ':' . $this->port);
    }

    public function handle($data)
    {
        // TODO: Implement handle() method.
        if (isset($data->err) && isset($data->payload) && isset($data->key)) {
            $keys   = explode('|', $data->key);
            $action = $keys[0];
            $index  = $keys[1];
            $type   = $keys[2];
            $rs     = $this->parsePayLoad($data->payload);
            switch ($type) {
                case 'biz_dest':
                    $id = $rs['dest_id'];
                    break;
                case 'biz_district':
                    $id = $rs['district_id'];
                    break;
                case 'biz_district_sign':
                    $id = $rs['sign_id'];
                    break;
                case 'biz_com_coordinate':
                    $id = $rs['coord_id'];
                    break;
                default:
                    foreach ($rs as $k => $v) {
                        $id = $v;
                        break;
                    }
            }
            switch ($action) {
                case 'UPDATE':
                    $this->update($index, $type, $id, json_encode($rs, JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK));
                    break;
                case 'INSERT':
                    $this->add($index, $type, $id, json_encode($rs, JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK));
                    break;
                case 'DELETE':
                    $this->delete($index, $type, $id);
                    break;
                default:
                    //...
            }
            $this->writeLog(array(
                'dbname'     => $index,
                'table'      => $type,
                'topic_name' => $data->topic_name,
                'message'    => '[' . $data->key . ']' . $data->payload,
            ));
        }
    }
    private function parsePayLoad($data)
    {
        $rs     = json_decode($data, true);
        $return = array();
        foreach ($rs[0]['cDatas'] as $k => $v) {
            $return[$v['name']] = $v['value'];
        }
        return $return;
    }
    private function update($index, $type, $id, $content)
    {
        $this->client->external_exec(
            'http://' . $this->host . ':' . $this->port . '/' . $index . '/' . $type . '/' . $id,
            $content,
            array(),
            'POST'
        );
    }
    private function delete($index, $type, $id)
    {
        $this->client->external_exec(
            'http://' . $this->host . ':' . $this->port . '/' . $index . '/' . $type . '/' . $id,
            '',
            array(),
            'DELETE'
        );
    }
    private function add($index, $type, $id, $content)
    {
        $this->client->external_exec(
            'http://' . $this->host . ':' . $this->port . '/' . $index . '/' . $type . '/' . $id,
            $content,
            array(),
            'POST'
        );
    }
    //把日志信息存储起来
    private function writeLog($data = array())
    {
        $tmp         = array();
        $index_names = array();
        $res         = $this->client->external_exec('http://' . $this->host . ':' . $this->port . '/_cat/indices/' . $this->log_index . '*');
        foreach (explode("\n", $res) as $v) {
            if (trim($v)) {
                $tmp           = explode(' ', $v);
                $index_names[] = $tmp[2];
            }
        }
        //不存在则创建
        if (!in_array($this->log_index, $index_names)) {
            $this->client->external_exec(
                'http://' . $this->host . ':' . $this->port . '/' . $this->log_index,
                $this->log_mappings,
                array(),
                'PUT'
            );
        }
        $data['createtime'] = date('Y-m-d H:i:s');
        $data['dbname']     = isset($data['dbname']) ? $data['dbname'] : '';
        $data['topic_name'] = isset($data['topic_name']) ? $data['topic_name'] : '';
        $data['table']      = isset($data['table']) ? $data['table'] : '';
        $data['message']    = isset($data['message']) ? $data['message'] : '';
        $this->client->external_exec(
            'http://' . $this->host . ':' . $this->port . '/' . $this->log_index . '/' . $this->log_type,
            json_encode($data, JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE),
            array(),
            'POST'
        );
    }

    public function error()
    {
        // TODO: Implement error() method.
    }

    public function timeOut()
    {
        // TODO: Implement timeOut() method.
    }

}
