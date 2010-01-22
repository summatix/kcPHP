<?php  // Keep spaces here as some poorly written CI extensions require 7 characters
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
 * Loader Class
 *
 * Loads views and files
 *
 * @category	Loader
 * @link		http://codeigniter.com/user_guide/libraries/loader.html
 */
class CI_Loader {
	
	// All these are set automatically. Don't mess with them.
	protected $_ci_ob_level;
	protected $_ci_view_path = '';
	protected $_ci_cached_vars = array();
	public $_ci_classes = array();
	protected $_ci_loaded_files = array();
	protected $_ci_models = array();
	protected $_ci_helpers = array();
	protected $_ci_plugins = array();
	protected $_ci_varmap = array('unit_test' => 'unit', 'user_agent' => 'agent');
	
	protected $CI;

	/**
	 * Constructor
	 *
	 * Sets the path to the view files and gets the initial output buffering level
	 */
	public function __construct()
	{
		$this->_ci_view_path = APPPATH.'views/';
		$this->_ci_ob_level  = ob_get_level();
		$this->CI =& get_instance();

		log_message('debug', 'Loader Class Initialized');
	}

	// --------------------------------------------------------------------

	/**
	 * Class Loader
	 *
	 * This function lets users load and instantiate classes.
	 * It is designed to be called from a user's app controllers.
	 *
	 * @param	string	the name of the class
	 * @param	mixed	the optional parameters
	 * @param	string	an optional object name
	 * @return	void
	 */
	public function library($library = '', $params = NULL, $object_name = NULL)
	{
		if ($library == '')
		{
			return FALSE;
		}

		if ( ! is_null($params) AND ! is_array($params))
		{
			$params = NULL;
		}

		if (is_array($library))
		{
			foreach ($library as $class)
			{
				$this->_ci_load_class($class, $params, $object_name);
			}
		}
		else
		{
			$this->_ci_load_class($library, $params, $object_name);
		}

		$this->_ci_assign_to_models();
	}

	// --------------------------------------------------------------------

	/**
	 * Model Loader
	 *
	 * This function lets users load and instantiate models.
	 *
	 * @param	string	the name of the class
	 * @param	string	name for the model
	 * @param	bool	database connection
	 * @return	void
	 */
	public function model($model, $name = '', $db_conn = FALSE)
	{
		if (is_array($model))
		{
			foreach ($model as $babe)
			{
				$this->model($babe);
			}

			return;
		}

		if ($model == '')
		{
			return;
		}

		// Is the model in a sub-folder? If so, parse out the filename and path.
		if (strpos($model, '/') === FALSE)
		{
			$path = '';
		}
		else
		{
			$x = explode('/', $model);
			$model = end($x);
			unset($x[count($x)-1]);
			$path = implode('/', $x).'/';
		}

		if ($name == '')
		{
			$name = $model;
		}

		if (in_array($name, $this->_ci_models, TRUE))
		{
			return;
		}

		if (isset($this->CI->$name))
		{
			show_error("The model name you are loading is the name of a resource that is already being used: {$name}");
		}

		$model = strtolower($model);

		if ( ! file_exists(APPPATH."models/{$path}{$model}.php"))
		{
			show_error("Unable to locate the model you have specified: {$model}");
		}

		if ($db_conn !== FALSE AND ! class_exists('CI_DB'))
		{
			$this->CI->load->database($db_conn === TRUE ? '' : $db_conn, FALSE, TRUE);
		}

		if ( ! class_exists('Model'))
		{
			load_class('Model', FALSE);
		}

		$model_class_name = ucfirst($model);

		if ( ! class_exists($model_class_name))
		{
			require(APPPATH."models/{$path}{$model}.php");
		}
		
		$this->CI->$name = new $model_class_name();
		
		// Only update the new model every time the CI object is changed if the class has the _assign_libraries()
		// method
		if (is_subclass_of($this->CI->$name, 'Model'))
		{
			$this->_ci_models[] = $name;
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Database Loader
	 *
	 * @param	string	the DB credentials
	 * @param	bool	whether to return the DB object
	 * @param	bool	whether to enable active record (this allows us to override the config setting)
	 * @return	object
	 */
	public function database($params = '', $return = FALSE, $active_record = FALSE)
	{
		// Do we even need to load the database class?
		if (class_exists('CI_DB') AND $return == FALSE AND $active_record == FALSE AND isset($this->CI->db) AND
			is_object($this->CI->db))
		{
			return FALSE;
		}

		require(BASEPATH.'database/DB.php');

		if ($return === TRUE)
		{
			return DB($params, $active_record);
		}

		// Load the DB class
		$this->CI->db =& DB($params, $active_record);

		// Assign the DB object to any existing models
		$this->_ci_assign_to_models();
	}

	// --------------------------------------------------------------------

	/**
	 * Load the Utilities Class
	 *
	 * @return	string
	 */
	public function dbutil()
	{
		if (class_exists('CI_DB_utility'))
		{
			return;
		}

		if ( ! class_exists('CI_DB'))
		{
			$this->database();
		}

		require(BASEPATH.'database/DB_utility.php');
		require(BASEPATH."database/drivers/{$this->CI->db->dbdriver}/{$this->CI->db->dbdriver}_utility.php");

		$class = 'CI_DB_'.$this->CI->db->dbdriver.'_utility';
		$this->CI->dbutil =& instantiate_class(new $class());
		$this->CI->load->_ci_assign_to_models();
	}

	// --------------------------------------------------------------------

	/**
	 * Load the Database Forge Class
	 *
	 * @return	string
	 */
	public function dbforge()
	{
		if (class_exists('CI_DB_forge'))
		{
			return;
		}

		if ( ! class_exists('CI_DB'))
		{
			$this->database();
		}

		require(BASEPATH.'database/DB_forge.php');
		require(BASEPATH."database/drivers/{$this->CI->db->dbdriver}/{$this->CI->db->dbdriver}_forge.php");

		$class = 'CI_DB_'.$this->CI->db->dbdriver.'_forge';
		$this->CI->dbforge = new $class();
		$this->CI->load->_ci_assign_to_models();
	}

	// --------------------------------------------------------------------

	/**
	 * Load View
	 *
	 * This function is used to load a "view" file.  It has three parameters:
	 *
	 * 1. The name of the "view" file to be included.
	 * 2. An associative array of data to be extracted for use in the view.
	 * 3. TRUE/FALSE - whether to return the data or load it.  In
	 * some cases it's advantageous to be able to return data so that
	 * a developer can process it in some way.
	 *
	 * @param	string
	 * @param	array
	 * @param	bool
	 * @return	void
	 */
	public function view($view, $vars = array(), $return = FALSE)
	{
		return $this->_ci_load(array(
			'_ci_view' => $view,
			'_ci_vars' => $this->_ci_object_to_array($vars),
			'_ci_return' => $return)
		);
	}

	// --------------------------------------------------------------------

	/**
	 * Load File
	 *
	 * This is a generic file loader
	 *
	 * @param	string
	 * @param	bool
	 * @return	string
	 */
	public function file($path, $return = FALSE)
	{
		return $this->_ci_load(array('_ci_path' => $path, '_ci_return' => $return));
	}

	// --------------------------------------------------------------------

	/**
	 * Set Variables
	 *
	 * Once variables are set they become available within
	 * the controller class and its "view" files.
	 *
	 * @param	array
	 * @return	void
	 */
	public function vars($vars = array(), $val = '')
	{
		if ($val != '' AND is_string($vars))
		{
			$vars = array($vars => $val);
		}

		$vars = $this->_ci_object_to_array($vars);

		if (is_array($vars) AND count($vars) > 0)
		{
			foreach ($vars as $key => $val)
			{
				$this->_ci_cached_vars[$key] = $val;
			}
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Load Helper
	 *
	 * This function loads the specified helper file.
	 *
	 * @param	mixed
	 * @return	void
	 */
	public function helper($helpers = array())
	{
		if (is_array($helpers))
		{
			foreach ($helpers as $helper)
			{
				$this->_helper($helper);
			}
		}
		else
		{
			$this->_helper($helpers);
		}
	}
	
	/**
	 * Loads the given helper
	 *
	 * @param string $helper
	 */
	protected function _helper($helper)
	{
		$helper = strtolower(str_replace('.php', '', str_replace('_helper', '', $helper)).'_helper');

		if (isset($this->_ci_helpers[$helper]))
		{
			return;
		}

		$base_helper = BASEPATH."helpers/{$helper}.php";
		$ext_helper = APPPATH.'helpers/'.config_item('subclass_prefix')."{$helper}.php";
		$overwrite_helper = APPPATH."helpers/{$helper}.php";

		if (file_exists($ext_helper))
		{
			require($ext_helper);
			require($base_helper);
		}
		elseif (file_exists($overwrite_helper))
		{
			require($overwrite_helper);
		}
		elseif (file_exists($base_helper))
		{
			require($base_helper);
		}
		else
		{
			show_error("Could not load helper: {$helper}");
			return;
		}

		$this->_ci_helpers[$helper] = TRUE;
		log_message('debug', "Helper loaded: {$helper}");
	}

	// --------------------------------------------------------------------

	/**
	 * Load Helpers
	 *
	 * This is simply an alias to the above function in case the
	 * user has written the plural form of this function.
	 *
	 * @param	array
	 * @return	void
	 */
	public function helpers($helpers = array())
	{
		$this->helper($helpers);
	}

	// --------------------------------------------------------------------

	/**
	 * Load Plugin
	 *
	 * This function loads the specified plugin.
	 *
	 * @param	array
	 * @return	void
	 */
	public function plugin($plugins = array())
	{
		if (is_array($plugins))
		{
			foreach ($plugins as $plugin)
			{
				$this->_plugin($plugin);
			}
		}
		else
		{
			$this->_plugin($plugin);
		}
	}

	private function _plugin($plugin)
	{
		$plugin = strtolower(str_replace('.php', '', str_replace('_pi', '', $plugin)).'_pi');

		if (isset($this->_ci_plugins[$plugin]))
		{
			return;
		}

		$base_plugin = BASEPATH."plugins/{$plugin}.php";
		$overwrite_plugin = APPPATH."plugins/{$plugin}.php";

		if (file_exists(APPPATH."plugins/{$plugin}.php"))
		{
			require(APPPATH."plugins/{$plugin}php");
		}
		elseif (file_exists(BASEPATH."plugins/{$plugin}.php"))
		{
			require(BASEPATH."plugins/{$plugin}.php");
		}
		else
		{
			show_error("Could not load plugin: {$plugin}");
			return;
		}

		$this->_ci_plugins[$plugin] = TRUE;
		log_message('debug', "Plugin loaded: {$plugin}");
	}

	// --------------------------------------------------------------------

	/**
	 * Load Plugins
	 *
	 * This is simply an alias to the above function in case the
	 * user has written the plural form of this function.
	 *
	 * @param	array
	 * @return	void
	 */
	public function plugins($plugins = array())
	{
		$this->plugin($plugins);
	}

	// --------------------------------------------------------------------

	/**
	 * Loads a language file
	 *
	 * @param	array
	 * @param	string
	 * @return	void
	 */
	public function language($file = array(), $lang = '')
	{
		if ( ! is_array($file))
		{
			$file = array($file);
		}

		foreach ($file as $langfile)
		{
			$this->CI->lang->load($langfile, $lang);
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Loads a config file
	 *
	 * @param	string
	 * @return	void
	 */
	public function config($file = '', $use_sections = FALSE, $fail_gracefully = FALSE)
	{
		$this->CI->config->load($file, $use_sections, $fail_gracefully);
	}

	// --------------------------------------------------------------------

	/**
	 * Loader
	 *
	 * This function is used to load views and files.
	 * Variables are prefixed with _ci_ to avoid symbol collision with
	 * variables made available to view files
	 *
	 * @param	array
	 * @return	void
	 */
	protected function _ci_load($_ci_data)
	{
		$arr = array('_ci_view', '_ci_vars', '_ci_path', '_ci_return');

		// Set the default data variables
		foreach ($arr as $_ci_val)
		{
			$$_ci_val = ( ! isset($_ci_data[$_ci_val])) ? FALSE : $_ci_data[$_ci_val];
		}

		// Set the path to the requested file
		if ($_ci_path == '')
		{
			$_ci_ext = pathinfo($_ci_view, PATHINFO_EXTENSION);
			$_ci_file = ($_ci_ext == '') ? "{$_ci_view}.php" : $_ci_view;
			$_ci_path = $this->_ci_view_path.$_ci_file;
		}
		else
		{
			$_ci_x = explode('/', $_ci_path);
			$_ci_file = end($_ci_x);
		}

		if ( ! file_exists($_ci_path))
		{
			show_error("Unable to load the requested file: {$_ci_file}");
		}

		// This allows anything loaded using $this->load (views, files, etc.)
		// to become accessible from within the Controller and Model functions.
		$_ci_object_vars = get_object_vars($this->CI);
		foreach ($_ci_object_vars as $_ci_key => $_ci_var)
		{
			if ( ! isset($this->$_ci_key))
			{
				$this->$_ci_key =& $this->CI->$_ci_key;
			}
		}

		/*
		 * Extract and cache variables
		 *
		 * You can either set variables using the dedicated $this->load_vars()
		 * function or via the second parameter of this function. We'll merge
		 * the two types and cache them so that views that are embedded within
		 * other views can have access to these variables.
		 */
		if (is_array($_ci_vars))
		{
			$this->_ci_cached_vars = array_merge($this->_ci_cached_vars, $_ci_vars);
		}
		extract($this->_ci_cached_vars);

		/*
		 * Buffer the output
		 *
		 * We buffer the output for two reasons:
		 * 1. Speed. You get a significant speed boost.
		 * 2. So that the final rendered template can be
		 * post-processed by the output class.  Why do we
		 * need post processing?  For one thing, in order to
		 * show the elapsed page load time.  Unless we
		 * can intercept the content right before it's sent to
		 * the browser and then stop the timer it won't be accurate.
		 */
		ob_start();

		// If the PHP installation does not support short tags we'll
		// do a little string replacement, changing the short tags
		// to standard PHP echo statements.

		if (config_item('rewrite_short_tags') == TRUE)
		{
			echo eval('?>'.preg_replace("/;*\s*\?>/", "; ?>", str_replace('<?=', '<?php echo ',
				file_get_contents($_ci_path))));
		}
		else
		{
			include($_ci_path);
		}

		log_message('debug', "File loaded: {$_ci_path}");

		// Return the file data if requested
		if ($_ci_return === TRUE)
		{
			$buffer = ob_get_contents();
			ob_end_clean();
			return $buffer;
		}

		/*
		 * Flush the buffer... or buff the flusher?
		 *
		 * In order to permit views to be nested within
		 * other views, we need to flush the content back out whenever
		 * we are beyond the first level of output buffering so that
		 * it can be seen and included properly by the first included
		 * template and any subsequent ones. Oy!
		 *
		 */
		if (ob_get_level() > $this->_ci_ob_level + 1)
		{
			ob_end_flush();
		}
		else
		{
			global $OUT;
			$OUT->append_output(ob_get_contents());
			ob_end_clean();
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Load class
	 *
	 * This function loads the requested class.
	 *
	 * @param 	string	the item that is being loaded
	 * @param	mixed	any additional parameters
	 * @param	string	an optional object name
	 * @return 	void
	 */
	protected function _ci_load_class($class, $params = NULL, $object_name = NULL)
	{
		// Get the class name, and while we're at it trim any slashes.
		// The directory path can be included as part of the class name,
		// but we don't want a leading slash
		$class = str_replace('.php', '', trim($class, '/'));

		// Was the path included with the class name?
		// We look for a slash to determine this
		$subdir = '';
		if (strpos($class, '/') !== FALSE)
		{
			// explode the path so we can separate the filename from the path
			$x = explode('/', $class);

			// Reset the $class variable now that we know the actual filename
			$class = end($x);

			// Kill the filename from the array
			unset($x[count($x)-1]);

			// Glue the path back together, sans filename
			$subdir = implode($x, '/').'/';
		}

		// We'll test for both lowercase and capitalized versions of the file name
		$class_names = array(ucfirst($class), strtolower($class));
		foreach ($class_names as $class)
		{
			$subclass = APPPATH.'libraries/'.$subdir.config_item('subclass_prefix')."{$class}.php";

			// Is this a class extension request?
			if (file_exists($subclass))
			{
				$baseclass = BASEPATH.'libraries/'.ucfirst($class).'.php';

				if ( ! file_exists($baseclass))
				{
					log_message('error', "Unable to load the requested class: {$class}");
					show_error("Unable to load the requested class: {$class}");
				}

				// Safety:  Was the class already loaded by a previous call?
				if (in_array($subclass, $this->_ci_loaded_files))
				{
					// Before we deem this to be a duplicate request, let's see
					// if a custom object name is being supplied.  If so, we'll
					// return a new instance of the object
					if ( ! is_null($object_name))
					{
						if ( ! isset($this->CI->$object_name))
						{
							return $this->_ci_init_class($class, config_item('subclass_prefix'), $params, $object_name);
						}
					}

					$is_duplicate = TRUE;
					log_message('debug', "{$class} class already loaded. Second attempt ignored.");
					return;
				}

				require($baseclass);
				require($subclass);
				$this->_ci_loaded_files[] = $subclass;

				return $this->_ci_init_class($class, config_item('subclass_prefix'), $params, $object_name);
			}

			// Lets search for the requested library file and load it.
			$is_duplicate = FALSE;
			for ($i = 1; $i < 3; $i++)
			{
				$path = ($i % 2) ? APPPATH : BASEPATH;
				$filepath = "{$path}libraries/{$subdir}{$class}.php";

				// Does the file exist?  No?  Bummer...
				if ( ! file_exists($filepath))
				{
					continue;
				}

				// Safety:  Was the class already loaded by a previous call?
				if (in_array($filepath, $this->_ci_loaded_files))
				{
					// Before we deem this to be a duplicate request, let's see
					// if a custom object name is being supplied.  If so, we'll
					// return a new instance of the object
					if ( ! is_null($object_name))
					{
						if ( ! isset($this->CI->$object_name))
						{
							return $this->_ci_init_class($class, '', $params, $object_name);
						}
					}

					$is_duplicate = TRUE;
					log_message('debug', "{$class} class already loaded. Second attempt ignored.");
					return;
				}

				require($filepath);
				$this->_ci_loaded_files[] = $filepath;
				return $this->_ci_init_class($class, '', $params, $object_name);
			}
		} // END FOREACH

		// One last attempt.  Maybe the library is in a subdirectory, but it wasn't specified?
		if ($subdir == '')
		{
			$path = strtolower($class).'/'.$class;
			return $this->_ci_load_class($path, $params);
		}

		// If we got this far we were unable to find the requested class.
		// We do not issue errors if the load call failed due to a duplicate request
		if ($is_duplicate == FALSE)
		{
			log_message('error', "Unable to load the requested class: {$class}");
			show_error("Unable to load the requested class: {$class}");
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Instantiates a class
	 *
	 * @param	string
	 * @param	string
	 * @param	string	an optional object name
	 * @return	null
	 */
	protected function _ci_init_class($class, $prefix = '', $config = FALSE, $object_name = NULL)
	{
		// Is there an associated config file for this class?
		if ($config === NULL)
		{
			// We test for both uppercase and lowercase, for servers that
			// are case-sensitive with regard to file names
			$lowercase = APPPATH.'config/'.strtolower($class).'.php';
			$uppercase = APPPATH.'config/'.ucfirst(strtolower($class)).'.php';
			if (file_exists($lowercase))
			{
				require($lowercase);
			}
			elseif (file_exists($uppercase))
			{
				require($uppercase);
			}
		}

		if ($prefix == '')
		{
			$class_name = "CI_{$class}";
			$prefix_name = config_item('subclass_prefix').$class;

			if (class_exists($class_name))
			{
				$name = $class_name;
			}
			elseif (class_exists($prefix_name))
			{
				$name = $prefix_name;
			}
			else
			{
				$name = $class;
			}
		}
		else
		{
			$name = $prefix.$class;
		}

		// Is the class name valid?
		if ( ! class_exists($name))
		{
			log_message('error', "Non-existent class: {$name}");
			show_error("Non-existent class: {$class}");
		}

		// Set the variable name we will assign the class to
		// Was a custom class name supplied?  If so we'll use it
		$class = strtolower($class);

		if (is_null($object_name))
		{
			$classvar = ( ! isset($this->_ci_varmap[$class])) ? $class : $this->_ci_varmap[$class];
		}
		else
		{
			$classvar = $object_name;
		}

		// Save the class name and object name
		$this->_ci_classes[$class] = $classvar;

		// Instantiate the class
		if ($config !== NULL)
		{
			$this->CI->$classvar = new $name($config);
		}
		else
		{
			$this->CI->$classvar = new $name;
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Autoloader
	 *
	 * The config/autoload.php file contains an array that permits sub-systems,
	 * libraries, plugins, and helpers to be loaded automatically.
	 *
	 * @param	array
	 * @return	void
	 */
	public function _ci_autoloader()
	{
		static $arr = array('helper', 'plugin', 'language');

		require(APPPATH.'config/autoload.php');

		if ( ! isset($autoload))
		{
			return FALSE;
		}

		$config = $autoload['config'];
		// Load any custom config file
		foreach ($config as $key => $val)
		{
			$this->CI->config->load($val);
		}

		// Autoload plugins, helpers and languages
		foreach ($arr as $type)
		{
			if (isset($autoload[$type]) AND count($autoload[$type]) > 0)
			{
				$this->$type($autoload[$type]);
			}
		}

		// Load libraries
		if (isset($autoload['libraries']) AND count($autoload['libraries']) > 0)
		{
			// Load the database driver.
			if (in_array('database', $autoload['libraries']))
			{
				$this->database();
				$autoload['libraries'] = array_diff($autoload['libraries'], array('database'));
			}

			// Load all other libraries
			foreach ($autoload['libraries'] as $item)
			{
				$this->library($item);
			}
		}

		// Autoload models
		if (isset($autoload['model']))
		{
			$this->model($autoload['model']);
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Assign to Models
	 *
	 * Makes sure that anything loaded by the loader class (libraries, plugins, etc.)
	 * will be available to models, if any exist.
	 *
	 * @param	object
	 * @return	array
	 */
	private function _ci_assign_to_models()
	{
		foreach ($this->_ci_models as $model)
		{
			$this->CI->$model->_assign_libraries();
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Object to Array
	 *
	 * Takes an object as input and converts the class variables to array key/vals
	 *
	 * @param	object
	 * @return	array
	 */
	protected function _ci_object_to_array($object)
	{
		return (is_object($object)) ? get_object_vars($object) : $object;
	}
}

/* End of file Loader.php */
/* Location: ./system/libraries/Loader.php */