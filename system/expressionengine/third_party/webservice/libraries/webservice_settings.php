<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *  Default settings
 *
 * @package		webservice
 * @category	Modules
 * @author		Rein de Vries <info@reinos.nl>
 * @license  	http://reinos.nl/add-ons/commercial-license
 * @link        http://reinos.nl/add-ons/gmaps
 * @copyright 	Copyright (c) 2012 Reinos.nl Internet Media
 */

/**
 * Include the config file
 */
require_once(PATH_THIRD.'webservice/config.php');

/**
 * Include helper
 */
require_once(PATH_THIRD.'webservice/libraries/webservice_helper.php');

class Webservice_settings {

	private $_config_items = array();

	private $_config_defaults = array();

	/**
	 * Constructor
	 */
	public function __construct()
	{
		//fix document_root
		if (substr($_SERVER['DOCUMENT_ROOT'], -1) != '/')
			$path = $_SERVER['DOCUMENT_ROOT'].'/';
		else
			$path = $_SERVER['DOCUMENT_ROOT'];

		//load string helper
		ee()->load->helper('string');

		//set the default settings
		$this->default_settings = array(
			'module_dir'   => PATH_THIRD.WEBSERVICE_MAP.'/',
			'theme_dir'   => PATH_THEMES.'third_party/'.WEBSERVICE_MAP.'/',
			'site_id' => ee()->config->item('site_id'),
			'site_url' => reduce_double_slashes(ee()->config->item('site_url').'/'.ee()->config->item('site_index').'/'),
			'base_dir' => $path,
		);

		//if older than EE 2.4
		if(!defined('URL_THIRD_THEMES'))
		{
			//set the theme url
			$theme_url = ee()->config->slash_item('theme_folder_url') != '' ? ee()->config->slash_item('theme_folder_url').'third_party/'.WEBSERVICE_MAP.'/' : ee()->config->item('theme_folder_url') .'third_party/'.WEBSERVICE_MAP.'/';

			//lets define the URL_THIRD_THEMES
			$this->default_settings['theme_url'] = $theme_url;
		}
		else
		{
			//set the Theme dir
			$this->default_settings['theme_url'] = URL_THIRD_THEMES.WEBSERVICE_MAP.'/';
		}

		// DB and BASE dependend
		if(REQ == 'CP' && isset(ee()->db))
		{
			//is the BASE constant defined?
			if(!defined('BASE'))
			{
				$s = '';

				if (ee()->config->item('admin_session_type') != 'c')
				{
					if(isset(ee()->session))
					{
						$s = ee()->session->userdata('session_id', 0);
					}
				}

				//lets define the BASE
				define('BASE', SELF.'?S='.$s.'&amp;D=cp');
			}

			//set the  BASE url
			if(APP_VER < '2.8.0')
			{
				$this->default_settings['base_url'] = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.WEBSERVICE_MAP;
				$this->default_settings['base_url_js'] = '&C=addons_modules&M=show_module_cp&module='.WEBSERVICE_MAP;
			}
			else
			{
				$this->default_settings['base_url'] = cp_url('cp/addons_modules/show_module_cp/', array('module' => WEBSERVICE_MAP));
				$this->default_settings['base_url_js'] = cp_url('cp/addons_modules/show_module_cp/', array('module' => WEBSERVICE_MAP));
			}
		}

		//require the settings
		require PATH_THIRD.WEBSERVICE_MAP.'/settings.php';

		//Custom (override) Config vars
		if(!empty($this->overide_settings))
		{
			foreach($this->overide_settings as $key=>$val)
			{
				$this->default_settings[$key] = ee()->config->item($key) != '' ? ee()->config->item($key) : str_replace(array('[theme_dir]', '[theme_url]'), array($this->default_settings['theme_dir'], $this->default_settings['theme_url']), $val);
			}
		}

		//check if all default settings are present
		//e.g. for MSM we must recreate the settings for the other site_id
		$this->check_db_settings();

		//get the settings
		$this->settings = $this->load_settings();
	}

	// ----------------------------------------------------------------

	/**
	 * Insert the settings to the database
	 *
	 * @param none
	 * @return void
	 */
	public function first_import_settings()
	{
		foreach($this->default_post as $key=>$val)
		{
			$data[] = array(
				'site_id' => $this->default_settings['site_id'],
				'var' => $key,
				'value'=> $val,
			);
		}

		//insert into db
		ee()->db->insert_batch(WEBSERVICE_MAP.'_settings', $data);

		//clear data
		unset($data);
	}

	// ----------------------------------------------------------------

	/**
	 * check if all default settings are present
	 * e.g. for MSM we must recreate the settings for the other site_id
	 *
	 * @param none
	 * @return void
	 */
	public function check_db_settings()
	{
		if (ee()->db->table_exists(WEBSERVICE_MAP.'_settings'))
		{
			//check if there is any result
			$check = ee()->db->from(WEBSERVICE_MAP.'_settings')->get();
			if($check->num_rows() > 0)
			{
				//get the site IDS
				$sites = $this->get_sites();

				$cache_result = array();

				//cache result
				foreach($check->result() as $row)
				{
					$cache_result[$row->site_id][$row->var] = $row->value;
				}

				//loop over the sites
				foreach($sites as $site_id)
				{
					foreach($this->default_post as $key=>$val)
					{
						//check the setting
						//ee()->db->where('var', $key);
						//ee()->db->where('site_id', $site_id);
						//$q = ee()->db->get(WEBSERVICE_MAP.'_settings');

						//if the setting not presents, we have to create this one
						if(!isset($cache_result[$site_id][$key]))
						{
							$data = array(
								'site_id' => $site_id,
								'var' => $key,
								'value'=> ($site_id != 1 ? $this->get_db_settings(1, $key) : $val),
							);

							//insert into db
							ee()->db->insert(WEBSERVICE_MAP.'_settings', $data);
						}
					}
				}
			}
		}
	}

	// ----------------------------------------------------------------------

	/**
	 * Get the Settings
	 *
	 * @param $all_sites
	 * @return mixed array
	 */
	public function load_settings($all_sites = TRUE)
	{
		if (ee()->db->table_exists(WEBSERVICE_MAP.'_settings'))
		{
			//get the settings from the database
			$get_setting = ee()->db->get_where(WEBSERVICE_MAP.'_settings', array(
				'site_id' => $this->default_settings['site_id']
			));

			//load helper
			ee()->load->helper('string');

			//set the settings
			$settings = array();
			foreach ($get_setting->result() as $row)
			{
				//is serialized?
				if(call_user_func(array(WEBSERVICE_MAP.'_helper','is_serialized'), $row->value))
				{
					$settings[$row->var] = @unserialize($row->value);
				}
				//default value
				else
				{
					$settings[$row->var] = $row->value;
				}
			}

			//clear data
			unset($get_setting);

			//return the settings
			return array_merge($this->default_settings, $settings);
		}
		else
		{
			return $this->default_settings;
		}
	}

	// ----------------------------------------------------------------------

	/**
	 * Get specific setting
	 *
	 * @param $all_sites
	 * @return mixed array
	 */
	public function get_settings($setting_name, $def_value = '')
	{
		if(isset($this->settings[$setting_name]))
		{
			//empty, return defualt value
			if($this->settings[$setting_name] == '')
			{
				return $def_value;
			}

			return $this->settings[$setting_name];
		}
		//nothing, return default value
		return $def_value;
	}
	//alias
	public function get_setting($setting_name, $def_value = '')
	{
		return $this->get_settings($setting_name, $def_value);
	}
	//alias
	public function item($setting_name, $def_value = '')
	{
		return $this->get_settings($setting_name, $def_value);
	}

	// ----------------------------------------------------------------------

	/**
	 * Get specific setting from the db instead of the array
	 *
	 * @param $all_sites
	 * @return mixed array
	 */
	public function get_db_settings($site_id = '', $setting_name = '')
	{
		ee()->db->where('site_id', $site_id);
		ee()->db->where('var', $setting_name);
		ee()->db->from(WEBSERVICE_MAP.'_settings');
		$q = ee()->db->get();

		if($q->num_rows() > 0)
		{
			return $q->row()->value;
		}
		return '';
	}

	// ----------------------------------------------------------------

	/**
	 * Prepare settings to save
	 *
	 * @return 	DB object
	 */
	public function save_post_settings()
	{
		if(isset($_POST))
		{
			//remove submit value
			unset($_POST['submit']);

			//loop through the post values
			foreach($_POST as $key=>$val)
			{
				$this->save_setting($key, $val);
			}
		}

		//set a message
		ee()->session->set_flashdata(
			'message_success',
			ee()->lang->line('preferences_updated')
		);

		//redirect
		ee()->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.WEBSERVICE_MAP.AMP.'method=settings');
	}

	// ----------------------------------------------------------------

	/**
	 * Prepare settings to save
	 *
	 * @return 	DB object
	 */
	public function save_setting($key = '', $val = '')
	{
		if($key != '')
		{
			//set the where clause
			ee()->db->where('var', $key);
			ee()->db->where('site_id', $this->item('site_id'));

			//is this a array?
			if(is_array($val))
			{
				$val = serialize($val);
			}

			//update the record
			ee()->db->update(WEBSERVICE_MAP.'_settings', array(
				'value' => $val
			));
		}
	}

	// ----------------------------------------------------------------

	/**
	 * set a static setting
	 *
	 * @return 	DB object
	 */
	public function set_setting($key = '', $val = '')
	{
		$this->settings[$key] = $val;
	}

	// ----------------------------------------------------------------

	/**
	 * Get the sites
	 *
	 * @return 	DB object
	 */
	public function get_sites()
	{
		ee()->db->select('site_id');
		$sites = ee()->db->get('sites');

		$return = array();

		if($sites->num_rows() > 0)
		{
			foreach($sites->result() as $site)
			{
				$return[] = $site->site_id;
			}
		}

		return $return;
	}

}

/* End of file ./libraries/default_settings.php */