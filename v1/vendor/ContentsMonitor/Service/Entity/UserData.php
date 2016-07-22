<?php
namespace ContentsMonitor\Service\Entity;
 
class UserData
{
    public $user_id;
    public $login_id;
    public $user_name;
 
    public function exchangeArray($data)
    {
        $this->user_id     = (isset($data['user_id'])) ? $data['user_id'] : null;
        $this->login_id = (isset($data['login_id'])) ? $data['login_id'] : null;
        $this->user_name  = (isset($data['user_name'])) ? $data['user_name'] : null;
    }
}