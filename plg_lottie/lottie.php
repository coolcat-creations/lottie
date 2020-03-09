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

		public function onContentPrepare($context, &$row, $page = 0)
		{
			// Don't run this plugin when the content is being indexed
			if ($context == 'com_finder.indexer') {
				return true;
			}

			if (is_object($row)) {

				$this->lottieToAnimatation($row->text, $row->id);

				HTMLHelper::_('script', 'plg_content_lottie/lottie.min.js', array('version' => 'auto', 'relative' => true));


				return true;
			}

			$this->lottieToAnimatation($row);

			return true;
		}

		protected function lottieToAnimatation(&$text, &$artid)
		{

			if (stripos($text, '{/lottie}') === false)
			{
				return true;
			}

			$tagname = "lottie";

			$pattern = "#{\s*?$tagname\b[^}]*}(.*?),(.*?),(.*?),(.*?){/$tagname\b[^}]*}#s";

			$text = preg_replace($pattern, $this->replaceLottie('$1', '$2', '$3', '$4'), $text);

			return true;
		}

		protected function replaceLottie($lottieId, $path, $loop, $size)
		{

			$replaceString = '<div class="lottie" id="' . htmlentities($lottieId) . '" style="height:' . htmlentities($size) . ';" data-animation-path="' . htmlentities($path) . '" data-anim-loop="' . htmlentities($loop) . '" data-name="' . htmlentities($lottieId) . '"></div>';

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



