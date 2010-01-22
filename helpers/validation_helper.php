<?php

/**
 * CodeIgniter Validation Helpers
 *
 * @package		kcPHP
 * @subpackage	helpers
 * @author		ShiverCube
 * @copyright	Copyright (c) 2008 - 2010, EllisLab, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 * @link		http://codeigniter.com
 * @since		Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

namespace validation;

if ( ! function_exists('validation\min_length'))
{
	/**
	 * Minimum Length
	 *
	 * @param	string
	 * @param	value
	 * @return	bool
	 */	
	function min_length($str, $val)
	{
		if (preg_match('/[^0-9]/', $val))
		{
			return FALSE;
		}

		return ! (utf8\strlen($str) < $val);
	}
}

if ( ! function_exists('validation\max_length'))
{
	/**
	 * Max Length
	 *
	 * @param	string
	 * @param	value
	 * @return	bool
	 */	
	function max_length($str, $val)
	{
		if (preg_match('/[^0-9]/', $val))
		{
			return FALSE;
		}

		return ! (utf8\strlen($str) > $val);
	}
}

if ( ! function_exists('validation\exact_length'))
{
	/**
	 * Exact Length
	 *
	 * @param	string
	 * @param	value
	 * @return	bool
	 */	
	function exact_length($str, $val)
	{
		if (preg_match('/[^0-9]/', $val))
		{
			return FALSE;
		}

		return utf8\strlen($str) == $val;
	}
}

if ( ! function_exists('validation\email'))
{
	/**
	 * Valid Email
	 *
	 * @param	string
	 * @return	bool
	 */	
	function email($str)
	{
		return preg_match('/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix', $str);
	}
}

if ( ! function_exists('validation\emails'))
{
	/**
	 * Valid Emails
	 *
	 * @param	string
	 * @return	bool
	 */	
	function emails($str)
	{
		if (strpos($str, ',') === FALSE)
		{
			return $this->valid_email(utf8\trim($str));
		}
		
		foreach(explode(',', $str) as $email)
		{
			if (utf8\trim($email) != '' && $this->valid_email(utf8\trim($email)) === FALSE)
			{
				return FALSE;
			}
		}
		
		return TRUE;
	}
}

if ( ! function_exists('validation\ip'))
{
	/**
	 * Validate IP Address
	 *
	 * @param	string
	 * @return	string
	 */
	function ip($ip)
	{
		$CI =& get_instance();
		return $CI->input->valid_ip($ip);
	}
}

if ( ! function_exists('validation\alpha'))
{
	/**
	 * Alpha
	 *
	 * @param	string
	 * @return	bool
	 */		
	function alpha($str)
	{
		return preg_match('/^([a-z])+$/i', $str);
	}
}

if ( ! function_exists('validation\alpha_numeric'))
{
	/**
	 * Alpha-numeric
	 *
	 * @param	string
	 * @return	bool
	 */	
	function alpha_numeric($str)
	{
		return preg_match('/^([a-z0-9])+$/i', $str);
	}
}

if ( ! function_exists('validation\alpha_dash'))
{
	/**
	 * Alpha-numeric with underscores and dashes
	 *
	 * @param	string
	 * @return	bool
	 */	
	function alpha_dash($str)
	{
		return preg_match('/^([a-z0-9_-])+$/i', $str);
	}
}

if ( ! function_exists('validation\alpha_score'))
{
	/**
	 * Alpha-numeric with underscores
	 *
	 * @param	string
	 * @return	bool
	 */	
	function alpha_score($str)
	{
		return preg_match('/^([a-z0-9_])+$/i', $str);
	}
}

if ( ! function_exists('validation\numeric'))
{
	/**
	 * Numeric
	 *
	 * @param	string
	 * @return	bool
	 */	
	function numeric($str)
	{
		return preg_match('/^[\-+]?[0-9]*\.?[0-9]+$/', $str);
	}
}

if ( ! function_exists('validation\integer'))
{
	/**
	 * Integer
	 *
	 * @param	string
	 * @return	bool
	 */	
	function integer($str)
	{
		return preg_match('/^[\-+]?[0-9]+$/', $str);
	}
}

if ( ! function_exists('validation\natural'))
{
	/**
	 * Is a Natural number  (0,1,2,3, etc.)
	 *
	 * @param	string
	 * @return	bool
	 */
	function natural($str)
	{
   		return preg_match('/^[0-9]+$/', $str);
	}
}

if ( ! function_exists('validation\natural_no_zero'))
{
	/**
	 * Is a Natural number, but not a zero  (1,2,3, etc.)
	 *
	 * @param	string
	 * @return	bool
	 */
	function natural_no_zero($str)
	{
		return preg_match('/^([1-9][0-9]*)|(0+[0-9]+)$/', $str);
	}
}

if ( ! function_exists('validation\base64'))
{
	/**
	 * Valid Base64
	 *
	 * Tests a string for characters outside of the Base64 alphabet
	 * as defined by RFC 2045 http://www.faqs.org/rfcs/rfc2045
	 *
	 * @param	string
	 * @return	bool
	 */
	function base64($str)
	{
		return ! preg_match('/[^a-zA-Z0-9\/\+=]/', $str);
	}
}

/* End of file validation_helper.php */
/* Location: ./system/helpers/validation_helper.php */