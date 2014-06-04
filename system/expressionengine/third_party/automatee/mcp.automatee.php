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
 * Automat:ee - CP Class
 *
 * Control Panel class
 *
 * @package 	mithra62:Automatee
 * @author		Eric Lamb
 * @filesource 	./system/expressionengine/third_party/automatee/mcp.automatee.php
 */
class Automatee_mcp 
{
	public $url_base = '';
	
	/**
	 * The amount of pagination items per page
	 * @var int
	 */
	public $perpage = 10;
	
	/**
	 * The delimiter for the datatables jquery
	 * @var stirng
	 */
	public $pipe_length = 1;
	
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
		$this->EE =& get_instance();

		//load EE stuff
		$this->EE->load->library('javascript');
		$this->EE->load->library('table');
		$this->EE->load->helper('form');
		$this->EE->load->library('logger');
		$this->EE->load->library('form_validation');
		
		$this->EE->load->model('automatee_settings_model', 'automatee_settings', TRUE);	
		$this->EE->load->model('automatee_crons_model', 'automatee_crons', TRUE);	
		$this->EE->load->library('automatee_js');
		$this->EE->load->library('automatee_lib', 'automatee_lib');
		$this->EE->load->library('cronparser');
		$this->settings = $this->EE->automatee_settings->get_settings();
		
		$this->query_base = 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->mod_name.AMP.'method=';
		$this->url_base = BASE.AMP.$this->query_base;
		$this->EE->automatee_lib->set_url_base($this->url_base);

		$this->EE->cp->set_breadcrumb(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->mod_name, $this->EE->lang->line('automatee_module_name'));
		$this->EE->cp->set_right_nav($this->EE->automatee_lib->get_right_menu());	
		
		$this->errors = $this->EE->automatee_lib->error_check();
		$this->EE->load->vars(
			array(
				'url_base' => $this->url_base, 
				'query_base' => $this->query_base, 
				'errors' => $this->errors, 
				'schedule_options' => $this->EE->automatee_crons->schedule_options, 
				'statuses' => $this->EE->automatee_crons->statuses, 
				'installed_modules' => $this->EE->automatee_lib->get_installed_modules(), 
				'installed_plugins' => $this->EE->automatee_lib->get_installed_plugins(), 
				'cron_types' => $this->EE->automatee_crons->cron_types
			)
		);
	}
	
	public function index()
	{		
		$vars = array();
		$vars['errors'] = $this->errors;
		$this->EE->view->cp_page_title = $this->EE->lang->line('automatee_module_name');	
		
		$action_id = $this->EE->cp->fetch_action_id('Automatee', 'test_cron');
		$action_url = $this->EE->config->config['site_url'].'?ACT='.$action_id.'&id=';
		
		if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on')
		{
			$action_url = str_replace('http://', 'https://', $action_url);
		}
		
		$this->EE->javascript->output($this->EE->automatee_js->get_check_toggle());
		$this->EE->javascript->output($this->EE->automatee_js->get_test_cron($action_url));
		
		
		$this->EE->jquery->tablesorter('#cli_crons table', '{headers: {4: {sorter: false}}, widgets: ["zebra"], sortList: [[0,0]]}');  
		$this->EE->javascript->compile();
		
		$vars['test_aid'] = $action_id;
		$vars['test_action_url']= $action_url;
		$vars['animated_url'] = $this->EE->config->config['theme_folder_url'].'/cp_global_images/indicator.gif';
		$vars['crons'] = $this->EE->automatee_crons->get_crons();
		return $this->EE->load->view('index', $vars, TRUE); 
	}

	public function view()
	{
		$id = $this->EE->input->get_post('id', FALSE);
		if(!$id)
		{
			$this->EE->session->set_flashdata('message_failure', $this->EE->lang->line('cron_not_found'));
			$this->EE->functions->redirect($this->url_base.'index');
			exit;				
		}
		
		$cron_data = $this->EE->automatee_crons->get_cron(array('id' => $id));
		if(!$cron_data)
		{
			$this->EE->session->set_flashdata('message_failure', $this->EE->lang->line('cron_not_found'));
			$this->EE->functions->redirect($this->url_base.'index');
			exit;			
		}
		
		$this->EE->form_validation->set_rules('name', 'Name', 'required');
		if ($this->EE->form_validation->run() == TRUE)
		{
			if($this->EE->automatee_crons->update_cron($_POST, array('id' => $id)))
			{
				$this->EE->session->set_flashdata('message_success', $this->EE->lang->line('cron_updated'));
				$this->EE->functions->redirect($this->url_base.'index');
				exit;
			}
		}		
		
		$vars = array();
		$vars['cron_data'] = $cron_data;
		$this->EE->view->cp_page_title = $this->EE->lang->line('cron_details');
		$this->EE->cp->add_js_script('ui', 'accordion'); 
		$this->EE->javascript->output($this->EE->automatee_js->get_accordian_css());
		$this->EE->javascript->output($this->EE->automatee_js->get_form_cron()); 		
		$this->EE->javascript->compile();

		return $this->EE->load->view('view', $vars, TRUE); 
	}
	
	public function add_cron()
	{
		$this->EE->form_validation->set_rules('name', 'Name', 'required');
		$proc_cron = $this->EE->input->get_post('go_cron_form', FALSE);
		if ($this->EE->form_validation->run() == TRUE)
		{
			if($this->EE->automatee_crons->add_cron($_POST))
			{
				$this->EE->session->set_flashdata('message_success', $this->EE->lang->line('cron_added'));
				$this->EE->functions->redirect($this->url_base.'index');
				exit;
			}
		}
		$this->EE->view->cp_page_title = $this->EE->lang->line('add_cron');
		
		$this->EE->cp->add_js_script('ui', 'accordion'); 
		$this->EE->javascript->output($this->EE->automatee_js->get_accordian_css());
		$this->EE->javascript->output($this->EE->automatee_js->get_form_cron());
		 		
		$this->EE->javascript->compile();	
		
		$vars = array();
		return $this->EE->load->view('form_cron', $vars, TRUE); 	
	}
	
	public function delete_cron_confirm()
	{
		$crons = $this->EE->input->get_post('toggle', TRUE);
		if(!$crons || count($crons) == 0)
		{
			$this->EE->session->set_flashdata('message_failure', $this->EE->lang->line('crons_not_found'));
			$this->EE->functions->redirect($this->url_base.'index');	
			exit;			
		}
		$ids = array();
		$i = 0;
		foreach($crons AS $cron)
		{
			$ids[$i] = $this->EE->automatee_crons->get_cron(array('id' => $cron));
			$i++;
		}

		$this->EE->view->cp_page_title = $this->EE->lang->line('delete_crons');
		$this->EE->load->vars(
			array(
				'cron_delete_question' => $this->EE->lang->line('cron_delete_question')
			)
		);
		
		$vars = array();
		$vars['form_action'] = $this->query_base.'delete_crons';
		$vars['damned'] = $ids;
		return $this->EE->load->view('delete_confirm', $vars, TRUE);
	}
	
	public function delete_crons()
	{
		$crons = $this->EE->input->get_post('delete', TRUE);
		if($this->EE->automatee_crons->delete_crons($crons))
		{
			$this->EE->logger->log_action($this->EE->lang->line('log_crons_deleted'));
			$this->EE->session->set_flashdata('message_success', $this->EE->lang->line('crons_deleted'));
			$this->EE->functions->redirect($this->url_base.'index');	
			exit;			
		}
		
		$this->EE->session->set_flashdata('message_failure', $this->EE->lang->line('crons_delete_failure'));
		$this->EE->functions->redirect($this->url_base.'index');
		exit;	
				
	}
	
	public function settings()
	{
		if(isset($_POST['go_settings']))
		{			
			if($this->EE->automatee_settings->update_settings($_POST))
			{	
				$this->EE->logger->log_action($this->EE->lang->line('log_settings_updated'));
				$this->EE->session->set_flashdata('message_success', $this->EE->lang->line('settings_updated'));
				$this->EE->functions->redirect($this->url_base.'settings');		
				exit;			
			}
			else
			{
				$this->EE->session->set_flashdata('message_failure', $this->EE->lang->line('settings_update_fail'));
				$this->EE->functions->redirect($this->url_base.'settings');	
				exit;					
			}
		}
		
		$this->EE->view->cp_page_title = $this->EE->lang->line('settings');
		
		$this->EE->cp->add_js_script('ui', 'accordion'); 
		$this->EE->javascript->output($this->EE->automatee_js->get_accordian_css()); 		
		$this->EE->javascript->compile();	

		$cron_action_id = $this->EE->automatee_lib->get_module_action($this->mod_name, 'cron');
		$vars = array();
		$vars['errors'] = $this->errors;
		$vars['settings'] = $this->settings;
		$vars['cron_url'] = $this->EE->config->config['site_url'].'?ACT='.$cron_action_id;
		$vars['member_groups'] = $this->EE->automatee_settings->get_member_groups();
		return $this->EE->load->view('settings', $vars, TRUE);
	}
}