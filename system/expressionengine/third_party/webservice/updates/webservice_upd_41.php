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
 
class Webservice_upd_41
{
	private $EE;
	private $version = '4.1';
	
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
		$sql[] = "ALTER TABLE `".ee()->db->dbprefix."webservice_logs` CHANGE `queries` `queries` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;";
		$sql[] = "ALTER TABLE `".ee()->db->dbprefix."webservice_logs` CHANGE `data` `data` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;";

		foreach ($sql as $query)
		{
			ee()->db->query($query);
		}
	}
}