<?php
namespace User;

use ZF\Apigility\Provider\ApigilityProviderInterface;
use Zend\Mvc\MvcEvent;

class Module implements ApigilityProviderInterface
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager = $e->getApplication()->getEventManager();
        $services = $e->getApplication()->getServiceManager();
        $eventManager->attach(
            MvcEvent::EVENT_ROUTE,
            array($this, 'protectAuthorization'),
            -100
        );
    }
    
    public function getConfig()
    {
        return include __DIR__ . '/../../config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'ZF\Apigility\Autoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__,
                ),
            ),
        );
    }
    
    /**
     * Protect Page Need Authorized
     * 
     * @param MvcEvent $e
     */
    public function protectAuthorization(MvcEvent $e)
    {
        $match = $e->getRouteMatch();
        $services   = $e->getApplication()->getServiceManager();
        $controller = $match->getParam('controller');
        $action     = $match->getParam('action'); // 'authorize'
    
        if ($controller === 'ZF\OAuth2\Controller\Auth' &&
            in_array($action, ['authorize', 'receiveCode'])
        ) {
            // if doesn't have identity redirect to login with redirection
            if ($services->get('zfcuser_auth_service')->hasIdentity() === false) {
                $requestUri = $services->get('Request')->getServer('REQUEST_URI');
                $request = $e->getRequest();
                $request->setQuery(new \Zend\Stdlib\Parameters(['redirect' => ltrim($requestUri, '/')]));
                $match->setParam('controller', 'zfcuser');
                $match->setParam('action', 'login');
            }
        }
    }
}
