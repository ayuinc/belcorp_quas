<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

 /**
 * mithra62 - Automat:ee
 *
 * @package		mithra62:Automatee
 * @author		Eric Lamb
 * @copyright	Copyright (c) 2012, mithra62, Eric Lamb.
 * @link		http://mithra62.com/projects/view/automat-ee/
 * @version		1.2
 * @filesource 	./system/expressionengine/third_party/automatee/
 */
 
 /**
 * Automat:ee - Cron Model
 *
 * @package 	mithra62:Automatee
 * @author		Eric Lamb
 * @filesource 	./system/expressionengine/third_party/automatee/models/automatee_crons_model.php
 */
class Automatee_crons_model extends CI_Model
{
	/**
	 * Name of the cron table
	 * @var string
	 */	
	private $_table = 'automatee_crons';
	
	/**
	 * The options available for scheduling
	 * @var array
	 */	
	public $schedule_options = array(
						'0,10,20,30,40,50 * * * *' => 'Every 10 minutes',
						'0,30 * * * *' => 'Twice an hour',
						'0 * * * *' => 'Once an hour',
						'0 0,12 * * *' => 'Twice a day',
						'0 0 * * *' => 'Once a day',
						'0 0 * * 0' => 'Once a week',
						'0 0 1,15 * *' => '1st and 15th',
						'0 0 1 * *' => 'Once a month',
						'0 0 1 1 *' => 'Once a year',
						'custom' => 'Custom'
	);
	
	/**
	 * The options available for crons
	 * @var array
	 */	
	public $cron_types = array(
							   'plugin' => 'plugin',
							   'module' => 'module',
							   'url' => 'get_url',
							   'cli' => 'command_line'
	
	);
	
	public $statuses = array(
							 '0' => 'inactive',
							 '1' => 'active'
	);
	
	public function __construct()
	{
		parent::__construct();
		$this->statuses = $this->_set_lang($this->statuses);
		$this->cron_types = $this->_set_lang($this->cron_types);
		$this->schedule_options = $this->_set_lang($this->schedule_options);
	}
	
	private function get_sql($cron)
	{
		return $data = array(
		   'name' => $cron['name'],
		   'description' => $cron['description'],
		   'type' => $cron['type'],
		   'schedule' => $cron['schedule'],
		   'command' => $cron['command'],
		   'active' => $cron['status'],
		   'cron_plugin' => $cron['cron_plugin'],
		   'cron_module' => $cron['cron_module'],
		   'cron_method' => $cron['cron_method'],
		   'ran_at' => time(),
		   'last_modified' => date('Y-m-d H:i:s')
		);
	}
	
	public function _set_lang($arr)
	{
		
		foreach($arr AS $key => $value)
		{
			$arr[$key] = lang($value);
		}
		return $arr;
	}
	
	/**
	 * Adds a cron to the databse
	 * @param string $cron
	 */
	public function add_cron($cron)
	{
		if(isset($cron['schedule']) && $cron['schedule'] == 'custom')
		{
			$cron['schedule'] = $cron['schedule_custom'];
		}
		$data = $this->get_sql($cron);
		$data['created_date'] = date('Y-m-d H:i:s');
		return $this->db->insert($this->_table, $data); 
	}	
	
	public function get_crons($where = array())
	{
		foreach($where AS $key => $value)
		{
			$this->db->where($key, $value);
		}
		$query = $this->db->get($this->_table);
		$data = $query->result_array();
		return $data;
	}
	
	/**
	 * Returns the value straigt from the database
	 * @param string $setting
	 */
	public function get_cron(array $where)
	{
		$data = $this->db->get_where($this->_table, $where)->result_array();
		if($data)
		{
			return $data['0'];
		}
	}	
	
	public function update_crons(array $data, $where)
	{
		foreach($data AS $key => $value)
		{	
			$this->update_cron($data, $where);
		}
		
		return TRUE;
	}
	
	/**
	 * Updates a cron
	 * @param string $key
	 * @param string $value
	 */
	public function update_cron($data, $where, $complete = TRUE)
	{	
		if(isset($data['schedule']) && $data['schedule'] == 'custom')
		{
			$data['schedule'] = $data['schedule_custom'];
		}

		if($complete)
		{
			$data = $this->get_sql($data);
		}
		return $this->db->update($this->_table, $data, $where);
	}
	
	public function delete_crons(array $ids)
	{
		foreach($ids AS $id)
		{
			$this->db->delete($this->_table, array('id' => $id));	
		}
		return TRUE;	
	}
}