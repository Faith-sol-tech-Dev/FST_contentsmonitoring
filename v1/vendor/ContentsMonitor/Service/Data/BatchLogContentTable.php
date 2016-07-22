<?php
namespace ContentsMonitor\Service\Data;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\AdapterAwareInterface;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Exception\ErrorException;

use ContentsMonitor\Service\Entity\BatchLogContentData;
use ContentsMonitor\Common\LogClass as Log;
use ContentsMonitor\Common\UtilityClass as Utility;
use ContentsMonitor\Exception\DbAccessExceptionClass as DbAccessException;


/**
 * 監視サイトで使用するDBアクセス処理（WK_BATCH_LOG_CONTENTS）
 *
 * @package ContentsMonitor
 * @author  FST goto
 * @since   PHP 5.6, Zendframwork 2.4.9
 * @version 1.0.0
 */
class BatchLogContentTable extends AbstractLocalAdapter implements AdapterAwareInterface
{

	/**
	 * DBアクセス用のモジュールの設定（Adapter, TableGateway）
	 * @param  Adapter $adapter   DBアダプタオブジェクト
	 */
    public function setDbAdapter(Adapter $adapter)
    {
        $resultSetPrototype = new ResultSet();
        $resultSetPrototype->setArrayObjectPrototype(new BatchLogContentData());
        $this->tableGateway = new TableGateway('WK_BATCH_LOG_CONTENTS', $adapter, null, $resultSetPrototype);
        $this->adapter = $adapter;
    }
    
	/**
	 * バッチ処理詳細情報を取得
	 * 
	 * @param  int $batch_transaction_id   バッチトランザクションNo
	 * @return     $row  		   		   WK_BATCH_LOG_CONTENTSテーブル情報
	 */
    public function getWkBatchLogContent($batch_transaction_id)
    {
    	$start_time=microtime(true);
    	
    	$row;
    	try {
	    	$batch_transaction_id = (int) $batch_transaction_id;

	    	Log::query(sprintf('SQL::getWkBatchLogContent() param:batch_transaction_id=%s',$batch_transaction_id));
	    	
	    	$rowset = $this->tableGateway->select(array('batch_transaction_id' => $batch_transaction_id));
	    	$row = $rowset->current();
	    	if (!$row) {
	    		throw new \DbAccessException("Could not find row $batch_transaction_id");
	    	}
	
    	}
    	catch( \ErrorException $ee ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getWkBatchLogContentDetailByContentsId() --  do not select from WK_BATCH_LOG_CONTENTS Table.');
    		Log::error(__FILE__, __LINE__, $ee->getMessage());
    		throw new DbAccessException($ee->getMessage());
    	}
    	catch( \PDOException $pe ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getWkBatchLogContentDetailByContentsId() --  do not select from WK_BATCH_LOG_CONTENTS Table.');
    		Log::error(__FILE__, __LINE__, $pe->getMessage());
    		throw new DbAccessException($pe->getMessage());
    	}
    	catch (\Exception $e) {
        	Log::debug(__FILE__, __LINE__, 'ERROR getWkBatchLogContentDetailByContentsId() --  do not select from WK_BATCH_LOG_CONTENTS Table.');
        	Log::error(__FILE__, __LINE__, $e->getMessage());
    		throw new DbAccessException($e->getMessage());
    	}

        $diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
    	Log::debug(__FILE__, __LINE__, 'INFO   getWkBatchLogContentDetailByContentsId() --  select from WK_BATCH_LOG_CONTENTS Table. get Batch Data. ('.$diff_time.')');
    	return $row;
    }
    
    /**
     * 登録処理(WK_BATCH_LOG_CONTENTS)
     *
     * @param BatchLogContentData $wkBatch BatchLogContentDataクラス(エンティティクラス)
     * @throws \DbAccessException
     */
    public function saveWkBatchLogContent(BatchLogContentData $wkBatchLogContent)
    {
    	$start_time=microtime(true);

    	try {
	    	$data = array(
	    			'batch_log_id' => $wkBatchLogContent->batch_log_id,
	    	);
	    
	    	$batch_transaction_id = (int) $wkBatchLogContent->batch_transaction_id;

	    	Log::query(sprintf('SQL::saveWkBatchLogContent() param:batch_log_id=%s',$data['batch_log_id']));
	    	Log::query(sprintf('SQL::saveWkBatchLogContent() param:batch_transaction_id=%s',$batch_transaction_id));
	    	
	    	if ($batch_transaction_id == 0) {
	    		$this->tableGateway->insert($data);
	    	} else {
	    		if ($this->getWkBatchLogContent($batch_transaction_id)) {
	    			$this->tableGateway->update($data, array('batch_transaction_id' => $batch_transaction_id));
	    		} else {
	    			throw new \DbAccessException('WkBatchLogContent batch_transaction_id does not exist');
	    		}
	    	}
		
    	}
    	catch( \ErrorException $ee ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR saveWkBatchLogContent() --  do not select from WK_BATCH_LOG_CONTENTS Table.');
    		Log::error(__FILE__, __LINE__, $ee->getMessage());
    		throw new DbAccessException($ee->getMessage());
    	}
    	catch( \PDOException $pe ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR saveWkBatchLogContent() --  do not select from WK_BATCH_LOG_CONTENTS Table.');
    		Log::error(__FILE__, __LINE__, $pe->getMessage());
    		throw new DbAccessException($pe->getMessage());
    	}
    	catch (\Exception $e) {
        	Log::debug(__FILE__, __LINE__, 'ERROR saveWkBatchLogContent() --  do not update from WK_BATCH_LOG_CONTENTS Table.');
        	Log::error(__FILE__, __LINE__, $e->getMessage());
    		throw new DbAccessException($e->getMessage());
    	}
    	 
        $diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
    	Log::debug(__FILE__, __LINE__, 'INFO   saveWkBatchLogContent() --  update from WK_BATCH_LOG_CONTENTS Table. update Batch Data. ('.$diff_time.')');
   	}


}