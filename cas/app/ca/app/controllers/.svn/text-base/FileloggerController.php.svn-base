<?php

/**
 * Created by PhpStorm.
 * User: jianghu
 */

use Phalcon\Logger\Adapter\File as FileAdapter,
    Phalcon\Logger\Formatter\Line as LineFormatter,
    Lvmama\Common\Utils\UCommon;

class FileloggerController extends ControllerBase
{
    private $_log_dir;

    public function initialize()
    {
        return parent::initialize();
    }

    //设置日志目录
    private function setLogDir($log_dir)
    {
        $this->_log_dir = $log_dir;
    }

    private function getFileAdapterObject($config = array())
    {
        $year = date("Y", time());
        $month = date("m", time());
        $day = date("d", time());
        $file_adapter = new FileAdapter($this->_log_dir . "/{$year}-{$month}-{$day}.log");
        if ($config)
            $file_adapter->setFormatter($this->getLineFormatterObject($config));
        return $file_adapter;
    }

    private function getLineFormatterObject($config = array())
    {
        $line_format = new LineFormatter();
        if ($config['message_format'])
            $line_format->setFormat($config['message_format']);
        if ($config['date_format'])
            $line_format->setDateFormat($config['date_format']);
        return $line_format;
    }

    private function getLogType($log_type)
    {
        switch (strtoupper($log_type)) {
            case 'INFO':
                $type = \Phalcon\Logger::INFO;
                break;
            case 'NOTICE':
                $type = \Phalcon\Logger::NOTICE;
                break;
            case 'WARNING':
                $type = \Phalcon\Logger::WARNING;
                break;
            case 'ERROR':
                $type = \Phalcon\Logger::ERROR;
                break;
            case 'ALERT':
                $type = \Phalcon\Logger::ALERT;
                break;
            default:
                $type = \Phalcon\Logger::DEBUG;
        }
        return $type;
    }

    public function addLogAction()
    {
        $log_level = $this->log_level;
        $message = $this->message;

        $type = $this->getLogType($log_level);
        $this->setLogDir('/phpCodeLog');
        $config = array(
            'message_format' => '[%date%] [%type%] %message%',
            'date_format' => 'Y-m-d H:i:s'
        );
        $file_adapter_obj = $this->getFileAdapterObject($config);

        $file_adapter_obj->log($message, $type);
        $file_adapter_obj->close();
    }

}