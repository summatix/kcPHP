<?php
/**
 * CodeIgniter Path Helpers
 *
 * @package		kcPHP
 * @subpackage	helpers
 * @author		ExpressionEngine Dev Team
 * @modified	ShiverCube - Removed PHP4 compatibily, and added a few framework tweaks
 * @copyright	Copyright (c) 2008 - 2010, EllisLab, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 * @link		http://codeigniter.com/user_guide/helpers/xml_helper.html
 * @since		Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Set Realpath
 *
 * @access	public
 * @param	string
 * @param	bool	checks to see if the path exists
 * @return	string
 */	
if ( ! function_exists('set_realpath'))
{
	function set_realpath($path, $check_existance = FALSE)
	{
		// Security check to make sure the path is NOT a URL.  No remote file inclusion!
		if (preg_match('%^(http:\/\/|https:\/\/|www\.|ftp|[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})%i', $path))
		{
			show_error('The path you submitted must be a local server path, not a URL');
		}
	
		// Resolve the path
		if (function_exists('realpath') AND @realpath($path) !== FALSE)
		{
			$path = realpath($path).'/';
		}
	
		// Add a trailing slash
		$path = preg_replace('%([^/])/*$%', '%\/%', $path);
	
		// Make sure the path exists
		if ($check_existance == TRUE)
		{
			if ( ! is_dir($path))
			{
				show_error('Not a valid path: '.$path);
			}
		}
	
		return $path;
	}
}

/* End of file path_helper.php */
/* Location: ./system/helpers/path_helper.php */