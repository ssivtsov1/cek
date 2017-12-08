<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
if (($this->error->getCode()) == '404'){
header("HTTP/1.0 404 Not Found");
echo file_get_contents("http://".$_SERVER['SERVER_NAME'].'/404');
}
?>