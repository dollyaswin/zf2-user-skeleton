<?php
namespace User\V1\Rest\User;

class UserResourceFactory
{
    public function __invoke($services)
    {
        $userService = $services->get('zfcuser_user_service');
        return new UserResource($userService);
    }
}
