<?php
/**
 * @version		3.0.x
 * @package		Simple Image Gallery Pro
 * @author		JoomlaWorks - http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2014 JoomlaWorks Ltd. All rights reserved.
 * @license		http://www.joomlaworks.net/license
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

$relName = 'swipebox';
$extraClass = 'swipebox';

$stylesheets = array('src/css/swipebox.css?v=1.2.9');
$stylesheetDeclarations = array();
$scripts = array('src/js/jquery.swipebox.min.js?v=1.2.9');

if(!defined('PE_SWIPEBOX_LOADED')){
	define('PE_SWIPEBOX_LOADED', true);
	$scriptDeclarations = array('
		jQuery.noConflict();
		jQuery(function($) {
			$("a.swipebox").swipebox({hideBarsDelay: 0});
		});
	');
} else {
	$scriptDeclarations = array();
}
