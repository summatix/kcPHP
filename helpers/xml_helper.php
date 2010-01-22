<?php
/**
 * CodeIgniter XML Helpers
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

namespace {

// ------------------------------------------------------------------------

/**
 * Convert Reserved XML characters to Entities
 *
 * @access	public
 * @param	string
 * @return	string
 */	
if ( ! function_exists('xml_convert'))
{
	function xml_convert($str)
	{
		$temp = '__TEMP_AMPERSANDS__';
		
		// Replace entities to temporary markers so that 
		// ampersands won't get messed up
		$str = preg_replace('/&#(\d+);/', $temp.'\1;', $str);
		$str = preg_replace('/&(\w+);/',  $temp.'\1;', $str);
		
		$str = str_replace(array('&', '<', '>', '"', '\'', '-'),
			array('&amp;', '&lt;', '&gt;', '&quot;', '&#39;', '&#45;'), $str);
		
		// Decode the temp markers back to entities		
		$str = preg_replace('/'.$temp.'(\d+);/', '&#\1;', $str);
		$str = preg_replace('/'.$temp.'(\w+);/', '&\1;', $str);
		
		return $str;
	}
}

if ( ! function_exists('xml_encode'))
{
	/**
	 * Returns the XML representation of a value. This is a private recursive method which is called by the public
	 * method encode()
	 *
	 * @param object|array $to_encode
	 * @param string $root The name of the root element
	 * @param string $encoding
	 * @param int $_level The current level within the XML document. Used for recursion purposes
	 * @param string $_last_key The last key which was used. This is updated on each recursive call
	 * @return string
	 */
	function xml_encode($to_encode, $root = 'array', $encoding = 'UTF-8', $_level = 1, $_last_key = '')
	{
		if ($_last_key == '')
		{
			$_last_key = $root;
		}
		
		// If this is the first call, then start with a new XML tag
		$xml = $_level == 1 ? "<?xml version=\"1.0\" encoding=\"{$encoding}\"?>\n<{$root}>\n" : '';
	 
		// If the given content is an object, convert it to an array so that we can loop through all the values
		if (is_object($to_encode))
		{
			$to_encode = get_object_vars($to_encode);
		}
	 
		// Loop through each value in the array and add it to the current level if it is a single value, or make a
		// recursive call and indent the level by one if the value contains a collection of sub values
		foreach ($to_encode as $key => $value)
		{
			if (is_numeric($key)) // Assume we are dealing with an index based array, so try to get a more suitable key
			{
				// Use the singular of $_last_key if it is not empty
				if ($_last_key != '')
				{
					$key = strtolower(trim($_last_key));
					$end = substr($key, -3);
	 
					if ($end == 'ies')
					{
						$key = substr($key, 0, strlen($key) - 3).'y';
					}
					elseif ($end == 'ses')
					{
						$key = substr($key, 0, strlen($key) - 2);
					}
					else
					{
						$end = substr($key, -1);
	 
						if ($end == 's')
						{
							$key = substr($key, 0, strlen($key)-1);
						}
					}
				}
				else // Otherwise just use root to avoid an error
				{
					$key = $root;
				}
			}
	 
			if (is_array($value) || is_object($value))
			{
				$xml .= str_repeat("\t", $_level)."<{$key}>\n".xml_encode($value, $root, $encoding, $_level + 1, $key)
					.str_repeat("\t", $_level)."</{$key}>\n";
			}
			else
			{
				// Trim the data since XML ignores whitespace, and convert entities to an appropriate form so that the
				// XML remains valid
				$value = xml_convert(trim($value));
				$xml .= str_repeat("\t", $_level)."<{$key}>{$value}</{$key}>\n";
			}
		}
	 
		// Close the XML tag if this is the last recursive call
		return $_level == 1 ? "{$xml}</{$root}>" : $xml;
	}
}

}

// ------------------------------------------------------------------------

namespace xml {

if ( ! function_exists('xml\output'))
{
	/**
	 * Converts and displays the given array as XML, and sends the appropriate headers
	 *
	 * @param $xml mixed The array or object to encode to XML
	 * @param $root string (default "array") The name of the root element
	 * @param $encoding string (default "UTF-8") The encoding type to use for XML document
	 */
	function output($xml = array(), $root = 'array', $encoding = 'UTF-8')
	{		
		$xml = xml_encode($xml, $root, $encoding);
		
		$CI =& get_instance();
		$CI->output->set_header('Content-Type: text/xml');
		$CI->output->set_header('Cache-Control: no-store, no-cache, must-revalidate');
		$CI->output->set_header('Pragma: no-cache');
		$CI->output->set_header('Content-Length: '.strlen($xml));
		$CI->output->set_output($xml);
	}
}

}

/* End of file xml_helper.php */
/* Location: ./system/helpers/xml_helper.php */