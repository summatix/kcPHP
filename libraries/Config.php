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
 * CodeIgniter Config Class
 *
 * This class contains functions that enable config files to be managed
 *
 * @author		ExpressionEngine Dev Team
 * @link		http://codeigniter.com/user_guide/libraries/config.html
 */
class CI_Config {

	protected $config = array();
	protected $is_loaded = array();

	/**
	 * Constructor
	 *
	 * Sets the $config data from the primary config.php file as a class variable
	 *
	 * @param   string	the config file name
	 * @param   boolean  if configuration values should be loaded into their own section
	 * @param   boolean  true if errors should just return false, false if an error message should be displayed
	 * @return  boolean  if the file was successfully loaded or not
	 */
	public function __construct()
	{
		$this->config =& get_config();
		log_message('debug', 'Config Class Initialized');
	}
  	
	// --------------------------------------------------------------------

	/**
	 * Load Config File
	 *
	 * @param	string	the config file name
	 * @return	boolean	if the file was loaded correctly
	 */	
	public function load($file = '', $use_sections = FALSE, $fail_gracefully = FALSE)
	{
		$file = ($file == '') ? 'config' : str_replace('.php', '', $file);
		if (in_array($file, $this->is_loaded, TRUE))
		{
			return TRUE;
		}
		
		if ( ! file_exists(APPPATH."config/{$file}.php"))
		{
			if ($fail_gracefully === TRUE)
			{
				return FALSE;
			}
			
			show_error("The configuration file {$file}.php does not exist.");
		}
		
		include(APPPATH."config/{$file}.php");
		if ( ! isset($config) OR ! is_array($config))
		{
			if ($fail_gracefully === TRUE)
			{
				return FALSE;
			}
			
			show_error("Your {$file}.php file does not appear to contain a valid configuration array.");
		}
		
		if ($use_sections === TRUE)
		{
			if (isset($this->config[$file]))
			{
				$this->config[$file] = array_merge($this->config[$file], $config);
			}
			else
			{
				$this->config[$file] = $config;
			}
		}
		else
		{
			$this->config = array_merge($this->config, $config);
		}
		
		$this->is_loaded[] = $file;
		unset($config);
		
		log_message('debug', "Config file loaded: config/{$file}.php");
		return TRUE;
	}
  	
	// --------------------------------------------------------------------

	/**
	 * Fetch a config file item
	 *
	 *
	 * @param	string	the config item name
	 * @param	string	the index name
	 * @param	bool
	 * @return	string
	 */
	public function item($item, $index = '')
	{	
		if ($index == '')
		{	
			if ( ! isset($this->config[$item]))
			{
				return FALSE;
			}

			$pref = $this->config[$item];
		}
		else
		{
			if ( ! isset($this->config[$index]))
			{
				return FALSE;
			}

			if ( ! isset($this->config[$index][$item]))
			{
				return FALSE;
			}

			$pref = $this->config[$index][$item];
		}

		return $pref;
	}
  	
  	// --------------------------------------------------------------------

	/**
	 * Fetch a config file item - adds slash after item
	 *
	 * The second parameter allows a slash to be added to the end of
	 * the item, in the case of a path.
	 *
	 * @param	string	the config item name
	 * @param	bool
	 * @return	string
	 */
	public function slash_item($item)
	{
		if ( ! isset($this->config[$item]))
		{
			return FALSE;
		}

		$pref = $this->config[$item];

		if ($pref != '' && substr($pref, -1) != '/')
		{	
			$pref .= '/';
		}

		return $pref;
	}
  	
	// --------------------------------------------------------------------

	/**
	 * Site URL
	 *
	 * @param	string	the URI string
	 * @param	bool	whether or not to use the secure URL
	 * @return	string
	 */
	public function site_url($uri = '', $secure = FALSE)
	{
		if (is_array($uri))
		{
			$uri = implode('/', $uri);
		}
		
		$base_url = $secure ? 'secure_base_url' : 'base_url';

		if ($uri == '')
		{
			return $this->slash_item($base_url).$this->item('index_page');
		}
		else
		{
			$suffix = ($this->item('url_suffix') == FALSE) ? '' : $this->item('url_suffix');
			return $this->slash_item($base_url).$this->slash_item('index_page').trim($uri, '/').$suffix; 
		}
	}
	
	// --------------------------------------------------------------------

	/**
	 * Secure Site URL
	 *
	 * @param	string	the URI string
	 * @return	string
	 */
	public function secure_site_url($uri = '')
	{
		return site_url($uri, TRUE);
	}
	
	// --------------------------------------------------------------------

	/**
	 * System URL
	 *
	 * @return	string
	 */
	public function system_url()
	{
		$x = explode('/', preg_replace('%/*(.+?)/*$%', '\1', BASEPATH));
		return $this->slash_item('base_url').end($x).'/';
	}
  	
	// --------------------------------------------------------------------

	/**
	 * Set a config file item
	 *
	 * @param	string	the config item key
	 * @param	string	the config item value
	 * @return	void
	 */
	public function set_item($item, $value)
	{
		$this->config[$item] = $value;
	}
}

/* End of file Config.php */
/* Location: ./system/libraries/Config.php */