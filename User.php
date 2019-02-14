<?php

namespace PostGen;

use GuzzleHttp\Client;
use Sunra\PhpSimple\HtmlDomParser;

const BASE_URI = 'http://localhost';

class User
{
	private $name;
	private $password;

	/** @var Client $client */
	private $client;

	/** @var \Faker\Generator[] $fakers */
	private $fakers;

	public function __construct($name, $password)
	{
		$this->name = $name;
		$this->password = $password;
		$this->fakers = [
			'en' => \Faker\Factory::create('en_US'),
			'ru' => \Faker\Factory::create('ru_RU')
		];
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

	/**
	 * @param $forumId
	 * @return int post ID
	 * @throws \Exception
	 */
	public function generateThread($forumId)
	{
		$this->log("Creating thread in forum $forumId");

		$url = "/newthread.php?do=newthread&f=$forumId";

		$html = $this->client->get($url)->getBody()->getContents();

		$response = $this->client->post($url, [
			'allow_redirects' => false,
			'form_params' => array_merge($this->getHiddenParams($html), [
				'subject' => $this->generateThreadSubject(),
				'message' => $this->generateMessageText(),
				'iconid' => 0,
				'taglist' => '',
				'parseurl' => 1,
				'emailupdate' => 9999 // no
			])
		]);

		if ($response->getStatusCode() != 302) {
			var_dump($response);
			throw new \Exception($this->msg('Failed to create thread'));
		}

		$threadUrl = $response->getHeader('Location')[0];
		$parsedUrl = parse_url($threadUrl);
		if (!$parsedUrl) {
			var_dump($response);
			throw new \Exception($this->msg("Failed to parse URL '$threadUrl'"));
		}

		parse_str($parsedUrl['query'], $query);
		$id = (int) $query['p'];
		if (!$id) {
			var_dump($response);
			throw new \Exception($this->msg("Failed to parse URL '$threadUrl'"));
		}

		$this->log("Created thread, post $id");

		return $id;
	}

	function generateReply($postId)
	{
		$html = $this->client->get("/newreply.php?do=newreply&noquote=1&p=$postId")->getBody()->getContents();

		$hiddenParams = $this->getHiddenParams($html);
		$threadId = (int) $hiddenParams['t'];
		if (!$threadId) {
			var_dump($html);
			throw new \Exception($this->msg("Failed to find thread id"));
		}

		$this->log("Replying to post $postId in thread $threadId");

		$response = $this->client->post("/newreply.php?do=postreply&t=$threadId", [
			'allow_redirects' => false,
			'form_params' => array_merge($this->getHiddenParams($html), [
				'title' => '',
				'message' => $this->generateMessageText(),
				'iconid' => 0,
				'parseurl' => 1,
				'rating' => 0,
				'emailupdate' => 9999 // no
			])
		]);

		if ($response->getStatusCode() != 302) {
			var_dump($response);
			throw new \Exception($this->msg('Failed to reply'));
		}
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

	/**
	 * @return string
	 */
	private function generateMessageText() {
		return utf8_to_cp1251(implode("\r\n\r\n", array_map(function () {
			return rand_value($this->fakers)->realText;
		}, range(1, rand(1, 3)))));
	}

	/**
	 * @return string
	 */
	private function generateThreadSubject() {
		return utf8_to_cp1251(rand_value($this->fakers)->realText(80));
	}

	/**
	 * @param $html
	 * @return array
	 */
	private function getHiddenParams($html)
	{
		$dom = HtmlDomParser::str_get_html($html);
		return flatten(array_map(function ($el) {
			return [$el->name => $el->value];
		}, $dom->find('input[type=hidden]')));
	}
}