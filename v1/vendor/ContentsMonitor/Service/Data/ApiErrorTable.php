<?php
namespace ContentsMonitor\Service\Data;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\AdapterAwareInterface;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Exception\ErrorException;

use ContentsMonitor\Service\Entity\ApiErrorData;
use ContentsMonitor\Common\LogClass as Log;
use ContentsMonitor\Common\UtilityClass as Utility;
use ContentsMonitor\Exception\DbAccessExceptionClass as DbAccessException;


/**
 * 監視サイトで使用するDBアクセス処理（MST_API_ERROR）
 *
 * @package ContentsMonitor
 * @author  FST goto
 * @since   PHP 5.6, Zendframwork 2.4.9
 * @version 1.0.0
 */
class ApiErrorTable extends AbstractLocalAdapter implements AdapterAwareInterface
{

	/**
	 * DBアクセス用のモジュールの設定（Adapter, TableGateway）
	 * @param  Adapter $adapter   DBアダプタオブジェクト
	 */
    public function setDbAdapter(Adapter $adapter)
    {
        $resultSetPrototype = new ResultSet();
        $resultSetPrototype->setArrayObjectPrototype(new ApiErrorData());
        $this->tableGateway = new TableGateway('MST_API_ERROR', $adapter, null, $resultSetPrototype);
        $this->adapter = $adapter;
    }
    
    /**
     * APIエラー情報を取得
     * @param string $no API番号
     * @return ArrayObject|NULL
     */
    public function getMstApiError($no)
    {
    	$start_time=microtime(true);
    	
    	try {
    		 
	    	$no  = (int) $no;

	    	Log::query(sprintf('SQL::getMstApiError() param:no=%s',$no));
	    	
	    	$rowset = $this->tableGateway->select(array('no' => $no));
	    	$row = $rowset->current();
	    	if (!$row) {
	    		//throw new \Exception("Could not find row $no");
	    	}
    	}
    	catch( \ErrorException $ee ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getMstApiError() --  do not select from MST_API_ERROR Table.');
    		Log::error(__FILE__, __LINE__, $ee->getMessage());
    		throw new DbAccessException($ee->getMessage());
    	}
    	catch( \PDOException $pe ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getMstApiError() --  do not select from MST_API_ERROR Table.');
    		Log::error(__FILE__, __LINE__, $pe->getMessage());
    		throw new DbAccessException($pe->getMessage());
    	}
    	catch (\Exception $e) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getMstApiError() --  do not select from MST_API_ERROR Table.');
    		Log::error(__FILE__, __LINE__, $e->getMessage());
    		throw new DbAccessException($e->getMessage());
    	}
    	
    	$diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
    	Log::debug(__FILE__, __LINE__, 'INFO   getMstApiError() --  select from MST_API_ERROR Table. get Api Error Data. ('.$diff_time.')');
    	return $row;
    }
    
    /**
     * APIエラー情報を取得 (サービスID指定)
     * @param unknown $service_id サービスID
     * @return array APIエラーデータ
     */
    public function getMstApiErrorByServiceId($service_id)
    {
    	$start_time=microtime(true);
    	
    	try {
    		 
	    	$service_id  = (int) $service_id;

	    	Log::query(sprintf('SQL::getMstApiErrorByServiceId() param:service_id=%s',$service_id));
	    	
	    	$ret = $this->tableGateway->select(array('service_id' => $service_id));
    	}
    	catch( \ErrorException $ee ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getMstApiErrorByServiceId() --  do not select from MST_API_ERROR Table.');
    		Log::error(__FILE__, __LINE__, $ee->getMessage());
    		throw new DbAccessException($ee->getMessage());
    	}
    	catch( \PDOException $pe ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getMstApiErrorByServiceId() --  do not select from MST_API_ERROR Table.');
    		Log::error(__FILE__, __LINE__, $pe->getMessage());
    		throw new DbAccessException($pe->getMessage());
    	}
    	catch (\Exception $e) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getMstApiErrorByServiceId() --  do not select from MST_API_ERROR Table.');
    		Log::error(__FILE__, __LINE__, $e->getMessage());
    		throw new DbAccessException($e->getMessage());
    	}
    	
    	$diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
    	Log::debug(__FILE__, __LINE__, 'INFO   getMstApiErrorByServiceId() --  select from MST_API_ERROR Table. get Api Error Data. ('.$diff_time.')');
    	return $ret;
    }
    
}