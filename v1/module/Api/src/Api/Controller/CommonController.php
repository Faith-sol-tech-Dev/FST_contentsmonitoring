<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link	  http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Api\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\ViewModel;

class CommonController extends AbstractActionController
{
	public function onDispatch(MvcEvent $e)
	{
		require 'vendor/ContentsMonitor/Common/loaclConst.php';
		return parent::onDispatch($e);
	}
	
	protected function createResponse($code)
	{
		$response = $this->getResponse();
		$response->setStatusCode($code);
		$response->setContent('');
		return $response;
	}
}
