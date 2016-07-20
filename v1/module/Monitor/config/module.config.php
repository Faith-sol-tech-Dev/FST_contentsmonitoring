<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Monitor;

return array(
    'router' => array(
        'routes' => array(
            'home' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/',
                    'defaults' => array(
                        'controller' => 'Monitor\Controller\Index',
                        //'controller' => 'Monitor\Controller\Common',
                        'action'     => 'index',
                    ),
                ),
            ),
            // The following is a route to simplify getting started creating
            // new controllers and actions without needing to create a new
            // module. Simply drop new controllers in, and you can access them
            // using the path /application/:controller/:action
            'application' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/monitor',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Monitor\Controller',
                        'controller'    => 'Index',
                        'action'        => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/[:controller[/:action][/]]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                            'defaults' => array(
                            ),
                        ),
                    ),
//                    'test' => array(
//                        'type'    => 'Segment',
//                        'options' => array(
//                            'route'    => '/test/index[/:page][/]]',
//                            'constraints' => array(
//                                'controller' => 'Api\Controller\Test',
//                                'action'     => 'index',
//                            ),
//                        ),
//                    ),
                ),
            ),
        ),
    ),
    'service_manager' => array(
        'abstract_factories' => array(
            'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
            'Zend\Log\LoggerAbstractServiceFactory',
        ),
        'factories' => array(
            'translator' => 'Zend\Mvc\Service\TranslatorServiceFactory',
            'Zend\Db\Adapter\Adapter' => 'Zend\Db\Adapter\AdapterServiceFactory',
        ),
    ),
    'translator' => array(
        'locale' => 'ja_JP',
        'translation_file_patterns' => array(
            array(
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ),
            array(
                'type'     => 'phpArray',
                'base_dir' => __DIR__ . '/../data/language',
                'pattern'  => '%s.php',
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'Monitor\Controller\Index'    => 'Monitor\Controller\IndexController',
            'Monitor\Controller\Auth'     => 'Monitor\Controller\AuthController',
            'Monitor\Controller\Content'  => 'Monitor\Controller\ContentController',
            'Monitor\Controller\Report'   => 'Monitor\Controller\ReportController',
            'Monitor\Controller\Service'  => 'Monitor\Controller\ServiceController',
            'Monitor\Controller\User'     => 'Monitor\Controller\UserController',
            'Monitor\Controller\Recovery' => 'Monitor\Controller\RecoveryController',
            'Monitor\Controller\Error'    => 'Monitor\Controller\ErrorController',
            'Monitor\Controller\Sample'    => 'Monitor\Controller\SampleController',
        ),
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => array(
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'layout/cm_login_layout'  => __DIR__ . '/../view/layout/cm_login_layout.phtml',
            'monitor/index/index'     => __DIR__ . '/../view/monitor/index/index.phtml',
        	'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
    // Placeholder for console routes
    'console' => array(
        'router' => array(
            'routes' => array(
            ),
        ),
    ),
);
