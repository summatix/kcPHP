<?php
/**
 * Contains the DynamicLibrary class
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
 * An implementation of both DynamicCI and Library in one class. Represents a base Library class which loads
 * configuration settings on startup, and also has the same properties as the singleton CI_Base instance
 *
 * @author	ShiverCube
 */
abstract class DynamicLibrary {
	
	private $vars = array(); // Holds the list of instance variables to be used by the magic methods
	private $__CI;
	
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
		$this->__CI =& get_instance();
		
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
				$this->$key = $this->config->item($this->config_prefix.$key);
				if (empty($this->$key))
				{
					$this->$key = $value;
				}
			}
		}
		
		if (is_string($this->helpers) || is_array($this->helpers))
		{
			$this->load->helpers($this->helpers);
		}
		
		$this->_initialize();
	}
	
	/**
	 * Called from within the constructor. Override to initialize the instance
	 */
	protected function _initialize()
	{
	}
	
	/**
	 * The magic property setter method. Adds all instance level properties to the vars array
	 *
	 * @param string $name
	 * @param mixed $value
	 */
	public function __set($name, $value)
	{
		$this->vars[$name] = $value;
	}
	
	/**
	 * The magic property getter method. When requested, returns an instance level property if it exists, otherwise it
	 * returns a property from the real CodeIgniter Controller instance
	 *
	 * @param string $name
	 */
	public function __get($name)
	{
		// Return the instance level property if it exists
		if (isset($this->vars[$name]))
		{
			return $this->vars[$name];
		}
		
		// Return the CodeIgniter Controller property if it exists
		if (isset($this->__CI->$name))
		{
			return $this->__CI->$name;
		}
		
		$this->_property_inexistent_error($name);
	}
	
	/**
	 * The magic isset method. Returns whether the given property exists either for this instance, or whether the
	 * property exists for the CodeIgniter Controller instance
	 *
	 * @param string $name
	 * @return bool
	 */
	public function __isset($name)
	{
		return isset($this->vars[$name]) ? TRUE : isset($this->__CI->$name);
	}
	
	/**
	 * The magic unset method. Unsets the given property either for this instance if it exists, or for the CodeIgniter
	 * Controller instance
	 *
	 * @param string $name
	 */
	public function __unset($name)
	{
		if (isset($this->vars[$name]))
		{
			unset($this->vars[$name]);
		}
		elseif (isset($this->__CI->$name))
		{
			unset($this->__CI->$name);
		}
		else
		{
			$this->_property_inexistent_error($name);
		}
	}
	
	/**
	 * Called from within a magic method. Displays an error if a property does not exist
	 *
	 * @param string $name The name of the property that does not exist
	 */
	private function _property_inexistent_error($name)
	{
		$back = debug_backtrace();
		$file = ( ! isset($back[1]['file'])) ? '' : $back[1]['file'];
		$line = ( ! isset($back[1]['line'])) ? '' : $back[1]['line'];
		
		$error =& load_class('Exceptions');
		$error->show_php_error(E_NOTICE, "Undefined property: {$name}", $file, $line);
	}
}

/* End of file Library.php */
/* Location: ./system/code/Library.php */