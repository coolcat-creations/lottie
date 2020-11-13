<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.Lottie
 *
 * @copyright   Copyright (C) 2017 Coolcat-Creations.com, Elisa Foltyn.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Utilities\ArrayHelper;

extract($displayData);

/**
 * Layout attributes for lottie output
 * -----------------
 * @var   integer  $counter     Conter id.
 * @var   array    $attributes  Attributes of lottie container.
 */

HTMLHelper::_('script', 'plg_content_lottie/lottie.min.js', array('version' => 'auto', 'relative' => true)); ?>

<div class="lottie"<?php echo ArrayHelper::toString($attributes); ?>></div>
