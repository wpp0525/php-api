<?php 
use Phalcon\CLI\Task;
use Lvmama\Common\Components\Daemon;

/**
* 有关行政区脚本
*/
class BizdistTask extends Task{


    /**
     * @var \Lvmama\Cas\Service\DistBaseDataService
     */
    private $dist_base;

    /**
     *
     * @see \Phalcon\DI\Injectable::setDI()
     */
    public function setDI(Phalcon\DiInterface $dependencyInjector) {
        parent::setDI ( $dependencyInjector );
        $this->dist_base = $dependencyInjector->get('cas')->get('dist_base_service');
    }

    /**
     * @example php ../ts.php Bizdist buildDistCode
     */
    public function buildDistCodeAction() {
        ini_set('memory_limit', '256M');

        $file_path = APPLICATION_PATH . "/logs/dist/ChinaDistrictCODE.txt";
        if(file_exists($file_path)){
            $arr =  file($file_path);

            $dist_code = array();
            $const = array("cancel_flag = " => "'Y'", "foreign_flag = " => "'N'");
            foreach ($arr as $a){
                $cn = explode(',', str_replace("\r\n", "", $a));
                $dist_code[$cn[0]] = $cn[1];
            }
            foreach ($dist_code as $code => $dname){
                $name = $dname;
                $params = array("district_name = " => "'" . $name . "'");
                $dist = $this->dist_base->getDistList(array_merge($params, $const));
                if(!$dist) {
                    $name = substr($name, 0, strlen($name) - 3);
                    $params = array("district_name = " => "'" . $name . "'");
                    $dist = $this->dist_base->getDistList(array_merge($params, $const));

                    if(!$dist && $name != ''){
                        $params = array("district_name like " => "'" . $name . "%'");
                        $dist = $this->dist_base->getDistList(array_merge($params, $const), 5);

                    }
                    if(!$dist && $name != ''){
                        $params = array("city_name like " => "'" . $name . "%'");
                        $dist = $this->dist_base->getDistList(array_merge($params, $const), 1);
                    }
                    if(!$dist && $name != ''){
                        $params = array("province_name like " => "'" . $name . "%'");
                        $dist = $this->dist_base->getDistList(array_merge($params, $const), 1);
                    }
                }

                if($dist && count($dist) > 1){
                    $fst = substr($code, 0, 2);
                    $sed = substr($code, 2, 2);
                    $thr = substr($code, 4, 2);
                    if($thr != '00'){
                        $parent = $this->dist_base->getOneDist(array_merge(array("district_code = " => "'" . $fst . $sed  . "00'"), $const));
                        if(!$parent){
                            $parent = $this->dist_base->getOneDist(array_merge(array("district_code = " => "'" . $fst . "0000'"), $const));
                        }
                        $params = array(
                            "district_name = " => "'" . $name . "'",
                            "parent_id = " => $parent ? $parent['district_id'] : "",
                        );
                        $dist = $this->dist_base->getDistList(array_merge($params, $const));
                        if(!$dist && $name != ''){
                            $params = array(
                                "district_name like " => "'" . $name . "%'",
                                "parent_id = " => $parent ? $parent['district_id'] : "",
                            );
                            $dist = $this->dist_base->getDistList(array_merge($params, $const));
                        }
                    }
                }

                if($dist && count($dist) == 1){
                    $id = $dist[0]['district_id'];
                    $data = array(
                        "district_code" => $code,
                        "district_name2" => $dname
                    );
                    $this->dist_base->update($id, $data);
                    echo "success: $code, $dname \n";
                }else{
                    echo "fail: $code, $dname \n";
                }
            }

        }

    }
}
