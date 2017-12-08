<?php 

defined('_JEXEC') or die('Restricted access'); ?>

<?php 

	$numSliders = count($this->arrSliders);

	if($numSliders == 0){	//error output
		?>
			<h2>Please add some slider before operating slides</h2>
		<?php 
	}else
		echo $this->loadTemplate("slide");	
?>

<!-- banner -->
	<div style="text-align:center;margin-top:30px;margin-bottom:30px;">
		<a target="_blank" href="http://www.unitecms.net/joomla-extensions/unite-nivo-slider-pro/nivo-slider-pro-demo">
			<img src="<?php echo JURI::root()?>/administrator/components/com_nivoslider/assets/component_nivopro_banner.jpg">
		</a>
	</div>


