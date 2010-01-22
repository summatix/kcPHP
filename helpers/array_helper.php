<?php
/**
 * CodeIgniter Array Helpers
 *
 * @package		kcPHP
 * @subpackage	helpers
 * @author		ExpressionEngine Dev Team
 * @modified	ShiverCube - Removed PHP4 compatibily, and added a few framework tweaks
 * @copyright	Copyright (c) 2008 - 2010, EllisLab, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 * @link		http://codeigniter.com/user_guide/helpers/array_helper.html
 * @since		Version 1.0
 * @filesource
 */

namespace {

// ------------------------------------------------------------------------

/**
 * Element
 *
 * Lets you determine whether an array index is set and whether it has a value.
 * If the element is empty it returns FALSE (or whatever you specify as the default value.)
 *
 * @access	public
 * @param	string
 * @param	array
 * @param	mixed
 * @return	mixed	depends on what the array contains
 */	
if ( ! function_exists('element'))
{
	function element($item, $array, $default = FALSE)
	{
		if ( ! isset($array[$item]) OR $array[$item] == '')
		{
			return $default;
		}

		return $array[$item];
	}	
}

// ------------------------------------------------------------------------

/**
 * Random Element - Takes an array as input and returns a random element
 *
 * @access	public
 * @param	array
 * @return	mixed	depends on what the array contains
 */	
if ( ! function_exists('random_element'))
{
	function random_element($array)
	{
		if ( ! is_array($array))
		{
			return $array;
		}
		return $array[array_rand($array)];
	}	
}

}

namespace arr {

if ( ! function_exists('arr\random'))
{
    /**
     * Takes an array as input and returns a random element
     *
     * @param array $array
     * @return mixed
     */
    function random($array)
    {
        if ( ! is_array($array))
        {
            return $array;
        }

        return $array[array_rand($array)];
    }
}

if ( ! function_exists('arr\format'))
{
	/**
	* Formats the list of values in the given array
	* 
	* @param array $arr The array to format
	* @param string $delimiter The text to appear between each value in the array (except the last)
	* @param string $last_delimiter The text to appear before the last item in the array
	* @param callback $callback The function to call to format each value of the array. The function must accept one
	* parameter for the value of the array
	* @return string The formatted list of values
	*/
    function format($arr, $delimiter = ', ', $last_delimiter = ' or ', $wrap = '', $callback = FALSE)
    {
        $len = count($arr);
        $str = '';

        if ($len == 1)
        {
            $str = $arr[0];
        }
        else
        {
            for ($i = 0, $alen = $len - 1; $i < $len; ++$i)
            {
            	$value = $callback !== FALSE ? call_user_func($callback, $arr[$i]) : $arr[$i];
            	
                if ($i != 0)
                {
                    if ($i != $alen)
                    {
                        $str .= $delimiter.$wrap.$value.$wrap;
                    }
                    else
                    {
                        $str .= $last_delimiter.$wrap.$value.$wrap;
                    }
                }
                else
                {
                    $str .= $wrap.$value.$wrap;
                }
            }
        }

        return $str;
    }
}

if ( ! function_exists('arr\to_object'))
{
	/**
	 * Recursively convert an array to an object.
	 *
	 * @param   array   array to convert
	 * @return  object
	 */
	function to_object(array $array, $class = '\stdClass')
	{
		$object = new $class;

		foreach ($array as $key => $value)
		{
			if (is_array($value))
			{
				// Convert the array to an object
				$value = to_object($value, $class);
			}

			// Add the value to the object
			$object->{$key} = $value;
		}

		return $object;
	}
}

if ( ! function_exists('arr\to_array'))
{
	/**
	 * Recursively convert an object to an array.
	 *
	 * @param	object	object to convert
	 * @return	array
	 */
	function to_array($object)
	{
		$array = (array)$object;
		foreach ($array as $key => $value)
		{
			if (is_object($value))
			{
				$array[$key] = to_array($value);
			}
			elseif (is_array($value))
			{
				$array[$key] = to_array($value);
			}
		}
		
		return $array;
	}
}

}

/* End of file array_helper.php */
/* Location: ./system/helpers/array_helper.php */