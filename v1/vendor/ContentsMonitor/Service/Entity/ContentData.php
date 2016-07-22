<?php
namespace ContentsMonitor\Service\Entity;
 
/**
 * TRN_CONTENTSテーブルのデータクラス
 *
 * @package ContentsMonitor
 * @author  FST goto
 * @since   PHP 5.6, Zendframwork 2.4.9
 * @version 1.0.0
 */
class ContentData
{
    public $contents_id;
    public $contents_type;
    public $service_id;
    public $monitoring_start_date;
    public $monitoring_end_date;
    public $import_type;
    public $import_ym;
    public $import_date;
    
    public function exchangeArray($data)
    {
        $this->contents_id           = (isset($data['contents_id'])) ? $data['contents_id'] : null;
        $this->contents_type         = (isset($data['contents_type'])) ? $data['contents_type'] : null;
        $this->service_id            = (isset($data['service_id'])) ? $data['service_id'] : null;
        $this->monitoring_start_date = (isset($data['monitoring_start_date'])) ? $data['monitoring_start_date'] : null;
        $this->monitoring_end_date   = (isset($data['monitoring_end_date'])) ? $data['monitoring_end_date'] : null;
        $this->import_type           = (isset($data['import_type'])) ? $data['import_type'] : null;
        $this->import_ym             = (isset($data['import_ym'])) ? $data['import_ym'] : null;
        $this->import_date           = (isset($data['import_date'])) ? $data['import_date'] : null;
    }
}