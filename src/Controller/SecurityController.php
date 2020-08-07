<?php

namespace App\Controller;


class SecurityController extends AbstractController
{

    protected function createNewUserEntity()
    {
        return $this->get('fos_user.user_manager')->createUser();
    }

    protected function persistUserEntity($user)
    {
        $this->get('fos_user.user_manager')->updateUser($user, false);
        parent::persistEntity($user);
    }

    protected function updateUserEntity($user)
    {
        $this->get('fos_user.user_manager')->updateUser($user, false);
        parent::updateEntity($user);
    }

}
