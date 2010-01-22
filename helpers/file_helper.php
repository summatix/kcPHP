<?php
/**
 * CodeIgniter File Helpers
 *
 * @package		kcPHP
 * @subpackage	helpers
 * @author		ExpressionEngine Dev Team
 * @modified	ShiverCube - Removed PHP4 compatibily, and added a few framework tweaks
 * @copyright	Copyright (c) 2008 - 2010, EllisLab, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 * @link		http://codeigniter.com/user_guide/helpers/file_helpers.html
 * @since		Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

namespace {

// ------------------------------------------------------------------------

/**
 * Read File
 *
 * Opens the file specfied in the path and returns it as a string.
 *
 * @access	public
 * @param	string	path to file
 * @return	string
 */	
if ( ! function_exists('read_file'))
{
	function read_file($file)
	{
		if ( ! file_exists($file))
		{
			return FALSE;
		}
	
		if (function_exists('file_get_contents'))
		{
			return file_get_contents($file);		
		}

		if ( ! $fp = @fopen($file, FOPEN_READ))
		{
			return FALSE;
		}
		
		flock($fp, LOCK_SH);
	
		$data = '';
		if (filesize($file) > 0)
		{
			$data =& fread($fp, filesize($file));
		}

		flock($fp, LOCK_UN);
		fclose($fp);

		return $data;
	}
}
	
// ------------------------------------------------------------------------

/**
 * Write File
 *
 * Writes data to the file specified in the path.
 * Creates a new file if non-existent.
 *
 * @access	public
 * @param	string	path to file
 * @param	string	file data
 * @return	bool
 */	
if ( ! function_exists('write_file'))
{
	function write_file($path, $data, $mode = FOPEN_WRITE_CREATE_DESTRUCTIVE)
	{
		if ( ! $fp = @fopen($path, $mode))
		{
			return FALSE;
		}
		
		flock($fp, LOCK_EX);
		fwrite($fp, $data);
		flock($fp, LOCK_UN);
		fclose($fp);	

		return TRUE;
	}
}
	
// ------------------------------------------------------------------------

/**
 * Delete Files
 *
 * Deletes all files contained in the supplied directory path.
 * Files must be writable or owned by the system in order to be deleted.
 * If the second parameter is set to TRUE, any directories contained
 * within the supplied base directory will be nuked as well.
 *
 * @access	public
 * @param	string	path to file
 * @param	bool	whether to delete any directories found in the path
 * @return	bool
 */	
if ( ! function_exists('delete_files'))
{
	function delete_files($path, $del_dir = FALSE, $level = 0)
	{	
		// Trim the trailing slash
		$path = rtrim($path, DIRECTORY_SEPARATOR);
			
		if ( ! $current_dir = @opendir($path))
		{
			return;
		}
	
		while ($filename = @readdir($current_dir) !== FALSE)
		{
			if ($filename != '.' AND $filename != '..')
			{
				if (is_dir($path.DIRECTORY_SEPARATOR.$filename))
				{
					// Ignore empty folders
					if (substr($filename, 0, 1) != '.')
					{
						delete_files($path.DIRECTORY_SEPARATOR.$filename, $del_dir, $level + 1);
					}				
				}
				else
				{
					unlink($path.DIRECTORY_SEPARATOR.$filename);
				}
			}
		}
		
		@closedir($current_dir);
	
		if ($del_dir == TRUE AND $level > 0)
		{
			@rmdir($path);
		}
	}
}

// ------------------------------------------------------------------------

/**
 * Get Filenames
 *
 * Reads the specified directory and builds an array containing the filenames.  
 * Any sub-folders contained within the specified path are read as well.
 *
 * @access	public
 * @param	string	path to source
 * @param	bool	whether to include the path as part of the filename
 * @param	bool	internal variable to determine recursion status - do not use in calls
 * @return	array
 */	
if ( ! function_exists('get_filenames'))
{
	function get_filenames($source_dir, $include_path = FALSE, $_recursion = FALSE)
	{
		static $filedata = array();
		
		// reset the array and make sure $source_dir has a trailing slash on the initial call
		if ($_recursion === FALSE)
		{
			$filedata = array();
			$source_dir = realpath($source_dir).'/';
		}
		
		if ($fp = @opendir($source_dir))
		{			
			while (($file = readdir($fp)) !== FALSE)
			{
				if (strncmp($file, '.', 1) !== 0)
				{
					if (@is_dir($source_dir.$file))
					{
						get_filenames($source_dir.$file.'/', $include_path, TRUE);
					}
					else
					{
						$filedata[] = $include_path ? realpath($source_dir.$file) : $file;
					}
				}
			}
			
			closedir($fp);
			return $filedata;
		}
		else
		{
			return FALSE;
		}
	}
}

// --------------------------------------------------------------------

/**
 * Get Directory File Information
 *
 * Reads the specified directory and builds an array containing the filenames,  
 * filesize, dates, and permissions
 *
 * Any sub-folders contained within the specified path are read as well.
 *
 * @access	public
 * @param	string	path to source
 * @param	bool	whether to include the path as part of the filename
 * @param	bool	internal variable to determine recursion status - do not use in calls
 * @return	array
 */	
if ( ! function_exists('get_dir_file_info'))
{
	function get_dir_file_info($source_dir, $include_path = FALSE, $_recursion = FALSE)
	{
		static $filedata = array();
		
		$relative_path = $source_dir;

		if ($fp = @opendir($source_dir))
		{
			// reset the array and make sure $source_dir has a trailing slash on the initial call
			if ($_recursion === FALSE)
			{
				$filedata = array();
				$source_dir = rtrim(realpath($source_dir), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
			}

			while ($file = readdir($fp) !== FALSE)
			{
				if (@is_dir($source_dir.$file) AND strncmp($file, '.', 1) !== 0)
				{
					 get_dir_file_info($source_dir.$file.DIRECTORY_SEPARATOR, $include_path, TRUE);
				}
				elseif (strncmp($file, '.', 1) !== 0)
				{
					$filedata[$file] = get_file_info($source_dir.$file);
					$filedata[$file]['relative_path'] = $relative_path;
				}
			}
			return $filedata;
		}
		else
		{
			return FALSE;
		}
	}
}

// --------------------------------------------------------------------

/**
* Get File Info
*
* Given a file and path, returns the name, path, size, date modified
* Second parameter allows you to explicitly declare what information you want returned
* Options are: name, server_path, size, date, readable, writable, executable, fileperms
* Returns FALSE if the file cannot be found.
*
* @access	public
* @param	string	path to file
* @param	mixed	array or comma separated string of information returned
* @return	array
*/
if ( ! function_exists('get_file_info'))
{
	function get_file_info($file, $returned_values = array('name', 'server_path', 'size', 'date'))
	{
		if ( ! file_exists($file))
		{
			return FALSE;
		}

		if (is_string($returned_values))
		{
			$returned_values = explode(',', $returned_values);
		}

		foreach ($returned_values as $key)
		{
			switch ($key)
			{
				case 'name':
					$fileinfo['name'] = substr(strrchr($file, DIRECTORY_SEPARATOR), 1);
					break;
				case 'server_path':
					$fileinfo['server_path'] = $file;
					break;
				case 'size':
					$fileinfo['size'] = filesize($file);
					break;
				case 'date':
					$fileinfo['date'] = filectime($file);
					break;
				case 'readable':
					$fileinfo['readable'] = is_readable($file);
					break;
				case 'writable':
					// There are known problems using is_weritable on IIS.  It may not be reliable - consider fileperms()
					$fileinfo['writable'] = is_writable($file);
					break;
				case 'executable':
					$fileinfo['executable'] = is_executable($file);
					break;
				case 'fileperms':
					$fileinfo['fileperms'] = fileperms($file);
					break;
			}
		}

		return $fileinfo;
	}
}

// --------------------------------------------------------------------

/**
 * Get Mime by Extension
 *
 * Translates a file extension into a mime type based on config/mimes.php. 
 * Returns FALSE if it can't determine the type, or open the mime config file
 *
 * Note: this is NOT an accurate way of determining file mime types, and is here strictly as a convenience
 * It should NOT be trusted, and should certainly NOT be used for security
 *
 * @access	public
 * @param	string	path to file
 * @return	mixed
 */	
if ( ! function_exists('get_mime_by_extension'))
{
	function get_mime_by_extension($file)
	{
		$extension = substr(strrchr($file, '.'), 1);

		global $mimes;

		if ( ! is_array($mimes))
		{
			if (file_exists(APPPATH.'config/mimes.php'))
			{
				include APPPATH.'config/mimes.php';
			}
			
			if ( ! is_array($mimes))
			{
				return FALSE;
			}
		}

		if (array_key_exists($extension, $mimes))
		{
			if (is_array($mimes[$extension]))
			{
				// Multiple mime types, just give the first one
				return current($mimes[$extension]);
			}
			else
			{
				return $mimes[$extension];
			}
		}
		else
		{
			return FALSE;
		}
	}
}

// --------------------------------------------------------------------

/**
 * Symbolic Permissions
 *
 * Takes a numeric value representing a file's permissions and returns
 * standard symbolic notation representing that value
 *
 * @access	public
 * @param	int
 * @return	string
 */	
if ( ! function_exists('symbolic_permissions'))
{
	function symbolic_permissions($perms)
	{	
		if (($perms & 0xC000) == 0xC000)
		{
			$symbolic = 's'; // Socket
		}
		elseif (($perms & 0xA000) == 0xA000)
		{
			$symbolic = 'l'; // Symbolic Link
		}
		elseif (($perms & 0x8000) == 0x8000)
		{
			$symbolic = '-'; // Regular
		}
		elseif (($perms & 0x6000) == 0x6000)
		{
			$symbolic = 'b'; // Block special
		}
		elseif (($perms & 0x4000) == 0x4000)
		{
			$symbolic = 'd'; // Directory
		}
		elseif (($perms & 0x2000) == 0x2000)
		{
			$symbolic = 'c'; // Character special
		}
		elseif (($perms & 0x1000) == 0x1000)
		{
			$symbolic = 'p'; // FIFO pipe
		}
		else
		{
			$symbolic = 'u'; // Unknown
		}

		// Owner
		$symbolic .= (($perms & 0x0100) ? 'r' : '-');
		$symbolic .= (($perms & 0x0080) ? 'w' : '-');
		$symbolic .= (($perms & 0x0040) ? (($perms & 0x0800) ? 's' : 'x' ) : (($perms & 0x0800) ? 'S' : '-'));

		// Group
		$symbolic .= (($perms & 0x0020) ? 'r' : '-');
		$symbolic .= (($perms & 0x0010) ? 'w' : '-');
		$symbolic .= (($perms & 0x0008) ? (($perms & 0x0400) ? 's' : 'x' ) : (($perms & 0x0400) ? 'S' : '-'));

		// World
		$symbolic .= (($perms & 0x0004) ? 'r' : '-');
		$symbolic .= (($perms & 0x0002) ? 'w' : '-');
		$symbolic .= (($perms & 0x0001) ? (($perms & 0x0200) ? 't' : 'x' ) : (($perms & 0x0200) ? 'T' : '-'));

		return $symbolic;		
	}
}

// --------------------------------------------------------------------

/**
 * Octal Permissions
 *
 * Takes a numeric value representing a file's permissions and returns
 * a three character string representing the file's octal permissions
 *
 * @access	public
 * @param	int
 * @return	string
 */	
if ( ! function_exists('octal_permissions'))
{
	function octal_permissions($perms)
	{
		return substr(sprintf('%o', $perms), -3);
	}
}

}

namespace file {
	
if ( ! function_exists('file\get_comments'))
{
	/**
	 * Parses the given file and returns an array of all of the functions or methods within it
	 *
	 * @param string $file The filename to extract the comments from
	 * @return array or FALSE on failure
	 */
	function get_comments($file)
	{
		$text = read_file($file);
		if ($text === FALSE)
		{
			return FALSE;
		}
		
		$methods = array();
		if (preg_match_all('%/\*\*(?P<comment>.+?)\*/\s*([\sa-z]+?\s+)?function\s+(?P<name>[_a-z0-9]+)\(%sim',
			$text, $matches))
		{
			for ($i = 0, $len = count($matches[0]); $i < $len; ++$i)
			{
				$name = $matches['name'][$i];
				$comment = $matches['comment'][$i];
				
				$method = array();
				
				if (preg_match_all('/\s*\*\s*@(?P<name>[a-z0-9]+)[ \t]+(?P<description>[^\r\n]+)/sim',
					$comment, $comment_matches))
				{
					for ($j = 0, $comment_len = count($comment_matches[0]); $j < $comment_len; ++$j)
					{
						$method['tags'][] = array(
							'name' => $comment_matches['name'][$j],
							'description' => $comment_matches['description'][$j]
						);
					}
					
					$comment = preg_replace('/\s*\*\s*@(?P<name>[a-z0-9]+)[ \t]+(?P<description>[^\r\n]+)/sim',
						' ', $comment);
				}
				
				$CI =& get_instance();
				$CI->load->helper('string');
				$method['comment'] = reduce_multiples(preg_replace('/[*\s]/sm', ' ', $comment), ' ', TRUE);
				$methods[$name] = $method;
			}
		}
		
		return $methods;
	}
}

if ( ! function_exists('file\get'))
{
	/**
	 * Gets a list of all the files within a directory, not including sub directories
	 *
	 * @param string $directory The directory to retrieve the list of files for
	 * @param bool $include_path (default FALSE) Set to TRUE to include the path name in the result
	 * @return array The list of files
	 */
	function get($directory, $include_path = FALSE)
	{
		$directory = realpath($directory).'/';
		if ($fp = @opendir($directory))
		{
			$files = array();
			while (($file = readdir($fp)) !== FALSE)
			{
				if ($file != '.' && $file != '..' && strpos($file, '\\') === FALSE && strpos($file, '/') === FALSE)
				{
					$files[] = $include_path ? realpath($directory.$file) : $file;
				}
			}
			
			closedir($fp);
			return $files;
		}
		else
		{
			return FALSE;
		}
	}
}

if ( ! function_exists('file\clean'))
{
	/**
	 * Removes the full path name from the given filename
	 *
	 * @param string $file
	 * @return string
	 */
	function clean($file)
	{
		$file = str_replace('\\', '/', $file);
		
		if (strpos($file, '/') !== FALSE)
		{
			$x = explode('/', $file);
			$file = $x[count($x) - 2].'/'.end($x);
		}
		
		return $file;
	}
}

if ( ! function_exists('file\get_dirs'))
{
	/**
	 * Get Filenames
	 *
	 * Reads the specified directory and builds an array containing the filenames.  
	 * Any sub-folders contained within the specified path are read as well. All
	 * results are grouped by directories.
	 *
	 * @access	public
	 * @param	string	path to source
	 * @param	bool	whether to include the path as part of the filename
	 * @return	array
	 */	
	function get_dirs($source_dir, $include_path = FALSE)
	{		
		$source_dir = realpath($source_dir).'/';
		
		if ($fp = @opendir($source_dir))
		{
			$filedata = array();
			
			while (($file = readdir($fp)) !== FALSE)
			{
				if (strncmp($file, '.', 1) !== 0)
				{
					// If we have found a sub-directory, recursively call this function again, this time appending the
					// results to the subgroup in the array
					if (@is_dir($source_dir.$file))
					{
						$dir = realpath($source_dir.$file);
						if ( ! $include_path)
						{
							$dir = clean($dir);
						}
						
						$filedata[$dir] = get_dirs($source_dir.$file, $include_path);
					}
					else
					{
						$filedata[] = $include_path ? realpath($source_dir.$file) : $file;
					}
				}
			}
			
			closedir($fp);
			return $filedata;
		}
		else
		{
			return FALSE;
		}
	}
}
}

/* End of file file_helper.php */
/* Location: ./system/helpers/file_helper.php */