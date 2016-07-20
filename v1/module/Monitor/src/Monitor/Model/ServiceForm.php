<?php
namespace Monitor\Model;

use Zend\Form\Form;

use ContentsMonitor\Service\Data\ServiceTable as ServiceTable;
use ContentsMonitor\Service\Entity\ServiceData;
use ContentsMonitor\Common\LogClass as Log;
use ContentsMonitor\Common\UtilityClass as Utility;
use ContentsMonitor\Exception\DbAccessExceptionClass as DbAccessException;

/**
 * サービス情報フォーム
 *
 * @package ContentsMonitor
 * @author  FST goto
 * @since   PHP 5.6, Zendframwork 2.4.9
 * @version 1.0.0
 */
class ServiceForm extends Form
{
	/**
	 * サービスロケーター
	 * @var $service_locator
	 */
	protected $service_locator;
	
	/**
	 * サービス情報へのアクセスクラス
	 * @var $serviceTable
	 */
	protected $serviceTable;
	
	/**
	 * 監視対象のサービスID
	 * @var $current_id
	 */
	public $current_id = '';
	
	/**
	 *　監視対象のサービス名称
	 * @var $current_name
	 */
	public $current_name = '';

	/**
	 * 監視対象のサービス情報
	 * @var $service_data
	 */
	public $service_data;

	/**
	 * 現在監視中のサービス情報リスト
	 * @var $service_list
	 */
	public $service_list = array();
	
    /**
     * コンストラクタ
     * @param unknown $service_locator サービスロケーター
     */
    public function __construct( $service_locator )
    {
    	$this->getServiceLocator = $service_locator;
    	$this->serviceTable = $this->getServiceLocator->get('ContentsMonitor\Service\Data\ServiceTable');
    	parent::__construct();
    }
    
    /**
     * 画面に表示する項目を追加
     * @param unknown $aryElm
     */
    protected function addElement( $aryElm )
    {
    	$this->add( $aryElm );
    }
    
    /**
     * 画面に表示する項目の属性を追加（サービスリストの属性を追加）
     * @param unknown $value
     */
    protected function addAttribute( $value )
    {
    	$this->service_list = $value;
    }
    
    /**
     * 監視対象のサービス情報をセット
     * @param unknown $servicedata
     */
    protected function setCurrentService( $servicedata )
    {
    	//現在設定しているサービス情報
    	$this->current_id = $servicedata->service_id;
    	$this->current_name = $servicedata->service_name;
    	$this->service_data = $servicedata;
    }
    
    /**
     * 現在使用可能なサービス情報をリストで取得。サービスリスト項目に格納。
     */
    public function getServiceListData()
    {
    	$start_time=microtime(true);
    	
    	try {
	    	//サービスリストを取得
	    	$prvRet = $this->serviceTable->fetchAll();
	    	$prvRet->buffer();
	    	$arySvc = array();
	    	$arySvc = array("0"=>"");
	    	foreach ( $prvRet as $item ) {
	    		$arySvc += array( (string)$item->service_id => $item->service_name );
	    	}
		   	// サービスリスト内容をセット
		   	$this->addAttribute($arySvc);
    	}
    	catch( \DbAccessException $de ) {
    		log::debug(__FILE__, __LINE__, 'ERROR getServiceListData() --  get Service Data failed.');
    		log::error(__FILE__, __LINE__, $de->getMessage());
    		throw $de;
    	}
    	catch( \Exception $e ) {
    		log::debug(__FILE__, __LINE__, 'ERROR getServiceListData() --  get Service Data failed.');
    		log::error(__FILE__, __LINE__, $e->getMessage(), true);
    		throw new DbAccessException($e->getMessage());
    	}

    	$diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
    	Log::debug(__FILE__, __LINE__, 'INFO   getServiceListData() --  has completed. ('.$diff_time.')');
    }
    
    /**
     * 現在監視対象のサービス詳細情報を取得。監視対象サービス項目に格納。
     * @param string $service_cd サービスID
     */
    public function getServiceData( $service_id )
    {
    	$start_time=microtime(true);

    	$prvRet = $this->serviceTable->getService( $service_id );
    	$this->setCurrentService($prvRet);
    	
    	$diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
    	Log::debug(__FILE__, __LINE__, 'INFO   getServiceData() --  has completed. ('.$diff_time.')');
    }

    /**
     * ユーザに紐づくサービス情報を取得する。
     * @param string $user_id ユーザID
     */
    public function getServiceToUserData( $user_id )
    {
    	$start_time=microtime(true);
    
    	try {
	    	$aryData = $this->serviceTable->getUserRelation( array('user_id' => $user_id) );
		    $arySvc = array();
	    	foreach ( $aryData as $item ) {
	    		$arySvc += array( (string)$item['service_id'] => $item['service_name'] );
	    	}
	    	// サービスリスト内容をセット
	    	$this->addAttribute($arySvc);
    	}
    	catch( \DbAccessException $de ) {
    		log::debug(__FILE__, __LINE__, 'ERROR getServiceListData() --  get Service Data failed.');
    		log::error(__FILE__, __LINE__, $de->getMessage());
    		throw $de;
    	}
    	catch( \Exception $e ) {
    		log::debug(__FILE__, __LINE__, 'ERROR getServiceListData() --  get Service Data failed.');
    		log::error(__FILE__, __LINE__, $e->getMessage(), true);
    		throw new DbAccessException($e->getMessage());
    	}
    	 
    	$diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
    	Log::debug(__FILE__, __LINE__, 'INFO   getServiceData() --  has completed. ('.$diff_time.')');
    }
    
}
