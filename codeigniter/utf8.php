<?php
/**
 * kcPHP
 *
 * An open source application development framework for PHP 5.3.0 or newer
 *
 * @package		kcPHP
 * @subpackage	codeigniter
 * @author		ExpressionEngine Dev Team
 * @modified	ShiverCube - Removed PHP4 compatibily, and added a few framework tweaks
 * @copyright	Copyright (c) 2008 - 2010, EllisLab, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 * @link		http://codeigniter.com
 * @since		Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * CodeIgniter UTF-8 functions
 *
 * @author		ShiverCube
 */

// ------------------------------------------------------------------------

namespace utf8;

\mb_internal_encoding('UTF-8');

/**
 * Recursively cleans arrays, objects, and strings. Removes ASCII control
 * codes and converts to UTF-8 while silently discarding incompatible
 * UTF-8 characters.
 *
 * @param   string  string to clean
 * @return  string
 */
function clean($str)
{
	if (is_array($str) OR is_object($str))
	{
		foreach ($str as $key => $val)
		{
			// Recursion!
			$str[clean($key)] = clean($val);
		}
	}
	elseif (is_string($str) AND $str !== '')
	{
		// Remove control characters
		$str = strip_ascii_ctrl($str);

		if ( ! is_ascii($str))
		{
			// iconv is expensive, so it is only used when needed
			$str = @iconv('UTF-8', 'UTF-8//IGNORE', $str); // Disable notices with @
		}
	}

	return $str;
}

/**
 * Tests whether a string contains only 7bit ASCII bytes. This is used to
 * determine when to use native functions or UTF-8 functions.
 *
 * @param   string  string to check
 * @return  bool
 */
function is_ascii($str)
{
	return ! preg_match('/[^\x00-\x7F]/S', $str);
}

/**
 * Returns the length of the given string
 *
 * @param string $str The string being measured for length
 * @return The length of the string on success, and 0 if the string is empty
 */
function strlen($str)
{
	return \mb_strlen($str);
}

/**
 * Strips out device control codes in the ASCII range.
 *
 * @param   string  string to clean
 * @return  string
 */
function strip_ascii_ctrl($str)
{
	return preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S', '', $str);
}

/**
 * Strips out all non-7bit ASCII bytes.
 *
 * @param   string  string to clean
 * @return  string
 */
function strip_non_ascii($str)
{
	return preg_replace('/[^\x00-\x7F]+/S', '', $str);
}

/**
 * Replaces special/accented UTF-8 characters by ASCII-7 'equivalents'.
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 *
 * @param   string   string to transliterate
 * @param   integer  -1 lowercase only, +1 uppercase only, 0 both cases
 * @return  string
 */
function transliterate_to_ascii($str, $case = 0)
{
	static $UTF8_LOWER_ACCENTS = NULL;
	static $UTF8_UPPER_ACCENTS = NULL;

	if ($case <= 0)
	{
		if ($UTF8_LOWER_ACCENTS === NULL)
		{
			$UTF8_LOWER_ACCENTS = array(
				'à' => 'a',  'ô' => 'o',  'ď' => 'd',  'ḟ' => 'f',  'ë' => 'e',  'š' => 's',  'ơ' => 'o',
				'ß' => 'ss', 'ă' => 'a',  'ř' => 'r',  'ț' => 't',  'ň' => 'n',  'ā' => 'a',  'ķ' => 'k',
				'ŝ' => 's',  'ỳ' => 'y',  'ņ' => 'n',  'ĺ' => 'l',  'ħ' => 'h',  'ṗ' => 'p',  'ó' => 'o',
				'ú' => 'u',  'ě' => 'e',  'é' => 'e',  'ç' => 'c',  'ẁ' => 'w',  'ċ' => 'c',  'õ' => 'o',
				'ṡ' => 's',  'ø' => 'o',  'ģ' => 'g',  'ŧ' => 't',  'ș' => 's',  'ė' => 'e',  'ĉ' => 'c',
				'ś' => 's',  'î' => 'i',  'ű' => 'u',  'ć' => 'c',  'ę' => 'e',  'ŵ' => 'w',  'ṫ' => 't',
				'ū' => 'u',  'č' => 'c',  'ö' => 'o',  'è' => 'e',  'ŷ' => 'y',  'ą' => 'a',  'ł' => 'l',
				'ų' => 'u',  'ů' => 'u',  'ş' => 's',  'ğ' => 'g',  'ļ' => 'l',  'ƒ' => 'f',  'ž' => 'z',
				'ẃ' => 'w',  'ḃ' => 'b',  'å' => 'a',  'ì' => 'i',  'ï' => 'i',  'ḋ' => 'd',  'ť' => 't',
				'ŗ' => 'r',  'ä' => 'a',  'í' => 'i',  'ŕ' => 'r',  'ê' => 'e',  'ü' => 'u',  'ò' => 'o',
				'ē' => 'e',  'ñ' => 'n',  'ń' => 'n',  'ĥ' => 'h',  'ĝ' => 'g',  'đ' => 'd',  'ĵ' => 'j',
				'ÿ' => 'y',  'ũ' => 'u',  'ŭ' => 'u',  'ư' => 'u',  'ţ' => 't',  'ý' => 'y',  'ő' => 'o',
				'â' => 'a',  'ľ' => 'l',  'ẅ' => 'w',  'ż' => 'z',  'ī' => 'i',  'ã' => 'a',  'ġ' => 'g',
				'ṁ' => 'm',  'ō' => 'o',  'ĩ' => 'i',  'ù' => 'u',  'į' => 'i',  'ź' => 'z',  'á' => 'a',
				'û' => 'u',  'þ' => 'th', 'ð' => 'dh', 'æ' => 'ae', 'µ' => 'u',  'ĕ' => 'e',
			);
		}

		$str = str_replace(
			array_keys($UTF8_LOWER_ACCENTS),
			array_values($UTF8_LOWER_ACCENTS),
			$str
		);
	}

	if ($case >= 0)
	{
		if ($UTF8_UPPER_ACCENTS === NULL)
		{
			$UTF8_UPPER_ACCENTS = array(
				'À' => 'A',  'Ô' => 'O',  'Ď' => 'D',  'Ḟ' => 'F',  'Ë' => 'E',  'Š' => 'S',  'Ơ' => 'O',
				'Ă' => 'A',  'Ř' => 'R',  'Ț' => 'T',  'Ň' => 'N',  'Ā' => 'A',  'Ķ' => 'K',  'Ĕ' => 'E',
				'Ŝ' => 'S',  'Ỳ' => 'Y',  'Ņ' => 'N',  'Ĺ' => 'L',  'Ħ' => 'H',  'Ṗ' => 'P',  'Ó' => 'O',
				'Ú' => 'U',  'Ě' => 'E',  'É' => 'E',  'Ç' => 'C',  'Ẁ' => 'W',  'Ċ' => 'C',  'Õ' => 'O',
				'Ṡ' => 'S',  'Ø' => 'O',  'Ģ' => 'G',  'Ŧ' => 'T',  'Ș' => 'S',  'Ė' => 'E',  'Ĉ' => 'C',
				'Ś' => 'S',  'Î' => 'I',  'Ű' => 'U',  'Ć' => 'C',  'Ę' => 'E',  'Ŵ' => 'W',  'Ṫ' => 'T',
				'Ū' => 'U',  'Č' => 'C',  'Ö' => 'O',  'È' => 'E',  'Ŷ' => 'Y',  'Ą' => 'A',  'Ł' => 'L',
				'Ų' => 'U',  'Ů' => 'U',  'Ş' => 'S',  'Ğ' => 'G',  'Ļ' => 'L',  'Ƒ' => 'F',  'Ž' => 'Z',
				'Ẃ' => 'W',  'Ḃ' => 'B',  'Å' => 'A',  'Ì' => 'I',  'Ï' => 'I',  'Ḋ' => 'D',  'Ť' => 'T',
				'Ŗ' => 'R',  'Ä' => 'A',  'Í' => 'I',  'Ŕ' => 'R',  'Ê' => 'E',  'Ü' => 'U',  'Ò' => 'O',
				'Ē' => 'E',  'Ñ' => 'N',  'Ń' => 'N',  'Ĥ' => 'H',  'Ĝ' => 'G',  'Đ' => 'D',  'Ĵ' => 'J',
				'Ÿ' => 'Y',  'Ũ' => 'U',  'Ŭ' => 'U',  'Ư' => 'U',  'Ţ' => 'T',  'Ý' => 'Y',  'Ő' => 'O',
				'Â' => 'A',  'Ľ' => 'L',  'Ẅ' => 'W',  'Ż' => 'Z',  'Ī' => 'I',  'Ã' => 'A',  'Ġ' => 'G',
				'Ṁ' => 'M',  'Ō' => 'O',  'Ĩ' => 'I',  'Ù' => 'U',  'Į' => 'I',  'Ź' => 'Z',  'Á' => 'A',
				'Û' => 'U',  'Þ' => 'Th', 'Ð' => 'Dh', 'Æ' => 'Ae',
			);
		}

		$str = str_replace(
			array_keys($UTF8_UPPER_ACCENTS),
			array_values($UTF8_UPPER_ACCENTS),
			$str
		);
	}

	return $str;
}

/**
 * Strips whitespace (or other UTF-8 characters) from the beginning of a string.
 * @see http://php.net/ltrim
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 *
 * @param   string   input string
 * @param   string   string of characters to remove
 * @return  string
 */
function ltrim($str, $charlist = NULL)
{
	if ($charlist === NULL)
	{
		return \ltrim($str);
	}

	if (is_ascii($charlist))
	{
		return \ltrim($str, $charlist);
	}

	$charlist = preg_replace('#[-\[\]:\\\\^/]#', '\\\\$0', $charlist);
	return preg_replace('/^['.$charlist.']+/u', '', $str);
}

/**
 * Strips whitespace (or other UTF-8 characters) from the end of a string.
 * @see http://php.net/rtrim
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 *
 * @param   string   input string
 * @param   string   string of characters to remove
 * @return  string
 */
function rtrim($str, $charlist = NULL)
{
	if ($charlist === NULL)
	{
		return \rtrim($str);
	}

	if (is_ascii($charlist))
	{
		return \rtrim($str, $charlist);
	}

	$charlist = preg_replace('#[-\[\]:\\\\^/]#', '\\\\$0', $charlist);
	return preg_replace('/['.$charlist.']++$/uD', '', $str);
}

/**
 * Strips whitespace (or other UTF-8 characters) from the beginning and
 * end of a string.
 * @see http://php.net/trim
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 *
 * @param   string   input string
 * @param   string   string of characters to remove
 * @return  string
 */
function trim($str, $charlist = NULL)
{
	if ($charlist === NULL)
	{
		return \trim($str);
	}

	return ltrim(rtrim($str, $charlist), $charlist);
}

/**
 * Returns part of a UTF-8 string.
 * @see http://php.net/substr
 *
 * @author  Chris Smith <chris@jalakai.co.uk>
 *
 * @param   string   input string
 * @param   integer  offset
 * @param   integer  length limit
 * @return  string
 */
function substr($str, $offset, $length = NULL)
{
	return ($length === NULL) ? \mb_substr($str, $offset) : \mb_substr($str, $offset, $length);
}

/**
 * Takes an UTF-8 string and returns an array of ints representing the Unicode characters.
 * Astral planes are supported i.e. the ints in the output can be > 0xFFFF.
 * Occurrances of the BOM are ignored. Surrogates are not allowed.
 *
 * The Original Code is Mozilla Communicator client code.
 * The Initial Developer of the Original Code is Netscape Communications Corporation.
 * Portions created by the Initial Developer are Copyright (C) 1998 the Initial Developer.
 * Ported to PHP by Henri Sivonen <hsivonen@iki.fi>, see http://hsivonen.iki.fi/php-utf8/.
 * Slight modifications to fit with phputf8 library by Harry Fuecks <hfuecks@gmail.com>.
 *
 * @param   string   UTF-8 encoded string
 * @return  array	unicode code points
 * @return  boolean  FALSE if the string is invalid
 */
function to_unicode($str)
{
	$mState = 0; // cached expected number of octets after the current octet until the beginning of the next
		// UTF8 character sequence
	$mUcs4  = 0; // cached Unicode character
	$mBytes = 1; // cached expected number of octets in the current sequence

	$out = array();

	$len = \strlen($str);

	for ($i = 0; $i < $len; $i++)
	{
		$in = \ord($str[$i]);

		if ($mState == 0)
		{
			// When mState is zero we expect either a US-ASCII character or a
			// multi-octet sequence.
			if (0 == (0x80 & $in))
			{
				// US-ASCII, pass straight through.
				$out[] = $in;
				$mBytes = 1;
			}
			elseif (0xC0 == (0xE0 & $in))
			{
				// First octet of 2 octet sequence
				$mUcs4 = $in;
				$mUcs4 = ($mUcs4 & 0x1F) << 6;
				$mState = 1;
				$mBytes = 2;
			}
			elseif (0xE0 == (0xF0 & $in))
			{
				// First octet of 3 octet sequence
				$mUcs4 = $in;
				$mUcs4 = ($mUcs4 & 0x0F) << 12;
				$mState = 2;
				$mBytes = 3;
			}
			elseif (0xF0 == (0xF8 & $in))
			{
				// First octet of 4 octet sequence
				$mUcs4 = $in;
				$mUcs4 = ($mUcs4 & 0x07) << 18;
				$mState = 3;
				$mBytes = 4;
			}
			elseif (0xF8 == (0xFC & $in))
			{
				// First octet of 5 octet sequence.
				//
				// This is illegal because the encoded codepoint must be either
				// (a) not the shortest form or
				// (b) outside the Unicode range of 0-0x10FFFF.
				// Rather than trying to resynchronize, we will carry on until the end
				// of the sequence and let the later error handling code catch it.
				$mUcs4 = $in;
				$mUcs4 = ($mUcs4 & 0x03) << 24;
				$mState = 4;
				$mBytes = 5;
			}
			elseif (0xFC == (0xFE & $in))
			{
				// First octet of 6 octet sequence, see comments for 5 octet sequence.
				$mUcs4 = $in;
				$mUcs4 = ($mUcs4 & 1) << 30;
				$mState = 5;
				$mBytes = 6;
			}
			else
			{
				// Current octet is neither in the US-ASCII range nor a legal first octet of a multi-octet sequence
				\trigger_error('to_unicode: Illegal sequence identifier in UTF-8 at byte '.$i,
					E_USER_WARNING);
				return FALSE;
			}
		}
		else
		{
			// When mState is non-zero, we expect a continuation of the multi-octet sequence
			if (0x80 == (0xC0 & $in))
			{
				// Legal continuation
				$shift = ($mState - 1) * 6;
				$tmp = $in;
				$tmp = ($tmp & 0x0000003F) << $shift;
				$mUcs4 |= $tmp;

				// End of the multi-octet sequence. mUcs4 now contains the final Unicode codepoint to be output
				if (0 == --$mState)
				{
					// Check for illegal sequences and codepoints

					// From Unicode 3.1, non-shortest form is illegal
					if (((2 == $mBytes) AND ($mUcs4 < 0x0080)) OR
						((3 == $mBytes) AND ($mUcs4 < 0x0800)) OR
						((4 == $mBytes) AND ($mUcs4 < 0x10000)) OR
						(4 < $mBytes) OR
						// From Unicode 3.2, surrogate characters are illegal
						(($mUcs4 & 0xFFFFF800) == 0xD800) OR
						// Codepoints outside the Unicode range are illegal
						($mUcs4 > 0x10FFFF))
					{
						\trigger_error('to_unicode: Illegal sequence or codepoint in UTF-8 at byte '.$i,
							E_USER_WARNING);
						return FALSE;
					}

					if (0xFEFF != $mUcs4)
					{
						// BOM is legal but we don't want to output it
						$out[] = $mUcs4;
					}

					// Initialize UTF-8 cache
					$mState = 0;
					$mUcs4  = 0;
					$mBytes = 1;
				}
			}
			else
			{
				// ((0xC0 & (*in) != 0x80) AND (mState != 0))
				// Incomplete multi-octet sequence
				\trigger_error('to_unicode: Incomplete multi-octet sequence in UTF-8 at byte '.$i,
					E_USER_WARNING);
				return FALSE;
			}
		}
	}

	return $out;
}

/**
 * Converts an array of unicode characters to a string of HTML character entities
 *
 * @param	array	The unicode characters to convert
 * @return	string	The string of HTML entities
 */
function to_entities($unicode)
{
	$entities = '';
	foreach ($unicode as $value)
	{
		$entities .= "&#{$value};";
	}
	
	return $entities;
}

/**
 * Converts an array of unicode characters to a string of HTML character entities, preserving all existing ASCII
 * characters
 */
function to_entities_preserving_ascii($unicode)
{
	$entities = '';
	foreach ($unicode as $value)
	{
		if ($value > 127)
		{
			$entities .= "&#{$value};";
		}
		else
		{
			$entities .= chr($value);
		}
	}
	
	return $entities;
}

/**
 * Takes an array of ints representing the Unicode characters and returns a UTF-8 string.
 * Astral planes are supported i.e. the ints in the input can be > 0xFFFF.
 * Occurrances of the BOM are ignored. Surrogates are not allowed.
 *
 * The Original Code is Mozilla Communicator client code.
 * The Initial Developer of the Original Code is Netscape Communications Corporation.
 * Portions created by the Initial Developer are Copyright (C) 1998 the Initial Developer.
 * Ported to PHP by Henri Sivonen <hsivonen@iki.fi>, see http://hsivonen.iki.fi/php-utf8/.
 * Slight modifications to fit with phputf8 library by Harry Fuecks <hfuecks@gmail.com>.
 *
 * @param   array	unicode code points representing a string
 * @return  string   utf8 string of characters
 * @return  boolean  FALSE if a code point cannot be found
 */
function from_unicode($arr)
{
	ob_start();

	$keys = array_keys($arr);

	foreach ($keys as $k)
	{
		// ASCII range (including control chars)
		if (($arr[$k] >= 0) AND ($arr[$k] <= 0x007f))
		{
			echo chr($arr[$k]);
		}
		// 2 byte sequence
		elseif ($arr[$k] <= 0x07ff)
		{
			echo chr(0xc0 | ($arr[$k] >> 6));
			echo chr(0x80 | ($arr[$k] & 0x003f));
		}
		// Byte order mark (skip)
		elseif ($arr[$k] == 0xFEFF)
		{
			// nop -- zap the BOM
		}
		// Test for illegal surrogates
		elseif ($arr[$k] >= 0xD800 AND $arr[$k] <= 0xDFFF)
		{
			// Found a surrogate
			\trigger_error('from_unicode: Illegal surrogate at index: '.$k.', value: '.$arr[$k],
				E_USER_WARNING);
			return FALSE;
		}
		// 3 byte sequence
		elseif ($arr[$k] <= 0xffff)
		{
			echo chr(0xe0 | ($arr[$k] >> 12));
			echo chr(0x80 | (($arr[$k] >> 6) & 0x003f));
			echo chr(0x80 | ($arr[$k] & 0x003f));
		}
		// 4 byte sequence
		elseif ($arr[$k] <= 0x10ffff)
		{
			echo chr(0xf0 | ($arr[$k] >> 18));
			echo chr(0x80 | (($arr[$k] >> 12) & 0x3f));
			echo chr(0x80 | (($arr[$k] >> 6) & 0x3f));
			echo chr(0x80 | ($arr[$k] & 0x3f));
		}
		// Out of range
		else
		{
			\trigger_error('from_unicode: Codepoint out of Unicode range at index: '.$k.', value: '.$arr[$k],
				E_USER_WARNING);
			return FALSE;
		}
	}

	$result = ob_get_contents();
	ob_end_clean();
	return $result;
}

/**
 * Makes a UTF-8 string lowercase.
 * @see http://php.net/strtolower
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 *
 * @param   string   mixed case string
 * @return  string
 */
function strtolower($str)
{
	return \mb_strtolower($str);
}

/**
 * Makes a UTF-8 string uppercase.
 * @see http://php.net/strtoupper
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 *
 * @param   string   mixed case string
 * @return  string
 */
function strtoupper($str)
{
	return \mb_strtoupper($str);
}

/**
 * Replaces text within a portion of a UTF-8 string.
 * @see http://php.net/substr_replace
 *
 * @author  Harry Fuecks <hfuecks@gmail.com>
 *
 * @param   string   input string
 * @param   string   replacement string
 * @param   integer  offset
 * @return  string
 */
function substr_replace($str, $replacement, $offset, $length = NULL)
{
	if (is_ascii($str))
	{
		return ($length === NULL) ?
			\substr_replace($str, $replacement, $offset) : \substr_replace($str, $replacement, $offset, $length);
	}
	
	$length = ($length === NULL) ? strlen($str) : (int)$length;
	preg_match_all('/./us', $str, $str_array);
	preg_match_all('/./us', $replacement, $replacement_array);
	
	array_splice($str_array[0], $offset, $length, $replacement_array[0]);
	return implode('', $str_array[0]);
}

/**
 * Makes a UTF-8 string's first character uppercase.
 * @see http://php.net/ucfirst
 *
 * @author  Harry Fuecks <hfuecks@gmail.com>
 *
 * @param   string   mixed case string
 * @return  string
 */
function ucfirst($str)
{
	if (is_ascii($str))
	{
		return \ucfirst($str);
	}

	preg_match('/^(.?)(.*)$/us', $str, $matches);
	return strtoupper($matches[1]).$matches[2];
}

/**
 * Makes the first character of every word in a UTF-8 string uppercase.
 * @see http://php.net/ucwords
 *
 * @author  Harry Fuecks <hfuecks@gmail.com>
 *
 * @param   string   mixed case string
 * @return  string
 */
function ucwords($str)
{
	return \mb_convert_case($str, MB_CASE_TITLE);
}

/**
 * Case-insensitive UTF-8 string comparison.
 * @see http://php.net/strcasecmp
 *
 * @author  Harry Fuecks <hfuecks@gmail.com>
 *
 * @param   string   string to compare
 * @param   string   string to compare
 * @return  integer  less than 0 if str1 is less than str2
 * @return  integer  greater than 0 if str1 is greater than str2
 * @return  integer  0 if they are equal
 */
function strcasecmp($str1, $str2)
{
	if (is_ascii($str1) AND is_ascii($str2))
	{
		return \strcasecmp($str1, $str2);
	}

	$str1 = strtolower($str1);
	$str2 = strtolower($str2);
	return strcmp($str1, $str2);
}

/**
 * Finds the length of the initial segment not matching mask.
 * @see http://php.net/strcspn
 *
 * @author  Harry Fuecks <hfuecks@gmail.com>
 *
 * @param   string   input string
 * @param   string   mask for search
 * @param   integer  start position of the string to examine
 * @param   integer  length of the string to examine
 * @return  integer  length of the initial segment that contains characters not in the mask
 */
function strcspn($str, $mask, $offset = NULL, $length = NULL)
{
	if ($str == '' OR $mask == '')
	{
		return 0;
	}

	if (is_ascii($str) AND is_ascii($mask))
	{
		return ($offset === NULL) ? \strcspn($str, $mask) :
			(($length === NULL) ? \strcspn($str, $mask, $offset) : \strcspn($str, $mask, $offset, $length));
	}

	if ($str !== NULL OR $length !== NULL)
	{
		$str = substr($str, $offset, $length);
	}

	// Escape these characters:  - [ ] . : \ ^ /
	// The . and : are escaped to prevent possible warnings about POSIX regex elements
	$mask = preg_replace('#[-[\].:\\\\^/]#', '\\\\$0', $mask);
	preg_match('/^[^'.$mask.']+/u', $str, $matches);

	return isset($matches[0]) ? strlen($matches[0]) : 0;
}

/**
 * Converts a UTF-8 string to an array.
 * @see http://php.net/str_split
 *
 * @author  Harry Fuecks <hfuecks@gmail.com>
 *
 * @param   string   input string
 * @param   integer  maximum length of each chunk
 * @return  array
 */
function str_split($str, $split_length = 1)
{
	$split_length = (int) $split_length;

	if (is_ascii($str))
	{
		return \str_split($str, $split_length);
	}

	if ($split_length < 1)
	{
		return FALSE;
	}

	if (strlen($str) <= $split_length)
	{
		return array($str);
	}

	preg_match_all('/.{'.$split_length.'}|[^\x00]{1,'.$split_length.'}$/us', $str, $matches);
	return $matches[0];
}

/**
 * Reverses a UTF-8 string.
 * @see http://php.net/strrev
 *
 * @author  Harry Fuecks <hfuecks@gmail.com>
 *
 * @param   string   string to be reversed
 * @return  string
 */
function strrev($str)
{
	if (is_ascii($str))
	{
		return \strrev($str);
	}

	preg_match_all('/./us', $str, $matches);
	return implode('', array_reverse($matches[0]));
}

/**
 * Returns the unicode ordinal for a character.
 * @see http://php.net/ord
 *
 * @author Harry Fuecks <hfuecks@gmail.com>
 *
 * @param   string   UTF-8 encoded character
 * @return  integer
 */
function ord($chr)
{
	$ord0 = \ord($chr);

	if ($ord0 >= 0 AND $ord0 <= 127)
	{
		return $ord0;
	}

	if ( ! isset($chr[1]))
	{
		\trigger_error('Short sequence - at least 2 bytes expected, only 1 seen', E_USER_WARNING);
		return FALSE;
	}

	$ord1 = \ord($chr[1]);

	if ($ord0 >= 192 AND $ord0 <= 223)
	{
		return ($ord0 - 192) * 64 + ($ord1 - 128);
	}

	if ( ! isset($chr[2]))
	{
		\trigger_error('Short sequence - at least 3 bytes expected, only 2 seen', E_USER_WARNING);
		return FALSE;
	}

	$ord2 = \ord($chr[2]);

	if ($ord0 >= 224 AND $ord0 <= 239)
	{
		return ($ord0 - 224) * 4096 + ($ord1 - 128) * 64 + ($ord2 - 128);
	}

	if ( ! isset($chr[3]))
	{
		\trigger_error('Short sequence - at least 4 bytes expected, only 3 seen', E_USER_WARNING);
		return FALSE;
	}

	$ord3 = \ord($chr[3]);

	if ($ord0 >= 240 AND $ord0 <= 247)
	{
		return ($ord0 - 240) * 262144 + ($ord1 - 128) * 4096 + ($ord2-128) * 64 + ($ord3 - 128);
	}

	if ( ! isset($chr[4]))
	{
		\trigger_error('Short sequence - at least 5 bytes expected, only 4 seen', E_USER_WARNING);
		return FALSE;
	}

	$ord4 = \ord($chr[4]);

	if ($ord0 >= 248 AND $ord0 <= 251)
	{
		return ($ord0 - 248) * 16777216 + ($ord1-128) * 262144 + ($ord2 - 128) * 4096 + ($ord3 - 128) * 64 + ($ord4 - 128);
	}

	if ( ! isset($chr[5]))
	{
		\trigger_error('Short sequence - at least 6 bytes expected, only 5 seen', E_USER_WARNING);
		return FALSE;
	}

	if ($ord0 >= 252 AND $ord0 <= 253)
	{
		return ($ord0 - 252) * 1073741824 + ($ord1 - 128) * 16777216 + ($ord2 - 128) * 262144 + ($ord3 - 128) * 4096 + ($ord4 - 128) * 64 + (ord($chr[5]) - 128);
	}

	if ($ord0 >= 254 AND $ord0 <= 255)
	{
		\trigger_error('Invalid UTF-8 with surrogate ordinal '.$ord0, E_USER_WARNING);
		return FALSE;
	}
}

/* End of file utf8_helper.php */
/* Location: ./system/helpers/utf8_helper.php */