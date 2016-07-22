<?php
namespace ContentsMonitor\Service\Data;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\AdapterAwareInterface;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\Exception\ErrorException;

use ContentsMonitor\Service\Entity\UserData;
use ContentsMonitor\Common\LogClass as Log;
use ContentsMonitor\Common\UtilityClass as Utility;
use ContentsMonitor\Exception\DbAccessExceptionClass as DbAccessException;


/**
 * 監視サイトで使用するDBアクセス処理（MST_USER）
 *
 * @package ContentsMonitor
 * @author  FST goto
 * @since   PHP 5.6, Zendframwork 2.4.9
 * @version 1.0.0
 */
class UserTable extends AbstractLocalAdapter implements AdapterAwareInterface
{

	/**
	 * DBアクセス用のモジュールの設定（Adapter, TableGateway）
	 * 
	 * @param  Adapter $adapter   DBアダプタオブジェクト
	 */
    public function setDbAdapter(Adapter $adapter)
    {
        $resultSetPrototype = new ResultSet();
        $resultSetPrototype->setArrayObjectPrototype(new UserData());
        $this->tableGateway = new TableGateway('MST_USER', $adapter, null, $resultSetPrototype);
        $this->adapter = $adapter;
    }
    
	/**
	 * ユーザ情報を取得
	 * 
	 * @param  int $id   ユーザID
	 * @return     $row  MST_USERテーブル情報
	 */
    public function getUser($id)
    {
    	$start_time=microtime(true);
    	
    	$row;
    	try {
	    	$id  = (int) $id;

	    	Log::query(sprintf('SQL::getUser() param:user_id=%s',$id));
	    	
	        $rowset = $this->tableGateway->select(array('user_id' => $id));
	        $row = $rowset->current();
	        if (!$row) {
	            throw new DbAccessException("Could not find row $id");
	        }
        }
    	catch( \ErrorException $ee ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getUser() --  do not select from MST_USER Table.');
    		Log::error(__FILE__, __LINE__, $ee->getMessage());
    		throw new DbAccessException($ee->getMessage());
    	}
    	catch( \PDOException $pe ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getUser() --  do not select from MST_USER Table.');
    		Log::error(__FILE__, __LINE__, $pe->getMessage());
    		throw new DbAccessException($pe->getMessage());
    	}
        catch ( Exception $e ) {
        	Log::debug(__FILE__, __LINE__, 'ERROR getUser() --  do not select from MST_USER Table.');
        	Log::error(__FILE__, __LINE__, $e->getMessage());
    		throw new DbAccessException($e->getMessage());
        }
        
        $diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
    	Log::debug(__FILE__, __LINE__, 'INFO   getUser() --  select from MST_USER Table. get User Data. ('.$diff_time.')');
        return $row;
    }
    
    /**
	 * ユーザ情報に紐づくトークンを取得
	 * 
	 * @param  int $id   ユーザID
	 * @return     $row  MST_USERテーブル情報
     */
    public function getUserToToken($param)
    {
    	$start_time=microtime(true);
    	
		$aryData = array();
    	try {
    		 
			$sql =  'SELECT tk.token, tk.token_expire '
				   .' FROM MST_USER AS us '
				   .' INNER JOIN TRN_TOKEN AS tk '
				   .'    ON us.login_id = tk.login_id '
				   .' WHERE us.user_id = :user_id '
				   .'   AND tk.index_key = :index_key ';
	
			$params = array( 'user_id'   => $param['user_id'],
							 'index_key' => $param['index_key']
					  );
	
			Log::query(sprintf('SQL::getUserToToken() query=%s',$sql));
			Log::query(sprintf('SQL::getUserToToken() param:user_id=%s',$param['user_id']));
			Log::query(sprintf('SQL::getUserToToken() param:index_key=%s',$param['index_key']));
			
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
    		Log::debug(__FILE__, __LINE__, 'ERROR getUserToToken() --  do not select from TRN_TOKEN Table.');
    		Log::error(__FILE__, __LINE__, $ee->getMessage());
    		throw new DbAccessException($ee->getMessage());
    	}
    	catch( \PDOException $pe ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR getUserToToken() --  do not select from TRN_TOKEN Table.');
    		Log::error(__FILE__, __LINE__, $pe->getMessage());
    		throw new DbAccessException($pe->getMessage());
    	}
        catch ( Exception $e ) {
        	Log::debug(__FILE__, __LINE__, 'ERROR getUserToToken() --  do not select from TRN_TOKEN Table.');
        	Log::error(__FILE__, __LINE__, $e->getMessage());
    		throw new DbAccessException($e->getMessage());
        }

        $diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
        Log::debug(__FILE__, __LINE__, 'INFO   getUserToToken() --  select from TRN_TOKEN Table. get Access Onetime Token. ('.$diff_time.')');
        return $aryData;
    }

    /**
	 * トークン情報を登録
	 * 
	 * @param  array $tokenParam   トークン情報
     */
    public function insertUserToToken( $tokenParam )
    {
    	$start_time=microtime(true);
    	
    	try {
    		 
	    	$sql = "";
	    	$params = array();
	    	$aryData = array();
	    	$aryData = $this->getUserToToken($tokenParam);
	    	if ( empty($aryData) ) {
	
	    		$sql =   'INSERT INTO TRN_TOKEN '
	    				.' ( '
	    				.'   login_id '
	    				.'  ,index_key '
	    				.'  ,token '
	    				.'  ,token_expire '
	    				.'  ,insert_date '
	    				.' ) '
	    				.' VALUES '
	    				.' ( '
	    				.'   :login_id '
	    				.'  ,:index_key '
	    				.'  ,:token '
	    				.'  ,:token_expire '
	    				.'  ,:insert_date '
	    				.' ) ';
	    		
	    		$params = array( 'login_id'  => $tokenParam['login_id'],
	    							'index_key' => $tokenParam['index_key'],
	    							'token'     => $tokenParam['token'],
	    							'token_expire' => $tokenParam['token_expire'],
	    							'insert_date'  => strftime("%Y-%m-%d %H:%M:%S", NOW )
	    					);
	
	    	} else {
	    		
	    		$sql =   'UPDATE TRN_TOKEN SET '
	    				.'   token = :token '
	    				.'  ,token_expire = :token_expire '
	    				.' WHERE '
	    				.'      login_id = :login_id '
	    				.'  AND index_key = :index_key ';
	    		
	    		$params = array( 'login_id'  => $tokenParam['login_id'],
	    						'index_key' => $tokenParam['index_key'],
	    						'token'     => $tokenParam['token'],
	    						'token_expire' => $tokenParam['token_expire']
	    				);
	    	}
	
	    	if ( empty($aryData) ) {
		    	Log::query(sprintf('SQL::insertUserToToken() query=%s',$sql));
	    		Log::query(sprintf('SQL::insertUserToToken() param:login_id=%s',$params['login_id']));
	    		Log::query(sprintf('SQL::insertUserToToken() param:index_key=%s',$params['index_key']));
	    		Log::query(sprintf('SQL::insertUserToToken() param:token=%s',$params['token']));
	    		Log::query(sprintf('SQL::insertUserToToken() param:token_expire=%s',$params['token_expire']));
	    		Log::query(sprintf('SQL::insertUserToToken() param:insert_date=%s',$params['insert_date']));
	    	} else {
		    	Log::query(sprintf('SQL::insertUserToToken() query=%s',$sql));
	    		Log::query(sprintf('SQL::insertUserToToken() param:login_id=%s',$params['login_id']));
	    		Log::query(sprintf('SQL::insertUserToToken() param:index_key=%s',$params['index_key']));
	    		Log::query(sprintf('SQL::insertUserToToken() param:token=%s',$params['token']));
	    		Log::query(sprintf('SQL::insertUserToToken() param:token_expire=%s',$params['token_expire']));
	    	}
	
	    	$stmt = $this->adapter->createStatement($sql);
			$results = $stmt->execute($params);
	
			if ($results instanceof ResultInterface && $results->isQueryResult()) {
			    $resultSet = new ResultSet;
			    $resultSet->initialize($results);
			}
		}
    	catch( \ErrorException $ee ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR insertUserToToken() --  do not insert/update from TRN_TOKEN Table.');
    		Log::error(__FILE__, __LINE__, $ee->getMessage());
    		throw new DbAccessException($ee->getMessage());
    	}
    	catch( \PDOException $pe ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR insertUserToToken() --  do not insert/update from TRN_TOKEN Table.');
    		Log::error(__FILE__, __LINE__, $pe->getMessage());
    		throw new DbAccessException($pe->getMessage());
    	}
		catch( Exception $e ) {
			Log::debug(__FILE__, __LINE__, 'ERROR insertUserToToken() --  do not insert/update from TRN_TOKEN Table.');
			Log::error(__FILE__, __LINE__, $e->getMessage());
    		throw new DbAccessException($e->getMessage());
		}
		
        $diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
        Log::debug(__FILE__, __LINE__, 'INFO   insertUserToToken() --  insert/update from TRN_TOKEN Table. set Access Onetime Token. ('.$diff_time.')');
    }   

    /**
     * トークン期日情報を更新
     *
     * @param  array $tokenParam   トークン情報
     *
     */
    public function updateUserToTokenExpire( $tokenParam )
    {
    	$start_time=microtime(true);
    	
    	try {
    
    		$sql =  'UPDATE TRN_TOKEN '
    			   .' SET token_expire = :token_expire'
    			   .' WHERE login_id = :login_id '
    			   .'   AND index_key = :index_key';
   
			$params = array('token_expire' => $tokenParam['token_expire'],
							'login_id'  => $tokenParam['login_id'],
							'index_key' => $tokenParam['index_key']
  						);

			Log::query(sprintf('SQL::updateUserToTokenExpire() query=%s',$sql));
    		Log::query(sprintf('SQL::updateUserToTokenExpire() param:token_expire=%s',$params['token_expire']));
    		Log::query(sprintf('SQL::updateUserToTokenExpire() param:login_id=%s',$params['login_id']));
    		Log::query(sprintf('SQL::updateUserToTokenExpire() param:index_key=%s',$params['index_key']));
			
			$stmt = $this->adapter->createStatement($sql);
    	    $results = $stmt->execute($params);

    	    if ($results instanceof ResultInterface && $results->isQueryResult()) {
    	    	$resultSet = new ResultSet;
    	    	$resultSet->initialize($results);
    	    }
    	}
    	catch( \ErrorException $ee ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR updateUserToTokenExpire() --  do not update from TRN_TOKEN Table.');
    		Log::error(__FILE__, __LINE__, $ee->getMessage());
    		throw new DbAccessException($ee->getMessage());
    	}
    	catch( \PDOException $pe ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR updateUserToTokenExpire() --  do not update from TRN_TOKEN Table.');
    		Log::error(__FILE__, __LINE__, $pe->getMessage());
    		throw new DbAccessException($pe->getMessage());
    	}
    	catch( Exception $e ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR updateUserToTokenExpire() --  do not update from TRN_TOKEN Table.');
    		Log::error(__FILE__, __LINE__, $e->getMessage());
    		throw new DbAccessException($e->getMessage());
    	}
    	 
        $diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
    	Log::debug(__FILE__, __LINE__, 'INFO   updateUserToTokenExpire() --  update from TRN_TOKEN Table. update Onetime Token expire. ('.$diff_time.')');
    }
    
    
    /**
     * トークン情報を削除
     * 
     * @param  array $tokenParam   トークン情報
     *
     */
    public function deleteUserToToken( $tokenParam )
    {
    	$start_time=microtime(true);
    	
    	try {

    		$sql =  'DELETE FROM TRN_TOKEN '
	    			.' WHERE login_id = :login_id '
	    			.'   AND index_key = :index_key';
	
	 		$params = array( 'login_id'  => $tokenParam['login_id'],
	  				 		 'index_key' => $tokenParam['index_key']
	    			);
	    			 
	    	Log::query(sprintf('SQL::deleteUserToToken() query=%s',$sql));
    		Log::query(sprintf('SQL::deleteUserToToken() param:login_id=%s',$params['login_id']));
    		Log::query(sprintf('SQL::deleteUserToToken() param:index_key=%s',$params['index_key']));
	 		
	    	$stmt = $this->adapter->createStatement($sql);
	    	$results = $stmt->execute($params);
	    
	    	if ($results instanceof ResultInterface && $results->isQueryResult()) {
	    		$resultSet = new ResultSet;
	    		$resultSet->initialize($results);
	    	}
    	}
    	catch( \ErrorException $ee ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR deleteUserToToken() --  do not delete from TRN_TOKEN Table.');
    		Log::error(__FILE__, __LINE__, $ee->getMessage());
    		throw new DbAccessException($ee->getMessage());
    	}
    	catch( \PDOException $pe ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR deleteUserToToken() --  do not delete from TRN_TOKEN Table.');
    		Log::error(__FILE__, __LINE__, $pe->getMessage());
    		throw new DbAccessException($pe->getMessage());
    	}
    	catch ( Exception $e ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR deleteUserToToken() --  do not delete from TRN_TOKEN Table.');
    		Log::error(__FILE__, __LINE__, $e->getMessage());
    		throw new DbAccessException($e->getMessage());
    	}
    	 
        $diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
    	Log::debug(__FILE__, __LINE__, 'INFO   deleteUserToToken() --  delete from TRN_TOKEN Table. delete Access Onetime Token. ('.$diff_time.')');
    }
    
    /**
	 * ユーザ情報を登録／変更
	 * 
	 * @param  array $user   ユーザ情報
     *
     */
    public function insertUser(UserData $user)
    {
    }
 
    /**
	 * 指定のユーザ情報の削除
	 * 
	 * @param  int  $id   ユーザID
     *
     */
    public function deleteUser($id)
    {
    	$start_time=microtime(true);
    	
    	try {
    		Log::query(sprintf('SQL::deleteUser() param:user_id=%s',$id));
    		
	        $this->tableGateway->delete(array('user_id' => $id));
        }
    	catch( \ErrorException $ee ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR deleteUser() --  do not delete from TRN_TOKEN Table.');
    		Log::error(__FILE__, __LINE__, $ee->getMessage());
    		throw new DbAccessException($ee->getMessage());
    	}
    	catch( \PDOException $pe ) {
    		Log::debug(__FILE__, __LINE__, 'ERROR deleteUser() --  do not delete from TRN_TOKEN Table.');
    		Log::error(__FILE__, __LINE__, $pe->getMessage());
    		throw new DbAccessException($pe->getMessage());
    	}
        catch ( Exception $e ) {
        	Log::debug(__FILE__, __LINE__, 'ERROR deleteUser() --  do not delete from MST_USER Table.');
        	Log::error(__FILE__, __LINE__, $e->getMessage());
    		throw new DbAccessException($e->getMessage());
        }
        
        $diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
    	Log::debug(__FILE__, __LINE__, 'INFO   deleteUser() --  delete from MST_USER Table. delete User Data. ('.$diff_time.')');
    }
}