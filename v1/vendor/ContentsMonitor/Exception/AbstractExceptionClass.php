<?php
namespace ContentsMonitor\Exception;

/**
 * 例外処理の定義クラス 
 *
 * @package ContentsMonitor
 * @author  FST goto
 * @since   PHP 5.6, Zendframwork 2.4.9
 * @version 1.0.0
 */
abstract class AbstractExceptionClass extends \Exception implements IException
{
	protected $message = 'Unknown exception';     // Exception message
	private   $string;                            // Unknown
	protected $code    = 0;                       // User-defined exception code
	protected $file;                              // Source filename of exception
	protected $line;                              // Source line of exception
	private   $trace;                             // Unknown

	public function __construct($message = null, $code = 0)
	{
		if (!$message) {
			throw new $this('Unknown '. get_class($this));
		}
		parent::__construct($message, $code);
	}

	public function __toString()
	{
		return get_class($this) . " '{$this->message}' in {$this->file}({$this->line})\n"
		. "{$this->getTraceAsString()}";
	}
}