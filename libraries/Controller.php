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
 * CodeIgniter Application Controller Class
 *
 * This class object is the super class that every library in
 * CodeIgniter will be assigned to.
 *
 * @author		ExpressionEngine Dev Team
 * @link		http://codeigniter.com/user_guide/general/controllers.html
 */
abstract class Controller extends CI_Base {
	
	private static $autoload_classes = array(
		'config'	=> 'Config',
		'benchmark'	=> 'Benchmark',
		'uri'		=> 'URI',
		'output'	=> 'Output',
		'lang'		=> 'Language',
		'loader'	=> 'Loader'
	);
	
	/**
	 * Constructor
	 *
	 * Calls the initialize() function
	 */
	public function __construct()
	{
		parent::__construct();
		$this->_ci_initialize();
		log_message('debug', 'Controller Class Initialized');
	}

	// --------------------------------------------------------------------

	/**
	 * Initialize
	 *
	 * Assigns all the bases classes loaded by the front controller to
	 * variables in this class.  Also calls the autoload routine.
	 *
	 * @return	void
	 */
	protected function _ci_initialize()
	{
		foreach (self::$autoload_classes as $var => $class)
		{
			$this->$var =& load_class($class);
		}
		
		// Only load Input class if this is the full blown CodeIgniter system, or if specified by MiniCI
		if ( ! defined('LOAD_INPUT_CLASS') || LOAD_INPUT_CLASS)
		{
			$this->input =& load_class('Input');
		}
		
		// Only load Router class if this is the full blown CodeIgniter system
		if ( ! defined('MINI_CI'))
		{
			$this->router =& load_class('Router');
		}

		$this->load =& load_class('Loader');
		$this->load->_ci_autoloader();
	}
}

/* End of file Controller.php */
/* Location: ./system/libraries/Controller.php */