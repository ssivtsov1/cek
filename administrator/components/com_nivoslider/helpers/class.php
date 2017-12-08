<?php

/**
 * @package Unite Nivo Slider Pro for Joomla 1.7-2.5
 * @version 1.0.0
 * @author UniteJoomla.com
 * @copyright (C) 2012- Unite Joomla
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
// no direct access
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.controller');
jimport('joomla.application.component.view');

$currentDir = dirname(__FILE__) . "/../";

$isJoomla3 = NivoSliderHelper::isJoomla3();

global $g_nivoSliderVersion;
$g_nivoSliderVersion = "3.0.1";

if ($isJoomla3) {

//    require_once $currentDir . 'masterfield_joomla3.class.php';
    require_once $currentDir . "models/model_joomla3.php";

    class JViewNivoBaseRev extends JViewLegacy {
        
    }

    ;

    abstract class JControllerNivoBaseRev extends JControllerLegacy {
        
    }

    ;
} else {  //joomla 2.5
    require_once $currentDir . "models/model_joomla2.php";

    class JViewNivoBaseRev extends JView {
        
    }

    ;

    abstract class JControllerNivoBaseRev extends JController {        
    }

    ;
}
//init the globals
?>