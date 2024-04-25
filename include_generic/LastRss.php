<?php

/**
 * LastRss
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the New BSD License.
 *
 * @name       LastRss
 * @version    2.0.1
 * @link       http://lastrss.oslab.net
 * @copyright  Copyright (c) 2011 Vojtech Semecky
 * @license    New BSD http://en.wikipedia.org/wiki/BSD_licenses
 * @author     Vojtech Semecky
 *
 */
class LastRss
{
	/**
	 * cURL options
	 * @var array
	 */
	private $curlOptions = array(
		CURLOPT_HEADER => false,
		CURLOPT_CONNECTTIMEOUT => 2,
		CURLOPT_TIMEOUT => 5,
//		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_MAXREDIRS => 3,
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_FAILONERROR => true,
		CURLOPT_ENCODING => 'gzip,deflate',
	);

	/**
	 * Error strings
	 */
	private static $parseError = 'Parse error: Invalid RSS/XML document (%s). Please check RSS by http://feedvalidator.org/.';
	private static $downloadError = 'Download error: %s';
	private static $fileOpenErorr = 'File error: Unable to open file %s';

	private $cacheDir = "./cache";
	private $cacheTime = 3600; // in seconds
	
	
	/**
	 * If true, HTML will be stripped from HTML is suspected tags
	 * @var boolean
	 */
	private $stripHtml = true;

	/**
	 * Array of tags where HTML is suspected
	 */
	private $htmlSuspected = array('title', 'description');

	private $channelTags = array('title', 'link', 'description', 'lastBuildDate');
	private $itemTags = array('title', 'link', 'description', 'pubDate', 'guid');

	/**
	 * Maximum number of items to be returned
	 */
	private $itemsLimit = 0;

	/**
	 * @var string Date time format for date related RSS items
	 */
	private $dateFormat = null;

	/**
	 * XML name spaces
	 */
	private static $xmlns = array(
		// @todo: asi se nikde nepouziva - smazat
		'atom' => 'http://www.w3.org/2005/Atom',
		'content' => 'http://purl.org/rss/1.0/modules/content/',
		'wfw' => 'http://wellformedweb.org/CommentAPI/',
		'dc' => 'http://purl.org/dc/elements/1.1/',
		'sy' => 'http://purl.org/rss/1.0/modules/syndication/',
		'slash' => 'http://purl.org/rss/1.0/modules/slash/',
	);

	/**
	 * XML name spaces used in used tags
	 */
	private $usedXmlns = array();

	public $lastError = null;

	/**
	 * Constructor to initialize all options
	 */
	public function __construct( $options = array() )
	{
		foreach ($options as $name => $value) {
			$this->$name = $value;
		}
	}

	/**
	 * Register all xml namespaces used in channel tags or item tags.
	 */
	private function registerNameSpaces(&$xpath)
	{
		foreach ($this->usedXmlns as $ns) {
			$xpath->registerNamespace($ns, '');
		}
	}

	/**
	 * Recalc array of all xml namespaces used in channel tags or item tags.
	 */
	private function calcNamespaces()
	{
		// Get all XML name spaces used in required tags
		$tags = implode(',', array_merge($this->channelTags, $this->itemTags));
		preg_match_all("'([a-zA-Z0-9]+):'si", $tags, $namespaces);
		$this->usedXmlns = $namespaces[1];
	}

	/**
	 * Set required channel tags
	 * @param array $tags (default is array('title', 'link', 'description')
	 */
	public function setChannelTags($tags) {
		$this->channelTags = $tags;
		$this->calcNamespaces();
		return $this;
	}

	/**
	 * Set required item tags
	 * @param array $tags Default is array('title', 'link', 'description', 'pubDate', 'guid')
	 */
	public function setItemTags($tags) {
		$this->itemTags = $tags;
		$this->calcNamespaces();
		return $this;
	}

	/**
	 * Set date/time format for date related items (lastBuildDate, pubDate).
	 * To disable date format conversion set date format to null (default value).
	 * @param string $dateFormat Date/time format compatible with PHP function date().
	 */
	public function setDateFormat($dateFormat) {
		$this->dateFormat = $dateFormat;
		return $this;
	}

	/**
	 * Fotmat the input variable into $dateFormat
	 *
	 * If input value is not valid date, change input value to null.
	 * If $dateFormat is specified, do nothing.
	 * @param string $dateString
	 */
	private function formatDate(&$dateString) {
		if ($this->dateFormat) {
			$timeStamp = strtotime($dateString);
			$dateString = ($timeStamp) ? date($this->dateFormat, $timeStamp) : null;
		}
	}

	/**
	 * Get URL content using CURL
	 * @param $url String
	 * @return String Returns XML string on success, FALSE on failure.
	 */
	private function loadUrl($url)
	// @todo: prejmenovat na downloadUrl()
	{
		if (!function_exists('curl_init')) {
			return file_get_contents($url);
		}
		$ch = curl_init();
		curl_setopt_array($ch, $this->curlOptions);
		if (!ini_get('safe_mode') && !ini_get('open_basedir'))
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_URL, $url);
		if (!($content = curl_exec($ch))) {
			$this->lastError = sprintf(self::$downloadError, curl_error($ch));
		}
		curl_close($ch);
		return $content;
	}

	/**
	 * Parse xmlData and return parsed result
	 *
	 */
	private function parse(&$xmlData)
	{
		if (!$xmlData)
		{
			return false;
		}
		$result = array();
		$doc = new DomDocument();
		// Try to load XML or return parse error
		if (false == @$doc->loadXml($xmlData)) {
			// Try to fix XML by Tidy and try to load one more time
			if (function_exists("tidy_repair_string"))
				$xmlData = tidy_repair_string($xmlData, array('output-xml' => true, 'input-xml' => true), 'utf8');
			if (false == @$doc->loadXml($xmlData)) {
				$this->lastError = self::$parseError;
				return false;
			}
		}
		unset($xmlData);
		$xpath = new DOMXPath($doc);

		// Register xml name spaces
		$this->registerNamespaces($xpath);

		// Parse channel tags
		foreach($this->channelTags as &$tag) {
			$result[$tag] = $xpath->evaluate("string(channel/$tag)");
			if ($tag == 'lastBuildDate') {
				$this->formatDate($result[$tag]);
			}
		}

		// Parse items
		// @todo: Zjistit, co bude rychlejsi
		//$items = $doc->getElementsByTagName('item');
		$items = $xpath->evaluate('/rss/channel/item');

		foreach($items as $item) {
			$tmpItem = array();

			// Parse item tags
			foreach ($this->itemTags as &$tag) {
				switch ($tag) {
					case 'pubDate':
						$tmpItem[$tag] = $xpath->evaluate('string(pubDate)', $item);
						$this->formatDate($tmpItem[$tag]);
						break;
					case 'category':
						// Category is multivalue tag
						$tmpItem[$tag] = array();
						foreach ($xpath->query('category', $item) as $node) {
							$tmpItem[$tag][] = trim($node->nodeValue);
						}
						break;
					case 'title':
					case 'description':
						$tmpItem[$tag] = $xpath->evaluate("string($tag)", $item);
						if ($this->stripHtml) {
							$tmpItem[$tag] = strip_tags($tmpItem[$tag]);
							$tmpItem[$tag] = html_entity_decode($tmpItem[$tag], ENT_QUOTES, 'UTF-8');
						}
						$tmpItem[$tag] = trim($tmpItem[$tag]);
						break;
					default:
						$tmpItem[$tag] = trim($xpath->evaluate("string($tag)", $item));
						break;
				}
			}

			$result['items'][] = $tmpItem;

			// If limit number of items is reached, stop processing remaining items
			if (count($result['items']) == $this->itemsLimit) {
				break;
			}
		}

		// Calc items count
		$result['itemsCount'] = isset($result['items']) ? count($result['items']) : 0;

		return $result;
	}

	/**
	 * Fetch RSS/Atom feed from remote URL
	 * @param string $url URL
	 * @return array
	 */
	public function getUrl($url)
	{
		$this->lastError = '';
		// Get feed content
		$xmlData = $this->loadUrl($url);
		return $this->parse($xmlData);
	}

	/**
	 * Fetch RSS/Atom feed from local file
	 * @param string $filename Local file name
	 * @return array
	 */
	public function getFile($filename)
	{
		$this->lastError = '';
		$xmlData = file_get_contents($filename);
		return $this->parse($xmlData);
	}

	/**
	 * Fetch RSS/Atom feed from remote URL, but retrieve cache if possible
	 * @param string $url URL
	 * @return array
	 */
	public function get($url)
	{
		$cache_file = $this->cacheDir . '/rsscache_' . preg_replace("/[^a-z0-9A-Z_]+/","_",$url);
		$timedif = @(time() - filemtime($cache_file));
		if ($timedif > $this->cacheTime) 
		{
			$xmlData = $this->loadUrl($url);
			$result = $this->parse($xmlData);
			if ($result && $result['itemsCount'] > 0)
			{
				@file_put_contents($cache_file,serialize($result));
				$result["cached"] = false;
			}
		}
		if (@!$result['itemsCount'])
		{
			$result = @unserialize( file_get_contents($cache_file) ) ?: array();
			$result["cached"] = true;
		}
		
		return $result;
	}

	/**
	 * Get last error message
	 * @return string Error description
	 */
	public function getLastError() {
		return $this->lastError;
	}

	/**
	 * @param int $limit Maximum number of items. Default is 0 which means "no limit".
	 */
	public function setItemsLimit($limit = 0) {
		$this->itemsLimit = (int) $limit;
		return $this;
	}
}
