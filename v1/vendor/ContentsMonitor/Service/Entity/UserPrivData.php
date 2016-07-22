<?php
namespace ContentsMonitor\Service\Entity;
 
/**
 * MST_USERPRIVテーブルのデータクラス
 *
 * @package ContentsMonitor
 * @author  FST goto
 * @since   PHP 5.6, Zendframwork 2.4.9
 * @version 1.0.0
 */
class UserPrivData
{
    public $user_id;
    public $corner_id;
    public $authority;
    public $insert_date;
    public $update_date;
    
    public function exchangeArray($data)
    {
        $this->user_id     = (isset($data['user_id'])) ? $data['user_id'] : null;
        $this->corner_id   = (isset($data['corner_id'])) ? $data['corner_id'] : null;
        $this->authority   = (isset($data['authority'])) ? $data['authority'] : null;
        $this->insert_date = (isset($data['insert_date'])) ? $data['insert_date'] : null;
        $this->update_date = (isset($data['update_date'])) ? $data['update_date'] : null;
    }
}