<?php
/*----------------------------------------------------------------------
# R3D Floater Module 3.1.0 for Joomla 3.1 
# ----------------------------------------------------------------------
# Copyright (C) 2013 R3D Internet Dienstleistungen. All Rights Reserved.
# Coded by: 	r3d & a3g
# Copyright: 	GNU/GPL
# Website: 		http://www.r3d.de
------------------------------------------------------------------------*/
defined('_JEXEC') or die('Restricted access');

/////////////////////////////// PARAMS //////////////////////////////////

$loadmodule = $params->get( 'loadmodule', false);

$direction = 'left';
$initdir = 'slideDR';

$timeout = $params->get( 'timeout', '10000' );
$oncepersession = $params->get( 'oncepersession', 'false' );

// Box Parameters
$boxwidth = $params->get( 'boxwidth', '300px' );
$boxheight = $params->get( 'boxheight', '300px' );
$boxleft = $params->get( 'boxleft', '-400px' );
$boxtop = $params->get( 'boxtop', '190px' );
$bgcolor = $params->get( 'bgcolor', '#f5f5f5' );
$border = $params->get( 'border', '7px solid #135CAE' );
$opacity = $params->get( 'opacity', '90' );

$talign = $params->get( 'talign', 'right' );

// inside positions
$iwidth = $params->get( 'iwidth', '270px' );
$iheight = $params->get( 'iheight', '250px' );
$ileft = $params->get( 'ileft', '10px' );
$itop = $params->get( 'itop', '0px' );
$ibgcolor = $params->get( 'ibgcolor', 'transparent' );
$iborder = $params->get( 'iborder', 'none' );
$italign = $params->get( 'italign', 'center' );
$ioverflow = $params->get( 'ioverflow', 'auto' );

// startpositions
$startpos = $params->get ( 'startpos', '-400' );
$rightpos = $params->get ( 'rightpos', '100' );
$rightspeed = $params->get ( 'rightspeed', '20' );
$leftpos = $params->get ( 'leftpos', '-400' );
$leftspeed = $params->get ( 'leftspeed', '20' );

$line1 = JText :: _('Закрити');

////////////////////////////   END PARAMS   //////////////////////////////



jimport('joomla.application.module.helper');

if($loadmodule!=false){
        // get a reference to the database
        $db = &JFactory::getDBO();
 
        // get a list of $userCount randomly ordered users 
	$query = 'SELECT id,title,module FROM #__modules WHERE id = '.$loadmodule.' LIMIT 1';
  $db->setQuery($query);
	$moduletoload = $db->loadObject();
	$modname = JModuleHelper::getModule(substr($moduletoload->module,4),$moduletoload->title);
}else{
	$modname = false;
}



// Init

// Set opacity
if($opacity>99){
	$opacityie = 100;
	$opacity = 1;
}else{
	$opacityie = $opacity;
	$opacity = '0.'.$opacity;
}
// Get session
$session = JFactory::getSession();

// Time for the once-per-day cookie.
$expire	=	time()+mktime(24,0,0);

if($oncepersession=='oncepersession'){
	if(!$session->get('mod_floaterpersession')){
		$check = true;
	}else{
		$check = false;
	}
}elseif($oncepersession=='onceperday'){
	if(!$_COOKIE['mod_floaterperday']){
		$check = true;
	}else{
		$check = false;
	}
}else{
		$check = false;
}
if($check or $oncepersession == 'false'){
?>
<style type="text/css"><!--
#floaterDiv
{
	position: absolute;
	width:<?php echo $boxwidth; ?>;
	height:<?php echo $boxheight; ?>;
	left:<?php echo $boxleft; ?>;
	top:<?php echo $boxtop; ?>;
	background-color:<?php echo $bgcolor; ?>;
	border:<?php echo $border; ?>;
	text-align:<?php echo $talign; ?>;
	z-index: 9999;
}
.translucent {-moz-opacity:<?php echo $opacity; ?>; opacity:<?php echo $opacity; ?>; filter:alpha(opacity=<?php echo $opacityie; ?>);}

#floaterDiv div.box
{
	position:absolute;
	left:5px;
	top:5px;
	text-align: right;
}
#insideDiv
{
	position: relative;
	width:<?php echo $iwidth; ?>;
	height:<?php echo $iheight; ?>;
	left:<?php echo $ileft; ?>;
	top:<?php echo $itop; ?>;
	background-color:<?php echo $ibgcolor; ?>;
	border:<?php echo $iborder; ?>;
	z-index: 10000;
	text-align:<?php echo $italign; ?>;
	overflow: <?php echo $ioverflow; ?>;
}
-->
</style>

<div style="left: <?php echo $startpos;?>px;" id="floaterDiv" class="translucent">
	<div class="box"><a onfocus="this.blur()" href="javascript:goaway()" title="Exit"> <strong> <?php echo $line1;?> &nbsp; X</strong></a><br>
	<div>
		<br>
		<div  id="insideDiv"><?php 
		if($modname){
			echo JModuleHelper::renderModule($modname,array('style'=> 'raw'));
		}else{
			echo 'No module loaded.';
		}
		
		 ?></div>
	</div>
</div>
	<script type="text/javascript">
<?php
echo "var direction = \"$direction\"; \n";
echo "var initdir = \"$initdir\"; \n";
echo "var startpos = $startpos; \n";
echo "var timeout = $timeout; \n";
echo "var rightpos = $rightpos; \n";
echo "var rightspeed = $rightspeed; \n";
echo "var leftpos = $leftpos; \n";
echo "var leftspeed = $leftspeed; \n";
?>

function moveTo(obj, x, y) {
        if (document.getElementById) {
        document.getElementById('floaterDiv').style.left = x;
        document.getElementById('floaterDiv').style.top = y;
        }
}
if (direction == 'top'){
var udlr = "top";
} else {
	var udlr = "left";
	}
function init(){
        if(document.getElementById){
        obj = document.getElementById("floaterDiv");
        obj.style[udlr] = parseInt(startpos) + "px";
        }
}
function slideDR(){
        if(document.getElementById){
                if(parseInt(obj.style[udlr]) < rightpos){
                        obj.style[udlr] = parseInt(obj.style[udlr]) + 20 + "px";
                        setTimeout("slideDR()",rightspeed);
                }
        }
}
function slideUL(){
        if(document.getElementById){
                if(parseInt(obj.style[udlr]) > leftpos){
                        obj.style[udlr] = parseInt(obj.style[udlr]) - 30 + "px";
                        setTimeout("slideUL()",leftspeed);
                }
        }
}
function ShowHide(id, visibility) {
    divs = document.getElementsByTagName("div");
    divs[id].style.visibility = visibility;
}
function goaway() 
{
   if (initdir == 'slideDR' ){
   slideUL();
   }
 else{
   slideDR();
   }
}
function selection() 
{
   if (initdir == 'slideDR' ){
   slideDR();
   }
 else{
   slideUL();
   }
}
function start() 
{
   init();
   selection();
}
window.onload = start;

if (initdir == 'slideDR' ){
   setTimeout('slideUL();',timeout);
   }
 else{
   setTimeout('slideDR();',timeout);
   }
</script>
<?php 
	}
	if($oncepersession == 'oncepersession'){
		// Session
		$session->set('mod_floaterpersession', 'true');
	}elseif($oncepersession == 'onceperday' && !$_COOKIE['mod_floaterperday']){
		// Cookie
		setcookie("mod_floaterperday", "true", $expire);
	}
?>
