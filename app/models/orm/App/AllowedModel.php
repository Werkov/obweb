<?php

namespace Model\App;

use Model\System\User;

class AllowedModel extends \Model\UsersItemsModel {

    private $exceptId;

    public function __construct($exceptId) {
        $this->exceptId = $exceptId;
        parent::__construct();
    }

    protected function IsUserValid(User $user) {
        return $user->id != $this->exceptId;
    }
}