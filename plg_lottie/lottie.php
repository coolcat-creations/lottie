<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.Lottie
 *
 * @copyright   Copyright (C) 2017 Coolcat-Creations.com, Elisa Foltyn.
 * @license     GNU General Public License version 3 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Plugin\CMSPlugin;

/**
 * Class to implemet lottie support.
 *
 * @package     Joomla.Plugin
 * @subpackage  Content.Lottie
 *
 * @since  1.0.0
 */
class PlgContentLottie extends CMSPlugin
{
	/**
	 * The regular expression to identify Plugin call.
	 *
	 * @var     string
	 * @since   1.0.0
	 */
	const PLUGIN_REGEX = "@(<(\w+)[^>]*>)?{lottie}(.*?){/lottie}(</\\2>)?@";

	/**
	 * Global application object
	 *
	 * @var     JApplication
	 * @since   1.0.1
	 */
	protected $app;

	/**
	 * Set counter
	 *
	 * @var     int
	 * @since   1.0.0
	 */
	private static $count = 0;


	/**
	 * Plugin to generates Forms within content
	 *
	 * @param   string   $context  The context of the content being passed to the plugin.
	 * @param   object   $article  The article object.  Note $article->text is also available
	 * @param   mixed    $params   The article params
	 * @param   integer  $page     The 'page' number
	 *
	 * @return   void
	 * @since    1.0.0
	 */
	public function onContentPrepare($context, &$article, &$params, $page = 0)
	{
		// Don't run this plugin when the content is being indexed
		if ($context == 'com_finder.indexer'
			// Don't run this plugin when we edit an custom module in frontend
			|| $this->app->input->getCmd('option') == 'com_config'
			// Don't run this plugin when we edit the content in frontend
			|| $this->app->input->getCmd('layout') == 'edit')
		{
			return;
		}

		if (is_object($article)
			&& strpos($article->text, '{/lottie}') !== false)
		{
			$this->lottieToAnimatation($article->text);
		}

		if (is_string($article)
			&& strpos($article, '{/lottie}') !== false)
		{
			$this->lottieToAnimatation($article);
		}
	}

	/**
	 * Replace plugin call with lottie html
	 *
	 * @param   string  $text  Article content to parse
	 *
	 * @return  void
	 *
	 * @since  1.0.0
	 */
	protected function lottieToAnimatation(&$text)
	{
		// Get all matches or return
		if (!preg_match_all(self::PLUGIN_REGEX, $text, $matches))
		{
			return;
		}

		// Exclude <code/> and <pre/> matches
		$code = array_keys($matches[1], '<code>');
		$pre  = array_keys($matches[1], '<pre>');

		if (!empty($code) || !empty($pre))
		{
			array_walk($matches,
				function (&$array, $key, $tags) {
					foreach ($tags as $tag)
					{
						if ($tag !== null && $tag !== false)
						{
							unset($array[$tag]);
						}
					}
				}, array_merge($code, $pre)
			);
		}

		$pluginCalls = $matches[0];
		$callParams  = $matches[3];
		$layout      = new FileLayout('default');

		// Define include path for layout and override
		$includePaths = array(
			JPATH_THEMES . '/' . $this->app->getTemplate() . '/html/plg_content_lottie',
			JPATH_PLUGINS . '/content/lottie/tmpl',
		);

		$layout->setIncludePaths($includePaths);

		foreach($pluginCalls as $key => $match)
		{
			$replacement = '';

			$displayData = array(
				'counter'    => self::$count,
				'attributes' => $this->getLottieAttributes($callParams[$key]),
			);

			if (!empty($displayData['attributes']))
			{
				$replacement = $layout->render($displayData);

				// Increment conter for unique lottie id
				self::$count++;
			}

			$pos = strpos($text, $match);
			$end = strlen($match);

			$text = substr_replace($text, $replacement, $pos, $end);
		}
	}

	/**
	 * Generate the attributes of lottie output container
	 *
	 * @param   string  $userParams  Comma separated values for:
	 *                               1. Path to lottie json file
	 *                               2. Boolean for loop (true/false)
	 *                               3. Value for height. If is numeric, automaticaly 'px' will be added.
	 *
	 * @return  array
	 *
	 * @since  1.0.1
	 */
	private function getLottieAttributes($userParams)
	{
		$attributes = array();

		$params    = explode(',', $userParams);
		$params    = array_map('trim', $params);

		if (empty($params[0]) || !file_exists(JPATH_ROOT . '/' . $params[0]))
		{
			return $attributes;
		}

		$params    = array_map('htmlentities', $params);
		$params[0] = ltrim($params[0], '\\/');

		$attributes['id']                  = 'lottie' . self::$count;
		$attributes['data-name']           = $attributes['id'];
		$attributes['data-animation-path'] = rtrim(JUri::base(true), '\\/') . '/' . $params[0];

		// Set default value for animation loop
		$attributes['data-anim-loop'] = false;

		if (!empty($params[1]))
		{
			$attributes['data-anim-loop'] = true;
		}

		if (!empty($params[2]))
		{
			if (is_numeric($params[2]))
			{
				$params[2] .= 'px';
			}

			$attributes['style'] = 'height:' . $params[2]. ';';
		}

		return $attributes;
	}
}



