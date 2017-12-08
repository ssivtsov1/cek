<?php
/**
 * @package   mod_nivo_slider
 * copyright Maxim Vendrov
 * @license GPL3
 */

// no direct access
defined('_JEXEC') or die;
	

	$urlModuleTemplate = $urlBase."modules/{$module->module}/tmpl/";
	
	$document = JFactory::getDocument();

	$theme = $params->get("theme","default");
	
	//add css
	$document->addStyleSheet($urlModuleTemplate."css/nivo-slider.css");

	//add js
	$urlNivoJsInclude = $urlModuleTemplate."js/jquery.nivo.slider.pack.js";
	
	$include_jquery = $params->get("include_jquery","true");	
	if($include_jquery == "true")	
		$document->addScript("http://ajax.googleapis.com/ajax/libs/jquery/1.7/jquery.min.js");

	$js_load_type = $params->get("js_load_type","head");		
	if($js_load_type == "head")
		$document->addScript($urlNivoJsInclude);
	
	$theme = $params->get("theme","light");
	
	$sliderID = "nivo_slider_".$module->id;
	
	$width = $params->get("width","618");
	$height = $params->get("height","246");
	$style = "max-width:{$width}px;max-height:{$height}px;";
	
	//set wrapper position:
	$position = $params->get("position","center");
	switch($position){
		case "center":
			$style .= "margin:0px auto;";
		break;
		case "left":
			$style .= "float:left;";
		break;
		case "right":
			$style .= "float:right;";
		break;
	}
	
	//set margin left / right
	if($position == "left" || $position == "right"){
		$marginLeft = $params->get("margin_left","0");
		$marginRight = $params->get("margin_right","0");
		$style .= "margin-left:{$marginLeft}px;margin-right:{$marginRight}px;";
	}
	
	//set margin top/bottom
	$marginTop = $params->get("margin_top","0");
	$marginBottom = $params->get("margin_bottom","0");
	$style .= "margin-top:{$marginTop}px;margin-bottom:{$marginBottom}px;";
	
	$addClearBoth =  $params->get("clear_both","false");
	
	$strEffects = "random";
	
	$arrEffects = $params->get('effect',"");
	if(is_array($arrEffects) && !empty($arrEffects))		
		$strEffects = implode(",",$arrEffects);
	
	//load theme css
	$document->addStyleSheet($urlModuleTemplate."themes/$theme/$theme.css");
	
?>
<!--  Begin "Unite Nivo Slider" -->
		
		<?php if($js_load_type == "body" && !defined("UNITE_NIVO_SLIDER_INCLUDED")): ?>
			<script type="text/javascript" src="<?php echo $urlNivoJsInclude?>"></script>
		<?php endif ?>
		
		<div class="nivo-slider-wrapper theme-<?php echo $theme?>" style="<?php echo $style?>">
			<div id="<?php echo $sliderID ?>" class="nivoSlider">
					<?php 
						//put slides
						foreach($arrSlides as $slide):
							$slideParams = $slide["params"];
							$slideImage = $slideParams->get("image");
							$info = pathinfo($slideImage);
							$altname = $info["filename"];
							
							//$slideThumb = $slideParams->get("thumb_url");
							$link = $slideParams->get("link");
							
							//get boolean activate link
							$activateLink = $slideParams->get("activate_link");
							$activateLink = ($activateLink == "yes")?true:false;
							
							$linkOpenIn = $slideParams->get("link_open_in","new");
							
							$linkTarget = "";
							if($linkOpenIn == "new")
								$linkTarget = " target='_blank'";
							
							$desc = $slideParams->get("description");
							
							//set title (reference to desc)
							$title = "";
							
							if(mod_NivoSliderHelper::isDescExists($desc)){							
								$descID = "nivo_desc_".$slide["id"];
								$title = "title=\"#$descID\"";
							}
								
							?>
							
							<?php if($activateLink == true):?>
								<a href="<?php echo $link?>"<?php echo $linkTarget?>><img src="<?php echo $slideImage?>" alt="<?php echo $altname?>" <?php echo $title?> /></a>
							<?php else:?>
								<img src="<?php echo $slideImage?>" alt="<?php echo $altname?>" <?php echo $title?>/>
							<?php endif;?>
							
							<?php 
						endforeach;
						
						//put descriptions
					?>
			</div>		
					<?php

						foreach($arrSlides as $slide){
							$slideParams = $slide["params"];
							$desc = $slideParams->get("description");
							$desc = trim($desc);
							
							if(mod_NivoSliderHelper::isDescExists($desc) == false)
								continue;
							
							$descID = "nivo_desc_".$slide["id"];
							?>
				            <div id="<?php echo $descID?>" class="nivo-html-caption"><span style='text-align:left;'><?php echo $desc?></span></div>
							<?php 
						}

					?>			
		</div>
		<?php if($addClearBoth == "true"): ?>
		<div style="clear:both"></div>
		<?php endif;?>
		
	
<script type="text/javascript">

<?php if($params->get("no_conflict_mode") == "true"): ?>
	jQuery.noConflict();	
<?php endif; ?>


jQuery(document).ready(function() {
		
	jQuery('#<?php echo $sliderID?>').show().nivoSlider({
			effect: '<?php echo $strEffects?>',
			slices: <?php echo $params->get("slices","15")?>,
			boxCols: <?php echo $params->get("boxCols","8")?>,
			boxRows: <?php echo $params->get("boxRows","4")?>,
			animSpeed: <?php echo $params->get("animSpeed","500")?>,
			pauseTime: <?php echo $params->get("pauseTime","3000")?>,
			startSlide: <?php echo $params->get("startSlide","0")?>,
			directionNav: <?php echo $params->get("directionNav","true")?>,
			controlNav: <?php echo $params->get("controlNav","true")?>,
			controlNavThumbs: false,
			pauseOnHover: <?php echo $params->get("pauseOnHover","true")?>,
			manualAdvance: <?php echo $params->get("manualAdvance","false")?>,
			prevText: '<?php echo $params->get("prevText","Prev")?>',
			nextText: '<?php echo $params->get("nextText","Next")?>',
			randomStart: <?php echo $params->get("randomStart","false")?>,
			beforeChange: <?php echo $params->get("beforeChange","function(){}")?>,
			afterChange: <?php echo $params->get("afterChange","function(){}")?>,
			slideshowEnd: <?php echo $params->get("slideshowEnd","function(){}")?>,
		    lastSlide: <?php echo $params->get("lastSlide","function(){}")?>,
		    afterLoad: <?php echo $params->get("afterLoad","function(){}")?>
		});
	});	//ready

</script>

<!--  End "Unite Nivo Slider" -->

<?php
	if($js_load_type == "body" && !defined('UNITE_NIVO_SLIDER_INCLUDED'))
		define("UNITE_NIVO_SLIDER_INCLUDED",true);
?>
