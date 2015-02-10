<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Webservice Extension helper
 *
 * @package		webservice
 * @category	Modules
 * @author		Rein de Vries <info@reinos.nl>
 * @link		http://reinos.nl/add-ons/entry-api
 * @license  	http://reinos.nl/add-ons/commercial-license
 * @copyright 	Copyright (c) 2014 Reinos.nl Internet Media
 */

/**
 * Include the config file
 */
require_once(PATH_THIRD.'webservice/config.php');

/**
 * Include helper
 */
require_once(PATH_THIRD.'webservice/libraries/webservice_helper.php');

class Webservice_lib
{
	private $default_settings;
	private $EE;

	public function __construct()
	{					
		//load model
		ee()->load->model(WEBSERVICE_MAP.'_model');

		//load the channel data
		ee()->load->driver('channel_data');

		//load the settings
		ee()->load->library(WEBSERVICE_MAP.'_settings');

		//load logger
		ee()->load->library('logger');

        //load helper
        ee()->load->helper('webservice_helper');
		
		//require the default settings
		require PATH_THIRD.WEBSERVICE_MAP.'/settings.php';
		
		// no time limit
		//set_time_limit(0);
			
		//check the tmp path
		ee()->load->helper('file');
		
		//create dir if not exists
		if(!is_dir(ee()->webservice_settings->item('tmp_dir')) && ee()->webservice_settings->item('tmp_dir') != '')
		{
			@mkdir(ee()->webservice_settings->item('tmp_dir'), 0777, true);
		}
		//chmod to write mode
		@chmod(ee()->webservice_settings->item('tmp_dir'), 0777);
		
		//set urls
		ee()->webservice_settings->set_setting('xmlrpc_url', reduce_double_slashes(ee()->config->item('site_url').ee()->config->item('site_index').'/webservice/xmlrpc'));
		ee()->webservice_settings->set_setting('soap_url', reduce_double_slashes(ee()->config->item('site_url').ee()->config->item('site_index').'/webservice/soap'));
		ee()->webservice_settings->set_setting('rest_url', reduce_double_slashes(ee()->config->item('site_url').ee()->config->item('site_index').'/webservice/rest'));

	}

	// --------------------------------------------------------------------
        
    /**
     * Has the user free access
     * User who exists has never free access
     * 0 = not free
     * 1 = no username, free access
     * 2 = inlog require, free access
     */
    public function has_free_access($method = '', $username = '')
    {
    	//user not exists, take the global settings
    	$user_exists = ee()->webservice_model->user_exists($username);

    	if($username == '' || $user_exists == false)
		{
			if(in_array($method, ee()->webservice_settings->item('free_apis')))
			{	
				return 1;
			}
			return 0;
		}
		else if($user_exists)
		{
			$member = ee()->webservice_model->get_member_based_on_username($username);
			if(in_array($method, explode('|', $member->free_apis)))
			{	
				return 2;
			}
			return 0;
		}

		return 0;
    }

    // --------------------------------------------------------------------
        
    /**
     * Load the apis based on their dir name
     */
    public function load_apis()
    {
		//get from cache
		if ( isset(ee()->session->cache[WEBSERVICE_MAP]['apis']))
		{
			return ee()->session->cache[WEBSERVICE_MAP]['apis'];
		}

    	$apis = array();

    	ee()->load->helper('file');
    	ee()->load->helper('directory');

    	$path = PATH_THIRD.'webservice/libraries/api';
		$dirs = directory_map($path);

		foreach ($dirs as $key=>$dir)
		{
			if(is_array($dir))
			{
				foreach($dir as $file)
				{
					 if($file == 'settings.json')
					 {
					 	$json = file_get_contents($path.'/'.$key.'/settings.json');
					 	$json = json_decode($json);
					 	$json->path = $path.'/'.$key;

                        //is enabled?
                        if(isset($json->enabled) && $json->enabled)
                        {
    					 	//set a quick array for the methods
    					 	$json->_methods = array();
    					 	foreach($json->methods as $method)
    					 	{
    					 		$json->_methods[$json->name] = $method->method;
    					 		$apis['_methods_class'][$method->method] = $json->name;
    					 	}

    					 	$apis['apis'][$json->name] = $json;
                        }
					 }
				}
			}		    
		}

		//also look in the other maps for webservice stuff
		$path = PATH_THIRD;
		$dirs = directory_map($path);

		foreach ($dirs as $key=>$dir)
		{
			if(is_array($dir))
			{
				foreach($dir as $file)
				{
					if($file == 'webservice_settings.json')
					{
						$json = file_get_contents($path.$key.'/webservice_settings.json');
						$json = json_decode($json);
						$json->path = $path.$key;

						//is enabled?
						if(isset($json->enabled) && $json->enabled && !isset($apis['apis'][$json->name]))
						{
							//set a quick array for the methods
							$json->_methods = array();
							foreach($json->methods as $method)
							{
								$json->_methods[$json->name] = $method->method;
								$apis['_methods_class'][$method->method] = $json->name;
							}

							$apis['apis'][$json->name] = $json;
						}
					}
				}
			}
		}

		//save as session
		ee()->session->cache[WEBSERVICE_MAP]['apis'] = $apis;

        return $apis;
    }

    // --------------------------------------------------------------------
        
    /**
     * Search for the api method
     */
    public function search_api_method_class($method = '')
    {
    	$apis = $this->load_apis();
    	if(isset($apis['_methods_class'][$method]))
    	{
    		return $apis['_methods_class'][$method];
    	}
    }

    // --------------------------------------------------------------------
        
    /**
     * Load the apis based on their dir name
     */
    public function get_api_names()
    {
    	$apis = $this->load_apis();

    	$return = array();
    	foreach($apis['apis'] as $val)
    	{
            if($val->public == false)
            {
                $return[$val->name] = $val->label.(isset($val->version) ? ' <small>(v'.$val->version.')</small>' : '');
            }
    	}

    	return $return;
    }

    // --------------------------------------------------------------------
        
    /**
     * Load the apis based on their dir name
     */
    public function get_api_free_names()
    {
    	$apis = $this->load_apis();

    	$return = array();
    	foreach($apis['apis'] as $val)
    	{
    		foreach($val->methods as $method)
    		{
    			if(isset($method->free_api) && $method->free_api)
    			{
    				$return[$method->method] = $val->name.'/'.$method->method;
    			}
    		}
    	}

    	return $return;
    }



    // --------------------------------------------------------------------
        
    /**
     * Load the apis based on their dir name
     */
    public function get_api($name = '')
    {
    	$apis = $this->load_apis();

    	if($name != '' && isset($apis['apis'][$name]))
    	{
    		return $apis['apis'][$name];
    	}

		return false;
    }

	// --------------------------------------------------------------------
        
    /**
     * Hook - allows each method to check for relevant hooks
     */
    public function activate_hook($hook='', $data=array())
    {
        if ($hook AND ee()->extensions->active_hook(DEFAULT_MAP.'_'.$hook) === TRUE)
        {
                $data = ee()->extensions->call(DEFAULT_MAP.'_'.$hook, $data);
                if (ee()->extensions->end_script === TRUE) return;
        }
        
        return $data;
    }
	
		
	// ----------------------------------------------------------------------
	
} // END CLASS

/* End of file webservice_lib.php  */
/* Location: ./system/expressionengine/third_party/webservice/libraries/webservice_lib.php */