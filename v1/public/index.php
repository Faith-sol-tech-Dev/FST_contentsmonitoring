<?php
/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 */
chdir(dirname(__DIR__));

// Decline static file requests back to the PHP built-in webserver
if (php_sapi_name() === 'cli-server') {
    $path = realpath(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    if (__FILE__ !== $path && is_file($path)) {
        return false;
    }
    unset($path);
}

// (初期設定)　追加ここから -----------------------------
require 'config/localsystem.php';

define('SYSTEM_ZEND_PATH', dirname(__DIR__).'/vendor/ZF2/library/');

// include_pathに本体までのパスをセットする
$paths = array(
    SYSTEM_ZEND_PATH,
    '.',
);
set_include_path(implode(PATH_SEPARATOR, $paths));
 
// ライブラリ本体へのパスを指定
$path = realpath(SYSTEM_ZEND_PATH);
 
// 環境変数を追加
putenv('ZF2_PATH='.$path);

define('V_PATH', dirname(__DIR__));

require './vendor/PHPExcel/PHPExcel.php';

// (初期設定)　追加ここまで -----------------------------

// Setup autoloading
require 'init_autoloader.php';

// Run the application!
Zend\Mvc\Application::init(require 'config/application.config.php')->run();

/**
 * アプリケーションで発生したエラーを補完する
 * @param unknown $errno
 * @param unknown $errstr
 * @param unknown $errfile
 * @param unknown $errline
 * @return void|boolean
 */
function myErrorHandler($errno, $errstr, $errfile, $errline)
{
	if (!(error_reporting() & $errno)) {
		// error_reporting 設定に含まれていないエラーコードです
		return;
	}

	/*エラーパターンに合わせてログ出力*/
	switch ($errno) {
		case E_USER_ERROR:
			//echo "<b>My ERROR</b> [$errno] $errstr<br />\n";
			//echo "  Fatal error on line $errline in file $errfile";
			//echo ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";
			//echo "Aborting...<br />\n";
			exit(1);
			break;

		case E_USER_WARNING:
			//echo "<b>My WARNING</b> [$errno] $errstr<br />\n";
			break;

		case E_USER_NOTICE:
			//echo "<b>My NOTICE</b> [$errno] $errstr<br />\n";
			break;

		default:
			//echo "Unknown error type: [$errno] $errstr<br />\n";
			break;
	}

	/* PHP の内部エラーハンドラを実行しません */
	return true;
}
