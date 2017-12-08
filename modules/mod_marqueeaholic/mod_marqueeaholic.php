<?php
/*-------------------------------------------------------------------------------
# MarqueeAholic - Marquee module for Joomla 3.x v1.0.0
# -------------------------------------------------------------------------------
# author    GraphicAholic
# copyright Copyright (C) 2011 GraphicAholic.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://www.graphicaholic.com
--------------------------------------------------------------------------------*/
// No direct access
defined('_JEXEC') or die('Restricted access');
defined('DS') or define('DS', DIRECTORY_SEPARATOR);
JHtml::_('bootstrap.framework');
// Import the file / foldersystem
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
$LiveSite 	= JURI::base();
$document = JFactory::getDocument();
$modbase = JURI::base(true).'/modules/mod_marqueeaholic/';
$document->addScript ($modbase.'js/jquery.marquee.min.js');
$document->addScript ($modbase.'js/jquery.pause.js');
$document->addScript ($modbase.'js/jquery.easing.min.js');
$document->addStyleSheet($modbase.'css/marquee.css');
$marqueeDuplication	= $params->get('marqueeDuplication');
if($marqueeDuplication == "0") $marqueeDuplication = "false";
if($marqueeDuplication == "1") $marqueeDuplication = "true";
$marqueePause	= $params->get('marqueePause');
if($marqueePause == "0") $marqueePause = "false";
if($marqueePause == "1") $marqueePause = "true";
$marqueeURL	= $params->get('marqueeURL');

$moduleID 	 	= $module->id;
?>
<script type="text/javascript">
			jQuery(function(){
				var $mwo = jQuery('.marquee-with-options');
				jQuery('.marquee').marquee();
				jQuery('.marquee-with-options').marquee({
					speed: <?php echo $params->get('marqueeSpeed') ?>, //speed in milliseconds of the marquee
					gap: <?php echo $params->get('marqueeGap') ?>, //gap in pixels between the tickers
					delayBeforeStart: <?php echo $params->get('marqueeDelay') ?>, //gap in pixels between the tickers
					direction: '<?php echo $params->get('marqueeDirection') ?>', //'left' or 'right'
					duplicated: <?php echo $params->get('marqueeDuplication') ?>, //true or false - should the marquee be duplicated to show an effect of continues flow
					pauseOnHover: <?php echo $params->get('marqueePause') ?> //on hover pause the marquee
				});
			});
</script>
<style>
.marquee-with-options {color: #<?php echo $params->get('marqueeFontColor') ?>; font-family:<?php echo $params->get('marqueeFontFamily') ?>; font-size: <?php echo $params->get('marqueeFontSize') ?>; line-height: <?php echo $params->get('marqueeHeight') ?>; width: <?php echo $params->get('marqueeWidth') ?>; background: #<?php echo $params->get('marqueeBackground') ?>; border: <?php echo $params->get('marqueeBorder') ?> <?php echo $params->get('marqueeBorderStyle') ?> #<?php echo $params->get('marqueeBorderColor') ?>; margin-bottom: <?php echo $params->get('marqueeBottomMargin') ?>; text-decoration: none;}
.marquee-with-options a {color: #<?php echo $params->get('marqueeFontColor') ?>;}
</style>
<?php if ($marqueeURL == "1"): ?>
<div class='marquee-with-options'><a href="<?php echo $params->get('hyperLink') ?>" target="_<?php echo $params->get('linkWindow') ?>" <?php echo $params->get('marqueeText') ?></a></div>
<?php elseif ($marqueeURL == "0"): ?>
<div class='marquee-with-options'><?php echo $params->get('marqueeText') ?></div>
<?php endif ; ?>