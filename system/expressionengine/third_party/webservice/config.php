<?php
/**
 * Default config
 *
 * @package		webservice
 * @category	Modules
 * @author		Rein de Vries <info@reinos.nl>
 * @link		http://reinos.nl/add-ons/entry-api
 * @license  	http://reinos.nl/add-ons/commercial-license
 * @copyright 	Copyright (c) 2014 Reinos.nl Internet Media
 */

//contants
if ( ! defined('WEBSERVICE_NAME'))
{
	define('WEBSERVICE_NAME', 'Webservice');
	define('WEBSERVICE_CLASS', 'Webservice');
	define('WEBSERVICE_MAP', 'webservice');
	define('WEBSERVICE_VERSION', '4.1.2');
	define('WEBSERVICE_DESCRIPTION', 'Webservice (SOAP/XMLRPC/REST) for select, insert, update and delete entries (and many more)');
	define('WEBSERVICE_DOCS', 'http://reinos.nl/add-ons/webservice');
	define('WEBSERVICE_DEVOTEE', '');
	define('WEBSERVICE_AUTHOR', 'Rein de Vries');
	define('WEBSERVICE_DEBUG', false);
	define('WEBSERVICE_STATS_URL', 'http://reinos.nl/index.php/module_stats_api/v1');
}

//configs
$config['name'] = WEBSERVICE_NAME;
$config['version'] = WEBSERVICE_VERSION;

//load compat file
require_once(PATH_THIRD.WEBSERVICE_MAP.'/compat.php');

/* End of file config.php */
/* Location: /system/expressionengine/third_party/webservice/config.php */