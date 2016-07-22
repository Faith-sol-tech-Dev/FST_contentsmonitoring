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
class BatchDetailData
{
    public $batch_id;
    public $contents_id;
    public $movie_url;
    public $image_url;
    public $comment;
    public $title;
    public $name;
    public $create_date;
    public $service_id;
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
        $this->batch_id				= (isset($data['batch_id'])) ? $data['batch_id'] : null;
        $this->contents_id			= (isset($data['contents_id'])) ? $data['contents_id'] : null;
        $this->movie_url			= (isset($data['movie_url'])) ? $data['movie_url'] : null;
        $this->image_url			= (isset($data['image_url'])) ? $data['image_url'] : null;
        $this->comment				= (isset($data['comment'])) ? $data['comment'] : null;
        $this->title				= (isset($data['title'])) ? $data['title'] : null;
        $this->name					= (isset($data['name'])) ? $data['name'] : null;
        $this->create_date			= (isset($data['create_date'])) ? $data['create_date'] : null;
        $this->service_id			= (isset($data['service_id'])) ? $data['service_id'] : null;
        $this->import_date			= (isset($data['import_date'])) ? $data['import_date'] : null;
        $this->import_state			= (isset($data['import_state'])) ? $data['import_state'] : null;
        $this->error_reason			= (isset($data['error_reason'])) ? $data['error_reason'] : null;
        $this->recovery_state		= (isset($data['recovery_state'])) ? $data['recovery_state'] : null;
        $this->recovery_state		= (isset($data['recovery_state'])) ? $data['recovery_state'] : null;
        $this->recovery_user		= (isset($data['recovery_user'])) ? $data['recovery_user'] : null;
        $this->insert_date			= (isset($data['insert_date'])) ? $data['insert_date'] : null;
        $this->update_date			= (isset($data['update_date'])) ? $data['update_date'] : null;
    }
}