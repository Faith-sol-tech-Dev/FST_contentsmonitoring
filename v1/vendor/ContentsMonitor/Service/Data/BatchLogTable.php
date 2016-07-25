<?php
namespace ContentsMonitor\Service\Data;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\AdapterAwareInterface;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\Exception\ErrorException;

use ContentsMonitor\Service\Entity\BatchLogData;
use ContentsMonitor\Common\LogClass as Log;
use ContentsMonitor\Common\UtilityClass as Utility;
use ContentsMonitor\Exception\DbAccessExceptionClass as DbAccessException;


/**
 * 監視サイトで使用するDBアクセス処理（WK_BATCH_LOG）
 *
 * @package ContentsMonitor
 * @author  FST goto
 * @since   PHP 5.6, Zendframwork 2.4.9
 * @version 1.0.0
 */
class BatchLogTable extends AbstractLocalAdapter implements AdapterAwareInterface
{

	/**
	 * DBアクセス用のモジュールの設定（Adapter, TableGateway）
	 * @param  Adapter $adapter   DBアダプタオブジェクト
	 */
    public function setDbAdapter(Adapter $adapter)
    {
        $resultSetPrototype = new ResultSet();
        $resultSetPrototype->setArrayObjectPrototype(new BatchLogData());
        $this->tableGateway = new TableGateway('WK_BATCH_LOG', $adapter, null, $resultSetPrototype);
        $this->adapter = $adapter;
    }
    
	/**
	 * バッチ処理情報を取得
	 * 
	 * @param  int $batch_log_id   バッチ処理No
	 * @return     $row  		   WK_BATCHテーブル情報
	 */
    public function getWkBatchLog($batch_log_id)
    {
    	$start_time=microtime(true);

    	$row;
    	try {
	    	$batch_log_id = (int) $batch_log_id;

	    	Log::query(sprintf('SQL::getWkBatchLog() param:batch_log_id=%s',$batch_log_id));
	    	
	        $rowset = $this->tableGateway->select(array('batch_log_id' => $batch_log_id));
	        $row = $rowset->current();
	        if (!$row) {
	            throw new \DbAccessException("Could not find row $batch_log_id");
	        }

    	}
    	catch( \ErrorException $ee ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getWkBatchLog() --  do not select from WK_BATCH_LOG Table.');
    		Log::error(__FILE__, __LINE__, $ee->getMessage());
    		throw new DbAccessException($ee->getMessage());
    	}
    	catch( \PDOException $pe ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getWkBatchLog() --  do not select from WK_BATCH_LOG Table.');
    		Log::error(__FILE__, __LINE__, $pe->getMessage());
    		throw new DbAccessException($pe->getMessage());
    	}
    	catch (\Exception $e) {
        	Log::debug(__FILE__, __LINE__, 'ERROR getWkBatchLog() --  do not select from WK_BATCH_LOG Table.');
        	Log::error(__FILE__, __LINE__, $e->getMessage());
    		throw new DbAccessException($e->getMessage());
    	}

        $diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
    	Log::debug(__FILE__, __LINE__, 'INFO   getWkBatchLog() --  select from WK_BATCH_LOG Table. get Batch LOG Data. ('.$diff_time.')');
    	return $row;
    }


    /**
     * 取込検索結果（件数）を取得
     *
     * @param  array $param   パラメータ
     * @return       $row  WK_BATCHテーブル情報
     */
    public function getBatchDataCount( $param )
    {
    	$start_time=microtime(true);
    	
    	$aryData = array();
    	try {
    		$sql =   'SELECT COUNT(0) AS sumcnt '
    				.' FROM WK_BATCH_LOG as wl '
    				.' INNER JOIN WK_BATCH as b ON ( wl.batch_id = b.batch_id ) '
    				.' INNER JOIN WK_BATCH_PROC as wlp ON ( wlp.batch_id = b.batch_id ) '
    				.' INNER JOIN MST_SERVICE as s ON ( b.service_id = s.service_id ) '
		    		.'  WHERE wlp.state = 0 ';
    		if(!empty($param['check_state1']) || !empty($param['check_state2']) || !empty($param['check_state3'])) {
    				$sql .= '   AND ( ';
    				if(!empty($param['check_state1'])) { $sql .= 'wl.import_type = :check_state1'; }
    		    	if(!empty($param['check_state2'])) { $sql .= (!empty($param['check_state1']) ? ' OR ':'').'wl.import_type = :check_state2'; }
    				if(!empty($param['check_state3'])) { $sql .= ((!empty($param['check_state1'])||!empty($param['check_state2'])) ? ' OR ':'').'wl.import_type = :check_state3'; }
    				$sql .= ' ) ';
    		}
    	    if(!empty($param['content_state']) && $param['content_state'] > 0) {
    	    		if(1==$param['content_state']) {
    	    			//OK
    	    			$sql .= '   AND wl.state IN (19,32,49,16,29,46) ';
    	    		}
    	    		elseif(2==$param['content_state']) {
    	    			//NG
    	    			$sql .= '   AND wl.state NOT IN (19,32,49,16,29,46) ';
    	    		}
    	    }
    		if(!empty($param['import_date_min']) && !empty($param['import_date_max'])) {
    	    		$sql .= '   AND ( wl.start_date >= :import_date_min and wl.end_date <= :import_date_max )';
    	    }
    	    
    	    if(!empty($param['service_list'])) {
    	    		$sql .= '   AND s.service_id = :service_list ';
    	    }
		    				
    		$params = array();
    		if(!empty($param['check_state1']))    { $params['check_state1'] = (int)$param['check_state1']; }
    		if(!empty($param['check_state2']))    { $params['check_state2'] = (int)$param['check_state2']; }
    		if(!empty($param['check_state3']))    { $params['check_state3'] = (int)$param['check_state3']; }
    	    //if(!empty($param['content_state']))   { $params['state'] = (int)$param['content_state']; }
    		if(!empty($param['import_date_min'])) { $params['import_date_min'] = date("Y-m-d H:i:s",strtotime($param['import_date_min'])); }
    		if(!empty($param['import_date_max'])) { $params['import_date_max'] = date("Y-m-d H:i:s",strtotime($param['import_date_max'])); }
    	    if(!empty($param['service_list']))    { $params['service_list'] = (int)$param['service_list']; }
    		
    	    Log::query(sprintf('SQL::getBatchDataCount() query=%s',$sql));
    		if(!empty($param['check_state1']))    { Log::query(sprintf('SQL::getBatchDataCount() param:check_state1=%s',$params['check_state1'])); }
    		if(!empty($param['check_state2']))    { Log::query(sprintf('SQL::getBatchDataCount() param:check_state2=%s',$params['check_state2'])); }
    		if(!empty($param['check_state3']))    { Log::query(sprintf('SQL::getBatchDataCount() param:check_state3=%s',$params['check_state3'])); }
    	    //if(!empty($param['content_state']))   { Log::query(sprintf('SQL::getBatchDataCount() param:state=%s',$params['state'])); }
    		if(!empty($param['import_date_min'])) { Log::query(sprintf('SQL::getBatchDataCount() param:import_date_min=%s',$params['import_date_min'])); }
    		if(!empty($param['import_date_max'])) { Log::query(sprintf('SQL::getBatchDataCount() param:import_date_max=%s',$params['import_date_max'])); }
    	    if(!empty($param['service_list']))    { Log::query(sprintf('SQL::getBatchDataCount() param:service_list=%s',$params['service_list'])); }
    	    
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
    		Log::debug(__FILE__, __LINE__, 'ERROR getBatchDataCount() --  do not select from WK_BATCH_LOG Table.');
    		Log::error(__FILE__, __LINE__, $ee->getMessage());
    		throw new DbAccessException($ee->getMessage());
    	}
    	catch( \PDOException $pe ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getBatchDataCount() --  do not select from WK_BATCH_LOG Table.');
    		Log::error(__FILE__, __LINE__, $pe->getMessage());
    		throw new DbAccessException($pe->getMessage());
    	}
    	catch (\Exception $e) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getBatchDataCount() --  do not select from WK_BATCH_LOG Table.');
    		Log::error(__FILE__, __LINE__, $e->getMessage());
    		throw new DbAccessException($e->getMessage());
    	}
    	 
    	$diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
    	Log::debug(__FILE__, __LINE__, 'INFO   getBatchDataCount() --  select from WK_BATCH_LOG Table. get Batch Data. ('.$diff_time.')');
    	return $aryData["sumcnt"];
    }
    
    /**
     * 取込検索結果を取得
     *
     * @param  array $param   パラメータ
     * @return       $row  WK_BATCHテーブル情報
     */
    public function getBatchData( $param )
    {
    	$start_time=microtime(true);

    	$aryData = array();
    	try {
    		 
    		$sql =   /*'SET @rownum=0; '
    				.*/'SELECT @rownum:=@rownum+1 as ROW_NUM'
    				.'    ,s.service_name '
    				.'    ,wl.import_type '
    				.'    ,wl.insert_date '
    				.'    ,wl.batch_id '
    				.'    ,wl.batch_log_id '
    				.'    ,b.batch_name '
    				.'    ,(SELECT COUNT(0) FROM WK_BATCH_LOG_CONTENTS AS wblc ' 
    				.'		WHERE wl.batch_log_id = wblc.batch_log_id GROUP BY wblc.batch_log_id) AS sumcnt '
    				.'    ,wl.recovery_state '
    				.'    ,wl.state '
    				.' FROM WK_BATCH_LOG as wl '
    				.' INNER JOIN WK_BATCH as b ON ( wl.batch_id = b.batch_id ) '
    				.' INNER JOIN WK_BATCH_PROC as wlp ON ( wlp.batch_id = b.batch_id ) '
    				.' INNER JOIN MST_SERVICE as s ON ( b.service_id = s.service_id ) '
		    		.'  WHERE wlp.state = 0 ';
    		if(!empty($param['check_state1']) || !empty($param['check_state2']) || !empty($param['check_state3'])) {
    				$sql .= '   AND ( ';
    				if(!empty($param['check_state1'])) { $sql .= 'wl.import_type = :check_state1'; }
    		    	if(!empty($param['check_state2'])) { $sql .= (!empty($param['check_state1']) ? ' OR ':'').'wl.import_type = :check_state2'; }
    				if(!empty($param['check_state3'])) { $sql .= ((!empty($param['check_state1'])||!empty($param['check_state2'])) ? ' OR ':'').'wl.import_type = :check_state3'; }
    				$sql .= ' ) ';
    		}
    	    if(!empty($param['content_state']) && $param['content_state'] > 0) {
    	    		if(1==$param['content_state']) {
    	    			//OK
    	    			$sql .= '   AND wl.state IN (19,32,49,16,29,46) ';
    	    		}
    	    		elseif(2==$param['content_state']) {
    	    			//NG
    	    			$sql .= '   AND wl.state NOT IN (19,32,49,16,29,46) ';
    	    		}
    	    }
    		if(!empty($param['import_date_min']) && !empty($param['import_date_max'])) {
    	    		$sql .= '   AND ( wl.start_date >= :import_date_min and wl.end_date <= :import_date_max )';
    	    }
    	    if(!empty($param['service_list'])) {
    	    		$sql .= '   AND s.service_id = :service_list ';
    	    }

    	    $params = array();
    	    if(!empty($param['check_state1']))    { $params['check_state1'] = (int)$param['check_state1']; }
    	    if(!empty($param['check_state2']))    { $params['check_state2'] = (int)$param['check_state2']; }
    	    if(!empty($param['check_state3']))    { $params['check_state3'] = (int)$param['check_state3']; }
    	    //if(!empty($param['content_state']))   { $params['state'] = (int)$param['content_state']; }
    	    if(!empty($param['import_date_min'])) { $params['import_date_min'] = date("Y-m-d H:i:s",strtotime($param['import_date_min'])); }
    	    if(!empty($param['import_date_max'])) { $params['import_date_max'] = date("Y-m-d H:i:s",strtotime($param['import_date_max'])); }
    	    if(!empty($param['service_list']))    { $params['service_list'] = (int)$param['service_list']; }
    	    	
    	    Log::query(sprintf('SQL::getBatchData() query=%s',$sql));
    	    if(!empty($param['check_state1']))    { Log::query(sprintf('SQL::getBatchData() param:check_state1=%s',$params['check_state1'])); }
    	    if(!empty($param['check_state2']))    { Log::query(sprintf('SQL::getBatchData() param:check_state2=%s',$params['check_state2'])); }
    	    if(!empty($param['check_state3']))    { Log::query(sprintf('SQL::getBatchData() param:check_state3=%s',$params['check_state3'])); }
    	    //if(!empty($param['content_state']))   { Log::query(sprintf('SQL::getBatchData() param:state=%s',$params['state'])); }
    	    if(!empty($param['import_date_min'])) { Log::query(sprintf('SQL::getBatchData() param:import_date_min=%s',$params['import_date_min'])); }
    	    if(!empty($param['import_date_max'])) { Log::query(sprintf('SQL::getBatchData() param:import_date_max=%s',$params['import_date_max'])); }
    	    if(!empty($param['service_list']))    { Log::query(sprintf('SQL::getBatchData() param:service_list=%s',$params['service_list'])); }
    	    
			$stmt = $this->adapter->createStatement($sql);
			$results = $stmt->execute($params);

    		if ($results instanceof ResultInterface && $results->isQueryResult()) {
    			$resultSet = new ResultSet;
    			$resultSet->initialize($results);
    			foreach ($resultSet as $row) {
    				array_push($aryData, $row);
    			}
    		}
    	}
    	catch( \ErrorException $ee ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getBatchData() --  do not select from WK_BATCH_LOG Table.');
    		Log::error(__FILE__, __LINE__, $ee->getMessage());
    		throw new DbAccessException($ee->getMessage());
    	}
    	catch( \PDOException $pe ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getBatchData() --  do not select from WK_BATCH_LOG Table.');
    		Log::error(__FILE__, __LINE__, $pe->getMessage());
    		throw new DbAccessException($pe->getMessage());
    	}
    	catch (\Exception $e) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getBatchData() --  do not select from WK_BATCH_LOG Table.');
    		Log::error(__FILE__, __LINE__, $e->getMessage());
    		throw new DbAccessException($e->getMessage());
    	}
    	
    	$diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
    	Log::debug(__FILE__, __LINE__, 'INFO   getBatchData() --  select from WK_BATCH_LOG Table. get Batch Data. ('.$diff_time.')');
    	return $aryData;
    }
    
    /**
     * 取込検索結果のbatchデータ取得
     *
     * @param  array $param   パラメータ batch_id
     * @return       $row  WK_BATCHテーブル情報
     */
    public function getBatch( $param )
    {
    	$start_time=microtime(true);
    	
    	$aryData = array();
    	try {
    		 
    		$sql =   'SELECT bl.batch_id '
    				.'    ,bl.batch_log_id '
    				.'    ,b.batch_name '
    				.'    ,b.service_id '
    				.'    ,bl.start_date '
    				.'    ,bl.state '
    				.'    ,bl.recovery_state '
    				.' FROM WK_BATCH_LOG as bl'
    				.'  INNER JOIN  WK_BATCH as b ON ( bl.batch_id = b.batch_id )'
    				.' WHERE batch_log_id = :batch_log_id ';

    		$params = array( 'batch_log_id' => (int)$param['batch_log_id'] );
    		
    		Log::query(sprintf('SQL::getBatch() query=%s',$sql));
    		Log::query(sprintf('SQL::getBatch() param:batch_log_id=%s',$param['batch_log_id']));
   
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
    	return $aryData;
    }
    
    /**
     * 取込検索結果のbatchデータ取得
     *
     * @param  array $param   パラメータ batch_id, contents_id
     * @return       $row  	　　WK_BATCH_LOG_CONTENTS_DETAILテーブル情報
     */
    public function getReuptake( $param )
    {
    	$start_time=microtime(true);
    	
    	$aryData = array();
    	try {
    		$sql =   'SELECT s.service_id '
    				.'    ,wb.import_type '
  					.'    ,wb.start_ym '
    				.'    ,wb.start_date '
    				.'    ,wd.batch_transaction_id '
    				.'    ,wd.contents_id '
    				.'    ,wd.contents_type '
    				//.'    ,wd.sub_id '
    				.'    ,wd.url '
    				.'    ,wd.comment '
    				.'    ,wd.title '
    				.'    ,wd.user '
    				.'    ,wd.create_date '
    				.'    ,s.monitoring_start_date '
    				.'    ,s.monitoring_end_date '
    				.'    ,(SELECT MAX(contents_id) FROM TRN_CONTENTS) as max_id '
    				.' FROM WK_BATCH_LOG as wb'
    				.' INNER JOIN WK_BATCH_LOG_CONTENTS as wc ON ( wb.batch_log_id = wc.batch_log_id ) '
    				.' INNER JOIN WK_BATCH_LOG_CONTENTS_DETAIL as wd ON ( wc.batch_transaction_id = wd.batch_transaction_id ) '
					.' INNER JOIN WK_BATCH as b ON ( wb.batch_id = b.batch_id )  '
    				.' INNER JOIN MST_SERVICE as s ON ( b.service_id = s.service_id ) '
    				.' WHERE wb.batch_log_id = :batch_log_id '
    				.'   AND wd.contents_id = :contents_id ';

    		$params = array( 'batch_log_id'=> (int)$param['batch_log_id'],
    						 'contents_id' => (int)$param['contents_id']
							);
    
    		Log::query(sprintf('SQL::getReuptake() query=%s',$sql));
    		Log::query(sprintf('SQL::getReuptake() param:batch_log_id=%s',$param['batch_log_id']));
    		Log::query(sprintf('SQL::getReuptake() param:contents_id=%s',$param['contents_id']));
    		
			$stmt = $this->adapter->createStatement($sql);
			$results = $stmt->execute($params);
    
    		if ($results instanceof ResultInterface && $results->isQueryResult()) {
    			$resultSet = new ResultSet;
    			$resultSet->initialize($results);
    			foreach ($resultSet as $row) {
    				$aryData = (array)$row;
    			}
    		}
    	}
    	catch( \ErrorException $ee ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getReuptake() --  do not select from WK_BATCH_LOG_CONTENTS_DETAIL Table.');
    		Log::error(__FILE__, __LINE__, $ee->getMessage());
    		throw new DbAccessException($ee->getMessage());
    	}
    	catch( \PDOException $pe ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getReuptake() --  do not select from WK_BATCH_LOG_CONTENTS_DETAIL Table.');
    		Log::error(__FILE__, __LINE__, $pe->getMessage());
    		throw new DbAccessException($pe->getMessage());
    	}
    	catch (\Exception $e) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getReuptake() --  do not select from WK_BATCH_LOG_CONTENTS_DETAIL Table.');
    		Log::error(__FILE__, __LINE__, $e->getMessage());
    		throw new DbAccessException($e->getMessage());
    	}
    	 
    	$diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
    	Log::debug(__FILE__, __LINE__, 'INFO   getReuptake() --  select from WK_BATCH_LOG_CONTENTS_DETAIL Table. get Batch Detail Data. ('.$diff_time.')');
    	return $aryData;
    }
    
    /**
     * バッチの再取込処理の反映
     *
     * @param  array $param  パラメータ
     * @return     　	　$row    ロックデータ
     */
    public function ReuptakeResult( $param )
    {
    	$start_time=microtime(true);
    	
    	$aryData = array();
    	$Rrow = 0;
    	try {
    		 
    		$sql =   ' UPDATE WK_BATCH_LOG '
    				.' SET recovery_state = :recovery_state '
 					.' WHERE batch_id = :batch_id ';
    
			$params = array( 'batch_id'			=> $param['batch_id'],
							 'recovery_state'	=> $param['recovery_state']
    						);
    
			Log::query(sprintf('SQL::ReuptakeResult() query=%s',$sql));
			Log::query(sprintf('SQL::ReuptakeResult() param:batch_id=%s',$param['batch_id']));
			Log::query(sprintf('SQL::ReuptakeResult() param:recovery_state=%s',$param['recovery_state']));
			
			$stmt = $this->adapter->createStatement($sql);
			$results = $stmt->execute($params);
    
			//結果の行数取得
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
    		Log::debug(__FILE__, __LINE__, 'ERROR ReuptakeResult() --  do not select from WK_BATCH_LOG Table.');
    		Log::error(__FILE__, __LINE__, $ee->getMessage());
    		throw new DbAccessException($ee->getMessage());
    	}
    	catch( \PDOException $pe ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR ReuptakeResult() --  do not select from WK_BATCH_LOG Table.');
    		Log::error(__FILE__, __LINE__, $pe->getMessage());
    		throw new DbAccessException($pe->getMessage());
    	}
    	catch ( \Exception $e){
    		Log::debug(__FILE__, __LINE__, 'ERROR ReuptakeResult() --  do not update from WK_BATCH_LOG Table.');
    		Log::error(__FILE__, __LINE__, $e->getMessage());
    		throw new DbAccessException($e->getMessage());
    	}
    
    	$diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
    	Log::debug(__FILE__, __LINE__, 'INFO   ReuptakeResult() --  update from WK_BATCH_LOG Table. update Batch (recovery_state). ('.$diff_time.')');
    	return $Rrow;
    }
    
    
    /**
     * 登録処理(WK_BATCH_LOG)
     * 
     * @param BatchLogData $wkBatch BatchLogDataクラス(エンティティクラス)
     * @throws \DbAccessException
     */
    public function saveWkBatchLog(BatchLogData $wkBatchLog)
    {
    	$start_time=microtime(true);
    	
    	try {
	    	$data = array(
	            'batch_id' => $wkBatchLog->batch_id,
	            'import_type' => $wkBatchLog->import_type,
	            'trigger_type' => $wkBatchLog->trigger_type,
	            'start_ym' => $wkBatchLog->start_ym,
	            'start_date' => $wkBatchLog->start_date,
	            'end_date' => $wkBatchLog->end_date,
	            'url' => $wkBatchLog->url,
	            'state' => $wkBatchLog->state,
	            'recovery_state' => $wkBatchLog->recovery_state,
	            'insert_date' => $wkBatchLog->insert_date,
	        );
	
	        $batch_log_id = (int) $wkBatchLog->batch_log_id;

	        Log::query(sprintf('SQL::saveWkBatchLog() param:batch_id=%s',$data['batch_id']));
	        Log::query(sprintf('SQL::saveWkBatchLog() param:import_type=%s',$data['import_type']));
	        Log::query(sprintf('SQL::saveWkBatchLog() param:trigger_type=%s',$data['trigger_type']));
	        Log::query(sprintf('SQL::saveWkBatchLog() param:start_ym=%s',$data['start_ym']));
	        Log::query(sprintf('SQL::saveWkBatchLog() param:start_date=%s',$data['start_date']));
	        Log::query(sprintf('SQL::saveWkBatchLog() param:end_date=%s',$data['end_date']));
	        Log::query(sprintf('SQL::saveWkBatchLog() param:url=%s',$data['url']));
	        Log::query(sprintf('SQL::saveWkBatchLog() param:state=%s',$data['state']));
	        Log::query(sprintf('SQL::saveWkBatchLog() param:recovery_state=%s',$data['recovery_state']));
	        Log::query(sprintf('SQL::saveWkBatchLog() param:insert_date=%s',$data['insert_date']));
	        Log::query(sprintf('SQL::saveWkBatchLog() param:batch_log_id=%s',$batch_log_id));
	        
	        if ($batch_log_id == 0) {
	            $this->tableGateway->insert($data);
	        } else {
	            if ($this->getWkBatchLog($batch_log_id)) {
	                $this->tableGateway->update($data, array('batch_log_id' => $batch_log_id));
	            } else {
	                throw new \DbAccessException('WkBatchLog batch_log_id does not exist');
	            }
	        }
	
    	}
    	catch( \ErrorException $ee ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR saveWkBatchLog() --  do not select from WK_BATCH_LOG Table.');
    		Log::error(__FILE__, __LINE__, $ee->getMessage());
    		throw new DbAccessException($ee->getMessage());
    	}
    	catch( \PDOException $pe ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR saveWkBatchLog() --  do not select from WK_BATCH_LOG Table.');
    		Log::error(__FILE__, __LINE__, $pe->getMessage());
    		throw new DbAccessException($pe->getMessage());
    	}
    	catch ( \Exception $e){
    		Log::debug(__FILE__, __LINE__, 'ERROR saveWkBatchLog() --  do not insert from WK_BATCH_LOG Table.');
    		Log::error(__FILE__, __LINE__, $e->getMessage());
    		throw new DbAccessException($e->getMessage());
    	}
    	 
    	$diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
    	Log::debug(__FILE__, __LINE__, 'INFO   saveWkBatchLog() --  insert from WK_BATCH_LOG Table. insert Batch Data. ('.$diff_time.')');
    }

}