<?php
namespace ContentsMonitor\Service\Entity;
 
/**
 * WK_BATCHテーブルのデータクラス
 *
 * @package ContentsMonitor
 * @author  FST goto
 * @since   PHP 5.6, Zendframwork 2.4.9
 * @version 1.0.0
 */
class BatchData
{
    public $batch_id;
    public $service_id;
    public $batch_type;
    public $batch_name;
    public $insert_user;
    public $insert_date;
    public $update_user;
    public $update_date;

    public function exchangeArray($data)
    {
        $this->batch_id = (!empty($data['batch_id'])) ? $data['batch_id'] : null;
        $this->service_id = (!empty($data['service_id'])) ? $data['service_id'] : null;
        $this->batch_type = (!empty($data['batch_type'])) ? $data['batch_type'] : null;
        $this->batch_name = (!empty($data['batch_name'])) ? $data['batch_name'] : null;
        $this->insert_user = (!empty($data['insert_user'])) ? $data['insert_user'] : null;
        $this->insert_date = (!empty($data['insert_date'])) ? $data['insert_date'] : null;
        $this->update_user = (!empty($data['update_user'])) ? $data['update_user'] : null;
        $this->update_date = (!empty($data['update_date'])) ? $data['update_date'] : null;
    }
}