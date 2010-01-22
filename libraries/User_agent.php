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
 * User Agent Class
 *
 * Identifies the platform, browser, robot, or mobile devise of the browsing agent
 *
 * @author		ExpressionEngine Dev Team
 * @link		http://codeigniter.com/user_guide/libraries/user_agent.html
 */
class CI_User_agent {

	private $agent		= NULL;
	
	private $is_browser	= FALSE;
	private $is_robot	= FALSE;
	private $is_mobile	= FALSE;

	private $languages	= array();
	private $charsets	= array();
	
	private $platforms	= array();
	private $browsers	= array();
	private $mobiles	= array();
	private $robots		= array();
	
	private $platform	= '';
	private $browser	= '';
	private $version	= '';
	private $mobile		= '';
	private $robot		= '';
	
	/**
	 * Constructor
	 *
	 * Sets the User Agent and runs the compilation routine
	 *
	 * @return	void
	 */		
	public function CI_User_agent()
	{
		if (isset($_SERVER['HTTP_USER_AGENT']))
		{
			$this->agent = trim($_SERVER['HTTP_USER_AGENT']);
		}
		
		if ( ! is_null($this->agent))
		{
			if ($this->_load_agent_file())
			{
				$this->_compile_data();
			}
		}
		
		log_message('debug', "User Agent Class Initialized");
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Compile the User Agent Data
	 *
	 * @return	bool
	 */		
	private function _load_agent_file()
	{
		$file = APPPATH.'config/user_agents.php';
		if ( ! file_exists($file))
		{
			return FALSE;
		}
		
		include($file);
		
		$return = FALSE;
		
		if (isset($platforms))
		{
			$this->platforms = $platforms;
			unset($platforms);
			$return = TRUE;
		}

		if (isset($browsers))
		{
			$this->browsers = $browsers;
			unset($browsers);
			$return = TRUE;
		}

		if (isset($mobiles))
		{
			$this->mobiles = $mobiles;
			unset($mobiles);
			$return = TRUE;
		}
		
		if (isset($robots))
		{
			$this->robots = $robots;
			unset($robots);
			$return = TRUE;
		}

		return $return;
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Compile the User Agent Data
	 *
	 * @return	bool
	 */		
	private function _compile_data()
	{
		$this->_set_platform();
	
		foreach (array('_set_browser', '_set_robot', '_set_mobile') as $function)
		{
			if ($this->$function() === TRUE)
			{
				break;
			}
		}	
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Set the Platform
	 *
	 * @return	mixed
	 */		
	private function _set_platform()
	{
		if (is_array($this->platforms) AND count($this->platforms) > 0)
		{
			foreach ($this->platforms as $key => $val)
			{
				if (preg_match("|".preg_quote($key)."|i", $this->agent))
				{
					$this->platform = $val;
					return TRUE;
				}
			}
		}
		$this->platform = 'Unknown Platform';
	}

	// --------------------------------------------------------------------
	
	/**
	 * Set the Browser
	 *
	 * @return	bool
	 */		
	private function _set_browser()
	{
		if (is_array($this->browsers) AND count($this->browsers) > 0)
		{
			foreach ($this->browsers as $key => $val)
			{		
				if (preg_match("|".preg_quote($key).".*?([0-9\.]+)|i", $this->agent, $match))
				{
					$this->is_browser = TRUE;
					$this->version = $match[1];
					$this->browser = $val;
					$this->_set_mobile();
					return TRUE;
				}
			}
		}
		return FALSE;
	}
			
	// --------------------------------------------------------------------
	
	/**
	 * Set the Robot
	 *
	 * @return	bool
	 */		
	private function _set_robot()
	{
		if (is_array($this->robots) AND count($this->robots) > 0)
		{		
			foreach ($this->robots as $key => $val)
			{
				if (preg_match("|".preg_quote($key)."|i", $this->agent))
				{
					$this->is_robot = TRUE;
					$this->robot = $val;
					return TRUE;
				}
			}
		}
		return FALSE;
	}

	// --------------------------------------------------------------------
	
	/**
	 * Set the Mobile Device
	 *
	 * @return	bool
	 */		
	private function _set_mobile()
	{
		if (is_array($this->mobiles) AND count($this->mobiles) > 0)
		{		
			foreach ($this->mobiles as $key => $val)
			{
				if (FALSE !== (strpos(strtolower($this->agent), $key)))
				{
					$this->is_mobile = TRUE;
					$this->mobile = $val;
					return TRUE;
				}
			}
		}	
		return FALSE;
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Set the accepted languages
	 *
	 * @return	void
	 */			
	private function _set_languages()
	{
		if ((count($this->languages) == 0) AND isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) AND $_SERVER['HTTP_ACCEPT_LANGUAGE'] != '')
		{
			$languages = preg_replace('/(;q=[0-9\.]+)/i', '', strtolower(trim($_SERVER['HTTP_ACCEPT_LANGUAGE'])));
			
			$this->languages = explode(',', $languages);
		}
		
		if (count($this->languages) == 0)
		{
			$this->languages = array('Undefined');
		}	
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Set the accepted character sets
	 *
	 * @return	void
	 */			
	private function _set_charsets()
	{	
		if ((count($this->charsets) == 0) AND isset($_SERVER['HTTP_ACCEPT_CHARSET']) AND $_SERVER['HTTP_ACCEPT_CHARSET'] != '')
		{
			$charsets = preg_replace('/(;q=.+)/i', '', strtolower(trim($_SERVER['HTTP_ACCEPT_CHARSET'])));
			
			$this->charsets = explode(',', $charsets);
		}
		
		if (count($this->charsets) == 0)
		{
			$this->charsets = array('Undefined');
		}	
	}

	// --------------------------------------------------------------------
	
	/**
	 * Is Browser
	 *
	 * @return	bool
	 */		
	public function is_browser()
	{
		return $this->is_browser;
	}

	// --------------------------------------------------------------------
	
	/**
	 * Is Robot
	 *
	 * @return	bool
	 */		
	public function is_robot()
	{
		return $this->is_robot;
	}

	// --------------------------------------------------------------------
	
	/**
	 * Is Mobile
	 *
	 * @return	bool
	 */		
	public function is_mobile()
	{
		return $this->is_mobile;
	}	

	// --------------------------------------------------------------------
	
	/**
	 * Is this a referral from another site?
	 *
	 * @return	bool
	 */			
	public function is_referral()
	{
		return ( ! isset($_SERVER['HTTP_REFERER']) OR $_SERVER['HTTP_REFERER'] == '') ? FALSE : TRUE;
	}

	// --------------------------------------------------------------------
	
	/**
	 * Agent String
	 *
	 * @return	string
	 */			
	public function agent_string()
	{
		return $this->agent;
	}

	// --------------------------------------------------------------------
	
	/**
	 * Get Platform
	 *
	 * @return	string
	 */			
	public function platform()
	{
		return $this->platform;
	}

	// --------------------------------------------------------------------
	
	/**
	 * Get Browser Name
	 *
	 * @return	string
	 */			
	public function browser()
	{
		return $this->browser;
	}

	// --------------------------------------------------------------------
	
	/**
	 * Get the Browser Version
	 *
	 * @return	string
	 */			
	public function version()
	{
		return $this->version;
	}

	// --------------------------------------------------------------------
	
	/**
	 * Get The Robot Name
	 *
	 * @return	string
	 */				
	public function robot()
	{
		return $this->robot;
	}
	// --------------------------------------------------------------------
	
	/**
	 * Get the Mobile Device
	 *
	 * @return	string
	 */			
	public function mobile()
	{
		return $this->mobile;
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Get the referrer
	 *
	 * @return	bool
	 */			
	public function referrer()
	{
		return ( ! isset($_SERVER['HTTP_REFERER']) OR $_SERVER['HTTP_REFERER'] == '') ? '' : trim($_SERVER['HTTP_REFERER']);
	}

	// --------------------------------------------------------------------
	
	/**
	 * Get the accepted languages
	 *
	 * @return	array
	 */			
	public function languages()
	{
		if (count($this->languages) == 0)
		{
			$this->_set_languages();
		}
	
		return $this->languages;
	}

	// --------------------------------------------------------------------
	
	/**
	 * Get the accepted Character Sets
	 *
	 * @return	array
	 */			
	public function charsets()
	{
		if (count($this->charsets) == 0)
		{
			$this->_set_charsets();
		}
	
		return $this->charsets;
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Test for a particular language
	 *
	 * @return	bool
	 */			
	public function accept_lang($lang = 'en')
	{
		return (in_array(strtolower($lang), $this->languages(), TRUE)) ? TRUE : FALSE;
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Test for a particular character set
	 *
	 * @return	bool
	 */			
	public function accept_charset($charset = 'utf-8')
	{
		return (in_array(strtolower($charset), $this->charsets(), TRUE)) ? TRUE : FALSE;
	}
}

/* End of file User_agent.php */
/* Location: ./system/libraries/User_agent.php */