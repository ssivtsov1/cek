<?php
/**
 * @package         Regular Labs Library
 * @version         17.7.17782
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://www.regularlabs.com
 * @copyright       Copyright © 2017 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace RegularLabs\Library;

defined('_JEXEC') or die;

use JFactory;

/**
 * Class Database
 * @package RegularLabs\Library
 */
class Database
{
	/**
	 * Check if a table exists in the database
	 *
	 * @param string $table
	 *
	 * @return bool
	 */
	public static function tableExists($table)
	{
		$db = JFactory::getDbo();

		$query = 'SHOW TABLES LIKE ' . $db->quote($db->getPrefix() . $table);
		$db->setQuery($query);
		$result = $db->loadResult();

		return ! empty($result);
	}

}
