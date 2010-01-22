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
 * CodeIgniter Hooks Class
 *
 * Provides a mechanism to extend the base system without hacking.  Most of
 * this class is borrowed from Paul's Extension class in ExpressionEngine.
 *
 * @author		ExpressionEngine Dev Team
 * @link		http://codeigniter.com/user_guide/libraries/encryption.html
 */
class CI_Hooks {

	private $enabled 		= FALSE;
	private $hooks   		= array();
	private $in_progress	= FALSE;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->_initialize();
		log_message('debug', 'Hooks Class Initialized');
	}
  
	// --------------------------------------------------------------------

	/**
	 * Initialize the Hooks Preferences
	 *
	 * @return	void
	 */  
  	protected function _initialize()
  	{
		$CFG =& load_class('Config');

		// If hooks are not enabled in the config file
		// there is nothing else to do

		if ($CFG->item('enable_hooks') == FALSE)
		{
			return;
		}

		// Grab the "hooks" definition file.
		// If there are no hooks, we're done.
		$file = APPPATH.'config/hooks.php';
		if (file_exists($file))
		{
			include($file);
		}

		if ( ! isset($hook) OR ! is_array($hook))
		{
			return;
		}

		$this->hooks =& $hook;
		$this->enabled = TRUE;
  	}
  
	// --------------------------------------------------------------------

	/**
	 * Call Hook
	 *
	 * Calls a particular hook
	 *
	 * @param	string	the hook name
	 * @return	mixed
	 */
	public function _call_hook($which = '')
	{
		if ( ! $this->enabled OR ! isset($this->hooks[$which]))
		{
			return FALSE;
		}

		if (isset($this->hooks[$which][0]) AND is_array($this->hooks[$which][0]))
		{
			foreach ($this->hooks[$which] as $val)
			{
				$this->_run_hook($val);
			}
		}
		else
		{
			$this->_run_hook($this->hooks[$which]);
		}

		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Run Hook
	 *
	 * Runs a particular hook
	 *
	 * @param	array	the hook details
	 * @return	bool
	 */
	protected function _run_hook($data)
	{
		if ( ! is_array($data))
		{
			return FALSE;
		}

		// -----------------------------------
		// Safety - Prevents run-away loops
		// -----------------------------------

		// If the script being called happens to have the same
		// hook call within it a loop can happen

		if ($this->in_progress == TRUE)
		{
			return;
		}

		// -----------------------------------
		// Set file path
		// -----------------------------------

		if ( ! isset($data['filepath']) OR ! isset($data['filename']))
		{
			return FALSE;
		}

		$filepath = APPPATH."{$data['filepath']}/{$data['filename']}";

		if ( ! file_exists($filepath))
		{
			return FALSE;
		}

		// -----------------------------------
		// Set class/function name
		// -----------------------------------

		$class		= FALSE;
		$function	= FALSE;
		$params		= '';

		if (isset($data['class']) AND $data['class'] != '')
		{
			$class = $data['class'];
		}

		if (isset($data['function']))
		{
			$function = $data['function'];
		}

		if (isset($data['params']))
		{
			$params = $data['params'];
		}

		if ($class === FALSE AND $function === FALSE)
		{
			return FALSE;
		}

		// -----------------------------------
		// Set the in_progress flag
		// -----------------------------------

		$this->in_progress = TRUE;

		// -----------------------------------
		// Call the requested class and/or function
		// -----------------------------------

		if ($class !== FALSE)
		{
			if ( ! class_exists($class))
			{
				require($filepath);
			}

			$HOOK = new $class;
			$HOOK->$function($params);
		}
		else
		{
			if ( ! function_exists($function))
			{
				require($filepath);
			}

			$function($params);
		}

		$this->in_progress = FALSE;
		return TRUE;
	}
}

/* End of file Hooks.php */
/* Location: ./system/libraries/Hooks.php */