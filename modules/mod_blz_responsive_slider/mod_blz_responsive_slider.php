<?php

/**
 * @package Blazing Responsive Slider for Joomla! 3.x
 * @version 1.2: mod_blz_responsive_slider.php March, 2015
 * @author Dario Pintaric
 * @copyright (C) 2015 - dblaze.eu
 * @link http://www.pixedelic.com/plugins/camera/
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 *
 */
defined('_JEXEC') or die;
// Get document
$doc = JFactory::getDocument();
// Get language
$lang = JFactory::getLanguage();
$langTag = $lang->getTag();
$lang->load('mod_blz_responsive_slider', null, null, true); // Ensure that the right language is loaded
// Get slides
$items = array();
$captionWidth = $params->get('caption_width', 40);
if((strpos($captionWidth, '%') && strpos($captionWidth, 'px') === false) || (strpos($captionWidth, '%') === false && strpos($captionWidth, 'px') === false))
        $captionWidth = str_replace (array('%', ' '), '', $captionWidth) . '%';
else
        $captionWidth = str_replace (' ', '', $captionWidth);

for ($i = 1; $i <= 10; $i++) {
    $img = $params->get('slide' . $i . '_img', false);
    if ($img) {
        $item = array(
            'image' => JURI::root() . $img,
            'link' => $params->get('slide' . $i . '_link', false),
            'target' => $params->get('slide' . $i . '_target', 0),
            'more' => $params->get('slide' . $i . '_more', false),
            'content' => $params->get('slide' . $i . '_text', false),
            'captionEffect' => $params->get('caption_fx', 'fadeFromBottom')
        );
		if(strpos($item['link'], 'index.php') === 0)
			$item['link'] = JRoute::_($item['link'], false);

        $items[] = JArrayHelper::toObject($item);
    }
}

if (count($items)) {
    $doc->addStylesheet(JURI::root() . 'modules/mod_blz_responsive_slider/css/camera.css', 'text/css', 'all', array('id' => 'camera-css'));
    $doc->addScript(JURI::root() . 'modules/mod_blz_responsive_slider/js/jquery.mobile.customized.min.js');
    $doc->addScript(JURI::root() . 'modules/mod_blz_responsive_slider/js/jquery.easing.1.3.js');
    $doc->addScript(JURI::root() . 'modules/mod_blz_responsive_slider/js/camera.min.js');

    $script = '
jQuery(function () {
';
    if ($params->get('fix_width', false)) {
        $script .= '
    function resizeSlider(){
        slideHandler = jQuery(".slideshow-handler");
        slideViewport = jQuery(window).width();
        slideHandler.width(slideViewport);
        slideHandler.css("left", 0);
        slideLeftPos = slideHandler.offset().left;
        slideHandler.css({
          position: "relative",
          left: (Math.abs(slideLeftPos) * -1)
        });
    };
    resizeSlider();
    jQuery(window).on("resize", function(event){
        resizeSlider();
    });
';
    }
    $script .= '
	jQuery("#ph-camera-slideshow-' . $module->id . '").camera({
		alignment: "topCenter",
		autoAdvance: ' . ($params->get('autoAdvance') ? 'true' : 'false') . ',
		mobileAutoAdvance: ' . ($params->get('mobileAutoAdvance') ? 'true' : 'false') . ',
		slideOn: "' . ($params->get('slideOn', 'random')) . '",
		thumbnails: ' . ($params->get('thumbnails') ? 'true' : 'false') . ',
		time: ' . ($params->get('time', 7000)) . ',
		transPeriod: ' . ($params->get('transPeriod', 1500)) . ',
		cols: ' . ($params->get('cols', 10)) . ',
		rows: ' . ($params->get('rows', 10)) . ',
		slicedCols: ' . ($params->get('slicedCols', 10)) . ',
		slicedRows: ' . ($params->get('slicedRows', 10)) . ',
		fx: "' . ($params->get('fx', false) ? implode(',', $params->get('fx')) : 'simpleFade') . '",
		gridDifference: ' . ($params->get('gridDifference', 250)) . ',
		height: "' . ($params->get('height', '30')) . '%",
		minHeight: "' . ($params->get('minHeight', 200)) . 'px",
		imagePath: "/modules/mod_blz_responsive_slider/images/",
		hover: ' . ($params->get('hover') ? 'true' : 'false') . ',
		loader: "' . ($params->get('loader', 'pie')) . '",
		barDirection: "' . ($params->get('barDirection', "leftToRight")) . '",
		barPosition: "' . ($params->get('barPosition', 'bottom')) . '",
		pieDiameter: ' . ($params->get('pieDiameter', 40)) . ',
		piePosition: "' . ($params->get('piePosition', 'rightTop')) . '",
		loaderColor: "' . ($params->get('loaderColor', '#ffffff')) . '",
		loaderBgColor: "' . ($params->get('loaderBgColor', '#00858C')) . '",
		loaderOpacity: ' . ($params->get('loaderOpacity', '0.7')) . ',
		loaderPadding: ' . ($params->get('loaderPadding', 0)) . ',
		loaderStroke: ' . ($params->get('loaderStroke', 5)) . ',
		navigation: ' . ($params->get('navigation') ? 'true' : 'false') . ',
		playPause: ' . ($params->get('playPause') ? 'true' : 'false') . ',
		navigationHover: ' . ($params->get('navigationHover') ? 'true' : 'false') . ',
		mobileNavHover: ' . ($params->get('mobileNavHover') ? 'true' : 'false') . ',
		opacityOnGrid: ' . ($params->get('opacityOnGrid') ? 'true' : 'false') . ',
		pagination: ' . ($params->get('pagination') ? 'true' : 'false') . ',
		pauseOnClick: ' . ($params->get('pauseOnClick') ? 'true' : 'false') . ',
		portrait: ' . ($params->get('portrait') ? 'true' : 'false') . '
	});
});
';
    $doc->addScriptDeclaration($script);

    $style = '.camera_caption > div { padding-bottom: ' . $params->get('caption_y_pos', 0) . 'px;}' . "\n";
    $style .= '.cameraContent .camera_caption .container { width: ' . $captionWidth . ';}' . "\n";
    $style .= '.camera_pie { width: ' . $params->get('pieDiameter', 40) . 'px; height: ' . $params->get('pieDiameter', 40) . 'px;}' . "\n";
    $style .= '.slideshow-handler { min-height: ' . $params->get('minHeight', 200) . 'px;}' . "\n";

    // Caption Background color and opacity
    if ($params->get('caption_bgcolor')) {
        $hexColor = $params->get('caption_bgcolor');
        $opacity = $params->get('caption_bg_opacity', 1);
        $rgbaColor = hex2rgb($hexColor) . ',' . number_format($opacity, 2, '.', '');
        $style .= '.camera_caption .container {background-color: ' . $hexColor . '; background-color: rgba(' . $rgbaColor . ');}' . "\n";
    }

    // Caption Text color
    if ($params->get('caption_color')) {
        $style .= '.camera_caption .container {color: ' . $params->get('caption_color', '#666666') . ';}' . "\n";
    }

    // Caption Padding
    if ($params->get('caption_padding')) {
        $style .= '.camera_caption .container {padding: ' . $params->get('caption_padding', 15) . 'px;}' . "\n";
    }

    // Button colors
    $style .= '.camera_caption .button:hover, .camera_prev > span:hover, .camera_next > span:hover, .camera_commands > .camera_play:hover, .camera_commands > .camera_stop:hover, .product-sl-handler ol li.prev:hover, .product-sl-handler ol li.next:hover,';
    $style .= '.camera_wrap .camera_pag .camera_pag_ul li.cameracurrent > span, .camera_wrap .camera_pag .camera_pag_ul li:hover > span, .slideshow-handler .slideLink a:hover {background-color: ' . $params->get('button_hovers', '#00858C') . ';}' . "\n";
    $style .= '.camera_wrap .camera_pag .camera_pag_ul li, .camera_prev > span, .camera_next > span, .camera_commands > .camera_play, .camera_commands > .camera_stop, .camera_prevThumbs div, .camera_nextThumbs div {border-color: ' . $params->get('button_borders', '#FFFFFF') . ';}' . "\n";

    $doc->addStyleDeclaration($style);

    $tmplName = pathinfo($params->get('template', 'default'), PATHINFO_FILENAME);
    $tmplPath = JModuleHelper::getLayoutPath('mod_blz_responsive_slider', $tmplName);

    if (file_exists($tmplPath))
        require($tmplPath);
}

function hex2rgb($hex) {
    $hex = str_replace("#", "", $hex);

    if (strlen($hex) == 3) {
        $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
        $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
        $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
    } else {
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
    }
    $rgb = array($r, $g, $b);
    return implode(",", $rgb);
}

?>