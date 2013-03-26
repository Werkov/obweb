<?php

namespace Model\Org;

use Model\System\User;

class EventAdministratorModel extends \Model\UsersItemsModel {

    protected function IsUserValid(User $user) {
        return true;
    }

}