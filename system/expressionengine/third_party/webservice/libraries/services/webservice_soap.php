<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Soap service class
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

class Webservice_soap
{	
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
	
	/*
	*	Soap server
	*/
	private $server;

	
	// ----------------------------------------------------------------------
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		//load the Classes
		ee()->load->library('webservice_lib');

		//load the nusoap server
		require_once(PATH_THIRD .'webservice/libraries/services/nusoap/nusoap.php');

		/** ---------------------------------
		/**  Create a soap service
		/** ---------------------------------*/
		$this->server = new soap_server();
		$this->server->setDebugLevel(100);
		
		/** ---------------------------------
		/**  Initialize WSDL support
		/** ---------------------------------*/
		$this->server->configureWSDL('EntryApi', 'urn:EntryApi', ee()->functions->create_url(ee()->webservice_settings->item('url_trigger').'/soap'));
		$namespace = "http://localhost/html/nusoap/index.php";

		// set our namespace
		$this->server->wsdl->schemaTargetNamespace = $namespace;
		
		/** ---------------------------------
		/**  create AssocativeArray support
		/** ---------------------------------*/		
		$this->server->wsdl->addComplexType(
			'Associative',
			'complexType',
			'struct',
			'all',
			'',
			array(
				'key' => array('name'=>'key','type'=>'xsd:string'),
				'value' => array('name'=>'value','type'=>'xsd:string')
			)
		);
		
		$this->server->wsdl->addComplexType(
			'AssociativeArray',
			'complexType',
			'array',
			'',
			'SOAP-ENC:Array',
			array(),
			array(
				array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:Associative[]')
			),
			'tns:Associative'
		);

		$this->server->wsdl->addComplexType(
			'ObjectList',
			'complexType',
			'array',
			'',
			'SOAP-ENC:Array',
			array(),
			array(
				array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:AssociativeArray[]')
			),
			'tns:AssociativeArray'
		);

		//get all the api settings
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

				$this->register_method($method->method, $method->name, '', $return_array);


				//bad!!?? but it works
				$dynamic_method = 'function ' . $method->method . "(\$auth = array(), \$data = array()) 
				{
					ee()->load->helper('webservice_helper');
					ee()->load->library('webservice_base_api');

					//load all the methods
					\$api_name = ee()->webservice_base_api->api_type = ee()->webservice_lib->search_api_method_class('{$method->method}');

					//get the api settings
					\$api_settings =  ee()->webservice_lib->get_api(\$api_name);

					//no settings, no api
					if(!\$api_settings)
					{
						//return response
						return new soap_fault('Client', '', 'API does not exist');
					}

					//set the class
					\$class = 'webservice_'.\$api_name.'_static';

					//load from the webservice packages
					if(strstr(\$api_settings->path, 'webservice/libraries/api/') != false)
					{
						//check if the file exists
						if(!file_exists(\$api_settings->path.'/'.\$class.'.php'))
						{
							//return response
							return new soap_fault('Client', '', 'API does not exist');
						}

						//load the api class
						ee()->load->library('api/'.\$api_name.'/'.\$class);
					}

					//we deal with a third party api for the webservice
					else
					{
						//set the class
						\$class = 'webservice_'.\$api_name.'_api_static';

						//check if the file exists
						if(!file_exists(\$api_settings->path.'/libraries/'.\$class.'.php'))
						{
							//return response
							return new soap_fault('Client', '', 'API does not exist');
						}

						//load the package path
						ee()->load->add_package_path(\$api_settings->path.'/');
						//load the api class
						ee()->load->library(\$class);
					}

					// check if method exists
					if (!method_exists(ucfirst(\$class), '{$method->method}'))
					{
						return new soap_fault('Client', '', 'Method does not exist');
					}

					/** ---------------------------------------
					/** From here we do some Specific things
					/** ---------------------------------------*/

			        \$error_auth = false;
			        \$return_data = array(
			            'message'           => '',
			            'code_http'         => 200,
			            'success'			=> false
			        );
						
					//set the site_id
					\$site_id = \$vars['data']['site_id'] = isset(\$vars['data']['site_id']) ? \$vars['data']['site_id'] : 1;

			        //if the api needs to be auth, do it here
			        if(isset(\$api_settings->auth) && (bool) \$api_settings->auth)
			        {
			            /** ---------------------------------------
			            /**  Run some default checks
			            /**  if the site id is given then switch to that site, otherwise use site_id = 1
			            /** ---------------------------------------*/
			            \$default_checks = ee()->webservice_base_api->default_checks(\$auth, '{$method->method}', \$site_id);
			
			            if( ! \$default_checks['succes'])
			            { 
			                \$error_auth = true;
			                \$return_data = array_merge(\$return_data, \$default_checks['message']);
			            }
			        }

			        //then the first array is not auth but data
			        else
			        {
			        	\$data = \$auth;
			        }

		         	if(\$error_auth === false)
			        {
	                    //call the method
			            \$result = call_user_func(array(\$class, '{$method->method}'), \$data, 'soap');

			            //unset the response txt
			            unset(\$result['response']);

			            //merge with default values
			            \$return_data = array_merge(\$return_data, \$result);
			        }

			        //add a log
			        ee()->webservice_model->add_log(ee()->session->userdata('username'), \$return_data, '{$method->method}', 'soap', ee()->webservice_base_api->servicedata);

			        //unset the http code
			        unset(\$return_data['code_http']);

					//convert success value to int
					\$return_data['success'] = (int)\$return_data['success'];

			        //return result
			        return \$return_data;
				}";
				//file_put_contents('test.txt', print_r($dynamic_method, true), FILE_APPEND);
				eval($dynamic_method);
			}
		}

		/** ---------------------------------
		/**  Use the request to (try to) invoke the service
		/** ---------------------------------*/
		$HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '';
		$this->server->service(file_get_contents('php://input'));

	}

	// ----------------------------------------------------------------------
	
	/**
	 * Register new methods for the service
	 *
	 * @param none
	 * @return void
	 */
	private function register_method($method_name, $description, $input = array(), $output = array())
	{
		//input
		if(empty($input))
		{
			$input = array(
				'auth' => 'xsd:struct',
				'data' => 'xsd:struct'
			);
		}
		
		//input
		$output = array_merge(array(
			'success' => 'xsd:int',
			'message' => 'xsd:string',
			'metadata' => 'tns:AssociativeArray'
		), $output);

		$this->server->register($method_name,
			$input, 						// input parameters      
			$output,  	 					// output parameters   
			'urn:EntryApi', 				// namespace
			'urn:EntryApi#'.$method_name, 	// soapaction
			'rpc', 							// style
			'encoded',					 	// use
			$description 					// documentation
		);
	}
}

/* End of file webservice_xmlrpc.php */
/* Location: /system/expressionengine/third_party/webservice/libraries/webservice_xmlrpc.php */