<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * MCP class
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

class Webservice_mcp {
	
	public $return_data;
	public $settings;
	
	public $api_url = '';
	
	private $_base_url;
	private $show_per_page = 25;

	/**
	 * Constructor
	 */
	public function __construct()
	{	
		//load the library`s
		ee()->load->library('table');
		ee()->load->library(WEBSERVICE_MAP.'_lib');
		ee()->load->model(WEBSERVICE_MAP.'_model');
		ee()->load->helper('form');	

 		//set the api_url
 		$this->api_url = reduce_double_slashes(ee()->webservice_settings->item('testing_tool_url'));
 
		// get the settings
		//$this->settings = ee()->webservice_lib->get_settings();
	   
	   //set the right nav
		$right_nav = array();
		$right_nav[lang('webservice_overview')] = ee()->webservice_settings->item('base_url');
		$right_nav[lang('webservice_settings')] = ee()->webservice_settings->item('base_url').AMP.'method=settings';

		$right_nav[lang('webservice_testing_tools')] = ee()->webservice_settings->item('base_url').AMP.'method=testing_tools';
		if(ee()->webservice_settings->item('debug'))
		{
			$right_nav[lang('webservice_logs')] = ee()->webservice_settings->item('base_url').AMP.'method=logs';
		}
		$right_nav[lang('webservice_api_keys')] = ee()->webservice_settings->item('base_url').AMP.'method=api_keys';
		$right_nav[lang('webservice_status_check')] = ee()->webservice_settings->item('base_url').AMP.'method=status_check';
		$right_nav[lang('webservice_api_overview')] = ee()->webservice_settings->item('base_url').AMP.'method=api_overview';
		$right_nav[lang('webservice_documentation')] = WEBSERVICE_DOCS;
		ee()->cp->set_right_nav($right_nav);

		ee()->cp->add_js_script('ui', 'accordion');
	    ee()->javascript->output('
	        $("#accordion").accordion({autoHeight: false,header: "h3"});
	    ');

	    ee()->javascript->compile();
		
		//require the default settings
		require PATH_THIRD.'webservice/settings.php';
	}

	// ----------------------------------------------------------------

	/**
	 * public method check
	 *
	 * @return 	void
	 */
	public function test()
	{
		/*ee()->load->library('webservice_public_methods');
		$result = ee()->webservice_public_methods->method('create_entry', array(
			'site_id' => 1,
			'channel_name' => 'webservice',
			'title' => 'test'
		));
		var_dump($result);*/
		exit;

	}
	
	// ----------------------------------------------------------------

	/**
	 * Index Function
	 *
	 * @return 	void
	 */
	public function index()
	{
		// Set Breadcrumb and Page Title
		$this->_set_cp_var('cp_page_title', lang('webservice_module_name'));
		$vars['cp_page_title'] = lang('webservice_module_name');

		//set the default arrays
		$vars['members'] = $this->_prepare_members();

		//load the view
		return ee()->load->view('overview', $vars, TRUE);   
	}

	// ----------------------------------------------------------------

	/**
	 * Status check Function
	 *
	 * @return 	void
	 */
	public function status_check()
	{
		// Set Breadcrumb and Page Title
		$this->_set_cp_var('cp_page_title', lang('webservice_module_name'));
		$vars['cp_page_title'] = lang('webservice_module_name');

		//set the default arrays
		$vars['xmlrpc'] = extension_loaded('xmlrpc');
		$vars['soap'] = extension_loaded('soap');
		$vars['curl'] = extension_loaded('curl');

		//load the view
		return ee()->load->view('status_check', $vars, TRUE);   
	}

	// ----------------------------------------------------------------

	/**
	 * Status check Function
	 *
	 * @return 	void
	 */
	public function api_overview()
	{
		// Set Breadcrumb and Page Title
		ee()->cp->set_breadcrumb(ee()->webservice_settings->item('base_url'), lang(WEBSERVICE_MAP.'_module_name'));
		$this->_set_cp_var('cp_page_title', lang(WEBSERVICE_MAP.'_api_overview'));
		$vars['cp_page_title'] = lang(WEBSERVICE_MAP.'_api_overview');

		//default var array
		$vars = array();

		$apis = ee()->webservice_lib->load_apis();
		$vars['apis'] = $apis['apis'];

		//load the view
		return ee()->load->view('api_overview', $vars, TRUE);
	}
	
	// ----------------------------------------------------------------

	/**
	 * Create a new member
	 *
	 * @return 	void
	 */
	public function add_member()
	{
		//is there some data tot save?
		if(ee()->input->post('submit') != '')
		{
			$this->_save_member('new');
			
			ee()->session->set_flashdata('message_success', lang('webservice_success_add'));
			ee()->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=webservice');
		}
	
		// Set Breadcrumb and Page Title
		ee()->cp->set_breadcrumb(ee()->webservice_settings->item('base_url'), lang('webservice_module_name'));
		$this->_set_cp_var('cp_page_title', lang('webservice_add_member'));
		$vars['cp_page_title'] = lang('webservice_add_member');

		//get membergroups
		$membergroups = ee()->webservice_model->get_raw_membergroups();
		$selected_membergroups = ee()->webservice_model->get_raw_selected_membergroups();
		$vars['membergroups'][0] = '--- choose a membergroup ---';
		if(!empty($membergroups))
		{
			foreach($membergroups as $membergoup)
			{
				if(!isset($selected_membergroups[$membergoup->group_id]))
				{
					$vars['membergroups'][$membergoup->group_id] = $membergoup->group_title;
				}
			}
		}

		//get the members
		$members = ee()->webservice_model->get_raw_members();
		$selected_members = ee()->webservice_model->get_raw_selected_members();
		$vars['members'][0] = '--- choose a member ---';
		if(!empty($members))
		{
			foreach($members as $member)
			{
				if(!isset($selected_members[$member->member_id]))
				{
					$vars['members'][$member->member_id] = $member->username;
				}
			}
		}

		//set the default arrays
		$vars['connection_services'] = $this->services; 
		$vars['active'] = $this->service_active ; 
		$vars['apis'] = ee()->webservice_lib->get_api_names();
		//$vars['free_apis'] = ee()->webservice_lib->get_api_free_names(); //$this->free_apis ;
		$vars['logging'] = $this->service_logging ;
		$vars['debug'] = $this->service_debug ;
		$vars['ajax_url'] = ee()->webservice_settings->item('site_url').'?ACT='.webservice_helper::fetch_action_id('Webservice', 'ajax_cp');
		$vars['urls']['xmlrpc'] = ee()->webservice_settings->item('xmlrpc_url');
		$vars['urls']['soap'] = ee()->webservice_settings->item('soap_url');
		$vars['urls']['rest'] = ee()->webservice_settings->item('rest_url');
		$vars['urls']['custom'] = 'no url defined, mostly called from a file';
	
		//load the view
		return ee()->load->view('new_member', $vars, TRUE);   
	}
	
	// ----------------------------------------------------------------
	
	/**
	 * show channel Function
	 *
	 * @return 	void
	 */
	public function show_member()
	{
		//is there some data tot save?
		if(ee()->input->post('submit') != '')
		{
			$this->_save_member('update');
		}
		
		// Set Breadcrumb and Page Title
		ee()->cp->set_breadcrumb(ee()->webservice_settings->item('base_url'), lang('webservice_module_name'));
		$this->_set_cp_var('cp_page_title', lang('webservice_show_member'));
		$vars['cp_page_title'] = lang('webservice_show_member');

		$vars['webservice_user'] = $webservice_user = ee()->webservice_model->get_webservice_user(ee()->input->get('webservice_id'));

		//set the default arrays
		$vars['member'] = ee()->webservice_model->get_member($webservice_user->member_id);
		$vars['membergroup'] = ee()->webservice_model->get_membergroup($webservice_user->membergroup_id);
		$vars['connection_services'] = $this->services; 
		$vars['active'] = $this->service_active ; 
		$vars['hidden'] = array(
			'webservice_id' => $webservice_user->webservice_id
		);
		$vars['apis'] = ee()->webservice_lib->get_api_names();//$this->apis ;
		//$vars['free_apis'] = ee()->webservice_lib->get_api_free_names(); //$this->free_apis ;
		$vars['logging'] = $this->service_logging ;
		//$vars['debug'] = $this->service_debug ; //@tmp disabled, not yet implemented
		$vars['urls']['xmlrpc'] = ee()->webservice_settings->item('xmlrpc_url');
		$vars['urls']['soap'] = ee()->webservice_settings->item('soap_url');
		$vars['urls']['rest'] = ee()->webservice_settings->item('rest_url');
		$vars['urls']['custom'] = 'no url defined, mostly called from a file';

		//get the members
		$members = ee()->webservice_model->get_raw_members();
		if(!empty($members))
		{
			foreach($members as $member)
			{
				$vars['members'][$member->member_id] = $member->username;
			}
		}

		//load the view
		return ee()->load->view('show_member', $vars, TRUE);   
	}

	// ----------------------------------------------------------------
	
	/**
	 * Delete Function
	 *
	 * @return 	void
	 */
	public function delete_member()
	{
		//restoren van een email
		if(ee()->input->post('confirm') == 'ok')
		{	
			//delete api keys
			ee()->db->where('webservice_id', ee()->input->post('webservice_id'));
			ee()->db->delete('webservice_keys');
		
			//delete the db record
			ee()->db->delete('webservice_services_settings', array(
				'webservice_id' => ee()->input->post('webservice_id')
			));
			
			ee()->session->set_flashdata('message_success', lang('webservice_delete_succes'));
			ee()->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=webservice');
		}
	
		// Set Breadcrumb and Page Title
		ee()->cp->set_breadcrumb(ee()->webservice_settings->item('base_url'), lang('webservice_module_name'));
		$this->_set_cp_var('cp_page_title', lang('webservice_delete_member'));
		$vars['cp_page_title'] = lang('webservice_delete_member');

		//vars
		$vars['webservice_id'] = ee()->input->get('webservice_id');
		//$vars['webservice_user'] = ee()->webservice_model->get_webservice_user(ee()->input->get('webservice_id'));

		//load the view
		return ee()->load->view('delete', $vars, TRUE);   
	}
	
	// ----------------------------------------------------------------

	/**
	 * show channel Function
	 *
	 * @return 	void
	 */
	public function _save_member($method = '')
	{
		if(isset($_POST))
		{
			//remove submit value
			unset($_POST['submit']);

			//empty both member and membergroup
			if($method == 'new')
			{
				if($_POST['member_id'] == 0 && $_POST['membergroup_id'] == 0)
				{
					ee()->session->set_flashdata(
						'message_failure',
						lang('webservice_no_user_selected')
					);
					ee()->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=webservice'.AMP.'method=add_member');
				}
			}

			//ee()->db->where('webservice_id', $_POST['webservice_id']);
			//ee()->db->where('services', $_POST['connection_services']);
			// if($method != 'new')
			// {
			// 	ee()->db->where('webservice_id !=', $_GET['webservice_id']);
			// }
			//$check = ee()->db->get('webservice_services_settings')->row();

			//is there already a record with the same data and is the method a insert?
			//than return a error message
			// if(!empty($check) )
			// {
			// 	//set a message
			// 	ee()->session->set_flashdata(
			// 		'message_failure',
			// 		lang('webservice_error_duplicated_channel')
			// 	);
				
			// 	//redirect
			// 	ee()->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=webservice');
			// }
			
			//build the array
			$data = array(
				'entry' => array(
					'def_entry'	=> ''
				)
			);

			//insert
			if($method == 'new')
			{				
				//insert data
				ee()->db->insert('webservice_services_settings', array(
					'member_id' => $_POST['member_id'],
					'membergroup_id' => $_POST['membergroup_id'],
					'services' => !empty($_POST['connection_services']) ? implode('|',$_POST['connection_services']) : '',
					'active' => $_POST['active'],
					'logging' => $_POST['logging'],
					//'debug' => $_POST['debug'], //@tmp disabled, not yet implemented
					'apis' => !empty($_POST['api']) ? implode('|',$_POST['api']) : '',
					'free_apis' => !empty($_POST['free_api']) ? implode('|',$_POST['free_api']) : '',
					'data' => serialize($data),
					'api_keys' => $_POST['api_keys']
				)); 

				$insert_id = ee()->db->insert_id();
				
				//save the keys
				$keys = ee()->webservice_model->save_keys($insert_id, explode("\n",$_POST['api_keys']));
				
				//update the keys
				ee()->db->where('webservice_id', $insert_id);
				ee()->db->update('webservice_services_settings', array(
					'api_keys' => implode("\n", $keys['keys']),
				));
			}
			
			//update
			else 
			{				
				ee()->db->where('webservice_id', $_GET['webservice_id']);
				ee()->db->update('webservice_services_settings', array(
					'services' => !empty($_POST['connection_services']) ? implode('|',$_POST['connection_services']) : '',
					'active' => $_POST['active'],
					'logging' => $_POST['logging'],
					// 'debug' => $_POST['debug'], //@tmp disabled, not yet implemented
					'apis' => !empty($_POST['api']) ? implode('|',$_POST['api']) : '',
					'free_apis' => !empty($_POST['free_api']) ? implode('|',$_POST['free_api']) : '',
					'data' => serialize($data),
					'api_keys' => $_POST['api_keys'],
				)); 
				
				//save the keys
				$keys = ee()->webservice_model->save_keys($_GET['webservice_id'], explode("\n",$_POST['api_keys']));
				
				//update the keys
				ee()->db->where('webservice_id', $_GET['webservice_id']);
				ee()->db->update('webservice_services_settings', array(
					'api_keys' => implode("\n", $keys['keys']),
				));
			}
		}

		//duplicated keys?
		$notification = array();
		if(!empty($keys['duplicated']))
		{
			$notification = array(
				'message_failure' => ee()->lang->line(WEBSERVICE_MAP.'_duplicated_keys_error')
			);
		}

		//set a message
		$notification['message_success'] = ee()->lang->line('preferences_updated');
		ee()->session->set_flashdata($notification);

		$webservice_id = isset($_GET['webservice_id']) ? $_GET['webservice_id'] : $insert_id;
		
		//redirect
		ee()->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=webservice'.AMP.'method=show_member'.AMP.'webservice_id='.$webservice_id);
	}

	// ----------------------------------------------------------------

	/**
	 * Prepare channels to show
	 *
	 * @return 	DB object
	 */
	private function _prepare_members()
	{
		//get the members and their data
		$members = ee()->webservice_model->get_webservice_users();
		
		//set the data
		if (!empty($members))
		{
			$vars = array();
			foreach ($members as $member)
			{
				$data['webservice_id'] = $member->webservice_id;
				$data['member_id'] = $member->member_id;
				$data['membergroup_id'] = $member->membergroup_id;
				$data['username'] = ee()->webservice_model->get_username($member->member_id);;
				$data['group_title'] = ee()->webservice_model->get_membergroup_title($member->membergroup_id);
				$data['services'] = !empty($member->services) ? $member->services : 'not set';
				$data['apis'] = !empty($member->apis) ? $member->apis : 'not set';
				$data['free_apis'] = !empty($member->free_apis) ? $member->free_apis : 'not set';
				$data['active'] = $member->active == 1 ? 'yes' : 'no';
				//$data['data'] = unserialize($member->data);

				$vars[] = $data;
			}
			
			//return the data
			return $vars;
		}
		
		//return empty array
		return array();
	}
		
	/**
	 * Documentation Function
	 *
	 * @return 	void
	 */
	public function documentation()
	{
		// Set Breadcrumb and Page Title
		ee()->cp->set_breadcrumb(ee()->webservice_settings->item('base_url'), lang('webservice_module_name'));
		$this->_set_cp_var('cp_page_title', lang('webservice_documentation'));
		$vars['cp_page_title'] = lang('webservice_documentation');

		//load the view
		return ee()->load->view('documentation', $vars, TRUE);   
	}

	// ----------------------------------------------------------------

	/**
	 * Overview Function
	 *
	 * @return 	void
	 */
	public function logs()
	{
		//clear log?
		if(isset($_GET['clear_log']) && $_GET['clear_log'])
		{
			ee()->db->truncate('webservice_logs');
		}

		// Set Breadcrumb and Page Title
		ee()->cp->set_breadcrumb(ee()->webservice_settings->item('base_url'), lang('webservice_module_name'));
		$this->_set_cp_var('cp_page_title', lang('webservice_logs'));
		$vars['cp_page_title'] = lang('webservice_logs');

		//set vars
		$vars['theme_url'] = ee()->webservice_settings->item('theme_url');
		$vars['base_url_js'] = ee()->webservice_settings->item('base_url_js');
		$vars['table_headers'] = $this->logs_table_headers;
		$vars['ajax_url'] = ee()->webservice_settings->item('site_url').'?ACT='.webservice_helper::fetch_action_id('Webservice', 'ajax_cp');

		//load the view
		return ee()->load->view('logs', $vars, TRUE);  
	}
	
	// ----------------------------------------------------------------

	/**
	 * This method will be called by the table class to get the results
	 *
	 * @return 	void
	 */
	public function _logs_data($state)
	{
		$offset = $state['offset'];
		$order = $state['sort'];

		$results = ee()->webservice_model->get_all_logs('', $this->show_per_page, $offset, $order);

		$rows = array();

		if(!empty($results))
		{
			foreach($results as $key=>$val)
			{
				//get the extra data
				$extra = @unserialize($val->data);
				$extra_id = isset($extra['id']) ? ' (ID:'.$extra['id'].')' : '' ;

				$rows[] = array(
					WEBSERVICE_MAP.'_log_id' => $val->log_id,
					WEBSERVICE_MAP.'_time' => $val->time != '' ? ee()->localize->format_date('%d-%m-%Y %g:%i:%s', $val->time, false) : '-',
					WEBSERVICE_MAP.'_username' => $val->username,
					WEBSERVICE_MAP.'_ip' => $val->ip,
					WEBSERVICE_MAP.'_service' => $val->service,
					WEBSERVICE_MAP.'_method' => $val->method,
					WEBSERVICE_MAP.'_msg' => $val->msg.$extra_id,
					WEBSERVICE_MAP.'_show_queries' => '<img src="'.ee()->config->item('site_url').'themes/cp_themes/default/images/indicator.gif" class="loader" style="display: none;"/><a href="javascript:;" class="show_queries" data-log-id="'.$val->log_id.'">Show</a>',
				);
			}
		}
		//empty
		else
		{
			$rows[] = array(
				WEBSERVICE_MAP.'_log_id' => '',
				WEBSERVICE_MAP.'_time' => '',
				WEBSERVICE_MAP.'_username' => '',
				WEBSERVICE_MAP.'_ip' => '',
				WEBSERVICE_MAP.'_service' => '',
				WEBSERVICE_MAP.'_method' => '',
				WEBSERVICE_MAP.'_msg' => '',
				WEBSERVICE_MAP.'_show_queries' => '',
			);
		}

		//return the data
		return array(
			'rows' => $rows,
			'pagination' => array(
				'per_page'   => $this->show_per_page,
				'total_rows' => ee()->webservice_model->count_logs(),
			),
		);
	}



	// ----------------------------------------------------------------

	/**
	 * Overview Function
	 *
	 * @return 	void
	 */
	public function api_keys()
	{
		//set vars
		$vars['theme_url'] = ee()->webservice_settings->item('theme_url');
		$vars['base_url_js'] = ee()->webservice_settings->item('base_url_js');
		$vars['table_headers'] = $this->api_keys_table_headers;

		//load the view
		return ee()->load->view('api_keys', $vars, TRUE);  
	}
	
	// ----------------------------------------------------------------

	/**
	 * This method will be called by the table class to get the results
	 *
	 * @return 	void
	 */
	public function _api_keys_data($state)
	{
		$offset = $state['offset'];
		$order = $state['sort'];

		$results = ee()->webservice_model->get_all_api_keys('', $this->show_per_page, $offset, $order);

		$rows = array();

		if(!empty($results))
		{
			foreach($results as $key=>$val)
			{
				$rows[] = array(
					//WEBSERVICE_MAP.'_api_key_id' => $val->api_key_id,
					WEBSERVICE_MAP.'_api_key' => $val->api_key,
					WEBSERVICE_MAP.'_edit' => '<a href="'.BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=webservice'.AMP.'method=show_member'.AMP.'webservice_id='.$val->webservice_id.'">Edit</a>',
				);
			}
		}
		//empty
		else
		{
			$rows[] = array(
				WEBSERVICE_MAP.'_api_key_id' => '',
			);
		}

		//return the data
		return array(
			'rows' => $rows,
			'pagination' => array(
				'per_page'   => $this->show_per_page,
				'total_rows' => ee()->webservice_model->count_api_keys(),
			),
		);
	}
	
	// ----------------------------------------------------------------

	/**
	 * Settings Function
	 *
	 * @return 	void
	 */
	public function settings()
	{
		//is there some data tot save?
		if(ee()->input->post('submit') != '')
		{
			ee()->webservice_settings->save_post_settings();
		}
				
		// Set Breadcrumb and Page Title
		ee()->cp->set_breadcrumb(ee()->webservice_settings->item('base_url'), lang('webservice_module_name'));
		$this->_set_cp_var('cp_page_title', lang('webservice_settings'));
		$vars['cp_page_title'] = lang('webservice_settings');

		//default var array
		$vars = array();
		
		//license key
		$license_key = ee()->webservice_settings->item('license_key');
		$report_stats = ee()->webservice_settings->item('report_stats');
		$free_apis = ee()->webservice_settings->item('free_apis');
		$ip_blacklist = ee()->webservice_settings->item('ip_blacklist');
		$url_trigger = ee()->webservice_settings->item('url_trigger', 'webservice');
		$super_admin_key = ee()->webservice_settings->item('super_admin_key');
		$rest_output_header = ee()->webservice_settings->item('rest_output_header');

		//Debug
		$debug_yes = ee()->webservice_settings->item('debug') ? true : false;
		$debug_no = !ee()->webservice_settings->item('debug') ? true : false;

		//vars for the view and the form
		$vars['settings']['backup'] = array(
			'license_key'   => form_input('license_key', $license_key),	
			WEBSERVICE_MAP.'_report_stats'  => array(form_dropdown('report_stats', array('1' => 'yes', '0' => 'no'), $report_stats), 'PHP & EE versions will be anonymously reported to help improve the product.'),
			WEBSERVICE_MAP.'_free_apis'   => array(form_multiselect('free_apis[]', ee()->webservice_lib->get_api_free_names(), $free_apis), 'the selected free api require <b>no</b> inlog.'),
			WEBSERVICE_MAP.'_ip_blacklist'   => array(form_input('ip_blacklist', $ip_blacklist), 'IP seperated by a pipline (|)'),
			WEBSERVICE_MAP.'_url_trigger'   => array(form_input('url_trigger', $url_trigger), 'Trigger segment_1 in de url'),
			WEBSERVICE_MAP.'_super_admin_key'   => array(form_input('super_admin_key', $super_admin_key), 'The super admin API key. With this key you can login as super admin. <br /><b style="color:red;">Be carefull with it, it provides full access to the API.</b>'),
			WEBSERVICE_MAP.'_rest_output_header'   => array(form_input('rest_output_header', $rest_output_header), 'Set the output header for the rest service, handy in some cases with "access control allow origin" issues. <br/><b>For example:</b> <i>Access-Control-Allow-Origin: *</i>'),
			WEBSERVICE_MAP.'_site_id_strict'  => array(form_dropdown('site_id_strict', array('1' => 'yes', '0' => 'no'), ee()->webservice_settings->item('site_id_strict')), 'Handle strict site_id usage.'),
			WEBSERVICE_MAP.'_testing_tool_url'   => array(form_input('testing_tool_url', ee()->webservice_settings->item('testing_tool_url')), 'This address is used by the testing tool'),



			//WEBSERVICE_MAP.'_ip_whitelist'   => form_input('ip_whitelist', $ip_whitelist),
			'debug'   => form_dropdown('debug', array('1' => 'yes', '0' => 'no'), ee()->webservice_settings->item('debug')),
			//'no_inlog_channels' => array(form_multiselect( 'no_inlog_channel[]', $no_inlog_channels, $no_inlog_channels_selected), 'Only apply on the search and read method'),
		);

		//load the view
		return ee()->load->view('settings', $vars, TRUE);   
	}
	
	// ----------------------------------------------------------------
	
	/**
	 * Retrieve site path
	 */
	function get_site_path()
	{
		// extract path info
		$site_url_path = parse_url(ee()->functions->fetch_site_index(), PHP_URL_PATH);

		$path_parts = pathinfo($site_url_path);
		$site_path = $path_parts['dirname'];

		$site_path = str_replace("\\", "/", $site_path);

		return $site_path;
	}	

	// ----------------------------------------------------------------
	// Testing methods
	// ----------------------------------------------------------------
	 
	/**
	 * Retrieve site path
	 */
	function testing_tools()
	{
		/** ---------------------------------------
		/** lad the overview of all apis if not anyone is selected
		/** ---------------------------------------*/
		if(!isset($_GET['api']))
		{
			//get all the apis
			$apis = ee()->webservice_lib->load_apis();

			//load the view
			return ee()->load->view('testing_tools/tools', $apis, TRUE);
		}

		/** ---------------------------------------
		/** get the selected api
		/** ---------------------------------------*/
		$api_settings =  ee()->webservice_lib->get_api($_GET['api']);

		/** ---------------------------------------
		/** load the methods
		/** ---------------------------------------*/
		if(!isset($_GET['api_method']))
		{
			//load the view
			return ee()->load->view('testing_tools/methods', $api_settings, TRUE);
		}

		/** ---------------------------------------
		/** load the form
		/** ---------------------------------------*/

		$vars = array();

		//action
		$vars['response'] = '';
		if(isset($_POST['submit']))
		{
			ee()->load->library('webservice_testing_tool');
			$vars['response'] = ee()->webservice_testing_tool->init();
		}

		//get the channel names
		$channels = ee()->webservice_model->get_channel_names();

		//set the data
		$vars['fields']['data'] = array(
			'path_xmlrpc'   => form_input(array(
				'name' => 'path',
				'value' => reduce_double_slashes($this->api_url.'/index.php/'.ee()->webservice_settings->item('url_trigger')).'/xmlrpc',
				'readonly' => 'readonly'
			)),
			'path_soap'   => form_input(array(
				'name' => 'path',
				'value' => reduce_double_slashes($this->api_url.'/index.php/'.ee()->webservice_settings->item('url_trigger')).'/soap?wsdl',
				'readonly' => 'readonly'
			)),
			'path_rest'   => form_input(array(
				'name' => 'path',
				'value' => reduce_double_slashes($this->api_url.'/index.php/'.ee()->webservice_settings->item('url_trigger')).'/rest/'.$_GET['api_method'],
				'readonly' => 'readonly'
			)),
			'path_custom'   => form_input(array(
				'name' => 'path',
				'value' => 'custom',
				'readonly' => 'readonly'
			))
		);

		$fields = isset($api_settings->test->{$_GET['api_method']}) ? $api_settings->test->{$_GET['api_method']} : array();
		if(isset($fields) && !empty($fields))
		{
			foreach($fields as $field)
			{
				//get dynamic value
				if(isset($field->value) && preg_match('/call::/', $field->value, $match))
				{
					$method = array_filter(explode(":", str_replace($match[0], '',$field->value)));
					$_method = array_pop($method);
					$_class = array_shift($method);
					$value = ee()->{$_class}->{$_method}();
				}
				else if(!isset($field->value))
				{
					$value = '';
				}
				else
				{
					$value = $field->value;
				}

				//no type?
				if(!isset($field->type))
				{
					$field->type = 'form_input';
				}

				switch($field->type)
				{
					case "form_dropdown":
						$value = form_dropdown('field:'.$field->name, $value, '');
						break;
					case "form_input":
						$value = form_input('field:'.$field->name, $value);
						break;
					case "form_textarea":
						$value = form_textarea('field:'.$field->name, $value);
						break;
				}
				$vars['fields']['data'][$field->name] = $value;
			}
		}

		//set the method
		$vars['method'] = $_GET['api_method'];
		$vars['action_method'] = $_GET['api_method'];

		//load the view
		//return ee()->load->view('testing_tools/entry', $vars, TRUE);

		//load the view
		return ee()->load->view('testing_tools/api', $vars, TRUE);
	}

	// --------------------------------------------------------------------

	/**
	 * Set cp var
	 *
	 * @access     private
	 * @param      string
	 * @param      string
	 * @return     void
	 */
	private function _set_cp_var($key, $val)
	{
		if (version_compare(APP_VER, '2.6.0', '<'))
		{
			ee()->cp->set_variable($key, $val);
		}
		else
		{
			ee()->view->$key = $val;
		}
	}


	
}
/* End of file mcp.webservice.php */
/* Location: /system/expressionengine/third_party/webservice/mcp.webservice.php */