<?php
namespace ContentsMonitor\Service\Data;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\AdapterAwareInterface;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Exception\ErrorException;

use ContentsMonitor\Service\Entity\BatchProcData;
use ContentsMonitor\Common\LogClass as Log;
use ContentsMonitor\Common\UtilityClass as Utility;
use ContentsMonitor\Exception\DbAccessExceptionClass as DbAccessException;


/**
 * 監視サイトで使用するDBアクセス処理（WK_BATCH_PROC）
 *
 * @package ContentsMonitor
 * @author  FST goto
 * @since   PHP 5.6, Zendframwork 2.4.9
 * @version 1.0.0
 */
class BatchProcTable extends AbstractLocalAdapter implements AdapterAwareInterface
{

	/**
	 * DBアクセス用のモジュールの設定（Adapter, TableGateway）
	 * @param  Adapter $adapter   DBアダプタオブジェクト
	 */
    public function setDbAdapter(Adapter $adapter)
    {
        $resultSetPrototype = new ResultSet();
        $resultSetPrototype->setArrayObjectPrototype(new BatchProcData());
        $this->tableGateway = new TableGateway('WK_BATCH_PROC', $adapter, null, $resultSetPrototype);
        $this->adapter = $adapter;
    }
    
	/**
	 * バッチリスト情報を取得
	 * 
	 * @param  int $batch_id   バッチID
	 * @return     $row  	   WK_BATCH_PROCテーブル情報
	 */
    public function getWkBatchProcForUpdate($batch_id)
    {
    	$start_time=microtime(true);
    	
    	try {
	    	$batch_id = (int) $batch_id;

	    	//存在チェック
	    	$aryData = $this->getWkBatchProc($batch_id);
	    	if(empty($aryData)) {
	    			$item = new BatchProcData();
	    			$item->batch_id = $batch_id;
	    			$item->state = 0;
	    			$this->saveWkBatchProc($item);
	    	}
	    	
	        $sql = 'SELECT * FROM ' . $this->tableGateway->table . ' WHERE batch_id = :batch_id FOR UPDATE';
	        $params = array(
	            'batch_id' => $batch_id,
	        );
	        
	        Log::query(sprintf('SQL::getWkBatchProcForUpdate() query=%s',$sql));
	        Log::query(sprintf('SQL::getWkBatchProcForUpdate() param:batch_id=%s',$params['batch_id']));
	        
	        $adapter = $this->tableGateway->getAdapter();
	        $statement = $adapter->createStatement($sql);
	        $rowset = $statement->execute($params);
	        $row = $rowset->current();
	        if (!$row) {
	            throw new \DbAccessException("Could not find row $batch_id");
	        }
    	}
    	catch( \ErrorException $ee ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getWkBatchProcForUpdate() --  do not select from WK_BATCH_PROC Table.');
    		Log::error(__FILE__, __LINE__, $ee->getMessage());
    		throw new DbAccessException($ee->getMessage());
    	}
    	catch( \PDOException $pe ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getWkBatchProcForUpdate() --  do not select from WK_BATCH_PROC Table.');
    		Log::error(__FILE__, __LINE__, $pe->getMessage());
    		throw new DbAccessException($pe->getMessage());
    	}
    	catch (\Exception $e) {
        	Log::debug(__FILE__, __LINE__, 'ERROR getWkBatchProcForUpdate() --  do not select from WK_BATCH_PROC Table.');
        	Log::error(__FILE__, __LINE__, $e->getMessage());
    		throw new DbAccessException($e->getMessage());
    	}
    	
        $diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
    	Log::debug(__FILE__, __LINE__, 'INFO   getWkBatchProcForUpdate() --  select from WK_BATCH_PROC Table. get Batch PROC Data. ('.$diff_time.')');
    	return $row;
    }

    /**
	 * バッチリスト情報を取得
	 * 
     * @param int $batch_id バッチID
     * @return 
     */
    public function getWkBatchProc($batch_id)
    {
    	$start_time=microtime(true);
    	
    	try {
	    	$batch_id = (int) $batch_id;

	    	Log::query(sprintf('SQL::getWkBatchProc() param:batch_id=%s',$batch_id));
	    	
	        $rowset = $this->tableGateway->select(array('batch_id' => $batch_id));
	        $row = $rowset->current();
	        if (!$row) {
	            //throw new \DbAccessException("Could not find row $batch_id");
	        }
    	}
    	catch( \ErrorException $ee ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getWkBatchProc() --  do not select from WK_BATCH_PROC Table.');
    		Log::error(__FILE__, __LINE__, $ee->getMessage());
    		throw new DbAccessException($ee->getMessage());
    	}
    	catch( \PDOException $pe ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getWkBatchProc() --  do not select from WK_BATCH_PROC Table.');
    		Log::error(__FILE__, __LINE__, $pe->getMessage());
    		throw new DbAccessException($pe->getMessage());
    	}
    	catch (\Exception $e) {
        	Log::debug(__FILE__, __LINE__, 'ERROR getWkBatchProc() --  do not select from WK_BATCH_PROC Table.');
        	Log::error(__FILE__, __LINE__, $e->getMessage());
    		throw new DbAccessException($e->getMessage());
    	}
    	 
        $diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
    	Log::debug(__FILE__, __LINE__, 'INFO   getWkBatchProc() --  select from WK_BATCH_PROC Table. get Batch LOG Data. ('.$diff_time.')');
        return $row;
    }

    /**
     * 登録処理(WK_BATCH_PROC)
     *
     * @param BatchProcData $wkBatch BatchProcDataクラス(エンティティクラス)
     * @throws \DbAccessException
     */
    public function saveWkBatchProc(BatchProcData $wkBatchProc)
    {
    	$start_time=microtime(true);
    	
    	try {
	    	$data = array(
	            'batch_id' => $wkBatchProc->batch_id,
	            'state' => $wkBatchProc->state,
	        );
	
	        $batch_id = (int) $wkBatchProc->batch_id;

	        Log::query(sprintf('SQL::saveWkBatchProc() param:batch_id=%s',$data['batch_id']));
    		Log::query(sprintf('SQL::saveWkBatchProc() param:state=%s',$data['state']));
    		Log::query(sprintf('SQL::saveWkBatchProc() param:batch_id=%s',$batch_id));
    		
	        if ($batch_id == 0 || !$this->getWkBatchProc($batch_id)) {
	            $this->tableGateway->insert($data);
	        } else {
                $this->tableGateway->update($data, array('batch_id' => $batch_id));
	        }
    	}
    	catch( \ErrorException $ee ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR saveWkBatchProc() --  do not select from WK_BATCH_PROC Table.');
    		Log::error(__FILE__, __LINE__, $ee->getMessage());
    		throw new DbAccessException($ee->getMessage());
    	}
    	catch( \PDOException $pe ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR saveWkBatchProc() --  do not select from WK_BATCH_PROC Table.');
    		Log::error(__FILE__, __LINE__, $pe->getMessage());
    		throw new DbAccessException($pe->getMessage());
    	}
    	catch (\Exception $e) {
        	Log::debug(__FILE__, __LINE__, 'ERROR saveWkBatchProc() --  do not select from WK_BATCH_PROC Table.');
        	Log::error(__FILE__, __LINE__, $e->getMessage());
    		throw new DbAccessException($e->getMessage());
    	}
    	
        $diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
    	Log::debug(__FILE__, __LINE__, 'INFO   saveWkBatchProc() --  select from WK_BATCH_PROC Table. get Batch LOG Data. ('.$diff_time.')');
    }
}