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

use AE\Library\ExampleNews\FetchArticles;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Plugin\CMSPlugin;

JLoader::registerNamespace('\\AE\\Library\\ExampleNews\\', __DIR__ . '/classes/AE/Library/ExampleNews', false, false, 'psr4');

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
		//to be safe we should add a csrf-token verification
		// but since a GET request is idempotent (does change with same input multiple times) we should be fine
		// If you feel it's insecuren, uncomment the next line
		// Session::checkToken('GET') || die(Text::_('JINVALID_TOKEN'));
		
		$verb = strtoupper($this->app->input->getMethod());
		
		switch ($verb)
		{
			case 'GET':
				$fetchNewsArticles = new FetchArticles(' https://newsapi.org', 'v2/top-headlines', ['country' => 'ca', 'category' => 'business'], $this->app->input->getAlnum('apiKey', ''), null);
				$response          = $fetchNewsArticles(['Accept' => 'application/json', 'Content-Type' => 'application/json']);
				$fetchNewsArticles->render($response);
			default:
				throw new BadMethodCallException('HTTP VERB Unknown', 405);
		}
	}
}
