<?php
namespace ContentsMonitor\Service\Data;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\AdapterAwareInterface;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Exception\ErrorException;

use ContentsMonitor\Service\Entity\CernerData as CernerData;
use ContentsMonitor\Common\LogClass as Log;
use ContentsMonitor\Common\UtilityClass as Utility;
use ContentsMonitor\Exception\DbAccessExceptionClass as DbAccessException;


/**
 * 監視サイトで使用するDBアクセス処理（MST_CORNER）
 *
 * @package ContentsMonitor
 * @author  FST goto
 * @since   PHP 5.6, Zendframwork 2.4.9
 * @version 1.0.0
 */
class CornerTable extends AbstractLocalAdapter implements AdapterAwareInterface
{

	/**
	 * DBアクセス用のモジュールの設定（Adapter, TableGateway）
	 * @param  Adapter $adapter   DBアダプタオブジェクト
	 */
    public function setDbAdapter(Adapter $adapter)
    {
        $resultSetPrototype = new ResultSet();
        $resultSetPrototype->setArrayObjectPrototype(new CernerData());
        $this->tableGateway = new TableGateway('MST_CORNER', $adapter, null, $resultSetPrototype);
        $this->adapter = $adapter;
    }
    
	/**
	 * コーナー情報を取得
	 * @param  int $id   コーナーID
	 * @return     $row  MST_CORNERテーブル情報
	 */
    public function getCorner( $id = null )
    {
    	$start_time=microtime(true);

    	$row;
    	try {
	    	$aryPrm = array();
	       	if( !empty($id) ) { $aryPrm = array('corner_id' => $id); }
	
	       	if( !empty($id) ) { Log::query(sprintf('SQL::getCorner() param:corner_id=%s',$id)); }
	       	
	       	$rowset = $this->tableGateway->select($aryPrm);
	        $row = $rowset->current();
	        if (!$row) {
	            throw new \DbAccessException("Could not find row $id");
	        }
    	}
    	catch( \ErrorException $ee ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getCorner() --  do not select from MST_CORNER Table.');
    		Log::error(__FILE__, __LINE__, $ee->getMessage());
    		throw new DbAccessException($ee->getMessage());
    	}
    	catch( \PDOException $pe ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getCorner() --  do not select from MST_CORNER Table.');
    		Log::error(__FILE__, __LINE__, $pe->getMessage());
    		throw new DbAccessException($pe->getMessage());
    	}
    	catch ( Exception $e ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getCorner() --  do not select from MST_CORNER Table.');
    		Log::error(__FILE__, __LINE__, $e->getMessage());
    		throw new DbAccessException($e->getMessage());
    	}
    	 
    	$diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
    	Log::debug(__FILE__, __LINE__, 'INFO   getCorner() --  select from MST_CORNER Table. get Corner Data. ('.$diff_time.')');
        return $row;
    }
    
    /**
     * 登録処理(MST_CORNER)
     * @param MST_CORNER $corner CORNERクラス(エンティティクラス)
     * @throws \DbAccessException
     */
    public function saveCorner(CernerData $corner)
    {
    	$start_time=microtime(true);
    
    	try {
    		$insert_now = time();
    		$insert_date = strftime('%Y-%m-%d %H:%M:%S', $insert_now);
    		$data = array(
    				'corner_id'   => (int) $corner->corner_id,
    				'corner_name' => $corner->corner_name,
    				'corner_url'  => $corner->corner_url,
    				'url_regx'    => $corner->url_regx,
    		);
    	  
    		$corner_id = (int) $corner->corner_id;
    
    		Log::query(sprintf('SQL::saveCorner() param:corner_name=%s',$data['corner_name']));
    		Log::query(sprintf('SQL::saveCorner() param:corner_url=%s',$data['corner_url']));
    		Log::query(sprintf('SQL::saveCorner() param:url_regx=%s',$data['url_regx']));
    		Log::query(sprintf('SQL::saveCorner() param:corner_id=%s',$corner_id));
    
    		if ($corner_id == 0) {
    			$this->tableGateway->insert($data);
    		} else {
    			if ($this->getWkBatch($corner_id)) {
    				$this->tableGateway->update($data, array('corner_id' => $corner_id));
    			} else {
    				throw new \DbAccessException('MST_CORNER corner_id does not exist');
    			}
    		}
    	}
    	catch( \ErrorException $ee ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR saveCorner() --  do not insert from MST_CORNER Table.');
    		Log::error(__FILE__, __LINE__, $ee->getMessage());
    		throw new DbAccessException($ee->getMessage());
    	}
    	catch( \PDOException $pe ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR saveCorner() --  do not insert from MST_CORNER Table.');
    		Log::error(__FILE__, __LINE__, $pe->getMessage());
    		throw new DbAccessException($pe->getMessage());
    	}
    	catch (\Exception $e) {
    		Log::debug(__FILE__, __LINE__, 'ERROR saveCorner() --  do not insert from MST_CORNER Table.');
    		Log::error(__FILE__, __LINE__, $e->getMessage());
    		throw new DbAccessException($e->getMessage());
    	}
    	 
    	$diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
    	Log::debug(__FILE__, __LINE__, 'INFO   saveCorner() --  insert from MST_CORNER Table. set Cerner Data. ('.$diff_time.')');
    }
 
    /**
     * 削除処理(MST_CORNER)
     * 
     * @param MST_CORNER $corner CORNERクラス(エンティティクラス)
     * @throws \DbAccessException
     */
    public function deleteCorner(CernerData $corner)
    {
    	$start_time=microtime(true);
    
    	try {
    		$data = array(
    				'corner_id'   => (int) $corner->corner_id,
    		);
    		
    		Log::query(sprintf('SQL::saveCorner() param:corner_id=%s',$data['corner_id']));
    
   			if ($this->getWkBatch($corner_id)) {
   				$this->tableGateway->delete($data);
   				$this->tableGateway->update($data, array('corner_id' => $corner_id));
   			} else {
   				throw new \DbAccessException('MST_CORNER corner_id does not exist');
   			}
    	}
    	catch( \ErrorException $ee ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR saveCorner() --  do not delete from MST_CORNER Table.');
    		Log::error(__FILE__, __LINE__, $ee->getMessage());
    		throw new DbAccessException($ee->getMessage());
    	}
    	catch( \PDOException $pe ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR saveCorner() --  do not delete from MST_CORNER Table.');
    		Log::error(__FILE__, __LINE__, $pe->getMessage());
    		throw new DbAccessException($pe->getMessage());
    	}
    	catch (\Exception $e) {
    		Log::debug(__FILE__, __LINE__, 'ERROR saveCorner() --  do not delete from MST_CORNER Table.');
    		Log::error(__FILE__, __LINE__, $e->getMessage());
    		throw new DbAccessException($e->getMessage());
    	}

    	$diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
    	Log::debug(__FILE__, __LINE__, 'INFO   saveCorner() --  delete from MST_CORNER Table. delete Cerner Data. ('.$diff_time.')');
    }
    
    
}