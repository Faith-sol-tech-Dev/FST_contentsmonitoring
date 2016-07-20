<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Monitor\Model;

use Zend\InputFilter\InputFilter;

use ContentsMonitor\Common\MessageClass as Message;

/**
 * ログイン認証バリデーション設定
 *
 * @package ContentsMonitor
 * @author  FST goto
 * @since   PHP 5.6, Zendframwork 2.4.9
 * @version 1.0.0
 */
class AuthFilter extends InputFilter
{

	/**
	 * コンストラクタ
	 * @param 
	 * @return 
	 */
    public function __construct()
    {
    	$message = new Message();

		//-----------------------------------
		// バリデーション項目をセット
		//-----------------------------------
        $this->add(array(
            'name' => 'id',
            'required' => true,
            'filters' => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
            ),
            'validators' => array(
           		array(
					'name' =>'NotEmpty',
					'break_chain_on_failure' => true,
            		'options'                => array(
	            		'messages' => array(
	            			\Zend\Validator\NotEmpty::IS_EMPTY => $message->HTTP_ERROR_MESSAGE['111'],
	            			)
	            		)
            	),
            	array(
                    'name' => 'StringLength',
                    'options' => array(
                        'min' => 1,
                        'max' => 10,
		                'messages' => array(
		                    \Zend\Validator\StringLength::TOO_SHORT => $message->HTTP_ERROR_MESSAGE['112'],
		                    \Zend\Validator\StringLength::TOO_LONG => $message->HTTP_ERROR_MESSAGE['113'],
		                ),
                    ),
                ),
            	array(
                    'name' => 'Regex',
                    'options' => array(
                        'pattern' => '/^[a-zA-Z0-9_-]+$/',
		                'messages' => array(
		                    \Zend\Validator\Regex::NOT_MATCH  => $message->HTTP_ERROR_MESSAGE['1'],
		                ),
                    ),
                ),
            	array(
                    'name' => 'Regex',
                    'options' => array(
                        'pattern' => '/\A[^[:cntrl:]]*\z/u',
		                'messages' => array(
		                    \Zend\Validator\Regex::INVALID  => $message->HTTP_ERROR_MESSAGE['1'].'a',
		                ),
                    ),
                ),
            ),            
        ));
        $this->add(array(
            'name' => 'password',
            'required' => true,
            'filters' => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
            ),
            'validators' => array(
           		array(
					'name' =>'NotEmpty',
					'break_chain_on_failure' => true,
            		'options'                => array(
	            		'messages' => array(
	            			\Zend\Validator\NotEmpty::IS_EMPTY => $message->HTTP_ERROR_MESSAGE['114'],
	            			)
	            		)
            	),
           		array(
                    'name' => 'StringLength',
                    'options' => array(
                        'min' => 1,
                        'max' => 20,
		                'messages' => array(
		                    \Zend\Validator\StringLength::TOO_SHORT => $message->HTTP_ERROR_MESSAGE['115'],
		                    \Zend\Validator\StringLength::TOO_LONG => $message->HTTP_ERROR_MESSAGE['116'],
		                ),
                    ),
                ),
            	array(
                    'name' => 'Regex',
                    'options' => array(
                        'pattern' => '/^[a-zA-Z0-9_-]+$/',
		                'messages' => array(
		                    \Zend\Validator\Regex::NOT_MATCH  => $message->HTTP_ERROR_MESSAGE['1'],
		                ),
                    ),
                ),
            	array(
                    'name' => 'Regex',
                    'options' => array(
                        'pattern' => '/\A[^[:cntrl:]]*\z/u',
		                'messages' => array(
		                    \Zend\Validator\Regex::INVALID  => $message->HTTP_ERROR_MESSAGE['1'].'a',
		                ),
                    ),
                ),
            ),            
        ));

    }
    
    
    
}
