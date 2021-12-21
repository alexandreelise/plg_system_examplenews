<?php
declare(strict_types=1);
/**
 * System - Examplenews
 *
 * @package    Examplenews
 *
 * @author     Alexandre ELISÉ <contact@alexapi.cloud>
 * @copyright  Copyright(c) 2009 - 2021 Alexandre ELISÉ. All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://alexapi.cloud
 */

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Http\Response;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Input\Input;
use Joomla\Registry\Registry;

/**
 * Examplenews plugin.
 *
 * @package   Examplenews
 * @since     0.1.0
 */
class PlgSystemExamplenews extends CMSPlugin
{
	/**
	 * Application object
	 *
	 * @var    CMSApplication
	 * @since  0.1.0
	 */
	protected ?CMSApplication $app = null;

	/**
	 * Database object
	 *
	 * @var    JDatabaseDriver
	 * @since  0.1.0
	 */
	protected ?JDatabaseDriver $db = null;

	/**
	 * @param $subject
	 * @param $config
	 */
	public function __construct(&$subject, $config = [])
	{
		$this->autoloadLanguage = true;
		parent::__construct($subject, $config);
	}

	/**
	 * Fetch top-headlines articles from newsapi.org API using the Joomla! 3.10
	 * Http Client and the StreamTransport
	 * This is meant to be sample code as an example to guide you along the way to
	 * achieve your goal
	 * @return mixed
	 */
	public function onAjaxExamplenews()
	{
		if ($this->app->isClient('administrator'))
		{
			return;
		}

		$this->handleRequest($this->app->input);
	}

	public function onAfterInitialise()
	{

		if ($this->app->isClient('administrator'))
		{
			return;
		}

		$this->handleRequest($this->app->input);
	}


	/**
	 * Provide a way to test this method independently
	 *
	 * @param   \Joomla\Input\Input|null  $givenInput
	 *
	 * @return mixed
	 */
	private function handleRequest(?Input $givenInput = null)
	{
		$input = $givenInput ?? $this->app->input;

		//to be safe we should add a csrf-token verification
		// but since
		// a GET request is idempotent (does change with same input multiple times) we should be fine
		// If you feel it's insecuren, uncomment the next line
		// Session::checkToken('GET') || die(Text::_('JINVALID_TOKEN'));

		$verb = strtoupper($input->getMethod());

		try
		{
			switch ($verb)
			{
				case 'GET':

					$uri = new Joomla\CMS\Uri\Uri('https://newsapi.org/v2/top-headlines');

					$uri->setQuery(
						[
							'country'  => 'ca',
							'category' => 'business', 'apiKey' =>
								$this->params->get('apiKey', ''),
						]
					);

					$response          = new Response;
					$response->headers = [
						'Content-Type' => 'application/json',
					];
					$response->code    = 200;
					$response->body    = file_get_contents((string) $uri);

					// Render the response
					$this->render($response);
				default:
					throw new BadMethodCallException('HTTP Verb Unknown', 405);
			}
		}
		catch (Throwable $throwable)
		{
			header('Content-Type: application/json');
			echo (new Registry($throwable))->toString();
			die;
		}
	}

	/**
	 * @param   \Joomla\CMS\Http\Response  $response
	 *
	 * @return void
	 */
	private function render(Response $response)
	{
		// Clear headers first to prevent unexpected errors
		// then redefined custom ones in the render method
		$this->app->clearHeaders();

		foreach ($response->headers as $key => $value)
		{
			header(sprintf('%s: %s', $key, $value));
		}

		http_response_code($response->code);
		echo $response->body;
		die;
	}
}
