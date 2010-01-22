<?php
/**
 * kcPHP
 *
 * An open source application development framework for PHP 5.3.0 or newer
 *
 * @package		kcPHP
 * @subpackage	codeigniter
 * @author		ExpressionEngine Dev Team
 * @modified	ShiverCube - Removed PHP4 compatibily, and added a few framework tweaks
 * @copyright	Copyright (c) 2008 - 2010, EllisLab, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 * @link		http://codeigniter.com
 * @since		Version 1.3
 * @filesource
 */


// ------------------------------------------------------------------------

/**
 * CI_BASE
 *
 * @author		ExpressionEngine Dev Team
 * @link		http://codeigniter.com/user_guide/
 */
class CI_Base {

	private static $instance = NULL;
	public $db = NULL;
	
	/**
	 * Fake a single instance class, but allow multiple instances to be created
	 */
	public function __construct()
	{
		if (self::$instance == NULL)
		{
			self::$instance =& $this;
		}
	}

	public static function &get_instance()
	{
		return self::$instance;
	}
}

function &get_instance()
{
	return CI_Base::get_instance();
}

/* End of file Base5.php */
/* Location: ./system/codeigniter/Base5.php */