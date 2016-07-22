<?php
namespace ContentsMonitor\Service\Data;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\AdapterAwareInterface;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\Exception\ErrorException;

use ContentsMonitor\Service\Entity\ContentData;
use ContentsMonitor\Common\LogClass as Log;
use ContentsMonitor\Common\UtilityClass as Utility;
use ContentsMonitor\Exception\DbAccessExceptionClass as DbAccessException;



/**
 * 監視サイトで使用するDBアクセス処理（TRN_CONTENTS）
 *
 * @package ContentsMonitor
 * @author  FST goto
 * @since   PHP 5.6, Zendframwork 2.4.9
 * @version 1.0.0
 */
class ContentTable extends AbstractLocalAdapter implements AdapterAwareInterface
{

	/**
	 * DBアクセス用のモジュールの設定（Adapter, TableGateway）
	 * @param  Adapter $adapter   DBアダプタオブジェクト
	 */
    public function setDbAdapter(Adapter $adapter)
    {
        $resultSetPrototype = new ResultSet();
        $resultSetPrototype->setArrayObjectPrototype(new ContentData());
        $this->tableGateway = new TableGateway('TRN_CONTENTS', $adapter, null, $resultSetPrototype);
        $this->adapter = $adapter;
    }
    
	/**
	 * コンテンツ情報を取得
	 * @param  int $id   コンテンツID
	 * @return     $row  TRN_CONTENTSテーブル情報
	 */
    public function getContents($id)
    {
    	$start_time=microtime(true);

    	$row;
    	try {
	        $id  = (int) $id;

	        Log::query(sprintf('SQL::getContents() param:id=%s',$id));
	        
	        $rowset = $this->tableGateway->select(array('contents_id' => $id));
	        $row = $rowset->current();
	        if (!$row) {
	            throw new \DbAccessException("Could not find row $id");
	        }
    	}
    	catch( \ErrorException $ee ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getContents() --  do not select from TRN_CONTENTS Table.');
    		Log::error(__FILE__, __LINE__, $ee->getMessage());
    		throw new DbAccessException($ee->getMessage());
    	}
    	catch( \PDOException $pe ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getContents() --  do not select from TRN_CONTENTS Table.');
    		Log::error(__FILE__, __LINE__, $pe->getMessage());
    		throw new DbAccessException($pe->getMessage());
    	}
        catch ( \Exception $e ) {
        	Log::debug(__FILE__, __LINE__, 'ERROR getContents() --  do not select from TRN_CONTENTS Table.');
        	Log::error(__FILE__, __LINE__, $e->getMessage());
    		throw new DbAccessException($e->getMessage());
        }
        
        $diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
        Log::debug(__FILE__, __LINE__, 'INFO   getContents() --  select from TRN_CONTENTS Table. get Contents Data. ('.$diff_time.')');
    	return $row;
    }

	/**
	 * コンテンツ情報を取得(コンテンツID指定)
	 * @param int $contents_id コンテンツID
	 */
    public function getTrnContentsByContentsId($contents_id)
    {
    	$start_time=microtime(true);

    	$row;
    	try {
	    	$contents_id = (int) $contents_id;

	    	Log::query(sprintf('SQL::getTrnContentsByContentsId() param:contents_id=%s',$contents_id));
	    	
	    	$rowset = $this->tableGateway->select(array('contents_id' => $contents_id));
	    	$row = $rowset->current();
	    	if (!$row) {
	    		throw new \DbAccessException("Could not find row $contents_id");
	    	}
    	}
        catch( \ErrorException $ee ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getTrnContentsByContentsId() --  do not select from TRN_CONTENTS Table.');
    		Log::error(__FILE__, __LINE__, $ee->getMessage());
    		throw new DbAccessException($ee->getMessage());
    	}
    	catch( \PDOException $pe ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getTrnContentsByContentsId() --  do not select from TRN_CONTENTS Table.');
    		Log::error(__FILE__, __LINE__, $pe->getMessage());
    		throw new DbAccessException($pe->getMessage());
    	}
        catch ( \Exception $e ) {
        	Log::debug(__FILE__, __LINE__, 'ERROR getTrnContentsByContentsId() --  do not select from TRN_CONTENTS Table.');
        	Log::error(__FILE__, __LINE__, $e->getMessage());
    		throw new DbAccessException($e->getMessage());
        }
        
        $diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
        Log::debug(__FILE__, __LINE__, 'INFO   getTrnContentsByContentsId() --  select from TRN_CONTENTS Table. get Contents Data. ('.$diff_time.')');
    	return $row;
    }
    
    /**
	 * コンテンツ検索結果（件数）を取得
	 * 
	 * @param  array $param   パラメータ
	 * @return       $row  TRN_CONTENTSテーブル情報
     */
    public function getContentDataCount( $param )
    {
    	$start_time=microtime(true);

   		$aryData = array();
    	try {

    		$sql =   'SELECT COUNT(0) AS sumcnt FROM TRN_CONTENTS as c '
    				.' INNER JOIN TRN_CONTENTS_DETAIL as cd ON ( cd.contents_id = c.contents_id ) '
    				.' INNER JOIN MST_SERVICE as s ON ( c.service_id = s.service_id ) '
    				.' WHERE 0=0 '
    				.'   AND c.service_id = :service_id ';
    		if(!empty($param['contents_type'])) { $sql .= '   AND c.contents_type = :contents_type '; }
    		if(!empty($param['check_state1']) || !empty($param['check_state3'])) {
    				$sql .= '   AND ( ';
    				if(!empty($param['check_state1'])) { $sql .= 'cd.check_state = :check_state1'; }
    		    	if(!empty($param['check_state3'])) { $sql .= (($param['check_state1'] == 1) ? ' OR ':'').'cd.check_state = :check_state3'; }
    				//if(!empty($param['check_state2'])) { $sql .= (!empty($param['check_state1']) ? ' OR ':'').'cd.check_state = :check_state2'; }
    				//if(!empty($param['check_state3'])) { $sql .= ((!empty($param['check_state1'])||!empty($param['check_state2'])) ? ' OR ':'').'cd.check_state = :check_state3'; }
    				$sql .= ' ) ';
    		}
    		elseif(!empty($param['check_state2'])) {
    				$sql .= '   AND lock_user is not null';
    		}
    	    if(!empty($param['check_result1']) || !empty($param['check_result2']) || !empty($param['check_result3'])) {
    				$sql .= '   AND ( ';
    				if(!empty($param['check_result1'])) { $sql .= 'cd.check_result = :check_result1'; }
    		    	if(!empty($param['check_result2'])) { $sql .= (!empty($param['check_result1']) ? ' OR ':'').'cd.check_result = :check_result2'; }
    				if(!empty($param['check_result3'])) { $sql .= ((!empty($param['check_result1'])||!empty($param['check_result2'])) ? ' OR ':'').'cd.check_result = :check_result3'; }
    				$sql .= ' ) ';
    		}
    	    if(!empty($param['import_date_min']) && !empty($param['import_date_max'])) {
    	    		$sql .= '   AND c.import_date between :import_date_min and :import_date_max ';
    	    }
    	    if(!empty($param['check_date_min']) && !empty($param['check_date_max'])) {
    	    		$sql .= '   AND cd.check_date between :check_date_min and :check_date_max ';
    	    }

			$params = array();
			$params['service_id'] = (int)$param['service_id'];
    	    if(!empty($param['contents_type']))   { $params['contents_type'] = (int)$param['contents_type']; }
			if(!empty($param['check_state1']))    { $params['check_state1'] = (int)$param['check_state1']; }
//    	   	if(!empty($param['check_state2']))    { $params['check_state2'] = (int)$param['check_state2']; }
    		if(!empty($param['check_state3']))    { $params['check_state3'] = (int)$param['check_state3']; }
			if(!empty($param['check_result1']))   { $params['check_result1'] = (int)$param['check_result1']; }
    		if(!empty($param['check_result2']))   { $params['check_result2'] = (int)$param['check_result2']; }
    		if(!empty($param['check_result3']))   { $params['check_result3'] = (int)$param['check_result3']; }
    	    if(!empty($param['import_date_min'])) { $params['import_date_min'] = date("Y-m-d H:i:s",strtotime($param['import_date_min'])); }
    	    if(!empty($param['import_date_max'])) { $params['import_date_max'] = date("Y-m-d H:i:s",strtotime($param['import_date_max'])); }
    	    if(!empty($param['check_date_min']))  { $params['check_date_min'] = date("Y-m-d H:i:s",strtotime($param['check_date_min'])); }
    	    if(!empty($param['check_date_max']))  { $params['check_date_max'] = date("Y-m-d H:i:s",strtotime($param['check_date_max'])); }
			
    	    Log::query(sprintf('SQL::getContentDataCount() query=%s',$sql));
    	    Log::query(sprintf('SQL::getContentDataCount() param:service_id=%s',$param['service_id']));
    	    Log::query(sprintf('SQL::getContentDataCount() param:contents_type=%s',$param['contents_type']));
    	    Log::query(sprintf('SQL::getContentDataCount() param:check_state1=%s',$param['check_state1']));
//    	    Log::query(sprintf('SQL::getContentDataCount() param:check_state2=%s',$param['check_state2']));
    	    Log::query(sprintf('SQL::getContentDataCount() param:check_state3=%s',$param['check_state3']));
    	    Log::query(sprintf('SQL::getContentDataCount() param:check_result1=%s',$param['check_result1']));
    	    Log::query(sprintf('SQL::getContentDataCount() param:check_result2=%s',$param['check_result2']));
    	    Log::query(sprintf('SQL::getContentDataCount() param:check_result3=%s',$param['check_result3']));
    	    Log::query(sprintf('SQL::getContentDataCount() param:import_date_min=%s',$param['import_date_min']));
    	    Log::query(sprintf('SQL::getContentDataCount() param:import_date_max=%s',$param['import_date_max']));
    	    Log::query(sprintf('SQL::getContentDataCount() param:check_date_min=%s',$param['check_date_min']));
    	    Log::query(sprintf('SQL::getContentDataCount() param:check_date_max=%s',$param['check_date_max']));
    	    	
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
    		Log::debug(__FILE__, __LINE__, 'ERROR getContentDataCount() --  do not select from TRN_CONTENTS Table.');
    		Log::error(__FILE__, __LINE__, $ee->getMessage());
    		throw new DbAccessException($ee->getMessage());
    	}
    	catch( \PDOException $pe ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getContentDataCount() --  do not select from TRN_CONTENTS Table.');
    		Log::error(__FILE__, __LINE__, $pe->getMessage());
    		throw new DbAccessException($pe->getMessage());
    	}
        catch ( \Exception $e ) {
        	Log::debug(__FILE__, __LINE__, 'ERROR getContentDataCount() --  do not select from TRN_CONTENTS Table.');
        	Log::error(__FILE__, __LINE__, $e->getMessage());
    		throw new DbAccessException($e->getMessage());
        }
        
        $diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
        Log::debug(__FILE__, __LINE__, 'INFO   getContentDataCount() --  select from TRN_CONTENTS Table. get Contents Data. ('.$diff_time.')');
        return $aryData["sumcnt"];
    }

    /**
     * コンテンツ検索結果を取得
     *
	 * @param  array $param   パラメータ
	 * @return       $row  TRN_CONTENTSテーブル情報
     */
    public function getContentData( $param )
    {
    	$start_time=microtime(true);

    	$aryData = array();
    	try {
    		 
    		$sql =   /*'SET @rownum=0; '
    				.*/'SELECT @rownum:=@rownum+1 as ROW_NUM'
    				.'    ,s.service_name '
    				.'    ,c.contents_id '
    				.'    ,c.contents_type '
    				.'    ,cd.title '
    				.'    ,cd.user '
    				.'    ,cd.comment '
    				.'    ,cd.create_date '
    				.'    ,cd.url '
    				.'    ,cd.check_state '
    				.'    ,cd.check_result '
    				.' FROM (SELECT @rownum:=0) as dummy ,TRN_CONTENTS as c '
    				.' INNER JOIN TRN_CONTENTS_DETAIL as cd ON ( cd.contents_id = c.contents_id ) '
    				.' INNER JOIN MST_SERVICE as s ON ( c.service_id = s.service_id ) '
    				.' WHERE 0=0 '
    				.'   AND c.service_id = :service_id ';
    		if(!empty($param['contents_type'])) { $sql .= '   AND c.contents_type = :contents_type '; }
    	    if(!empty($param['check_state1']) || !empty($param['check_state3'])) {
    				$sql .= '   AND ( ';
    				if(!empty($param['check_state1'])) { $sql .= 'cd.check_state = :check_state1'; }
    		    	if(!empty($param['check_state3'])) { $sql .= (($param['check_state1'] == 1) ? ' OR ':'').'cd.check_state = :check_state3'; }
    				//if(!empty($param['check_state2'])) { $sql .= (!empty($param['check_state1']) ? ' OR ':'').'cd.check_state = :check_state2'; }
    				//if(!empty($param['check_state3'])) { $sql .= ((!empty($param['check_state1'])||!empty($param['check_state2'])) ? ' OR ':'').'cd.check_state = :check_state3'; }
    				$sql .= ' ) ';
    		}
    		elseif(!empty($param['check_state2'])) {
    				$sql .= '   AND lock_user is not null';
    		}
    	    if(!empty($param['check_result1']) || !empty($param['check_result2']) || !empty($param['check_result3'])) {
    				$sql .= '   AND ( ';
    				if(!empty($param['check_result1'])) { $sql .= 'cd.check_result = :check_result1'; }
    		    	if(!empty($param['check_result2'])) { $sql .= (!empty($param['check_result1']) ? ' OR ':'').'cd.check_result = :check_result2'; }
    				if(!empty($param['check_result3'])) { $sql .= ((!empty($param['check_result1'])||!empty($param['check_result2'])) ? ' OR ':'').'cd.check_result = :check_result3'; }
    				$sql .= ' ) ';
    		}
    		if(!empty($param['import_date_min']) && !empty($param['import_date_max'])) {
    	    		$sql .= '   AND c.import_date between :import_date_min and :import_date_max ';
    	    }
    	    if(!empty($param['check_date_min']) && !empty($param['check_date_max'])) {
    	    		$sql .= '   AND cd.check_date between :check_date_min and :check_date_max ';
    	    }

			$params = array();
			$params['service_id'] = (int)$param['service_id'];
			if(!empty($param['contents_type']))   { $params['contents_type'] = (int)$param['contents_type']; }
			if(!empty($param['check_state1']))    { $params['check_state1'] = (int)$param['check_state1']; }
//    	   	if(!empty($param['check_state2']))    { $params['check_state2'] = (int)$param['check_state2']; }
    		if(!empty($param['check_state3']))    { $params['check_state3'] = (int)$param['check_state3']; }
			if(!empty($param['check_result1']))   { $params['check_result1'] = (int)$param['check_result1']; }
    		if(!empty($param['check_result2']))   { $params['check_result2'] = (int)$param['check_result2']; }
    		if(!empty($param['check_result3']))   { $params['check_result3'] = (int)$param['check_result3']; }
    	    if(!empty($param['import_date_min'])) { $params['import_date_min'] = date("Y-m-d H:i:s",strtotime($param['import_date_min'])); }
    	    if(!empty($param['import_date_max'])) { $params['import_date_max'] = date("Y-m-d H:i:s",strtotime($param['import_date_max'])); }
    	    if(!empty($param['check_date_min']))  { $params['check_date_min'] = date("Y-m-d H:i:s",strtotime($param['check_date_min'])); }
    	    if(!empty($param['check_date_max']))  { $params['check_date_max'] = date("Y-m-d H:i:s",strtotime($param['check_date_max'])); }
			
    	    Log::query(sprintf('SQL::getContentDataCount() query=%s',$sql));
    	    Log::query(sprintf('SQL::getContentDataCount() param:service_id=%s',$param['service_id']));
    	    Log::query(sprintf('SQL::getContentDataCount() param:contents_type=%s',$param['contents_type']));
    	    Log::query(sprintf('SQL::getContentDataCount() param:check_state1=%s',$param['check_state1']));
//    	    Log::query(sprintf('SQL::getContentDataCount() param:check_state2=%s',$param['check_state2']));
    	    Log::query(sprintf('SQL::getContentDataCount() param:check_state3=%s',$param['check_state3']));
    	    Log::query(sprintf('SQL::getContentDataCount() param:check_result1=%s',$param['check_result1']));
    	    Log::query(sprintf('SQL::getContentDataCount() param:check_result2=%s',$param['check_result2']));
    	    Log::query(sprintf('SQL::getContentDataCount() param:check_result3=%s',$param['check_result3']));
    	    Log::query(sprintf('SQL::getContentDataCount() param:import_date_min=%s',$param['import_date_min']));
    	    Log::query(sprintf('SQL::getContentDataCount() param:import_date_max=%s',$param['import_date_max']));
    	    Log::query(sprintf('SQL::getContentDataCount() param:check_date_min=%s',$param['check_date_min']));
    	    Log::query(sprintf('SQL::getContentDataCount() param:check_date_max=%s',$param['check_date_max']));
    	    
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
    		Log::debug(__FILE__, __LINE__, 'ERROR getContentData() --  do not select from TRN_CONTENTS Table.');
    		Log::error(__FILE__, __LINE__, $ee->getMessage());
    		throw new DbAccessException($ee->getMessage());
    	}
    	catch( \PDOException $pe ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getContentData() --  do not select from TRN_CONTENTS Table.');
    		Log::error(__FILE__, __LINE__, $pe->getMessage());
    		throw new DbAccessException($pe->getMessage());
    	}
        catch ( \Exception $e ) {
        	Log::debug(__FILE__, __LINE__, 'ERROR getContentData() --  do not select from TRN_CONTENTS Table.');
        	Log::error(__FILE__, __LINE__, $e->getMessage());
    		throw new DbAccessException($e->getMessage());
        }
        
        $diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
        Log::debug(__FILE__, __LINE__, 'INFO   getContentData() --  select from TRN_CONTENTS Table. get Contents Data. ('.$diff_time.')');
   		return $aryData;
    }
    
    /**
     * コンテンツ検索結果のIDリストを取得
     * 
	 * @param  array $param   パラメータ
	 * @return       $row  TRN_CONTENTSテーブル情報
     */
    public function getContentIdList( $param )
    {
    	$start_time=microtime(true);
    	 
    	$aryData = array();
    	try {
    		 
    		$sql =   /*'SET @rownum=0; '
    				.*/'SELECT @rownum:=@rownum+1 as ROW_NUM'
    				.'    ,s.service_name '
    				.'    ,c.contents_id '
    				.' FROM (SELECT @rownum:=0) as dummy ,TRN_CONTENTS as c '
    				.' INNER JOIN TRN_CONTENTS_DETAIL as cd ON ( cd.contents_id = c.contents_id ) '
    				.' INNER JOIN MST_SERVICE as s ON ( c.service_id = s.service_id ) '
    				.' WHERE 0=0 '
    				.'   AND c.service_id = :service_id ';
    		if(!empty($param['contents_type'])) { $sql .= '   AND c.contents_type = :contents_type '; }
			if(!empty($param['check_state1']) || !empty($param['check_state3'])) {
    				$sql .= '   AND ( ';
    				if(!empty($param['check_state1'])) { $sql .= 'cd.check_state = :check_state1'; }
    		    	if(!empty($param['check_state3'])) { $sql .= (($param['check_state1'] == 1) ? ' OR ':'').'cd.check_state = :check_state3'; }
    				//if(!empty($param['check_state2'])) { $sql .= (!empty($param['check_state1']) ? ' OR ':'').'cd.check_state = :check_state2'; }
    				//if(!empty($param['check_state3'])) { $sql .= ((!empty($param['check_state1'])||!empty($param['check_state2'])) ? ' OR ':'').'cd.check_state = :check_state3'; }
    				$sql .= ' ) ';
    		}
    		elseif(!empty($param['check_state2'])) {
    				$sql .= '   AND lock_user is not null';
    		}
    	    if(!empty($param['check_result1']) || !empty($param['check_result2']) || !empty($param['check_result3'])) {
    				$sql .= '   AND ( ';
    				if(!empty($param['check_result1'])) { $sql .= 'cd.check_result = :check_result1'; }
    		    	if(!empty($param['check_result2'])) { $sql .= (!empty($param['check_result1']) ? ' OR ':'').'cd.check_result = :check_result2'; }
    				if(!empty($param['check_result3'])) { $sql .= ((!empty($param['check_result1'])||!empty($param['check_result2'])) ? ' OR ':'').'cd.check_result = :check_result3'; }
    				$sql .= ' ) ';
    		}
    		if(!empty($param['import_date_min']) && !empty($param['import_date_max'])) {
    	    		$sql .= '   AND c.import_date between :import_date_min and :import_date_max ';
    	    }
    	    if(!empty($param['check_date_min']) && !empty($param['check_date_max'])) {
    	    		$sql .= '   AND cd.check_date between :check_date_min and :check_date_max ';
    	    }

			$params = array();
			$params['service_id'] = (int)$param['service_id'];
			if(!empty($param['contents_type']))   { $params['contents_type'] = (int)$param['contents_type']; }
			if(!empty($param['check_state1']))    { $params['check_state1'] = (int)$param['check_state1']; }
//    	   	if(!empty($param['check_state2']))    { $params['check_state2'] = (int)$param['check_state2']; }
    		if(!empty($param['check_state3']))    { $params['check_state3'] = (int)$param['check_state3']; }
			if(!empty($param['check_result1']))   { $params['check_result1'] = (int)$param['check_result1']; }
    		if(!empty($param['check_result2']))   { $params['check_result2'] = (int)$param['check_result2']; }
    		if(!empty($param['check_result3']))   { $params['check_result3'] = (int)$param['check_result3']; }
    	    if(!empty($param['import_date_min'])) { $params['import_date_min'] = date("Y-m-d H:i:s",strtotime($param['import_date_min'])); }
    	    if(!empty($param['import_date_max'])) { $params['import_date_max'] = date("Y-m-d H:i:s",strtotime($param['import_date_max'])); }
    	    if(!empty($param['check_date_min']))  { $params['check_date_min'] = date("Y-m-d H:i:s",strtotime($param['check_date_min'])); }
    	    if(!empty($param['check_date_max']))  { $params['check_date_max'] = date("Y-m-d H:i:s",strtotime($param['check_date_max'])); }

    	    Log::query(sprintf('SQL::getContentDataCount() query=%s',$sql));
    	    Log::query(sprintf('SQL::getContentDataCount() param:service_id=%s',$param['service_id']));
    	    Log::query(sprintf('SQL::getContentDataCount() param:contents_type=%s',$param['contents_type']));
    	    Log::query(sprintf('SQL::getContentDataCount() param:check_state1=%s',$param['check_state1']));
//    	    Log::query(sprintf('SQL::getContentDataCount() param:check_state2=%s',$param['check_state2']));
    	    Log::query(sprintf('SQL::getContentDataCount() param:check_state3=%s',$param['check_state3']));
    	    Log::query(sprintf('SQL::getContentDataCount() param:check_result1=%s',$param['check_result1']));
    	    Log::query(sprintf('SQL::getContentDataCount() param:check_result2=%s',$param['check_result2']));
    	    Log::query(sprintf('SQL::getContentDataCount() param:check_result3=%s',$param['check_result3']));
    	    Log::query(sprintf('SQL::getContentDataCount() param:import_date_min=%s',$param['import_date_min']));
    	    Log::query(sprintf('SQL::getContentDataCount() param:import_date_max=%s',$param['import_date_max']));
    	    Log::query(sprintf('SQL::getContentDataCount() param:check_date_min=%s',$param['check_date_min']));
    	    Log::query(sprintf('SQL::getContentDataCount() param:check_date_max=%s',$param['check_date_max']));
    	    	
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
    		Log::debug(__FILE__, __LINE__, 'ERROR getContentIdList() --  do not select from TRN_CONTENTS Table.');
    		Log::error(__FILE__, __LINE__, $ee->getMessage());
    		throw new DbAccessException($ee->getMessage());
    	}
    	catch( \PDOException $pe ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getContentIdList() --  do not select from TRN_CONTENTS Table.');
    		Log::error(__FILE__, __LINE__, $pe->getMessage());
    		throw new DbAccessException($pe->getMessage());
    	}
    	catch ( \Exception $e ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getContentIdList() --  do not select from TRN_CONTENTS Table.');
    		Log::error(__FILE__, __LINE__, $e->getMessage());
    		throw new DbAccessException($e->getMessage());
    	}
    	
    	$diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
    	Log::debug(__FILE__, __LINE__, 'INFO   getContentIdList() --  select from TRN_CONTENTS Table. get Contents Data. ('.$diff_time.')');
    	return $aryData;
    }
    
    /**
     * バッチの再取込処理
     *
     * @param  array $param  パラメータ
     * @return     　	　$row    ロックデータ
     */
    public function setContentsReuptake( $param )
    {
    	$start_time=microtime(true);
    
    	$aryData = array();
    	$Rrow = 0;
    	try {
    		 
    		$sql =   'INSERT INTO TRN_CONTENTS( '
    				.' contents_id '
    				.' ,contents_type '
    				.' ,service_id '
    				.' ,monitoring_start_date '
    				.' ,monitoring_end_date '
    				.' ,import_type '
    				.' ,import_ym '
    				.' ,import_date) '
    				.' VALUES( '
    				.' :max_id '
    				.' ,:contents_type '
    				.' ,:service_id '
    				.' ,:monitoring_start_date '
    				.' ,:monitoring_end_date '
    				.' ,:import_type '
    				.' ,:import_ym '
    				.' ,:import_date) ';

    		$params = array( 'max_id'				=> $param['max_id'],
    						'contents_type'			=> $param['contents_type'],
    						'service_id'			=> $param['service_id'],
    						'monitoring_start_date'	=> $param['monitoring_start_date'],
    						'monitoring_end_date'	=> $param['monitoring_end_date'],
    						'import_type'			=> $param['import_type'],
    						'import_ym'				=> $param['start_ym'],
    						'import_date'			=> $param['start_date'],
    						);

    		Log::query(sprintf('SQL::getContentDataCount() query=%s',$sql));
    		Log::query(sprintf('SQL::getContentDataCount() param:max_id=%s',$param['max_id']));
    		Log::query(sprintf('SQL::getContentDataCount() param:contents_type=%s',$param['contents_type']));
    		Log::query(sprintf('SQL::getContentDataCount() param:service_id=%s',$param['service_id']));
    		Log::query(sprintf('SQL::getContentDataCount() param:monitoring_start_date=%s',$param['monitoring_start_date']));
    		Log::query(sprintf('SQL::getContentDataCount() param:monitoring_end_date=%s',$param['monitoring_end_date']));
    		Log::query(sprintf('SQL::getContentDataCount() param:start_ym=%s',$param['start_ym']));
    		Log::query(sprintf('SQL::getContentDataCount() param:start_date=%s',$param['start_date']));
    		
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
    		Log::debug(__FILE__, __LINE__, 'ERROR setContentsReuptake() --  do not insert from TRN_CONTENTS Table.');
    		Log::error(__FILE__, __LINE__, $ee->getMessage());
    		throw new DbAccessException($ee->getMessage());
    	}
    	catch( \PDOException $pe ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR setContentsReuptake() --  do not insert from TRN_CONTENTS Table.');
    		Log::error(__FILE__, __LINE__, $pe->getMessage());
    		throw new DbAccessException($pe->getMessage());
    	}
    	catch ( \Exception $e ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR setContentsReuptake() --  do not insert from TRN_CONTENTS Table.');
    		Log::error(__FILE__, __LINE__, $e->getMessage());
    		throw new DbAccessException($e->getMessage());
    		//insert失敗・・・０が返るはず
    		//$Rrow = $stmt->getResource()->rowCount();
    	}
    	 
    	$diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
    	Log::debug(__FILE__, __LINE__, 'INFO   setContentsReuptake() --  insert from TRN_CONTENTS Table. insert Contents Data. ('.$diff_time.')');
    	return $Rrow;
    }
    
    /**
     * 集計結果（件数）を取得
     *
     * @param  array $param   パラメータ
     * @return       $row  TRN_CONTENTSテーブル情報
     */
    public function getAggregateCount( $param )
    {
    	$start_time=microtime(true);
    
    	$aryData = array();
    	try {
    
    		$sql =   'SELECT COUNT(DISTINCT import_date) AS sumcnt '
    				.' FROM TRN_CONTENTS ';

    		if(is_array($param)){
    			$sql = $sql.' WHERE service_id = :service_id'
    						.' AND ( contents_type = :contents_type_1 OR contents_type = :contents_type_2 OR contents_type = :contents_type_3 ) '
    						.' AND import_date BETWEEN :total_date_min AND :total_date_max ';
				$params = array(
    					'service_id' => $param['service_id'],
    					'contents_type_1' => $param['contents_type_1'],
    					'contents_type_2' => $param['contents_type_2'],
    					'contents_type_3' => $param['contents_type_3'],
    					'total_date_min' => date("Y-m-d H:i:s",strtotime($param['total_date_min'])),
    					'total_date_max' => date("Y-m-d H:i:s",strtotime($param['total_date_max'])),
    			);
    		}else{
    			$sql = $sql.' WHERE service_id = :service_id';
    			$params = array(
    					'service_id' => $param,
    			);
    		}
    		
    		Log::query(sprintf('SQL::getAggregateCount() query=%s',$sql));
    		if(is_array($param)){
	    		Log::query(sprintf('SQL::getAggregateCount() param:lock_user=%s',$params['lock_user']));
	    		Log::query(sprintf('SQL::getAggregateCount() param:service_id=%s',$params['service_id']));
	    		Log::query(sprintf('SQL::getAggregateCount() param:contents_type_1=%s',$params['contents_type_1']));
	    		Log::query(sprintf('SQL::getAggregateCount() param:contents_type_2=%s',$params['contents_type_2']));
	    		Log::query(sprintf('SQL::getAggregateCount() param:contents_type_3=%s',$params['contents_type_3']));
	    		Log::query(sprintf('SQL::getAggregateCount() param:total_date_min=%s',$params['total_date_min']));
	    		Log::query(sprintf('SQL::getAggregateCount() param:total_date_max=%s',$params['total_date_max']));
    		}else{
    			Log::query(sprintf('SQL::getAggregateCount() param:service_id=%s',$param));
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
    		Log::debug(__FILE__, __LINE__, 'ERROR getAggregateCount() --  do not select from TRN_CONTENTS Table.');
        	Log::error(__FILE__, __LINE__, $ee->getMessage());
    		throw new DbAccessException($ee->getMessage());
    	}
    	catch( \PDOException $pe ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getAggregateCount() --  do not select from TRN_CONTENTS Table.');
    		Log::error(__FILE__, __LINE__, $pe->getMessage());
    		throw new DbAccessException($pe->getMessage());
    	}
    	catch ( \Exception $e ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getAggregateCount() --  do not select from TRN_CONTENTS Table.');
    		Log::error(__FILE__, __LINE__, $e->getMessage());
    		throw new DbAccessException($e->getMessage());
    	}
    	
    	$diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
    	Log::debug(__FILE__, __LINE__, 'INFO   getAggregateCount() --  select from TRN_CONTENTS Table. get Contents Data. ('.$diff_time.')');
    	return $aryData["sumcnt"];
    }
    
    /**
     * 集計結果を取得
     *
     * @param  array $param   パラメータ
     * @return       $row  TRN_CONTENTSテーブル情報
     */
    public function getAggregate( $param )
    {
    	$start_time=microtime(true);

    	$aryData = array();
    	$where = '';
    	try {
    		 

    		if(is_array($param)){
    			$where = 'WHERE c.service_id = :service_id'
    					.' AND cd.sub_id=1 '
    					.' AND ( cd.contents_type = :contents_type_1 OR cd.contents_type = :contents_type_2 OR cd.contents_type = :contents_type_3 ) '
    					.' AND c.import_date BETWEEN :total_date_min AND :total_date_max';
    			
    			$params = array(
    					'service_id' => $param['service_id'],
    					'contents_type_1' => $param['contents_type_1'],
    					'contents_type_2' => $param['contents_type_2'],
    					'contents_type_3' => $param['contents_type_3'],
    					'total_date_min' => date("Y-m-d H:i:s",strtotime($param['total_date_min'])),
    					'total_date_max' => date("Y-m-d H:i:s",strtotime($param['total_date_max'])),
    			);
    		} else {
    			$where = 'WHERE c.service_id = :service_id'
    					.' AND cd.sub_id=1 ';
    			$params = array(
    					'service_id' => $param,
    			);
    		}

    		$sql =   'SELECT c.import_date, '
    				.'    COUNT(c.contents_id) AS sumcnt, '
    				.'    COUNT(cd.contents_type = 1 OR null) AS m_sumcnt, '
    				.'    COUNT( ( cd.contents_type = 1 AND cd.check_state = 1 ) OR null) AS m_sumcnt_already, '
    				.'    COUNT(cd.contents_type = 2 OR null) AS i_sumcnt, '
    				.'    COUNT( ( cd.contents_type = 2 AND cd.check_state = 1 ) OR null) AS i_sumcnt_already, '
    				.'    COUNT(cd.contents_type = 3 OR null) AS c_sumcnt, '
    				.'    COUNT( ( cd.contents_type = 3 AND cd.check_state = 1 ) OR null) AS c_sumcnt_already, '
    				.'    COUNT(COALESCE(cd.check_state, \'\') AND null) AS yet_check_sumcnt '
    				.' FROM TRN_CONTENTS AS c '
    				.' INNER JOIN TRN_CONTENTS_DETAIL AS cd ON (c.contents_id = cd.contents_id) '
    				.$where
    				.' GROUP BY c.import_date ';
    		
    		if(is_array($param)){
    			Log::query(sprintf('SQL::getAggregate() query=%s',$sql));
    			Log::query(sprintf('SQL::getAggregate() param:service_id=%s',$param['service_id']));
    			Log::query(sprintf('SQL::getAggregate() param:contents_type_1=%s',$params['contents_type_1']));
    			Log::query(sprintf('SQL::getAggregate() param:contents_type_2=%s',$params['contents_type_2']));
    			Log::query(sprintf('SQL::getAggregate() param:contents_type_3=%s',$params['contents_type_3']));
    			Log::query(sprintf('SQL::getAggregate() param:total_date_min=%s',$params['total_date_min']));
    			Log::query(sprintf('SQL::getAggregate() param:total_date_max=%s',$params['total_date_max']));
    		} else {
    			Log::query(sprintf('SQL::getAggregate() query=%s',$sql));
    			Log::query(sprintf('SQL::getAggregate() param:service_id=%s',$param));
    		}

    		$stmt = $this->adapter->createStatement($sql);
    		$results = $stmt->execute($params);

    		if ($results instanceof ResultInterface && $results->isQueryResult()) {
    			$resultSet = new ResultSet;
    			$resultSet->initialize($results);
    			$row_num = 1;
    			//sqlでROW_NUMがうまく取れなかったからここで入れている
    			foreach ($resultSet as $row) {
    				$row['ROW_NUM'] = $row_num;
    				array_push($aryData, (array)$row);
    				$row_num++;
    			}
    		}
    	}
    	catch( \ErrorException $ee ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getAggregate() --  do not select from TRN_CONTENTS Table.');
    		Log::error(__FILE__, __LINE__, $ee->getMessage());
    		throw new DbAccessException($ee->getMessage());
    	}
    	catch( \PDOException $pe ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getAggregate() --  do not select from TRN_CONTENTS Table.');
    		Log::error(__FILE__, __LINE__, $pe->getMessage());
    		throw new DbAccessException($pe->getMessage());
    	}
    	catch ( \Exception $e ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getAggregate() --  do not select from TRN_CONTENTS Table.');
    		Log::error(__FILE__, __LINE__, $e->getMessage());
    		throw new DbAccessException($e->getMessage());
    	}
    	 
    	$diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
    	Log::debug(__FILE__, __LINE__, 'INFO   getAggregate() --  select from TRN_CONTENTS Table. get Contents Data. ('.$diff_time.')');
    	return $aryData;
    }


    /**
     * 登録処理 (TRN_CONTENTS)
     * @param ContentData $trnContents
     * @throws \Exception
     */
    public function saveTrnContents(ContentData $trnContents)
    {
    	$start_time=microtime(true);

    	try {
	    	$data = array(
	    			'contents_type' => $trnContents->contents_type,
	    			'service_id' => $trnContents->service_id,
	    			'monitoring_start_date' => $trnContents->monitoring_start_date,
	    			'monitoring_end_date' => $trnContents->monitoring_end_date,
	    			'import_type' => $trnContents->import_type,
	    			'import_ym' => $trnContents->import_ym,
	    			'import_date' => $trnContents->import_date,
	    	);
	    
	    	$contents_id = (int) $trnContents->contents_id;

	    	Log::query(sprintf('SQL::saveTrnContents() param:contents_type=%s',$data['contents_type']));
    		Log::query(sprintf('SQL::saveTrnContents() param:service_id=%s',$data['service_id']));
    		Log::query(sprintf('SQL::saveTrnContents() param:monitoring_start_date=%s',$data['monitoring_start_date']));
    		Log::query(sprintf('SQL::saveTrnContents() param:monitoring_end_date=%s',$data['monitoring_end_date']));
    		Log::query(sprintf('SQL::saveTrnContents() param:import_type=%s',$data['import_type']));
    		Log::query(sprintf('SQL::saveTrnContents() param:import_ym=%s',$data['import_ym']));
    		Log::query(sprintf('SQL::saveTrnContents() param:import_date=%s',$data['import_date']));
    		Log::query(sprintf('SQL::saveTrnContents() param:contents_id=%s',$contents_id));
	    	
	    	if ($contents_id == 0) {
	    		$this->tableGateway->insert($data);
	    	} else {
	    		if ($this->getTrnContentsByContentsId($contents_id)) {
	    			$this->tableGateway->update($data, array('contents_id' => $contents_id));
	    		} else {
	    			throw new \DbAccessException('TrnContents contents_id does not exist');
	    		}
	    	}
    	}
    	catch( \ErrorException $ee ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR saveTrnContents() --  do not update from TRN_CONTENTS Table.');
    		Log::error(__FILE__, __LINE__, $ee->getMessage());
    		throw new DbAccessException($ee->getMessage());
    	}
    	catch( \PDOException $pe ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR saveTrnContents() --  do not update from TRN_CONTENTS Table.');
    		Log::error(__FILE__, __LINE__, $pe->getMessage());
    		throw new DbAccessException($pe->getMessage());
    	}
    	catch ( \Exception $e ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR saveTrnContents() --  do not update from TRN_CONTENTS Table.');
    		Log::error(__FILE__, __LINE__, $e->getMessage());
    		throw new DbAccessException($e->getMessage());
    	}
    	
    	$diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
    	Log::debug(__FILE__, __LINE__, 'INFO   saveTrnContents() --  update from TRN_CONTENTS Table. update Contents Data. ('.$diff_time.')');
   	}
    
}