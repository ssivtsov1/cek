<?php
/**
 * @package Unite Nivo Slider for Joomla 1.7-2.5
 * @version 1.0.0
 * @author UniteJoomla.com
 * @copyright (C) 2012- Unite Joomla
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
//error_reporting(E_ALL); // debug
require_once JPATH_COMPONENT.'/helpers/nivoslider.php';
require_once JPATH_COMPONENT.'/helpers/class.php';
// Include dependancies
jimport('joomla.application.component.controller');
if (NivoSliderHelper::isJoomla3())
    $controller = JControllerLegacy::getInstance('nivoslider');
else
    $controller = JController::getInstance('nivoslider');


// Perform the Request task
$task = JFactory::getApplication()->input->get('task');

$controller->execute($task);
$controller->redirect();

?>