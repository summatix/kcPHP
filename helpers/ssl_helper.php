<?php
/**
 * CodeIgniter SSL Helpers
 *
 * @package		kcPHP
 * @subpackage	helpers
 * @author		ShiverCube
 * @license		http://codeigniter.com/user_guide/license.html
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Force SSL
 *
 * @access	public
 * @param	bool	whether or not to force all URLs to be prefixed with https://
 * @return	string
 */
if ( ! function_exists('force_ssl'))
{
	function force_ssl($change_url = FALSE)
	{
		if ($change_url)
		{
			$CI =& get_instance();
			$CI->config->config['base_url'] = str_replace('http://', 'https://', $CI->config->config['base_url']);
		}
		
		if ($_SERVER['SERVER_PORT'] != 443)
		{
			ci_secure_redirect($CI->uri->uri_string());
		}
	}
}

// ------------------------------------------------------------------------

/**
 * Remove SSL
 *
 * @access	public
 * @param	bool	whether or not to force all URLs to be prefixed with http://
 * @return	string
 */
if ( ! function_exists('force_ssl'))
{
	function force_ssl($change_url = FALSE)
	{
		if ($change_url)
		{
			$CI =& get_instance();
			$CI->config->config['base_url'] = str_replace('https://', 'http://', $CI->config->config['base_url']);
		}
		
		if ($_SERVER['SERVER_PORT'] != 80)
		{
			ci_redirect($CI->uri->uri_string());
		}
	}
}

/* End of file ssl_helper.php */
/* Location: ./system/helpers/ssl_helper.php */