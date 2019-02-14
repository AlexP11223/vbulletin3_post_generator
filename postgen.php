<?php

use PostGen\User;

require 'vendor/autoload.php';
require 'utils.php';
require 'User.php';

const THREADS_COUNT = 100;
const MAX_REPLIES_COUNT = 21;

/** @var User[] $users */
$users = [
	new User('admin', 'pass123'),
	new User('user1', 'pass123'),
	new User('user2', 'pass123')
];

foreach ($users as $user) {
	$user->login();
}

$forum_ids = array_diff(range(6, 35), [23]);

for ($i = 0; $i < THREADS_COUNT; ++$i) {
	$postId = rand_value($users)->generateThread(rand_value($forum_ids));
	for ($j = rand(0, MAX_REPLIES_COUNT) - 1; $j >= 0; --$j) {
		rand_value($users)->generateReply($postId);
	}
}
