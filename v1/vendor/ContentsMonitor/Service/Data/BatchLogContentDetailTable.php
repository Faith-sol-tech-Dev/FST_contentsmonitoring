<?php
namespace ContentsMonitor\Service\Data;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\AdapterAwareInterface;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\Exception\ErrorException;

use ContentsMonitor\Service\Entity\BatchLogContentDetailData;
use ContentsMonitor\Common\LogClass as Log;
use ContentsMonitor\Common\UtilityClass as Utility;
use ContentsMonitor\Exception\DbAccessExceptionClass as DbAccessException;


/**
 * 監視サイトで使用するDBアクセス処理（WK_BATCH_LOG_CONTENTS_DETAIL）
 *
 * @package ContentsMonitor
 * @author  FST goto
 * @since   PHP 5.6, Zendframwork 2.4.9
 * @version 1.0.0
 */
class BatchLogContentDetailTable extends AbstractLocalAdapter implements AdapterAwareInterface
{

	/**
	 * DBアクセス用のモジュールの設定（Adapter, TableGateway）
	 * @param  Adapter $adapter   DBアダプタオブジェクト
	 */
    public function setDbAdapter(Adapter $adapter)
    {
        $resultSetPrototype = new ResultSet();
        $resultSetPrototype->setArrayObjectPrototype(new BatchLogContentDetailData());
        $this->tableGateway = new TableGateway('WK_BATCH_LOG_CONTENTS_DETAIL', $adapter, null, $resultSetPrototype);
        $this->adapter = $adapter;
    }
    
	/**
	 * 取込データ情報を取得
	 * 
	 * @param  int $batch_contents_id   バッチトランザクションNo
	 * @return     $row  		   	   　   WK_BATCH_LOG_CONTENTS_DETAILテーブル情報
	 */
    public function getWkBatchLogContentDetailByContentsId($batch_contents_id)
    {
    	$start_time=microtime(true);

    	try {
	    	$batch_contents_id = (int) $batch_contents_id;

	    	Log::query(sprintf('SQL::getWkBatchLogContentDetailByContentsId() param:batch_contents_id=%s',$batch_contents_id));
	    	
	        $rowset = $this->tableGateway->select(array('batch_contents_id' => $batch_contents_id));
	        $row = $rowset->current();
	        if (!$row) {
	            throw new \DbAccessException("Could not find row $batch_contents_id");
	        }
    	}
    	catch( \ErrorException $ee ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getWkBatchLogContentDetailByContentsId() --  do not select from WK_BATCH_LOG_CONTENTS_DETAIL Table.');
    		Log::error(__FILE__, __LINE__, $ee->getMessage());
    		throw new DbAccessException($ee->getMessage());
    	}
    	catch( \PDOException $pe ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getWkBatchLogContentDetailByContentsId() --  do not select from WK_BATCH_LOG_CONTENTS_DETAIL Table.');
    		Log::error(__FILE__, __LINE__, $pe->getMessage());
    		throw new DbAccessException($pe->getMessage());
    	}
    	catch (\Exception $e) {
        	Log::debug(__FILE__, __LINE__, 'ERROR getWkBatchLogContentDetailByContentsId() --  do not select from WK_BATCH_LOG_CONTENTS_DETAIL Table.');
        	Log::error(__FILE__, __LINE__, $e->getMessage());
    		throw new DbAccessException($e->getMessage());
    	}

        $diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
    	Log::debug(__FILE__, __LINE__, 'INFO   getWkBatchLogContentDetailByContentsId() --  select from WK_BATCH_LOG_CONTENTS_DETAIL Table. get Batch Data. ('.$diff_time.')');
        return $row;
    }

    /**
     * 登録処理(WK_BATCH_LOG_CONTENTS_DETAIL)
     *
     * @param BatchLogContentDetailData $wkBatch BatchLogContentDetailDataクラス(エンティティクラス)
     * @throws \DbAccessException
     */
    public function saveWkBatchLogContentDetail(BatchLogContentDetailData $wkBatchLogContentDetail)
    {
    	$start_time=microtime(true);
    	Log::debug(__FILE__, __LINE__, 'Dedub::saveWkBatchLogContentDetail() Start.');

    	try {
	    	$data = array(
	            'batch_transaction_id' => $wkBatchLogContentDetail->batch_transaction_id,
	            'contents_type' => $wkBatchLogContentDetail->contents_type,
	            'contents_id' => $wkBatchLogContentDetail->contents_id,
	            'url' => $wkBatchLogContentDetail->url,
	            'format' => $wkBatchLogContentDetail->format,
	            'comment' => $wkBatchLogContentDetail->comment,
	            'title' => $wkBatchLogContentDetail->title,
	            'caption' => $wkBatchLogContentDetail->caption,
	            'user' => $wkBatchLogContentDetail->user,
	            'create_date' => $wkBatchLogContentDetail->create_date,
	            'import_date' => $wkBatchLogContentDetail->import_date,
	            'import_state' => $wkBatchLogContentDetail->import_state,
	            'error_reason' => $wkBatchLogContentDetail->error_reason,
	            'recovery_state' => $wkBatchLogContentDetail->recovery_state,
	            'recovery_date' => $wkBatchLogContentDetail->recovery_date,
	            'recovery_user' => $wkBatchLogContentDetail->recovery_user,
	            'insert_date' => $wkBatchLogContentDetail->insert_date,
	            'update_date' => $wkBatchLogContentDetail->update_date,
	        );
	
	        $batch_contents_id = (int) $wkBatchLogContentDetail->batch_contents_id;

	        Log::query(sprintf('SQL::saveWkBatchLogContentDetail() param:batch_transaction_id=%s',$data['batch_transaction_id']));
	        Log::query(sprintf('SQL::saveWkBatchLogContentDetail() param:contents_type=%s',$data['contents_type']));
	        Log::query(sprintf('SQL::saveWkBatchLogContentDetail() param:contents_id=%s',$data['contents_id']));
	        Log::query(sprintf('SQL::saveWkBatchLogContentDetail() param:url=%s',$data['url']));
	        Log::query(sprintf('SQL::saveWkBatchLogContentDetail() param:format=%s',$data['format']));
	        Log::query(sprintf('SQL::saveWkBatchLogContentDetail() param:comment=%s',$data['comment']));
	        Log::query(sprintf('SQL::saveWkBatchLogContentDetail() param:title=%s',$data['title']));
	        Log::query(sprintf('SQL::saveWkBatchLogContentDetail() param:caption=%s',$data['caption']));
	        Log::query(sprintf('SQL::saveWkBatchLogContentDetail() param:user=%s',$data['user']));
	        Log::query(sprintf('SQL::saveWkBatchLogContentDetail() param:create_date=%s',$data['create_date']));
	        Log::query(sprintf('SQL::saveWkBatchLogContentDetail() param:import_date=%s',$data['import_date']));
	        Log::query(sprintf('SQL::saveWkBatchLogContentDetail() param:import_state=%s',$data['import_state']));
	        Log::query(sprintf('SQL::saveWkBatchLogContentDetail() param:error_reason=%s',$data['error_reason']));
	        Log::query(sprintf('SQL::saveWkBatchLogContentDetail() param:recovery_state=%s',$data['recovery_state']));
	        Log::query(sprintf('SQL::saveWkBatchLogContentDetail() param:recovery_date=%s',$data['recovery_date']));
	        Log::query(sprintf('SQL::saveWkBatchLogContentDetail() param:recovery_user=%s',$data['recovery_user']));
	        Log::query(sprintf('SQL::saveWkBatchLogContentDetail() param:insert_date=%s',$data['insert_date']));
	        Log::query(sprintf('SQL::saveWkBatchLogContentDetail() param:update_date=%s',$data['update_date']));
	        
	        if ($batch_contents_id == 0) {
	            $this->tableGateway->insert($data);
	        } else {
	            if ($this->getWkBatchLogContentDetailByContentsId($batch_contents_id)) {
	                $this->tableGateway->update($data, array('batch_contents_id' => $batch_contents_id));
	            } else {
	                throw new \DbAccessException('WkBatchLogContentDetail batch_contents_id does not exist');
	            }
	        }
    	}
    	catch( \ErrorException $ee ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR saveWkBatchLogContentDetail() --  do not select from WK_BATCH_LOG_CONTENTS_DETAIL Table.');
    		Log::error(__FILE__, __LINE__, $ee->getMessage());
    		throw new DbAccessException($ee->getMessage());
    	}
    	catch( \PDOException $pe ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR saveWkBatchLogContentDetail() --  do not select from WK_BATCH_LOG_CONTENTS_DETAIL Table.');
    		Log::error(__FILE__, __LINE__, $pe->getMessage());
    		throw new DbAccessException($pe->getMessage());
    	}
    	catch (\Exception $e) {
    		Log::debug(__FILE__, __LINE__, 'ERROR saveWkBatchLogContentDetail() --  do not insert from WK_BATCH_LOG_CONTENTS_DETAIL Table.');
    		Log::error(__FILE__, __LINE__, $e->getMessage());
    		throw new DbAccessException($e->getMessage());
    	}
    	 
        $diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
    	Log::debug(__FILE__, __LINE__, 'INFO   saveWkBatchLogContentDetail() --  select from WK_BATCH_LOG_CONTENTS_DETAIL Table. get Batch Data. ('.$diff_time.')');
    }
 
    /**
     * 取込検索結果（件数）を取得
     *
     * @param  array $param   パラメータ
     * @return       $row  WK_BATCH_DETAILテーブル情報
     */
    public function getBatchDetailListCount( $param )
    {
    	$start_time=microtime(true);
    
    	$aryData = array();
    	try {
    
    		$sql =   'SELECT COUNT(0) AS sumcnt '
    				.' FROM WK_BATCH_LOG_CONTENTS as wc '
    				.' INNER JOIN WK_BATCH_LOG_CONTENTS_DETAIL as wcd '
    				.'  ON wc.batch_transaction_id = wcd.batch_transaction_id '
    				.' INNER JOIN WK_BATCH_LOG as wb '
    				.'  ON wc.batch_log_id = wb.batch_log_id '
    				.' WHERE wc.batch_log_id = :batch_log_id';
    
			$params = array('batch_log_id'     => (int)$param['batch_log_id']);
			
 			if(isset($param['content_state']) && $param['content_state'] != 0){
  				$sql = $sql.' AND import_state = :content_state';
    			$params['content_state'] = (int)$param['content_state'];
    		}
    		
    		Log::query(sprintf('SQL::getBatchDetailListCount() query=%s',$sql));
    		if(isset($param['batch_log_id'])) Log::query(sprintf('SQL::getBatchDetailListCount() param:batch_log_id=%s',$param['batch_log_id']));
    		if(isset($param['content_state'])) Log::query(sprintf('SQL::getBatchDetailListCount() param:import_state=%s',$param['content_state']));
    		
    		$stmt = $this->adapter->createStatement($sql);
    		$results = $stmt->execute($params);
    
    		if ($results instanceof ResultInterface && $results->isQueryResult()) {
    			$resultSet = new ResultSet;
    			$resultSet->initialize($results);
    			foreach ($resultSet as $row) {
    				$aryData = $row;
    			}
    		}
    	}
    	catch( \ErrorException $ee ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getBatchDetailListCount() --  do not select from WK_BATCH_LOG_CONTENTS_DETAIL Table.');
    		Log::error(__FILE__, __LINE__, $ee->getMessage());
    		throw new DbAccessException($ee->getMessage());
    	}
    	catch( \PDOException $pe ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getBatchDetailListCount() --  do not select from WK_BATCH_LOG_CONTENTS_DETAIL Table.');
    		Log::error(__FILE__, __LINE__, $pe->getMessage());
    		throw new DbAccessException($pe->getMessage());
    	}
    	catch (\Exception $e) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getBatchDetailListCount() --  do not select from WK_BATCH_LOG_CONTENTS_DETAIL Table.');
    		Log::error(__FILE__, __LINE__, $e->getMessage());
    		throw new DbAccessException($e->getMessage());
    	}
    
    	$diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
    	Log::debug(__FILE__, __LINE__, 'INFO   getBatchDetailListCount() --  select from WK_BATCH_LOG_CONTENTS_DETAIL Table. get Batch Data. ('.$diff_time.')');
    	return $aryData["sumcnt"];
    }
    
    /**
     * 取込検索結果のbatch_detailリストデータ取得
     *
     * @param  array $param   パラメータ
     * @return       $row  WK_BATCH_DETAILテーブル情報
     */
    public function getBatchDetailList( $param )
    {
    	$start_time=microtime(true);
    
    	$aryData = array();
    	try {
    		 
    		$sql =   'SELECT @rownum:=@rownum+1 as ROW_NUM'
    				.'    ,wb.batch_id '
    				.'    ,wcd.contents_id '
    				.'    ,wcd.title '
    				.'    ,wcd.recovery_state '
    				.'    ,wcd.import_state '
    				.' FROM (SELECT @rownum:=0) as dummy , WK_BATCH_LOG_CONTENTS as wc '
    				.' INNER JOIN WK_BATCH_LOG_CONTENTS_DETAIL as wcd '
    				.'  ON wc.batch_transaction_id = wcd.batch_transaction_id '
    				.' INNER JOIN WK_BATCH_LOG as wb '
    				.'  ON wc.batch_log_id = wb.batch_log_id '
    				.' WHERE wc.batch_log_id = :batch_log_id';
    		
    		$params = array( 'batch_log_id'   => (int)$param['batch_log_id'] );
    
    		if(isset($param['content_state']) && $param['content_state'] != 0){
    			$sql = $sql.' AND wcd.import_state = :content_state';
    			$params['content_state'] = (int)$param['content_state'];
    		}

    		Log::query(sprintf('SQL::getBatchDetailList() query=%s',$sql));
    		if(isset($param['batch_log_id'])) Log::query(sprintf('SQL::getBatchDetailListCount() param:batch_log_id=%s',$param['batch_log_id']));
    		if(isset($param['content_state'])) Log::query(sprintf('SQL::getBatchDetailListCount() param:import_state=%s',$param['content_state']));
    		
			$stmt = $this->adapter->createStatement($sql);
    		$results = $stmt->execute($params);
    
    		if ($results instanceof ResultInterface && $results->isQueryResult()) {
    			$resultSet = new ResultSet;
    			$resultSet->initialize($results);
    			foreach ($resultSet as $row) {
    				array_push($aryData, (array)$row);
    			}
    		}
    	}
    	catch( \ErrorException $ee ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getBatchDetailList() --  do not select from WK_BATCH_DETAIL Table.');
    		Log::error(__FILE__, __LINE__, $ee->getMessage());
    		throw new DbAccessException($ee->getMessage());
    	}
    	catch( \PDOException $pe ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getBatchDetailList() --  do not select from WK_BATCH_DETAIL Table.');
    		Log::error(__FILE__, __LINE__, $pe->getMessage());
    		throw new DbAccessException($pe->getMessage());
    	}
    	catch (\Exception $e) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getBatchDetailList() --  do not select from WK_BATCH_DETAIL Table.');
    		Log::error(__FILE__, __LINE__, $e->getMessage());
    		throw new DbAccessException($e->getMessage());
    	}
    
    	$diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
    	Log::debug(__FILE__, __LINE__, 'INFO   getBatchDetailList() --  select from WK_BATCH_DETAIL Table. get Batch Data. ('.$diff_time.')');
    	return $aryData;
    }
    
    /**
     * 取込検索結果のbatch_detailデータ取得
     *
     * @param  array $param   パラメータ
     * @return       $row  WK_BATCH_DETAILテーブル情報
     */
    public function getBatchDetail( $param )
    {
    	$start_time=microtime(true);
    
    	$aryData = array();
    	try {
    		 
    		$sql =   'SELECT batch_contents_id '
    				.'    ,wcd.contents_id '
    				.'    ,wcd.contents_type '
    				.'    ,wcd.url '
    				.'    ,wcd.comment '
    				.'    ,wcd.title '
    				.'    ,wcd.caption '
 					.'    ,wcd.user '
    				.'    ,wcd.create_date '
    				.'    ,wcd.import_date '
    				.'    ,wcd.error_reason '
    				.'    ,wcd.recovery_state '
    				.'    ,s.service_name '
    				.' FROM WK_BATCH_LOG_CONTENTS as wc '
    				.' INNER JOIN WK_BATCH_LOG_CONTENTS_DETAIL as wcd '
    				.'  ON wc.batch_transaction_id = wcd.batch_contents_id '
    				.' INNER JOIN WK_BATCH_LOG as wl ON ( wc.batch_log_id = wl.batch_log_id ) '
    				.' INNER JOIN WK_BATCH as b ON ( wl.batch_id = b.batch_id ) '
    				.' INNER JOIN MST_SERVICE as s ON ( b.service_id = s.service_id ) '
    				.' WHERE wc.batch_log_id = :batch_log_id '
    				.'   AND wcd.contents_id = :contents_id ';
    				
    		$params = array( 'batch_log_id' => (int)$param['batch_log_id'],
    						 'contents_id'  => (int)$param['contents_id']
    					);
    
    		$stmt = $this->adapter->createStatement($sql);
    		$results = $stmt->execute($params);
    
    		if ($results instanceof ResultInterface && $results->isQueryResult()) {
    			$resultSet = new ResultSet;
    			$resultSet->initialize($results);
    			foreach ($resultSet as $row) {
    				//array_push($aryData, (array)$row);
    				$aryData = (array)$row;
    			}
    		}
    	}
    	catch( \ErrorException $ee ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getBatchDetail() --  do not select from WK_BATCH_DETAIL Table.');
    		Log::error(__FILE__, __LINE__, $ee->getMessage());
    		throw new DbAccessException($ee->getMessage());
    	}
    	catch( \PDOException $pe ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getBatchDetail() --  do not select from WK_BATCH_DETAIL Table.');
    		Log::error(__FILE__, __LINE__, $pe->getMessage());
    		throw new DbAccessException($pe->getMessage());
    	}
    	catch (\Exception $e) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getBatchDetail() --  do not select from WK_BATCH_DETAIL Table.');
    		Log::error(__FILE__, __LINE__, $e->getMessage());
    		throw new DbAccessException($e->getMessage());
    	}
    
    	$diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
    	Log::debug(__FILE__, __LINE__, 'INFO   getBatchDetail() --  select from WK_BATCH_DETAIL Table. get Batch Data. ('.$diff_time.')');
    	return $aryData;
    }
    
    /**
     * バッチの再取込処理の反映
     *
     * @param  array $param  パラメータ
     * @return     　	　$row    ロックデータ
     */
    public function ReuptakeDetailResult( $param )
    {
    	$start_time=microtime(true);
    
    	$aryData = array();
    	$Rrow = 0;
    	try {
    		 
    		$sql =   ' UPDATE WK_BATCH_LOG_CONTENTS_DETAIL '
    				.' SET recovery_state = 54 '
 					.' ,recovery_date = :recovery_date '
    				.' ,recovery_user = :user_id '
    				.' ,update_date = :update_date '
    				.' WHERE batch_contents_id = :batch_contents_id '
    				.' AND contents_id = :contents_id ';

      		$params = array( 'recovery_date'		=> date("Y-m-d H:i:s"),
    						 'user_id'				=> $param['user_id'],
    						 'update_date'			=> date("Y-m-d H:i:s"),
    						 'batch_contents_id'	=> $param['batch_contents_id'],
    						 'contents_id'			=> $param['contents_id'],
    					);
    
    		$stmt = $this->adapter->createStatement($sql);
    		$results = $stmt->execute($params);
    
    		$Rrow = $stmt->getResource()->rowCount();
    
    		if ($results instanceof ResultInterface && $results->isQueryResult()) {
    			$resultSet = new ResultSet;
    			$resultSet->initialize($results);
    			foreach ($resultSet as $row) {
    				$aryData = $row;
    			}
    		}
    	}
    	catch( \ErrorException $ee ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR ReuptakeDetailResult() --  do not select from WK_BATCH_DETAIL Table.');
    		Log::error(__FILE__, __LINE__, $ee->getMessage());
    		throw new DbAccessException($ee->getMessage());
    	}
    	catch( \PDOException $pe ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR ReuptakeDetailResult() --  do not select from WK_BATCH_DETAIL Table.');
    		Log::error(__FILE__, __LINE__, $pe->getMessage());
    		throw new DbAccessException($pe->getMessage());
    	}
    	catch ( \Exception $e){
    		Log::debug(__FILE__, __LINE__, 'ERROR ReuptakeDetailResult() --  do not update from WK_BATCH_DETAIL Table.');
    		Log::error(__FILE__, __LINE__, $de->getMessage());
    		throw new DbAccessException($e->getMessage());
    
    		////update失敗・・・０が返るはず
    		//echo("<pre>");
    		//var_dump($stmt->getResource()->rowCount());
    		//echo("</pre>");
    		//$Rrow = $stmt->getResource()->rowCount();
    	}
    
    	$diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
    	Log::debug(__FILE__, __LINE__, 'INFO   ReuptakeDetailResult() --  update from WK_BATCH_DETAIL Table. update Batch Data. ('.$diff_time.')');
    	return $Rrow;
    }
    
    
    
}