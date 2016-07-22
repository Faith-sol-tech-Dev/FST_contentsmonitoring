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
    
}