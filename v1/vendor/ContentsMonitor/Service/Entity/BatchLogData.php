<?php
namespace ContentsMonitor\Service\Entity;
 
/**
 * WK_BATCH_LOGテーブルのデータクラス
 *
 * @package ContentsMonitor
 * @author  FST goto
 * @since   PHP 5.6, Zendframwork 2.4.9
 * @version 1.0.0
 */
class BatchLogData
{
    public $batch_log_id;
    public $batch_id;
    public $import_type;
    public $trigger_type;
    public $start_ym;
    public $start_date;
    public $end_date;
    public $url;
    public $state;
    public $recovery_state;
    public $insert_date;

    public function exchangeArray($data)
    {
        $this->batch_log_id = (!empty($data['batch_log_id'])) ? $data['batch_log_id'] : null;
        $this->batch_id = (!empty($data['batch_id'])) ? $data['batch_id'] : null;
        $this->import_type = (!empty($data['import_type'])) ? $data['import_type'] : null;
        $this->trigger_type = (!empty($data['trigger_type'])) ? $data['trigger_type'] : null;
        $this->start_ym = (!empty($data['start_ym'])) ? $data['start_ym'] : null;
        $this->start_date = (!empty($data['start_date'])) ? $data['start_date'] : null;
        $this->end_date = (!empty($data['end_date'])) ? $data['end_date'] : null;
        $this->url = (!empty($data['url'])) ? $data['url'] : null;
        $this->state = (!empty($data['state'])) ? $data['state'] : null;
        $this->recovery_state = (!empty($data['recovery_state'])) ? $data['recovery_state'] : null;
        $this->insert_date = (!empty($data['insert_date'])) ? $data['insert_date'] : null;
    }
}