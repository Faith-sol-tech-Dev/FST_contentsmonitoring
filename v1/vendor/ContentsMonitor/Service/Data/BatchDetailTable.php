<?php
namespace ContentsMonitor\Service\Data;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\AdapterAwareInterface;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\Exception\ErrorException;

use ContentsMonitor\Service\Entity\BatchDetailData;
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
class BatchDetailTable extends AbstractLocalAdapter implements AdapterAwareInterface
{

	/**
	 * DBアクセス用のモジュールの設定（Adapter, TableGateway）
	 * @param  Adapter $adapter   DBアダプタオブジェクト
	 */
    public function setDbAdapter(Adapter $adapter)
    {
        $resultSetPrototype = new ResultSet();
        $resultSetPrototype->setArrayObjectPrototype(new BatchDetailData());
        $this->tableGateway = new TableGateway('WK_BATCH_DETAIL', $adapter, null, $resultSetPrototype);
        $this->adapter = $adapter;
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
    
    		$sql =   'SELECT COUNT(0) AS sumcnt FROM WK_BATCH_DETAIL as wd '
    				.' WHERE batch_id = :batch_id ';

    		$params = array('batch_id'     => (int)$param['batch_id']);
    		
    		if(isset($param['content_state']) && $param['content_state'] != 0){
    			$sql = $sql.' AND import_state = :content_state';
    			$params['content_state'] = (int)$param['content_state'];
    		}
    		
    		Log::query(sprintf('SQL::getBatchDetailListCount() query=%s',$sql));
    		Log::query(sprintf('SQL::getBatchDetailListCount() param:batch_id=%s',$param['batch_id']));
    		if(isset($param['content_state']) && $param['content_state'] != 0){
    			Log::query(sprintf('SQL::getBatchDetailListCount() param:content_state=%s',$param['content_state']));
    		}

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
    		Log::debug(__FILE__, __LINE__, 'ERROR getBatchDetailListCount() --  do not select from WK_BATCH_DETAIL Table.');
    		Log::error(__FILE__, __LINE__, $ee->getMessage());
    		throw new DbAccessException($ee->getMessage());
    	}
    	catch( \PDOException $pe ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getBatchDetailListCount() --  do not select from WK_BATCH_DETAIL Table.');
    		Log::error(__FILE__, __LINE__, $pe->getMessage());
    		throw new DbAccessException($pe->getMessage());
    	}
    	catch (\Exception $e) {
        	Log::debug(__FILE__, __LINE__, 'ERROR getBatchDetailListCount() --  do not select from WK_BATCH_DETAIL Table.');
        	Log::error(__FILE__, __LINE__, $e->getMessage());
    		throw new DbAccessException($e->getMessage());
    	}

        $diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
    	Log::debug(__FILE__, __LINE__, 'INFO   getBatchDetailListCount() --  select from WK_BATCH_DETAIL Table. get Batch Data. ('.$diff_time.')');
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
    				.'    ,batch_id '
    				.'    ,contents_id '
    				.'    ,title '
    				.'    ,recovery_state '
    				.'    ,import_state '
    				.' FROM (SELECT @rownum:=0) as dummy ,WK_BATCH_DETAIL '
    				.' WHERE batch_id = :batch_id ';
    
    		$params = array( 'batch_id'   => (int)$param['batch_id'] );
    		
    		if(isset($param['content_state']) && $param['content_state'] != 0){
    			$sql = $sql.' AND import_state = :content_state';
    			$params['content_state'] = (int)$param['content_state'];
    		}
    		
    		Log::query(sprintf('SQL::getBatchDetailList() query=%s',$sql));
    		Log::query(sprintf('SQL::getBatchDetailList() param:batch_id=%s',$param['batch_id']));
    		if(isset($param['content_state']) && $param['content_state'] != 0){
    			Log::query(sprintf('SQL::getBatchDetailList() param:content_state=%s',$param['content_state']));
    		}
    
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
    		 
    		$sql =   'SELECT batch_id '
    				.'    ,wd.contents_id '
    				.'    ,wd.movie_url '
    				.'    ,wd.image_url '
    				.'    ,wd.comment '
    				.'    ,wd.title '
    				.'    ,wd.name '
    				.'    ,wd.create_date '
    				.'    ,wd.service_id '
    				.'    ,wd.import_date '
    				.'    ,wd.error_reason '
    				.'    ,wd.recovery_state '
    				.'    ,s.service_name '
    				.' FROM WK_BATCH_DETAIL as wd '
    				.' INNER JOIN MST_SERVICE as s ON ( wd.service_id = s.service_id ) '
    				.' WHERE wd.batch_id = :batch_id '
    				.' AND wd.contents_id = :contents_id ';

    		$params = array( 'batch_id'   => (int)$param['batch_id'],
    						 'contents_id' => (int)$param['contents_id']
    		);
    		
    		Log::query(sprintf('SQL::getBatchDetail() query=%s',$sql));
    		Log::query(sprintf('SQL::getBatchDetail() param:batch_id=%s',$param['batch_id']));
    		Log::query(sprintf('SQL::getBatchDetail() param:contents_id=%s',$param['contents_id']));

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
    		 
    		$sql =   ' UPDATE WK_BATCH_DETAIL '
    				.' SET recovery_state = 54 '
    				.' ,recovery_date = :recovery_date '
    				.' ,recovery_user = :user_id '
    				.' ,update_date = :update_date '
    				.' WHERE batch_id = :batch_id '
    				.' AND contents_id = :contents_id ';

    		$params = array( 'recovery_date'		=> date("Y-m-d H:i:s"),
    						 'user_id'				=> $param['user_id'],
    						 'update_date'			=> date("Y-m-d H:i:s"),
    						 'batch_id'				=> $param['batch_id'],
    						 'contents_id'			=> $param['contents_id'],
    						);
    
    		Log::query(sprintf('SQL::ReuptakeDetailResult() query=%s',$sql));
    		Log::query(sprintf('SQL::ReuptakeDetailResult() param:recovery_date=%s',$params['recovery_date']));
    		Log::query(sprintf('SQL::ReuptakeDetailResult() param:user_id=%s',$params['user_id']));
    		Log::query(sprintf('SQL::ReuptakeDetailResult() param:update_date=%s',$params['update_date']));
    		Log::query(sprintf('SQL::ReuptakeDetailResult() param:batch_id=%s',$params['batch_id']));
    		Log::query(sprintf('SQL::ReuptakeDetailResult() param:contents_id=%s',$params['contents_id']));
    		
    		$stmt = $this->adapter->createStatement($sql);
    		$results = $stmt->execute($params);

    		//結果の行数取得
    		echo("<pre>");
    		var_dump($stmt->getResource()->rowCount());
    		echo("</pre>");
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