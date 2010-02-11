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
 * System Front Controller
 *
 * Loads the base classes and executes the request.
 *
 * @author		ExpressionEngine Dev Team
 * @link		http://codeigniter.com/user_guide/
 */

// CI Version
define('CI_VERSION',	'1.7.2');

if ( ! defined('HANDLE_E_STRICT'))
{
	define('HANDLE_E_STRICT', FALSE);
}

// Set the default content type
header('Content-Type: text/html;charset=utf-8');

/*
 * ------------------------------------------------------
 *  Load the global functions
 * ------------------------------------------------------
 */
require(BASEPATH.'codeigniter/Common.php');

/*
 * ------------------------------------------------------
 *  Load the framework constants
 * ------------------------------------------------------
 */
require(APPPATH.'config/constants.php');

/*
 * ------------------------------------------------------
 *  Define a custom error handler so we can log PHP errors
 * ------------------------------------------------------
 */
set_error_handler('_error_handler');

/*
 * ------------------------------------------------------
 *  Define a custom exception handler so we can log and handle them accordingly
 * ------------------------------------------------------
 */
set_exception_handler('_exception_handler');

/*
 * ------------------------------------------------------
 *  Register the __autoload handler before any other classes are initialized
 * ------------------------------------------------------
 */
spl_autoload_register('_autoload');

/*
 * ------------------------------------------------------
 *  Start the timer... tick tock tick tock...
 * ------------------------------------------------------
 */

$BM =& load_class('Benchmark');
$BM->mark('total_execution_time_start');
$BM->mark('loading_time_base_classes_start');

/*
 * ------------------------------------------------------
 *  Instantiate the hooks class
 * ------------------------------------------------------
 */

$EXT =& load_class('Hooks');

/*
 * ------------------------------------------------------
 *  Is there a "pre_system" hook?
 * ------------------------------------------------------
 */
$EXT->_call_hook('pre_system');

/*
 * ------------------------------------------------------
 *  Instantiate the base classes
 * ------------------------------------------------------
 */

$CFG =& load_class('Config');
$URI =& load_class('URI');
$RTR =& load_class('Router');
$OUT =& load_class('Output');

/*
 * ------------------------------------------------------
 *	Is there a valid cache file?  If so, we're done...
 * ------------------------------------------------------
 */

if ($EXT->_call_hook('cache_override') === FALSE)
{
	if ($OUT->_display_cache($CFG, $URI) == TRUE)
	{
		/*
		 * ------------------------------------------------------
		 *  Close the DB connection if one exists
		 * ------------------------------------------------------
		 */
		if (class_exists('CI_DB') AND isset($CI->db))
		{
			$CI->db->close();
		}
		
		exit;
	}
}

/*
 * ------------------------------------------------------
 *  Load the remaining base classes
 * ------------------------------------------------------
 */

require(BASEPATH.'codeigniter/utf8.php');
$IN		=& load_class('Input');
$LANG	=& load_class('Language');

/*
 * ------------------------------------------------------
 *  Load the app controller and local controller
 * ------------------------------------------------------
 */
require(BASEPATH.'codeigniter/Base.php');

// Load the base controller class
load_class('Controller', FALSE);

$default_controller = $RTR->get_controller();

// Load the local application controller
// Note: The Router class automatically validates the controller path.  If this include fails it 
// means that the default controller in the Routes.php file is not resolving to something valid.
if ( ! file_exists($default_controller))
{
	show_error('Unable to load your default controller.  Please make sure the controller specified in your Routes.php file is valid.');
}

include($default_controller);

// Set a mark point for benchmarking
$BM->mark('loading_time_base_classes_end');


/*
 * ------------------------------------------------------
 *  Security check
 * ------------------------------------------------------
 *
 *  None of the functions in the app controller or the
 *  loader class can be called via the URI, nor can
 *  controller functions that begin with an underscore
 */
$class  = $RTR->fetch_class();
$method = $RTR->fetch_method();

if ( ! class_exists($class)
	OR $method == 'controller'
	OR strncmp($method, '_', 1) == 0
	OR in_array(strtolower($method), array_map('strtolower', get_class_methods('Controller')))
	)
{
	show_404("{$class}/{$method}");
}

/*
 * ------------------------------------------------------
 *  Is there a "pre_controller" hook?
 * ------------------------------------------------------
 */
$EXT->_call_hook('pre_controller');

/*
 * ------------------------------------------------------
 *  Instantiate the controller and call requested method
 * ------------------------------------------------------
 */

// Mark a start point so we can benchmark the controller
$BM->mark("controller_execution_time_( {$class} / {$method} )_start");

$CI = new $class();

/*
 * ------------------------------------------------------
 *  Is there a "post_controller_constructor" hook?
 * ------------------------------------------------------
 */
$EXT->_call_hook('post_controller_constructor');

// Is there a "remap" function?
if (method_exists($CI, '_remap'))
{
	$CI->_remap($method);
}
else
{
	// is_callable() returns TRUE on some versions of PHP 5 for private and protected
	// methods, so we'll use this workaround for consistent behavior
	if ( ! in_array(strtolower($method), array_map('strtolower', get_class_methods($CI))))
	{
		show_404("{$class}/{$method}");
	}

	// Call the requested method.
	// Any URI segments present (besides the class/function) will be passed to the method for convenience
	call_user_func_array(array(&$CI, $method), array_slice($URI->rsegments, 2));
}

// Mark a benchmark end point
$BM->mark("controller_execution_time_( {$class} / {$method} )_end");

/*
 * ------------------------------------------------------
 *  Is there a "post_controller" hook?
 * ------------------------------------------------------
 */
$EXT->_call_hook('post_controller');

/*
 * ------------------------------------------------------
 *  Send the final rendered output to the browser
 * ------------------------------------------------------
 */

if ($EXT->_call_hook('display_override') === FALSE)
{
	$OUT->_display();
}

/*
 * ------------------------------------------------------
 *  Is there a "post_system" hook?
 * ------------------------------------------------------
 */
$EXT->_call_hook('post_system');

/*
 * ------------------------------------------------------
 *  Close the DB connection if one exists
 * ------------------------------------------------------
 */
if (class_exists('CI_DB') AND isset($CI->db))
{
	$CI->db->close();
}

/* End of file CodeIgniter.php */
/* Location: ./system/codeigniter/CodeIgniter.php */