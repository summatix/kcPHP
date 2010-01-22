<?php
/**
 * CodeIgniter Language Helpers
 *
 * @package		kcPHP
 * @subpackage	helpers
 * @author		ExpressionEngine Dev Team
 * @modified	ShiverCube - Removed PHP4 compatibily, and added a few framework tweaks
 * @copyright	Copyright (c) 2008 - 2010, EllisLab, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 * @link		http://codeigniter.com/user_guide/helpers/language_helper.html
 * @since		Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

if ( ! function_exists('lang'))
{
	/**
	 * Lang
	 *
	 * Fetches a language variable. This function supports multiple arguments which
	 * can be passed to the sprintf function
	 *
	 * @access	public
	 * @param	string	the language line
	 * @return	string
	 */
	function lang($line)
	{
		$lang = NULL;
		if ($lang == NULL)
		{
			$CI =& get_instance();
			$lang =& $CI->lang;
		}
		
		return call_user_func_array(array($lang, 'line'), func_get_args());
	}
}

/* End of file language_helper.php */
/* Location: ./system/helpers/language_helper.php */