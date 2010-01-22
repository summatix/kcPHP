<?php
/**
 * CodeIgniter HTML Helpers
 *
 * @package		kcPHP
 * @subpackage	helpers
 * @author		ExpressionEngine Dev Team
 * @modified	ShiverCube - Removed PHP4 compatibily, and added a few framework tweaks
 * @copyright	Copyright (c) 2008 - 2010, EllisLab, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 * @link		http://codeigniter.com/user_guide/helpers/html_helper.html
 * @since		Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

namespace {

// ------------------------------------------------------------------------

/**
 * Heading
 *
 * Generates an HTML heading tag.  First param is the data.
 * Second param is the size of the heading tag.
 *
 * @access	public
 * @param	string
 * @param	integer
 * @return	string
 */
if ( ! function_exists('heading'))
{
	function heading($data = '', $h = '1')
	{
		return "<h{$h}>{$data}</h{$h}>";
	}
}

// ------------------------------------------------------------------------

/**
 * Unordered List
 *
 * Generates an HTML unordered list from an single or multi-dimensional array.
 *
 * @access	public
 * @param	array
 * @param	mixed
 * @return	string
 */
if ( ! function_exists('ul'))
{
	function ul($list, $attributes = '')
	{
		return _list('ul', $list, $attributes);
	}
}

// ------------------------------------------------------------------------

/**
 * Ordered List
 *
 * Generates an HTML ordered list from an single or multi-dimensional array.
 *
 * @access	public
 * @param	array
 * @param	mixed
 * @return	string
 */
if ( ! function_exists('ol'))
{
	function ol($list, $attributes = '')
	{
		return _list('ol', $list, $attributes);
	}
}

// ------------------------------------------------------------------------

/**
 * Generates the list
 *
 * Generates an HTML ordered list from an single or multi-dimensional array.
 *
 * @access	private
 * @param	string
 * @param	mixed
 * @param	mixed
 * @param	intiger
 * @return	string
 */
if ( ! function_exists('_list'))
{
	function _list($type = 'ul', $list, $attributes = '', $depth = 0)
	{
		// If an array wasn't submitted there's nothing to do...
		if ( ! is_array($list))
		{
			return $list;
		}

		// Set the indentation based on the depth
		$out = str_repeat(" ", $depth);

		// Were any attributes submitted?  If so generate a string
		if (is_array($attributes))
		{
			$atts = '';
			foreach ($attributes as $key => $val)
			{
				$atts .= ' ' . $key . '="' . $val . '"';
			}
			$attributes = $atts;
		}

		// Write the opening list tag
		$out .= "<{$type}{$attributes}>\n";

		// Cycle through the list elements.  If an array is
		// encountered we will recursively call _list()

		static $_last_list_item = '';
		foreach ($list as $key => $val)
		{
			$_last_list_item = $key;

			$out .= str_repeat(' ', $depth + 2).'<li>';
			if ( ! is_array($val))
			{
				$out .= $val;
			}
			else
			{
				$out .= "{$_last_list_item}\n"._list($type, $val, '', $depth + 4).str_repeat(' ', $depth + 2);
			}

			$out .= "</li>\n";
		}

		return str_repeat(' ', $depth)."</{$type}>\n";
	}
}

// ------------------------------------------------------------------------

/**
 * Generates HTML BR tags based on number supplied
 *
 * @access	public
 * @param	integer
 * @return	string
 */
if ( ! function_exists('br'))
{
	function br($num = 1)
	{
		return str_repeat('<br/>', $num);
	}
}

// ------------------------------------------------------------------------

/**
 * Image
 *
 * Generates an <img /> element
 *
 * @access	public
 * @param	mixed
 * @return	string
 */
if ( ! function_exists('img'))
{
	function img($src = '', $index_page = FALSE)
	{
		$CI =& get_instance();
		
		if ( ! is_array($src) )
		{
			$src = array('src' => $src);
		}

		$img = '<img';

		foreach ($src as $k=>$v)
		{
			if ($k == 'src' AND strpos($v, '://') === FALSE)
			{
				if ($index_page === TRUE)
				{
					$img .= ' src="'.$CI->config->site_url($v).'" ';
				}
				else
				{
					$img .= ' src="'.$CI->config->slash_item('base_url').$v.'" ';
				}
			}
			else
			{
				$img .= " {$k}=\"{$v}\" ";
			}
		}

		return "{$img}/>";
	}
}

// ------------------------------------------------------------------------

/**
 * Doctype
 *
 * Generates a page document type declaration
 *
 * Valid options are xhtml-11, xhtml-strict, xhtml-trans, xhtml-frame,
 * html4-strict, html4-trans, and html4-frame.  Values are saved in the
 * doctypes config file.
 *
 * @access	public
 * @param	string	type	The doctype to be generated
 * @return	string
 */
if ( ! function_exists('doctype'))
{
	function doctype($type = 'xhtml1-strict')
	{
		global $_doctypes;

		if ( ! is_array($_doctypes))
		{
			if (file_exists(APPPATH.'config/doctypes.php'))
			{
				include(APPPATH.'config/doctypes.php');
			}
			
			if ( ! is_array($_doctypes))
			{
				return FALSE;
			}
		}

		if (isset($_doctypes[$type]))
		{
			return $_doctypes[$type];
		}
		else
		{
			return FALSE;
		}
	}
}

// ------------------------------------------------------------------------

/**
 * Link
 *
 * Generates link to a CSS file
 *
 * @access	public
 * @param	mixed	stylesheet hrefs or an array
 * @param	string	rel
 * @param	string	type
 * @param	string	title
 * @param	string	media
 * @param	boolean	should index_page be added to the css path
 * @return	string
 */
if ( ! function_exists('link_tag'))
{
	function link_tag($href = '', $rel = 'stylesheet', $type = 'text/css', $title = '', $media = '', $index_page = FALSE)
	{
		$CI =& get_instance();

		$link = '<link ';
		if (is_array($href))
		{
			foreach ($href as $k=>$v)
			{
				if ($k == 'href' AND strpos($v, '://') === FALSE)
				{
					if ($index_page === TRUE)
					{
						$link .= ' href="'.$CI->config->site_url($v).'" ';
					}
					else
					{
						$link .= ' href="'.$CI->config->slash_item('base_url').$v.'" ';
					}
				}
				else
				{
					$link .= "{$k}=\"{$v}\" ";
				}
			}

			$link .= '/>';
		}
		else
		{
			if (strpos($href, '://') !== FALSE)
			{
				$link .= " href=\"{$href}\" ";
			}
			elseif ($index_page === TRUE)
			{
				$link .= ' href="'.$CI->config->site_url($href).'" ';
			}
			else
			{
				$link .= ' href="'.$CI->config->slash_item('base_url').$href.'" ';
			}

			$link .= "rel=\"{$rel}\" type=\"{$type}\" ";

			if ($media	!= '')
			{
				$link .= "media=\"{$media}\" ";
			}

			if ($title	!= '')
			{
				$link .= "title=\"{$title}\" ";
			}

			$link .= '/>';
		}

		return $link;
	}
}

// ------------------------------------------------------------------------

/**
 * Generates meta tags from an array of key/values
 *
 * @access	public
 * @param	array
 * @return	string
 */
if ( ! function_exists('meta'))
{
	function meta($name = '', $content = '', $type = 'name', $newline = "\n")
	{
		// Since we allow the data to be passes as a string, a simple array
		// or a multidimensional one, we need to do a little prepping.
		if ( ! is_array($name))
		{
			$name = array(array('name' => $name, 'content' => $content, 'type' => $type, 'newline' => $newline));
		}
		else
		{
			// Turn single array into multidimensional
			if (isset($name['name']))
			{
				$name = array($name);
			}
		}

		$str = '';
		foreach ($name as $meta)
		{
			$type = ( ! isset($meta['type']) OR $meta['type'] == 'name') ? 'name' : 'http-equiv';
			$name = ( ! isset($meta['name'])) ? '' : $meta['name'];
			$content = ( ! isset($meta['content']))	? '' : $meta['content'];
			$newline = ( ! isset($meta['newline']))	? "\n" : $meta['newline'];
			
			$str .= "<meta $type=\"{$name}\" content=\"{$content}\" />{$newline}";
		}

		return $str;
	}
}

// ------------------------------------------------------------------------

/**
 * Generates non-breaking space entities based on number supplied
 *
 * @access	public
 * @param	integer
 * @return	string
 */
if ( ! function_exists('nbs'))
{
	function nbs($num = 1)
	{
		return str_repeat('&nbsp;', $num);
	}
}

/**
 * Returns an XHTML start tag
 *
 * @param string $lang
 * @return string
 */
if ( ! function_exists('xhtml_tag'))
{
	function xhtml_tag($lang = 'en')
	{
		// return "<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"{$lang}\" lang=\"{$lang}\">";
		return "<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"{$lang}\">";
	}
}

}

namespace html {

if ( ! function_exists('html\wrap'))
{
	/**
	 * Creates a given tag type, and optionally places the given content inside
	 *
	 * @param $tag string default "div"
	 * @param $content string (optional)
	 * @param $id string (optional)
	 * @param $class string (optional)
	 * @param $style string (optional)
	 * @return string
	 */
	function wrap($tag = 'div', $content = '', $id = '', $class = '', $style = '')
	{
		return "<{$tag}".($id == '' ? '' : " id=\"{$id}\"") . ($class == '' ? '' : " class=\"{$class}\"") .
			($style == '' ? '' : " style=\"{$style}\"").">{$content}</{$tag}>";
	}
}

if ( ! function_exists('html\position'))
{
	/**
	 * Creates a relatively positioned div container
	 *
	 * @param $content string The content to place inside the wrapper
	 * @param $x int The left position to set
	 * @param $y int The top position to set
	 * @param $width (optional) int The width to set
	 * @param $height (optional) int The height to set
	 * @param $class string (optional) The class to set the wrapper to
	 */
	function position($content, $x, $y, $width = '', $height = '', $class = '')
	{
		$style = "left:{$x}px;top:{$y}px;";
		
		if ($width != '')
		{
			$style .= "width:{$width}px;";
		}
		
		if ($height != '')
		{
			$style .= "height:{$height}px;";
		}
		
		return wrap('div', $content, '', $class, $style);
	}
}

if ( ! function_exists('html\flash'))
{
	/**
	 * Creates a simple embedded flash object
	 *
	 * @param $url The flash object URL
	 * @param $width The width to set the flash object
	 * @param $height The height to set the flash object
	 */
	function flash($url, $width, $height, $params = array())
	{
		$object =
			"<object type=\"application/x-shockwave-flash\" data=\"{$url}\" width=\"{$width}\" height=\"{$height}\">";
		
		foreach ($params as $key => $value)
		{
			$object .= "<param name=\"{$key}\" value=\"{$value}\"/>";
		}
		
		return $object.'</object>';
	}
}

if ( ! function_exists('html\youtube'))
{
	/**
	 * Creates a youtube video with the given specifications
	 *
	 * @param $video_id string
	 * @param $width int default 480
	 * @param $height int default 295
	 * @return string
	 */
	function youtube($video_id, $width = 480, $height = 295)
	{
		$url = "http://www.youtube.com/v/{$video_id}&amp;autoplay=0&amp;rel=0&amp;border=0&amp;loop=0";
		return flash($url, $width, $height, array(
			'movie' => $url,
			'quality' => 'high'
		));
	}
}

if ( ! function_exists('html\vimeo'))
{
	/**
	 * Creates a vimeo video with the given specifications
	 *
	 * @access public
	 * @param $videoId string
	 * @param $width int default 400
	 * @param $height int default 225
	 * @return string
	 */
	function vimeo($videoId, $width = 400, $height = 225)
	{
		$url = "http://vimeo.com/moogaloop.swf?clip_id={$videoId}&amp;server=vimeo.com&amp;show_title=1&amp;show_byline=1&amp;show_portrait=0&amp;color=&amp;fullscreen=1";
		return flash($url, $width, $height, array(
			'allowfullscreen' => 'true',
			'movie' => $url
		));
	}
}

if ( ! function_exists('html\script'))
{
	/**
	 * Creates a JavaScript script tag
	 *
	 * @access public
	 * @param $src mixed The source JavaScript file
	 * @param $type string default "text/javascript"
	 * @return string
	 */
	function script($src, $type = 'text/javascript')
	{
		$tag = '';
		
		if (is_array($src))
		{
			foreach ($src as $s)
			{
				$tag .= script($s, $type);
			}
		}
		else
		{
			$CI =& get_instance();
			$url = $CI->config->site_url($src);
			$tag .= "<script src=\"{$url}\" type=\"{$type}\"></script>";
		}
		
		return $tag;
	}
}

}

/* End of file html_helper.php */
/* Location: ./system/helpers/html_helper.php */