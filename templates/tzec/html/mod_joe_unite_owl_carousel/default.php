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


	//add js

	$include_jquery = $params->get("include_jquery","true");	
	if($include_jquery == "true")	
		$document->addScript("http://ajax.googleapis.com/ajax/libs/jquery/1.7/jquery.min.js");

	if($params->get("include_owl",1)) {
		$urlNivoJsInclude = $urlModuleTemplate . "js/owl.carousel.min.js";
		$document->addScript($urlNivoJsInclude);
	}


		$sliderID = "owl_slider_".$module->id;
		$script = '';
		if($params->get("no_conflict_mode") == "true"):
			$script.='jQuery.noConflict()';
		endif;
		$script .='jQuery(document).ready(function(){
			var '.$sliderID.' = jQuery("#'.$sliderID.'");
			'.$sliderID.'.show()
			.on("initialized.owl.carousel", function(event) {
			    jQuery("#'.$sliderID.'_navigation").hide();
			    '.$sliderID.'.find(".owl-dot").each(function(ind,val){		
			    	jQuery(this).html( jQuery("#'.$sliderID.'_navigation .caption").eq(ind).html() );
			    })
			})
			.owlCarousel({ items:'.$params->get("items","5")
			.', responsive:{'
			.' 0:{ items:'.$params->get("itemsMobile","1")
			.'}, 768:{items:'.$params->get("itemsTablet","3")
			.'}, 980:{items:'.$params->get("itemsDesktopSmall","4")
			.'}, 1200:{items:'.$params->get("itemsDesktop","5")
			.'}'
			.'}, startPosition:'.$params->get("startSlide","0")
			.', nav:'.$params->get("directionNav","true")
			.', dots:'.$params->get("controlNav","true")
			.', stopOnHover:'.$params->get("pauseOnHover","true")
			.($params->get("pauseTime",'-1')!='-1'?', autoplay:true, autoplayTimeout: '.$params->get("pauseTime",'-1'):'')
			.($params->get("animSpeed",false)?', autoplaySpeed:'. $params->get("animSpeed",false):'')
			.', navText:["'.$params->get("prevText","Prev").'","'.$params->get("nextText","Next").'"]'
			. ($params->get("loop",false)?', loop:true':'')
			.'});});';
		$document->addScriptDeclaration($script);



	//add css
	if($params->get("include_css",1))
	$document->addStyleSheet($urlModuleTemplate."css/owl.carousel.css");


	
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

?>
<?php
	if($params->get('pretext',"")) echo '<p class="pretext">'.$params->get('pretext',"").'</p>';
 ?>

		<div class="owlcarousel" id="<?php echo $sliderID ?>" style="<?php echo $style?>">			
					<?php foreach($arrSlides as $slide){ ?>
                    <div class="slide">    
						<?php 	$slideParams = $slide["params"];
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
							
							if(mod_OwlCarouselHelper::isDescExists($desc)){
								$descID = "nivo_desc_".$slide["id"];
								$title = "title=\"#$descID\"";
							}
								
							?>
							
							<?php if($activateLink == true):?>
								<a href="<?php echo $link?>"<?php echo $linkTarget?>><img src="<?php echo $slideImage?>" alt="<?php echo $altname?>" <?php echo $title?> />
							<?php else:?>
								<img src="<?php echo $slideImage?>" alt="<?php echo $altname?>" <?php echo $title?>/>
							<?php endif;

							// $desc = $slideParams->get("description");
							// $desc = trim($desc);
							
							?>
				            
                           <?php if($activateLink == true){?>
								</a>
							<?php }?>
						</div>			
							<?php };?>
			
					
		</div>
		<div id="<?php echo $sliderID ?>_navigation">
			<?php foreach($arrSlides as $slide){ ?>
				<div class="caption"><?php 
							$slideParams = $slide["params"];
							$slideImage = $slideParams->get("image");
							$desc = $slideParams->get("description");
							$desc = trim($desc);
				echo $desc?></div>
			<?php };?>
		</div>
		
		<?php if($addClearBoth == "true"): ?>
		<div class="clr"></div>
		<?php endif;?>
