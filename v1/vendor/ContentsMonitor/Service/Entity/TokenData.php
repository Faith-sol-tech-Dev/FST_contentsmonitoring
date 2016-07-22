<?php
namespace ContentsMonitor\Service\Entity;
 
class TokenData
{
    public $login_id;
    public $index_key;
    public $token;
    public $token_expire;
    public $insert_date;
 
    public function exchangeArray( $data )
    {
        $this->login_id      = (isset($data['login_id']))     ? $data['login_id'] : null;
        $this->index_key     = (isset($data['index_key']))    ? $data['index_key'] : null;
        $this->token         = (isset($data['token']))        ? $data['token'] : null;
        $this->token_expire  = (isset($data['token_expire'])) ? $data['token_expire'] : null;
        $this->insert_date   = (isset($data['insert_date']))  ? $data['insert_date'] : null;
    }
}