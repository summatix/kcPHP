<?php
/**
 * CodeIgniter JSON Helpers - Assists in sending JSON output
 *
 * @package		kcPHP
 * @subpackage	helpers
 * @author		ShiverCube
 * @copyright	Copyright (c) 2008 - 2010, EllisLab, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 * @since		Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

namespace json;

if ( ! function_exists('json\output'))
{
	/**
	 * Converts and displays the given array as JSON, and sends the appropriate headers
	 *
	 * @param $json mixed The array or object to encode to JSON
	 * @param $download bool (default TRUE) Whether or not to send the content as a JSON file download
	 * @param $json_name string (default "result.json") When $download is TRUE, the name of the file download
	 */
	function output($json = array(), $download = TRUE, $json_name = 'result.json')
	{
		$CI =& get_instance();
		
		$json = json_encode($json);
		
		if ($download)
		{
			$CI->output->set_header('Content-Type: application/json');
			$CI->output->set_header("Content-Disposition: attachment; filename=\"{$json_name}\"");
		}
		
		$CI->output->set_header('Cache-Control: no-store, no-cache, must-revalidate');
		$CI->output->set_header('Pragma: no-cache');
		$CI->output->set_header('Content-Length: '.strlen($json));
		
		$CI->output->set_output($json);
	}
}

/* End of file json_helper.php */
/* Location: ./system/helpers/json_helper.php */