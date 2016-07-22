<?php
namespace ContentsMonitor\Service\Entity;
 
/**
 * MST_API_ERRORテーブルのデータクラス
 *
 * @package ContentsMonitor
 * @author  FST goto
 * @since   PHP 5.6, Zendframwork 2.4.9
 * @version 1.0.0
 */
class ApiErrorData
{
    public $no;
    public $service_id;
    public $error_code;
    public $error_message;
    public $insert_date;
    public $update_date;

    public function exchangeArray($data)
    {
        $this->no = (!empty($data['no'])) ? $data['no'] : null;
        $this->service_id = (!empty($data['service_id'])) ? $data['service_id'] : null;
        $this->error_code = (!empty($data['error_code'])) ? $data['error_code'] : null;
        $this->error_message = (!empty($data['error_message'])) ? $data['error_message'] : null;
        $this->insert_date = (!empty($data['insert_date'])) ? $data['insert_date'] : null;
        $this->update_date = (!empty($data['update_date'])) ? $data['update_date'] : null;
    }
}
