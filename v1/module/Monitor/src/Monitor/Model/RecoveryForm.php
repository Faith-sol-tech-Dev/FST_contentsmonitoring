<?php
namespace Monitor\Model;

use Zend\Form\Form;

use ContentsMonitor\Service\Data\ContentTable as ContentTable;
use ContentsMonitor\Service\Data\ContentDetailTable as ContentDetailTable;
use ContentsMonitor\Common\LogClass as Log;
use ContentsMonitor\Common\RequestClass as Request;
use ContentsMonitor\Common\ValidationClass as Validation;
use ContentsMonitor\Common\PaginationClass as Pagination;
use ContentsMonitor\Common\UtilityClass as Utility;
use ContentsMonitor\Exception\DbAccessException as DbAccessException;
use ContentsMonitor\Exception\FormExceptionClass as FormException;


/**
 * サービス情報フォーム
 *
 * @package ContentsMonitor
 * @author  FST goto
 * @since   PHP 5.6, Zendframwork 2.4.9
 * @version 1.0.0
 */
class RecoveryForm extends Form
{
	/**
	 * サービスロケーター
	 * @var $service_locator
	 */
	public $service_locator;
	
	/**
	 * バッチ情報へのアクセスクラス
	 * @var $batchTable
	 */
	public $batchLogTable;
	
	/**
	 * バッチ情報へのアクセスクラス
	 * @var $batchDetailTable
	 */
	public $batchLogContentDetailTable;
	
	/**
	 * コンテンツ情報へのアクセスクラス
	 * @var $contentTable
	 */
	public $contentTable;
	
	/**
	 * コンテンツ情報へのアクセスクラス
	 * @var $contentTable
	 */
	public $contentDetailTable;
	
	/**
	 * アダプター
	 * @var $adapter
	 */
	public $adapter;
	
	/**
	 * 検索項目 コンテンツの種類リスト
	 * @var $content_list_type
	 */
	public $content_list_type = array();
	
	//取込種別
	public $content_import_type = array();
	
	//ときこみ期間
	public $content_list_date_s = "";
	public $content_list_date_e = "";
	//対象サービス
	public $content_list_service = array();
	//実行結果ステータス
	public $status = array();
	//リカバリーステータス
	public $recovery_state = array();
	//エラー事由
	public $error_reason = array();
	//再取込結果
	public $insert_result = null;
	
	
	/**
	 * 検索項目 (選択データ) 実行ステータス
	 * @var $current_state
	 */
	public $current_status = "";
	
	/**
	 * 検索項目 (選択データ)  取込期間（開始）のリスト
	 * @var $current_impDate_str
	 */
	public $current_impDate_str = "";
	
	/**
	 * 検索項目 (選択データ) 取込期間（終了）のリスト
	 * @var $current_impDate_end
	 */
	public $current_impDate_end = "";
	
	/**
	 * 検索項目 (選択データ) 対象サービスのリスト
	 * @var $current_list_service
	 */
	public $current_list_service = "";
	
	/**
	 * 現在の選択しているページ番号
	 * @var $current_page_no
	 */
	public $current_page_no = 1;
	
	/**
	 * 検索結果データのリスト件数
	 * @var $content_search_result_list_cnt
	 */
	public $content_search_result_list_cnt = 0;
	
	/**
	 * 検索項目 (選択データ) 表示件数のリスト
	 * @var $current_list_display_cnt
	 */
	public $current_list_display_cnt = "";
	
	/**
	 * 検索項目 (選択データ) 詳細
	 * @var $current_batch
	 */
	public $current_batch = array();
	
	/**
	 * 検索項目 (選択データ) 詳細の実行ステータス
	 * @var $current_recovery_state
	 */
	public $current_recovery_state = "";
	
	/**
	 * 検索項目 (選択データ) の詳細
	 * @var $content_search_result
	 */
	public $content_search_result = array();	
	
	/**
	 * コンストラクタ
	 *
	 * @param ServiceLocator $service_locator サービスロケーター
	 */
	public function __construct( $service_locator )
	{
		$this->getServiceLocator = $service_locator;
		$this->batchLogTable = $this->getServiceLocator->get('ContentsMonitor\Service\Data\BatchLogTable');
		$this->batchLogContentDetailTable = $this->getServiceLocator->get('ContentsMonitor\Service\Data\BatchLogContentDetailTable');
		$this->contentTable = $this->getServiceLocator->get('ContentsMonitor\Service\Data\ContentTable');
		$this->contentDetailTable = $this->getServiceLocator->get('ContentsMonitor\Service\Data\ContentDetailTable');
		$this->adapter = $this->getServiceLocator->get('Zend\Db\Adapter\Adapter');
		$this->initElement();
		parent::__construct();
	}
	
	/**
	 * 画面に表示する検索項目の初期値をセット
	 */
	protected function initElement()
	{
		// コンテンツ種類
		$this->content_list_type = array( '1'=>'動画', '2'=>'画像', '3'=>'コメント', );
		//取込タイプ
		$this->content_import_type = array( '1'=>'API', '2'=>'CSV', '3'=>'クローラ', );
		//実行ステータス
		$this->status = array( '0' => '', '1'=>'OK', '2'=>'NG', );
		//リカバリーステータス
		$this->recovery_state = array( '0' => '正常終了', '1' => 'リカバリ', '12' => 'リカバリ', '22' => 'リカバリ', '51' => 'リカバリ', '52' => 'リカバリ', '53' => 'リカバリ', '54' => 'リカバリ済み');
		//エラー事由
		$this->error_reason = array( '0' => 'リカバリ再実行済み', '1' => 'DBによる不具合', '2' => 'その他');
	}

	/**
	 * バッチ詳細画面へのリクエストにつくパラメータのチェック
	 *
	 * @param array $request　パラメータ情報
	 * @return boolean[]|string[]|boolean
	 */
	public function checkRequesrParam( $request )
	{
    	$start_time=microtime(true);
		
		try {
			 
			$message = "";
			//バッチ処理No
			if( "" != ($message= Validation::validateForm($request, "batch_log_id", "バッチ処理No", true, 1, null, null)) ) { return array(false, $message); }
			 
		}
		catch( \FormException $fe ) {
			Log::error(__FILE__, __LINE__, $fe->getMessage());
			return false;
			 
		}
		catch( \Exception $e ) {
			Log::error(__FILE__, __LINE__, $e->getMessage());
			return false;
		}
	
        $diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
    	Log::debug(__FILE__, __LINE__, 'Dedub::checkRequesrParam() --  has completed.　('.$diff_time.')');
		return true;
	}
	
	/**
	 * 検索処理
	 *
	 * @param array $request 画面で入力された値リスト
	 */
	public function searchItem( $request )
	{
    	$start_time=microtime(true);

		try {
	
			// 検索結果の件数を取得する
			$sumcnt = $this->batchLogTable->getBatchDataCount( $request );
			if( 0 < $sumcnt ) {
				// 検索結果の情報を取得する
				$this->content_search_result_list = $this->batchLogTable->getBatchData( $request );
			}
			$this->content_search_result_list_cnt = $sumcnt;
		}
		catch( \DbAccessException $de ) 
		{
			Log::error(__FILE__, __LINE__, $de->getMessage());
			Log::error(__FILE__, __LINE__, $de);
			$this->content_search_result_list_cnt = 0;
			$this->content_search_result_list = array();
		}
		catch( \FormException $fe ) 
		{
			Log::error(__FILE__, __LINE__, $fe->getMessage());
			Log::error(__FILE__, __LINE__, $fe);
			$this->content_search_result_list_cnt = 0;
			$this->content_search_result_list = array();
		}
		catch( \Exception $e ) 
		{
			Log::error(__FILE__, __LINE__, $e->getMessage());
			Log::error(__FILE__, __LINE__, $e);
			$this->content_search_result_list_cnt = 0;
			$this->content_search_result_list = array();
		}
		finally
		{
			$diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
    		Log::debug(__FILE__, __LINE__, 'Dedub::searchItem() --  has completed.　('.$diff_time.')');
		}
		
	}
	
	/**
	 * 対象のバッチを取得する
	 *
	 * @param string $batch_id バッチID
	 * @return array/boolean バッチ情報(WK_BATCH_LOG) ／ false: 処理エラー時
	 */
	public function searchBatch( $batch_id )
	{
		$start_time=microtime(true);
		
		$ret = array();
		try {
			$ret = $this->batchLogTable->getBatch($batch_id);

		}
		catch( \DbAccessException $de ) {
			Log::error(__FILE__, __LINE__, $de->getMessage());
			throw $de;
		}
		catch( \FormException $fe ) {
			Log::error(__FILE__, __LINE__, $fe->getMessage());
			throw $fe;
		}
		catch( \Exception $e ) {
			Log::error(__FILE__, __LINE__, $e->getMessage());
			throw $e;
		}
		finally
		{
			$diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
			Log::debug(__FILE__, __LINE__, 'Dedub::searchBatch() --  has completed.　('.$diff_time.')');
		}
		
		return $ret;
	}
	
	/**
	 * 検索処理
	 *
	 * @param array $request 画面で入力された値リスト
	 */
	public function searchBatchDetailList( $request )
	{
		$start_time=microtime(true);

		try {
	
			// 検索結果の件数を取得する
			$sumcnt = $this->batchLogContentDetailTable->getBatchDetailListCount( $request );
			if( 0 < $sumcnt ) {
				// 検索結果の情報を取得する
				$this->content_search_result_list = $this->batchLogContentDetailTable->getBatchDetailList( $request );
			}
			$this->content_search_result_list_cnt = $sumcnt;
		}
		catch( \DbAccessException $de ) {
			Log::error(__FILE__, __LINE__, $de->getMessage());
			Log::error(__FILE__, __LINE__, $de);
	
		}
		catch( \FormException $fe ) {
			Log::error(__FILE__, __LINE__, $fe->getMessage());
			Log::error(__FILE__, __LINE__, $fe);
	
		}
		catch( \Exception $e ) {
			Log::error(__FILE__, __LINE__, $e->getMessage());
			Log::error(__FILE__, __LINE__, $e);
	
		}
		finally
		{
			$diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
			Log::debug(__FILE__, __LINE__, 'Dedub::searchBatchDetailList() --  has completed.　('.$diff_time.')');
		}
		
	}
	
	/**
	 * 検索処理
	 *
	 * @param array $request 画面で入力された値リスト
	 */
	public function searchBatchDetail( $request )
	{
		Log::debug(__FILE__, __LINE__, 'Dedub::searchBatchDetail --  Start.');
		$start_time=microtime(true);
		
		try {
			// 検索結果の情報を取得する
			$this->content_search_result = $this->batchLogContentDetailTable->getBatchDetail( $request );
		}
		catch( \DbAccessException $de ) {
			Log::error(__FILE__, __LINE__, $de->getMessage());
			Log::error(__FILE__, __LINE__, $de);
	
		}
		catch( \FormException $fe ) {
			Log::error(__FILE__, __LINE__, $fe->getMessage());
			Log::error(__FILE__, __LINE__, $fe);
	
		}
		catch( \Exception $e ) {
			Log::error(__FILE__, __LINE__, $e->getMessage());
			Log::error(__FILE__, __LINE__, $e);
	
		}
		finally
		{
			$diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
			Log::debug(__FILE__, __LINE__, 'Dedub::searchBatchDetail --  has completed.　('.$diff_time.')');
		}
		
	}
	
	/**
	 * 再取込処理
	 *
	 * @param array $request 選択したリストの値
	 */
	public function contentsReuptake( $request )
	{
		Log::debug(__FILE__, __LINE__, 'Dedub::contentsReuptake() --  Start.');
		$start_time=microtime(true);
		
		$conn = $this->adapter->getDriver()->getConnection();
		
		try {
			$conn->beginTransaction();
			
			//必要データ取得
			$data = $this->batchLogTable->getReuptake( $request );
			$data["user_id"] = $request["user_id"];
			$data["insert_date"] = date("Y-m-d H:i:s");
			
			//Sub_idを選定
			if(1== $data["sub_id"])
			{
				//自身が親コンテンツ
				//自身が親コンテンツ、もしくは子コンテンツを持たない場合は、値なし
				$data["contents_parent_id"] =  "";
			}
			else 
			{
				//自身が子コンテンツ（親コンテンツを取得する）
				$arySub = $this->batchLogContentDetailTable->getBatchDetailToSub($request);
				$data["contents_parent_id"] = $arySub["contents_id"];
			}
			//TRN＿CONTENTS系にセットする親コンテンツIDをセット
			//$data配列にsub_idはセットされている状態
			//コンテンツの登録を実施。
			//TRN_CONTENTS,TRN_CONTENTS_DETAILテーブルにデータ登録
			//WK_BATCHテーブルのステータスを更新
			  //WK_BATCH_LOG_CONTENTS_DETAILテーブルのステータスを更新
			  //WK_BATCH_LOGテーブルのステータスを更新
			
/*↓破棄			
			//動画がある場合
			if(isset($data["movie_url"])){
				if(!isset($data["contents_type"])){
					$data["contents_type"] = CONTENTS_TYPE_MV;
					echo "コンテンツ種別を動画に設定<br />";
				}
				if(isset($data["image_url"]) || isset($data["comment"])){
					$data["sub_id"] = CONTENTS_TYPE_MV;
					echo "サブIDを1に設定<br />";
				}
				//コンテンツURLをセット
				$data["url"] = $data["movie_url"];
				//現在の最大idの次をセット
				$data["max_id"] = $data["max_id"] + 1;
				// BATCHのデータをCONTENTSへ格納
				$result = $this->contentTable->setContentsReuptake( $data );
				if($result != 1){
					echo '失敗したよ';
					$conn->rollback();
					//再取込失敗
					return false;
				}
				// BATCHのデータをCONTENTS_DETAILへ格納
				$Dresult = $this->contentDetailTable->setContentsDetailReuptake( $data );
				if($Dresult != 1){
					echo '失敗したよ';
					$conn->rollback();
					//再取込失敗
					return false;
				}
			}
			
			//画像がある場合
			if(isset($data["image_url"])){
				if(!isset($data["contents_type"])){
					$data["contents_type"] = CONTENTS_TYPE_IM;
					echo "コンテンツ種別を画像に設定<br />";
				}
				if(isset($data["image_url"]) || isset($data["comment"])){
					$data["sub_id"] = CONTENTS_TYPE_IM;
					echo "サブIDを2に設定<br />";
				}
				//コンテンツURLをセット
				$data["url"] = $data["image_url"];
				//現在の最大idの次をセット
				$data["max_id"] = $data["max_id"] + 1;
				// BATCHのデータをCONTENTSへ格納
				$result = $this->contentTable->setContentsReuptake( $data );
				if($result != 1){
					echo '失敗したよ';
					$conn->rollback();
					//再取込失敗
					return false;
				}
				// BATCHのデータをCONTENTS_DETAILへ格納
				$Dresult = $this->contentDetailTable->setContentsDetailReuptake( $data );
				if($Dresult != 1){
					echo '失敗したよ';
					$conn->rollback();
					//再取込失敗
					return false;
				}
			}
			
			//コメントがある場合
			if(isset($data["comment"])){
				if(!isset($data["contents_type"])){
					$data["contents_type"] = CONTENTS_TYPE_CT;
					echo "コンテンツ種別をコメントに設定<br />";
				}
				if(isset($data["image_url"]) || isset($data["comment"])){
					$data["sub_id"] = CONTENTS_TYPE_CT;
					echo "サブIDを3に設定<br />";
				}
				//コンテンツURLをセット
				$data["url"] = null;
				//現在の最大idの次をセット
				$data["max_id"] = $data["max_id"] + 1;
				// BATCHのデータをCONTENTSへ格納
				$result = $this->contentTable->setContentsReuptake( $data );
				if($result != 1){
					echo '失敗したよ';
					$conn->rollback();
					//再取込失敗
					return false;
				}
				// BATCHのデータをCONTENTS_DETAILへ格納
				$Dresult = $this->contentDetailTable->setContentsDetailReuptake( $data );
				if($Dresult != 1){
					echo '失敗したよ';
					$conn->rollback();
					//再取込失敗
					return false;
				}
			}
			
			//動画、画像、コメントがない場合
			if(!isset($data["movie_url"]) && !isset($data["image_url"]) &&!isset($data["comment"])){
				//おかしい
			}
*/
			//WK_BATCH_LOG_CONTENTS_DETAILの更新
			$this->batchLogContentDetailTable->ReuptakeDetailResult( $request );
			
			//WK_BATCHを更新するかどうかの処理が必要ではないか？
			$detail_list = $this->batchLogContentDetailTable->getBatchDetailList( $request );
			$update_flag = true;
			foreach ($detail_list as $list){
				if(isset($list['recovery_state']) && $list['recovery_state'] != 54){
					$update_flag = false;
					break;
				}
			}
			if($update_flag === true){
				//WK_BATCHを更新
				$this->batchTable->ReuptakeResult( $request );
			}
			
			
			//再取込成功
			$conn->commit();
			return true;
		}
		catch( \DbAccessException $de ) {
			Log::error(__FILE__, __LINE__, $de->getMessage());
			Log::error(__FILE__, __LINE__, $de);
			$conn->rollback();
		}
		catch( \FormException $fe ) {
			Log::error(__FILE__, __LINE__, $fe->getMessage());
			Log::error(__FILE__, __LINE__, $fe);
			$conn->rollback();
		}
		catch( \Exception $e ) {
			Log::error(__FILE__, __LINE__, $e->getMessage());
			Log::error(__FILE__, __LINE__, $e);
			$conn->rollback();
		}
		finally
		{
			$diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
			Log::debug(__FILE__, __LINE__, 'Dedub::contentsReuptake() --  has completed.　('.$diff_time.')');
		}
		
	}
	
	/**
	 * 画面から入力された検索項目の値をチェック
	 *
	 * @param array $request 画面で入力された値リスト
	 * @return bool true:チェックOK ／ false:チェックNG
	 */
	public function searchItemChecker( $request )
	{
		$start_time=microtime(true);
		
		try {
			$message = "";
			//取込タイプ
			if( "" != ($message= Validation::validateForm($request, "check_state1", "取込タイプ", false, 1, null, null)) ) {
				return array(false, $message); }
			if( "" != ($message= Validation::validateForm($request, "check_state2", "取込タイプ", false, 1, null, null)) ) {
				return array(false, $message); }
			if( "" != ($message= Validation::validateForm($request, "check_state3", "取込タイプ", false, 1, null, null)) ) {
				return array(false, $message); }
			//実行ステータス
			if( "" != ($message= Validation::validateForm($request, "content_state", "実行ステータス", false, 0, null, null)) ) {
				return array(false, $message); }
			//取込期間(開始)
			if( "" != ($message= Validation::validateForm($request, "import_date_min", "取込期間(開始)", false, 4, null, null)) ) {
				return array(false, $message); }
			//取込期間(終了)
			if( "" != ($message= Validation::validateForm($request, "import_date_max", "取込期間(終了)", false, 4, null, null)) ) {
				return array(false, $message); }
			if( empty($request['import_date_max']) && !empty($request['import_date_min']) ) {
				return array(false, '取込期間(終了)日時に値がありません。'); }
			if( !empty($request['import_date_max']) && empty($request['import_date_min']) ) {
				return array(false, '取込期間(開始)日時に値がありません。'); }
			if( !empty($request['import_date_max']) && ($request['import_date_max'] < $request['import_date_min']) ) {
				return array(false, '取込期間(開始)の値が不正です。'); }
			//対象サービス
			if( "" != ($message= Validation::validateForm($request, "service_list", "対象サービス", false, 1, null, null)) ) {
				return array(false, $message); }
			//ページ数
			if( "" != ($message= Validation::validateForm($request, "page_no", "ページャ数", false, 1, null, null)) ) {
				return array(false, $message); }	
		}
		catch( \FormException $fe ) {
			Log::error(__FILE__, __LINE__, $fe->getMessage());
			Log::error(__FILE__, __LINE__, $fe);
			return false;
	
		}
		catch( \Exception $e ) {
			Log::error(__FILE__, __LINE__, $e->getMessage());
			Log::error(__FILE__, __LINE__, $e);
			return false;
		}
		finally
		{
			$diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
			Log::debug(__FILE__, __LINE__, 'Dedub::searchItemChecker() --  has completed.　('.$diff_time.')');
		}
		
		return true;
	}

	/**
	 * 画面から入力された検索項目(実行ステータス)の値をチェック
	 *
	 * @param array $request 画面で入力された値リスト
	 * @return bool true:チェックOK ／ false:チェックNG
	 */
	public function searchStatusChecker( $request )
	{
		$start_time=microtime(true);
		
		try {
			$message = "";
			//コンテンツの種類
			if( "" != ($message= Validation::validateForm($request, "content_state", "実行ステータス", true, 1, null, null)) ) {
				return array(false, $message); }
			//ページ数
			if( "" != ($message= Validation::validateForm($request, "page_no", "ページャ数", false, 1, null, null)) ) {
				return array(false, $message); }
		}
		catch( \FormException $fe ) {
			Log::error(__FILE__, __LINE__, $fe->getMessage());
			Log::error(__FILE__, __LINE__, $fe);
			return false;
	
		}
		catch( \Exception $e ) {
			Log::error(__FILE__, __LINE__, $e->getMessage());
			Log::error(__FILE__, __LINE__, $e);
			return false;
		}
		 
		$diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
		Log::debug(__FILE__, __LINE__, 'Dedub::searchStatusChecker() --  has completed.　('.$diff_time.')');
		return true;
	}
	
	/**
	 * 画面に表示するページャをセット
	 *
	 * @return boolean|\ContentsMonitor\Common\PaginationClass
	 */
	public function setPagination($disp_cnt=null)
	{
		$start_time=microtime(true);
		
		$pagination = new Pagination( APP_CONTENTS_RESULT_PATH, $disp_cnt );
		 
		try {
			if($this->content_search_result_list_cnt > 0) {
				// 最大件数をセット
				$pagination->maxcount($this->content_search_result_list_cnt);
				// 現在のページNoをセット
				$pagination->currentpage($this->current_page_no);
				// ページャ設定
				$pagination->CalcPage();
			}
			else {
				return false;
			}
	
		}
		catch( \FormException $fe )
		{
			Log::error(__FILE__, __LINE__, $fe->getMessage());
			Log::error(__FILE__, __LINE__, $fe);
			return false;
		}
		catch( \Exception $e ) 
		{
			Log::error(__FILE__, __LINE__, $e->getMessage());
			Log::error(__FILE__, __LINE__, $e);
			return false;
		}
		finally
		{
			$diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
			Log::debug(__FILE__, __LINE__, 'Dedub::setPagination() -- has completed. ('.$diff_time.')');
		}
		
		return $pagination;
	}
}