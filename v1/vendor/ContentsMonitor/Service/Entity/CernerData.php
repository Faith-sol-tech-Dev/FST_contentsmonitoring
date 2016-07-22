<?php
namespace ContentsMonitor\Service\Entity;

/**
 * MST_CORNERテーブルのデータクラス
 *
 * @package ContentsMonitor
 * @author  FST goto
 * @since   PHP 5.6, Zendframwork 2.4.9
 * @version 1.0.0
 */ 
class CernerData
{

	public $corner_id;
    public $corner_name;
    public $corner_url;
    public $url_regx;
    
    public function exchangeArray($data)
    {
        $this->corner_id   = (isset($data['corner_id'])) ? $data['corner_id'] : null;
        $this->corner_name = (isset($data['corner_name'])) ? $data['corner_name'] : null;
        $this->corner_url  = (isset($data['corner_url'])) ? $data['corner_url'] : null;
        $this->url_regx   = (isset($data['url_regx'])) ? $data['url_regx'] : null;
    }
}