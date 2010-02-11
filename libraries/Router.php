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
 * Router Class
 *
 * Parses URIs and determines routing
 *
 * @author		ExpressionEngine Dev Team
 * @link		http://codeigniter.com/user_guide/general/routing.html
 */
class CI_Router {

	protected $config;	
	protected $routes = array();
	protected $error_routes = array();
	protected $class = '';
	protected $method = 'index';
	protected $directory = '';
	protected $uri_protocol = 'auto';
	protected $default_controller;
	
	/**
	 * Constructor
	 *
	 * Runs the route mapping function.
	 */
	public function __construct()
	{
		$this->config =& load_class('Config');
		$this->uri =& load_class('URI');
		$this->_set_routing();
		log_message('debug', 'Router Class Initialized');
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Set the route mapping
	 *
	 * This function determines what should be served based on the URI request,
	 * as well as any "routes" that have been set in the routing config file.
	 *
	 * @return	void
	 */
	protected function _set_routing()
	{
		// Load the routes.php file.
		require(APPPATH.'config/routes.php');
		$this->routes = ( ! isset($route) OR ! is_array($route)) ? array() : $route;
		unset($route);

		// Set the default controller so we can display it in the event
		// the URI doesn't correlated to a valid controller.
		$this->default_controller = ( ! isset($this->routes['default_controller']) OR
			$this->routes['default_controller'] == '') ? FALSE : strtolower($this->routes['default_controller']);	
		
		// Fetch the complete URI string
		$this->uri->_fetch_uri_string();
	
		// Is there a URI string? If not, the default controller specified in the "routes" file will be shown.
		if ($this->uri->uri_string == '')
		{
			if ($this->default_controller === FALSE)
			{
				show_error('Unable to determine what should be displayed. A default route has not been specified in the routing file.');
			}
			
			if (strpos($this->default_controller, '/') !== FALSE)
			{
				$x = explode('/', $this->default_controller);

				$this->set_class(end($x));
				$this->set_method('index');
				$this->_set_request($x);
			}
			else
			{
				$this->set_class($this->default_controller);
				$this->set_method('index');
				$this->_set_request(array($this->default_controller, 'index'));
			}

			// re-index the routed segments array so it starts with 1 rather than 0
			$this->uri->_reindex_segments();
			
			log_message('debug', 'No URI present. Default controller set.');
			return;
		}
		unset($this->routes['default_controller']);
		
		// Do we need to remove the URL suffix?
		$this->uri->_remove_url_suffix();
		
		// Compile the segments into an array
		$this->uri->_explode_segments();
		
		// Parse any custom routing that may exist
		$this->_parse_routes();		
		
		// Re-index the segment array so that it starts with 1 rather than 0
		$this->uri->_reindex_segments();
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Set the Route
	 *
	 * This function takes an array of URI segments as
	 * input, and sets the current class/method
	 *
	 * @param	array
	 * @param	bool
	 * @return	void
	 */
	protected function _set_request($segments = array())
	{
		$segments = $this->_validate_request($segments);
		
		if (count($segments) == 0)
		{
			return;
		}
		
		$this->set_class($segments[0]);
		
		if (isset($segments[1]))
		{
			// A standard method request
			$this->set_method($segments[1]);
		}
		else
		{
			// This lets the "routed" segment array identify that the default
			// index method is being used.
			$segments[1] = 'index';
		}
		
		// Update our "routed" segment array to contain the segments.
		// Note: If there is no custom routing, this array will be
		// identical to $this->uri->segments
		$this->uri->rsegments = $segments;
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Validates the supplied segments.  Attempts to determine the path to
	 * the controller.
	 *
	 * @param	array
	 * @return	array
	 */	
	protected function _validate_request($segments)
	{
		// Does the requested controller exist in the root folder?
		if (file_exists(APPPATH."controllers/{$segments[0]}.php"))
		{
			return $segments;
		}

		// Is the controller in a sub-folder?
		if (is_dir(APPPATH."controllers/{$segments[0]}"))
		{		
			// Set the directory and remove it from the segment array
			$this->set_directory($segments[0]);
			$segments = array_slice($segments, 1);
			
			if (count($segments) > 0)
			{
				// Does the requested controller exist in the sub-folder?
				if ( ! file_exists(APPPATH.'controllers/'.$this->fetch_directory()."{$segments[0]}.php"))
				{
					show_404($this->fetch_directory().$segments[0], $this);
					return;
				}
			}
			else
			{
				$this->set_class($this->default_controller);
				$this->set_method('index');
			
				// Does the default controller exist in the sub-folder?
				if ( ! file_exists(APPPATH.'controllers/'.$this->fetch_directory()."{$this->default_controller}.php"))
				{
					$this->directory = '';
					return array();
				}
			}

			return $segments;
		}

		// Can't find the requested controller...
		show_404($segments[0], $this);
	}

	// --------------------------------------------------------------------

	/**
	 *  Parse Routes
	 *
	 * This function matches any routes that may exist in
	 * the config/routes.php file against the URI to
	 * determine if the class/method need to be remapped.
	 *
	 * @return	void
	 */
	protected function _parse_routes()
	{
		// Do we even have any custom routing to deal with?
		if (count($this->routes) == 1)
		{
			$this->_set_request($this->uri->segments);
			return;
		}

		// Turn the segment array into a URI string
		$uri = implode('/', $this->uri->segments);

		// Is there a literal match?  If so we're done
		if (isset($this->routes[$uri]))
		{
			$this->_set_request(explode('/', $this->routes[$uri]));		
			return;
		}
		
		// Loop through the route array looking for wild-cards
		foreach ($this->routes as $key => $val)
		{
			// Convert wild-cards to RegEx
			$key = str_replace(':any', '.+', str_replace(':num', '[0-9]+', $key));
			
			// Does the RegEx match?
			$regex = "#^{$key}$#";
			if (preg_match($regex, $uri))
			{
				// Do we have a back-reference?
				if (strpos($val, '$') !== FALSE AND strpos($key, '(') !== FALSE)
				{
					$val = preg_replace($regex, $val, $uri);
				}
				
				$this->_set_request(explode('/', $val));		
				return;
			}
		}
		
		// If we got this far it means we didn't encounter a
		// matching route so we'll set the site default route
		$this->_set_request($this->uri->segments);
	}

	// --------------------------------------------------------------------
	
	/**
	 * Set the class name
	 *
	 * @param	string
	 * @return	void
	 */	
	public function set_class($class)
	{
		$this->class = $class;
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Fetch the current class
	 *
	 * @return	string
	 */	
	public function fetch_class()
	{
		return $this->class;
	}
	
	// --------------------------------------------------------------------
	
	/**
	 *  Set the method name
	 *
	 * @param	string
	 * @return	void
	 */	
	public function set_method($method)
	{
		$this->method = $method;
	}

	// --------------------------------------------------------------------
	
	/**
	 *  Fetch the current method
	 *
	 * @return	string
	 */	
	public function fetch_method()
	{
		if ($this->method == $this->fetch_class())
		{
			return 'index';
		}

		return $this->method;
	}

	// --------------------------------------------------------------------
	
	/**
	 *  Set the directory name
	 *
	 * @param	string
	 * @return	void
	 */	
	public function set_directory($dir)
	{
		$this->directory = $dir == '' ? '' : $dir.'/';
	}

	// --------------------------------------------------------------------
	
	/**
	 *  Fetch the sub-directory (if any) that contains the requested controller class
	 *
	 * @return	string
	 */	
	public function fetch_directory()
	{
		return $this->directory;
	}
	
	// --------------------------------------------------------------------
	
	/**
	 *  Gets the filename of the Controller to load
	 *
	 * @return	string
	 */
	public function get_controller()
	{
		return APPPATH.'controllers/'.$this->fetch_directory().$this->fetch_class().'.php';
	}
}

/* End of file Router.php */
/* Location: ./system/libraries/Router.php */