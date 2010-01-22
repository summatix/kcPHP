<?php
/**
 * Contains the Library class
 *
 * @package		kcPHP
 * @subpackage	code
 * @author		ShiverCube
 * @copyright	Copyright (c) 2008 - 2010, EllisLab, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 * @since		Version 1.0
 * @filesource
 */

/**
 * Represents a base Library class which loads configuration settings on startup
 *
 * @author	ShiverCube
 */
abstract class Library {
	
	/**
	 * The reference to the CodeIgniter object
	 *
	 * @type object
	 */
	protected $CI;
	
	/**
	 * The config file to load, or FALSE not to load a config file
	 *
	 * @type string or FALSE
	 */
	protected $config_file = FALSE;
	
	/**
	 * The prefix for the settings in the config file
	 *
	 * @type string
	 */
	protected $config_prefix = 'config_';
	
	/**
	 * The array of settings to read in from either the parameters or from the config. All keys in this array will be
	 * set as properties for the instance
	 *
	 * @type array
	 */
	protected $settings = array(/*
		// Examples:
		'path' => 'default/path',
		'table_name' => 'library_table'
	*/);
	
	/**
	 * The helper or list of helpers to load upon instantiation
	 *
	 * @type string or array of string
	 */
	protected $helpers = FALSE;
	
	/**
	 * Initializes the instance and loads the settings
	 *
	 * @param array $params (optional) The arguments to pass to the library
	 */
	public function __construct($params = array())
	{
		$this->CI =& get_instance();
		
		if ($this->config_file !== FALSE)
		{
			$this->config->load($this->config_file, FALSE, TRUE);
		}
		
		foreach ($this->settings as $key => $value)
		{
			if (isset($params[$key]))
			{
				$this->$key = $params[$key];
			}
			else
			{
				$this->$key = $this->CI->config->item($this->config_prefix.$key);
				if (empty($this->$key))
				{
					$this->$key = $value;
				}
			}
		}
		
		if (is_string($this->helpers) || is_array($this->helpers))
		{
			$this->CI->load->helpers($this->helpers);
		}
		
		$this->_initialize();
	}
	
	/**
	 * Called from within the constructor. Override to initialize the instance
	 */
	protected function _initialize()
	{
	}
}

/* End of file Library.php */
/* Location: ./system/code/Library.php */