<?php
/**
 * CodeIgniter Debug Helpers
 *
 * @package		kcPHP
 * @subpackage	helpers
 * @author		ShiverCube
 * @copyright	Copyright (c) 2008 - 2010, EllisLab, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 * @since		Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

namespace debug;

if ( ! function_exists('debug\backtrace'))
{
	/**
	 * Returns the file and line number of the call made the specified number of levels ago
	 *
	 * @param int $level (default 1) The number of steps to backtrace
	 * @param bool $clean (default FALSE) Set to TRUE to clean the file property
	 * @return object The object containing properties for the file and line number ("file" and "line" respectively)
	 */
	function backtrace($level = 1, $clean = FALSE)
	{
		$back = debug_backtrace();
		$obj = new \stdClass;
		$obj->file = ( ! isset($back[$level]['file'])) ? '' : $back[$level]['file'];
		$obj->line = ( ! isset($back[$level]['line'])) ? '' : $back[$level]['line'];
		
		if ($clean)
		{
			$CI =& get_instance();
			$CI->load->helper('file');
			$obj->file = \file\clean($obj->file);
		}
		
		return $obj;
	}
}

/* End of file debug_helper.php */
/* Location: ./system/helpers/debug_helper.php */