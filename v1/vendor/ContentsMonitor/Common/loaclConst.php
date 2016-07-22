<?php
/**
 *　環境設定ファイル (APPLICATION_ENVで定義したファイルを読み込み) 
 */
define('CONF_PATH', 'config/autoload/'.$_SERVER['APPLICATION_ENV'].'.php');

// ----------------------------------------------------
// ## URL設定

define('APP_PATH', '/monitor/');

/**
 * /monitor/アプリケーションURL
 */
define('APP_LOGIN_PATH',           APP_PATH . 'auth/login/');
define('APP_LOGOUT_PATH',          APP_PATH . 'auth/logout/');
define('APP_HOME_PATH',            APP_PATH . '');
define('APP_HOME_UNLOCK_PATH',     APP_PATH . 'index/unlock/');
define('APP_CONTENTS_SEARCH_PATH', APP_PATH . 'content/search/');
define('APP_CONTENTS_RESULT_PATH', APP_PATH . 'content/result/');
define('APP_CONTENTS_DETAIL_PATH', APP_PATH . 'content/detail/?cid=%s');
define('APP_CONTENTS_UNLOCK_PATH', APP_PATH . 'content/unlock/');
define('APP_CONTENTS_UPDATE_PATH', APP_PATH . 'content/update/');
define('APP_CONTENTS_SEND_REPORT_PATH', APP_PATH . 'content/report/');
define('APP_CONTENTS_REPORT_PATH', APP_PATH . 'report/');
define('APP_SERVICE_PATH',         APP_PATH . 'service/');
define('APP_SERVICE_SEARCH_PATH',  APP_PATH . 'report/search/');
define('APP_SERVICE_REGIST_PATH',  APP_PATH . 'report/regist/');
define('APP_SERVICE_UPDATE_PATH',  APP_PATH . 'report/update/');
define('APP_SERVICE_NGWORD_PATH',  APP_PATH . 'report/ngword/');
define('APP_USER_PATH',            APP_PATH . 'user/');
define('APP_USER_SEARCH_PATH',     APP_PATH . 'user/search/');
define('APP_USER_REGIST_PATH',     APP_PATH . 'user/regist/');
define('APP_USER_UPDATE_PATH',     APP_PATH . 'user/update/');
define('APP_RECOVERY_PATH',        APP_PATH . 'recovery/');
define('APP_RECOVERY_SEARCH_PATH', APP_PATH . 'recovery/search');
define('APP_RECOVERY_RESULT_PATH', APP_PATH . 'recovery/result');
define('APP_RECOVERY_LIST_PATH',   APP_PATH . 'recovery/list/?blid=%s');
define('APP_RECOVERY_DETAIL_PATH', APP_PATH . 'recovery/detail/?bid=%s&cid=%s');
define('APP_ERROR_PATH',           APP_PATH . 'error/');


// ----------------------------------------------------
// ## パス設定

/**
 * LOGパス情報
 */
define('LOG_PATH', V_PATH.'/data/log/');
define('EXCEL_PATH', V_PATH.'/data/excel/');
/**
 * CSV保存パス情報
 */
define('SYSTEM_PATH', realpath(dirname(__FILE__) . '/../../..'));
define('DOWNLOAD_PATH', SYSTEM_PATH . '/data/download');
define('DOWNLOAD_API_PATH', DOWNLOAD_PATH . '/api' );
define('DOWNLOAD_CSV_PATH', DOWNLOAD_PATH . '/csv' );


// ----------------------------------------------------
// ## 言語設定

/**
 * 言語設定 (日本語)
 */
define('LOCALE_JAPANESE', 'ja');
/**
 * 言語設定 (英語)
 */
define('LOCALE_ENGLISH' , 'en');
/**
 * デフォルト言語
 */
define('LOCALE_DEFAULT', LOCALE_JAPANESE);


// ----------------------------------------------------
// ## 設定
define ( 'PAGE_TURNER_LIST_VIEW_COUNT', 3 );
define ( 'PAGE_COUNT', 10 );

define ( 'CONTENTS_TYPE_MV', 1 );//動画
define ( 'CONTENTS_TYPE_IM', 2 );//画像
define ( 'CONTENTS_TYPE_CT', 3 );//コメント

define ( 'CONTENTS_CHECK_RESULT_PENDING', 0 );//保留
define ( 'CONTENTS_CHECK_RESULT_OK', 1 );//OK
define ( 'CONTENTS_CHECK_RESULT_NG', 2 );//NG

define ( 'CONTENTS_TYPE_MV_NAME', 'Movie' );//動画
define ( 'CONTENTS_TYPE_IM_NAME', 'Image' );//画像
define ( 'CONTENTS_TYPE_CT_NAME', 'Comment' );//コメント

define ( 'MONITOR_STATE_OFF', '1' );//未監視
define ( 'MONITOR_STATE_ON', '2' ); //監視済み

// WK_BATCH.import_type
define ('WK_BATCH_IMPORT_TYPE_API', 1);
define ('WK_BATCH_IMPORT_TYPE_CSV', 2);
define ('WK_BATCH_IMPORT_TYPE_CRAWLER', 3);

// WK_BATCH系 trigger_type
define ('WK_BATCH_TRIGGER_TYPE_PUSH', 1);
define ('WK_BATCH_TRIGGER_TYPE_PULL', 2);

// WK_BATCH_PROC.state
define ('WK_BATCH_PROC_STATE_WAIT', 0);
define ('WK_BATCH_PROC_STATE_RUN', 1);

// APIタイプ
define ('API_TYPE_XML', 1);
define ('API_TYPE_JSON', 2);
define ('API_TYPE_REST', 3);

// ファイルタイプ
define ('FILE_TYPE_JSON', 'json');
define ('FILE_TYPE_XML', 'xml');
define ('FILE_TYPE_CSV', 'csv');

// リカバリーステータス
define ('API_NO_CONNECTION_DATA', 51);
define ('API_ERR_CONNECTION', 52);
define ('API_NO_CSV', 53);
define ('API_ERR_GET_DATA', 54);
define ('API_ERR_DATA', 55);
define ('API_SUCCESS', 0);

// 取込処理ステータス
define('API_API_START_PUSH', 1);
define('API_API_START_PULL_AUTO', 2);
define('API_API_START_PULL_MANUAL', 3);
define('API_CSV_START_PUSH', 4);
define('API_CSV_START_PULL_AUTO', 5);
define('API_CSV_START_PULL_MANUAL', 6);
define('API_CRAW_START_PULL_AUTO', 7);
define('API_CRAW_START_PULL_MANUAL', 8);
// API取込処理ステータス
define('API_API_START', 10);
define('API_API_START_GET_CONNECTION_DATA', 11);
define('API_API_GET_CONNECTION_DATA', 12);
define('API_API_START_CONNECTION_API', 13);
define('API_API_CONNECTION_API', 14);
define('API_API_CHECK_API', 15);
define('API_API_NO_LINE', 16);
define('API_API_EXIST_LINE', 17);
define('API_API_START_INSERT', 18);
define('API_API_END', 19);
// CSV取込処理ステータス
define('API_CSV_START', 20);
define('API_CSV_START_GET_CONNECTION_DATA', 21);
define('API_CSV_GET_CONNECTION_DATA', 22);
define('API_CSV_START_CONNECTION_HTTP', 23);
define('API_CSV_CONNECTION_HTTP', 24);
define('API_CSV_NO_FILE', 25);
define('API_CSV_START_GET_CSV', 26);
define('API_CSV_GET_CSV', 27);
define('API_CSV_CHECK_CSV', 28);
define('API_CSV_NO_LINE', 29);
define('API_CSV_EXIST_LINE', 30);
define('API_CSV_START_INSERT', 31);
define('API_CSV_END', 32);

// APIカラム名
define('CSV_COLMON_SERVICE_CODE', 'ServiceCode');
define('CSV_COLMON_START_DATE', 'StartDate');
define('CSV_COLMON_END_DATE', 'EndDate');
define('CSV_COLMON_TYPE', 'Type');
define('CSV_COLMON_ID', 'Id');
define('CSV_COLMON_URL', 'Url');
define('CSV_COLMON_FORMAT', 'Format');
define('CSV_COLMON_COMMENT', 'Comment');
define('CSV_COLMON_TITLE', 'Title');
define('CSV_COLMON_USER', 'User');
define('CSV_COLMON_CAPTION', 'Caption');
define('CSV_COLMON_CREAT_DATE', 'CreateDate');
define('CSV_COLMON_IMAGE_LIST', 'ImageList');
define('CSV_COLMON_IMAGE_ID', 'ImageId');
define('CSV_COLMON_IMAGE_URL', 'ImageUrl');
define('CSV_COLMON_IMAGE_CREAT_DATE', 'ImageCreatDate');

// フォーマットタイプ
define('FORMAT_TYPE_JPG', 'jpg');
define('FORMAT_TYPE_PNG', 'png');
define('FORMAT_TYPE_MP4', 'mp4');

// チェック文言定数
define('CHK_COOKIE_SET', 'FST_CHECK');

// 日付時間設定
define ('DATE_MIN_TIME', ' 0:00:00');
define ('DATE_MAX_TIME', ' 23:59:59');

