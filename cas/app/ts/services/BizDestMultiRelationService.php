<?php 

use Lvmama\Cas\Component\DaemonServiceInterface;
use Lvmama\Cas\Service\MsgDataService;
use Lvmama\Common\Components\ApiClient;

/**
* 目的地多级关系任务
* 时时将biz_dest中的数据同步到biz_dest_multi_relation
* @author gaocz
*/
class BizDestMultiRelationService implements \Lvmama\Cas\Component\Kafka\ClientInterface
{
	public $result = array(
        'dest_id' => 0,
        'parent_id' => 0,
        'district_type' => '',
        'continent_id' => 0,
        'country_id' => 0,
        'province_id' => 0,
        'city_id' => 0,
        'county_id' => 0,
        'town_id' => 0,
        'span_city_id' => 0,
        'span_country_id' => 0,
        'span_province_id' => 0
    );

	/**
     * 递归目的地类型
     * @var array
     */
    public $dest_type_list = array(
        "CITY",
        "CONTINENT",
        "COUNTRY",
        "PROVINCE",
        "SPAN_CITY",
        "SPAN_COUNTRY",
        "SPAN_PROVINCE",
        "COUNTY",
        "TOWN",
    );

    /*
     * 基础库
     * @var \Lvmama\Cas\Service\DestinBaseDataService
     */
    private $distin_base;
	
	/**
	 * @var \Lvmama\Cas\Service\DestinBaseMultiRelationDataService
	 */
	private $biz_dest_multi_relation;
	
	function __construct($di)
	{
		$this->distin_base = $di->get('cas')->get('destin_base_service');

		$this->biz_dest_multi_relation = $di->get('cas')->get('destin_multi_relation_base_service');
	}

	public function handle($data)
	{
		if(isset($data->err) && isset($data->payload)){
			
			$payload = json_decode($data->payload, true);
			var_dump($payload);
			$this->result['dest_id'] = $payload['dest_id'];
            $this->result['parent_id'] = $payload['parent_id'];
            $this->result['district_type'] = $payload['dest_type'];

            // 避免重复数据
            $check_data = array();
            $check_data['dest_id'] = $payload['dest_id'];

            $result_format = $this->formatQuery($check_data);
            $tmp = $this->biz_dest_multi_relation->getOneDest($result_format);

			$this->getParent($payload);
			if ( empty($tmp) ) {
                $this->biz_dest_multi_relation->insert($this->result);
            }
		}
	}

	/**
     * 递归查询上级节点信息
     * @param $data
     * @return array
     */
    private function getParent($data)
    {
        $parent_id = $data['parent_id'];

        $data = $this->distin_base->getOneById($parent_id);

        if ( empty($data) ) {
            return $this->result;
        } else {
            if ( in_array( $data['dest_type'],$this->dest_type_list ) ) {
                // 数据库列和字段值
                $column_name = strtolower($data['dest_type']) . '_id';
                $column_id = $data['dest_id'];

                $this->result[$column_name] = $column_id;
            }

            $this->getParent($data);

        }

    }

    /**
     * format where条件
     * @param $arr
     * @return array
     */
    private function formatQuery($arr)
    {
        $result = array();

        if ( is_array($arr) ) {
            foreach ( $arr as $key=>$value ) {
                $result[$key] =  " = '$value'";
            }
        } else {
            $result = $arr;
        }

        return $result;
    }

	public function error()
	{
		// TODO: Implement error() method.
	}

	public function timeOut()
	{
		// TODO: Implement timeOut() method.
		echo 'time out!';
	}

	/**
	 * @see \Lvmama\Cas\Component\DaemonServiceInterface::shutdown()
	 */
	public function shutdown($timestamp = null, $flag = null) {
		//关闭时收尾任务
	}
}