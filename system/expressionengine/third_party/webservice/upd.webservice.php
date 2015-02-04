<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * UPD file
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
 
class Webservice_upd {
	
	public $version = WEBSERVICE_VERSION;
	
	private $EE;
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		//create a instance of the EE object
		//$this->EE =& get_instance();
		
		//load the classes
		ee()->load->dbforge();
		
		//require the settings
		require PATH_THIRD.'webservice/settings.php';
	}
	
	// ----------------------------------------------------------------
	
	/**
	 * Installation Method
	 *
	 * @return 	boolean 	TRUE
	 */
	public function install()
	{	
		if (strnatcmp(phpversion(),'5.3') <= 0) 
		{ 
			show_error('Webservice require PHP 5.3 or higher.', 500, 'Oeps!');
			return FALSE;
		}

		//set the module data
		$mod_data = array(
			'module_name'			=> WEBSERVICE_CLASS,
			'module_version'		=> WEBSERVICE_VERSION,
			'has_cp_backend'		=> "y",
			'has_publish_fields'	=> 'n'
		);
	
		//insert the module
		ee()->db->insert('modules', $mod_data);
		
		//create some actions for the ajax in the control panel
		$this->_register_action('ajax_cp');

		//install the extension
		$this->_register_hook('sessions_start', 'sessions_start');
		$this->_register_hook('webservice_modify_search', 'webservice_modify_search');
		
		//create the Login backup tables
		$this->_create_webservice_tables();

		//load the helper
		ee()->load->library('webservice_lib');
		
		//insert the settings data
		ee()->webservice_settings->first_import_settings();
		
		return TRUE;
	}

	// ----------------------------------------------------------------
	
	/**
	 * Uninstall
	 *
	 * @return 	boolean 	TRUE
	 */	
	public function uninstall()
	{
		//delete the module
		ee()->db->where('module_name', WEBSERVICE_CLASS);
		ee()->db->delete('modules');

		//remove databases
		ee()->dbforge->drop_table('webservice_services_settings');
		ee()->dbforge->drop_table('webservice_settings');
		ee()->dbforge->drop_table('webservice_keys');
		ee()->dbforge->drop_table('webservice_logs');
		ee()->dbforge->drop_table('webservice_sessions');
		
		//remove actions
		ee()->db->where('class', WEBSERVICE_CLASS);
		ee()->db->delete('actions');
		
		//remove the extension
		ee()->db->where('class', WEBSERVICE_CLASS.'_ext');
		ee()->db->delete('extensions');
		
		return TRUE;
	}
	
	// ----------------------------------------------------------------
	
	/**
	 * Module Updater
	 *
	 * @return 	boolean 	TRUE
	 */	
	public function update($current = '')
	{
		//nothing to update
		if ($current == '' OR $current == $this->version)
			return FALSE;
		
		//loop through the updates and install them.
		if(!empty($this->updates))
		{
			foreach ($this->updates as $version)
			{
				//$current = str_replace('.', '', $current);
				//$version = str_replace('.', '', $version);
				
				if ($current < $version)
				{
					$this->_init_update($version);
				}
			}
		}

		return true;
	}
		
	// ----------------------------------------------------------------
	
	/**
	 * Add the tables for the module
	 *
	 * @return 	boolean 	TRUE
	 */	
	private function _create_webservice_tables()
	{		
		// add webservice setting table
		$fields = array(
				'webservice_id'	=> array(
									'type'				=> 'int',
									'constraint'		=> 7,
									'unsigned'			=> TRUE,
									'null'				=> FALSE,
									'auto_increment'	=> TRUE,
								),
				'member_id'	=> array(
									'type'				=> 'int',
									'constraint'		=> 7,
									'unsigned'			=> TRUE,
									'null'				=> FALSE,
								),
				'membergroup_id'	=> array(
									'type'				=> 'int',
									'constraint'		=> 7,
									'unsigned'			=> TRUE,
									'null'				=> FALSE,
								),
				'services'  => array(
									'type' 				=> 'varchar',
									'constraint'		=> '255',
									'null'				=> FALSE,
									'default'			=> ''
								),
				'active'  => array(
									'type' 				=> 'int',
									'constraint'		=> '1',
									'null'				=> FALSE,
									'default'			=> 0
								),
				'logging'  => array(
									'type' 				=> 'int',
									'constraint'		=> '1',
									'null'				=> FALSE,
									'default'			=> 0
								),
				'debug'  => array(
									'type' 				=> 'int',
									'constraint'		=> '1',
									'null'				=> FALSE,
									'default'			=> 0
								),
				'apis'  => array(
									'type' 				=> 'varchar',
									'constraint'		=> '255',
									'null'				=> FALSE,
									'default'			=> ''
								),
				'free_apis'  => array(
									'type' 				=> 'varchar',
									'constraint'		=> '255',
									'null'				=> FALSE,
									'default'			=> ''
								),
				'search_fields'  => array(
									'type' 				=> 'text',
									'null'				=> TRUE,
								),
				'api_keys'  => array(
									'type' 				=> 'text',
									'null'				=> TRUE,
								),
				'data'  => array(
									'type' 				=> 'text',
									'null'				=> TRUE,
								),
		);
		
		//create the channel setting table
		ee()->dbforge->add_field($fields);
		ee()->dbforge->add_key('webservice_id', TRUE);
		ee()->dbforge->create_table('webservice_services_settings', TRUE);
	
		// add log tables
		$fields = array(
				'log_id'	=> array(
									'type'			=> 'int',
									'constraint'		=> 7,
									'unsigned'		=> TRUE,
									'null'			=> FALSE,
									'auto_increment'	=> TRUE
								),
				'site_id'  => array(
									'type'			=> 'int',
									'constraint'		=> 7,
									'unsigned'		=> TRUE,
									'null'			=> FALSE,
									'default'			=> 0
								),
				'username'  => array(
									'type' 			=> 'varchar',
									'constraint'		=> '255',
									'null'			=> FALSE,
									'default'			=> ''
								),
				'time'  => array(
									'type' 			=> 'varchar',
									'constraint'		=> '150',
									'null'			=> FALSE,
									'default'			=> ''
								),
				'service'  => array(
									'type' 			=> 'varchar',
									'constraint'		=> '255',
									'null'			=> FALSE,
									'default'			=> ''
								),
				'ip'  => array(
									'type' 			=> 'varchar',
									'constraint'		=> '255',
									'null'			=> FALSE,
									'default'			=> ''
								),
				'log_number'  => array(
									'type'			=> 'int',
									'constraint'		=> 7,
									'unsigned'		=> TRUE,
									'null'			=> FALSE,
									'default'			=> 0
								),
				'method'  => array(
									'type' 			=> 'varchar',
									'constraint'		=> '255',
									'null'			=> FALSE,
									'default'			=> ''
								),
				'msg'  => array(
									'type' 			=> 'varchar',
									'constraint'		=> '255',
									'null'			=> FALSE,
									'default'			=> ''
								),
				'total_queries'  => array(
									'type'			=> 'int',
									'constraint'		=> 7,
									'unsigned'		=> TRUE,
									'null'			=> FALSE,
									'default'			=> 0
								),
				'queries'  => array(
									'type' 				=> 'mediumtext',
									'null'				=> FALSE,
								),
				'data'  => array(
									'type' 				=> 'mediumtext',
									'null'				=> FALSE,
								),
		);
		
		//create the backup database
		ee()->dbforge->add_field($fields);
		ee()->dbforge->add_key('log_id', TRUE);
		ee()->dbforge->create_table('webservice_logs', TRUE);

		// add config tables
		$fields = array(
				'settings_id'	=> array(
									'type'			=> 'int',
									'constraint'		=> 7,
									'unsigned'		=> TRUE,
									'null'			=> FALSE,
									'auto_increment'	=> TRUE
								),
				'site_id'  => array(
									'type'			=> 'int',
									'constraint'		=> 7,
									'unsigned'		=> TRUE,
									'null'			=> FALSE,
									'default'			=> 0
								),
				'var'  => array(
									'type' 			=> 'varchar',
									'constraint'		=> '200',
									'null'			=> FALSE,
									'default'			=> ''
								),
				'value'  => array(
									'type' 			=> 'varchar',
									'constraint'		=> '255',
									'null'			=> FALSE,
									'default'			=> ''
								),
		);
		
		//create the backup database
		ee()->dbforge->add_field($fields);
		ee()->dbforge->add_key('settings_id', TRUE);
		ee()->dbforge->create_table('webservice_settings', TRUE);
		
		// add config tables
		$fields = array(
				'api_key_id'	=> array(
									'type'			=> 'int',
									'constraint'		=> 7,
									'unsigned'		=> TRUE,
									'null'			=> FALSE,
									'auto_increment'	=> TRUE
								),
				'site_id'  => array(
									'type'			=> 'int',
									'constraint'		=> 7,
									'unsigned'		=> TRUE,
									'null'			=> FALSE,
									'default'			=> 0
								),
				'webservice_id'  => array(
									'type'			=> 'int',
									'constraint'		=> 7,
									'unsigned'		=> TRUE,
									'null'			=> FALSE,
									'default'			=> 0
								),
				'api_key'  => array(
									'type' 			=> 'varchar',
									'constraint'		=> '200',
									'null'			=> FALSE,
									'default'			=> ''
								)
		);
		
		//create the backup database
		ee()->dbforge->add_field($fields);
		ee()->dbforge->add_key('api_key_id', TRUE);
		ee()->dbforge->create_table('webservice_keys', TRUE);

		// add config tables
		$fields = array(
			'user_agent_id'	=> array(
				'type'			=> 'int',
				'constraint'		=> 7,
				'unsigned'		=> TRUE,
				'null'			=> FALSE,
				'auto_increment'	=> TRUE
			),
			'session_id'  => array(
				'type' 			=> 'varchar',
				'constraint'		=> '255',
				'null'			=> FALSE,
				'default'			=> ''
			),
			'user_agent'  => array(
				'type' 			=> 'varchar',
				'constraint'		=> '200',
				'null'			=> FALSE,
				'default'			=> ''
			),
			'timestamp'  => array(
				'type' 			=> 'varchar',
				'constraint'		=> '200',
				'null'			=> FALSE,
				'default'			=> ''
			)
		);

		//create the backup database
		ee()->dbforge->add_field($fields);
		ee()->dbforge->add_key('user_agent_id', TRUE);
		ee()->dbforge->create_table('webservice_sessions', TRUE);
		
	}
	
	// ----------------------------------------------------------------
	
	/**
	 * Install a hook for the extension
	 *
	 * @return 	boolean 	TRUE
	 */		
	private function _register_hook($hook, $method = NULL, $priority = 10)
	{
		if (is_null($method))
		{
			$method = $hook;
		}

		if (ee()->db->where('class', WEBSERVICE_CLASS.'_ext')
			->where('hook', $hook)
			->count_all_results('extensions') == 0)
		{
			ee()->db->insert('extensions', array(
				'class'		=> WEBSERVICE_CLASS.'_ext',
				'method'	=> $method,
				'hook'		=> $hook,
				'settings'	=> '',
				'priority'	=> $priority,
				'version'	=> WEBSERVICE_VERSION,
				'enabled'	=> 'y'
			));
		}
	}
	
	// ----------------------------------------------------------------
	
	/**
	 * Create a action
	 *
	 * @return 	boolean 	TRUE
	 */	
	private function _register_action($method)
	{		
		if (ee()->db->where('class', WEBSERVICE_CLASS)
			->where('method', $method)
			->count_all_results('actions') == 0)
		{
			ee()->db->insert('actions', array(
				'class' => WEBSERVICE_CLASS,
				'method' => $method
			));
		}
	}
	
	// ----------------------------------------------------------------
	
	/**
	 * Run a update from a file
	 *
	 * @return 	boolean 	TRUE
	 */	
	
	private function _init_update($version, $data = '')
	{
		// run the update file
		$class_name = 'Webservice_upd_'.str_replace('.', '', $version);
		require_once(PATH_THIRD.'webservice/updates/'.strtolower($class_name).'.php');
		$updater = new $class_name($data);
		return $updater->run_update();
	}
	
}
/* End of file upd.webservice.php */
/* Location: /system/expressionengine/third_party/webservice/upd.webservice.php */