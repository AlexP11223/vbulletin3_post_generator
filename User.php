<?php

namespace PostGen;

use GuzzleHttp\Client;
use Sunra\PhpSimple\HtmlDomParser;

const BASE_URI = 'http://localhost';

class User
{
	private $name;
	private $password;
	private $client;

	public function __construct($name, $password)
	{
		$this->name = $name;
		$this->password = $password;
	}

	/**
	 * @param $text
	 * @return string
	 */
	private function msg($text)
	{
		return '* ' . $this->name . ' * ' . $text;
	}

	private function log($text)
	{
		echo $this->msg($text) . "\n";
	}

	public function login()
	{
		$this->client = new Client([
			'base_uri' => BASE_URI,
			'cookies' => new \GuzzleHttp\Cookie\CookieJar(),
			//'proxy' => 'localhost:8888',
		]);

		$this->client->get('/');

		$this->client->post('/login.php?do=login', [
			'form_params' => [
				'vb_login_username' => $this->name,
				'vb_login_password' => $this->password,
				'cookieuser' => 1,
				's' => '',
				'securitytoken' => 'guest',
				'do' => 'login'
			]
		]);

		$response = $this->client->get('/');

		$dom = HtmlDomParser::str_get_html($response->getBody()->getContents());
		if (!$dom->find('a[href=usercp.php]'))
			throw new \Exception($this->msg('Failed to log in'));

		$this->log('Logged in');
	}
}