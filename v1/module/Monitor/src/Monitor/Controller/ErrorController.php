<?php
namespace Monitor\Controller;

use Zend\View\Model\ViewModel;

/**
 * エラー画面専用コントローラ
 *
 * @package ContentsMonitor
 * @author  FST goto
 * @since   PHP 5.6, Zendframwork 2.4.9
 * @version 1.0.0
 */
class ErrorController extends CommonController
{
    public function indexAction()
    {
    	// エラー番号
    	$errno = $this->params()->fromQuery('er');
    	var_dump($errno);
		// エラーメッセージを取得
    	$errmsg = $this->_comment->HTTP_ERROR_MESSAGE[$errno];
    	var_dump($errmsg);
    	 
        $this->layout('layout/cm_error_layout');
    	return $this->display(array('errmsg' => $errmsg));
    }
}
