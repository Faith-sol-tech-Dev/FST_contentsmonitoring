<?php
namespace ContentsMonitor\Service\Data;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\AdapterAwareInterface;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Exception\ErrorException;

use ContentsMonitor\Service\Entity\BatchData;
use ContentsMonitor\Common\LogClass as Log;
use ContentsMonitor\Common\UtilityClass as Utility;
use ContentsMonitor\Exception\DbAccessExceptionClass as DbAccessException;


/**
 * 監視サイトで使用するDBアクセス処理（WK_BATCH）
 *
 * @package ContentsMonitor
 * @author  FST goto
 * @since   PHP 5.6, Zendframwork 2.4.9
 * @version 1.0.0
 */
class BatchTable extends AbstractLocalAdapter implements AdapterAwareInterface
{

	/**
	 * DBアクセス用のモジュールの設定（Adapter, TableGateway）
	 * @param  Adapter $adapter   DBアダプタオブジェクト
	 */
    public function setDbAdapter(Adapter $adapter)
    {
        $resultSetPrototype = new ResultSet();
        $resultSetPrototype->setArrayObjectPrototype(new BatchData());
        $this->tableGateway = new TableGateway('WK_BATCH', $adapter, null, $resultSetPrototype);
        $this->adapter = $adapter;
    }
    
	/**
	 * バッチ情報を取得
	 * @param  int $id   コンテンツID
	 * @return     $row  WK_BATCHテーブル情報
	 */
    public function getBatch($id)
    {
    	$start_time=microtime(true);

    	$row;
    	try {
	        $id  = (int) $id;

	        Log::query(sprintf('SQL::getBatch() param:id=%s',$id));
	        
	        $rowset = $this->tableGateway->select(array('batch_id' => $id));
	        $row = $rowset->current();
	        if (!$row) {
	            throw new \DbAccessException("Could not find row $id");
	        }
    	}
    	catch( \ErrorException $ee ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getBatch() --  do not select from WK_BATCH Table.');
    		Log::error(__FILE__, __LINE__, $ee->getMessage());
    		throw new DbAccessException($ee->getMessage());
    	}
    	catch( \PDOException $pe ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getBatch() --  do not select from WK_BATCH Table.');
    		Log::error(__FILE__, __LINE__, $pe->getMessage());
    		throw new DbAccessException($pe->getMessage());
    	}
    	catch (\Exception $e) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getBatch() --  do not select from WK_BATCH Table.');
    		Log::error(__FILE__, __LINE__, $e->getMessage());
    		throw new DbAccessException($e->getMessage());
    	}
    	 
    	$diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
    	Log::debug(__FILE__, __LINE__, 'INFO   getBatch() --  select from WK_BATCH Table. get Batch Data. ('.$diff_time.')');
    	return $row;
    }

    /**
     * 登録処理(WK_BATCH)
     * @param BatchData $wkBatch BatchDataクラス(エンティティクラス)
     * @throws \DbAccessException
     */
    public function saveWkBatch(BatchData $wkBatch)
    {
    	$start_time=microtime(true);
    
    	try {
	    	$data = array(
	    			'service_id' => $wkBatch->service_id,
	    			'batch_type' => $wkBatch->batch_type,
	    			'batch_name' => $wkBatch->batch_name,
	    			'insert_user' => $wkBatch->insert_user,
	    			'insert_date' => $wkBatch->insert_date,
	    			'update_user' => $wkBatch->update_user,
	    			'update_date' => $wkBatch->update_date,
	    	);
	    
	    	$batch_id = (int) $wkBatch->batch_id;

	    	Log::query(sprintf('SQL::saveWkBatch() param:service_id=%s',$data['service_id']));
    		Log::query(sprintf('SQL::saveWkBatch() param:batch_type=%s',$data['batch_type']));
    		Log::query(sprintf('SQL::saveWkBatch() param:batch_name=%s',$data['batch_name']));
    		Log::query(sprintf('SQL::saveWkBatch() param:insert_user=%s',$data['insert_user']));
    		Log::query(sprintf('SQL::saveWkBatch() param:insert_date=%s',$data['insert_date']));
    		Log::query(sprintf('SQL::saveWkBatch() param:update_user=%s',$data['update_user']));
    		Log::query(sprintf('SQL::saveWkBatch() param:update_date=%s',$data['update_date']));
    		Log::query(sprintf('SQL::saveWkBatch() param:batch_id=%s',$batch_id));
    		
	    	if ($batch_id == 0) {
	    		$this->tableGateway->insert($data);
	    	} else {
	    		if ($this->getWkBatch($batch_id)) {
	    			$this->tableGateway->update($data, array('batch_id' => $batch_id));
	    		} else {
	    			throw new \DbAccessException('WkBatch batch_id does not exist');
	    		}
	    	}
    	}
    	catch( \ErrorException $ee ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR saveWkBatch() --  do not select from WK_BATCH Table.');
    		Log::error(__FILE__, __LINE__, $ee->getMessage());
    		throw new DbAccessException($ee->getMessage());
    	}
    	catch( \PDOException $pe ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR saveWkBatch() --  do not select from WK_BATCH Table.');
    		Log::error(__FILE__, __LINE__, $pe->getMessage());
    		throw new DbAccessException($pe->getMessage());
    	}
    	catch (\Exception $e) {
        	Log::debug(__FILE__, __LINE__, 'ERROR saveWkBatch() --  do not select from WK_BATCH Table.');
        	Log::error(__FILE__, __LINE__, $e->getMessage());
    		throw new DbAccessException($e->getMessage());
    	}
    	
        $diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
    	Log::debug(__FILE__, __LINE__, 'INFO   saveWkBatch() --  select from WK_BATCH Table. get Batch Data. ('.$diff_time.')');
    }
}