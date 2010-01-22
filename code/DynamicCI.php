<?php
/**
 * Contains the DynamicCI class
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
 * Represents an instance which has the same properties as the CI_Base singleton
 *
 * @author	ShiverCube
 */
abstract class DynamicCI {
	
	private static $__CI = NULL;
	private $vars = array(); // Holds the list of instance variables to be used by the magic methods
	
	/**
	 * Initializes the instance
	 */
	public function __construct()
	{
		$this->_initialize();
	}
	
	/**
	 * Called from the constructor. Override to implement initialization behaviour
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
		$this->_init_ci();
		if (isset($this->vars[$name]))
		{
			return $this->vars[$name];
		}
		
		if (isset(self::$__CI->$name))
		{
			return self::$__CI->$name;
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
		$this->_init_ci();
		return isset($this->vars[$name]) ? TRUE : isset(self::$__CI->$name);
	}
	
	/**
	 * The magic unset method. Unsets the given property either for this instance if it exists, or for the CodeIgniter
	 * Controller instance
	 *
	 * @param string $name
	 */
	public function __unset($name)
	{
		$this->_init_ci();
		if (isset($this->vars[$name]))
		{
			unset($this->vars[$name]);
		}
		elseif (isset(self::$__CI->$name))
		{
			unset(self::$__CI->$name);
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
	
	/**
	 * Initializes the CodeIgniter instance
	 */
	private function _init_ci()
	{
		if (self::$__CI == NULL)
		{
			self::$__CI =& get_instance();
		}
	}
}

/* End of file DynamicCI.php */
/* Location: ./system/code/DynamicCI.php */