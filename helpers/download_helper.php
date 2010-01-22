<?php
/**
 * CodeIgniter Download Helpers
 *
 * @package		kcPHP
 * @subpackage	helpers
 * @author		ExpressionEngine Dev Team
 * @modified	ShiverCube - Removed PHP4 compatibily, and added a few framework tweaks
 * @copyright	Copyright (c) 2008 - 2010, EllisLab, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 * @link		http://codeigniter.com/user_guide/helpers/download_helper.html
 * @since		Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Force Download
 *
 * Generates headers that force a download to happen
 *
 * @access	public
 * @param	string	filename
 * @param	mixed	the data to be downloaded
 * @return	void
 */	
if ( ! function_exists('force_download'))
{
	function force_download($filename = '', $data = '')
	{
		if ($filename == '' OR $data == '')
		{
			return FALSE;
		}

		// Try to determine if the filename includes a file extension.
		// We need it in order to set the MIME type
		if (strpos($filename, '.') === FALSE)
		{
			return FALSE;
		}
	
		// Grab the file extension
		$x = explode('.', $filename);
		$extension = end($x);

		// Load the mime types
		if (file_exists(APPPATH.'config/mimes.php'))
		{
			include(APPPATH.'config/mimes.php');
		}
	
		// Set a default mime if we can't find it
		if ( ! isset($mimes[$extension]))
		{
			$mime = 'application/octet-stream';
		}
		else
		{
			$mime = (is_array($mimes[$extension])) ? $mimes[$extension][0] : $mimes[$extension];
		}
	
		// Generate the server headers
		if (strstr($_SERVER['HTTP_USER_AGENT'], "MSIE"))
		{
			header("Content-Type: \"{$mime}\"");
			header("Content-Disposition: attachment; filename=\"{$filename}\"");
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Content-Transfer-Encoding: binary');
			header('Pragma: public');
			header('Content-Length: '.strlen($data));
		}
		else
		{
			header("Content-Type: \"{$mime}\"");
			header("Content-Disposition: attachment; filename=\"{$filename}\"");
			header("Content-Transfer-Encoding: binary");
			header('Expires: 0');
			header('Pragma: no-cache');
			header('Content-Length: '.strlen($data));
		}
	
		exit($data);
	}
}


/* End of file download_helper.php */
/* Location: ./system/helpers/download_helper.php */