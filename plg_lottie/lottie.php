<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.CCCyoutubefield
 *
 * @copyright   Copyright (C) 2017 Coolcat-Creations.com, Elisa Foltyn.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\HTML\HTMLHelper;

defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');

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
	 * Set counter
	 *
	 * @var     int
	 * @since   1.0.0
	 */
	private static $count = 0;

	public function onContentPrepare($context, &$row, $page = 0)
	{
		// Don't run this plugin when the content is being indexed
		if ($context == 'com_finder.indexer')
		{
			return true;
		}

		if (is_object($row))
		{

			$found = $this->lottieToAnimatation($row->text);
		}

		if (is_string($row))
		{
			$found = $this->lottieToAnimatation($row);
		}


		if ($found)
		{
			HTMLHelper::_('script', 'plg_content_lottie/lottie.min.js', array('version' => 'auto', 'relative' => true));
		}

		return true;
	}

	protected function lottieToAnimatation(&$text)
	{

		if (stripos($text, '{/lottie}') === false)
		{
			return false;
		}

		// Get all matches or return
		if (!preg_match_all(self::PLUGIN_REGEX, $text, $matches))
		{
			return false;
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

		foreach($pluginCalls as $key => $match)
		{
			$counter = self::$count;
			$userParams = explode(',', $callParams[$key]);

			$replacement = $this->replaceLottie($counter, $userParams);

			$pos = strpos($text, $match);
			$end = strlen($match);

			$text = substr_replace($text, $replacement, $pos, $end);
			self::$count++;
		}

		return true;
	}

	protected function replaceLottie($lottieId, $params)
	{
		$attributes = [];
		$attributes[] = 'id="lottie' . $lottieId . '"';
		$attributes[] = 'data-name="lottie' . $lottieId . '"';

		if (empty($params[0]) || !file_exists($params[0]))
		{
			return '';
		}

		$attributes[] = 'data-animation-path="' . JUri::base(true) . '/' . htmlentities($params[0]) . '"';

		if (!empty($params[1]))
		{
			$attributes[] = 'style="height:' . htmlentities($params[2]) . '"';
		}

		if (!empty($params[2]))
		{
			$attributes[] = 'data-anim-loop="' . htmlentities($params[1]) . '"';
		}

		$replaceString = '<div class="lottie" ' . implode($attributes,' ') . '></div>';

		/*	Saving this for possible later more flexible use of lottie
		$replaceString .= "<script type='text/javascript'>\n";
		$replaceString .= "var animation = bodymovin.loadAnimation({ \n";
		$replaceString .= "container: document.getElementById(\"$lottieId\"), \n";
		$replaceString .= "path: \"$path\",\n";
		$replaceString .= "renderer: 'svg', \n";
		$replaceString .= "loop: $loop, \n";
		$replaceString .= "autoplay: $autoplay, \n";
		$replaceString .= "name: \"$lottieId\", \n";
		$replaceString .= "}) \n";
		$replaceString .= "</script>\n";
		*/


		return $replaceString;
	}

}



