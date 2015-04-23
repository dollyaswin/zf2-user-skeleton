<?php
namespace User\V1\Rest\User;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Stdlib\Hydrator;
use ZF\ApiProblem\ApiProblem;
use ZF\Rest\AbstractResourceListener;
use ZfcUser\Service\User as UserService;
use ZfcUser\Entity\User  as ZfcUserEntity;
use Doctrine\ORM\EntityManagerInterface;

class UserResource extends AbstractResourceListener
{
    /**
     * User Service
     * 
     * @var ZfcUser\Service\User
     */
    protected $userService;
    
    /**
     * Constructor
     *
     * @param ServiceLocator $sl
     * @param UserService    $userService
     * @param EntityManager  $em
     */
    public function __construct(ServiceLocatorInterface $sl, UserService $userService, EntityManagerInterface $em)
    {
        $this->userService    = $userService;
        $this->serviceLocator = $sl;
        $this->entityManager  = $em;
    }
    
    /**
     * Create a resource
     *
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function create($data)
    {
        $hydrator = new Hydrator\ObjectProperty();
        // extract data from request object to array
        $postData = $hydrator->extract($data);
        $user = $this->getUserService()->register($postData);
        if ($user === false) {
            // get form error messages
            $form   = $this->getUserService()->getRegisterForm();
            // compose error response
            $return = new ApiProblem(
                400,
                'The field you sent was malformed',
                '/api/v1/documentation/request#malformed',
                'Malformed request',
                array('messages' => $form->getMessages())
            );
        } else {
            $return = $this->hydrate($user);
        }
        
        return $return;
    }

    /**
     * Delete a resource
     *
     * @param  mixed $id
     * @return ApiProblem|mixed
     */
    public function delete($id)
    {
        return new ApiProblem(405, 'The DELETE method has not been defined for individual resources');
    }

    /**
     * Delete a collection, or members of a collection
     *
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function deleteList($data)
    {
        return new ApiProblem(405, 'The DELETE method has not been defined for collections');
    }

    /**
     * Fetch a resource
     *
     * @param  mixed $id
     * @return ApiProblem|mixed
     */
    public function fetch($id)
    {
        if (!$this->isAuthorize($id)) {
            return new ApiProblem(403, "You don't have access for this resource");
        }
        
        $user = $this->entityManager->getRepository('ZfcUserDoctrineORM\Entity\User')
                    ->findOneBy(['username' => $id]);
        
        return $this->hydrate($user);
    }

    /**
     * Fetch all or a subset of resources
     *
     * @param  array $params
     * @return ApiProblem|mixed
     */
    public function fetchAll($params = array())
    {
        return new ApiProblem(405, 'The GET method has not been defined for collections');
    }

    /**
     * Patch (partial in-place update) a resource
     *
     * @param  mixed $id
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function patch($id, $data)
    {
        return new ApiProblem(405, 'The PATCH method has not been defined for individual resources');
    }

    /**
     * Replace a collection or members of a collection
     *
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function replaceList($data)
    {
        return new ApiProblem(405, 'The PUT method has not been defined for collections');
    }

    /**
     * Update a resource
     *
     * @param  mixed $id
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function update($id, $data)
    {
        return new ApiProblem(405, 'The PUT method has not been defined for collections');
    }
    
    /**
     * Get UserService
     * 
     * @return ZfcUser\Service\User
     */
    public function getUserService()
    {
        return $this->userService;
    }
    
    /**
     * Hydrate User Object
     * 
     * @param  ZfcUser\Entity\User $user
     * @return array
     */
    protected function hydrate(ZfcUserEntity $user)
    {
        $hydrator = $this->getUserService()->getFormHydrator();
        // filter password field
        $hydrator->addFilter(
            'password',
            new Hydrator\Filter\MethodMatchFilter('getPassword'),
            Hydrator\Filter\FilterComposite::CONDITION_AND
        );
        
        return $hydrator->extract($user);
    }
}
