<?php
/**
 * CodeIgniter Inflector Helpers
 *
 * @package		kcPHP
 * @author		ExpressionEngine Dev Team
 * @modified	ShiverCube - Removed PHP4 compatibily, and added a few framework tweaks
 * @copyright	Copyright (c) 2008 - 2010, EllisLab, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 * @link		http://codeigniter.com/user_guide/helpers/directory_helper.html
 * @since		Version 1.0
 * @filesource
 */

// --------------------------------------------------------------------

/**
 * Singular
 *
 * Takes a plural word and makes it singular
 *
 * @access	public
 * @param	string
 * @return	str
 */	
if ( ! function_exists('singular'))
{	
	function singular($str)
	{
		$str = strtolower(trim($str));
		$end = substr($str, -3);
	
		if ($end == 'ies')
		{
			$str = substr($str, 0, strlen($str)-3).'y';
		}
		elseif ($end == 'ses')
		{
			$str = substr($str, 0, strlen($str)-2);
		}
		else
		{
			$end = substr($str, -1);
		
			if ($end == 's')
			{
				$str = substr($str, 0, strlen($str)-1);
			}
		}
	
		return $str;
	}
}

// --------------------------------------------------------------------

/**
 * Plural
 *
 * Takes a singular word and makes it plural
 *
 * @access	public
 * @param	string
 * @param	bool
 * @return	str
 */	
if ( ! function_exists('plural'))
{	
	function plural($str, $force = FALSE)
	{
		$str = strtolower(trim($str));
		$end = substr($str, -1);

		if ($end == 'y')
		{
			// Y preceded by vowel => regular plural
			$vowels = array('a', 'e', 'i', 'o', 'u');
			$str = in_array(substr($str, -2, 1), $vowels) ? $str.'s' : substr($str, 0, -1).'ies';
		}
		else if ($end == 's')
		{
			if ($force == TRUE)
			{
				$str .= 'es';
			}
		}
		else
		{
			$str .= 's';
		}

		return $str;
	}
}

// --------------------------------------------------------------------

/**
 * Camelize
 *
 * Takes multiple words separated by spaces or underscores and camelizes them
 *
 * @access	public
 * @param	string
 * @return	str
 */	
if ( ! function_exists('camelize'))
{	
	function camelize($str)
	{		
		$str = 'x'.strtolower(trim($str));
		$str = ucwords(preg_replace('/[\s_]+/', ' ', $str));
		return substr(str_replace(' ', '', $str), 1);
	}
}

// --------------------------------------------------------------------

/**
 * Underscore
 *
 * Takes multiple words separated by spaces and underscores them
 *
 * @access	public
 * @param	string
 * @return	str
 */	
if ( ! function_exists('underscore'))
{
	function underscore($str)
	{
		return preg_replace('/[\s]+/', '_', strtolower(trim($str)));
	}
}

// --------------------------------------------------------------------

/**
 * Humanize
 *
 * Takes multiple words separated by underscores and changes them to spaces
 *
 * @access	public
 * @param	string
 * @return	str
 */	
if ( ! function_exists('humanize'))
{	
	function humanize($str)
	{
		return ucwords(preg_replace('/[_]+/', ' ', strtolower(trim($str))));
	}
}

/* End of file inflector_helper.php */
/* Location: ./system/helpers/inflector_helper.php */