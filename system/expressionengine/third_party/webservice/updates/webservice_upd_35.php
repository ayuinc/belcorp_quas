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
 
class Webservice_upd_35
{
	private $EE;
	private $version = '3.5';
	
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
		$sql[] = "ALTER TABLE `".ee()->db->dbprefix."entry_api_logs` ADD `total_queries` INT(2) NOT NULL AFTER `msg`;";
		$sql[] = "ALTER TABLE `".ee()->db->dbprefix."entry_api_logs` ADD `queries` TEXT NOT NULL AFTER `msg`;";

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