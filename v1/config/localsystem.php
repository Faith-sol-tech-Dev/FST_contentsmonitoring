<?php
/**
 * エラー表示設定
 */
if('development' == $_SERVER['APPLICATION_ENV']) {
	ini_set( 'display_errors', 1 ); // エラー出力する場合
} else {
	ini_set( 'display_errors', 0 ); // エラー出力しない場合
}

/**
 *  1回1回のPHP処理に対する一意な値
 *
 *  @note ほぼ重複しない一意ということに注意してください
 */
define('PROCESS_ID', uniqid('', true));

/**
 *  実行開始時間(エポックタイム)
 *
 *  処理の実行開始時間で、1970年1月1日を基準とした秒数、いわゆるエポックタイムです。
 */
define('NOW_MSEC', microtime(true));

/**
 *  実行開始時間(エポックタイム)
 *
 *  処理の実行開始時間で、1970年1月1日を基準とした秒数、いわゆるエポックタイムです。
 */
define('NOW', time());

/**
 *  実行開始時間(日付文字列)
 *
 *  NOWを基にした日付です。NOWを「YYYY-mm-dd」で表現した値になります。
 */
define('NOW_DATE', strftime('%Y-%m-%d', NOW));

/**
 *  実行開始時間(時間文字列)
 *
 *  NOWを基にした時間です。NOWを「HH:MM:SS」で表現した値になります。
 */
define('NOW_TIME', strftime('%H:%M:%S', NOW));

/**
 *  現在時間(日時文字列)
 *
 *  NOWを基にした日付です。NOWを「YYYY-mm-dd HH:MM:SS」で表現した値になります。
 */
define('NOW_DATETIME', strftime('%Y-%m-%d %H:%M:%S', NOW));

