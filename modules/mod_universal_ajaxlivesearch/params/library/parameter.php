<?php

defined('JPATH_BASE') or die();

Offlajnjimport('joomla.html.parameter');
Offlajnjimport('joomla.html.parameter.element');

if(version_compare(JVERSION,'1.6.0','l'))
  defined('OFFLAJNCOMPAT') or define( 'OFFLAJNCOMPAT', JPATH_SITE.DS.'plugins'.DS.'system'.DS.'compat'.DS.'libraries');
else{
  defined('OFFLAJNCOMPAT') or define( 'OFFLAJNCOMPAT', JPATH_SITE.DS.'plugins'.DS.'system'.DS.'offlajnjoomla3compat'.DS.'compat'.DS.'libraries');
  require_once(OFFLAJNCOMPAT.'/coomla/utilities/simplexml.php');
}

if(version_compare(JVERSION,'3.0.0','ge')) {
  class SearchOfflajnJParameter extends OfflajnBaseJParameter{
    public function __construct($data = '', $path = ''){
      parent::__construct($data, $path);
    }
    
    public function render($name = 'params', $group = '_default'){
  		if (!isset($this->_xml[$group])) {
  			return false;
  		}
  	  
  		$params = $this->getParams($name, $group);
  		$html="";
  
  		if ($description = $this->_xml[$group]->attributes('description')) {
  			// Add the params description to the display
  			$desc	= JText::_($description);
  			$html.= '<li><p class="paramrow_desc">'.$desc.'</p></li>';
  		}
        
  		foreach ($params as $param) {
        $spacer = "";
    		if($param[3]=="@spacer"){
    		  $spacer = "spacer";
        }

  		  $html.= '<div class="control-group '.$spacer.'">';
  			if ($param[0]) {
  				$html.= '<div class="control-label">'.$param[0].'</div>';
  				$html.= '<div class="controls"> <fieldset class="jelement">'.$param[1].'<div style="clear:left;"></div></fieldset></div>';
  			} else {
  				$html.= '<li>'.$param[1].'<div style="clear:left;"></div></li>';
  			}
        $html.= '</div>';
  		}
  
  		if (count($params) < 1) {
  			$html.= "<li><p class=\"noparams\">".JText::_('JLIB_HTML_NO_PARAMETERS_FOR_THIS_ITEM')."</p></li>";
  		}
  		
  		return $html;
  	}
    
    public function & getXML(){
      return $this->_xml;
    }

    function loadJSON($data){
          return $this->loadString($data, 'JSON');
      }
   
    function loadIni($data){
          return $this->loadString($data, 'ini');
      }    
    
  }
}else if(version_compare(JVERSION,'1.6.0','ge')) {
  class SearchOfflajnJParameter extends JParameter{
    public function __construct($data = '', $path = ''){
      parent::__construct($data, $path);
    }
    
    public function render($name = 'params', $group = '_default'){
  		if (!isset($this->_xml[$group])) {
  			return false;
  		}
  
  		$params = $this->getParams($name, $group);
  		$html = '<ul class="adminformlist">';
  
  		if ($description = $this->_xml[$group]->attributes('description')) {
  			// Add the params description to the display
  			$desc	= JText::_($description);
  			$html.= '<li><p class="paramrow_desc">'.$desc.'</p></li>';
  		}
  
  		foreach ($params as $param) {
  			if ($param[0]) {
  				$html.= '<li>'.$param[0];
  				$html.= '<fieldset class="jelement">'.$param[1].'<div style="clear:left;"></div></fieldset></li>';
  			} else {
  				$html.= '<li>'.$param[1].'<div style="clear:left;"></div></li>';
  			}
  		}
  
  		if (count($params) < 1) {
  			$html.= "<li><p class=\"noparams\">".JText::_('JLIB_HTML_NO_PARAMETERS_FOR_THIS_ITEM')."</p></li>";
  		}
  
  		return $html;
  	}
    
    public function & getXML(){
      return $this->_xml;
    }

    function loadJSON($data){
          return $this->loadString($data, 'JSON');
      }
   
    function loadIni($data){
          return $this->loadString($data, 'ini');
      }    
    
  }
}else{
  class SearchOfflajnJParameter extends JParameter{
    function &getXML(){
      return $this->_xml;
    }
  }
}