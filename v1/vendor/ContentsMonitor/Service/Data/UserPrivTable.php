<?php
namespace ContentsMonitor\Service\Data;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\AdapterAwareInterface;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Exception\ErrorException;

use ContentsMonitor\Service\Entity\UserPrivData;
use ContentsMonitor\Common\LogClass as Log;
use ContentsMonitor\Common\UtilityClass as Utility;
use ContentsMonitor\Exception\DbAccessExceptionClass as DbAccessException;

/**
 * 監視サイトで使用するDBアクセス処理（MST_USERPRIV）
 *
 * @package ContentsMonitor
 * @author  FST goto
 * @since   PHP 5.6, Zendframwork 2.4.9
 * @version 1.0.0
 */
class UserPrivTable extends AbstractLocalAdapter implements AdapterAwareInterface
{

	/**
	 * DBアクセス用のモジュールの設定（Adapter, TableGateway）
	 * 
	 * @param  Adapter $adapter   DBアダプタオブジェクト
	 */
    public function setDbAdapter(Adapter $adapter)
    {
        $resultSetPrototype = new ResultSet();
        $resultSetPrototype->setArrayObjectPrototype(new UserPrivData());
        $this->tableGateway = new TableGateway('MST_USERPRIV', $adapter, null, $resultSetPrototype);
        $this->adapter = $adapter;
    }
    
	/**
	 * ユーザのアクセス権限情報を取得
	 * 
	 * @param  int $id      ユーザID
	 * @param  int $corner  コーナーID
	 * @return     $row		MST_USERPRIVテーブル情報
	 */
    public function getUserPriv( $id, $corner=null)
    {
    	$start_time=microtime(true);
    	
    	$row;
    	try {
    		
	    	$id  = (int) $id;
	        $aryParam = array('user_id' => $id);
	        if( !empty($corner) ) { $aryParam = array_merge($aryParam, array('corner_id' => $corner)); }

    		Log::query(sprintf('SQL::getUserPriv() param:user_id=%s',$aryParam['user_id']));
    		if( !empty($corner) ) { Log::query(sprintf('SQL::getUserPriv() param:corner_id=%s',$aryParam['corner_id'])); }
    		
	        $rowset = $this->tableGateway->select($aryParam);
	        $row = $rowset->current();
	        if (!$row) {
	            throw new DbAccessException("Could not find row $id");
	        }
    	}
    	catch( \ErrorException $ee ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getUserPriv() --  do not select from MST_USERPRIV Table.');
    		Log::error(__FILE__, __LINE__, $ee->getMessage());
    		throw new DbAccessException($ee->getMessage());
    	}
    	catch( \PDOException $pe ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getUserPriv() --  do not select from MST_USERPRIV Table.');
    		Log::error(__FILE__, __LINE__, $pe->getMessage());
    		throw new DbAccessException($pe->getMessage());
    	}
    	catch ( Exception $e ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getUserPriv() --  do not select from MST_USERPRIV Table.');
    		Log::error(__FILE__, __LINE__, $e->getMessage());
    		throw new DbAccessException($e->getMessage());
    	}
    	 
        $diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
    	Log::debug(__FILE__, __LINE__, 'INFO   getUserPriv() --  select from MST_USERPRIV Table. get UserPriv Data. ('.$diff_time.')');
    	return $row;
    }
    
    /**
	 * ユーザのアクセス権限情報を登録／変更
	 * 
	 * @param  array $user   ユーザ情報
     */
    public function insertUser(UserPrivData $user)
    {

    }
 
    /**
	 * 指定のユーザのアクセス権限情報の削除
	 * 
	 * @param  int  $id   ユーザID
     */
    public function deleteUser($id)
    {
    	$start_time=microtime(true);
    	
    	try {
    		Log::query(sprintf('SQL::deleteUser() param:user_id=%s',$id));
    		
	    	$this->tableGateway->delete(array('user_id' => $id));
    	}
    	catch( \ErrorException $ee ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR deleteUser() --  do not select from MST_USERPRIV Table.');
    		Log::error(__FILE__, __LINE__, $ee->getMessage());
    		throw new DbAccessException($ee->getMessage());
    	}
    	catch( \PDOException $pe ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR deleteUser() --  do not select from MST_USERPRIV Table.');
    		Log::error(__FILE__, __LINE__, $pe->getMessage());
    		throw new DbAccessException($pe->getMessage());
    	}
    	catch ( Exception $e ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR deleteUser() --  do not delete from MST_USERPRIV Table.');
    		Log::error(__FILE__, __LINE__, $e->getMessage());
    		throw new DbAccessException($e->getMessage());
    	}

    	$diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
    	Log::debug(__FILE__, __LINE__, 'INFO   deleteUser() --  delete from MST_USERPRIV Table. delete UserPriv Data. ('.$diff_time.')');
    }
}