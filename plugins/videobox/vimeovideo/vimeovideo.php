<?php
/*------------------------------------------------------------------------
# plg_videobox - Videobox
# ------------------------------------------------------------------------
# author	HitkoDev
# copyright	Copyright (C) 2014 HitkoDev. All Rights Reserved.
# @license	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites:	http://hitko.eu/software/videobox
-------------------------------------------------------------------------*/
// no direct access
defined( '_JEXEC' ) or die( 'Restricted Access' );

jimport( 'videobox.video' );

class plgVideoboxVimeoVideo extends Video {
	
	/*
	*	$id - one of the following:
	*		- numeric Vimeo video ID
	*		- link to the video (http://vimeo.com/4700344)
	*/
	function adapterSwitch($id, $title, $offset, $vb){
		if(is_numeric($id)){
			return new self($id, $title, $offset);
		}
		if(strpos($id, 'vimeo')!==false){
			preg_match('/vimeo.com\/([0-9]*?)/isU', $id, $v_urls);
			return new self($v_urls[1], $title, $offset);
		}
		return false;
	}

	function getTitle($forced = false){
		if($forced && $this->title==''){
			return 'http://vimeo.com/' . $this->id;
		} else {
			return $this->title; 
		}
	}
	
	function getThumb(){
		$th = parent::getThumb();
		if($th !== false) return $th;
		$data = unserialize(file_get_contents('http://vimeo.com/api/v2/video/' . $this->id . '.php'));
		$img = $data[0]['thumbnail_large'];
		$im = @getimagesize($img);
		if($im !== false) return array($img, $im[2]);
		return false;
	}
	
	function getPlayerLink($autoplay = false){
		$src = 'https://player.vimeo.com/video/' . $this->id . '?byline=0&portrait=0';
		if($autoplay) $src .= '&autoplay=1';
		if($this->offset != 0) $src .= '#t=' . $this->splitOffset();
		return $src;
	}
	
}