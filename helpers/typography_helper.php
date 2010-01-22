<?php
/**
 * CodeIgniter Typography Helpers
 *
 * @package		kcPHP
 * @subpackage	helpers
 * @author		ExpressionEngine Dev Team
 * @modified	ShiverCube - Removed PHP4 compatibily, and added a few framework tweaks
 * @copyright	Copyright (c) 2008 - 2010, EllisLab, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 * @link		http://codeigniter.com/user_guide/helpers/typography_helper.html
 * @since		Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Convert newlines to HTML line breaks except within PRE tags
 *
 * @access	public
 * @param	string
 * @return	string
 */	
if ( ! function_exists('nl2br_except_pre'))
{
	function nl2br_except_pre($str)
	{
		$CI =& get_instance();
		$CI->load->library('typography');
		
		return $CI->typography->nl2br_except_pre($str);
	}
}
	
// ------------------------------------------------------------------------

/**
 * Auto Typography Wrapper Function
 *
 *
 * @access	public
 * @param	string
 * @param	bool	whether to reduce multiple instances of double newlines to two
 * @return	string
 */
if ( ! function_exists('auto_typography'))
{
	function auto_typography($str, $reduce_linebreaks = FALSE)
	{
		$CI =& get_instance();	
		$CI->load->library('typography');
		return $CI->typography->auto_typography($str, $reduce_linebreaks);
	}
}

/* End of file typography_helper.php */
/* Location: ./system/helpers/typography_helper.php */