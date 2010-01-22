<?php

/*
 * ------------------------------------------------------
 *  Define constants
 * ------------------------------------------------------
 */
if ( ! defined('HANDLE_ERRORS'))
{
	define('HANDLE_ERRORS', TRUE);
}

if ( ! defined('HANDLE_E_STRICT'))
{
	define('HANDLE_E_STRICT', FALSE);
}

if ( ! defined('LOAD_INPUT_CLASS'))
{
	define('LOAD_INPUT_CLASS', TRUE);
}

define('MINI_CI', TRUE);
define('FCPATH', __FILE__);
define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));

// ------------------------------------------------------------------------

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
if (HANDLE_ERRORS)
{
	set_error_handler('_error_handler');
	set_exception_handler('_error_handler');
}

/*
 * ------------------------------------------------------
 *  Instantiate the benchmark class so that the output class will function
 * ------------------------------------------------------
 */
$BM =& load_class('Benchmark');
$BM->mark('total_execution_time_start');

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
$OUT =& load_class('Output');

if (LOAD_INPUT_CLASS)
{
	$IN =& load_class('Input');
}

$LANG =& load_class('Language');

/*
 * ------------------------------------------------------
 *  Load the app controller and local controller
 * ------------------------------------------------------
 */
require(BASEPATH.'codeigniter/Base.php');

/*
 * ------------------------------------------------------
 *  Instantiate a fake controller
 * ------------------------------------------------------
 */
$CI = load_class('Controller', TRUE);

/*
 * ------------------------------------------------------
 *  Is there a "pre_controller" hook?
 * ------------------------------------------------------
 */
$EXT->_call_hook('pre_controller');

/*
 * ------------------------------------------------------
 *  Is there a "post_controller_constructor" hook?
 * ------------------------------------------------------
 */
$EXT->_call_hook('post_controller_constructor');

/*
 * ------------------------------------------------------
 *  All code goes under here
 * ------------------------------------------------------
 */
