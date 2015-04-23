<?php
namespace User;

use ZF\Apigility\Provider\ApigilityProviderInterface;
use ZF\MvcAuth\MvcAuthEvent;
use Zend\Mvc\MvcEvent;

class Module implements ApigilityProviderInterface
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager = $e->getApplication()->getEventManager();
        $services = $e->getApplication()->getServiceManager();
        $eventManager->attach(
            MvcEvent::EVENT_ROUTE,
            array($this, 'protectPages'),
            -100
        );
        
        $eventManager->attach(
            MvcAuthEvent::EVENT_AUTHORIZATION_POST,
            array($this, 'protectEntity'),
            100
        );
        
        // set username as identifier
        $halPlugin = $services->get('ViewHelperManager')->get('Hal');
        $halPlugin->getEventManager()->attach('renderEntity', function ($e) {
            $entity = $e->getParam('entity');
            foreach ($entity->getLinks() as $link) {
                $link->setRouteParams(['username' => $entity->entity['username']]);
            }
        });
    }
    
    /**
     * Get Module Configuration
     * 
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__ . '/../../config/module.config.php';
    }

    /**
     * Get Autoloader Configuration
     * 
     * @return multitype:multitype:multitype:string
     */
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
     * Protect Pages Authorization eeded
     * ex: /oauth/authorize, /oauth/receivecode
     * 
     * @param  MvcEvent $e
     * @return void
     */
    public function protectPages(MvcEvent $e)
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
    
    /**
     * Protect Entity From Forbidden Request
     * 
     * @param MvcAuthEvent $e
     * @param void
     */
    public function protectEntity(MvcAuthEvent $e)
    {
        $mvcEvent = $e->getMvcEvent();
        $username = $mvcEvent->getRouteMatch()->getParam('username', null);
        $identity = $e->getIdentity()->getName();
        if ($username !== $identity) {
            $response = $mvcEvent->getResponse();
            $response->setStatusCode(403);
            $response->setReasonPhrase('Forbidden');
        }
    }
}
