<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Update file for the update to 2.2
 * Add a new field for the search fields
 *
 * @package		webservice
 * @category	Modules
 * @author		Rein de Vries <info@reinos.nl>
 * @link		http://reinos.nl
 * @license  	http://reinos.nl/add-ons/commercial-license
 * @copyright 	Copyright (c) 2014 Reinos.nl Internet Media
 */
 
include(PATH_THIRD.'webservice/config.php');
 
class Webservice_upd_40
{
	private $EE;
	private $version = '3.0';
	
	/**
	 * Construct method
	 *
	 * @return      boolean         TRUE
	 */
	public function __construct()
	{
		//get a intance of the EE object
		$this->EE = get_instance();
		
		//load the classes
		ee()->load->dbforge();
	}
	
	/**
	 * Run the update
	 *
	 * @return      boolean         TRUE
	 */
	public function run_update()
	{
		$sql = array();
				
		//ADD KEYS table
		$sql[] = "REPLACE INTO `".ee()->db->dbprefix."extensions` (`extension_id`, `class`, `method`, `hook`, `settings`, `priority`, `version`, `enabled`) VALUES (NULL, 'Entry_api_ext', 'entry_api_modify_search', 'entry_api_modify_search', '', '10', '3.6', 'y');";

		$sql[] = "
		CREATE TABLE IF NOT EXISTS `".ee()->db->dbprefix."entry_api_sessions` (
		`user_agent_id` int(7) unsigned NOT NULL,
		  `session_id` varchar(255) NOT NULL DEFAULT '',
		  `user_agent` varchar(200) NOT NULL DEFAULT '',
		  `timestamp` varchar(200) NOT NULL DEFAULT ''
		) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;
		";

		foreach ($sql as $query)
		{
			ee()->db->query($query);
		}

		// ee()->db->insert('extensions', array(
		// 	'class'		=> 'Entry_api_ext',
		// 	'method'	=> 'publisher_set_language',
		// 	'hook'		=> 'publisher_set_language',
		// 	'settings'	=> '',
		// 	'priority'	=> 10,
		// 	'version'	=> WEBSERVICE_VERSION,
		// 	'enabled'	=> 'y'
		// ));
	}
}