<?php
/**
 * @package Blazing Responsive Slider for Joomla! 3.x
 * @version 1.0: mod_blz_responsive_slider.php January, 2015
 * @author Dario Pintariæ
 * @copyright (C) 2015 - dblaze.eu
 * @link http://www.pixedelic.com/plugins/camera/
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 *
 */
defined('_JEXEC') or die('(@) | (@)');
?>
<div class="slideshow-handler">
	<div id="ph-camera-slideshow-<?php echo $module->id ?>" class="camera_wrap camera_emboss">
		<?php foreach ($items as $key => $item): ?>
			<?php if ($params->get('slideAsLink', 1)): ?>
				<div data-thumb="<?php echo $item->image ?>" data-src="<?php echo $item->image ?>"<?php echo $item->link ? ' data-link="' . $item->link . '"' : '' ?><?php echo $item->link ? ($item->target ? ' data-target="_blank"' : ' data-target="_parent"') : '' ?>>
				<?php else: ?>
				<div data-thumb="<?php echo $item->image ?>" data-src="<?php echo $item->image ?>">
				<?php endif; ?>
				<?php if (trim(strip_tags($item->content))): ?>
					<div class="camera_caption <?php echo $item->captionEffect; ?> camera_effected">
						<div class="container"><?php echo $item->content; ?></div>
						<?php if ($item->link && $item->more): ?>
						<div class="slideLink"><a href="<?php echo $item->link ?>" target="<?php echo $item->target ? '_blank' : '_parent' ?>"><?php echo JText::_('MOD_BLZ_RS_READ_MORE'); ?></a></div>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>
		<?php endforeach; ?> 
	</div>
</div>