<?php

declare(strict_types=1);

use RedBeanPHP\R;
use RedBeanPHP\ToolBox;

require __DIR__.'/../vendor/autoload.php';

App\Bootstrap::boot()
    ->createContainer()
    ->getByType(ToolBox::class);

$faker = Faker\Factory::create();

R::nuke();

[$admin, $user] = R::dispense('role', 2);

$admin->name = 'admin';
$user->name = 'user';

R::storeAll([$admin, $user]);

$user = R::dispense('user');
$user->name = $faker->name();
$user->facebook_id = $faker->uuid();
$user->photo = $faker->url();
$user->remember_token = $faker->password();
$user->sharedRoleList[] = R::load('role', 2);

R::store($user);
R::wipe('user');
