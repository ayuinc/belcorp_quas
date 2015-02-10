<?php
/**
 * the settings for the module
 *
 * @package		webservice
 * @category	Modules
 * @author		Rein de Vries <info@reinos.nl>
 * @link		http://reinos.nl
 * @license  	http://reinos.nl/add-ons/commercial-license
 * @copyright 	Copyright (c) 2014 Reinos.nl Internet Media
 */

//updates
$this->updates = array(
//	'2.1',
//	'2.2',
//	'3.0',
//	'3.4.1',
//	'3.5',
//	'4.0'
	'4.1'
);

//service methods
$this->services = array(
	'soap' => 'SOAP',
	'xmlrpc' => 'XML-RPC',
	'rest' => 'REST',
	'custom' => 'Custom'
);

//service api
// $this->apis = array(
// 	'entry' => 'Entry',
// 	'category' => 'Category',
// 	'category_group' => 'Category Group',
// 	'channel' => 'Channel',
// 	//'member_group' => 'Member Group',
// 	//'member' => 'Member',
// 	//'settings' => 'Settings',
// 	//'field' => 'Field'
// );

//enabled disables
$this->service_active = array(
	'1' => 'Active',
	'0' => 'Inactive',
);

//enabled disables
$this->service_logging = array(
	'2' => 'All messages',
	'1' => 'Success calls only',
	'0' => 'Nothing',
);

//Debug
$this->service_debug = array(
	'1' => 'Yes',
	'0' => 'No',
);

//Free apis
// $this->free_apis = array(
// 	'read_entry' => 'Read Entry',
// 	'search_entry' => 'Search_entry',
// 	//'read_category' => 'Read Category',
// 	//'read_category_group' => 'Read Category Group',
// );

//Default Post
$this->default_post = array(
	'license_key'   				=> '',
	'report_date' 					=> time(),
	'report_stats' 					=> true,
	'debug'   						=> true,
	'no_inlog_channels' 			=> '',
	'tmp_dir'						=> PATH_THIRD.'webservice/_tmp',
	'ip_blacklist'					=> '',
	//'ip_whitelist'				=> '',
	'free_apis'						=> serialize(array('')),
	'rest_auth' 					=> 'none',
	'url_trigger'					=> 'webservice',
	'super_admin_key'				=> '',
	'rest_output_header'			=> '',
	'site_id_strict'				=> false,
	'testing_tool_url'				=> 'http://'.$_SERVER['SERVER_NAME']
);	

// $this->rest_auths = array(
// 	'none',
// 	'basic',
// 	'digest',
// 	'rest_ip_whitelist_enabled'
// );

//set a secret auth token
//$this->secret_auth_token = md5(ee()->config->item('site_url'));

//overrides
$this->overide_settings = array(
	//'gmaps_icon_dir' => '[theme_dir]images/icons/',
	//'gmaps_icon_url' => '[theme_url]images/icons/',
);

// Backwards-compatibility with pre-2.6 Localize class
$this->format_date_fn = (version_compare(APP_VER, '2.6', '>=')) ? 'format_date' : 'decode_date';

//mcp veld header
$this->logs_table_headers = array(
	WEBSERVICE_MAP.'_log_id' => array('data' => lang(WEBSERVICE_MAP.'_log_id'), 'style' => 'width:10%;'),
	WEBSERVICE_MAP.'_time' => array('time' => lang(WEBSERVICE_MAP.'_time'), 'style' => 'width:40%;'),
	WEBSERVICE_MAP.'_username' => array('data' => lang(WEBSERVICE_MAP.'_username'), 'style' => 'width:40%;'),
	WEBSERVICE_MAP.'_ip' => array('data' => lang(WEBSERVICE_MAP.'_ip'), 'style' => 'width:40%;'),
	WEBSERVICE_MAP.'_service' => array('data' => lang(WEBSERVICE_MAP.'_service'), 'style' => 'width:40%;'),
	WEBSERVICE_MAP.'_method' => array('data' => lang(WEBSERVICE_MAP.'_method'), 'style' => 'width:40%;'),
	WEBSERVICE_MAP.'_msg' => array('data' => lang(WEBSERVICE_MAP.'_msg'), 'style' => 'width:40%;'),
	WEBSERVICE_MAP.'_show_queries' => array('data' => lang(WEBSERVICE_MAP.'_show_queries'), 'style' => 'width:10%;'),
);

//mcp veld header
$this->api_keys_table_headers = array(
	//WEBSERVICE_MAP.'_api_key_id' => array('data' => lang(WEBSERVICE_MAP.'_api_key_id'), 'style' => 'width:10%;'),
	WEBSERVICE_MAP.'_api_key' => array('data' => lang(WEBSERVICE_MAP.'_api_key'), 'style' => 'width:80%;'),
	WEBSERVICE_MAP.'_edit' => array('data' => lang(WEBSERVICE_MAP.'_edit'), 'style' => 'width:10%;'),
);

$this->fieldtype_settings = array(
	array(
		'label' => lang('license'),
		'name' => 'license',
		'type' => 't', // s=select, m=multiselect t=text
		//'options' => array('No', 'Yes'),
		'def_value' => '',
		'global' => true, //show on the global settings page
	),

);

//the service errors
$this->service_error = array(
	//success
	'succes_create' => array(
		'response' 			=> 'ok',	
		'message'			=> 'Created successfully',
		//'code'				=> 200,
		'code_http'			=> 200,
	),

	'succes_read' => array(
		'response' 			=> 'ok',
		'message'			=> 'Successfully readed',
		//'code'				=> 200,
		'code_http'			=> 200,
	),

	'succes_update' => array(
		'response' 			=> 'ok',
		'message'			=> 'Successfully updated',
		//'code'				=> 200,
		'code_http'			=> 200,
	),

	'succes_delete' => array(
		'response' 			=> 'ok',
		'message'			=> 'Successfully deleted',
		//'code'				=> 200,
		'code_http'			=> 200,
	),

	'succes_auth' => array(
		'message'			=> 'Auth success',
		//'code'				=> 200,
		'code_http'			=> 200,
	),

	//-------------------------------------------------------------
	
	//errors API/Services
	'error_access' => array(
		'message'			=> 'You are not authorized to use this service',
		//'code'				=> 5201,
		'code_http'			=> 200,
	),
	
	'error_inactive' => array(
		'message'			=> 'Service is not running',
		//'code'				=> 5202,
		'code_http'			=> 200,
	),
	
	'error_api' => array(
		//'code'				=> 5203, //general api error
		'code_http'			=> 200,
	),

	'error_api_type' => array(
		'message'			=> 'This API is not active for this services',
		//'code'				=> 5204,
		'code_http'			=> 200,
	),

	'error_api_ip' => array(
		'message'			=> 'This IP ('.$_SERVER['REMOTE_ADDR'].') has no access',
		//'code'				=> 5205,
		'code_http'			=> 200,
	),
	'error_auth' => array(
		'message'			=> 'Auth error',
		//'code'				=> 5206,
		'code_http'			=> 200,
	),
	'error_license' => array(
		'message'			=> 'Oeps! The '.WEBSERVICE_NAME.' has an incorrect License. Grab a license from devote:ee and fill the license in the CP',
		//'code'				=> 5207,
		'code_http'			=> 200,
	),
);

$this->content_types = array(
	'json' => 'application/json',
	'xml' => 'text/xml',
	'array' => 'php/array',
	'default' => 'text/html',
);

/* End of file settings.php */
/* Location: /system/expressionengine/third_party/webservice/settings.php */