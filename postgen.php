<?php

use PostGen\User;

require 'vendor/autoload.php';
require_once 'User.php';

$users = [
	new User('admin', 'pass123'),
	new User('user1', 'pass123'),
	new User('user2', 'pass123')
];

foreach ($users as $user)
{
	$user->login();
}