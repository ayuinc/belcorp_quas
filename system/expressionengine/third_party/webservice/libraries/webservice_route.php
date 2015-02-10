<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Webservice route
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

class Webservice_route
{
	/*
	*	EE instance
	*/
	private $EE;
	
	/**
	 * Constructor
	 */
	public function __construct($type = '')
	{		
		//get the instance
		//$this->EE =& get_instance();
				
		switch($type)
		{
			//SOAP service
			case 'soap':
				include_once PATH_THIRD .'webservice/libraries/services/webservice_soap.php';
				$this->soap = new Webservice_soap();
			break;
			
			//XML-RPC service
			case 'xmlrpc':
				include_once PATH_THIRD .'webservice/libraries/services/webservice_xmlrpc.php';
				$this->xmlrpc = new Webservice_xmlrpc();
			break;
			
			//REST services
			case 'rest': 
				include_once PATH_THIRD .'webservice/libraries/services/webservice_rest.php';
				$this->rest = new Webservice_rest();
			break;

			//Test the services
			case 'test': 
				include_once PATH_THIRD .'webservice/tests/webservice_test.php';
				$this->test = new Webservice_test();
			break;
		}
		
	}
}

/* End of file webservice_server_helper.php */
/* Location: /system/expressionengine/third_party/webservice/libraries/webservice_route.php */