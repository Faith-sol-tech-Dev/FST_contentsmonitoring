<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link	  http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Api\Controller;

//use Zend\Mvc\Controller\AbstractActionController;
//use Zend\View\Model\ViewModel;

use Zend\Http\Response;

use ContentsMonitor\Common\LogClass as Log;
use ContentsMonitor\Common\UtilityClass as Utility;

use Api\Model\ImportContentApiForm;

class ImportContentApiController extends CommonController
{
	public function indexAction()
	{
		Log::debug(__FILE__, __LINE__, 'BEGIN indexAction()');
		$start_time=microtime(true);
		try {
			$request = $this->getRequest();
			
			$debug = true;
			if ($debug) {
				$params = array();
				if ($request->isGet()) {
					Log::batch(__FILE__, __LINE__, 'Request type id GET');
					$params['batch_id'] = (int)$this->params()->fromQuery('batch_id');
					$params['trigger_type'] = (int)$this->params()->fromQuery('trigger_type');
					$params['data'] = $this->params()->fromQuery('data');
				} elseif ($request->isPost()) {
					Log::batch(__FILE__, __LINE__, 'Request type id POST');
					$params['batch_id'] = $this->params()->fromPost('batch_id');
					$params['trigger_type'] = $this->params()->fromPost('trigger_type');
					$params['data'] = $this->params()->fromPost('data');
				} else {
					// error
					Log::batch(__FILE__, __LINE__, '不正なリクエストです');
					throw new \Exception($request->getMethod(), Response::STATUS_CODE_405);
				}
			} else {
				if (!$request->isPost()) {
					// error
					Log::batch(__FILE__, __LINE__, 'Request type id NOT POST');
					throw new \Exception($request->getMethod(), Response::STATUS_CODE_405);
				}
				$params = array();
				$params['batch_id'] = $this->params()->fromPost('batch_id');
				$params['trigger_type'] = $this->params()->fromPost('trigger_type');
				$params['data'] = $this->params()->fromPost('data');
			}
			
			Log::batch(__FILE__, __LINE__, 'Param//batch_id : '.$params['batch_id']);
			Log::batch(__FILE__, __LINE__, 'Param//trigger_type : '.$params['trigger_type']);
			Log::batch(__FILE__, __LINE__, 'Param//data : '.$params['data']);
			
			$importContentApiForm = new ImportContentApiForm($this->getServiceLocator());
			$resultCode = $importContentApiForm->import($params);
		} catch (\Exception $e) {
			if ($e->getCode() == 0) {
				$resultCode = Response::STATUS_CODE_500;
			} else {
				$resultCode = $e->getCode();
			}
			Log::batch($e->getFile(), $e->getLine(), $e->getMessage());
		} finally {
			$diff_time = Utility::formatMicrotime(microtime(true) - $start_time);
			Log::debug(__FILE__, __LINE__, 'END indexAction() -- ('.$diff_time.')');
        }
		
		return $this->createResponse($resultCode);
	}
}
