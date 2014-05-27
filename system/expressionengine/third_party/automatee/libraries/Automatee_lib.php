<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Automatee_lib
{
	/**
	 * Preceeds URLs 
	 * @var mixed
	 */
	private $url_base = FALSE;
	
	/**
	 * The full path to the log file for the progress bar
	 * @var string
	 */
	public $progress_log_file;
	
	
	public function __construct()
	{
		$this->EE =& get_instance();
		$this->settings = $this->EE->automatee_settings->get_settings();
	}
	
	/**
	 * Sets up the right menu options
	 * @return multitype:string
	 */
	public function get_right_menu()
	{
		return array(
				'view_crons'	=> $this->url_base.'index',
				'add_cron'		=> $this->url_base.'add_cron',
				'settings'		=> $this->url_base.'settings'
		);
	}

	/**
	 * Wrapper that runs all the tests to ensure system stability
	 * @return array;
	 */
	public function error_check()
	{
		$errors = array();
		if($this->settings['license_number'] == '')
		{
			$errors['license_number'] = 'missing_license_number';
		}
		return $errors;
	}
	
	/**
	 * Wrapper to handle CP URL creation
	 * @param string $method
	 */
	public function _create_url($method)
	{
		return $this->url_base.$method;
	}

	/**
	 * Creates the value for $url_base
	 * @param string $url_base
	 */
	public function set_url_base($url_base)
	{
		$this->url_base = $url_base;
	}
	
	/**
	 * Creates a manageable array of the installed plugins. 
	 */
	public function get_installed_plugins()
	{
		//$plugins = $this->EE->addons_model->get_plugins();
		$arr = array('' => '');
		$this->EE->load->helper('directory');

		$plugins = array();
		$ext_len = strlen(EXT);
		if(($map = directory_map(PATH_PI, TRUE)) !== FALSE)
		{
			foreach($map as $file)
			{
				if(strncasecmp($file, 'pi.', 3) == 0 && substr($file, -$ext_len) == EXT && strlen($file) > strlen('pi.'.EXT))
				{
					$arr[$file] = $file;
				}				
			}
		}

		if (($map = directory_map(PATH_THIRD, 2)) !== FALSE)
		{
			foreach($map as $pkg_name => $files)
			{
				if(!is_array($files))
				{
					$files = array($files);
				}
				
				foreach($files as $file)
				{
					if(is_array($file))
					{
						continue;
					}
					
					elseif(strncasecmp($file, 'pi.', 3) == 0 && substr($file, -$ext_len) == EXT && strlen($file) > strlen('pi.'.EXT))
					{							
						$arr[$file] = $file;
					}					
				}
			}
		}
		
		asort($arr);
		$return = array();
		foreach($arr AS $key => $value)
		{
			$replace = array('pi.', '.php');
			$with = array('', '');
			$key = str_replace($replace, $with, $key);
			$replace[] = '_';
			$with[] = ' ';
			$value = ucwords(str_replace($replace, $with, $key));
			$return[$key] = $value;
		}
		return $return;
	}
	
	public function get_installed_modules()
	{
		$modules = $this->EE->addons->get_installed();
		$arr = array('' => '');
		foreach($modules AS $module_id => $module)
		{
			$arr[$module_id] = $module['name'];
		}
		
		asort($arr);
		return $arr;
	}

	/**
	 * Takes a cron formatted string and formats it for people
	 * @param array $str
	 */
	public function parse_cron_string($str)
	{
		$this->EE->cronparser->calcLastRan($str);
		$arr = array();
		$arr['last_run_unix'] = $this->EE->cronparser->getLastRanUnix();
		$arr['last_run_array'] = $this->EE->cronparser->getLastRan();
		return $arr;	
	}
	
	
	public function get_module_actions($module)
	{
		$this->EE->load->dbforge();
		return $this->EE->db->get_where('actions', array('class' => $module))->result_array();		
	}

	public function get_module_action($module, $action)
	{
		$this->EE->load->dbforge();
		$this->EE->db->select('action_id');
		$data = $this->EE->db->get_where('actions', array('class' => $module, 'method' => $action), '1')->result_array();
		if($data)
		{
			return $data['0']['action_id'];
		}
	}
}