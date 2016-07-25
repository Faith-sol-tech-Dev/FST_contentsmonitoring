<?php
namespace ContentsMonitor\Common;

/**
 * ログ出力用クラス
 * 
 * @package ContentsMonitor
 * @author  FST goto
 * @since   PHP 5.6, Zendframwork 2.4.9
 * @version 1.0.0
 */
class LogClass
{
	const FATAL = 'fatal';
	const ERROR = 'error';
	const WARN  = 'warn';
	const CRIT  = 'crit';
	const NOTICE = 'notice';
	const ALERT = 'alert';
	const EMERG = 'emerg';
	const DEBUG = 'debug';
	const INFO  = 'info';
	
	const BATCH = 'batch';
	const QUERY = 'query';
	
	private static function write($log_name, $message, $backtrace)
	{
		// IP取得
		$remote_addr = isset($_SERVER["REMOTE_ADDR"]) ? $_SERVER["REMOTE_ADDR"] : '-';
		
		// セッションID取得
		$sid = session_id();
		if ($sid == '') {
			$sid = '-';
		}
		
		// 現在日時取得
		$now = time();
		$now_datetime = strftime('%Y-%m-%d %H:%M:%S', $now);
		
		// ファイル名＋日付
		$filename = LOG_PATH . '/' . $log_name . "_" . strftime('%Y%m%d', $now);
		
		if('debug' == $log_name) {
			$buf = sprintf("INFO %s -- %s \n", $now_datetime, $message);
		} else {
			$buf = sprintf("%s -- %s %s %s %s 	%s\n", $now_datetime, PROCESS_ID, NOW_DATETIME, $sid, $remote_addr, $message);
		}
		
		try {
			// 新規作成の場合はパーミッションを設定
			if (!file_exists($filename)) {
				if(!touch($filename)) {
					throw new \Exception('touch() failed.');
				}
				if(!chmod($filename, 0666)) {
					throw new \Exception('chmod() failed.');
				}
			}
			if (!error_log($buf, 3, $filename)) {
				throw new \Exception('error_log() failed.');
			}
			if ($backtrace) {
				self::backtrace($filename);
			}
		} catch (\Exception $e) {
			$error_message = sprintf("%s(%s): %s filename=[%s], message=[%s]", $e->getFile(), $e->getLine (), $e->getMessage(), $filename, $buf);
			error_log($error_message);
		}
	}
	
	private static function backtrace($filename)
	{
		$backtrace = debug_backtrace();
		array_shift($backtrace);
		$cols = array('file', 'line', 'function');
		$idx = 0;
		foreach ($backtrace as $v) {
			$item = array();
			foreach ($cols as $col) {
				$item[$col] = isset($v[$col]) ? $v[$col] : '';
			}
			if ($item['file'] == __FILE__) {
				continue;
			}
			$buf = sprintf("%d) %s(%s): %s\n", $idx, $item['file'], $item['line'], $item['function']);
			if (!error_log($buf, 3, $filename)) {
				throw new \Exception('error_log() failed.');
			}
			$idx++;
		}
	}
	
	private static function writePlain($log_name, $file, $line, $message, $backtrace)
	{
		if('development' == $_SERVER['APPLICATION_ENV']) {
			//$buf = sprintf("%s(%s):\t %s", $file, $line, $message);
			$buf = sprintf("[%s]: \t %s\t\t[%s(%s)]", "", $message, $file, $line);
			self::write('debug', $buf, false);
		}
	}

	private static function writeStandard($log_name, $file, $line, $message, $backtrace)
	{
		$buf = sprintf("%s(%s): %s", $file, $line, $message);
		self::write($log_name, $buf, $backtrace);
	}
	
	public static function batch($file, $line, $message, $backtrace=false)
	{
		self::writeStandard(self::BATCH, $file, $line, $message, $backtrace);
	}

	public static function query($message, $backtrace=false)
	{
		self::writePlain(self::QUERY, null, null, $message, $backtrace);
	}
	
	public static function emerg($file, $line, $message, $backtrace=false)
	{
		self::writeStandard(self::EMERG, $file, $line, $message, $backtrace);
	}
	
	public static function debug($file, $line, $message, $backtrace=false)
	{
		self::writePlain(self::DEBUG, $file, $line, $message, $backtrace);
	}
	
	public static function notice($file, $line, $message, $backtrace=false)
	{
		self::writeStandard(self::NOTICE, $file, $line, $message, $backtrace);
	}

	public static function warn($file, $line, $message, $backtrace=false)
	{
		self::writeStandard(self::WARN, $file, $line, $message, $backtrace);
	}
	
	public static function error($file, $line, $message, $backtrace=false)
	{
		self::writeStandard(self::ERROR, $file, $line, $message, $backtrace);
	}

	public static function fatal($file, $line, $message, $backtrace=false)
	{
		self::writeStandard(self::FATAL, $file, $line, $message, $backtrace);
	}

}