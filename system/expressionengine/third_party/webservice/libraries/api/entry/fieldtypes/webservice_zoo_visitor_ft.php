<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Zoo Visitor class
 *
 * @package		webservice
 * @category	Modules
 * @author		Rein de Vries <info@reinos.nl>
 * @license  	http://reinos.nl/add-ons/commercial-license
 * @link        http://reinos.nl/add-ons//add-ons/entry-api
 * @copyright 	Copyright (c) 2014 Reinos.nl Internet Media
 */
 
 /*
	 array(
		'member_account' => array(
			'email' => 'djrein86@hotmail.com',
			'username' => 'Rein de Vries',
			'password' => 'test123',
			'screen_name' => 'Rein de Vries',
			'group_id' => '4',			
		),
	);
);

http://devot-ee.com/add-ons/support/zoo-visitor/viewthread/9847
http://devot-ee.com/add-ons/support/zoo-visitor/viewthread/8860
http://devot-ee.com/add-ons/support/zoo-visitor/viewthread/10875#37640
 */

/**
 * Include the config file
 */
require_once PATH_THIRD.'webservice/config.php';

class Webservice_zoo_visitor_ft
{
	public $name = 'zoo_visitor';
	
	// ----------------------------------------------------------------

	/**
	 * Preps the data for saving
	 *
	 * Hint: you only have to format the data likes the publish page
	 * 
	 * @param  mixed $data  
	 * @param  bool $is_new
	 * @param  int $entry_id
	 * @return mixed string            
	 */
	public function webservice_save($data = null, $is_new = false, $entry_id = 0)
	{
		//return empty, because this we saving nothing in the channel_data table
		return '';
	}
	
	// ----------------------------------------------------------------

	/**
	 * Validate the field
	 * 
	 * @param  mixed $data  
	 * @param  bool $is_new
	 * @return bool            
	 */
	public function webservice_validate($data = null, $is_new = false, $entry_id = 0)
	{
		if(!empty($data) && is_array($data))
		{
			ee()->load->add_package_path(PATH_THIRD . 'zoo_visitor/');
			ee()->load->helper('zoo_visitor_helper');
			ee()->load->library('zoo_visitor_cp');
			ee()->load->library('zoo_visitor_lib');
			$zoo_visitor_settings = get_zoo_settings(ee());
			
			//New
			if($is_new == true)
			{
				$_POST['EE_member_id'] = '';
				$_POST['EE_group_id'] = isset($data['group_id']) ? $data['group_id'] : '4';
				$_POST['EE_username'] = isset($data['username']) ? $data['username'] : '';
				$_POST['EE_email'] = isset($data['email']) ? $data['email'] : ''; 
				$_POST['EE_screen_name'] = isset($data['screen_name']) ? $data['screen_name'] : '';
				$_POST['EE_password'] = isset($data['password']) ? $data['password'] : '';
				$_POST['EE_new_password_confirm'] = isset($data['password']) ? $data['password'] : '';
				
			}
			//update
			else if($is_new == false)
			{
				
				//get the author_id
				$member_id = ee()->db->select('author_id')->where('entry_id', $entry_id)->from('channel_titles')->get()->row()->author_id;
				
				//get the member
				$member_data = ee()->db->from('members')->where('member_id', $member_id)->get();				

				//exists
				if($member_data->num_rows() == 0)
				{
					$this->validate_error = 'no member found';
					return false;
				}
				
				//get info
				$member_data = $member_data->row();
				
				//set the data
				$_POST['EE_member_id'] = isset($data['member_id']) ? $data['member_id'] : $member_data->member_id;
				$_POST['EE_group_id'] = isset($data['group_id']) ? $data['group_id'] : $member_data->group_id;
				$_POST['EE_username'] = isset($data['username']) ? $data['username'] : $member_data->username;
				$_POST['EE_current_username'] = $member_data->username;
				$_POST['EE_email'] = isset($data['email']) ? $data['email'] : $member_data->email; 
				$_POST['EE_current_email'] = $member_data->email;
				$_POST['EE_screen_name'] = isset($data['screen_name']) ? $data['screen_name'] : $member_data->screen_name;
				$_POST['EE_current_screen_name'] = $member_data->screen_name;
				
				$_POST['EE_new_password'] = isset($data['password']) ? $data['password'] : '';
				$_POST['EE_new_password_confirm'] = isset($data['password']) ? $data['password'] : '';

			}			
		}
		
		//check username and email duplicate
		if($is_new == true)
		{
			//validate
			if($_POST['EE_group_id'] == '')
			{
				$this->validate_error = 'group_id is missing';
				return false;
			}
			
			if($_POST['EE_username'] == '')
			{
				$this->validate_error = 'username is missing';
				return false;
			}
			
			if($_POST['EE_email'] == '')
			{
				$this->validate_error = 'email is missing';
				return false;
			}
			
			if($_POST['EE_password'] == '')
			{
				$this->validate_error = 'password is missing';
				return false;
			}
			
			//do a quick check on the username.
			$check_username = ee()->db->from('members')->where('username', $_POST['EE_username'])->get();			
			if($check_username->num_rows() > 0)
			{
				$this->validate_error = 'duplicated username';
				return false;
			}
			
			//do a quick check on the email.
			$check_email = ee()->db->from('members')->where('email', $_POST['EE_email'])->get();
			if($check_email->num_rows() > 0)
			{
				$this->validate_error = 'duplicated email';
				return false;
			}
		}
		
		return true;
	}

	// ----------------------------------------------------------------------
	
	/**
	 * Preprocess the data to be returned
	 * 
	 * @param  mixed $data  
	 * @param  string $free_access
	 * @param  int $entry_id
	 * @return mixed string
	 */
	public function webservice_pre_process($data = null, $free_access = false, $entry_id = 0)
	{
		//get the author_id
		$member_id = ee()->db->select('author_id')->where('entry_id', $entry_id)->from('channel_titles')->get()->row()->author_id;
		
		//get the member
		$member_data = ee()->db->
			select('member_id,group_id,username,screen_name,email,url,location,occupation,interests,bday_d,bday_m,bday_y,aol_im,yahoo_im,msn_im,icq,bio')
			->from('members')
			->where('member_id', $member_id)
			->get();				
		
		//get info
		$data = $member_data->row_array();

		return $data;
	}

	// ----------------------------------------------------------------------
	
	/**
	 * delete field data, before the entry is deleted
	 *
	 * Hint: EE will mostly do everything for you, because the delete() function will trigger
	 * 
	 * @param  mixed $data   
	 * @param  int $entry_id
	 * @return void
	 */
	public function webservice_delete($data = null, $entry_id = 0)
	{
		//get the author_id
		$member_id = ee()->db->select('author_id')->where('entry_id', $entry_id)->from('channel_titles')->get()->row()->author_id;

		//delete also the member
		ee()->load->model('member_model');
		ee()->member_model->delete_member($member_id);
	}
		
}