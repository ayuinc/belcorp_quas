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
 * Automat:ee - Module Class
 *
 * Module class
 *
 * @package 	mithra62:Automatee
 * @author		Eric Lamb
 * @filesource 	./system/expressionengine/third_party/automatee/mod.automatee.php
 */
class Automatee 
{

	public $return_data	= '';
	
	/**
	 * The name of the module; used for links and whatnots
	 * @var string
	 */
	private $mod_name = 'automatee';
	
	/**
	 * The name of the class for the module references
	 * @var string
	 */
	public $class = 'Automatee';
	
	public function __construct()
	{
		ini_set('memory_limit', -1);
		set_time_limit(86400); //limit the time to 24 hours
				
		$this->EE =& get_instance();
		$this->EE->load->model('automatee_settings_model', 'automatee_settings', TRUE);	
		$this->EE->load->model('automatee_crons_model', 'automatee_crons', TRUE);	
		$this->EE->load->library('automatee_lib');
		$this->EE->load->library('automatee_cron');
		$this->EE->load->library('cronparser');		
		$this->EE->load->library('addons');	
		$this->EE->lang->loadfile('automatee');
		//$this->EE->load->library('Template', NULL, 'TMPL');
		$this->EE->load->model('addons_model', 'addons_model', TRUE);				
		$this->settings = $this->EE->automatee_settings->get_settings();
	}
	
	public function example()
	{
		
	}
	
	public function image_bug()
	{
		header("Content-Type: image/gif");
		echo base64_decode("R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==");
		register_shutdown_function(array($this, 'cron'));
		exit;
	}
	
	public function pseudo_cron()
	{
		$type = $this->EE->TMPL->fetch_param('type', 'bug');
		
		//get the action to attach
		$actions = $this->EE->automatee_lib->get_module_actions($this->mod_name);
		foreach($actions AS $action)
		{
			if($action['method'] == 'cron')
			{
				$jquery_url = $this->EE->config->config['site_url'].'?ACT='.$action['action_id'];	
				continue;
			}
			
			if($action['method'] == 'image_bug')
			{
				$bug_url = $this->EE->config->config['site_url'].'?ACT='.$action['action_id'];	
				continue;
			}					
		}
		
		//generate
		$data = '';
		switch($type)
		{
			case 'bug':
				$data = '<img src="'.$bug_url.'" alt="Automat:ee" />';
			break;
			
			case 'jquery':
				$data = $this->EE->automatee_js->get_jquery_cron();
			break;
		}
		return $data;
	}
	
	public function cron()
	{
		ignore_user_abort(true);
		$crons = $this->EE->automatee_crons->get_crons(array('active' => '1'));
		foreach($crons AS $cron)
		{	
			$this->EE->automatee_cron->run_cron($cron['id']);
		}
	}
	
	public function test_cron()
	{
		$id = $this->EE->input->get_post('id', TRUE);
		$check = $this->EE->automatee_crons->get_cron(array('id' => $id));
		if($check)
		{
			$this->EE->automatee_cron->proc($check);
		}
	}
}