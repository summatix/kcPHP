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
 * Language Class
 *
 * @author		ExpressionEngine Dev Team
 * @link		http://codeigniter.com/user_guide/libraries/language.html
 */
class CI_Language {

	private $language	= array();
	public $is_loaded	= array();

	/**
	 * Constructor
	 */
	public function CI_Language()
	{
		log_message('debug', "Language Class Initialized");
	}

	// --------------------------------------------------------------------

	/**
	 * Load a language file
	 *
	 * @param	mixed	the name of the language file to be loaded. Can be an array
	 * @param	string	the language (english, etc.)
	 * @return	mixed
	 */
	public function load($langfile = '', $idiom = '', $return = FALSE)
	{
		$langfile = str_replace('.php', '', str_replace('_lang.', '', $langfile)).'_lang.php';

		if (in_array($langfile, $this->is_loaded, TRUE))
		{
			return;
		}

		if ($idiom == '')
		{
			$CI =& get_instance();
			$deft_lang = $CI->config->item('language');
			$idiom = ($deft_lang == '') ? 'english' : $deft_lang;
		}

		// Determine where the language file is and load it
		if (file_exists(APPPATH.'language/'.$idiom.'/'.$langfile))
		{
			include(APPPATH.'language/'.$idiom.'/'.$langfile);
		}
		else
		{
			if (file_exists(BASEPATH.'language/'.$idiom.'/'.$langfile))
			{
				include(BASEPATH.'language/'.$idiom.'/'.$langfile);
			}
			else
			{
				show_error('Unable to load the requested language file: language/'.$idiom.'/'.$langfile);
			}
		}

		if ( ! isset($lang))
		{
			log_message('error', 'Language file contains no data: language/'.$idiom.'/'.$langfile);
			return;
		}

		if ($return == TRUE)
		{
			return $lang;
		}

		$this->is_loaded[] = $langfile;
		$this->language = array_merge($this->language, $lang);
		unset($lang);

		log_message('debug', 'Language file loaded: language/'.$idiom.'/'.$langfile);
		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch a single line of text from the language array
	 *
	 * @access	public
	 * @param	string	$line 	the language line
	 * @return	string
	 */
	public function line($line = '')
	{
		$line = ($line == '' OR ! isset($this->language[$line])) ? FALSE : $this->language[$line];
		if (func_num_args() == 1)
		{
			return $line;
		}
		else
		{
			$args = func_get_args();
			$args[0] = $line;
			return call_user_func_array('sprintf', $args);
		}
	}
}

/* End of file Language.php */
/* Location: ./system/libraries/Language.php */