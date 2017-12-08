<?php
/**
 * @package         Sourcerer
 * @version         7.1.8
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://www.regularlabs.com
 * @copyright       Copyright © 2017 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

if ( ! is_file(__DIR__ . '/vendor/autoload.php'))
{
	return;
}

require_once __DIR__ . '/vendor/autoload.php';

use RegularLabs\Plugin\System\Sourcerer\Plugin;

/**
 * Plugin that replaces Sourcerer code with its HTML / CSS / JavaScript / PHP equivalent
 */
class PlgSystemSourcerer extends Plugin
{
	public $_alias       = 'sourcerer';
	public $_title       = 'SOURCERER';
	public $_lang_prefix = 'SRC';

	public $_can_disable_by_url = false;
	public $_page_types         = ['html', 'feed', 'pdf', 'xml', 'ajax', 'json', 'raw'];

	/*
	 * Below are the events that this plugin uses
	 * All handling is passed along to the parent run method
	 */
	public function onContentPrepare()
	{
		$this->run();
	}

	public function onAfterDispatch()
	{
		$this->run();
	}

	public function onAfterRender()
	{
		$this->run();
	}
}
