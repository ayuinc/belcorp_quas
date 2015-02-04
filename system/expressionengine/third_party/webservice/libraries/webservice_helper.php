<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * helper
 *
 * @package		Module name
 * @category	Modules
 * @author		Rein de Vries <info@reinos.nl>
 * @link		http://reinos.nl/add-ons/entry-api
 * @license  	http://reinos.nl/add-ons/commercial-license
 * @copyright 	Copyright (c) 2014 Reinos.nl Internet Media
 */

class Webservice_helper
{
	/**
	 * Remove the double slashes
	 */
	public static function remove_double_slashes($str)
    {
        return preg_replace("#(^|[^:])//+#", "\\1/", $str);
    }

	// ----------------------------------------------------------------------

	/**
	 * Check if Submitted String is a Yes value
	 *
	 * If the value is 'y', 'yes', 'true', or 'on', then returns TRUE, otherwise FALSE
	 *
	 */
	public static function check_yes($which, $string = false)
	{
	    if (is_string($which))
	    {
	        $which = strtolower(trim($which));
	    }

	    $result = in_array($which, array('yes', 'y', 'true', 'on'), TRUE);

	    if($string)
	    {
	       return $result ? 'true' : 'false' ; 
	    }

	    return $result;
	}

	// ------------------------------------------------------------------------

	/**
	 * Log an array to a file
	 *
	 */
	public static function log_array($array)
    {
		@file_put_contents(__DIR__.'/print.txt', print_r($array, true), FILE_APPEND);
    }

	// ----------------------------------------------------------------------------------

	/**
	* Log all messages
	*
	* @param array $logs The debug messages.
	* @return void
	*/
	public static function log_to_ee( $logs = array(), $name = '')
    {
        if(!empty($logs))
        {
            foreach ($logs as $log)
            {
                ee()->TMPL->log_item('&nbsp;&nbsp;***&nbsp;&nbsp;'.$name.' debug: ' . $log);
            }
        }
    }

	// ------------------------------------------------------------------------

	/**
	 * Is the string serialized
	 *
	 */
	public static function is_serialized($val)
    {
        if (!is_string($val)){ return false; }
        if (trim($val) == "") { return false; }
        if (preg_match("/^(i|s|a|o|d):(.*);/si",$val)) { return true; }
        return false;
    }

	// ------------------------------------------------------------------------

	/**
	 * Is the string json
	 *
	 */
	public static function is_json($string)
    {
       json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

	// ------------------------------------------------------------------------

	/**
	 * Retrieve site path
	 */
	public static function get_site_path()
    {
        // extract path info
        $site_url_path = parse_url(ee()->functions->fetch_site_index(), PHP_URL_PATH);

        $path_parts = pathinfo($site_url_path);
        $site_path = $path_parts['dirname'];

        $site_path = str_replace("\\", "/", $site_path);

        return $site_path;
    }   

	// ------------------------------------------------------------------------

	/**
	 * remove beginning and ending slashes in a url
	 *
	 * @param  $url
	 * @return void
	 */
	public static function remove_begin_end_slash($url, $slash = '/')
    {
        $url = explode($slash, $url);
        array_pop($url);
        array_shift($url);
        return implode($slash, $url);
    }

	// ----------------------------------------------------------------------

	/**
	 * add slashes for an array
	 *
	 * @param  $arr_r
	 * @return void
	 */
	public static function add_slashes_extended(&$arr_r)
    {
        if(is_array($arr_r))
        {
            foreach ($arr_r as &$val)
                is_array($val) ? self::add_slashes_extended($val):$val=addslashes($val);
            unset($val);
        }
        else
            $arr_r = addslashes($arr_r);
    }

	// ----------------------------------------------------------------

	/**
	 * add a element to a array
	 *
	 * @return  DB object
	 */
	public static function array_unshift_assoc(&$arr, $key, $val)
    {
        $arr = array_reverse($arr, true);
        $arr[$key] = $val;
        $arr = array_reverse($arr, true);
        return $arr;
    }

	// ----------------------------------------------------------------------

	/**
	 * get the memory usage
	 *
	 * @param 
	 * @return void
	 */
	public static function memory_usage()
    {
         $mem_usage = memory_get_usage(true);
       
        if ($mem_usage < 1024)
            return $mem_usage." bytes";
        elseif ($mem_usage < 1048576)
            return round($mem_usage/1024,2)." KB";
        else
            return round($mem_usage/1048576,2)." MB";
    }

    // ----------------------------------------------------------------------
	
	/**
	 * EDT benchmark
	 * https://github.com/mithra62/ee_debug_toolbar/wiki/Benchmarks
	 *
	 * @param none
	 * @return void
	 */
	public static function benchmark($method = '', $start = true)
	{
		if($method != '')
		{
			$prefix = DEFAULT_MAP.'_';
			$type = $start ? '_start' : '_end';
			ee()->benchmark->mark($prefix.$method.$type);
		}
	}

	// ----------------------------------------------------------------------
		
	/**
	 * 	Fetch Action IDs
	 *
	 * 	@access public
	 *	@param string
	 * 	@param string
	 *	@return mixed
	 */
	public static function fetch_action_id($class = '', $method)
	{
		ee()->db->select('action_id');
		ee()->db->where('class', $class);
		ee()->db->where('method', $method);
		$query = ee()->db->get('actions');
		
		if ($query->num_rows() == 0)
		{
			return FALSE;
		}
		
		return $query->row('action_id');
	}

	// ----------------------------------------------------------------------

	/**
	 * Parse only a string
	 *
	 * @param none
	 * @return void
	 */
	public static function parse_channel_data($tag = '', $parse = true)
	{
		// do we need to parse, and are there any modules/tags to parse?
		if($parse && (strpos($tag, LD.'exp:') !== FALSE))
		{
			require_once APPPATH.'libraries/Template.php';
			$OLD_TMPL = isset(ee()->TMPL) ? ee()->TMPL : NULL;
			ee()->TMPL = new EE_Template();
			ee()->TMPL->parse($tag, true);
			ee()->TMPL = $OLD_TMPL;

			//whas the TMPL not yet set, delete this again
			if($OLD_TMPL === NULL)
			{
				unset(ee()->TMPL);
			}
		}

		//return the data
		return trim($tag);		
	}

	// ----------------------------------------------------------------------

	/**
	 * Parse a template
	 *
	 * @param none
	 * @return void
	 */
	public static function parse_template($template_id = 0)
	{
		//load model
		ee()->load->model('template_model');

		//get the template
		$template = ee()->template_model->get_templates(NULL, array(), array('template_id' => $template_id) );

		//is there an template
		if($template->num_rows() > 0)
		{
		   $template = $template->result();

		   //go to the template parser
		   require_once APPPATH.'libraries/Template.php';
		   ee()->TMPL = new EE_Template();
		   ee()->TMPL->run_template_engine($template[0]->group_name, $template[0]->template_name);
		   ee()->output->_display(); 
		}
		else 
		{
		   echo 'No template selected';
		}

		exit;
	}

	// ----------------------------------------------------------------------

    /**
     * set_cache
     *
     * @access private
    */
    public static function set_cache($name = '', $value = '')
    {
    	if (session_id() == "") 
		{
			session_start(); 
		}

		$_SESSION[$name] = $value;
    }

    // ----------------------------------------------------------------------

    /**
     * get_cache
     *
     * @access private
    */
    public static function get_cache($name = '')
    {
    	// if no active session we start a new one
		if (session_id() == "") 
		{
			session_start(); 
		}
		
		if (isset($_SESSION[$name]))
		{
			return $_SESSION[$name];
		}
		
		else
		{
			return '';
		}
    }

    // ----------------------------------------------------------------------

    /**
     * delete_cache
     *
     * @access private
    */
    public static function delete_cache($name = '')
    {
    	// if no active session we start a new one
		if (session_id() == "") 
		{
			session_start(); 
		}
		
		unset($_SESSION[$name]);
    }

    // ----------------------------------------------------------------------

    /**
     * mcp_meta_parser
     *
     * @access private
    */
	public static function mcp_meta_parser($type='', $file)
	{
		// -----------------------------------------
		// CSS
		// -----------------------------------------
		if ($type == 'css')
		{
			if ( isset(ee()->session->cache[DEFAULT_MAP]['CSS'][$file]) == FALSE )
			{
				ee()->cp->add_to_head('<link rel="stylesheet" href="' . ee()->webservice_settings->get_setting('theme_url') . 'css/' . $file . '" type="text/css" media="print, projection, screen" />');
				ee()->session->cache[DEFAULT_MAP]['CSS'][$file] = TRUE;
			}
		}

		// -----------------------------------------
		// CSS Inline
		// -----------------------------------------
		if ($type == 'css_inline')
		{
			ee()->cp->add_to_foot('<style type="text/css">'.$file.'</style>');
			
		}

		// -----------------------------------------
		// Javascript
		// -----------------------------------------
		if ($type == 'js')
		{
			if ( isset(ee()->session->cache[DEFAULT_MAP]['JS'][$file]) == FALSE )
			{
				ee()->cp->add_to_foot('<script src="' . ee()->webservice_settings->get_setting('theme_url') . 'javascript/' . $file . '" type="text/javascript"></script>');
				ee()->session->cache[DEFAULT_MAP]['JS'][$file] = TRUE;
			}
		}

		// -----------------------------------------
		// Javascript Inline
		// -----------------------------------------
		if ($type == 'js_inline')
		{
			ee()->cp->add_to_foot('<script type="text/javascript">'.$file.'</script>');
			
		}
	}

	// ----------------------------------------------------------------------

	/**
	 * Create url title
	 */
	public static function create_uri($uri = '', $replace_with = '-')
	{
		return preg_replace("#[^a-zA-Z0-9_\-]+#i", $replace_with, strtolower($uri));
	}

	// ----------------------------------------------------------------------
     
    /**
     * Anonymously report EE & PHP versions used to improve the product.
     */
    public static function stats($overide = array())
    {
        if (
            ee()->webservice_settings->item('report_stats') != 0 &&
            function_exists('curl_init') &&
            ee()->webservice_settings->item('report_date') <  ee()->localize->now)
        {
            $data = http_build_query(array(
                // anonymous reference generated using one-way hash
                'hash' => isset($overide['hash']) ? $overide['hash'] : md5(ee()->webservice_settings->item('license_key').ee()->webservice_settings->item('site_url')),
                'license' => isset($overide['license']) ? $overide['license'] : ee()->webservice_settings->item('license_key'),
                'product' => isset($overide['product']) ? $overide['product'] : WEBSERVICE_NAME,
                'version' => isset($overide['version']) ? $overide['version'] : WEBSERVICE_VERSION,
                'ee' => APP_VER,
                'php' => PHP_VERSION,
                'time' => ee()->localize->now,
            ));
            ee()->load->library('curl');
            ee()->curl->simple_post(WEBSERVICE_STATS_URL, $data);
            //ee()->curl->debug();

            // report again in 7 days
            ee()->webservice_settings->save_setting('report_date', ee()->localize->now + 7*24*60*60);
        }
    }

    // ----------------------------------------------------------------------
	
	/**
	 * Simple license check.
	 *
	 * @access     private
	 * @return     bool
	 */
	public static function license_check()
	{
		$is_valid = FALSE;

		$valid_patterns = array(
			'/^[0-9a-f]{8}\-[0-9a-f]{4}\-[0-9a-f]{4}\-[0-9a-f]{4}\-[0-9a-f]{12}$/' // devot-ee.com
		);

		foreach ($valid_patterns as $pattern)
		{
			if (preg_match($pattern, ee()->webservice_settings->item('license_key')))
			{
				$is_valid = TRUE;
				break;
			}
		}

		return $is_valid;
	}

	// ----------------------------------------------------------------------
	 	
	/**
	 * encode data
	 */
	public static function encode_data($str = '')
	{
		if(is_array($str))
		{
			$str = serialize($str);	
		}

		ee()->load->library('encrypt');
		$str = ee()->encrypt->encode($str);
		
		return $str;
	}

	// ----------------------------------------------------------------------
	 	
	/**
	 * encode data
	 */
	public static function decode_data($str = '')
	{
		ee()->load->library('encrypt');
		$str = ee()->encrypt->decode($str);

		if(self::is_serialized($str))
		{
			$str = unserialize($str);	
		}
		
		return $str;
	}

	// --------------------------------------------------------------------
        
    /**
     * add a hook
     */
    public static function add_hook($hook = '', $data = array(), $end_script = false, $extra_param2 = null, $extra_param3 = null)
    {
        if ($hook && ee()->extensions->active_hook(WEBSERVICE_MAP.'_'.$hook) === TRUE)
        {
        	//call the extension
            $data = ee()->extensions->call(WEBSERVICE_MAP.'_'.$hook, $data, $extra_param2, $extra_param3);
            
            //end of script?
            if($end_script)
            {
            	if (ee()->extensions->end_script === TRUE) return;
            }
        }
        
        return $data;
    }

    
	
	
} // END CLASS

/* End of file default_helper.php  */
/* Location: ./system/expressionengine/third_party/default/libraries/default_helper.php */