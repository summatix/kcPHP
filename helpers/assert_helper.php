<?php
/**
 * CodeIgniter Assert Helpers - Provides functions used for unit testing
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

namespace assert;

if ( ! function_exists('assert\is_true'))
{
	/**
	 * Is true
	 *
	 * @param mixed
	 * @param bool (optional) Set to TRUE to use === instead of ==
	 * @return object
	 */
	function is_true($assertion, $exactly = FALSE)
	{
		return $exactly ?
			($assertion === TRUE ? _result() : _result("\"{$assertion}\" !== TRUE")) :
			($assertion ? _result() : _result("\"{$assertion}\" != TRUE"));
	}
}

if ( ! function_exists('assert\is_false'))
{
	/**
	 * Is false
	 *
	 * @param mixed
	 * @param bool (optional) Set to TRUE to use === instead of ==
	 * @return object
	 */
	function is_false($assertion, $exactly = FALSE)
	{
		return $exactly ?
			($assertion !== FALSE ? _result("\"{$assertion}\" !== FALSE") : _result()) :
			($assertion ? _result("\"{$assertion}\" != FALSE") : _result());
	}
}

if ( ! function_exists('assert\is_not_false'))
{
	/**
	 * Is not false
	 *
	 * @param mixed
	 * @return object
	 */
	function is_not_false($assertion)
	{
		return $assertion !== FALSE ? _result() : _result('value is FALSE');
	}
}

if ( ! function_exists('assert\is_equal'))
{
	/**
	 * Is equal
	 *
	 * @param mixed
	 * @param mixed
	 * @param bool (optional) Set to TRUE to use === instead of ==
	 * @return object
	 */
	function is_equal($base, $check, $exactly = FALSE)
	{
		return $exactly ?
			($base === $check ? _result() : _result("\"{$base}\" !== \"{$check}\"")) :
			($base == $check ? _result() : _result("\"{$base}\" != \"{$check}\""));
	}
}

if ( ! function_exists('assert\not_equal'))
{
	/**
	 * Is not equal
	 *
	 * @param mixed
	 * @param mixed
	 * @param bool (optional) Set to TRUE to use === instead of ==
	 * @return object
	 */
	function not_equal($base, $check, $exactly = FALSE)
	{
		return $exactly ?
			($base !== $check ? _result() : _result("\"{$base}\" === \"{$check}\"")) :
			($base != $check ? _result() : _result("\"{$base}\" == \"{$check}\""));
	}
}

if ( ! function_exists('assert\is_empty'))
{
	/**
	 * Is empty
	 *
	 * @param mixed
	 * @return object
	 */
	function is_empty($assertion)
	{
		return empty($assertion) ? _result() : _result(" ! empty(\"{$assertion}\")");
	}
}

if ( ! function_exists('assert\not_empty'))
{
	/**
	 * Is not empty
	 *
	 * @param mixed
	 * @return object
	 */
	function not_empty($assertion)
	{
		return ! empty($assertion) ? _result() : _result("empty(\"{$assertion}\")");
	}
}

if ( ! function_exists('assert\is_empty_string'))
{
	/**
	 * Is empty string
	 *
	 * @param mixed
	 * @return object
	 */
	function is_empty_string($assertion)
	{
		return $assertion === '' ? _result() : _result("\"{$assertion}\" !== \"\"");
	}
}

if ( ! function_exists('assert\is_array'))
{
	/**
	 * Is array
	 *
	 * @param mixed
	 * @return object
	 */
	function is_array($assertion)
	{
		return \is_array($assertion) ? _result() : _result('value is not an array');
	}
}

if ( ! function_exists('assert\is_object'))
{
	/**
	 * Is object
	 *
	 * @param mixed
	 * @return object
	 */
	function is_object($assertion)
	{
		return \is_object($assertion) ? _result() : _result('value is not an object');
	}
}

if ( ! function_exists('assert\is_set'))
{
	/**
	 * Is set
	 *
	 * @param mixed The object to test
	 * @param $key string The key to search for
	 * @return object
	 */
	function is_set($obj, $key)
	{
		if (\is_array($obj))
		{
			return isset($obj[$key]) ? _result() : _result("\"{$key}\" is not set in array");
		}
		elseif (\is_object($obj))
		{
			return isset($obj->$key) ? _result() : _result("\"{$key}\" is not set in object");
		}
		
		return _result('Invalid value given to function');
	}
}

if ( ! function_exists('assert\file_exists'))
{
	/**
	 * File exists
	 *
	 * @param string
	 * @return object
	 */
	function file_exists($filename)
	{
		return \file_exists($filename) ? _result() : _result("\"{$filename}\" does not exist");
	}
}

if ( ! function_exists('assert\are_arrays_equal'))
{
	/**
	 * Are arrays equal. Tests whether the given one dimensional arrays are the same
	 *
	 * @param array $arr1
	 * @param array $arr2
	 * @return object
	 */
	function are_arrays_equal($arr1, $arr2)
	{
		if ( ! is_array($arr1) || ! is_array($arr2))
		{
			return _result('Arrays must be given to is_array_equal function');
		}
		
		return count(array_diff($arr1, $arr2)) == 0 ? _result() : _result('Arrays are different');
	}
}

if ( ! function_exists('assert\is_string'))
{
	/**
	 * Is string
	 *
	 * @param mixed $assertion
	 * @return object
	 */
	function is_string($assertion)
	{
		
		return \is_string($assertion) ? _result() : _result('value is not a string');
	}
}

if ( ! function_exists('assert\_result'))
{
	/**
	 * Generates a result for an assert
	 *
	 * @param string $description (optional) The description of the error. If this is set, the result of the assert is
	 * set to fail
	 * @return object
	 */
	function _result($error_description = FALSE)
	{
		$obj = new \stdClass;
		
		if (\is_string($error_description) && ! empty($error_description)) // If the assert failed
		{
			$obj->result = FALSE;
			$obj->error_description = $error_description;
		}
		else // If the assert passed
		{
			$obj->result = TRUE;
		}
		
		return $obj;
	}
}

/* End of file assert_helper.php */
/* Location: ./system/helpers/assert_helper.php */