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
 * Automat:ee - Cron Library
 *
 * Cron library
 *
 * @package 	mithra62:Automatee
 * @author		Eric Lamb
 * @filesource 	./system/expressionengine/third_party/automatee/libraries/Automatee_cron.php
 */
class Automatee_cron
{
	public $error = FALSE;
	
	public function __construct()
	{
		$this->EE =& get_instance();
		$this->EE->load->library('logger');
		$this->EE->load->library('email');
		$this->settings = $this->EE->automatee_settings->get_settings();
	}
	
	public function proc($cron = FALSE)
	{
		if($cron)
		{
			$this->cron = $cron;
		}
		
		if ($this->EE->extensions->active_hook('automatee_cron_proc_start') === TRUE)
		{
			$this->EE->extensions->call('automatee_cron_proc_start', $this);
			if ($this->EE->extensions->end_script === TRUE) return;
		}
						
		if($this->settings['enable_debug'] == '1')
		{		
			$this->EE->logger->log_action($this->cron['name'].' '.$this->EE->lang->line('log_cron_start'));
		}
		
		$data = $this->cron;
		$data['ran_at'] = time();
		$data['total_runs'] = ($this->cron['total_runs']+1);
		$data['last_run_status'] = '0';
		$this->EE->automatee_crons->update_cron($data, "id = ".$this->cron['id'], FALSE);
		switch($this->cron['type'])
		{
			case 'url':
				$this->_proc_url();
			break;
			
			case 'cli':
				$this->_proc_cli();
			break;
			
			case 'module':
				$this->_proc_module();
			break;
			
			case 'plugin':
				$this->_proc_plugin();
			break;
		}
		
		$data = $this->cron;
		$data = array('last_run_status' => '1');
		$this->EE->automatee_crons->update_cron($data, "id = ".$this->cron['id'], FALSE);
		
		if($this->settings['enable_debug'] == '1' && !$this->error)
		{	
			$this->EE->logger->log_action($this->cron['name'].' '.$this->EE->lang->line('log_cron_sucess'));
		}

		if ($this->EE->extensions->active_hook('automatee_cron_proc_end') === TRUE)
		{
			$this->EE->extensions->call('automatee_cron_proc_end', $this);
			if ($this->EE->extensions->end_script === TRUE) return;
		}		
					
	}
	
	public function run_cron($cron_id)
	{
		//we have to check the cron action again to make sure it hasn't ran 
		//in between the last loop and this one. Just a basic santiy check. 
		
		$this->cron = $this->EE->automatee_crons->get_cron(array('id' => $cron_id));
		if(!$this->cron)
		{
			return;
		}

		if ($this->cron['last_run_status'] == '0' && $this->cron['total_runs'] >= '1')
		{
			$this->log_debug('log_last_run_fail');
		}
		
		if ($this->EE->cronparser->calcLastRan($this->cron['schedule']))
		{
			$lastRan = $this->EE->cronparser->getLastRan();		
			if ($this->EE->cronparser->getLastRanUnix() > $this->cron['ran_at'])
			{
				$this->proc();				
			}
		
		}
		else
		{
			$data = $this->cron;
			$data['ran_at'] = '0';
			$data['last_run_status'] = '0';
			$this->EE->automatee_crons->update_cron($data, "id = ".$this->cron['id'], FALSE);				
			$this->log_debug('log_calc_last_run_fail');
		}		
	}
	
	/**
	 * Handles the processing of an outside module
	 * @param array $cron
	 */
	private function _proc_module()
	{
		$modules = $this->EE->addons->get_installed();
		if(!isset($modules[$this->cron['cron_module']]))
		{
			$this->log_debug('log_module_not_installed');
			return FALSE;
		}
		
		$module = $modules[$this->cron['cron_module']];
		$module_file = $module['path'].'mod.'.$module['module_name'].EXT;
		if(!file_exists($module_file))
		{
			$this->log_debug('log_module_not_exist');
			return FALSE;			
		}
		
		$this->EE->load->add_package_path($module['path']); 
		require_once $module_file;
		
		if(class_exists($this->cron['cron_module']))
		{
			$class = new $this->cron['cron_module'];
			if ($this->cron['cron_method'] != '' && method_exists($class, $this->cron['cron_method']))
			{
				$method = $this->cron['cron_method'];
				$class->$method();
			}
			elseif ($this->cron['cron_method'] != '' && !method_exists($class, $this->cron['cron_method'])) 
			{
				$this->log_debug('log_module_method_not_exist');
				return FALSE;				
			}
		}
	}
	
	/**
	 * Handles the processing of an outside plugin
	 * @param array $cron
	 */
	private function _proc_plugin()
	{
		$plugins = $this->EE->addons_model->get_plugins();
		if(!isset($plugins[$this->cron['cron_plugin']]))
		{
			$this->log_debug('log_plugin_not_exist');
			return FALSE;
		}
		
		if(in_array($plugins[$this->cron['cron_plugin']], $this->EE->core->native_plugins))
		{
			$plugin_file = PATH_PI.'pi.'.$this->cron['cron_plugin'].EXT;
		}
		else
		{
			$plugin_file = PATH_THIRD.$this->cron['cron_plugin'].'/'.'pi.'.$this->cron['cron_plugin'].EXT;
		}
	
		if(!file_exists($plugin_file))
		{
			$this->log_debug('log_plugin_not_exist');
			return FALSE;			
		}
		
		require_once $plugin_file;
		
		if(class_exists($this->cron['cron_plugin']))
		{
			$class = new $this->cron['cron_plugin'];
			if ($this->cron['cron_method'] != '' && method_exists($class, $this->cron['cron_method']))
			{
				$method = $this->cron['cron_method'];
				$class->$method();
			}
			elseif($this->cron['cron_method'] != '' && !method_exists($class, $this->cron['cron_method']))
			{
				$this->log_debug('log_plugin_method_not_exist');
				return FALSE;				
			}			
		}		
	}

	
	private function _proc_cli()
	{
		if(function_exists('exec'))
		{
			exec(escapeshellarg($this->cron['command']));
		}
		else
		{
			show_error(lang('exec_disabled'), '500');
		}
	}	
	
	private function _proc_url()
	{
		$ch = curl_init($this->cron['command']);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1");
		$response = urldecode(curl_exec($ch));	
		
		$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);	
		$bad_statuses = array('404','500', '503', '0');
		if(in_array($http_status, $bad_statuses))
		{
			$lang = 'status_url_response_'.$http_status;
			$this->log_debug($lang, $response);
		}
	}
	
	private function send_alert_email($error, $content, $attachment = FALSE)
	{
		$replace = array('#cron_id#', '#cron_name#', '#error_issue#');
		$with = array($this->cron['id'], $this->cron['name'], $this->EE->lang->line($error));
		$message = str_replace($replace, $with, $this->EE->lang->line('log_email_message'));
		$message .= $content;
		$this->EE->email->from($this->EE->config->config['webmaster_email'], $this->EE->config->config['webmaster_name']);
		$this->EE->email->to($this->settings['debug_email']);
		$this->EE->email->subject($this->EE->lang->line('log_email_subject'));
		$this->EE->email->message($message);
		$this->EE->email->send();
		echo $this->EE->email->print_debugger();	
	}
	
	private function log_debug($lang, $content = FALSE)
	{
		$this->error = TRUE;
		if($this->settings['enable_debug'] == '1')
		{
			$this->EE->logger->log_action($this->EE->lang->line($lang));
			$this->send_alert_email($lang, $content);
		}
	}	
}