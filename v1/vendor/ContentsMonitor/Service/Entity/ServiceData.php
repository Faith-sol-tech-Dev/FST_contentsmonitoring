<?php
namespace ContentsMonitor\Service\Entity;
 
/**
 * MST_SERVICEテーブルのデータクラス
 *
 * @package ContentsMonitor
 * @author  FST goto
 * @since   PHP 5.6, Zendframwork 2.4.9
 * @version 1.0.0
 */
class ServiceData
{
    
    public $service_id;
    public $service_name;
    public $service_site_url;
    public $site_auth_user;
    public $site_auth_pass;
    public $monitoring_start_date;
    public $monitoring_end_date;
    public $site_administrator;
    public $site_administrator_mail;
    public $import_type;
    public $api_url;
    public $api_key;
    public $api_param;
    public $api_status;
    public $api_type;
    public $api_auth_flag;
    public $api_auth_user;
    public $api_auth_pass;
    public $crawler_flag;
    public $crawler_url1;
    public $crawler_tag1;
    public $crawler_url2;
    public $crawler_tag2;
    public $csv_url;
    public $csv_param;
    public $ng_char_group;
    public $notes;
    public $maintenance_flag;
    public $insert_date;
    public $update_date;
    
    public function exchangeArray($data)
    {
        $this->service_id            = (isset($data['service_id'])) ? $data['service_id'] : null;
        $this->service_name          = (isset($data['service_name'])) ? $data['service_name'] : null;
        $this->service_site_url      = (isset($data['service_site_url'])) ? $data['service_site_url'] : null;
        $this->site_auth_user        = (isset($data['site_auth_user'])) ? $data['site_auth_user'] : null;
        $this->site_auth_pass        = (isset($data['site_auth_pass'])) ? $data['site_auth_pass'] : null;
        $this->monitoring_start_date = (isset($data['monitoring_start_date'])) ? $data['monitoring_start_date'] : null;
        $this->monitoring_end_date   = (isset($data['monitoring_end_date'])) ? $data['monitoring_end_date'] : null;
        $this->site_administrator    = (isset($data['site_administrator'])) ? $data['site_administrator'] : null;
        $this->site_administrator_mail = (isset($data['site_administrator_mail'])) ? $data['site_administrator_mail'] : null;
        $this->import_type           = (isset($data['import_type'])) ? $data['import_type'] : null;
        $this->api_url               = (isset($data['api_url'])) ? $data['api_url'] : null;
        $this->api_key               = (isset($data['api_key'])) ? $data['api_key'] : null;
        $this->api_param             = (isset($data['api_param'])) ? $data['api_param'] : null;
        $this->api_status            = (isset($data['api_status'])) ? $data['api_status'] : null;
        $this->api_type              = (isset($data['api_type'])) ? $data['api_type'] : null;
        $this->api_auth_flag         = (isset($data['api_auth_flag'])) ? $data['api_auth_flag'] : null;
        $this->api_auth_user         = (isset($data['api_auth_user'])) ? $data['api_auth_user'] : null;
        $this->api_auth_pass         = (isset($data['api_auth_pass'])) ? $data['api_auth_pass'] : null;
        $this->crawler_flag          = (isset($data['crawler_flag'])) ? $data['crawler_flag'] : null;
        $this->crawler_url1          = (isset($data['crawler_url1'])) ? $data['crawler_url1'] : null;
        $this->crawler_tag1          = (isset($data['crawler_tag1'])) ? $data['crawler_tag1'] : null;
        $this->crawler_url2          = (isset($data['crawler_url2'])) ? $data['crawler_url2'] : null;
        $this->crawler_tag2          = (isset($data['crawler_tag2'])) ? $data['crawler_tag2'] : null;
        $this->csv_url               = (isset($data['csv_url'])) ? $data['csv_url'] : null;
        $this->csv_param             = (isset($data['csv_param'])) ? $data['csv_param'] : null;
        $this->ng_char_group         = (isset($data['ng_char_group'])) ? $data['ng_char_group'] : null;
        $this->notes                 = (isset($data['notes'])) ? $data['notes'] : null;
        $this->maintenance_flag      = (isset($data['maintenance_flag'])) ? $data['maintenance_flag'] : null;
        $this->insert_date           = (isset($data['insert_date'])) ? $data['insert_date'] : null;
        $this->update_date           = (isset($data['update_date'])) ? $data['update_date'] : null;
    }
}