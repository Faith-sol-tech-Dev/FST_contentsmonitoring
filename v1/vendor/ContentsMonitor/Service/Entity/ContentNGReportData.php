<?php
namespace ContentsMonitor\Service\Entity;
 
/**
 * TRN_CONTENTS_NG_REPORTテーブルのデータクラス
 *
 * @package ContentsMonitor
 * @author  FST goto
 * @since   PHP 5.6, Zendframwork 2.4.9
 * @version 1.0.0
 */
class ContentNGReportData
{
    public $no;
    public $contents_id;
    public $contents_type;
    public $contents_inner_id;
    public $monitor_user_id;
    public $monitor_date;
    public $access_token;
    public $monitoring;
    public $insert_date;
    
    public function exchangeArray($data)
    {
        $this->no					= (isset($data['no'])) ? $data['no'] : null;
        $this->contents_id			= (isset($data['contents_id'])) ? $data['contents_id'] : null;
        $this->contents_type		= (isset($data['contents_type'])) ? $data['contents_type'] : null;
        $this->contents_inner_id	= (isset($data['contents_inner_id'])) ? $data['contents_inner_id'] : null;
        $this->monitor_user_id		= (isset($data['monitor_user_id'])) ? $data['monitor_user_id'] : null;
        $this->monitor_date			= (isset($data['monitor_date'])) ? $data['monitor_date'] : null;
        $this->access_token			= (isset($data['access_token'])) ? $data['access_token'] : null;
        $this->monitoring			= (isset($data['monitoring'])) ? $data['monitoring'] : null;
        $this->insert_date			= (isset($data['insert_date'])) ? $data['insert_date'] : null;
    }
}