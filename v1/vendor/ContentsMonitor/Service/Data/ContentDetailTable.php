<?php
namespace ContentsMonitor\Service\Data;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\AdapterAwareInterface;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\Exception\ErrorException;

use ContentsMonitor\Service\Entity\ContentDetailData;
use ContentsMonitor\Common\LogClass as Log;
use ContentsMonitor\Common\UtilityClass as Utility;
use ContentsMonitor\Exception\DbAccessExceptionClass as DbAccessException;


/**
 * 監視サイトで使用するDBアクセス処理（TRN_CONTENTS_DETAIL）
 *
 * @package ContentsMonitor
 * @author  FST goto
 * @since   PHP 5.6, Zendframwork 2.4.9
 * @version 1.0.0
 */
class ContentDetailTable extends AbstractLocalAdapter implements AdapterAwareInterface
{

	/**
	 * DBアクセス用のモジュールの設定（Adapter, TableGateway）
	 * 
	 * @param  Adapter $adapter   DBアダプタオブジェクト
	 */
    public function setDbAdapter(Adapter $adapter)
    {
        $resultSetPrototype = new ResultSet();
        $resultSetPrototype->setArrayObjectPrototype(new ContentDetailData());
        $this->tableGateway = new TableGateway('TRN_CONTENTS_DETAIL', $adapter, null, $resultSetPrototype);
        $this->adapter = $adapter;
    }
    
	/**
	 * コンテンツ詳細情報を取得
	 * 
	 * @param  int $id   ユーザID
	 * @return     $row  TRN_CONTENTS_DETAILテーブル情報
	 */
    public function getContentsDetail($id)
    {
    	$start_time=microtime(true);
    	
    	$row;
    	try {
	    	$id  = (int) $id;
	    	
	    	Log::query(sprintf('SQL::getContentsDetail() param:id=%s',$id));
	    	
	        $rowset = $this->tableGateway->select(array('contents_id' => $id));
	        $row = $rowset->current();
	        if (!$row) {
	            throw new \DbAccessException("Could not find row $id");
	        }
        }
    	catch( \ErrorException $ee ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getContentsDetail() --  do not select from TRN_CONTENTS_DETAIL Table.');
    		Log::error(__FILE__, __LINE__, $ee->getMessage());
    		throw new DbAccessException($ee->getMessage());
    	}
    	catch( \PDOException $pe ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getContentsDetail() --  do not select from TRN_CONTENTS_DETAIL Table.');
    		Log::error(__FILE__, __LINE__, $pe->getMessage());
    		throw new DbAccessException($pe->getMessage());
    	}
        catch ( Exception $e ) {
        	Log::debug(__FILE__, __LINE__, 'ERROR getContentsDetail() --  do not select from TRN_CONTENTS_DETAIL Table.');
        	Log::error(__FILE__, __LINE__, $e->getMessage());
    		throw new DbAccessException($e->getMessage());
        }
        
        $diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
        Log::debug(__FILE__, __LINE__, 'INFO   getContentsDetail() --  select from TRN_CONTENTS_DETAIL Table. get Contents Data. ('.$diff_time.')');
        return $row;
    }
    
    /**
     * コンテンツ詳細情報を取得(コンテンツID)
     * 
     * @param int $contents_id コンテンツID
     * @return ArrayObject|NULL
     */
    public function getTrnContentsDetailByContentsId($contents_id)
    {
    	$start_time=microtime(true);
    	
    	$row;
    	try {
	    	$contents_id = (int) $contents_id;
	    	
	    	Log::query(sprintf('SQL::getTrnContentsDetailByContentsId() param:contents_id=%s',$contents_id));
	    	
	    	$rowset = $this->tableGateway->select(array('contents_id' => $contents_id));
	    	$row = $rowset->current();
	    	if (!$row) {
	    		//throw new DbAccessException("Could not find row $contents_id");
	    	}
        }
    	catch( \ErrorException $ee ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getTrnContentsDetailByContentsId() --  do not select from TRN_CONTENTS_DETAIL Table.');
    		Log::error(__FILE__, __LINE__, $ee->getMessage());
    		throw new DbAccessException($ee->getMessage());
    	}
    	catch( \PDOException $pe ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getTrnContentsDetailByContentsId() --  do not select from TRN_CONTENTS_DETAIL Table.');
    		Log::error(__FILE__, __LINE__, $pe->getMessage());
    		throw new DbAccessException($pe->getMessage());
    	}
        catch ( Exception $e ) {
        	Log::debug(__FILE__, __LINE__, 'ERROR getTrnContentsDetailByContentsId() --  do not select from TRN_CONTENTS_DETAIL Table.');
        	Log::error(__FILE__, __LINE__, $e->getMessage());
    		throw new DbAccessException($e->getMessage());
        }
        
        $diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
        Log::debug(__FILE__, __LINE__, 'INFO   getTrnContentsDetailByContentsId() --  select from TRN_CONTENTS_DETAIL Table. get Contents Data. ('.$diff_time.')');
    	return $row;
    }
    
    /**
	 * コンテンツ詳細情報に紐づくINNERIDを取得する
	 * 
	 * @param  array $Id     コンテンツID
	 * @return     　	　$nId    コンテンツ内部ID
     */
    public function getContentsDetailTakeInnerId( $param )
    {
    	$start_time=microtime(true);

    	$aryData = array();
		try {
    		 
	    	$sql =  'SELECT contents_parent_id '
				   .' FROM TRN_CONTENTS_DETAIL '
				   .' WHERE contents_id = :contents_id ';
	    	
			$params = array( 'contents_id'   => (int)$param );

			Log::query(sprintf('SQL::getContentsDetailTakeInnerId() query=%s',$sql));
			Log::query(sprintf('SQL::getContentsDetailTakeInnerId() param:contents_id=%s',$param));
			
			$stmt = $this->adapter->createStatement($sql);
			$results = $stmt->execute($params);
	
	        if ($results instanceof ResultInterface && $results->isQueryResult()) {
			    $resultSet = new ResultSet;
			    $resultSet->initialize($results);
			    foreach ($resultSet as $row) {
			    	$aryData = (array)$row;
			    }
	        }
    		unset($results);
		}
    	catch( \ErrorException $ee ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getContentsDetailTakeInnerId() --  do not select from TRN_CONTENTS_DETAIL Table.');
    		Log::error(__FILE__, __LINE__, $ee->getMessage());
    		throw new DbAccessException($ee->getMessage());
    	}
    	catch( \PDOException $pe ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getContentsDetailTakeInnerId() --  do not select from TRN_CONTENTS_DETAIL Table.');
    		Log::error(__FILE__, __LINE__, $pe->getMessage());
    		throw new DbAccessException($pe->getMessage());
    	}
        catch ( \Exception $e ) {
        	Log::debug(__FILE__, __LINE__, 'ERROR getContentsDetailTakeInnerId() --  do not select from TRN_CONTENTS_DETAIL Table.');
        	Log::error(__FILE__, __LINE__, $e->getMessage());
    		throw new DbAccessException($e->getMessage());
        }
        
        $diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
        Log::debug(__FILE__, __LINE__, 'INFO   getContentsDetailTakeInnerId() --  select from TRN_CONTENTS_DETAIL Table. get Contents Data. ('.$diff_time.')');
        return $aryData['contents_parent_id'];
    }

    /**
     * コンテンツ詳細情報を取得
     *
     * @param  array $param   パラメータ
     * @return       $row     TRN_CONTENTS_DETAILテーブル情報
     */
    public function getContentDetailData( $param )
    {
    	$start_time=microtime(true);
    
    	$aryData = array();
    	try {
    		 
    		$sql =   /*'SET @rownum=0; '
    				.*/'SELECT @rownum:=@rownum+1 as ROW_NUM'
    				.'    ,s.service_name '
    				.'    ,c.contents_id '
    				.'    ,c.contents_type '
    				.'    ,c.import_date '
    				.'    ,cd.title '
    				.'    ,cd.user '
    				.'    ,cd.comment '
    				.'    ,cd.create_date '
    				.'    ,cd.url '
    				.'    ,cd.format '
    				.'    ,cd.check_state '
    				.'    ,cd.check_result '
    				.'    ,cd.check_note '
    				.'    ,cd.contents_inner_id '
    				.'    ,cd.contents_parent_id '
    				.'    ,cd.sub_id '
    				.' FROM (SELECT @rownum:=0) as dummy ,TRN_CONTENTS as c '
    				.' INNER JOIN TRN_CONTENTS_DETAIL as cd ON ( cd.contents_id = c.contents_id ) '
    				.' INNER JOIN MST_SERVICE as s ON ( c.service_id = s.service_id ) '
    				.' WHERE 0=0 ';

    		if( !empty($param['contents_parent_id']) ) {
    			$sql .= '   AND cd.contents_parent_id = :contents_parent_id';
    		} 
    		else {
    			$sql .= '   AND cd.contents_id = :contents_id';
    		}
    		$sql .= '  ORDER BY cd.sub_id ';
    		
    		$params = array();
    	    if( !empty($param['contents_parent_id']) ) { 
    	    	$params = array( 'contents_parent_id' => $param['contents_parent_id'] ); 
    	    } 
    		else {
    			$params = array( 'contents_id' => $param['contents_id'] );
    		}

    		Log::query(sprintf('SQL::getContentDetailData() query=%s',$sql));
    		if( !empty($param['contents_inner_id']) ) {
    			Log::query(sprintf('SQL::getContentDetailData() param:contents_inner_id=%s',$param['contents_inner_id']));
    		}
    		else {
    			Log::query(sprintf('SQL::getContentDetailData() param:contents_id=%s',$param['contents_id']));
    		}
    		
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
    		unset($results);

    	}
    	catch( \ErrorException $ee ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getContentDetailData() --  do not select from TRN_CONTENTS_DETAIL Table.');
    		Log::error(__FILE__, __LINE__, $ee->getMessage());
    		throw new DbAccessException($ee->getMessage());
    	}
    	catch( \PDOException $pe ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getContentDetailData() --  do not select from TRN_CONTENTS_DETAIL Table.');
    		Log::error(__FILE__, __LINE__, $pe->getMessage());
    		throw new DbAccessException($pe->getMessage());
    	}
        catch ( \Exception $e ) {
        	Log::debug(__FILE__, __LINE__, 'ERROR getContentDetailData() --  do not select from TRN_CONTENTS_DETAIL Table.');
        	Log::error(__FILE__, __LINE__, $e->getMessage());
    		throw new DbAccessException($e->getMessage());
        }
        
        $diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
        Log::debug(__FILE__, __LINE__, 'INFO   getContentDetailData() --  select from TRN_CONTENTS_DETAIL Table. get Contents Data. ('.$diff_time.')');
    	return $aryData;
    }
    
    /**
     * コンテンツ詳細情報のロックデータを取得 (排他処理)
     *
     * @param  array $Id     コンテンツID
     * @return     　	　$row    ロックデータ
     */
    public function getContentsDetailTakeLockdata( $param )
    {
    	$start_time=microtime(true);
    	 
    	$aryData = array();
    	try {
    		 
    		$sql =  'SELECT cd.lock_user, cd.lock_date, u.user_name, u.user_id '
    				.' FROM TRN_CONTENTS_DETAIL as cd '
    				.' INNER JOIN MST_USER as u ON ( cd.lock_user = u.user_id ) '
					.' WHERE cd.contents_id = :contents_id ';
    
    		$params = array( 'contents_id'   => (int)$param['contents_id'] );
    		
    		Log::query(sprintf('SQL::getContentsDetailTakeLockdata() query=%s',$sql));
    		Log::query(sprintf('SQL::getContentsDetailTakeLockdata() param:contents_id=%s',$param['contents_id']));
    		
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
    		Log::debug(__FILE__, __LINE__, 'ERROR getContentsDetailTakeLockdata() --  do not select from TRN_CONTENTS_DETAIL Table.');
    		Log::error(__FILE__, __LINE__, $ee->getMessage());
    		throw new DbAccessException($ee->getMessage());
    	}
    	catch( \PDOException $pe ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getContentsDetailTakeLockdata() --  do not select from TRN_CONTENTS_DETAIL Table.');
    		Log::error(__FILE__, __LINE__, $pe->getMessage());
    		throw new DbAccessException($pe->getMessage());
    	}
    	catch ( Exception $e ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getContentsDetailTakeLockdata() --  do not select from TRN_CONTENTS_DETAIL Table.');
    		Log::error(__FILE__, __LINE__, $e->getMessage());
    		throw new DbAccessException($e->getMessage());
    	}
    	 
        $diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
        Log::debug(__FILE__, __LINE__, 'INFO   getContentsDetailTakeLockdata() --  select from TRN_CONTENTS_DETAIL Table. get Contents Lock Data. ('.$diff_time.')');
    	return $aryData;
    }
    
    /**
     * コンテンツ詳細情報のロックデータを設定 (排他処理)
     *
     * @param  array $Id     コンテンツID
     * @return     　	　$row    ロックデータ
     */
    public function setContentsDetailTakeLockdata( $param )
    {
		$start_time=microtime(true);
    
    	$aryData = array();
    	try {
    		 
    		$sql =   'UPDATE TRN_CONTENTS_DETAIL SET '
    			    .'   lock_user = :lock_user, '
    			    .'   lock_date = NOW() '
    				.' WHERE 0 = 0 ';
    		
    		if( !empty($param['contents_inner_id']) ) { $sql .= '   AND contents_inner_id = :contents_inner_id'; }
    		else { $sql .= '   AND contents_id = :contents_id'; }

    	    $params = array();
    	    if( !empty($param['contents_inner_id']) ) { 
    	    	$params = array( 'lock_user' => $param['lock_user'],
    	    					 'contents_inner_id' => $param['contents_inner_id'] );
    	    } 
    		else {
    			$params = array( 'lock_user' => $param['lock_user'],
    	    					 'contents_id' => $param['contents_id'] );
    		}
    		
    		Log::query(sprintf('SQL::setContentsDetailTakeLockdata() query=%s',$sql));
    		Log::query(sprintf('SQL::setContentsDetailTakeLockdata() param:lock_user=%s',$param['lock_user']));
    		Log::query(sprintf('SQL::setContentsDetailTakeLockdata() param:contents_id=%s',$param['contents_id']));
    		Log::query(sprintf('SQL::setContentsDetailTakeLockdata() param:contents_inner_id=%s',$param['contents_inner_id']));
    		
    		$stmt = $this->adapter->createStatement($sql);
			$results = $stmt->execute($params);
			
			//結果の行数取得
			$Rrow = $stmt->getResource()->rowCount();

/*			if ($results instanceof ResultInterface && $results->isQueryResult()) {
				$resultSet = new ResultSet;
				$resultSet->initialize($results);
				foreach ($resultSet as $row) {
					$aryData = $row;
				}
			}
*/
    
    	}
    	catch( \ErrorException $ee ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR setContentsDetailTakeLockdata() --  do not select from TRN_CONTENTS_DETAIL Table.');
    		Log::error(__FILE__, __LINE__, $ee->getMessage());
    		throw new DbAccessException($ee->getMessage());
    	}
    	catch( \PDOException $pe ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR setContentsDetailTakeLockdata() --  do not select from TRN_CONTENTS_DETAIL Table.');
    		Log::error(__FILE__, __LINE__, $pe->getMessage());
    		throw new DbAccessException($pe->getMessage());
    	}
    	catch ( Exception $e ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR setContentsDetailTakeLockdata() --  do not update from TRN_CONTENTS_DETAIL Table.');
    		Log::error(__FILE__, __LINE__, $e->getMessage());
    		throw new DbAccessException($e->getMessage());
    	}
    	 
        $diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
        Log::debug(__FILE__, __LINE__, 'INFO   setContentsDetailTakeLockdata() --  update from TRN_CONTENTS_DETAIL Table. update Contents Lock Data. ('.$diff_time.')');
    	return $Rrow;
    }
    
    /**
     * コンテンツ詳細情報のロックデータを解除(排他処理)
     *
     * @param  array $Id     コンテンツID
     * @return     　	　$row    ロックデータ
     */
    public function setContentsDetailUnlock( $param )
    {
		$start_time=microtime(true);
    	
    	$aryData = array();
    	try {
    		 
    		$sql =   'UPDATE TRN_CONTENTS_DETAIL SET '
    				.'   lock_user = null, '
    				.'   lock_date = null '
    				.' WHERE 0 = 0 '
    				.' AND lock_user = :user_id ';

    		$params = array( 'user_id' => $param['user_id']);
    		
    		if( !empty($param['contents_inner_id']) ) { $sql .= '   AND contents_inner_id = :contents_inner_id'; }
    		else { $sql .= '   AND contents_id = :contents_id'; }

    		if( !empty($param['contents_inner_id']) ) {
    			$params = array( 'user_id' => $param['user_id'],
    					'contents_inner_id' => $param['contents_inner_id'] );
    			}
    		else {
    			$params = array( 'user_id' => $param['user_id'],
    					'contents_id' => $param['contents_id'] );
    		}

    		Log::query(sprintf('SQL::setContentsDetailUnlock() query=%s',$sql));
    		Log::query(sprintf('SQL::setContentsDetailUnlock() param:lock_user=%s',$param['user_id']));
    		Log::query(sprintf('SQL::setContentsDetailUnlock() param:contents_id=%s',$param['contents_id']));
    		Log::query(sprintf('SQL::setContentsDetailUnlock() param:contents_inner_id=%s',$param['contents_inner_id']));
    		
    		$stmt = $this->adapter->createStatement($sql);
    		$results = $stmt->execute($params);

    		//結果の行数取得
    		$Rrow = $stmt->getResource()->rowCount();

/*    		if ($results instanceof ResultInterface && $results->isQueryResult()) {
    			$resultSet = new ResultSet;
    			$resultSet->initialize($results);
    			foreach ($resultSet as $row) {
    				$aryData = $row;
    			}
    		}
*/    
    	}
    	catch( \ErrorException $ee ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR setContentsDetailUnlock() --  do not select from TRN_CONTENTS_DETAIL Table.');
    		Log::error(__FILE__, __LINE__, $ee->getMessage());
    		throw new DbAccessException($ee->getMessage());
    	}
    	catch( \PDOException $pe ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR setContentsDetailUnlock() --  do not select from TRN_CONTENTS_DETAIL Table.');
    		Log::error(__FILE__, __LINE__, $pe->getMessage());
    		throw new DbAccessException($pe->getMessage());
    	}
    	catch ( Exception $e ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR setContentsDetailUnlock() --  do not update from TRN_CONTENTS_DETAIL Table.');
    		Log::error(__FILE__, __LINE__, $e->getMessage());
    		throw new DbAccessException($e->getMessage());
    		//update失敗・・・０が返るはず
    		//$Rrow = $stmt->getResource()->rowCount();
    	}
    	
    	$diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
    	Log::debug(__FILE__, __LINE__, 'INFO   setContentsDetailUnlock() --  update from TRN_CONTENTS_DETAIL Table. update Contents Lock Data. ('.$diff_time.')');
    	return $Rrow;
    }
    
    /**
     * コンテンツ詳細情報のロックデータを解除(排他処理)
     *
     * @param  array $Id     コンテンツID
     * @return     　	　$row    ロックデータ
     */
    public function setContentsDetailUnlockUserAll( $param )
    {
		$start_time=microtime(true);
    
    	$aryData = array();
    	try {
    		 
    		$sql =   'UPDATE TRN_CONTENTS_DETAIL SET '
    				.'   lock_user = null, '
    				.'   lock_date = null '
    				.' WHERE 0 = 0 '
    				.' AND lock_user = :user_id ';

    		$params = array( 'user_id' => $param['user_id']);

    		Log::query(sprintf('SQL::setContentsDetailUnlockUserAll() query=%s',$sql));
    		Log::query(sprintf('SQL::setContentsDetailUnlockUserAll() param:user_id=%s',$param['user_id']));
    		
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
    		Log::debug(__FILE__, __LINE__, 'ERROR setContentsDetailUnlockUserAll() --  do not select from TRN_CONTENTS_DETAIL Table.');
    		Log::error(__FILE__, __LINE__, $ee->getMessage());
    		throw new DbAccessException($ee->getMessage());
    	}
    	catch( \PDOException $pe ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR setContentsDetailUnlockUserAll() --  do not select from TRN_CONTENTS_DETAIL Table.');
    		Log::error(__FILE__, __LINE__, $pe->getMessage());
    		throw new DbAccessException($pe->getMessage());
    	}
    	catch ( \Exception $e){
			Log::debug(__FILE__, __LINE__, 'ERROR setContentsDetailUnlockUserAll() --  do not update from TRN_CONTENTS_DETAIL Table.');
    		Log::error(__FILE__, __LINE__, $e->getMessage());
    		throw new \DbAccessException($e->getMessage());
    		//update失敗・・・０が返るはず
    		//$Rrow = $stmt->getResource()->rowCount();
    	}
    
        $diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
    	Log::debug(__FILE__, __LINE__, 'INFO   setContentsDetailUnlockUserAll() --  update from TRN_CONTENTS_DETAIL Table. update Contents Lock Data. ('.$diff_time.')');
    	return $Rrow;
    }
    
    /**
     * バッチの再取込処理
     *
     * @param  array $Id     コンテンツID
     * @return     　	　$row    ロックデータ
     */
    public function setContentsDetailReuptake( $param )
    {
    	$start_time=microtime(true);
    
    	$aryData = array();
    	try {
    		 
    		$sql =   'INSERT INTO TRN_CONTENTS_DETAIL( '
    				.' contents_id '
    				.' ,contents_type '
    				.' ,contents_inner_id '
    				.' ,sub_id '
    				.' ,url '
    				.' ,comment '
    				.' ,title '
    				.' ,user '
    				.' ,create_date '
    				.' ,insert_user '
    				.' ,insert_date) '
    				.' VALUES( '
    				.' :contents_id '
    				.' ,:contents_type '
    				.' ,:contents_inner_id '
    				.' ,:sub_id '
    				.' ,:url '
    				.' ,:comment '
    				.' ,:title '
    				.' ,:user '
    				.' ,:create_date '
    				.' ,:insert_user '
    				.' ,:insert_date) ';

    		$params = array( 'contents_id'			=> $param['max_id'],
    						'contents_type'			=> $param['contents_type'],
    						'contents_inner_id'		=> (string)$param['contents_id'],
    						'sub_id'				=> $param['sub_id'],
    						'url'					=> $param['url'],
    						'comment'				=> $param['comment'],
    						'title'					=> $param['title'],
    						'user'					=> $param['user'],
    						'create_date'			=> $param['create_date'],
    						'insert_user'			=> $param['user_id'],
    						'insert_date'			=> $param['insert_date'],
    						);
    		
    		Log::query(sprintf('SQL::setContentsDetailReuptake() query=%s',$sql));
    		Log::query(sprintf('SQL::setContentsDetailReuptake() param:contents_id=%s',$param['max_id']));
    		Log::query(sprintf('SQL::setContentsDetailReuptake() param:contents_type=%s',$param['contents_type']));
    		Log::query(sprintf('SQL::setContentsDetailReuptake() param:contents_inner_id=%s',$param['contents_id']));
    		Log::query(sprintf('SQL::setContentsDetailReuptake() param:sub_id=%s',$param['sub_id']));
    		Log::query(sprintf('SQL::setContentsDetailReuptake() param:url=%s',$param['url']));
    		Log::query(sprintf('SQL::setContentsDetailReuptake() param:comment=%s',$param['comment']));
    		Log::query(sprintf('SQL::setContentsDetailReuptake() param:title=%s',$param['title']));
    		Log::query(sprintf('SQL::setContentsDetailReuptake() param:user=%s',$param['user']));
    		Log::query(sprintf('SQL::setContentsDetailReuptake() param:create_date=%s',$param['create_date']));
    		Log::query(sprintf('SQL::setContentsDetailReuptake() param:insert_user=%s',$param['user_id']));
    		Log::query(sprintf('SQL::setContentsDetailReuptake() param:insert_date=%s',$param['insert_date']));
    		
    		$stmt = $this->adapter->createStatement($sql);
    		$results = $stmt->execute($params);
    		
    		//結果の行数取得
//    		echo("<pre>");
//   		var_dump($stmt->getResource()->rowCount());
//    		echo("</pre>");
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
    		Log::debug(__FILE__, __LINE__, 'ERROR setContentsDetailReuptake() --  do not select from TRN_CONTENTS_DETAIL Table.');
    		Log::error(__FILE__, __LINE__, $ee->getMessage());
    		throw new DbAccessException($ee->getMessage());
    	}
    	catch( \PDOException $pe ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR setContentsDetailReuptake() --  do not select from TRN_CONTENTS_DETAIL Table.');
    		Log::error(__FILE__, __LINE__, $pe->getMessage());
    		throw new DbAccessException($pe->getMessage());
    	}
    	catch ( \Exception $e){
    		Log::debug(__FILE__, __LINE__, 'ERROR setContentsDetailReuptake() --  do not insert from TRN_CONTENTS_DETAIL Table.');
    		Log::error(__FILE__, __LINE__, $e->getMessage());
    		throw new \DbAccessException($e->getMessage());
    		//update失敗・・・０が返るはず
    		//$Rrow = $stmt->getResource()->rowCount();
    	}
    	
    	$diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
    	Log::debug(__FILE__, __LINE__, 'INFO   setContentsDetailUnlockUserAll() --  insert from TRN_CONTENTS_DETAIL Table. insert Contents Lock Data. ('.$diff_time.')');
    	return $Rrow;
    }
    
    /**
     * コンテンツ詳細情報の監視結果更新
     *
     * @param  array $param		パラメータ
     * @return     　	　$Rrow		更新行数
     */
    public function setContentsDetailCheckUpdate( $param )
    {
    	$start_time=microtime(true);
    
    	$aryData = array();
    	try {
    		 
    		$sql =   'UPDATE TRN_CONTENTS_DETAIL SET '
    				.'   check_state = :check_state, '
    				.'   check_result = :check_result, '
    				.'   check_note = :check_note, '
    				.'   check_user = :check_user, '
    				.'   check_date = :check_date, '
    				.'   update_user = :update_user, '
    				.'   update_date = :update_date '
    				.' WHERE 0 = 0 ';
    		
    		$params = array( 'check_state'	=>	$param['check_state'],
    						 'check_result'	=>	$param['check_result'],
    						 'check_note'	=>	$param['check_note'],
    						 'check_user'	=>	$param['check_user'],
    						 'check_date'	=>	$param['check_date'],
    						 'update_user'	=>	$param['update_user'],
    						 'update_date'	=>	$param['update_date'],
    		);

    		if(isset($param['contents_parent_id'])){
    			$sql .= ' AND contents_parent_id = :contents_parent_id ';
    			$params['contents_parent_id'] = $param['contents_parent_id'];
    		}
    		else {
    			$sql .= ' AND contents_id = :contents_id ';
    			$params['contents_id'] = $param['contents_id'];
    		}
    		
    		Log::query(sprintf('SQL::setContentsDetailCheckUpdate() query=%s',$sql));
    		Log::query(sprintf('SQL::setContentsDetailCheckUpdate() param:check_state=%s',$param['check_state']));
    		Log::query(sprintf('SQL::setContentsDetailCheckUpdate() param:check_result=%s',$param['check_result']));
    		Log::query(sprintf('SQL::setContentsDetailCheckUpdate() param:check_note=%s',$param['check_note']));
    		Log::query(sprintf('SQL::setContentsDetailCheckUpdate() param:check_user=%s',$param['check_user']));
    		Log::query(sprintf('SQL::setContentsDetailCheckUpdate() param:check_date=%s',$param['check_date']));
    		Log::query(sprintf('SQL::setContentsDetailCheckUpdate() param:update_user=%s',$param['update_user']));
    		Log::query(sprintf('SQL::setContentsDetailCheckUpdate() param:update_date=%s',$param['update_date']));
    		
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
    		Log::debug(__FILE__, __LINE__, 'ERROR setContentsDetailCheckUpdate() --  do not select from TRN_CONTENTS_DETAIL Table.');
    		Log::error(__FILE__, __LINE__, $ee->getMessage());
    		throw new DbAccessException($ee->getMessage());
    	}
    	catch( \PDOException $pe ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR setContentsDetailCheckUpdate() --  do not select from TRN_CONTENTS_DETAIL Table.');
    		Log::error(__FILE__, __LINE__, $pe->getMessage());
    		throw new DbAccessException($pe->getMessage());
    	}
    	catch ( \Exception $e){
    		Log::debug(__FILE__, __LINE__, 'ERROR setContentsDetailCheckUpdate() --  do not update from TRN_CONTENTS_DETAIL Table.');
    		Log::error(__FILE__, __LINE__, $e->getMessage());
    		throw new \DbAccessException($e->getMessage());
    		//update失敗・・・０が返るはず
    		//$Rrow = $stmt->getResource()->rowCount();
    	}
    	 
    	$diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
    	Log::debug(__FILE__, __LINE__, 'INFO   setContentsDetailCheckUpdate() --  update from TRN_CONTENTS_DETAIL Table. update Contents Status Data. ('.$diff_time.')');
    	return $Rrow;
    }

    /**
     * 登録処理(TRN_CONTENTS_DETAIL)
     * 
     * @param ContentDetailData $trnContentsDetail ContentDetailDataクラス(エンティティクラス)
     * @throws DbAccessException
     */
    public function saveTrnContentsDetail(ContentDetailData $trnContentsDetail)
    {
    	$start_time=microtime(true);
    	
    	$data = array();
    	try {
	    		 
	    	$data = array(
	    			'contents_id' => $trnContentsDetail->contents_id,
	    			'contents_type' => $trnContentsDetail->contents_type,
	    			'contents_inner_id' => $trnContentsDetail->contents_inner_id,
	    			'contents_parent_id' => $trnContentsDetail->contents_parent_id,
	    			'sub_id' => $trnContentsDetail->sub_id,
	    			'url' => $trnContentsDetail->url,
	    			'format' => $trnContentsDetail->format,
	    			'comment' => $trnContentsDetail->comment,
	    			'title' => $trnContentsDetail->title,
	    			'caption' => $trnContentsDetail->caption,
	    			'user' => $trnContentsDetail->user,
	    			'create_date' => $trnContentsDetail->create_date,
	    			'check_state' => $trnContentsDetail->check_state,
	    			'check_result' => $trnContentsDetail->check_result,
	    			'check_note' => $trnContentsDetail->check_note,
	    			'check_user' => $trnContentsDetail->check_user,
	    			'check_date' => $trnContentsDetail->check_date,
	    			'lock_user' => $trnContentsDetail->lock_user,
	    			'lock_date' => $trnContentsDetail->lock_date,
	    			'insert_user' => $trnContentsDetail->insert_user,
	    			'insert_date' => $trnContentsDetail->insert_date,
	    			'update_user' => $trnContentsDetail->update_user,
	    			'update_date' => $trnContentsDetail->update_date,
	    	);
	    
	    	Log::query(sprintf('SQL::saveTrnContentsDetail() param:contents_id=%s',$data['contents_id']));
	    	Log::query(sprintf('SQL::saveTrnContentsDetail() param:contents_type=%s',$data['contents_type']));
	    	Log::query(sprintf('SQL::saveTrnContentsDetail() param:contents_inner_id=%s',$data['contents_inner_id']));
	    	Log::query(sprintf('SQL::saveTrnContentsDetail() param:contents_parent_id=%s',$data['contents_parent_id']));
	    	Log::query(sprintf('SQL::saveTrnContentsDetail() param:sub_id=%s',$data['sub_id']));
	    	Log::query(sprintf('SQL::saveTrnContentsDetail() param:url=%s',$data['url']));
	    	Log::query(sprintf('SQL::saveTrnContentsDetail() param:format=%s',$data['format']));
	    	Log::query(sprintf('SQL::saveTrnContentsDetail() param:comment=%s',$data['comment']));
	    	Log::query(sprintf('SQL::saveTrnContentsDetail() param:title=%s',$data['title']));
	    	Log::query(sprintf('SQL::saveTrnContentsDetail() param:caption=%s',$data['caption']));
	    	Log::query(sprintf('SQL::saveTrnContentsDetail() param:user=%s',$data['user']));
	    	Log::query(sprintf('SQL::saveTrnContentsDetail() param:create_date=%s',$data['create_date']));
	    	Log::query(sprintf('SQL::saveTrnContentsDetail() param:check_state=%s',$data['check_state']));
	    	Log::query(sprintf('SQL::saveTrnContentsDetail() param:check_result=%s',$data['check_result']));
	    	Log::query(sprintf('SQL::saveTrnContentsDetail() param:check_note=%s',$data['check_note']));
	    	Log::query(sprintf('SQL::saveTrnContentsDetail() param:check_user=%s',$data['check_user']));
	    	Log::query(sprintf('SQL::saveTrnContentsDetail() param:check_date=%s',$data['check_date']));
	    	Log::query(sprintf('SQL::saveTrnContentsDetail() param:lock_user=%s',$data['lock_user']));
	    	Log::query(sprintf('SQL::saveTrnContentsDetail() param:lock_date=%s',$data['lock_date']));
	    	Log::query(sprintf('SQL::saveTrnContentsDetail() param:insert_user=%s',$data['insert_user']));
	    	Log::query(sprintf('SQL::saveTrnContentsDetail() param:insert_date=%s',$data['insert_date']));
	    	Log::query(sprintf('SQL::saveTrnContentsDetail() param:update_user=%s',$data['update_user']));
	    	Log::query(sprintf('SQL::saveTrnContentsDetail() param:update_date=%s',$data['update_date']));
	    	
	    	$contents_id = (int) $trnContentsDetail->contents_id;
	    	if ($contents_id == 0 || !$this->getTrnContentsDetailByContentsId($contents_id)) {
	    		$this->tableGateway->insert($data);
	    	} else {
	    		$this->tableGateway->update($data, array('contents_id' => $contents_id));
	    	}
    	}
    	catch( \ErrorException $ee ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR saveTrnContentsDetail() --  do not select from TRN_CONTENTS_DETAIL Table.');
    		Log::error(__FILE__, __LINE__, $ee->getMessage());
    		throw new DbAccessException($ee->getMessage());
    	}
    	catch( \PDOException $pe ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR saveTrnContentsDetail() --  do not select from TRN_CONTENTS_DETAIL Table.');
    		Log::error(__FILE__, __LINE__, $pe->getMessage());
    		throw new DbAccessException($pe->getMessage());
    	}
    	catch ( \Exception $e){
    		Log::debug(__FILE__, __LINE__, 'ERROR saveTrnContentsDetail() --  do not update from TRN_CONTENTS_DETAIL Table.');
    		Log::error(__FILE__, __LINE__, $e->getMessage());
    		throw new \DbAccessException($e->getMessage());
    	}
    	
    	$diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
    	Log::debug(__FILE__, __LINE__, 'INFO   saveTrnContentsDetail() --  update from TRN_CONTENTS_DETAIL Table. update Contents Data. ('.$diff_time.')');
   	}
}