<?php

require 'utils.php';

use PostGen\User;

require 'vendor/autoload.php';
require_once 'User.php';

/** @var User[] $users */
$users = [
	new User('admin', 'pass123'),
	new User('user1', 'pass123'),
	new User('user2', 'pass123')
];

foreach ($users as $user)
{
	$user->login();
}

$forum_ids = array_diff(range(6, 35), [23]);

rand_value($users)->generateThread(rand_value($forum_ids));
