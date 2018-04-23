<?php

/**
 * kafka消息队列 Worker服务类
 *
 * @author libiying
 *
 */
class CanalUserWorkerService implements \Lvmama\Cas\Component\Kafka\ClientInterface
{
    /**
     * @var Lvmama\Cas\Service\SemUserDataService
     */
    private $user_srv = null;

    public function __construct($di)
    {

        $this->user_srv = $di->get('cas')->get('sem_user_service');
    }

    public function handle($data)
    {
        if (isset($data->payload) && isset($data->key)) {
            $keys      = explode('|', $data->key);
            $action    = $keys[0];
            $dbname    = $keys[1];
            $tablename = $keys[2];

            $columns = $this->user_srv->getColumns();

            $rDatas    = json_decode($data->payload, true);
            $rSize     = count($rDatas);
            $num       = 0;
            $batchSize = 20;
            $datas     = array();
            foreach ($rDatas as $p) {
                $num++;
                $cDatas = isset($p['cDatas']) ? $p['cDatas'] : null;
                $data   = array();

                //批量保存，不超过20条
                $this->user_srv->saveBatch($data);
                foreach ($cDatas as $d) {
                    if (in_array($d['name'], $columns)) {
                        $data[$d['name']] = $d['value'];
                    }
                }
                $datas[] = $data;
                if ($batchSize == count($datas) || $num == $rSize) {
                    $this->user_srv->saveBatch($datas);
                    echo date('Y-m-d H:i:s') . " $action:" . json_encode($datas) . "\n";
                    $datas = array();
                }

            }
        }

    }

    public function error()
    {
        // TODO: Implement error() method.
    }

    public function timeOut()
    {
        // TODO: Implement timeOut() method.
        echo "There is no messages from kafka,please wait.\n";
    }

}
