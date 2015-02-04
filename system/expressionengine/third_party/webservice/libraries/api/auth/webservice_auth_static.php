<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Static Auth API
 *
 * @package		Webservice
 * @category	Modules
 * @author		Rein de Vries <info@reinos.nl>
 * @link		http://reinos.nl/add-ons/webservice
 * @license  	http://reinos.nl/add-ons/commercial-license
 * @copyright 	Copyright (c) 2014 Reinos.nl Internet Media
 */

/**
 * Include the config file
 */
require_once PATH_THIRD.'webservice/config.php';

class Webservice_auth_static
{	

	//-------------------------------------------------------------------------

	/**
	 * authenticate_username method
	 * @param array $auth
	 * @param array $data
	 * @return
	 */
	public static function authenticate_username($data = array(), $type = '')
	{
		//load the entry class
		ee()->load->library('api/auth/webservice_auth');

		//post the data to the service
		$return_data = ee()->webservice_auth->authenticate_username($data);

		//var_dump($return_data);exit;
		if($type == 'soap')
		{
			if(isset($return_data['data']))
			{
				$return_data['data'] = webservice_format_soap_data($return_data['data'], 'entry_list');
			}
		}

		//format the array, because we cannot do nested arrays
		if($type != 'rest' && isset($return_data['data']))
		{
			$return_data['data'] = webservice_format_data($return_data['data'], $type);
		}
//file_put_contents('test.txt', print_r($return_data, true));
		//return result
		return $return_data;
	}

	//-------------------------------------------------------------------------

	/**
     * authenticate_username method
    */
	 public static function authenticate_email($data = array(), $type = '')
	 {
	 	//load the entry class
	 	ee()->load->library('api/auth/webservice_auth');

	 	//post the data to the service
	 	$return_data = ee()->webservice_auth->authenticate_email($data, $type);

	 	//unset the response txt
	 	unset($return_data['response']);

	 	//return result
	 	return $return_data;
	 }

	//-------------------------------------------------------------------------

	/**
     * authenticate_username method
    */
	 public static function authenticate_member_id($data = array(), $type = '')
	 {
	 	//load the entry class
	 	ee()->load->library('api/auth/webservice_auth');

	 	//post the data to the service
	 	$return_data = ee()->webservice_auth->authenticate_member_id($data, $type);

	 	//unset the response txt
	 	unset($return_data['response']);

	 	//return result
	 	return $return_data;
	 }

	//-------------------------------------------------------------------------

	/**
     * authenticate_username method
    */
	 public static function authenticate_session_id($data = array(), $type = '')
	 {
	 	//load the entry class
	 	ee()->load->library('api/auth/webservice_auth');

	 	//post the data to the service
	 	$return_data = ee()->webservice_auth->authenticate_session_id($data, $type);

	 	//unset the response txt
	 	unset($return_data['response']);

	 	//return result
	 	return $return_data;
	 }
}