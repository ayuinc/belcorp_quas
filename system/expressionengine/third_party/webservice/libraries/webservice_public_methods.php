<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Public methods
 *
 * This file has one purpose, expose the methods on the CP without any services arround
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
require_once PATH_THIRD.'webservice/config.php';

class Webservice_public_methods
{

	// ----------------------------------------------------------------------
	
	/**
	 * Constructor
	 */
	public function __construct()
	{	
		ee()->load->library('webservice_lib');
		
		//load the helper
		ee()->load->helper('webservice_helper');
        ee()->load->library('webservice_base_api');
	}

    // --------------------------------------------------------------------
        
    /**
     * Dynamic calling
     */
    public function __call($name, $arguments)
    {
        return $this->method($name, $arguments);
    }

	// --------------------------------------------------------------------
        
    /**
     * Call Method
     */
    public function method($method = '', $data = array(), $inlog = array())
    {
		//load all the methods
		$api_name = ee()->webservice_base_api->api_type = ee()->webservice_lib->search_api_method_class($method);

		//get the api settings
		$api_settings =  ee()->webservice_lib->get_api($api_name);

		//no settings, no api
		if(!$api_settings)
		{
			//return response
			return array(
				'message' => 'API does not exist'
			);
		}

		//set the class
		$class = 'webservice_'.$api_name.'_static';

		//load from the webservice packages
		if(strstr($api_settings->path, 'webservice/libraries/api/') != false)
		{
			//check if the file exists
			if(!file_exists($api_settings->path.'/'.$class.'.php'))
			{
				//return response
				return array(
					'message' => 'API does not exist'
				);
			}

			//load the api class
			ee()->load->library('api/'.$api_name.'/'.$class);
		}

		//we deal with a third party api for the webservice
		else
		{
			//set the class
			$class = 'webservice_'.$api_name.'_api_static';

			//check if the file exists
			if(!file_exists($api_settings->path.'/libraries/'.$class.'.php'))
			{
				//return response
				return array(
					'message' => 'API does not exist'
				);
			}

			//load the package path
			ee()->load->add_package_path($api_settings->path.'/');
			//load the api class
			ee()->load->library($class);
		}

		// check if method exists
		if (!method_exists(ucfirst($class), $method))
		{
			return array(
				'message' => 'Method does not exist'
			);
		}

		/** ---------------------------------------
		/** From here we do some Specific things
		/** ---------------------------------------*/

        //get the paramaters
        $parameters = $data;
        
        $error_auth = false;
        $return_data = array(
			'message'           => '',
			'code_http'         => 200,
			'success'			=> false
        );

        //set the site_id
        $site_id = $vars['data']['site_id'] = isset($vars['data']['site_id']) ? $vars['data']['site_id'] : 1;

        //if the api needs to be auth, do it here
        if(isset($api_settings->auth) && (bool) $api_settings->auth)
        {
            /** ---------------------------------------
            /**  Run some default checks
            /**  if the site id is given then switch to that site, otherwise use site_id = 1
            /** ---------------------------------------*/
            $default_checks = ee()->webservice_base_api->default_checks($parameters[0], $method, $site_id);
     
            if( ! $default_checks['succes'])
            { 
                $error_auth = true;
                $return_data = array_merge($return_data, $default_checks['message']);
            }
        }

        if($error_auth === false)
        {
            //call the method
            $result = call_user_func(array($class, $method), $parameters[1], 'xmlrpc');

            //unset the response txt
            unset($result['response']);      

            //merge with default result
            $return_data = array_merge($return_data, $result);      
        }

        //add a log
        ee()->webservice_model->add_log(ee()->session->userdata('username'), $return_data, $method, 'xmlrpc', ee()->webservice_base_api->servicedata);
        
        //unset the http code
        if(isset($return_data['code_http']))
        {
            $http_code = $return_data['code_http'];
            unset($return_data['code_http']);
        }
        
        //return
        return $return_data;
    }
	
}