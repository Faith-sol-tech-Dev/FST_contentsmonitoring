<?php
namespace ContentsMonitor\Service\Data;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\AdapterAwareInterface;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Exception\ErrorException;

use ContentsMonitor\Service\Entity\ServiceData;
use ContentsMonitor\Common\LogClass as Log;
use ContentsMonitor\Common\UtilityClass as Utility;
use ContentsMonitor\Exception\DbAccessExceptionClass as DbAccessException;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * 監視サイトで使用するDBアクセス処理（MST_SERVICE）
 *
 * @package ContentsMonitor
 * @author  FST goto
 * @since   PHP 5.6, Zendframwork 2.4.9
 * @version 1.0.0
 */
class ServiceTable extends AbstractLocalAdapter implements AdapterAwareInterface
{

	/**
	 * DBアクセス用のモジュールの設定（Adapter, TableGateway）
	 * 
	 * @param  Adapter $adapter   DBアダプタオブジェクト
	 */
    public function setDbAdapter(Adapter $adapter)
    {
        $resultSetPrototype = new ResultSet();
        $resultSetPrototype->setArrayObjectPrototype(new ServiceData());
        $this->tableGateway = new TableGateway('MST_SERVICE', $adapter, null, $resultSetPrototype);
        $this->adapter = $adapter;
    }
    
	/**
	 * サービス情報を取得
	 * 
	 * @param  int $id   サービスID
	 * @return     $row  MST_SERVICEテーブル情報
	 */
    public function getService($id)
    {
    	$start_time=microtime(true);
    	
    	$row;
    	try {
	    	$id  = (int) $id;

	    	Log::query(sprintf('SQL::getService() param:id=%s',$id));
	    	
	        $rowset = $this->tableGateway->select(array('service_id' => $id));
	        $row = $rowset->current();
	        if (!$row) {
	            //throw new \DbAccessException("Could not find row $id");
	        }
    	}
    	catch( \ErrorException $ee ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getService() --  do not select from MST_SERVICE Table.');
    		Log::error(__FILE__, __LINE__, $ee->getMessage());
    		throw new DbAccessException($ee->getMessage());
    	}
    	catch( \PDOException $pe ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getService() --  do not select from MST_SERVICE Table.');
    		Log::error(__FILE__, __LINE__, $pe->getMessage());
    		throw new DbAccessException($pe->getMessage());
    	}
    	catch ( Exception $e ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getService() --  do not select from MST_SERVICE Table.');
    		Log::error(__FILE__, __LINE__, $e->getMessage());
    		throw new DbAccessException($e->getMessage());
    	}
    	
    	$diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
    	Log::debug(__FILE__, __LINE__, 'INFO   getService() --  select from MST_SERVICE Table. get Service Data. ('.$diff_time.')');
        return $row;
    }
    
    /**
     * サービス情報を取得(APIキーを指定)
     * 
     * @param int $api_key (APIキー情報)
     * @return ArrayObject|NULL
     */
    public function getMstServiceByApiKey($api_key)
    {
    	$start_time=microtime(true);
    	
    	$row;
    	try {
    		Log::query(sprintf('SQL::getMstServiceByApiKey() param:api_key=%s',$api_key));
    		
	    	$rowset = $this->tableGateway->select(array('api_key' => $api_key));
	    	$row = $rowset->current();
	    	if (!$row) {
	    		throw new \DbAccessException("Could not find row $api_key");
	    	}
    	}
    	catch( \ErrorException $ee ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getMstServiceByApiKey() --  do not select from MST_SERVICE Table.');
    		Log::error(__FILE__, __LINE__, $ee->getMessage());
    		throw new DbAccessException($ee->getMessage());
    	}
    	catch( \PDOException $pe ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getMstServiceByApiKey() --  do not select from MST_SERVICE Table.');
    		Log::error(__FILE__, __LINE__, $pe->getMessage());
    		throw new DbAccessException($pe->getMessage());
    	}
    	catch ( Exception $e ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getMstServiceByApiKey() --  do not select from MST_SERVICE Table.');
    		Log::error(__FILE__, __LINE__, $e->getMessage());
    		throw new DbAccessException($e->getMessage());
    	}
    	 
    	$diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
    	Log::debug(__FILE__, __LINE__, 'INFO   getMstServiceByApiKey() --  select from MST_SERVICE Table. get Service Data. ('.$diff_time.')');
    	return $row;
    }
    
    /**
     * ユーザ情報に紐づくサービス関連情報
     *
     * @param  int $id   ユーザID
     * @return     $row  MST_SERVICE_RELテーブル情報
     */
    public function getUserRelation($param)
    {
    	$start_time=microtime(true);
    
    	$aryData = array();
    	try {
    
    		$sql =  'SELECT srl.user_id, srl.service_id, srv.service_name '
    				.' FROM MST_USER AS us '
 					.' INNER JOIN MST_SERVICE_REL AS srl '
 					.'    ON us.user_id = srl.user_id '
 					.' INNER JOIN MST_SERVICE AS srv '
 					.'    ON srv.service_id = srl.service_id '
 					.' WHERE us.user_id = :user_id ';
    
    		$params = array( 'user_id'   => $param['user_id'], );
    
    		Log::query(sprintf('SQL::getUserRelation() query=%s',$sql));
    		Log::query(sprintf('SQL::getUserRelation() param:user_id=%s',$param['user_id']));
    
    		$stmt = $this->adapter->createStatement($sql);
    		$results = $stmt->execute($params);
    
    		if ($results instanceof ResultInterface && $results->isQueryResult()) {
    			$resultSet = new ResultSet;
    			$resultSet->initialize($results);
    			foreach ($resultSet as $row) {
    				if($resultSet->count() > 1) {
	    				array_push($aryData, $row);
    				} else {
    					$aryData[] = $row;
    				}
    			}
    		}
    	}
    	catch( \ErrorException $ee ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getUserRelation() --  do not select from MST_SERVICE_REL Table.');
    		Log::error(__FILE__, __LINE__, $ee->getMessage());
    		throw new DbAccessException($ee->getMessage());
    	}
    	catch( \PDOException $pe ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getUserRelation() --  do not select from MST_SERVICE_REL Table.');
    		Log::error(__FILE__, __LINE__, $pe->getMessage());
    		throw new DbAccessException($pe->getMessage());
    	}
    	catch ( Exception $e ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getUserRelation() --  do not select from MST_SERVICE_REL Table.');
    		Log::error(__FILE__, __LINE__, $e->getMessage());
    		throw new DbAccessException($e->getMessage());
    	}
    
    	$diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
    	Log::debug(__FILE__, __LINE__, 'INFO   getUserRelation() --  select from MST_SERVICE_REL Table. get Service ID for User. ('.$diff_time.')');
    	return $aryData;
    }
    
}