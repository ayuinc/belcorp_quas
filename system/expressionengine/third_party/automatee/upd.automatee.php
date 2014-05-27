<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

 /**
 * mithra62 - Automat:ee
 *
 * @package		mithra62:Automatee
 * @author		Eric Lamb
 * @copyright	Copyright (c) 2012, mithra62, Eric Lamb.
 * @link		http://mithra62.com/projects/view/automat-ee/
 * @version		1.2.1
 * @filesource 	./system/expressionengine/third_party/automatee/
 */
 
 /**
 * Automat:ee - Update Class
 *
 * Update class
 *
 * @package 	mithra62:Automatee
 * @author		Eric Lamb
 * @filesource 	./system/expressionengine/third_party/automatee/upd.automatee.php
 */
class Automatee_upd 
{ 

    public $version = '1.2.2'; 
    
    public $name = 'Automatee';
    
    public $class = 'Automatee';
    
    public $settings_table = 'automatee_settings';
    
    public $crons_table = 'automatee_crons';
     
    public function __construct() 
    { 
		// Make a local reference to the ExpressionEngine super object
		$this->EE =& get_instance();	
    } 
    
	public function install() 
	{
		$this->EE->load->dbforge();
	
		$data = array(
			'module_name' => $this->name,
			'module_version' => $this->version,
			'has_cp_backend' => 'y',
			'has_publish_fields' => 'n'
		);
	
		$this->EE->db->insert('modules', $data);
		
		$sql = "INSERT INTO exp_actions (class, method) VALUES ('".$this->name."', 'cron')";
		$this->EE->db->query($sql);
		
		$sql = "INSERT INTO exp_actions (class, method) VALUES ('".$this->name."', 'test_cron')";
		$this->EE->db->query($sql);		
		
		$sql = "INSERT INTO exp_actions (class, method) VALUES ('".$this->name."', 'image_bug')";
		$this->EE->db->query($sql);		

		$this->add_settings_table();
		$this->add_crons_table();
		
		return TRUE;
	} 
	
	private function add_settings_table()
	{
		$this->EE->load->dbforge();
		$fields = array(
						'id'	=> array(
											'type'			=> 'int',
											'constraint'	=> 10,
											'unsigned'		=> TRUE,
											'null'			=> FALSE,
											'auto_increment'=> TRUE
										),
						'setting_key'	=> array(
											'type' 			=> 'varchar',
											'constraint'	=> '30',
											'null'			=> FALSE,
											'default'		=> ''
										),
						'setting_value'  => array(
											'type' 			=> 'text',
											'null'			=> FALSE
										),
						'serialized' => array(
											'type' => 'int',
											'constraint' => 1,
											'null' => TRUE,
											'default' => '0'
						)										
		);

		$this->EE->dbforge->add_field($fields);
		$this->EE->dbforge->add_key('id', TRUE);
		$this->EE->dbforge->create_table($this->settings_table, TRUE);		
	}
	
	private function add_crons_table()
	{
		$this->EE->load->dbforge();
		$fields = array(
						'id'	=> array(
											'type'			=> 'int',
											'constraint'	=> 10,
											'unsigned'		=> TRUE,
											'null'			=> FALSE,
											'auto_increment'=> TRUE
										),
						'name'	=> array(
											'type' 			=> 'varchar',
											'constraint'	=> '100',
											'null'			=> FALSE,
											'default'		=> ''
										),
						'description'  => array(
											'type' 			=> 'text',
											'null'			=> FALSE
										),				
						'active' => array(
											'type' => 'tinyint',
											'constraint' => 1,
											'null' => TRUE,
											'default' => '0'
										),
						'schedule'	=> array(
											'type' 			=> 'varchar',
											'constraint'	=> '100',
											'null'			=> FALSE,
											'default'		=> ''
										),	
						'type'	=> array(
											'type' 			=> 'varchar',
											'constraint'	=> '100',
											'null'			=> FALSE,
											'default'		=> ''
										),																																																												
						'command'  => array(
											'type' 			=> 'text',
											'null'			=> FALSE
										),										
						'cron_plugin'	=> array(
											'type' 			=> 'varchar',
											'constraint'	=> '100',
											'null'			=> FALSE,
											'default'		=> ''
										),
										
						'cron_module'	=> array(
											'type' 			=> 'varchar',
											'constraint'	=> '100',
											'null'			=> FALSE,
											'default'		=> ''
										),
						'cron_method'	=> array(
											'type' 			=> 'varchar',
											'constraint'	=> '100',
											'null'			=> FALSE,
											'default'		=> ''
										),
						'ran_at'	=> array(
											'type' 			=> 'int',
											'constraint'	=> 11,
											'null'			=> FALSE,
											'default'		=> '0'
										),
						'total_runs'	=> array(
											'type' 			=> 'int',
											'constraint'	=> 10,
											'null'			=> FALSE,
											'default'		=> '0'
										),
						'last_run_status'	=> array(
											'type' 			=> 'int',
											'constraint'	=> 1,
											'null'			=> FALSE,
											'default'		=> '0'
										),
						'last_modified'	=> array(
											'type' 			=> 'datetime'
										),
						'created_date'	=> array(
											'type' 			=> 'datetime'
						)										
		);

		$this->EE->dbforge->add_field($fields);
		$this->EE->dbforge->add_key('id', TRUE);
		$this->EE->dbforge->create_table($this->crons_table, TRUE);	

		$data = array(
					 'name' => 'Example Module Cron', 
					 'active' => '0', 
					 'schedule' => '0,30 * * * *', 
					 'type' => 'module', 
					 'command' => '', 
					 'description' => '', 
					 'cron_plugin' => '', 
					 'cron_module' => 'automatee', 
					 'cron_method' => 'example',
					 'ran_at' => time(),
		   			 'last_modified' => date('Y-m-d H:i:s'),
					 'created_date' => date('Y-m-d H:i:s')
		);
		
		$this->EE->db->insert($this->crons_table, $data); 	

		$data = array(
					 'name' => 'Example CLI and Custom Schedule', 
					 'active' => '0', 
					 'schedule' => '3 3,21-23,10 * * *', 
					 'description' => '', 
					 'type' => 'cli', 
					 'command' => '/path/to/shell/script.sh', 
					 'cron_plugin' => '', 
					 'cron_module' => '', 
					 'cron_method' => '',
					 'ran_at' => time(),
		   			 'last_modified' => date('Y-m-d H:i:s'),
					 'created_date' => date('Y-m-d H:i:s')
		);
		
		$this->EE->db->insert($this->crons_table, $data); 	

		$data = array(
					 'name' => 'Example Get URL', 
					 'active' => '0', 
					 'schedule' => '0,30 * * * *', 
					 'description' => '', 
					 'type' => 'url', 
					 'command' => 'http://google.com', 
					 'cron_plugin' => '', 
					 'cron_module' => '', 
					 'cron_method' => '',
					 'ran_at' => time(),
		   			 'last_modified' => date('Y-m-d H:i:s'),
					 'created_date' => date('Y-m-d H:i:s')
		);
		
		$this->EE->db->insert($this->crons_table, $data); 

		//check for Backup Pro and install a Cron for it
		$where = array('class' => 'M62_backup', 'method' => 'cron');
		$data = $this->EE->db->get_where('actions', $where)->result_array();
		if(count($data) == '1' && isset($data['0']))
		{
			$data = $data['0'];
			$url = $this->EE->config->config['site_url'].'?ACT='.$data['action_id'].'&type=';
			$data = array(
					'name' => 'Backup Pro (Database Backup)',
					'active' => '0',
					'schedule' => '0 0 * * *',
					'description' => '', 
					'type' => 'url',
					'command' => $url.'db',
					'cron_plugin' => '',
					'cron_module' => '',
					'cron_method' => '',
					'ran_at' => time(),
					'last_modified' => date('Y-m-d H:i:s'),
					'created_date' => date('Y-m-d H:i:s')
			);
			
			$this->EE->db->insert($this->crons_table, $data);

			$data = array(
					'name' => 'Backup Pro (File Backup)',
					'active' => '0',
					'schedule' => '0 0 * * 0',
					'description' => '', 
					'type' => 'url',
					'command' => $url.'file',
					'cron_plugin' => '',
					'cron_module' => '',
					'cron_method' => '',
					'ran_at' => time(),
					'last_modified' => date('Y-m-d H:i:s'),
					'created_date' => date('Y-m-d H:i:s')
			);
			
			$this->EE->db->insert($this->crons_table, $data);			
		}
		
		//check for Backup Pro(ish) and install the Crons for it
		$where = array('class' => 'Backup_proish', 'method' => 'cron');
		$data = $this->EE->db->get_where('actions', $where)->result_array();
		if(count($data) == '1' && isset($data['0']))
		{
			$data = $data['0'];
			$url = $this->EE->config->config['site_url'].'?ACT='.$data['action_id'].'&type=';
			$data = array(
					'name' => 'Backup Pro(ish) Database Backup',
					'active' => '0',
					'schedule' => '0 0 * * *',
					'type' => 'url',
					'description' => '', 
					'command' => $url.'db',
					'cron_plugin' => '',
					'cron_module' => '',
					'cron_method' => '',
					'ran_at' => time(),
					'last_modified' => date('Y-m-d H:i:s'),
					'created_date' => date('Y-m-d H:i:s')
			);
				
			$this->EE->db->insert($this->crons_table, $data);
		
			$data = array(
					'name' => 'Backup Pro(ish) File Backup',
					'active' => '0',
					'schedule' => '0 0 * * 0',
					'description' => '', 
					'type' => 'url',
					'command' => $url.'file',
					'cron_plugin' => '',
					'cron_module' => '',
					'cron_method' => '',
					'ran_at' => time(),
					'last_modified' => date('Y-m-d H:i:s'),
					'created_date' => date('Y-m-d H:i:s')
			);
				
			$this->EE->db->insert($this->crons_table, $data);
		}		
		
		//check for CartThrob and install Cron's for garbage collection
		$where = array('class' => 'Cartthrob_mcp', 'method' => 'garbage_collection');
		$data = $this->EE->db->get_where('actions', $where)->result_array();
		if(count($data) == '1' && isset($data['0']))
		{
			$data = $data['0'];
			$url = $this->EE->config->config['site_url'].'?ACT='.$data['action_id'];
			$data = array(
					'name' => 'CartThrob Garbage Collection',
					'active' => '0',
					'schedule' => '0 0 * * *',
					'description' => '', 
					'type' => 'url',
					'command' => $url,
					'cron_plugin' => '',
					'cron_module' => '',
					'cron_method' => '',
					'ran_at' => time(),
					'last_modified' => date('Y-m-d H:i:s'),
					'created_date' => date('Y-m-d H:i:s')
			);
				
			$this->EE->db->insert($this->crons_table, $data);
		}

		//check for Securit:ee and install Cron's for file monitor
		$where = array('module_name' => 'Securitee');
		$data = $this->EE->db->get_where('modules', $where)->result_array();
		if(count($data) == '1')
		{
			$data = array(
					'name' => 'Securit:ee File Monitor',
					'active' => '0',
					'schedule' => '0 0 * * *',
					'description' => '', 
					'type' => 'module',
					'command' => '',
					'cron_plugin' => '',
					'cron_module' => 'securitee',
					'cron_method' => 'file_monitor',
					'ran_at' => time(),
					'last_modified' => date('Y-m-d H:i:s'),
					'created_date' => date('Y-m-d H:i:s')
			);
		
			$this->EE->db->insert($this->crons_table, $data);
		}		
	}	

	public function uninstall()
	{
		$this->EE->load->dbforge();
	
		$this->EE->db->select('module_id');
		$query = $this->EE->db->get_where('modules', array('module_name' => $this->class));
	
		$this->EE->db->where('module_id', $query->row('module_id'));
		$this->EE->db->delete('module_member_groups');
	
		$this->EE->db->where('module_name', $this->class);
		$this->EE->db->delete('modules');
	
		$this->EE->db->where('class', $this->class);
		$this->EE->db->delete('actions');
		
		$this->EE->dbforge->drop_table($this->settings_table);
		$this->EE->dbforge->drop_table($this->crons_table);
	
		return TRUE;
	}

	public function update($current = '')
	{
		if ($current == $this->version)
		{
			return FALSE;
		}
		
		$db_prefix = $this->EE->db->dbprefix;
		if($current < 1.2)
		{
			$sql = "INSERT INTO ".$db_prefix."actions (class, method) VALUES ('".$this->name."', 'cron')";
			$this->EE->db->query($sql);				
		}
		
		if($current < '1.2.1')
		{
			$sql = "INSERT INTO ".$db_prefix."actions (class, method) VALUES ('".$this->name."', 'test_cron')";
			$this->EE->db->query($sql);

			$sql = "ALTER TABLE `".$db_prefix."automatee_crons` ADD `description` TEXT NULL DEFAULT NULL AFTER `name` ";
			$this->EE->db->query($sql);
		}
		
		return TRUE;
	}	
    
}