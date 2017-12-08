<?php

defined('_JEXEC') or die;
$mc = $module->content;
if ($params->get('timer')!='') {
    $time = explode('-', $params->get('timer'));
    $msec = strtotime($params->get('timer'))* 1000;    
    $tc ='<div class="timer '.$moduleclass_sfx.'">
    <div class="dash day"><span>00</span>Часов</div>
    <div class="blink"><span></span></div>    
    <div class="dash hour"><span>00</span>Минут</div>
    <div class="blink"><span></span></div>
    <div class="dash min"><span>00</span>Секунд</div>
</div>';   
    $mc = str_replace('[timer]',$tc, $mc);
};
if($params->get('timer')!='') {
?>
<script>
    joe_timer(<?php echo $msec; ?>, <?php echo "'.timer.".trim(str_replace(' ','.',$moduleclass_sfx))."'";?>);
</script>
<?php }?>

<div class="custom" <?php if ($params->get('backgroundimage')) : ?> style="background-image:url(<?php echo $params->get('backgroundimage');?>)"<?php endif;?> >
    	   <?php echo $mc;?>
</div>    
