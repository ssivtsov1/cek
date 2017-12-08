<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

class NivoSliderController extends JControllerNivoBaseRev {

    protected $default_view = 'sliders';

    public function display($cachable = false, $urlparams = false) {

        $urlAssets = "components/com_nivoslider/assets/";

        //add style
        $document = JFactory::getDocument();
        $document->addStyleSheet($urlAssets . "style.css");
        $document->addScript("http://ajax.googleapis.com/ajax/libs/jquery/1.7/jquery.min.js");

        //TODO: remove the js func
        //$document->addScript($urlAssets."jsfunc.js");

        $currentView = JRequest::getVar('view', $this->default_view);
        if ($currentView == $this->default_view || $currentView == 'items') {
            NivoSliderHelper::addSubmenu($currentView);
        }
        if (NivoSliderHelper::isJoomla3()) {
            $document->addStyleSheet($urlAssets . "style-joomla3.css");
        }
        parent::display();

        return $this;
    }

}