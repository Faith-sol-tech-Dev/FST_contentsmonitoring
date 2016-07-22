<?php
namespace ContentsMonitor\Common;

use ContentsMonitor\Common\LogClass as Log;
use ContentsMonitor\Common\UtilityClass as Utility;

/**
 * 監視サイトで使用するワンタイムトークン
 *
 * @package ContentsMonitor
 * @author  FST goto
 * @since   PHP 5.6, Zendframwork 2.4.9
 * @version 1.0.0
 */
class lockClass
{
	/**
	 * サービスロケーター
	 * @var $service_locator
	 */
	public $service_locator;
	
	/**
	 * コンテンツ情報へのアクセスクラス
	 * @var $contentTable
	 */
	public $contentTable;
	
	/**
	 * コンテンツ詳細情報へのアクセスクラス
	 * @var $contentDetailTable
	 */
	public $contentDetailTable;
	
	/**
	 * コンテンツ内部ID
	 * @var $content_detail_inner_id
	 */
	public $content_detail_inner_id;
	
	/**
     * コンストラクタ
     * 
     * @param ServiceLocator $service_locator サービスロケーター
     */
    public function __construct( $service_locator )
    {
    	$this->getServiceLocator = $service_locator;
    	$this->contentTable = $this->getServiceLocator->get('ContentsMonitor\Service\Data\ContentTable');
    	$this->contentDetailTable = $this->getServiceLocator->get('ContentsMonitor\Service\Data\ContentDetailTable');
    }
	
    /**
     * 対象のコンテンツのロックデータを更新
     *
     * @param int $content_id　 コンテンツID
     * @param int $user_id　　　　ユーザID
     * @return bool true:正常にロック更新 / false:ロック失敗
     */
    public function setContentsLock( $content_id, $user_id, $inner_id )
    {
		$start_time=microtime(true);
    
    	$ret = false;
    	try {
    			
    		//対象のコンテンツのロックデータを更新
    		$aryParam = array( 'lock_user' => $user_id, 'contents_inner_id' => $inner_id, 'contents_id' => $content_id );
    		$lock_ret = $this->contentDetailTable->setContentsDetailTakeLockdata($aryParam);
    		if( $lock_ret != 0 ) { $ret = true; }
    
    	}
    	catch(DbAccessException $de) {
			Log::debug(__FILE__, __LINE__, 'NOTICE setContentsLock() --  lock failed. (userid='.$user_id.', content_id='.$content_id.')');
    		Log::error(__FILE__, __LINE__, $de->getMessage(), true);
    	}
    	catch(Exception $e) {
			Log::debug(__FILE__, __LINE__, 'NOTICE setContentsLock() --  lock failed. (userid='.$user_id.', content_id='.$content_id.')');
    		Log::error(__FILE__, __LINE__, $e->getMessage(), true);
    	}
    
        $diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
        Log::debug(__FILE__, __LINE__, 'INFO   setContentsLock() --  has completed. ('.$diff_time.')');
    	return $ret;
    }
    
	/**
	 * 対象のコンテンツのロックデータを更新(解除)対象のコンテンツのみ
	 * 
	 * @param int $content_id,$user_id　 コンテンツID,ユーザID
	 * @param int $user_id　　　　ユーザID
	 * @return bool true:正常にロック更新 / false:ロック失敗 
	 */
	public function setContentsUnlock( $content_id, $user_id )
	{
		$start_time=microtime(true);

    	$ret = false;
		try {
			$aryParam = array( 'contents_id' => $content_id , 'user_id' => $user_id);
			
			//内部IDを保持しているか確認
			$this->content_detail_inner_id = $this->contentDetailTable->getContentsDetailTakeInnerId($content_id);
			if( !empty($this->content_detail_inner_id) ) { $aryParam['contents_inner_id'] = $this->content_detail_inner_id;  }
			
			//対象のコンテンツのロックデータを更新
			$lock_ret = $this->contentDetailTable->setContentsDetailUnlock($aryParam);
			if($lock_ret != 0){ $ret = true;}

		}
		catch(DbAccessException $de) {
			Log::debug(__FILE__, __LINE__, 'NOTICE setContentsUnlock() --  unlock failed. (userid='.$user_id.', content_id='.$content_id.')');
			Log::error(__FILE__, __LINE__, $de->getMessage(), true);
		}
		catch(Exception $e) {
			Log::debug(__FILE__, __LINE__, 'NOTICE setContentsUnlock() --  unlock failed. (userid='.$user_id.', content_id='.$content_id.')');
			Log::error(__FILE__, __LINE__, $e->getMessage(), true);
		}
		
        $diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
        Log::debug(__FILE__, __LINE__, 'INFO   setContentsUnlock() --  has completed. ('.$diff_time.')');
    	return $ret;
	}
	
	/**
	 * 対象のコンテンツのロックデータを更新(解除)対象ユーザがつかんでいるコンテンツ
	 *
	 * @param int $user_id　ユーザID
	 * @param int $user_id　　　　ユーザID
	 * @return bool true:正常にロック更新 / false:ロック失敗
	 */
	public function setContentsUnlockUserAll( $user_id )
	{
		$start_time=microtime(true);
	
		$ret = false;
		try {
			$aryParam = array( 'user_id' => $user_id);
				
			//内部IDを保持しているか確認
			//$this->content_detail_inner_id = $this->contentDetailTable->getContentsDetailTakeInnerId($content_id);
			//if( !empty($this->content_detail_inner_id) ) { $aryParam['contents_inner_id'] = $this->content_detail_inner_id;  }
				
			//対象のコンテンツのロックデータを更新
			$lock_ret = $this->contentDetailTable->setContentsDetailUnlockUserAll($aryParam);
			
			if($lock_ret != 0){ $ret = true;}
	
		}
		catch(DbAccessException $de) {
			Log::debug(__FILE__, __LINE__, 'NOTICE setContentsUnlockUserAll() --  unlock failed. (userid='.$user_id.')');
			Log::error(__FILE__, __LINE__, $de->getMessage(), true);
		}
		catch(Exception $e) {
			Log::debug(__FILE__, __LINE__, 'NOTICE setContentsUnlockUserAll() --  unlock failed. (userid='.$user_id.')');
			Log::error(__FILE__, __LINE__, $e->getMessage(), true);
		}
	
        $diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
		Log::debug(__FILE__, __LINE__, 'INFO   setContentsUnlockUserAll() --  has completed. ('.$diff_time.')');
		return $ret;
	}
}
