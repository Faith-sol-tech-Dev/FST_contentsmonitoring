<?php
namespace ContentsMonitor\Service\Entity;
 
/**
 * WK_BATCH_LOG_CONTENTSテーブルのデータクラス
 *
 * @package ContentsMonitor
 * @author  FST goto
 * @since   PHP 5.6, Zendframwork 2.4.9
 * @version 1.0.0
 */
class BatchLogContentData
{
    public $batch_transaction_id;
    public $batch_log_id;

    public function exchangeArray($data)
    {
        $this->batch_transaction_id = (!empty($data['batch_transaction_id'])) ? $data['batch_transaction_id'] : null;
        $this->batch_log_id = (!empty($data['batch_log_id'])) ? $data['batch_log_id'] : null;
    }
}