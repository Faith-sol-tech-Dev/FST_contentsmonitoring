<?php
namespace Monitor\Model;

use Zend\Form\Form;

/**
 * 共通情報フォーム（Const情報）
 *
 * @package ContentsMonitor
 * @author  FST goto
 * @since   PHP 5.6, Zendframwork 2.4.9
 * @version 1.0.0
 */
class CommonForm extends Form
{
	/*---------------------------------------
	 * 表示文言
	 *---------------------------------------*/
	public $_STR_LOGIN = "ログイン";
	public $_STR_LOGOUT = "ログアウト";
	public $_STR_USER_NORMAL = "一般";
	public $_STR_USER_ADMIN = "管理";

	public $_STR_PAGE_HOME = "HOME";
	public $_STR_PAGE_CONTENTS_SEARCH = "コンテンツ検索";
	public $_STR_PAGE_CONTENTS_RESULT = "コンテンツ検索結果";
	public $_STR_PAGE_CONTENTS_DETAIL = "コンテンツ詳細";
	public $_STR_PAGE_CONTENTS_REPORT = "コンテンツ集計";
	public $_STR_PAGE_SERVICE = "サービス検索";
	public $_STR_PAGE_SERVICE_SEARCH = "サービス検索結果";
	public $_STR_PAGE_SERVICE_REGIST = "サービス登録";
	public $_STR_PAGE_SERVICE_UPDATE = "サービス変更";
	public $_STR_PAGE_SERVICE_NGWORD = "NGワード更新";
	public $_STR_PAGE_USER = "ユーザ検索";
	public $_STR_PAGE_USER_SEARCH = "ユーザ検索結果";
	public $_STR_PAGE_USER_REGIST = "ユーザ登録";
	public $_STR_PAGE_USER_UPDATE = "ユーザ変更";
	public $_STR_PAGE_RECOVERY = "リカバリ検索結果";
	public $_STR_PAGE_RECOVERY_SEARCH = "リカバリ検索";
	public $_STR_PAGE_RECOVERY_RESULT = "リカバリ検索結果";
	public $_STR_PAGE_RECOVERY_LIST = "取込結果一覧";
	public $_STR_PAGE_RECOVERY_DETAIL = "取込結果詳細";
	
	/*---------------------------------------
	 * URL情報群　（サイトURL）
	 *---------------------------------------*/
	public $_APP_PATH = APP_PATH;
	public $_APP_LOGIN_PATH = APP_LOGIN_PATH;
	public $_APP_LOGOUT_PATH = APP_LOGOUT_PATH;
	public $_APP_HOME_PATH = APP_HOME_PATH;
	public $_APP_HOME_UNLOCK_PATH = APP_HOME_UNLOCK_PATH;
	public $_APP_CONTENTS_SEARCH_PATH = APP_CONTENTS_SEARCH_PATH;
	public $_APP_CONTENTS_RESULT_PATH = APP_CONTENTS_RESULT_PATH;
	public $_APP_CONTENTS_DETAIL_PATH = APP_CONTENTS_DETAIL_PATH;
	public $_APP_CONTENTS_UNLOCK_PATH = APP_CONTENTS_UNLOCK_PATH;
	public $_APP_CONTENTS_UPDATE_PATH = APP_CONTENTS_UPDATE_PATH;
	public $_APP_CONTENTS_SEND_REPORT_PATH = APP_CONTENTS_SEND_REPORT_PATH;
	public $_APP_CONTENTS_REPORT_PATH = APP_CONTENTS_REPORT_PATH;
	public $_APP_SERVICE_PATH = APP_SERVICE_PATH;
	public $_APP_SERVICE_SEARCH_PATH = APP_SERVICE_SEARCH_PATH;
	public $_APP_SERVICE_REGIST_PATH = APP_SERVICE_REGIST_PATH;
	public $_APP_SERVICE_UPDATE_PATH = APP_SERVICE_UPDATE_PATH;
	public $_APP_SERVICE_NGWORD_PATH = APP_SERVICE_NGWORD_PATH;
	public $_APP_USER_PATH = APP_USER_PATH;
	public $_APP_USER_SEARCH_PATH = APP_USER_SEARCH_PATH;
	public $_APP_USER_REGIST_PATH = APP_USER_REGIST_PATH;
	public $_APP_USER_UPDATE_PATH = APP_USER_UPDATE_PATH;
	public $_APP_RECOVERY_PATH = APP_RECOVERY_PATH;
	public $_APP_RECOVERY_SEARCH_PATH = APP_RECOVERY_SEARCH_PATH;
	public $_APP_RECOVERY_RESULT_PATH = APP_RECOVERY_RESULT_PATH;
	public $_APP_RECOVERY_LIST_PATH = APP_RECOVERY_LIST_PATH;
	public $_APP_RECOVERY_DETAIL_PATH = APP_RECOVERY_DETAIL_PATH;
	public $_APP_ERROR_PATH = APP_ERROR_PATH;
    
}
