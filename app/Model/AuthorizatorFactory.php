<?php

declare(strict_types=1);

namespace App\Model;

use Nette;

class AuthorizatorFactory
{
    public static function create(): Nette\Security\Permission
    {
        $acl = new Nette\Security\Permission();


        return $acl;
    }
}
