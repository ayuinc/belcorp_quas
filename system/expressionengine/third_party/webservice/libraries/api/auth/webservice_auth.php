<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Auth API
 *
 * @package		webserviceAPI
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

class Webservice_auth
{
	//-------------------------------------------------------------------------

	/**
     * Constructor
    */
	public function __construct()
	{
		//include_once PATH_THIRD.'webservice/libraries/webservice_base_api.php';
		//require the default settings
		require PATH_THIRD.'webservice/settings.php';
	}

	//-------------------------------------------------------------------------

	/**
	 * authenticate_username
	 * @param $auth
	 * @param $data
	 * @return array
	 */
	public function authenticate_username($data)
	{
		$base_api = new webservice_base_api();
		$new_session = isset($data['new_session']) ? true : false;
		$ret_auth = $base_api->auth($data, $new_session);

		if(!$ret_auth['success'])
		{
			return array(
				'message' => 'cannot auth with given data',
			);
		}

		unset($ret_auth['success']);

		return array(
			'message' => 'successfully auth',
			'success' => true,
			'data' => array($ret_auth)
		);
	}

	//-------------------------------------------------------------------------

	/**
     * authenticate_email
    */
	 public function authenticate_email($data = array())
	 {
	 }

	//-------------------------------------------------------------------------

	/**
     * authenticate_member_id
    */
//	 public function authenticate_member_id($data = array())
//	 {
//	 	$base_api = new webservice_base_api();
//		$new_session = isset($data['create_new_session']) ? true : false;
//		$auth = $base_api->auth($data, $new_session);
//	 	return $base_api->auth_data;
//	 }

	//-------------------------------------------------------------------------

	/**
     * authenticate_session_id
    */
//	 public function authenticate_session_id($data = array())
//	 {
//	 	$base_api = new webservice_base_api();
//		$new_session = isset($data['create_new_session']) ? true : false;
//		$auth = $base_api->auth($data, $new_session);
//	 	var_dump($base_api->error_str);
//	 	//return $base_api->auth_data;
//	 }

}

