<?php
namespace ContentsMonitor\Service\Entity;
 
/**
 * WK_BATCH_LOG_CONTENTS_DETAILテーブルのデータクラス
 *
 * @package ContentsMonitor
 * @author  FST goto
 * @since   PHP 5.6, Zendframwork 2.4.9
 * @version 1.0.0
 */
class BatchLogContentDetailData
{
    public $batch_contents_id;
    public $batch_transaction_id;
    public $contents_type;
    public $contents_id;
    public $url;
    public $format;
    public $comment;
    public $title;
    public $caption;
    public $user;
    public $create_date;
    public $import_date;
    public $import_state;
    public $error_reason;
    public $recovery_state;
    public $recovery_date;
    public $recovery_user;
    public $insert_date;
    public $update_date;

    public function exchangeArray($data)
    {
        $this->batch_contents_id = (!empty($data['batch_contents_id'])) ? $data['batch_contents_id'] : null;
        $this->batch_transaction_id = (!empty($data['batch_transaction_id'])) ? $data['batch_transaction_id'] : null;
        $this->contents_type = (!empty($data['contents_type'])) ? $data['contents_type'] : null;
        $this->contents_id = (!empty($data['contents_id'])) ? $data['contents_id'] : null;
        $this->url = (!empty($data['url'])) ? $data['url'] : null;
        $this->format = (!empty($data['format'])) ? $data['format'] : null;
        $this->comment = (!empty($data['comment'])) ? $data['comment'] : null;
        $this->title = (!empty($data['title'])) ? $data['title'] : null;
        $this->caption = (!empty($data['caption'])) ? $data['caption'] : null;
        $this->user = (!empty($data['user'])) ? $data['user'] : null;
        $this->create_date = (!empty($data['create_date'])) ? $data['create_date'] : null;
        $this->import_date = (!empty($data['import_date'])) ? $data['import_date'] : null;
        $this->import_state = (!empty($data['import_state'])) ? $data['import_state'] : null;
        $this->error_reason = (!empty($data['error_reason'])) ? $data['error_reason'] : null;
        $this->recovery_state = (!empty($data['recovery_state'])) ? $data['recovery_state'] : null;
        $this->recovery_date = (!empty($data['recovery_date'])) ? $data['recovery_date'] : null;
        $this->recovery_user = (!empty($data['recovery_user'])) ? $data['recovery_user'] : null;
        $this->insert_date = (!empty($data['insert_date'])) ? $data['insert_date'] : null;
        $this->update_date = (!empty($data['update_date'])) ? $data['update_date'] : null;
    }
}