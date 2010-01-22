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
 * Tests for file writability
 *
 * is_writable() returns TRUE on Windows servers when you really can't write to 
 * the file, based on the read-only attribute.  is_writable() is also unreliable
 * on Unix servers if safe_mode is on. 
 *
 * @access	private
 * @return	void
 */
function is_really_writable($file)
{	
	// If we're on a Unix server with safe_mode off we call is_writable
	if (DIRECTORY_SEPARATOR == '/' AND ! ini_get('safe_mode'))
	{
		return is_writable($file);
	}

	// For windows servers and safe_mode "on" installations we'll actually
	// write a file then read it.  Bah...
	if (is_dir($file))
	{
		$file = rtrim($file, '/').'/'.md5(rand(1, 100));

		if (($fp = @fopen($file, FOPEN_WRITE_CREATE)) === FALSE)
		{
			return FALSE;
		}

		fclose($fp);
		@chmod($file, DIR_WRITE_MODE);
		@unlink($file);
		return TRUE;
	}
	else if (($fp = @fopen($file, FOPEN_WRITE_CREATE)) === FALSE)
	{
		return FALSE;
	}

	fclose($fp);
	return TRUE;
}

// ------------------------------------------------------------------------

/**
* Class registry
*
* This function acts as a singleton.  If the requested class does not
* exist it is instantiated and set to a static variable.  If it has
* previously been instantiated the variable is returned.
*
* @access	public
* @param	string	the class name being requested
* @param	bool	optional flag that lets classes get loaded but not instantiated
* @return	object
*/
function &load_class($class, $instantiate = TRUE)
{
	static $objects = array();

	// Does the class exist?  If so, we're done...
	if (isset($objects[$class]))
	{
		return $objects[$class];
	}

	$subclass_prefix = config_item('subclass_prefix');
	$subclass = APPPATH."libraries/{$subclass_prefix}{$class}.php";
	$baseclass = BASEPATH."libraries/{$class}.php";
	
	// If the requested class does not exist in the application/libraries
	// folder we'll load the native class from the system/libraries folder.	
	if (file_exists($subclass))
	{
		require($baseclass);
		require($subclass);
		$is_subclass = TRUE;
	}
	else
	{
		$overwrite_class = APPPATH."libraries/{$class}.php";
		if (file_exists($overwrite_class))
		{
			require($overwrite_class);
		}
		else
		{
			require($baseclass);
		}
		
		$is_subclass = FALSE;
	}

	if ( ! $instantiate)
	{
		$objects[$class] = TRUE;
		return $objects[$class];
	}

	if ($is_subclass == TRUE)
	{
		$name = $subclass_prefix.$class;

		$objects[$class] =& instantiate_class(new $name());
		return $objects[$class];
	}

	$name = ($class != 'Controller') ? 'CI_'.$class : $class;

	$objects[$class] =& instantiate_class(new $name());
	return $objects[$class];
}

/**
 * Instantiate Class
 *
 * Returns a new class object by reference, used by load_class() and the DB class.
 * Required to not make PHP 5.3 cry.
 *
 * Use: $obj =& instantiate_class(new Foo());
 * 
 * @access	public
 * @param	object
 * @return	object
 */
function &instantiate_class(&$class_object)
{
	return $class_object;
}

/**
* Loads the main config.php file
*
* @access	private
* @return	array
*/
function &get_config()
{
	static $main_conf;

	if ( ! isset($main_conf))
	{
		require(APPPATH.'config/config.php');
		require(APPPATH.'config/common.php');

		if ( ! isset($config) OR ! is_array($config))
		{
			exit('Your config file does not appear to be formatted correctly.');
		}

		$main_conf[0] =& $config;
	}
	
	return $main_conf[0];
}

/**
* Gets a config item
*
* @access	public
* @return	mixed
*/
function config_item($item)
{
	static $config_item = array();

	if ( ! isset($config_item[$item]))
	{
		$config =& get_config();

		if ( ! isset($config[$item]))
		{
			return FALSE;
		}
		
		$config_item[$item] = $config[$item];
	}

	return $config_item[$item];
}


/**
* Error Handler
*
* This function lets us invoke the exception class and
* display errors using the standard error template located
* in application/errors/errors.php
* This function will send the error page directly to the
* browser and exit.
*
* @access	public
* @return	void
*/
function show_error($message, $status_code = 500)
{
	$error =& load_class('Exceptions');
	echo $error->show_error('An Error Was Encountered', $message, 'error_general', $status_code);
	exit;
}


/**
* 404 Page Handler
*
* This function is similar to the show_error() function above
* However, instead of the standard error template it displays
* 404 errors.
*
* @access	public
* @return	void
*/
function show_404($page = '', $log = TRUE, $heading = '404 Page Not Found',
		$message = 'The page you requested was not found.')
{
	// Check to see if the Controller is already loaded
	if (function_exists('get_instance') && (($CI =& get_instance()) != NULL))
	{
		$error_method_404 = config_item('error_method_404');
		
		// If the default error method is not set, or the method does not exist, show the default 404 error page
		if (empty($error_method_404) || ! method_exists($CI, $error_method_404))
		{
			_show_404($page, $log, $heading, $message);
		}
		
		// Otherwise call the error handler method
		else
		{
			$CI->$error_method_404();
		}
	}
	
	// If the Controller is not loaded, either load the set error handler controller, or show the default 404 error
	// page
	else
	{
		$error_controller = config_item('error_controller');
		
		// Show the default 404 error page if no error controller is set
		if (empty($error_controller))
		{
			_show_404($page, $log, $heading, $message);
		}
		
		// If the error controller is set, load it
		else
		{
			// We need to some how obtain a reference to the Router class so we can set the appropriate properties and
			// continue execution as normal
			
			// First we try and see if the Router class passed itself as an argument
			if (is_object($log))
			{
				$RTR =& $log;
			}
			// Otherwise we should be able to use the global
			else
			{
				global $RTR;
			}
			
			// Set the appropriate properties to the error handler so that execution can continue as normal
			$error_method_404 = config_item('error_method_404');
			$RTR->set_directory('');
			$RTR->set_class($error_controller);
			$RTR->set_method(empty($error_method_404) ? 'index' : $error_method_404);
		}
	}
}

/**
 * Shows the 404 error page
 *
 * @access private
 */
function _show_404($page, $log, $heading, $message)
{
	$error =& load_class('Exceptions');
	$error->show_404($page, $log, $heading, $message);
	exit;
}


/**
* Error Logging Interface
*
* We use this as a simple mechanism to access the logging
* class and send messages to be logged.
*
* @access	public
* @return	void
*/
function log_message($level = 'error', $message, $php_error = FALSE)
{
	$config =& get_config();
	if ($config['log_threshold'] == 0)
	{
		return;
	}
	
	$LOG =& load_class('Log');
	$LOG->write_log($level, $message, $php_error);
}


/**
 * Set HTTP Status Header
 *
 * @access	public
 * @param	int 	the status code
 * @param	string	
 * @return	void
 */
function set_status_header($code = 200, $text = '')
{
	$stati = array(
		200	=> 'OK',
		201	=> 'Created',
		202	=> 'Accepted',
		203	=> 'Non-Authoritative Information',
		204	=> 'No Content',
		205	=> 'Reset Content',
		206	=> 'Partial Content',

		300	=> 'Multiple Choices',
		301	=> 'Moved Permanently',
		302	=> 'Found',
		304	=> 'Not Modified',
		305	=> 'Use Proxy',
		307	=> 'Temporary Redirect',

		400	=> 'Bad Request',
		401	=> 'Unauthorized',
		403	=> 'Forbidden',
		404	=> 'Not Found',
		405	=> 'Method Not Allowed',
		406	=> 'Not Acceptable',
		407	=> 'Proxy Authentication Required',
		408	=> 'Request Timeout',
		409	=> 'Conflict',
		410	=> 'Gone',
		411	=> 'Length Required',
		412	=> 'Precondition Failed',
		413	=> 'Request Entity Too Large',
		414	=> 'Request-URI Too Long',
		415	=> 'Unsupported Media Type',
		416	=> 'Requested Range Not Satisfiable',
		417	=> 'Expectation Failed',

		500	=> 'Internal Server Error',
		501	=> 'Not Implemented',
		502	=> 'Bad Gateway',
		503	=> 'Service Unavailable',
		504	=> 'Gateway Timeout',
		505	=> 'HTTP Version Not Supported'
	);

	if ($code == '' OR ! is_numeric($code))
	{
		show_error('Status codes must be numeric', 500);
	}

	if (isset($stati[$code]) AND $text == '')
	{				
		$text = $stati[$code];
	}
	
	if ($text == '')
	{
		show_error('No status text available.  Please check your status code number or supply your own message text.', 500);
	}
	
	$server_protocol = (isset($_SERVER['SERVER_PROTOCOL'])) ? $_SERVER['SERVER_PROTOCOL'] : FALSE;

	if (substr(php_sapi_name(), 0, 3) == 'cgi')
	{
		header("Status: {$code} {$text}", TRUE);
	}
	elseif ($server_protocol == 'HTTP/1.1' OR $server_protocol == 'HTTP/1.0')
	{
		header("{$server_protocol} {$code} {$text}", TRUE, $code);
	}
	else
	{
		header("HTTP/1.1 {$code} {$text}", TRUE, $code);
	}
}


/**
* Error Handler
*
* This is the custom error handler that is declaired at the top
* of Codeigniter.php.  The main reason we use this is permit
* PHP errors to be logged in our own log files since we may
* not have access to server logs. Since this function
* effectively intercepts PHP errors, however, we also need
* to display errors based on the current error_reporting level.
* We do that with the use of a PHP error template.
*
* @access	private
* @return	void
*/
function _error_handler($severity, $message, $filepath, $line)
{
	$error =& load_class('Exceptions');
	$config =& get_config();
	
	if ($severity == E_STRICT && ! HANDLE_E_STRICT)
	{
		return;
	}

	// Should we display the error?
	// We'll get the current error_reporting level and add its bits
	// with the severity bits to find out.
	if (($severity & error_reporting()) == $severity)
	{
		$error->show_php_error($severity, $message, $filepath, $line);
	}
	
	// Should we log the error?  No?  We're done...
	if ($config['log_threshold'] == 0)
	{
		return;
	}

	$error->log_exception($severity, $message, $filepath, $line);
}

/**
 * Handles the given Exception by displaying the error and logging the message
 *
 * @access private
 * @param $exception Exception The Exception which was thrown
 */
function _exception_handler($exception)
{
	$error =& load_class('Exceptions');
	
	$type = get_class($exception);
	$message = $exception->getMessage();
	$file = $exception->getFile();
	$line = $exception->getLine();
	
	$error->show_exception($type, $message, $file, $line);
	
	log_message('error', "Severity: Error  --> {$message} {$file} {$line}", TRUE);
}

/**
 * __autoload handler to load classes from the "code" directories or namespaced folders
 *
 * @access private
 * @param string
 */
function _autoload($class)
{
	if (preg_match('/^([_a-z0-9]+\\\\)+[_a-z0-9]+$/im', $class))
	{
		$file = APPPATH.str_replace('\\', '/', $class).'.php';
		if (file_exists($file))
		{
			require $file;
		}
	}
	else
	{
		if (file_exists(APPPATH."code/{$class}.php"))
		{
			require APPPATH."code/{$class}.php";
		}
		elseif (file_exists(BASEPATH."code/{$class}.php"))
		{
			require BASEPATH."code/{$class}.php";
		}
	}
}

/* End of file Common.php */
/* Location: ./system/codeigniter/Common.php */