<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Testing tools
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

class Webservice_testing_tool
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		ee()->webservice_model->insert_user_agent();
		ini_set( 'soap.wsdl_cache_enabled', 0);
		//return $this->init();
	}

	// ----------------------------------------------------------------------
	
	/**
	 * Insert the settings to the database
	 *
	 * @param none
	 * @return void
	 */
	public function init()
	{
		//set return var
		$return = '';

		//what type we have to serve
		if(isset($_POST['type']) && isset($_POST['method']) && isset($_POST['path']))
		{
			switch($_POST['type'])
			{
				case 'soap': $return = $this->soap($_POST['method']); break;
				case 'xmlrpc': $return = $this->xmlrpc($_POST['method']); break;
				case 'rest': $return = $this->rest($_POST['method']); break;
				case 'custom': $return = $this->custom($_POST['method']); break;
			}
		}

		return $return;	
	}

	// ----------------------------------------------------------------------
	
	/**
	 * Insert the settings to the database
	 *
	 * @param none
	 * @return void
	 */
	public function soap($type = '')
	{
		$reponse = '';

		$client = new SoapClient(ee()->input->post('path'), array('trace' => 1));

		try
		{
			$reponse = $client->{$_POST['method']}(array(
				'session_id' => ee()->session->userdata('session_id')
			), $this->format_data());

		}
		catch (Exception $e)
		{
			return array(
				//$e,
				$client->__getLastRequestHeaders(),
				$client->__getLastRequest(),
				$client->__getLastResponseHeaders(),
				$client->__getLastResponse()
			);
		}	

		return $reponse;
	}

	// ----------------------------------------------------------------------
	
	/**
	 * Insert the settings to the database
	 *
	 * @param none
	 * @return void
	 */
	public function xmlrpc($type = '')
	{
		include("xmlrpc/xmlrpc.inc");

		$c = new xmlrpc_client(ee()->input->post('path'));
		//$c->debug = true;

		$x = new xmlrpcmsg($_POST['method'], array(
			php_xmlrpc_encode(array(
				'session_id' => ee()->session->userdata('session_id')
			)),
			php_xmlrpc_encode($this->format_data()),
		));

		$c->return_type = 'phpvals';
		$r =$c->send($x);

		return $r;
	}

	// ----------------------------------------------------------------------
	
	/**
	 * Insert the settings to the database
	 *
	 * @param none
	 * @return void
	 */
	public function rest($type = '')
	{
		//include(PATH_THIRD.'webservice/client_example/includes/rest_lib/rest_curl_client.php');

		ee()->load->library('curl');
		ee()->curl->option('FAILONERROR', false); 
		ee()->curl->create(ee()->input->post('path'));

		//http auth
		if(ee()->input->post('rest_http_auth') == "yes")
		{
			//echo 1;
			ee()->curl->http_login(ee()->input->post('username'), ee()->input->post('password'));
			$normal_auth = array();
		}
		else
		{
			$normal_auth = array(
				'auth' => array(
					'session_id' => ee()->session->userdata('session_id')
				)
			);
		}

		$reponse = '';

		$data = array_merge(array(
			'data' => $this->format_data()
		), $normal_auth);

		ee()->curl->post(http_build_query($data));

		//ee()->curl->option(CURLINFO_HEADER_OUT, true);
		$return = ee()->curl->execute();
		//print_r(ee()->curl->info);
		//ee()->curl->debug();exit;
		return $return;
	}

	// ----------------------------------------------------------------------
	
	/**
	 * Insert the settings to the database
	 *
	 * @param none
	 * @return void
	 */
	public function custom($type = '')
	{
		ee()->load->library('webservice_public_methods');

		$reponse = '';
		
		$extra_array = isset($_POST['extra']) && $_POST['extra'] != '' ? eval("return ".$_POST['extra']) : array() ;

		$reponse = ee()->webservice_public_methods->{$_POST['method']}(array(
			'session_id' => ee()->session->userdata('session_id')
		), $this->format_data());

		return $reponse;
	}

	function format_data()
	{
		$data = array(
			'site_id' => ee()->config->item('site_id')
		);

		$extra = isset($_POST['field:extra']) && $_POST['field:extra'] != '' ? eval("return ".$_POST['field:extra']) : array() ;
		unset($_POST['field:extra']);

		foreach($_POST as $key=>$val)
		{
			if(preg_match('/field:/', $key, $match))
			{
				$key_correctly = str_replace($match[0], '', $key);
				$data[$key_correctly] = ee()->input->post($key);
			}
		}

		//attach extra data
		return array_merge($data, $extra);
	}
}