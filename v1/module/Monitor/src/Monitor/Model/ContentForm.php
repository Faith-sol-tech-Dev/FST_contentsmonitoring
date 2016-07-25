<?php
namespace Monitor\Model;

use Zend\Form\Form;

use ContentsMonitor\Service\Data\ContentTable as ContentTable;
use ContentsMonitor\Service\Data\ContentTable as ContentDetailTable;
use ContentsMonitor\Common\LogClass as Log;
use ContentsMonitor\Common\UtilityClass as Utility;
use ContentsMonitor\Common\RequestClass as Request;
use ContentsMonitor\Common\ValidationClass as Validation;
use ContentsMonitor\Common\PaginationClass as Pagination;
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
class ContentForm extends Form
{
	/**
	 * サービスロケーター
	 * @var $service_locator
	 */
	protected $service_locator;
	
	/**
	 * コンテンツ情報へのアクセスクラス
	 * @var $contentTable
	 */
	protected $contentTable;

	/**
	 * コンテンツ詳細情報へのアクセスクラス
	 * @var $contentDetailTable
	 */
	protected $contentDetailTable;
	
	/**
	 * コンテンツ監視結果（NGパターン）報告へのアクセスクラス
	 * @var $contentNGReportTable
	 */
	protected $contentNGReportTable;
	
	/**
	 * 検索項目 コンテンツの種類リスト
	 * @var $content_list_type
	 */
	public $content_list_type = array();

	/**
	 * 検索項目 監視状態のリスト
	 * @var $content_list_mode
	 */
	public $content_list_mode = array();
	
	/**
	 * 検索項目 コンテンツ状態のリスト
	 * @var $content_list_stats
	 */
	public $content_list_stats = array();
	
	/**
	 * 検索項目 取込期間（開始）のリスト
	 * @var $content_text_impDate_str
	 */
	public $content_text_impDate_str = array();
	
	/**
	 * 検索項目 取込期間（終了）のリスト
	 * @var $content_text_impDate_end
	 */
	public $content_text_impDate_end = array();
	
	/**
	 * 検索項目 チェック期間（開始）のリスト
	 * @var $content_text_ckDate_str
	 */
	public $content_text_ckDate_str = array();
	
	/**
	 * 検索項目 チェック期間（終了）のリスト
	 * @var $content_text_ckDate_end
	 */
	public $content_text_ckDate_end = array();
	
	/**
	 * 検索項目 表示件数のリスト
	 * @var $content_list_display_cnt
	 */
	public $content_list_display_cnt = array();
	
	/**
	 * 検索項目 (選択データ) コンテンツの種類リスト
	 * @var $content_list_type
	 */
	public $current_list_type = "";
	
	/**
	 * 検索項目 (選択データ) 監視状態のリスト
	 * @var $content_list_mode
	 */
	public $current_list_mode1 = "";
	public $current_list_mode2 = "";
	public $current_list_mode3 = "";
	
	/**
	 * 検索項目 (選択データ) コンテンツ状態のリスト
	 * @var $content_list_stats
	 */
	public $current_list_stats1 = "";
	public $current_list_stats2 = "";
	public $current_list_stats3 = "";
	
	/**
	 * 検索項目 (選択データ) 取込期間（開始）のリスト
	 * @var $content_text_impDate_str
	 */
	public $current_text_impDate_str = "";
	
	/**
	 * 検索項目 (選択データ) 取込期間（終了）のリスト
	 * @var $content_text_impDate_end
	 */
	public $current_text_impDate_end = "";
	
	/**
	 * 検索項目 (選択データ) チェック期間（開始）のリスト
	 * @var $content_text_ckDate_str
	 */
	public $current_text_ckDate_str = "";
	
	/**
	 * 検索項目 (選択データ) チェック期間（終了）のリスト
	 * @var $content_text__ckDate_end
	 */
	public $current_text_ckDate_end = "";
	
	/**
	 * 検索項目 (選択データ) 表示件数のリスト
	 * @var $content_list_display_cnt
	 */
	public $current_list_display_cnt = "";
	
	/**
	 * 検索結果データのリスト
	 * @var $content_search_result_list
	 */
	public $content_search_result_list = array();

	/**
	 * 検索結果データ(コンテンツID)のリスト
	 * @var $content_search_result_id_list
	 */
	public $content_search_result_id_list = array();
	
	/**
	 * 検索結果データのリスト件数
	 * @var $content_search_result_list_cnt
	 */
	public $content_search_result_list_cnt = 0;

	/**
	 * 詳細 監視チェック項目 コンテンツ状態のリスト
	 * @var $content_detail_ck_stats
	 */
	public $content_detail_ck_stats = array();
	
	/**
	 * 詳細 監視チェック項目 監視コメント
	 * @var $content_detail_ck_comment
	 */
	public $content_detail_ck_comment = "";
	
	/**
	 * コンテンツ詳細データ
	 * @var $content_detail_list
	 */
	public $content_detail_list = array();
	
	/**
	 * コンテンツ詳細データ　タイプ別振り分け
	 * @var $content_detail_list_divide
	 */
	public $content_detail_list_divide = array();
	
	/**
	 * コンテンツ詳細データ件数
	 * @var $content_detail_cnt
	 */
	public $content_detail_cnt = 0;

	/**
	 * コンテンツ詳細のコンテンツ内部ID
	 * @var $content_detail_inner_id
	 */
	public $content_detail_inner_id = null;
	
	/**
	 * コンテンツID
	 * @var $content_id
	 */
	public $content_id = null;
	
	/**
	 * 現在の選択しているページ番号
	 * @var $current_page_no
	 */
	public $current_page_no = 1;
	
	/**
	 * ロック情報
	 * @var $lock_data
	 */
	public $lock_data = array();
	
	/**
	 * ロック状態 (true:自身がロック/false:他人がロック)
	 * @var $lock_state
	 */
	public $lock_state = true;

	/**
	 * コンテンツ詳細　取込日時
	 * @var $content_detail_imp_date
	 */
	public $content_detail_imp_date = '';
	
	/**
	 * コンテンツ詳細　コンテンツ作成日時
	 * @var $content_detail_creat_date
	 */
	public $content_detail_creat_date = '';

	/**
	 * コンテンツ詳細　表示用コンテンツ項目配列
	 * @var $content_detail_creat_date
	 */
	public $content_detail_display_ary = array();
	
	/**
	 * コンテンツ詳細　前ページ
	 * @var $content_detail_prev_page
	 */
	public $content_detail_prev_page = 0;
	
	/**
	 * コンテンツ詳細　次ページ
	 * @var $content_detail_next_page
	 */
	public $content_detail_next_page = 0;
	
	//-------------------------------------------------------------------------------------------
	
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
    	$this->contentNGReportTable = $this->getServiceLocator->get('ContentsMonitor\Service\Data\contentNGReportTable');
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
    	// 監視状態のリスト
    	$this->content_list_mode = array( '1'=>'未監視', '2'=>'監視済み', '3'=>'監視中' );
    	// コンテンツ状態のリスト
    	$this->content_list_stats = array(  '1'=>'保留', '2'=>'OK', '3'=>'NG' );
    	// コンテンツ種類
    	$this->content_list_display_cnt = array( '1'=>'10', '2'=>'50', '3'=>'100', '4'=>'500', );
    }
    
    /**
     * コンテンツ詳細画面へのリクエストにつくパラメータのチェック
     * 
     * @param array $request
     * @return boolean[]|string[]|boolean
     */
    public function checkRequesrParam( $request )
    {
    	$start_time=microtime(true);
    	 
    	try {
    	
    		$message = "";
    		//コンテンツID
    		if( "" != ($message= Validation::validateForm($request, "content_id", "コンテンツID", true, 1, null, null)) ) { return array(false, $message); }
    	
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
    	Log::debug(__FILE__, __LINE__, 'INFO   checkRequesrParam() --  has completed. ('.$diff_time.')');
    	return true;    	
    }
    
    /**
     * 画面に表示する項目の属性を追加
     * 
     * @param string  $elm 項目変数名
     * @param string  $val　設定値 
     */
    public function addAttribute( $elm, $val )
    {
    	$this->$elm = $val;
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
    		$sumcnt = $this->contentTable->getContentDataCount( $request );
    		if( 0 < $sumcnt ) {
	    		// 検索結果の情報を取得する
	    		$this->content_search_result_list = $this->contentTable->getContentData( $request );
    		}
    		$this->content_search_result_list_cnt = $sumcnt;
    	}
    	catch( \DbAccessException $de ) {
    		Log::error(__FILE__, __LINE__, $de->getMessage());
    		$this->content_search_result_list_cnt = 0;
    		$this->content_search_result_list = array();
    	}
    	catch( \FormException $fe ) {
    		Log::error(__FILE__, __LINE__, $fe->getMessage());
    		$this->content_search_result_list_cnt = 0;
    		$this->content_search_result_list = array();
    	}
    	catch( \Exception $e ) {
    		Log::error(__FILE__, __LINE__, $e->getMessage());
    		$this->content_search_result_list_cnt = 0;
    		$this->content_search_result_list = array();
    	}
    	
    	$diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
    	Log::debug(__FILE__, __LINE__, 'INFO   searchItem() --  has completed. ('.$diff_time.')');
    }

    /**
     * 検索処理  (コンテンツIDリスト)
     *
     * @param array $request 画面で入力された値リスト
     * @return array コンテンツIDリスト
     */
    public function searchItemIdList( $request )
    {
    	$start_time=microtime(true);
    	
    	$ary_idList = array();
    	try {
    		
    		// 検索結果のIDリストを取得する
    		$ary_idList = $this->contentTable->getContentIdList( $request );
    
    	}
    	catch( \DbAccessException $de ) {
    		Log::error(__FILE__, __LINE__, $de->getMessage());
    	}
    	catch( \FormException $fe ) {
    		Log::error(__FILE__, __LINE__, $fe->getMessage());
    	}
    	catch( \Exception $e ) {
    		Log::error(__FILE__, __LINE__, $e->getMessage());
    	}
    	 
    	$diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
    	Log::debug(__FILE__, __LINE__, 'INFO   searchItemIdList() --  has completed. ('.$diff_time.')');
    	return $ary_idList;
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
    		//コンテンツの種類
    		if( "" != ($message= Validation::validateForm($request, "contents_type", "コンテンツの種類", true, 1, null, null)) ) {
    			return array(false, $message); }
    		//監視状態
    		if( "" != ($message= Validation::validateForm($request, "check_state1", "監視状態", false, 1, null, null)) ) {
    			return array(false, $message); }
    		if( "" != ($message= Validation::validateForm($request, "check_state2", "監視状態", false, 1, null, null)) ) {
    			return array(false, $message); }
    		if( "" != ($message= Validation::validateForm($request, "check_state3", "監視状態", false, 1, null, null)) ) {
    			return array(false, $message); }
    		//コンテンツの状態
    		if( "" != ($message= Validation::validateForm($request, "check_result1", "コンテンツの状態", false, 1, null, null)) ) {
    			return array(false, $message); }
    		if( "" != ($message= Validation::validateForm($request, "check_result2", "コンテンツの状態", false, 1, null, null)) ) {
    			return array(false, $message); }
    		if( "" != ($message= Validation::validateForm($request, "check_result3", "コンテンツの状態", false, 1, null, null)) ) {
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
    		//チェック期間(開始)
    		if( "" != ($message= Validation::validateForm($request, "check_date_min", "チェック期間(開始)", false, 4, null, null)) ) {
    			return array(false, $message); }
    		//チェック期間(終了)
    		if( "" != ($message= Validation::validateForm($request, "check_date_max", "チェック期間(終了)", false, 4, null, null)) ) {
    			return array(false, $message); }
    	    if( empty($request['check_date_max']) && !empty($request['check_date_min']) ) {
    	    	return array(false, 'チェック期間(終了)日時に値がありません。'); }
    		if( !empty($request['check_date_max']) && empty($request['check_date_min']) ) {
    	    	return array(false, 'チェック期間(開始)日時に値がありません。'); }
    	    if( !empty($request['check_date_max']) && ($request['check_date_max'] < $request['check_date_min']) ) {
    	    	return array(false, 'チェック期間(開始)の値が不正です。'); }
    		//表示件数
    		if( "" != ($message= Validation::validateForm($request, "display_cnt", "表示件数", false, 1, null, null)) ) {
    			return array(false, $message); }
    		//ページ数
    		if( "" != ($message= Validation::validateForm($request, "page_no", "ページャ数", false, 1, null, null)) ) {
    			return array(false, $message); }
    		
    	}
        catch( \FormException $fe ) {
    		Log::error(__FILE__, __LINE__, $fe->getMessage(), true);
    		return false;
    		
    	}
    	catch( \Exception $e ) {
    		Log::error(__FILE__, __LINE__, $e->getMessage(), true);
    		return false;
    	}
    	
    	$diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
    	Log::debug(__FILE__, __LINE__, 'INFO   searchItemChecker() --  has completed. ('.$diff_time.')');
    	return true;
    }

    /**
     * 画面に表示するページャをセット
     * 
     * @param int $disp_cnt 画面に表示する件数
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
	    		//　表示件数
	    		if(!empty($this->current_list_display_cnt)) {
		    		$pagination->viewCount($this->current_list_display_cnt);
	    		}
	    		// ページャ設定
	    		$pagination->CalcPage();
    		}
    		else {
    			return false;
    		}
    		
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
    	Log::debug(__FILE__, __LINE__, 'INFO   setPagination() --  has completed. ('.$diff_time.')');
   		return $pagination;
    }

    /**
     * コンテンツ詳細情報を取得する
     * 
     * @param int $content_id　コンテンツID
     */
	public function getContentsDetailData( $content_id )
	{
    	$start_time=microtime(true);
		
		try {
			
			$aryParam = array('contents_inner_id' => null, 'contents_id' => $content_id );

			//内部IDを保持しているか確認
			$this->content_detail_inner_id = $this->contentDetailTable->getContentsDetailTakeInnerId($content_id);
			if( !empty($this->content_detail_inner_id) ) { $aryParam['contents_parent_id'] = $this->content_detail_inner_id;  }

			//コンテンツ詳細情報を取得
			$this->content_detail_list = $this->contentDetailTable->getContentDetailData($aryParam);
			if(!empty($this->content_detail_list[0]['check_note'])){
				$this->content_detail_ck_comment = $this->content_detail_list[0]['check_note'];
			}

			if( !empty($this->content_detail_list) ) {
				$this->content_detail_cnt = count($this->content_detail_list);
				
				//種別ごとに分ける
				foreach ($this->content_detail_list as $list){
					if($list['sub_id'] == 1 || ($list['sub_id'] == null && $list['contents_type'] == 1)){
						//動画
						$this->content_detail_list_divide[1] = $list;
					}
					elseif ($list['sub_id'] == 2 || ($list['sub_id'] == null && $list['contents_type'] == 2)){
						//画像
						$this->content_detail_list_divide[2] = $list;
					}
					elseif ($list['sub_id'] == 3 || ($list['sub_id'] == null && $list['contents_type'] == 3)){
						//コメント
						$this->content_detail_list_divide[3] = $list;
					}
				}
			}
			$this->content_detail_imp_date= $this->content_detail_list[0]['import_date'];
			$this->content_detail_creat_date= $this->content_detail_list[0]['create_date'];
			$this->content_detail_display_ary= $this->content_detail_list[0];

		}
	    catch( \DbAccessException $de) {
    		Log::error(__FILE__, __LINE__, $de->getMessage());
    		throw $de;
    	}
    	catch( \FormException $fe) {
    		Log::error(__FILE__, __LINE__, $fe->getMessage());
    		throw $fe;
    	}
    	catch( \Exception $e) {
    		Log::error(__FILE__, __LINE__, $e->getMessage());
    		throw $e;
    	}
		
    	$diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
    	Log::debug(__FILE__, __LINE__, 'INFO   getContentsDetailData() --  has completed. ('.$diff_time.')');
	}

	/**
	 * 対象のコンテンツをロックしているユーザ情報を取得する
	 * 
	 * @param int $contents_id コンテンツID
	 * @return boolean ロックなし:array(true, null) ／ ロックあり: array(false,user_id)　※DBエラー時もロックありとして対処
	 */
	public function checkContentsLock( $contents_id )
	{
    	$start_time=microtime(true);
		$ret = array();
		try {

			$aryParam = array( 'contents_id' => $contents_id );
			
			//対象のコンテンツをロックしているユーザ情報を取得・チェック
			$this->lock_data = (array)$this->contentDetailTable->getContentsDetailTakeLockdata($aryParam);
			if( empty($this->lock_data) ) { $ret = array(true, null);  }
			else{ $ret = array(false, $this->lock_data['user_id']); }
				

		}
		catch( \DbAccessException $de ) {
    		Log::error(__FILE__, __LINE__, $de->getMessage());
    		Log::error(__FILE__, __LINE__, $de);
    		$ret = array(false, null);
    	}
    	catch( \FormException $fe ) {
    		Log::error(__FILE__, __LINE__, $fe->getMessage());
    		Log::error(__FILE__, __LINE__, $fe);
    		$ret = array(false, null);
    	}
    	catch( \Exception $e ) {
    		Log::error(__FILE__, __LINE__, $e->getMessage());
    		Log::error(__FILE__, __LINE__, $e);
    		$ret = array(false, null);
    	}
    	
    	$diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
    	Log::debug(__FILE__, __LINE__, 'INFO   checkContentsLock() --  has completed. ('.$diff_time.')');
    	return $ret;
	}
	
	/**
	 * コンテンツの監視結果を更新
	 *
	 * @param array $param 監視結果の内容
	 * @return boolean 成功:true ／ 失敗: false
	 */
	public function setCheckResult( $param )
	{
    	$start_time=microtime(true);
		$ret = false;
		$inner_id = null;
		try {
			$aryParam = $param;
			
			//内部IDを保持しているか確認
			$inner_id = $this->contentDetailTable->getContentsDetailTakeInnerId($aryParam['contents_id']);
			$aryParam['contents_parent_id'] = $inner_id;
			
			//更新
			$row = $this->contentDetailTable->setContentsDetailCheckUpdate($aryParam);
			
			if($row != 0)$ret = true;
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
		 
    	$diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
    	Log::debug(__FILE__, __LINE__, 'INFO   setCheckResult() --  has completed. ('.$diff_time.')');
		return $ret;
	}

	/**
	 * コンテンツの監視結果がNGの場合NG_REPORTをセット
	 *
	 * @param array $param　コンテンツ情報
	 * @return boolean 成功:true ／ 失敗: false
	 */
	public function setCheckResult_NG( $param )
	{
    	$start_time=microtime(true);
		$ret = false;
		$conn = $this->getServiceLocator->get('Zend\Db\Adapter\Adapter')->getDriver()->getConnection();
		try {
			//トランザクション用
			$conn->beginTransaction();
			
			$aryParam = $param;
			
			//内部IDからコンテンツID（複数）取得
			$inner = array(
					'contents_inner_id' => $aryParam['contents_inner_id']
			);
			$detail = $this->contentDetailTable->getContentDetailData($inner);
			//更新
			foreach ($detail as $list){
				//コンテンツID設定
				$aryParam['contents_id'] = $list['contents_id'];
				
				//既にNGREPORTに存在しているか確認
				$count = $this->contentNGReportTable->getContentsNGReport_count($aryParam);
				
				if($count == 0){
					//NGREPORT格納
					$row = $this->contentNGReportTable->setContentsNGReport($aryParam);
					if($row == 0){
						$ret = false;
						break;
					} else { $ret = true; }
				} else {
					//NGREPORT更新
					$row = $this->contentNGReportTable->setContentsNGReport_update($aryParam);
					if($row == 0){
						$ret = false;
						break;
					} else { $ret = true; }
				}
			}
			if($ret == true){
				//再取込成功
				$conn->commit();
			}
			else {
				$conn->rollback();
			}
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
			
    	$diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
    	Log::debug(__FILE__, __LINE__, 'INFO   setCheckResult_NG() --  has completed. ('.$diff_time.')');
		return $ret;
	}

	/**
	 * 報告対象のコンテンツが存在するかチェック
	 *
	 * @param array $request 画面で入力された値リスト
	 */
	public function getReportCount( $request )
	{
    	$start_time=microtime(true);

    	$sumcnt = 0;
		try {
			// コンテンツ件数を取得する
			$sumcnt = $this->contentNGReportTable->getContentsNGReportToUserId_count( $request );
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
		 
    	$diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
    	Log::debug(__FILE__, __LINE__, 'INFO   getReportCount() --  has completed. ('.$diff_time.')');
		return $sumcnt;
	}
	
	/**
	 * 監視レポートの監視未報告のレポート件数を取得する
	 * @return unknown
	 */
	public function getMonitoringCount()
	{
    	$start_time=microtime(true);

    	$sumcnt = 0;
		try {
			$sumcnt = $this->contentNGReportTable->getContentsNGReportToMonitorFlag();
		}
		catch( \DbAccessException $de ) {
			Log::debug(__FILE__, __LINE__, 'FATAL ERROR：  監視報告に対する連絡ができませんでした。至急報告を行ってください。');
			Log::error(__FILE__, __LINE__, 'FATAL ERROR：  監視報告に対する連絡ができませんでした。至急報告を行ってください。');
			Log::error(__FILE__, __LINE__, $de->getMessage());
		}
		catch( \Exception $e ) {
			Log::debug(__FILE__, __LINE__, 'FATAL ERROR：  監視報告に対する連絡ができませんでした。至急報告を行ってください。');
			Log::error(__FILE__, __LINE__, 'FATAL ERROR：  監視報告に対する連絡ができませんでした。至急報告を行ってください。');
			Log::error(__FILE__, __LINE__, $e->getMessage());
		}
			
		$diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
		Log::debug(__FILE__, __LINE__, 'INFO   getMonitoringCount() --  has completed. ('.$diff_time.')');
		return $sumcnt;
	}
}
