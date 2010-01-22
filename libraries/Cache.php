<?php
/**
 * kcPHP
 *
 * An open source application development framework for PHP 5.3.0 or newer
 *
 * @package		kcPHP
 * @subpackage	libraries
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
 * Cache Class
 *
 * Partial Caching library for kcPHP
 *
 * @author		ShiverCube
 */
class CI_Cache extends Library {
	
	protected $config_prefix = 'cache_';
	protected $settings = array(
		'path' => ''
	);
	protected $helpers = array('file', 'date');
	
	/**
	 * Initializes the instance
	 */
	protected function _initialize()
	{
		if (empty($this->path))
		{
			$this->path = APPPATH.'cache/';
		}
		
		if ( ! is_dir($this->path))
		{
			throw new InvalidArgumentException("Cache Path not found: {$this->path}");
		}
		
		if ( ! is_really_writable($this->path))
		{
			throw new InvalidArgumentException("Cannot write to Cache Path: {$this->path}");
		}
		
		log_message('debug', 'Cache Class Initialized.');
	}
	
	/**
	 * Executes the given method of a given Library, caching the result, or returns a non expired cached result from a
	 * previous call
	 *
	 * @param string $name The name of the Library
	 * @param string $method The name of the method to call
	 * @param array $arguments (optional) The arguments to provide to the method
	 * @param int $expires (optional) The number of seconds for the cached result to live. If not provided, the cached
	 * result will last forever. Value must be larger than 0
	 */
	public function library($name, $method, array $arguments = array(), $expires = NULL)
	{
		if ( ! isset($this->CI->$name))
		{
			$this->CI->load->library($name);
			log_message('debug', "Cache Class loaded Library: {$name}.");
		}
		
		if ( ! isset($this->CI->$name))
		{
			throw new Exception("The property for the {$name} Library must be named the same as the Library itself.");
		}
		
		return $this->_call($name, $method, $arguments, $expires, 'l');
	}
	
	/**
	 * Executes the given method of a given Model, caching the result, or returns a non expired cached result from a
	 * previous call
	 *
	 * @param string $name The name of the Model
	 * @param string $method The name of the method to call
	 * @param array $arguments (optional) The arguments to provide to the method
	 * @param int $expires (optional) The number of seconds for the cached result to live. If not provided, the cached
	 * result will last forever. Value must be larger than 0
	 */
	public function model($name, $method, array $arguments = array(), $expires = NULL)
	{
		if ( ! isset($this->CI->$name))
		{
			$this->CI->load->model($name);
			log_message('debug', "Cache Class loaded Model: {$name}.");
		}
		
		if ( ! isset($this->CI->$name))
		{
			throw new Exception("The property for the {$name} Model must be named the same as the Model itself.");
		}
		
		return $this->_call($name, $method, $arguments, $expires, 'm');
	}
	
	/**
	 * Calls the given method on the given object, caching the result, or returns a non expired cached result from a
	 * previous call
	 *
	 * @param string $class_name The name of the class to call the method from
	 * @param string $method The name of the method to execute
	 * @param array $arguments The list of arguments to send to the method
	 * @param int $expires The number of seconds for the cached result to live. Value must be larger than 0
	 * @param char $prefix The prefix to add to the front of the file name
	 */
	private function _call($class_name, $method, array $arguments, $expires, $prefix)
	{
		// Clean given arguments to a 0-index array
		$arguments = array_values($arguments);
		
		$cache_file = $this->_get_call_name($class_name, $method, $arguments, $prefix);
		
		$cached_response = $this->get($cache_file);
		if (is_array($cached_response) && isset($cached_response['contents']))
		{
			return $cached_response['contents'];
		}
		else
		{
			$new_response = call_user_func_array(array($this->CI->$class_name, $method), $arguments);
			$this->write($new_response, $cache_file, $expires);
			return $new_response;
		}
	}
	
	/**
	 * Gets the name of a cache file for a Library or a Model
	 *
	 * @param string $class_name The name of the class
	 * @param string $method The name of the method
	 * @param array $arguments The list of arguments for the method
	 * @param char $prefix 'm' for a Model, or 'l' for a Library
	 */
	private function _get_call_name($class_name, $method, array $arguments, $prefix)
	{
		$cache_file = "{$prefix}_{$class_name}-{$method}";
		if (count($arguments) > 0)
		{
			$cache_file .= '_'.md5(serialize($arguments));
		}
		
		return $cache_file;
	}
	
	/**
	 * Writes the given content to the cache
	 *
	 * @throws InvalidArgumentException when $filename is invalid
	 * @param mixed $contents The content to write
	 * @param string $filename The name of the cache file to write to
	 * @param int $expires (optional) The number of seconds for the file to live. If not provided, the cache file will
	 * last forever
	 * @return bool
	 */
	public function write($contents, $filename, $expires = NULL)
	{
		if (empty($filename) || ! is_string($filename))
		{
			throw new InvalidArgumentException;
		}
		
		return $this->_write($contents, $this->_get_filepath($filename), $expires);
	}
	
	/**
	 * Wries the given content to the given filename
	 *
	 * @param mixed $contents The content to write
	 * @param string $filename The filename to write to
	 * @param int $expires The numer of seconds for the file to live. Value must be larger than 0
	 */
	private function _write($contents, $filename, $expires)
	{
		$to_write = array('contents' => $contents);
		
		$expires = (int)$expires;
		if ($expires > 0)
		{
			$to_write['expires'] = now() + $expires;
		}
		
		if ( ! $fp = @fopen($filename, FOPEN_WRITE_CREATE_DESTRUCTIVE))
		{
			log_message('error', "Unable to write Cache file: {$filename}");
			return FALSE;
		}
		
		if (flock($fp, LOCK_EX))
		{
			fwrite($fp, serialize($to_write));
			flock($fp, LOCK_UN);
		}
		else
		{
			log_message('error', "Cache was unable to secure a file lock for file at: {$filename}");
			return FALSE;
		}
		
		fclose($fp);
		
		log_message('debug', "Cache file created: {$filename}");		
		return TRUE;
	}
	
	/**
	 * Gets a file from the cache
	 *
	 * @param string $filename The file to retrieve
	 * @return array An array containing the cached data, or FALSE on failure
	 */
	public function get($filename)
	{
		$filepath = $this->_get_filepath($filename);
		
		if ( ! file_exists($filepath))
		{
			log_message('debug', "Requested cached file \"{$filename}\" does not exist.");
			return FALSE;
		}
		
		if ( ! $fp = @fopen($filepath, FOPEN_READ))
		{
			log_message('error', "Could not get lock on cache folder: {$filepath}");
			return FALSE;
		}
		
		flock($fp, LOCK_SH);
		
		$size = filesize($filepath);
		$contents = $size > 0 ? unserialize(fread($fp, $size)) : array();
		
		flock($fp, LOCK_UN);
		fclose($fp);
		
		if ( ! empty($contents['expires']) && (int)$contents['expires'] < now())
		{
			log_message('debug', "Deleting expired cached file file \"{$filename}\".");
			$this->_delete($filepath);
			return FALSE;
		}
		
		log_message('debug', "Returned cached contents for \"{$filename}\".");
		return $contents;
	}
	
	/**
	 * Deletes a cached Library result
	 *
	 * @param string $name The name of the Library
	 * @param string $method The name of the method which was called
	 * @param $arguments (optional) The arguments which were provided to the call
	 * @return bool TRUE on success
	 */
	public function delete_library($name, $method, array $arguments = array())
	{
		return $this->delete($this->_get_call_name($name, $method, array_values($arguments), 'l'));
	}
	
	/**
	 * Deletes a cached Library result
	 *
	 * @param string $name The name of the Model
	 * @param string $method The name of the method which was called
	 * @param $arguments (optional) The arguments which were provided to the call
	 * @return bool TRUE on success
	 */
	public function delete_model($name, $method, array $arguments = array())
	{
		return $this->delete($this->_get_call_name($name, $method, array_values($arguments), 'm'));
	}
	
	/**
	 * Delets a file from the cache
	 *
	 * @param string $filename The name of the file to delete from the cache
	 * @returns bool TRUE on success
	 */
	public function delete($filename)
	{
		return $this->_delete($this->_get_filepath($filename));
	}
	
	/**
	 * Deletes a file
	 *
	 * @param string $filename The complete path to the file to delete
	 * @returns bool TRUE on success
	 */
	private function _delete($filename)
	{
		$result = file_exists($filename) ? unlink($filename) : FALSE;
		if ($result)
		{
			log_message('debug', "Cached file deleted: {$filename}");
		}
		else
		{
			log_message('debug', "Could not delete cached file: {$filename}");
		}
		
		return $result;
	}
	
	/**
	 * Gets the full name of the cache file for the given short filename
	 *
	 * @param string $filename The filename to retrieve the full path for
	 * @return stirng The full path for the cache file representation
	 */
	private function _get_filepath($filename)
	{
		return "{$this->path}{$filename}.cache";
	}
}

/* End of file Cache.php */
/* Location: ./system/libraries/Cache.php */