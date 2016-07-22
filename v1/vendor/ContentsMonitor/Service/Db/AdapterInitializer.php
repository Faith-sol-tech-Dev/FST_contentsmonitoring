<?php
namespace ContentsMonitor\Service\Db;

use Zend\ServiceManager\InitializerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\Adapter\AdapterAwareInterface;

/**
 * 監視サイトで使用するDBアクセス用のアダプタインターフェイス
 *
 * @package ContentsMonitor
 * @author  FST goto
 * @since   PHP 5.6, Zendframwork 2.4.9
 * @version 1.0.0
 */
class AdapterInitializer implements InitializerInterface
{
    public function initialize($instance, ServiceLocatorInterface $serviceLocator)
    {
        if ($instance instanceof AdapterAwareInterface)
        {
            $instance->setDbAdapter($serviceLocator->get('Zend\Db\Adapter\Adapter'));
        }
    }
}
