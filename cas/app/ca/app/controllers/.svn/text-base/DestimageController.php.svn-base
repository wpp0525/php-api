<?php

use Lvmama\Common\Utils\Misc;
use Lvmama\Cas\Service\RedisDataService;

/**
 * 游记 控制器
 *
 * @author mac.zhao
 *
 */
class DestimageController extends ControllerBase {
    private $dest_image_svc;
    public function initialize() {
        parent::initialize();
        $this->dest_image_svc = $this->di->get('cas')->get('dest_image_service');
    }
}
