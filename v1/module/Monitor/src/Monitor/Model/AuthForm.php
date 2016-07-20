<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Monitor\Model;

use Zend\Form\Form;
use Zend\Form\Element;

use ContentsMonitor\Common\LogClass as Log;
use ContentsMonitor\Common\RequestClass as Request;
use ContentsMonitor\Common\ValidationClass as Validation;
use ContentsMonitor\Common\UtilityClass as Utility;


/**
 * ログイン認証フォーム
 *
 * @package ContentsMonitor
 * @author  FST goto
 * @since   PHP 5.6, Zendframwork 2.4.9
 * @version 1.0.0
 */
class AuthForm extends Form
{
	/**
	 * エラー番号
	 * @var /?er=[XX] エラーパラメータ番号
	 */
	public $error_no = "";

	/**
	 * コンストラクタ
	 * @param 
	 * @return 
	 */
    public function __construct()
    {
		parent::__construct();
		
		//-----------------------------------
		// 表示項目をセット
		//-----------------------------------
		$this->add(array(
            'name' => 'id',
            'attributes' => array(
                'type' => 'text',
                'size' => '10',
            ),
            'options' => array(
                'label' => 'login id :  ',
            ),
        ));
        $this->add(array(
            'name' => 'password',
            'attributes' => array(
                'type' => 'password',
                'size' => '20',
            ),
            'options' => array(
                'label' => 'password : ',
            ),
        ));
        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type' => 'submit',
                'value' => ' ログイン ',
            ),
        ));
        $this->add(new Element\Csrf('csrf'));
    }
    
    public function addElement($aryElm)
    {
    	$this->add( $aryElm );
    }
    
    /**
     * 画面から入力されたログイン項目の値をチェック
     *
     * @param array $request 画面で入力された値リスト
     * @return bool true:チェックOK ／ false:チェックNG
     */
    public function loginItemChecker( $request )
    {
		$start_time=microtime(true);
    	
    	try {
    		$message = "";
    		//ユーザID
    		if( "" != ($message= Validation::validateForm($request, "id", "ユーザID", true, 0, null, null)) ) {
    			return array(false, $message); }
    	    if( "" != ($message= Validation::validateForm($request, "id", "ユーザID", true, 8, null, null)) ) {
    			return array(false, $message); }
    		//パスワード
   			if( "" != ($message= Validation::validateForm($request, "password", "パスワード", true, 3, null, null)) ) {
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
    	Log::debug(__FILE__, __LINE__, 'INFO   loginItemChecker() --  has completed.　('.$diff_time.')');
    	return true;
    }
}
