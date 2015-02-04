<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * XMLRPC service class
 *
 * @package		webservice
 * @category	Modules
 * @author		Rein de Vries <info@reinos.nl>
 * @license  	http://reinos.nl/add-ons/commercial-license
 * @link        http://reinos.nl/add-ons//add-ons/entry-api
 * @copyright 	Copyright (c) 2014 Reinos.nl Internet Media
 */

/**
 * Include the config file
 */
require_once PATH_THIRD.'webservice/config.php';

class Webservice_xmlrpc
{
	/*
	*	EE instance
	*/
	private $EE;
	
	/*
	*	The username
	*/
	public $username;
	
	/*
	*	the password
	*/
	public $password;
	
	/*
	*	The postdata
	*/
	public $post_data;
	
	/*
	*	the channel
	*/
	public $post_data_channel;

	
	// ----------------------------------------------------------------------
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		//load the Classes
		//$this->EE =& get_instance();
		ee()->load->helper('security');  	
			
		/** ---------------------------------
		/**  Load the XML-RPC Files
		/** ---------------------------------*/
		ee()->load->library('xmlrpc');
		ee()->load->library('xmlrpcs');
		
		/* ---------------------------------
		/*  Specify Functions
		/* ---------------------------------*/
		$functions = array();

		$apis = ee()->webservice_lib->load_apis();
		foreach($apis['apis'] as $api)
		{
			foreach($api->methods as $method)
			{

				$return_array = array();
				foreach($method->soap as $val)
				{
					$return_array[$val->name] = $val->type;
				}

				$functions[$method->method] = array('function' => 'Webservice_xmlrpc.call_method');

			}
		}

		/** ---------------------------------
		/**  Instantiate the Server Class
		/** ---------------------------------*/
		//ee()->xmlrpc->set_debug(TRUE);
		ee()->xmlrpcs->initialize(array('functions' => $functions, 'object' => $this, 'xss_clean' => FALSE));
		ee()->xmlrpcs->serve();
		die();
	}

	// ----------------------------------------------------------------------
	
	/**
	 * call the method
	 *
	 * @param none
	 * @return void
	 */
	public function call_method($request)
	{
		ee()->load->helper('webservice_helper');
		ee()->load->library('webservice_base_api');

		$return_data = array(
			'message'           => '',
			'code_http'         => 200,
			'success'			=> false
		);

		//load all the methods
		$api_name = ee()->webservice_base_api->api_type = ee()->webservice_lib->search_api_method_class($request->method_name);

		//get the api settings
		$api_settings =  ee()->webservice_lib->get_api($api_name);

		//no settings, no api
		if(!$api_settings)
		{
			return $this->response(array_merge($return_data, array(
				'message' => 'API does not exist'
			)));
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
				return $this->response(array_merge($return_data, array(
					'message' => 'API does not exist'
				)));
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
				return $this->response(array_merge($return_data, array(
					'message' => 'API does not exist'
				)));
			}

			//load the package path
			ee()->load->add_package_path($api_settings->path.'/');
			//load the api class
			ee()->load->library($class);
		}

		// check if method exists
		if (!method_exists(ucfirst($class), $request->method_name))
		{
			//return response
			return $this->response(array_merge($return_data, array(
				'message' => 'Method does not exist'
			)));
		}

		/** ---------------------------------------
		/** From here we do some Specific things
		/** ---------------------------------------*/

		//get the paramaters
		$parameters = $request->output_parameters();
		
		$error_auth = false;

        //set the site_id
        $site_id = $vars['data']['site_id'] = isset($vars['data']['site_id']) ? $vars['data']['site_id'] : 1;

        //if the api needs to be auth, do it here
        if(isset($api_settings->auth) && (bool) $api_settings->auth)
        {
            /** ---------------------------------------
            /**  Run some default checks
            /**  if the site id is given then switch to that site, otherwise use site_id = 1
            /** ---------------------------------------*/
            $default_checks = ee()->webservice_base_api->default_checks($parameters[0], $request->method_name, $site_id);
     
            if( ! $default_checks['succes'])
            { 
                $error_auth = true;
                $return_data = array_merge($return_data, $default_checks['message']);
            }
        }
		//then the first array is not auth but data
//		else
//		{
//			$parameters[1] = $parameters[0];
//		}

        if($error_auth === false)
        {
            //call the method
            $result = call_user_func(array($class, $request->method_name), $parameters[1], 'xmlrpc');

            //unset the response txt
            unset($result['response']);      

            //merge with default result
            $return_data = array_merge($return_data, $result);      
        }

        //add a log
        ee()->webservice_model->add_log(ee()->session->userdata('username'), $return_data, $request->method_name, 'xmlrpc', ee()->webservice_base_api->servicedata);
		
		//unset the http code
        if(isset($return_data['code_http']))
        {
            $http_code = $return_data['code_http'];
            unset($return_data['code_http']);
        }
        
        //return
        return $this->response($return_data, $http_code);
	}

	// ----------------------------------------------------------------------
	
	/**
	 * response
	 *
	 * @param none
	 * @return void
	 */
	public function response($result, $http_code = 200)
	{
		//good call?
		if($http_code == 200)
		{
			$response = array(
					array(
							'success'	=> array((int)$result['success'],'int'),
							'message'	=> array($result['message'],'string'),
							'metadata'	=> isset($result['metadata']) ? array($result['metadata'],'struct') : ''
						),
				'struct'
			);

			//is there an id returnend by an create invoke
			if(isset($result['id']))
			{
				$response[0]['id'] = array($result['id'],'string');
			}
			
			//grab the data and assing it to the response array
			if(!empty($result['data'])) 
			{
				$values = array();
				foreach($result['data'] as $key=>$entry)
				{
					$values[$key] = array($entry, 'struct');
				}

				$response[0]['data'] = array($values,'array');
			}

			//return data
			return ee()->xmlrpc->send_response($response);
		}
		//error?
		else
		{	
			return ee()->xmlrpc->send_error_message($result['code'], $result['message']);
		}
	}

	// ----------------------------------------------------------------------
	
}
/* End of file webservice_xmlrpc.php */
/* Location: /system/expressionengine/third_party/webservice/libraries/xmlrpc/webservice_xmlrpc.php */