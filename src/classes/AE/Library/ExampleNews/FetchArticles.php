<?php

namespace AE\Library\ExampleNews;

use InvalidArgumentException;
use Joomla\CMS\Http\Http;
use Joomla\CMS\Http\HttpFactory;
use Joomla\CMS\Http\Response;
use Joomla\Registry\Registry;
use function header;
use function http_response_code;
use function json_encode;
use function sprintf;

final class FetchArticles
{
	private string $baseUrl = 'https://newsapi.org';

	private string $basePath = 'v2/top-headlines';
	
	private array $data = [];
	
	private string $apiKey = '';
	
	private string $fullUrl = '';
	
	private ?Http $httpClient = null;
	
	/**
	 * @var \Joomla\CMS\Http\Http|null
	 */
	private ?Http $http;
	
	/**
	 * @param   string                      $baseUrl
	 * @param   string                      $basePath
	 * @param   array                       $data
	 * @param   string                      $apiKey
	 * @param   \Joomla\CMS\Http\Http|null  $http
	 */
	public function __construct(string $baseUrl, string $basePath, array $data = [], string $apiKey = '', ?Http $http = null)
	{
		if (empty($baseUrl))
		{
			throw new InvalidArgumentException('Base url cannot be empty', 422);
		}
		
		if (empty($basePath))
		{
			throw new InvalidArgumentException('Base url cannot be empty', 422);
		}
		
		$this->baseUrl  = $baseUrl;
		$this->basePath = $basePath;
		
		$this->fullUrl = sprintf('%s/%s', $this->baseUrl, $this->basePath);
		
		$this->data   = $data;
		$this->apiKey = $apiKey;
		
		// use the StreamTransport rather than the default CurlTransport
		$options = new Registry;
		
		// More control when creating a Http client using getAvailableDriver static method
		$myHttpClient = HttpFactory::getAvailableDriver($options, ['stream', 'curl']);
		
		$this->httpClient = $http ?? $myHttpClient;
	}
	
	/**
	 * @param   array  $data
	 * @param   array  $headers
	 * @param   int    $timeout
	 *
	 * @return \Joomla\CMS\Http\Response
	 */
	public function __invoke(array $data = [], array $headers = [], int $timeout = 7)
	{
		// With this request method you can use any HTTP supported verbs it is more
		// fine grained and it is used under the hood by the other methods (get, post,etc...)
		return $this->httpClient->request('GET', $this->fullUrl, json_encode(array_merge($this->data, $data)), $headers, $timeout);
	}
	
	/**
	 * @param   \Joomla\CMS\Http\Response  $response
	 *
	 * @return void
	 */
	public function render(Response $response)
	{
		foreach ($response->headers as $key => $value)
		{
			header(sprintf('%s: %s', $key, $value));
		}
		
		http_response_code($response->code);
		echo $response->body;
		die;
	}
	
}
