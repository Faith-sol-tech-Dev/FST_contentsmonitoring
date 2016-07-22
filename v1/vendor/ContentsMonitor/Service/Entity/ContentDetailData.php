<?php
namespace ContentsMonitor\Service\Entity;
 
/**
 * TRN_CONTENTS_DETAILテーブルのデータクラス
 *
 * @package ContentsMonitor
 * @author  FST goto
 * @since   PHP 5.6, Zendframwork 2.4.9
 * @version 1.0.0
 */
class ContentDetailData
{
    public $contents_id;
    public $contents_type;
    public $contents_inner_id;
    public $contents_parent_id;
    public $sub_id;
    public $url;
    public $format;
    public $comment;
    public $title;
    public $caption;
    public $user;
    public $create_date;
    public $check_state;
    public $check_result;
    public $check_note;
    public $check_user;
    public $check_date;
    public $lock_user;
    public $lock_date;
    public $insert_user;
    public $insert_date;
    public $update_user;
    public $update_date;
    
    public function exchangeArray($data)
    {
        $this->contents_id           = (isset($data['contents_id'])) ? $data['contents_id'] : null;
        $this->contents_type         = (isset($data['contents_type'])) ? $data['contents_type'] : null;
        $this->contents_inner_id     = (isset($data['contents_inner_id'])) ? $data['contents_inner_id'] : null;
        $this->contents_parent_id = (!empty($data['contents_parent_id'])) ? $data['contents_parent_id'] : null;
        $this->sub_id                = (isset($data['sub_id'])) ? $data['sub_id'] : null;
        $this->url                   = (isset($data['url'])) ? $data['url'] : null;
        $this->format                = (isset($data['format'])) ? $data['format'] : null;
        $this->comment               = (isset($data['comment'])) ? $data['comment'] : null;
        $this->title                 = (isset($data['title'])) ? $data['title'] : null;
        $this->caption               = (isset($data['caption'])) ? $data['caption'] : null;
        $this->user                  = (isset($data['user'])) ? $data['user'] : null;
        $this->create_date           = (isset($data['create_date'])) ? $data['create_date'] : null;
        $this->check_state           = (isset($data['check_state'])) ? $data['check_state'] : null;
        $this->check_result          = (isset($data['check_result'])) ? $data['check_result'] : null;
        $this->check_note            = (isset($data['check_note'])) ? $data['check_note'] : null;
        $this->check_user            = (isset($data['check_user'])) ? $data['check_user'] : null;
        $this->check_date            = (isset($data['check_date'])) ? $data['check_date'] : null;
        $this->lock_user             = (isset($data['lock_user'])) ? $data['lock_user'] : null;
        $this->lock_date             = (isset($data['lock_date'])) ? $data['lock_date'] : null;
        $this->insert_user           = (isset($data['insert_user'])) ? $data['insert_user'] : null;
        $this->insert_date           = (isset($data['insert_date'])) ? $data['insert_date'] : null;
        $this->update_user           = (isset($data['update_user'])) ? $data['update_user'] : null;
        $this->update_date           = (isset($data['update_date'])) ? $data['update_date'] : null;
    }
}