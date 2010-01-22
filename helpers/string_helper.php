<?php
/**
 * CodeIgniter String Helpers
 *
 * @package		kcPHP
 * @subpackage	helpers
 * @author		ExpressionEngine Dev Team
 * @modified	ShiverCube - Removed PHP4 compatibily, and added a few framework tweaks
 * @copyright	Copyright (c) 2008 - 2010, EllisLab, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 * @link		http://codeigniter.com/user_guide/helpers/string_helper.html
 * @since		Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

namespace {

/**
 * Trim Slashes
 *
 * Removes any leading/traling slashes from a string:
 *
 * /this/that/theother/
 *
 * becomes:
 *
 * this/that/theother
 *
 * @access	public
 * @param	string
 * @return	string
 */	
if ( ! function_exists('trim_slashes'))
{
	function trim_slashes($str)
	{
		return trim($str, '/');
	} 
}
	
// ------------------------------------------------------------------------

/**
 * Strip Slashes
 *
 * Removes slashes contained in a string or in an array
 *
 * @access	public
 * @param	mixed	string or array
 * @return	mixed	string or array
 */	
if ( ! function_exists('strip_slashes'))
{
	function strip_slashes($str)
	{
		if (is_array($str))
		{	
			foreach ($str as $key => $val)
			{
				$str[$key] = strip_slashes($val);
			}
		}
		else
		{
			$str = stripslashes($str);
		}
	
		return $str;
	}
}

// ------------------------------------------------------------------------

/**
 * Strip Quotes
 *
 * Removes single and double quotes from a string
 *
 * @access	public
 * @param	string
 * @return	string
 */	
if ( ! function_exists('strip_quotes'))
{
	function strip_quotes($str)
	{
		return str_replace(array('"', '\''), '', $str);
	}
}

// ------------------------------------------------------------------------

/**
 * Quotes to Entities
 *
 * Converts single and double quotes to entities
 *
 * @access	public
 * @param	string
 * @return	string
 */	
if ( ! function_exists('quotes_to_entities'))
{
	function quotes_to_entities($str)
	{
		return str_replace(array("\'", "\"", "'", '"'), array('&#39;', '&quot;', '&#39;', '&quot;'), $str);
	}
}

// ------------------------------------------------------------------------
/**
 * Reduce Double Slashes
 *
 * Converts double slashes in a string to a single slash,
 * except those found in http://
 *
 * http://www.some-site.com//index.php
 *
 * becomes:
 *
 * http://www.some-site.com/index.php
 *
 * @access	public
 * @param	string
 * @return	string
 */	
if ( ! function_exists('reduce_double_slashes'))
{
	function reduce_double_slashes($str)
	{
		return preg_replace('%([^:])//+%', '%\/%', $str);
	}
}
	
// ------------------------------------------------------------------------

/**
 * Reduce Multiples
 *
 * Reduces multiple instances of a particular character.  Example:
 *
 * Fred, Bill,, Joe, Jimmy
 *
 * becomes:
 *
 * Fred, Bill, Joe, Jimmy
 *
 * @access	public
 * @param	string
 * @param	string	the character you wish to reduce
 * @param	bool	TRUE/FALSE - whether to trim the character from the beginning/end
 * @return	string
 */	
if ( ! function_exists('reduce_multiples'))
{
	function reduce_multiples($str, $character = ',', $trim = FALSE)
	{
		$str = preg_replace('#'.preg_quote($character, '#').'{2,}#', $character, $str);

		if ($trim === TRUE)
		{
			$str = trim($str, $character);
		}

		return $str;
	}
}
	
// ------------------------------------------------------------------------

/**
 * Create a Random String
 *
 * Useful for generating passwords or hashes.
 *
 * @access	public
 * @param	string 	type of random string.  Options: alunum, numeric, nozero, unique
 * @param	integer	number of characters
 * @return	string
 */
if ( ! function_exists('random_string'))
{	
	function random_string($type = 'alnum', $len = 8)
	{					
		switch ($type)
		{
			case 'alnum':
			case 'numeric':
			case 'nozero':
				switch ($type)
				{
					case 'alnum':
						$pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
						break;
						
					case 'numeric':
						$pool = '0123456789';
						break;
						
					case 'nozero':
						$pool = '123456789';
						break;
				}

				$str = '';
				for ($i=0; $i < $len; $i++)
				{
					$str .= substr($pool, mt_rand(0, strlen($pool) -1), 1);
				}
				
				return $str;
				
			case 'unique':
				return md5(uniqid(mt_rand()));
		}
	}
}

// ------------------------------------------------------------------------

/**
 * Alternator
 *
 * Allows strings to be alternated.  See docs...
 *
 * @access	public
 * @param	string (as many parameters as needed)
 * @return	string
 */	
if ( ! function_exists('alternator'))
{
	function alternator()
	{
		static $i;	

		if (func_num_args() == 0)
		{
			$i = 0;
			return '';
		}
		
		$args = func_get_args();
		return $args[($i++ % count($args))];
	}
}

// ------------------------------------------------------------------------

/**
 * Repeater function
 *
 * @access	public
 * @param	string
 * @param	integer	number of repeats
 * @return	string
 */	
if ( ! function_exists('repeater'))
{
	function repeater($data, $num = 1)
	{
		return (($num > 0) ? str_repeat($data, $num) : '');
	} 
}

}

namespace str {

if ( ! function_exists('str/remove_last_slash'))
{
	/**
	 * Remove the trailing slash from the given string if it exists
	 *
	 * @param string $str The string to remove the slash from
	 * @return string The original string with the trailing slash removed 
	 */
	function remove_last_slash($str)
	{
		if (substr($str, -1) == '/')
		{
			return substr($str, 0, -1);
		}
		
		return $str;
	}
}

if ( ! function_exists('str/bytes_to_mb'))
{
	/**
	 * Converts the given value of bytes into a megabyte string
	 *
	 * @param int $bytes
	 * @return string
	 */
	function bytes_to_mb($bytes)
	{
		static $NUMBER_OF_BYTES_IN_MB = 1048576;
		return ((int)$bytes / $NUMBER_OF_BYTES_IN_MB).'MB';
	}
}

if ( ! function_exists('str/append'))
{
	/**
	 * Append the given text to the end of the string, adding a newline before it if required
	 *
	 * @param $string string The string to append the text to
	 * @param $text string The text to append to the end of the string
	 * @return string
	 */
	function append($string, $text)
	{
		// Add a newline before the text if the string contains existing data
		if ($string != '')
		{
			$string .= "\n";
		}

		return $string.$text;
	}
}

}

/* End of file string_helper.php */
/* Location: ./system/helpers/string_helper.php */