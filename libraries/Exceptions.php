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
 * Exceptions Class
 *
 * @author		ExpressionEngine Dev Team
 * @link		http://codeigniter.com/user_guide/libraries/exceptions.html
 */
class CI_Exceptions {
	
	private $action;
	private $severity;
	private $message;
	private $filename;
	private $line;
	private $ob_level;

	private $levels = array(
		E_ERROR				=>	'Error',
		E_WARNING			=>	'Warning',
		E_PARSE				=>	'Parsing Error',
		E_NOTICE			=>	'Notice',
		E_CORE_ERROR		=>	'Core Error',
		E_CORE_WARNING		=>	'Core Warning',
		E_COMPILE_ERROR		=>	'Compile Error',
		E_COMPILE_WARNING	=>	'Compile Warning',
		E_USER_ERROR		=>	'User Error',
		E_USER_WARNING		=>	'User Warning',
		E_USER_NOTICE		=>	'User Notice',
		E_STRICT			=>	'Runtime Notice',
		E_RECOVERABLE_ERROR	=>	'Catchable Fatal Error',
		E_DEPRECATED		=>	'Deprecated',
		E_USER_DEPRECATED	=>	'User Deprecated'
	);

	/**
	 * Initializes the object
	 */
	public function __construct()
	{
		$this->ob_level = ob_get_level();
	}
  	
	// --------------------------------------------------------------------

	/**
	 * Exception Logger
	 *
	 * This function logs PHP generated error messages
	 *
	 * @param	string	the error severity
	 * @param	string	the error string
	 * @param	string	the error filepath
	 * @param	string	the error line number
	 * @return	string
	 */
	public function log_exception($severity, $message, $filepath, $line)
	{	
		$severity = ( ! isset($this->levels[$severity])) ? $severity : $this->levels[$severity];
		
		log_message('error', "Severity: {$severity}  --> {$message} {$filepath} {$line}", TRUE);
	}

	// --------------------------------------------------------------------

	/**
	 * 404 Page Not Found Handler
	 *
	 * @param	string
	 * @param	bool
	 * @param	string
	 * @param	string
	 * @return	string
	 */
	public function show_404($page = '', $log = TRUE, $heading = '404 Page Not Found',
		$message = 'The page you requested was not found.')
	{
		if ($log)
		{
			if ($page != '')
			{
				log_message('error', "404 Page Not Found --> {$page}");
			}
			else
			{
				log_message('error', '404 Page Not Found');
			}
		}
		
		echo $this->show_error($heading, $message, 'error_404', 404);
		exit;
	}
  	
	// --------------------------------------------------------------------

	/**
	 * General Error Page
	 *
	 * This function takes an error message as input
	 * (either as a string or an array) and displays
	 * it using the specified template.
	 *
	 * @param	string	the heading
	 * @param	string	the message
	 * @param	string	the template name
	 * @return	string
	 */
	public function show_error($heading, $message, $template = 'error_general', $status_code = 500)
	{
		set_status_header($status_code);
		
		$message = '<p>'.implode('</p><p>', ( ! is_array($message)) ? array($message) : $message).'</p>';

		if (ob_get_level() > $this->ob_level + 1)
		{
			ob_end_flush();	
		}
		ob_start();
		include(APPPATH."errors/{$template}.php");
		$buffer = ob_get_contents();
		ob_end_clean();
		return $buffer;
	}

	// --------------------------------------------------------------------

	/**
	 * Native PHP error handler
	 *
	 * @param	string	the error severity
	 * @param	string	the error string
	 * @param	string	the error filepath
	 * @param	string	the error line number
	 * @return	string
	 */
	public function show_php_error($severity, $message, $filepath, $line)
	{	
		$severity = ( ! isset($this->levels[$severity])) ? $severity : $this->levels[$severity];
	
		$filepath = str_replace('\\', '/', $filepath);
		
		// For safety reasons we do not show the full file path
		if (strpos($filepath, '/') !== FALSE)
		{
			$x = explode('/', $filepath);
			$filepath = $x[count($x)-2].'/'.end($x);
		}
		
		if (ob_get_level() > $this->ob_level + 1)
		{
			ob_end_flush();	
		}
		ob_start();
		include(APPPATH.'errors/error_php.php');
		$buffer = ob_get_contents();
		ob_end_clean();
		echo $buffer;
	}

	/**
	 * Displays a formatted description of an Exception
	 *
	 * @param $type string The type of Exception thrown
	 * @param $message string The Exception message
	 * @param $filepath string The file that caused the Exception
	 * @param $line string The line number of where the Exception occurred
	 */
	public function show_exception($type, $message, $filepath, $line)
	{
		$filepath = str_replace('\\', '/', $filepath);
		
		// For safety reasons we do not show the full file path
		if (strpos($filepath, '/') !== FALSE)
		{
			$x = explode('/', $filepath);
			$filepath = $x[count($x) - 2] . '/' . end($x);
		}
		
		if (ob_get_level() > $this->ob_level + 1)
		{
			ob_end_flush();	
		}
		ob_start();
		include(APPPATH.'errors/error_exception.php');
		$buffer = ob_get_contents();
		ob_end_clean();
		echo $buffer;
	}
}

/* End of file Exceptions.php */
/* Location: ./system/libraries/Exceptions.php */