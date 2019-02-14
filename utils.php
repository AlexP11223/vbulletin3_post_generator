<?php

function rand_value($arr)
{
	return $arr[array_rand($arr)];
}

function flatten($arr)
{
	return array_merge(...$arr);
}

function utf8_to_cp1251($str)
{
	return mb_convert_encoding($str, "windows-1251","utf-8");
}