<?php 
/*------------------------------------------------------------------------
# mod_accordion_menu - Accordion Menu - Offlajn.com 
# ------------------------------------------------------------------------
# author    Roland Soos 
# copyright Copyright (C) 2012 Offlajn.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://www.offlajn.com
-------------------------------------------------------------------------*/
?>
<?php
// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

include_once(dirname(__FILE__).DS.'library'.DS.'fakeElementBase.php');

class JElementOfflajnupdatechecker extends JOfflajnFakeElementBase
{
  
	var	$_name = 'Offlajnupdatechecker';
//	var $_moduleName = 'mod_universal_ajaxlivesearch';
	var $offlajnDashboard = '';

	function loadDashboard(){
    $logoUrl = JURI::base(true).'/../modules/'.$this->_moduleName.'/params/images/dashboard-offlajn.png';
    $supportTicketUrl = JURI::base(true).'/../modules/'.$this->_moduleName.'/params/images/support-ticket-button.png';
    $supportUsUrl = JURI::base(true).'/../modules/'.$this->_moduleName.'/params/images/support-us-button.png';
    ob_start();
    include('offlajndashboard.tmpl.php');
    $this->offlajnDashboard = ob_get_contents();
    ob_end_clean();	
  }
  
	function universalfetchElement($name, $value, &$node){

    $document =& JFactory::getDocument();
    if(version_compare(JVERSION,'3.0.0','lt'))
      $document->addStyleSheet(JURI::base().'../modules/'.$this->_moduleName.'/params/css/offlajn.css');
    else
      $document->addStyleSheet(JURI::base().'../modules/'.$this->_moduleName.'/params/css/offlajnj30.css');
    
    $xml = dirname(__FILE__).DS.'../'.$this->_moduleName.'.xml';
    
  	if(!file_exists($xml)){
      $xml = dirname(__FILE__).DS.'../install.xml';
      if(!file_exists($xml)){
        return;
      }
    } 
    
    if(version_compare(JVERSION,'3.0.0','lt')){
      $xml = simplexml_load_file($xml);
      $hash = (string)$xml->hash;
      $this->label = (string)$xml->name;
      if($hash == '') return;
  	  return '<iframe src="http://offlajn.com/index2.php?option=com_offlajn_update&hash='.base64_url_encode($hash).'&v='.$xml->version.'&u='.JURI::root().'" frameborder="no" style="border: 0;" width="100%" height="30"></iframe>';
    }
    
    if(version_compare(JVERSION,'3.0','ge')){
      $xmlo = JFactory::getXML($xml);
      $xmld = $xmlo;
    }else{
      jimport( 'joomla.utilities.simplexml' );
      $xmlo = JFactory::getXMLParser('Simple');
      $xmlo->loadFile($xml);
      $xmld = $xmlo->document;
    }
    
    if(isset($xmld->hash) && $xmld->hash[0]){
      if(version_compare(JVERSION,'3.0','ge')){
        $hash = (string)$xmld->hash[0];
        $this->label = (string)$xmld->name[0];
      }else
        $hash = (string)$xmld->hash[0]->data();
    }

    if (!isset($hash)) {
      $this->generalInfo = '<iframe src="http://offlajn.com/index2.php?option=com_offlajn_update_info&amp;v='.(version_compare(JVERSION,'3.0','ge') ?  (string)$xmld->version : $xmld->version[0]->data()).'" frameborder="no" style="border: 0;" width="100%" height="200px" ></iframe>';
      $this->relatedNews = '<iframe id="related-news-iframe" src="http://offlajn.com/index2.php?option=com_offlajn_related_news" frameborder="no" style="border: 0;" width="100%" ></iframe>';    
    } else {
      $this->generalInfo = '<iframe src="http://offlajn.com/index2.php?option=com_offlajn_update_info&amp;hash='.base64_url_encode($hash).'&amp;v='.(version_compare(JVERSION,'3.0','ge') ? (string)$xmld->version : $xmld->version[0]->data()).'&amp;u='.JURI::root().'" frameborder="no" style="border: 0;" width="100%" height="200px" ></iframe>';
      $this->relatedNews = '<iframe id="related-news-iframe" src="http://offlajn.com/index2.php?option=com_offlajn_related_news&amp;tag=Universal AJAX Live Search" frameborder="no" style="border: 0;" width="100%" ></iframe>';    
    }
    $this->loadDashboard();
    return  $this->offlajnDashboard;    
	}
}


function base64_url_encode($input) {
 return strtr(base64_encode($input), '+/=', '-_,');
}

if(version_compare(JVERSION,'1.6.0','ge')) {
        class JFormFieldOfflajnupdatechecker extends JElementOfflajnupdatechecker {}
}

if (!function_exists('json_encode')){
  function json_encode($a=false){
    if (is_null($a)) return 'null';
    if ($a === false) return 'false';
    if ($a === true) return 'true';
    if (is_scalar($a)){
      if (is_float($a)){
        // Always use "." for floats.
        return floatval(str_replace(",", ".", strval($a)));
      }

      if (is_string($a))
      {
        static $jsonReplaces = array(array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'), array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'));
        return '"' . str_replace($jsonReplaces[0], $jsonReplaces[1], $a) . '"';
      }
      else
        return $a;
    }
    $isList = true;
    for ($i = 0, reset($a); $i < count($a); $i++, next($a)){
      if (key($a) !== $i){
        $isList = false;
        break;
      }
    }
    $result = array();
    if ($isList){
      foreach ($a as $v) $result[] = json_encode($v);
      return '[' . join(',', $result) . ']';
    }else{
      foreach ($a as $k => $v) $result[] = json_encode($k).':'.json_encode($v);
      return '{' . join(',', $result) . '}';
    }
  }
}