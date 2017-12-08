<?php
/**
 * @package     Joomla.Site
 * @subpackage  Template.system
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;


/*
 * xhtml (divs and font headder tags)
 */
function modChrome_joexhtml ($module, &$params, &$attribs)
{   ?>
	
		<div class="moduletable<?php echo htmlspecialchars($params->get('moduleclass_sfx')); ?>">
		<?php if ((bool) $module->showtitle) : ?>
			<h3<?php 
            $headerClass	= $params->get('header_class');
	        $headerClass	= !empty($headerClass) ? ' class="' . htmlspecialchars($headerClass) . '"' : '';
             echo $headerClass;                
             ?>>
            <?php if($params->get('header_link')){ ?>
            <a href="<?php echo $params->get('header_link'); ?>" title="<?php echo $module->title; ?>">
            <?php } ?>
            <span><?php echo $module->title; ?></span>
             <?php if($params->get('header_link')){ ?>
            </a>
            <?php } ?>
            </h3>
		<?php endif; ?>
			<?php echo $module->content; ?>
            <div class="clr"></div>
		</div>
	<?php 
}

