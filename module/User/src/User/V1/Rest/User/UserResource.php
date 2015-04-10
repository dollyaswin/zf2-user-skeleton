<?php
namespace User\V1\Rest\User;

use ZF\ApiProblem\ApiProblem;
use ZF\Rest\AbstractResourceListener;
use ZfcUser\Service\User as UserService;
use Zend\Stdlib\Hydrator;
use Zend\Stdlib\Hydrator\Aggregate\HydrateEvent;
use Zend\Form\Form;

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
     * @param UserService $userService
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
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
                        '/apigility/documentation/request#malformed',
                        'Malformed request',
                        array('messages' => $form->getMessages())
            );
        } else {
            // extract data from User Entity to array
            $hydrator = new Hydrator\ClassMethods();
            // filter password field
            $hydrator->addFilter(
                'password',
                new Hydrator\Filter\MethodMatchFilter('getPassword'),
                Hydrator\Filter\FilterComposite::CONDITION_AND
            );
            $return = $hydrator->extract($user);
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
        return new ApiProblem(405, 'The GET method has not been defined for individual resources');
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
        return new ApiProblem(405, 'The PUT method has not been defined for individual resources');
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
}
