<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * API - base file
 *
 * This class will be extends by the API classes in /LIBRARIES/API/
 *
 * @package		webservice
 * @category	Modules
 * @author		Rein de Vries <info@reinos.nl>
 * @link		http://reinos.nl/add-ons/entry-api
 * @license  	http://reinos.nl/add-ons/commercial-license
 * @copyright 	Copyright (c) 2014 Reinos.nl Internet Media
 */

/**
 * Include the config file
 */
require_once PATH_THIRD.'webservice/config.php';

class Webservice_base_api
{	
	/*
	*	error str
	*/
	public $error_str;

	/*
	*	auth retrun data
	*/
	public $auth_data;
	
	/*
	*	The userdata
	*/
	public $userdata;

	/*
	*	The servicesdata
	*/
	public $servicedata;
	
	/*
	*	the custom fields
	*/
	public $fields;
	
	/*
	*	assigned fields for the user
	*/
	public $assigned_channels;
	
	/*
	*	Type of service
	*/
	public $type;
	
	/*
	*	the log data
	*/
	public $log_data = array();

	/*
	*	The api
	*/
	public $api;

	/*
	*	The backtrace
	*/
	public $backtrace;

	public $session_id = null;
	
	
	/**
	 * Constructor
	 */
	public function __construct()
	{		
		//get the instance
		//$this->EE =& get_instance();
		
		//load the genereal helper
		ee()->load->library('webservice_lib');
		ee()->load->library('uri');	
		//ee()->load->library('webservice_upload');
		
				
		//set the type of api
		//echo $this->api_type = $type;
		
		//require the default settings
		require PATH_THIRD.'webservice/settings.php';

		$this->backtrace = debug_backtrace();		
	}
	
	// ----------------------------------------------------------------
	
	/**
	 * Destroy the session
	 */
	public function __destruct()
	{//var_dump(ee()->session->userdata);
		//ee()->session->destroy();
	}

	// ----------------------------------------------------------------

	/**
	 * Set the type for the services
	 * 
	 * @param string $method [description]
	 */
	public function set_xmlrpc($method = '') 
	{
		$this->type = 'xmlrpc';
	}
	public function set_rest() 
	{
		$this->type = 'rest';
	}
	public function set_soap() 
	{
		$this->type = 'soap';
	}

	// ----------------------------------------------------------------

	/**
	 * The default checks
	 * 
	 * @param  [type] $username 
	 * @param  [type] $password 
	 * @return [type]           
	 */
	public function default_checks($auth = array(), $method = '', $site_id = 1)
	{
		//set the correct site to preform our actions
		ee()->config->site_prefs('', $site_id);
		
		//is admin, yeah sure you can do what you want
		//set the session so we can create a good session for him
		if(ee()->session->userdata('member_id') == 1 && ee()->session->userdata('session_id') != '')
		{
			$auth = array('session_id' => ee()->session->userdata('session_id'));
		}
		
		/** ---------------------------------------
		/**  API Licence check
		/** ---------------------------------------*/

		//stats and license
		if(!webservice_helper::license_check())
		{
			//generate error
			return array(
				'succes' => false,
				'message' => $this->service_error['error_license']
			);
		}

		//report stats
		webservice_helper::stats();

		/** ---------------------------------------
		/**  No auth data
		/** ---------------------------------------*/
		if(empty($auth) && ee()->webservice_lib->has_free_access($method, ee()->session->userdata('username')) == 0)
		{
			//generate error
			return array(
				'succes' => false,
				'message' => $this->service_error['error_access']
			);	
		}

		/** ---------------------------------------
		/**  Auth the user
		/** ---------------------------------------*/
		$is_auth = $this->auth($auth);

		if(ee()->webservice_lib->has_free_access($method, ee()->session->userdata('username')) != 1)
		{	
			if ( ! $is_auth)
			{
				//generate error
				return array(
					'succes' => false,
					'message' => $this->service_error['error_access']
				);
			}	
		}

		/** ---------------------------------------
		/**  (1) Service check, is the services active
		/** ---------------------------------------*/
		if(ee()->webservice_lib->has_free_access($method, ee()->session->userdata('username')) != 1)
		{	
			if( (!isset($this->servicedata->active) || ! $this->servicedata->active) && $this->servicedata->admin == false )
			{
				//generate error
				return array(
					'succes' => false,
					'message' => $this->service_error['error_inactive']
				);
			}
		}
		
		/** ---------------------------------------
		/**  (2) Service check, is the services selected (check by uri)
		/** ---------------------------------------*/
		if(ee()->webservice_lib->has_free_access($method, ee()->session->userdata('username')) != 1)
		{	
			if( ( (@stripos($this->servicedata->services, ee()->uri->segment(2)) === false) && (@stripos($this->servicedata->services, ee()->session->cache[WEBSERVICE_MAP]['API']) === false))  && $this->servicedata->admin == false)
			//if( (@stripos($this->servicedata->services, ee()->uri->segment(2)) === false)  && $this->servicedata->admin == false)
			{
				//generate error
				return array(
					'succes' => false,
					'message' => $this->service_error['error_inactive']
				);
			}
		}

		/** ---------------------------------------
		/**  (3) IP blacklist check
		/** ---------------------------------------*/
		$ip_blacklist = array_filter(explode('|', ee()->webservice_settings->item('ip_blacklist')));
		if(in_array($_SERVER['REMOTE_ADDR'], $ip_blacklist))
		{
			//generate error
			return array(
				'succes' => false,
				'message' => $this->service_error['error_api_ip']
			);
		}

		/** ---------------------------------------
		/**  API check, runs this api for this server
		/** ---------------------------------------*/
		if(ee()->webservice_lib->has_free_access($method, ee()->session->userdata('username')) != 1)
		{
			if( (@stripos($this->servicedata->apis, $this->api_type) === false)  && $this->servicedata->admin == false)
			{
				//generate error
				return array(
					'succes' => false,
					'message' => $this->service_error['error_api_type']
				);
			}
		}

		//generate succes message
		return array(
			'succes' => true
		);
	}

	// ----------------------------------------------------------------

	/**
	 * Auth a user
	 * 
	 * @param  [string] $username 
	 * @param  [string] $password 
	 * @return void           
	 */
	public function auth($auth = array(), $new_session = false)
	{

		//no value? shame on you, you must auth!
		if(empty($auth))
		{
			$this->error_str = 'no_auth_data';
			return false;
		}

		//auth, based on username and password
		if(isset($auth['username']) && isset($auth['password']))
		{
			$res_auth = $this->auth_data = $this->base_authenticate_username($auth['username'], $auth['password']);

			//is response false?
			if(!$res_auth['success'])
			{
				$this->error_str = $res_auth['message'];
				return false;
			}

			//create new session if needed
			if($new_session)
			{
				$this->session_id = ee()->session->create_new_session($res_auth['member_id']);
			}

			$this->_fetch_member_data($res_auth['member_id']);
			$this->_setup_channel_privs();
			$this->_setup_module_privs();
			$this->_setup_template_privs();
			$this->_setup_assigned_sites();
		}

		//auth session
		else if (isset($auth['session_id']))
		{
			//auth the session with some fake data
			$res_auth = $this->authenticate_session($auth['session_id'], true);

			//is response false?
			if($res_auth == false)
			{
				$this->error_str = 'no valid session_id';
				return false;
			}

			//create new session if needed
			if($new_session)
			{
				$this->session_id = ee()->session->create_new_session($res_auth['member_id']);
			}

			//set some data
			$this->_fetch_member_data($res_auth['member_id']);
			$this->_setup_channel_privs();
			$this->_setup_module_privs();
			$this->_setup_template_privs();
			$this->_setup_assigned_sites();

			//update session
			//not working, it delete the session :-|
			//ee()->session->update_session();
		}

		//api_keys?
		else if(isset($auth['api_key']) && $auth['api_key'] != '')
		{
			//search the api key
			$api_key = ee()->webservice_model->search_api_key($auth['api_key']);

			//is this a super admin api key?
			$super_api_key = ee()->webservice_settings->item('super_admin_key') == $auth['api_key'];

			$new_member_id = null;

			//we got result, so the use has a correct auth
			if($api_key != false)
			{
				//check if the user_id is empty, otherwise whe must get the member id
				//when the membergroup is not empty
				if(($api_key->member_id == 0 || $api_key->member_id == '') && ($api_key->membergroup_id != '' || $api_key->membergroup_id != 0))
				{
					ee()->db->where('group_id', $api_key->membergroup_id);
					$query = ee()->db->get('members');
					
					if($query->num_rows() > 0)
					{
						$api_key->member_id = $query->row()->member_id;
					}
				}

				//set the new member_id
				$new_member_id = $api_key->member_id;
				
			}

			//super admin key
			else if($super_api_key)
			{
				$new_member_id = 1;
			}

			//do we have a new member_id
			if($new_member_id != null)
			{
				// create a new session id
	    		ee()->session->validation = null;
	   		 	//$session_id = ee()->session->create_new_session((int)$new_member_id);

				//create new session if needed
				if($new_session)
				{
					$this->session_id = ee()->session->create_new_session((int)$new_member_id);
				}

				//create al other stuff for the logged in member
				$this->_fetch_member_data($new_member_id);
				$this->_setup_channel_privs();
				$this->_setup_module_privs();
				$this->_setup_template_privs();
				$this->_setup_assigned_sites();
			}	
		}

		//no auth? shame on you
		else
		{
			$this->error_str = 'no_auth_data';
			return false;
		}

		//look if the username is filled in, otherwise no access
		if(ee()->session->userdata('member_id') == '')
		{
			$this->error_str = 'no member_id';
			return false;
		}

		//get the services data
		//admin can do everything
		if(ee()->session->userdata('member_id') == 1)
		{
			$this->servicedata = new stdClass();
			$this->servicedata->active = true;
			$this->servicedata->services = ee()->uri->segment(2);
			$this->servicedata->admin = true;
		}
		else
		{	
			//zoek eerst de member_id, most of the time this one has more right
			ee()->db->where('member_id', ee()->session->userdata('member_id'));
			$query = ee()->db->get('webservice_services_settings');

			//no member_id, then search the group_id
			if($query->num_rows == 0)
			{
				ee()->db->where('membergroup_id', ee()->session->userdata('group_id'));
				$query = ee()->db->get('webservice_services_settings');
			}

			//get the result
			$this->servicedata = $query->row();

			if(isset($this->servicedata->data))
			{
				$this->servicedata->data = unserialize($this->servicedata->data);
			}
			else
			{
				$this->servicedata = new stdClass();
				$this->servicedata->active = false;
			}
			//no admin
			$this->servicedata->admin = false;
		}

		return array(
			'success' => true,
			'member_id'	=> ee()->session->userdata['member_id'],
			'session_id'	=> $this->session_id

		);
	}

	// --------------------------------------------------------------------
        
    /**
     * Authenticate Session
	 *
     */
    public function authenticate_session($session_id = '')
    {
    	//ee()->session->delete_old_sessions();

        // check for session id
        if ($session_id == '' || empty($session_id))
        {
            return false;
        }
        
        // check if session id exists in database and get member id
        ee()->db->select('member_id, user_agent, fingerprint');
        ee()->db->where('session_id', $session_id);
        $query = ee()->db->get('sessions');
        
        if (!$row = $query->row())
        {
            return false;
        }

        //set member_id
        $member_id = $row->member_id;

        // get member data
        ee()->db->select('*');
        ee()->db->where('member_id', $member_id);
        $query = ee()->db->get('members');
        
        $member_data = $query->row();

//        //set some session data
//        ee()->session->sdata['member_id'] = $member_id;
//		ee()->session->sdata['session_id'] = $session_id;
//		ee()->session->validation = null;
//		ee()->session->sess_crypt_key = $member_data->crypt_key;

		return array(
			'member_id' => $member_id,
			'username' => $member_data->username,
			'screen_name' => $member_data->screen_name
		);
    }	

    // ----------------------------------------------------------------

	/**
	 * Auth based on username
	 * 
	 * @param  string $username  
	 * @param  string $password  
	 * @param  array  $post_data 
	 * @return array            
	 */
	public function base_authenticate_username($username = '', $password = '')
	{
		//no username
		if(empty($username))
		{
			return $this->service_error['error_auth'];
		}

		// get member id
        $query = ee()->db->get_where('members', array('username' => $username));
        if ($query == false || $query->num_rows() == 0)
        {
        	return $this->service_error['error_auth'];
        }

        $row = $query->row();
        
        $member_id = $row->member_id;

       	// authenticate member
       	$auth = $this->authenticate_member($member_id, $password);

       	if(!$auth)
       	{
			return array(
				'message' => 'Auth error',
				'success' => false
			);
       		//return $this->service_error['error_auth'];
       	}
       	
       	/** ---------------------------------------
		/** return response
		/** ---------------------------------------*/
		$auth['success'] = true;
		return $auth;
	}

	// ----------------------------------------------------------------

	/**
	 * Auth based on username
	 * 
	 * @param  string $username  
	 * @param  string $password  
	 * @param  array  $post_data 
	 * @return array            
	 */
//	public function base_authenticate_email($email = '', $password = '')
//	{
//		// get member id
//        $query = ee()->db->get_where('members', array('email' => $email));
//
//        if (!$row = $query->row())
//        {
//        	return $this->service_error['error_access'];
//        }
//
//        $member_id = $row->member_id;
//
//        // authenticate member
//       	$auth = $this->authenticate_member($member_id, $password);
//
//       	if(!$auth)
//       	{
//       		return $this->service_error['error_access'];
//       	}
//
//       	/** ---------------------------------------
//		/** return response
//		/** ---------------------------------------*/
//		$this->service_error['succes_auth']['data'][0] = $auth;
//		return $this->service_error['succes_auth'];
//	}

	// --------------------------------------------------------------------
        
    /**
     * Authenticate Member
     */
    public function authenticate_member($member_id, $password)
    {
        // load auth library
        ee()->load->library('auth');

        // authenticate member id
        $userdata = ee()->auth->authenticate_id($member_id, $password);

        if (!$userdata)
        {
            return false;
        }

        // get member details
        $query = ee()->db->get_where('members', array('member_id' => $member_id));
        $member = $query->row();

        return array(
            'member_id' => $member_id,
            'username' => $member->username,
            'screen_name' => $member->screen_name
        );
    }

    // --------------------------------------------------------------------

	/**
	 * Setup Assigned Sites
	 *
	 * @return void
	 */
	protected function _setup_assigned_sites()
	{
		// Fetch Assigned Sites Available to User

		$assigned_sites = array();

		if (ee()->session->userdata('group_id') == 1)
		{
			$qry = ee()->db->select('site_id, site_label')
								->order_by('site_label')
								->get('sites');
		}
		else
		{
			// Groups that can access the Site's CP, see the site in the 'Sites' pulldown
			$qry = ee()->db->select('es.site_id, es.site_label')
								->from(array('sites es', 'member_groups mg'))
								->where('mg.site_id', ' es.site_id', FALSE)
								->where('mg.group_id', ee()->session->userdata('group_id'))
								->order_by('es.site_label')
								->get();
		}

		if ($qry->num_rows() > 0)
		{
			foreach ($qry->result() as $row)
			{
				$assigned_sites[$row->site_id] = $row->site_label;
			}
		}

		ee()->session->userdata['assigned_sites'] = $assigned_sites;
	}

	// --------------------------------------------------------------------

	/**
	 * Setup CP Channel Privileges
	 *
	 * @return void
	 */
	protected function _setup_channel_privs()
	{
		// Fetch channel privileges

		$assigned_channels = array();

		if (ee()->session->userdata('group_id') == 1)
		{
			ee()->db->select('channel_id, channel_title');
			ee()->db->order_by('channel_title');
			$res = ee()->db->get_where(
				'channels',
				array('site_id' => ee()->config->item('site_id'))
			);
		}
		else
		{
			ee()->db->save_queries = true;
			$res = ee()->db->select('ec.channel_id, ec.channel_title')
				->from(array('channel_member_groups ecmg', 'channels ec'))
				->where('ecmg.channel_id', 'ec.channel_id',  FALSE)
				->where('ecmg.group_id', ee()->session->userdata('group_id'))
				->where('site_id', ee()->config->item('site_id'))
				->order_by('ec.channel_title')
				->get();
		}

		if ($res->num_rows() > 0)
		{
			foreach ($res->result() as $row)
			{
				$assigned_channels[$row->channel_id] = $row->channel_title;
			}
		}

		$res->free_result();

		ee()->session->userdata['assigned_channels'] = $assigned_channels;
	}

	// --------------------------------------------------------------------

	/**
	 * Setup Module Privileges
	 *
	 * @return void
	 */
	protected function _setup_module_privs()
	{
		$assigned_modules = array();

		ee()->db->select('module_id');
		$qry = ee()->db->get_where('module_member_groups',
										array('group_id' => ee()->session->userdata('group_id')));

		if ($qry->num_rows() > 0)
		{
			foreach ($qry->result() as $row)
			{
				$assigned_modules[$row->module_id] = TRUE;
			}
		}

		ee()->session->userdata['assigned_modules'] = $assigned_modules;

		$qry->free_result();
	}

	// --------------------------------------------------------------------

	/**
	 * Setup Template Privileges
	 *
	 * @return void
	 */
	protected function _setup_template_privs()
	{
		$assigned_template_groups = array();

		ee()->db->select('template_group_id');
		$qry = ee()->db->get_where('template_member_groups',
										array('group_id' => ee()->session->userdata('group_id')));


		if ($qry->num_rows() > 0)
		{
			foreach ($qry->result() as $row)
			{
				$assigned_template_groups[$row->template_group_id] = TRUE;
			}
		}

		ee()->session->userdata['assigned_template_groups'] = $assigned_template_groups;

		$qry->free_result();
	}

	// --------------------------------------------------------------------

	/**
	 * Perform the big query to grab member data
	 *
	 * @return 	object 	database result.
	 */
	private function _fetch_member_data($member_id = 0)
	{
		// Query DB for member data.  Depending on the validation type we'll
		// either use the cookie data or the member ID gathered with the session query.

		ee()->db->from(array('members m', 'member_groups g'))
			->where('g.site_id', (int) ee()->config->item('site_id'))
			->where('m.group_id', ' g.group_id', FALSE);

		ee()->db->where('member_id', (int) $member_id);

		$member_query = ee()->db->get();

		// Turn the query rows into array values
		foreach ($member_query->row_array() as $key => $val)
		{
			if ($key != 'crypt_key')
			{
				ee()->session->userdata[$key] = $val;
			}
		}

		//set member_id
		ee()->session->userdata['member_id'] = $member_id;


	}

}

/* End of file webservice_server_helper.php */
/* Location: /system/expressionengine/third_party/webservice/libraries/webservice_server_helper.php */