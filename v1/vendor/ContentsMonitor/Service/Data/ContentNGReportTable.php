<?php
namespace ContentsMonitor\Service\Data;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\AdapterAwareInterface;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\Exception\ErrorException;

use ContentsMonitor\Service\Entity\ContentNGReportData;
use ContentsMonitor\Common\LogClass as Log;
use ContentsMonitor\Common\UtilityClass as Utility;
use ContentsMonitor\Exception\DbAccessExceptionClass as DbAccessException;


/**
 * 監視サイトで使用するDBアクセス処理（TRN_CONTENTS_NG_REPORT）
 *
 * @package ContentsMonitor
 * @author  FST goto
 * @since   PHP 5.6, Zendframwork 2.4.9
 * @version 1.0.0
 */
class ContentNGReportTable extends AbstractLocalAdapter implements AdapterAwareInterface
{

	/**
	 * DBアクセス用のモジュールの設定（Adapter, TableGateway）
	 * 
	 * @param  Adapter $adapter   DBアダプタオブジェクト
	 */
    public function setDbAdapter(Adapter $adapter)
    {
        $resultSetPrototype = new ResultSet();
        $resultSetPrototype->setArrayObjectPrototype(new ContentNGReportData());
        $this->tableGateway = new TableGateway('TRN_CONTENTS_NG_REPORT', $adapter, null, $resultSetPrototype);
        $this->adapter = $adapter;
    }
    
    /**
     * 既に存在しているか確認
     *
     * @param  array $param   パラメータ
     * @return     $count  カウント
     */
    public function getContentsNGReport_count($param)
    {
    	$start_time=microtime(true);
    	 
    	$aryData = array();
    	try {
    		 
    		$sql =   'SELECT COUNT(0) AS sumcnt FROM TRN_CONTENTS_NG_REPORT '
    				.' WHERE contents_id = :contents_id '
    				.' AND contents_inner_id = :contents_inner_id ';
    
    		$params = array( 'contents_id'			=> $param['contents_id'],
    						'contents_inner_id'		=> $param['contents_inner_id'],
    		);
    		
    		Log::query(sprintf('SQL::getContentsNGReport_count() query=%s',$sql));
    		Log::query(sprintf('SQL::getContentsNGReport_count() param:contents_id=%s',$param['contents_id']));
    		Log::query(sprintf('SQL::getContentsNGReport_count() param:contents_inner_id=%s',$param['contents_inner_id']));
    
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
    		Log::debug(__FILE__, __LINE__, 'ERROR getContentsNGReport_count() --  do not select from TRN_CONTENTS_NG_REPORT Table.');
    		Log::error(__FILE__, __LINE__, $ee->getMessage());
    		throw new DbAccessException($ee->getMessage());
    	}
    	catch( \PDOException $pe ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getContentsNGReport_count() --  do not select from TRN_CONTENTS_NG_REPORT Table.');
    		Log::error(__FILE__, __LINE__, $pe->getMessage());
    		throw new DbAccessException($pe->getMessage());
    	}
        catch ( Exception $e ) {
        	Log::debug(__FILE__, __LINE__, 'ERROR getContentsNGReport_count() --  do not select from TRN_CONTENTS_NG_REPORT Table.');
        	Log::error(__FILE__, __LINE__, $e->getMessage());
    		throw new DbAccessException($e->getMessage());
        }
        
        $diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
        Log::debug(__FILE__, __LINE__, 'INFO   getContentsNGReport_count() --  select from TRN_CONTENTS_NG_REPORT Table. get Contents Ng Report Data. ('.$diff_time.')');
	   	return $aryData['sumcnt'];
    }

    /**
     * 既に存在しているか確認
     *
     * @param  array $param   パラメータ
     * @return     $count  カウント
     */
    public function getContentsNGReportToUserId_count($param)
    {
    	$start_time=microtime(true);
    
    	$aryData = array();
    	try {
    		 
    		$sql =   'SELECT COUNT(0) AS sumcnt FROM TRN_CONTENTS_NG_REPORT '
    				.' WHERE monitor_user_id = :user_id ';
    
			$params = array( 'user_id' => $param['user_id'] );
    
			Log::query(sprintf('SQL::getContentsNGReportToUserId_count() query=%s',$sql));
			Log::query(sprintf('SQL::getContentsNGReportToUserId_count() param:user_id=%s',$param['user_id']));
			
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
    		Log::debug(__FILE__, __LINE__, 'ERROR getContentsNGReportToUserId_count() --  do not select from TRN_CONTENTS_NG_REPORT Table.');
    		Log::error(__FILE__, __LINE__, $ee->getMessage());
    		throw new DbAccessException($ee->getMessage());
    	}
    	catch( \PDOException $pe ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getContentsNGReportToUserId_count() --  do not select from TRN_CONTENTS_NG_REPORT Table.');
    		Log::error(__FILE__, __LINE__, $pe->getMessage());
    		throw new DbAccessException($pe->getMessage());
    	}
    	catch ( Exception $e ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getContentsNGReportToUserId_count() --  do not select from TRN_CONTENTS_NG_REPORT Table.');
    		Log::error(__FILE__, __LINE__, $e->getMessage());
    		throw new DbAccessException($e->getMessage());
    	}
    	
    	$diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
    	Log::debug(__FILE__, __LINE__, 'INFO   getContentsNGReportToUserId_count() --  select from TRN_CONTENTS_NG_REPORT Table. get Contents Ng Report Data. ('.$diff_time.')');
    	return $aryData['sumcnt'];
    }

    /**
     * 既に存在しているか確認
     *
     * @return     $count  カウント
     */
    public function getContentsNGReportToMonitorFlag()
    {
    	$start_time=microtime(true);
    
    	$aryData = array();
    	try {
    		 
    		$sql =  'SELECT COUNT(0) AS sumcnt  '
    			.	' FROM TRN_CONTENTS_NG_REPORT '
    			.   ' WHERE monitoring = 0 ';
    
			Log::query(sprintf('SQL::getContentsNGReportToMonitorFlag() query=%s',$sql));

			$stmt = $this->adapter->createStatement($sql);
    		$results = $stmt->execute();
    
			if ($results instanceof ResultInterface && $results->isQueryResult()) {
    			$resultSet = new ResultSet;
    			$resultSet->initialize($results);
    			foreach ($resultSet as $row) {
    				$aryData = $row;
    			}
    		}
    	}
    	catch( \ErrorException $ee ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getContentsNGReportToMonitorFlag() --  do not select from TRN_CONTENTS_NG_REPORT Table.');
    		Log::error(__FILE__, __LINE__, $ee->getMessage());
    		throw new DbAccessException($ee->getMessage());
    	}
    	catch( \PDOException $pe ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getContentsNGReportToMonitorFlag() --  do not select from TRN_CONTENTS_NG_REPORT Table.');
    		Log::error(__FILE__, __LINE__, $pe->getMessage());
    		throw new DbAccessException($pe->getMessage());
    	}
    	catch ( Exception $e ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getContentsNGReportToMonitorFlag() --  do not select from TRN_CONTENTS_NG_REPORT Table.');
    		Log::error(__FILE__, __LINE__, $e->getMessage());
    		throw new DbAccessException($e->getMessage());
    	}
    	 
    	$diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
    	Log::debug(__FILE__, __LINE__, 'INFO   getContentsNGReportToMonitorFlag() --  select from TRN_CONTENTS_NG_REPORT Table. get Contents Ng Report Data. ('.$diff_time.')');
    	return $aryData['sumcnt'];
    }
    
	/**
	 * コンテンツ監視結果（NG）をセット
	 * 
	 * @param  int $param   パラメータ
	 * @return     $Rrow  insert行数
	 */
    public function setContentsNGReport($param)
    {
    	$start_time=microtime(true);
    	
    	$aryData = array();
    	try {
    		 
    		$sql =   'INSERT INTO TRN_CONTENTS_NG_REPORT( '
    				.' contents_id '
    				.' ,contents_type '
    				.' ,contents_inner_id '
    				.' ,monitor_user_id '
    				.' ,monitor_date '
    				.' ,access_token '
    				.' ,insert_date )'
    				.' VALUES( '
    				.' :contents_id '
    				.' ,:contents_type '
    				.' ,:contents_inner_id '
    				.' ,:monitor_user_id '
    				.' ,:monitor_date '
    				.' ,:token '
    				.' ,:insert_date )';

    		$params = array( 'contents_id'			=> $param['contents_id'],
    						'contents_type'			=> $param['contents_type'],
    						'contents_inner_id'		=> $param['contents_inner_id'],
    						'monitor_user_id'		=> $param['check_user'],
    						'monitor_date'			=> $param['check_date'],
    						'insert_date'			=> $param['update_date'],
    						'token'					=> $param['token'],
    						);
    		
    		Log::query(sprintf('SQL::setContentsNGReport() query=%s',$sql));
    		Log::query(sprintf('SQL::setContentsNGReport() param:contents_id=%s',$param['contents_id']));
    		Log::query(sprintf('SQL::setContentsNGReport() param:contents_type=%s',$param['contents_type']));
    		Log::query(sprintf('SQL::setContentsNGReport() param:contents_inner_id=%s',$param['contents_inner_id']));
    		Log::query(sprintf('SQL::setContentsNGReport() param:monitor_user_id=%s',$param['check_user']));
    		Log::query(sprintf('SQL::setContentsNGReport() param:monitor_date=%s',$param['check_date']));
    		Log::query(sprintf('SQL::setContentsNGReport() param:insert_date=%s',$param['update_date']));
    		Log::query(sprintf('SQL::setContentsNGReport() param:token=%s',$param['token']));
    		
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
    		Log::debug(__FILE__, __LINE__, 'ERROR setContentsNGReport() --  do not select from TRN_CONTENTS_NG_REPORT Table.');
    		Log::error(__FILE__, __LINE__, $ee->getMessage());
    		throw new DbAccessException($ee->getMessage());
    	}
    	catch( \PDOException $pe ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR setContentsNGReport() --  do not select from TRN_CONTENTS_NG_REPORT Table.');
    		Log::error(__FILE__, __LINE__, $pe->getMessage());
    		throw new DbAccessException($pe->getMessage());
    	}
    	catch ( Exception $e ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR setContentsNGReport() --  do not insert from TRN_CONTENTS_NG_REPORT Table.');
    		Log::error(__FILE__, __LINE__, $e->getMessage());
    		throw new DbAccessException($e->getMessage());
    		////insert失敗・・・０が返るはず
    		//$Rrow = $stmt->getResource()->rowCount();
    	}
    	 
    	$diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
    	Log::debug(__FILE__, __LINE__, 'INFO   setContentsNGReport() --  insert from TRN_CONTENTS_NG_REPORT Table. insert Contents Ng Report Data. ('.$diff_time.')');
    	return $Rrow;
    }
    
    /**
	 * コンテンツ監視結果（NG）を更新
	 * 
	 * @param  int $param   パラメータ
	 * @return     $Rrow  update行数
	 */
    public function setContentsNGReport_update( $param )
    {
    	$start_time=microtime(true);
    
    	$aryData = array();
    	try {
    		 
    		$sql =   'UPDATE TRN_CONTENTS_NG_REPORT SET '
    				.'   monitor_user_id = :monitor_user_id, '
    				.'   monitor_date = :monitor_date '
    				.' WHERE 0 = 0 '
    				.' AND contents_id = :contents_id '
    				.' AND contents_inner_id = :contents_inner_id ';

    		$params = array( 'monitor_user_id'		=> $param['check_user'],
    						 'monitor_date'			=> $param['check_date'],
    						 'contents_id'			=> $param['contents_id'],
							 'contents_inner_id'	=> $param['contents_inner_id'] );

    		Log::query(sprintf('SQL::setContentsNGReport_update() query=%s',$sql));
    		Log::query(sprintf('SQL::setContentsNGReport_update() param:monitor_user_id=%s',$param['check_user']));
    		Log::query(sprintf('SQL::setContentsNGReport_update() param:monitor_date=%s',$param['check_date']));
    		Log::query(sprintf('SQL::setContentsNGReport_update() param:contents_id=%s',$param['contents_id']));
    		Log::query(sprintf('SQL::setContentsNGReport_update() param:contents_inner_id=%s',$param['contents_inner_id']));
    		
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
    		Log::debug(__FILE__, __LINE__, 'ERROR setContentsNGReport_update() --  do not select from TRN_CONTENTS_NG_REPORT Table.');
    		Log::error(__FILE__, __LINE__, $ee->getMessage());
    		throw new DbAccessException($ee->getMessage());
    	}
    	catch( \PDOException $pe ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR setContentsNGReport_update() --  do not select from TRN_CONTENTS_NG_REPORT Table.');
    		Log::error(__FILE__, __LINE__, $pe->getMessage());
    		throw new DbAccessException($pe->getMessage());
    	}
    	catch ( Exception $e ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR setContentsNGReport_update() --  do not update from TRN_CONTENTS_NG_REPORT Table.');
    		Log::error(__FILE__, __LINE__, $e->getMessage());
    		throw new DbAccessException($e->getMessage());
    		////insert失敗・・・０が返るはず
    		//$Rrow = $stmt->getResource()->rowCount();
    	}
    	
    	$diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
    	Log::debug(__FILE__, __LINE__, 'INFO   setContentsNGReport_update() --  update from TRN_CONTENTS_NG_REPORT Table. update Contents Ng Report Data. ('.$diff_time.')');
    	return $Rrow;
    }
    
    /**
     * 報告データ取得
     *
     * @param  array $param   トークン
     * @return     $array  データ
     */
    public function getReportDate($param)
    {
    	$start_time=microtime(true);
    
    	$aryData = array();
    	try {
    		 
    		$sql =   'SELECT s.service_id '
    				.' ,c.monitoring_start_date '
    				.' ,c.monitoring_end_date '
    				.' ,c.import_date '
    				.' ,c.contents_id '
    				.' ,c.contents_type '
    				.' ,cd.url '
    				.' ,cd.create_date '
    				.' ,cd.check_result '
    				.' ,cd.check_note '
    				.' FROM TRN_CONTENTS_NG_REPORT AS cng '
    				.' INNER JOIN trn_contents AS c ON ( cng.contents_id = c.contents_id) '
    				.' INNER JOIN trn_contents_detail AS cd ON ( cng.contents_id = cd.contents_id) '
    				.' INNER JOIN mst_service AS s ON ( c.service_id = s.service_id) '
    				.' WHERE cng.access_token = :token ';

    		$params = array( 'token'			=> $param,
    						);

    		Log::query(sprintf('SQL::getReportDate() query=%s',$sql));
    		Log::query(sprintf('SQL::getReportDate() param:token=%s',$param));
    		
    		$stmt = $this->adapter->createStatement($sql);
    		$results = $stmt->execute($params);

    		if ($results instanceof ResultInterface && $results->isQueryResult()) {
    			$resultSet = new ResultSet;
    			$resultSet->initialize($results);
    			foreach ($resultSet as $row) {
    				//$aryData = $row;
    				array_push($aryData, (array)$row);
    			}
    		}
    
    	}
    	catch( \ErrorException $ee ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getReportDate() --  do not select from TRN_CONTENTS_NG_REPORT Table.');
    		Log::error(__FILE__, __LINE__, $ee->getMessage());
    		throw new DbAccessException($ee->getMessage());
    	}
    	catch( \PDOException $pe ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getReportDate() --  do not select from TRN_CONTENTS_NG_REPORT Table.');
    		Log::error(__FILE__, __LINE__, $pe->getMessage());
    		throw new DbAccessException($pe->getMessage());
    	}
    	catch ( Exception $e ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getReportDate() --  do not select from TRN_CONTENTS_NG_REPORT Table.');
    		Log::error(__FILE__, __LINE__, $e->getMessage());
    		throw new DbAccessException($e->getMessage());
    	}
    	 
    	$diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
    	Log::debug(__FILE__, __LINE__, 'INFO   getReportDate() --  select from TRN_CONTENTS_NG_REPORT Table. select Contents Ng Report Data. ('.$diff_time.')');
    	return $aryData;
    }
    
}