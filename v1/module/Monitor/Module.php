<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Monitor;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;


class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getServiceConfig()
    {
/*
        return array(
            'factories' => array(
                'ContentsMonitor\Service\Data\UserTable' =>  function($sm) {
                    $tableGateway = $sm->get('UserTableGateway');
                    $table = new UserTable($tableGateway);
                    return $table;
                },
                'UserTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new UserData());
                    return new TableGateway('MST_USER', $dbAdapter, null, $resultSetPrototype);
                },
            ),
        );
*/

        return array(
            'invokables' => array(
                'ContentsMonitor\Service\Data\UserTable' => 'ContentsMonitor\Service\Data\UserTable',
                'ContentsMonitor\Service\Data\UserPrivTable' => 'ContentsMonitor\Service\Data\UserPrivTable',
                'ContentsMonitor\Service\Data\ServiceTable' => 'ContentsMonitor\Service\Data\ServiceTable',
            	'ContentsMonitor\Service\Data\CornerTable' => 'ContentsMonitor\Service\Data\CornerTable',
            	'ContentsMonitor\Service\Data\ContentTable' => 'ContentsMonitor\Service\Data\ContentTable',
            	'ContentsMonitor\Service\Data\ContentDetailTable' => 'ContentsMonitor\Service\Data\ContentDetailTable',
            	'ContentsMonitor\Service\Data\ContentNGReportTable' => 'ContentsMonitor\Service\Data\ContentNGReportTable',
            	'ContentsMonitor\Service\Data\BatchTable' => 'ContentsMonitor\Service\Data\BatchTable',
            	'ContentsMonitor\Service\Data\BatchDetailTable' => 'ContentsMonitor\Service\Data\BatchDetailTable',
            	'ContentsMonitor\Service\Data\BatchLogTable' => 'ContentsMonitor\Service\Data\BatchLogTable',
            	'ContentsMonitor\Service\Data\BatchProcTable' => 'ContentsMonitor\Service\Data\BatchProcTable',
            	'ContentsMonitor\Service\Data\BatchLogContentTable' => 'ContentsMonitor\Service\Data\BatchLogContentTable',
            	'ContentsMonitor\Service\Data\BatchLogContentDetailTable' => 'ContentsMonitor\Service\Data\BatchLogContentDetailTable',
            	'ContentsMonitor\Service\Data\ApiErrorTable' => 'ContentsMonitor\Service\Data\ApiErrorTable',
            ),
/*            'initializers' => array(
                function ($instance, ServiceLocatorInterface $sm) {
                    if ($instance instanceof AdapterAwareInterface)
                    {
                        $instance->setDbAdapter($sm->get('Zend\Db\Adapter\Adapter'));
                    }
                }
            ),
*/
			'initializers' => array(
                'ContentsMonitor\Service\Db\AdapterInitializer',
            ),
        );
        
    }

}
