<?php
namespace ContentsMonitor\Service\Data;


/**
 * 監視サイトで使用するDBアクセス処理
 *
 * @package ContentsMonitor
 * @author  FST goto
 * @since   PHP 5.6, Zendframwork 2.4.9
 * @version 1.0.0
 */

class AbstractLocalAdapter 
{
	/**
	 * サービスロケーター
	 */
	protected $service_locator;

	/**
	 * 対象のテーブルアダプター
	 */
	protected $adapter;

	/**
	 * 対象のテーブルゲートウェイ（構造体）※使用しない
	 */
    protected $tableGateway;

	/**
	 * コンストラクタ
	 */
    public function __construct()
    {
    }

	/**
	 * サービスロケーターの設定
	 * @param  ServiceLocatorInterface $serviceLocator   サービスロケーターオブジェクト
	 */
    //protected function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    //{
    //    $this->service_locator = $serviceLocator;
    //}

	/**
	 * サービスロケーターを取得
	 * @return ServiceLocator $this->service_locator 	 サービスロケーターオブジェクト
	 */
    //protected function getServiceLocator()
    //{
    //    return $this->service_locator;
    //}

	/**
	 * テーブルゲートウェイを使用したデータ取得処理
	 * @return array  $resultSet   DBデータ
	 */
    public function fetchAll()
    {
        $resultSet = $this->tableGateway->select();
        return $resultSet;
    }

    /**
     * テーブルゲートウェイを使用したデータ登録処理
     */
    public function getLastInsertValue()
    {
    	return $this->tableGateway->getLastInsertValue();
    }
    

}