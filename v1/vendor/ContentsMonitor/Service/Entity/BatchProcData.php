<?php
namespace ContentsMonitor\Service\Entity;
 
/**
 * WK_BATCH_PROCテーブルのデータクラス
 *
 * @package ContentsMonitor
 * @author  FST goto
 * @since   PHP 5.6, Zendframwork 2.4.9
 * @version 1.0.0
 */
class BatchProcData
{
    public $batch_id;
    public $state;

    public function exchangeArray($data)
    {
        $this->batch_id = (!empty($data['batch_id'])) ? $data['batch_id'] : null;
        $this->state = (!empty($data['state'])) ? $data['state'] : null;
    }
}