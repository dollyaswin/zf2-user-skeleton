<?php
namespace User\V1\Rest\User;

class UserResourceFactory
{
    public function __invoke($services)
    {
        $userService   = $services->get('zfcuser_user_service');
        $entityManager = $services->get('zfcuser_doctrine_em');
        return new UserResource($services, $userService, $entityManager);
    }
}
