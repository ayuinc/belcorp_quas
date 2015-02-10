<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * File fieldtype file
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

class Webservice_file_ft
{
	public $name = 'file';
	
	// ----------------------------------------------------------------

	/**
	 * Preps the data for saving
	 * 
	 * @param  mixed $data  
	 * @param  bool $is_new
	 * @param  int $entry_id
	 * @return void            
	 */
	public function webservice_save($data = null, $is_new = false, $entry_id = 0)
	{
		$file_data = $this->do_upload_file($data, $this->field_data);
		return $file_data['file_dir'];
	}

	// ----------------------------------------------------------------

	/**
	 * Validate the field
	 * 
	 * @param  string $data  
	 * @param  bool $is_new
	 * @return void            
	 */
	public function webservice_validate($data = null, $is_new = false)
	{
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
		ee()->load->library('file_field');
		return ee()->file_field->parse_field($data);
	}

	// ----------------------------------------------------------------
	// All code below is tmp, need some kickass api for files
	// http://ellislab.com/forums/viewthread/238953/
	// http://expressionengine.stackexchange.com/questions/16806/filemanager-api
	// ----------------------------------------------------------------
	
	/**
	 * Upload a file for a entry to the server 
	 * 
	 * @param  array  $post_data 
	 * @param  array  $field     
	 * @return array           
	 */
	protected function do_upload_file($post_data = array(), $field)
	{
		//require the default settings
		require PATH_THIRD.'webservice/settings.php';

		//is the data set?
		if(isset($post_data['filedata']) && !empty($post_data['filedata']) && isset($post_data['filename']) && !empty($post_data['filename']))
		{		
			//get the default dir id				
			$dir_ids = ee()->functions->fetch_file_paths();
			$_dir_id = '';
			if(!empty($dir_ids))
			{
				$_dir_id = array_keys($dir_ids);
				$_dir_id =  array_shift($_dir_id);
			}
			
			//set the dir_id
			$dir_id = isset($post_data['dir_id']) ? $post_data['dir_id'] : $_dir_id;

			//upload the image and save it to its directiory
			$file_dir = $this->upload_file(
				$post_data['filedata'], 
				$post_data['filename'], 
				$dir_id, 
				$field
			);
				
			//error messages before the upload
			if(isset($file_dir['code']) && isset($file_dir['message']))
			{
				return array(
					'code' 		=> $file_dir['code'], 
					'code_http' => 202, 
					'message' 	=> $file_dir['message'],
				);
			}
				
			//error message from the upload, default number?
			if(isset($file_dir['error']))
			{
				return array(
					'message' => str_replace(array('<p>', '</p>'), '',$file_dir['error'])
				);
			}

			//save the field
			$return_data = array();
			$return_data['file_dir']  = '{filedir_'.$file_dir['upload_location_id'].'}'.$file_dir['file_name'];

			return $return_data; 	
		}
	}

	// ----------------------------------------------------------------
	
	/**
	 * upload file method
	 *
	 * @return 	void
	 */
	public function upload_file($file_data = '', $filename = '', $dir_id = '', $field_settings)
	{
		//load the classes
		ee()->load->library('filemanager');
		ee()->load->helper('file');
	
		//is there a file
		if(empty($file_data) || $file_data == '')
		{
			//return error, no filadata
			return array(
				'message' => 'No filedata is given',
			);
		}
		
		//is there a name?
		if(empty($filename) || $filename == '' )
		{
			//return error, no filename
			return array(
				'message' => 'No filename is given',
			);
		}	

		//decode file data
		$file_data = base64_decode($file_data);
		
		//grab the field settings
		$field_settings['field_settings'] = unserialize(base64_decode($field_settings['field_settings']));
		
		//if the upload dir is set to all
		if($field_settings['field_settings']['allowed_directories'] == 'all')
		{
			//is there a dir_id, otherwuse generate a error because we don`t know witch to use.
			if($dir_id == '') {
				//return error, no filename
				return array(
					'message' => 'No upload dir_id is given',
				);
			}
			
			//set the dir_id
			$field_settings['field_settings']['allowed_directories'] = $dir_id;
		}
		
		//tmp save the file to a the tmp dir
		if(!@file_put_contents(ee()->webservice_settings->item('tmp_dir').$filename, $file_data))
		{
			//return error, can`t save the file to the tmp map
			return array(
				'message' => 'cannot save the file to a tmp file on: ' . ee()->webservice_settings->item('tmp_dir').$filename,
			);
		}
		
		//get the dir info
		$dir_info = ee()->db->get_where('upload_prefs', array('id' => $field_settings['field_settings']['allowed_directories']))->row_array();
		
		//get the file info
		$file_info = get_file_info(ee()->webservice_settings->item('tmp_dir').$filename);
		
		//build a file array for the file
		$file['field_id_'.$field_settings['field_id']] = array(
			'name'     => $filename,
			'type'     => get_mime_by_extension(ee()->webservice_settings->item('tmp_dir').$filename),
			'tmp_name' => ee()->webservice_settings->item('tmp_dir').$filename,
			'size'     => $file_info['size'],
		);
			
		//upload the file.
		$file_upload_info = $this->_upload_file(
			$dir_info,
			$file,
			'field_id_'.$field_settings['field_id'],
			$field_settings
		);
		
		//return the info
		return $file_upload_info;
		
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * upload file method
	 *
	 * @return 	void
	 */
	private function _upload_file($dir, $files, $field_name, $field_settings)
	{		
		// Restricted upload directory?
		switch($dir['allowed_types'])
		{
			case 'all': 
				$allowed_types = '*';
				break;
				
			case 'img': 
				$allowed_types = 'gif|jpg|jpeg|png|jpe';
				break;
				
			default: 
				$allowed_types = '';
		}
		
		// Is this a custom field?
		if (strpos($field_name, 'field_id_') === 0)
		{
			$field_id = str_replace('field_id_', '', $field_name);
		
			ee()->db->select('field_type, field_settings');
			$type_query = ee()->db->get_where('channel_fields', array('field_id' => $field_id));
		
			if ($type_query->num_rows())
			{
				$settings = unserialize(base64_decode($type_query->row('field_settings')));
				
				// Permissions can only get more strict!
				if (isset($settings['field_content_type']) && $settings['field_content_type'] == 'image')
				{
					$allowed_types = 'gif|jpg|jpeg|png|jpe';
				}
			}
			
			$type_query->free_result();
		}
		
		// --------------------------------------------------------------------
		// Upload the file
		
		$file = $files[$field_name];
		$original_filename = $file['name'];
		$clean_filename = basename(ee()->filemanager->clean_filename(
			$file['name'],
			$dir['id'], 
			array('ignore_dupes' => TRUE)
		));

		//build the config
		$config = array(
			'file_name'		=> $clean_filename,
			'upload_path'	=> $dir['server_path'],
			'allowed_types'	=> $allowed_types,
			'max_size'		=> round($dir['max_size']/1024, 2)
		);

		//load xss helper
		ee()->load->helper('xss');
		
		// Check to see if the file needs to be XSS Cleaned
		if (xss_check())
		{
			$config['xss_clean'] = TRUE;
		}
		else
		{
			$config['xss_clean'] = FALSE;
			ee()->filemanager->xss_clean_off();
		}
		
		// Upload the file
		ee()->load->library('Upload');
		ee()->load->library('Webservice_upload_manually');
		ee()->webservice_upload_manually->initialize($config);
		
		if ( ! ee()->webservice_upload_manually->do_upload_manually($file))
		{
			//remove the tmp file
			@unlink($file['tmp_name']);
			
			return ee()->filemanager->_upload_error(
				ee()->webservice_upload_manually->display_errors()
			);
		}
		
		//get the file info
		$fileinfo = ee()->webservice_upload_manually->data();
		
		// (try to) Set proper permissions
		@chmod($fileinfo['full_path'], FILE_WRITE_MODE);
		
		//remove the tmp file
		@unlink($file['tmp_name']);
		
		// --------------------------------------------------------------------
		// Add file the database

		$thumb_info = ee()->filemanager->get_thumb($fileinfo['file_name'], $dir['id']);

		// Build list of information to save and return
		$file_data = array(
			'upload_location_id'	=> $dir['id'],
			'site_id'				=> ee()->config->item('site_id'),
			
			'file_name'				=> $fileinfo['file_name'],
			'orig_name'				=> $original_filename, // name before any upload library processing
			'file_data_orig_name'	=> $fileinfo['orig_name'], // name after upload lib but before duplicate checks
			
			'is_image'				=> $fileinfo['is_image'],
			'mime_type'				=> $fileinfo['file_type'],
			
			'rel_path'				=> $fileinfo['full_path'],
			'file_thumb'			=> $thumb_info['thumb'],
			'thumb_class' 			=> $thumb_info['thumb_class'],
		
			'modified_by_member_id' => ee()->session->userdata('member_id'),
			'uploaded_by_member_id'	=> ee()->session->userdata('member_id'),
			
			'file_size'				=> $fileinfo['file_size'] * 1024, // Bring it back to Bytes from KB
			'file_height'			=> $fileinfo['image_height'],
			'file_width'			=> $fileinfo['image_width'],
			'file_hw_original'		=> $fileinfo['image_height'].' '.$fileinfo['image_width'],
			'max_width'				=> $dir['max_width'],
			'max_height'			=> $dir['max_height']
		);
		
		
		// Check to see if its an editable image, if it is, check max h/w
		if (ee()->filemanager->is_editable_image($fileinfo['full_path'], $fileinfo['file_type']))
		{
		 	$file_data = ee()->filemanager->max_hw_check($fileinfo['full_path'], $file_data);
		
			if ( ! $file_data)
			{
				return ee()->filemanager->_upload_error(
					lang('exceeds_max_dimensions'),
					array(
						'file_name'		=> $fileinfo['file_name'],
						'directory_id'	=> $dir['id']
					)
				);
			}
		}
		
		// Save fileinfo to database
		$saved = ee()->filemanager->save_file($fileinfo['full_path'], $dir['id'], $file_data);

		// Return errors from the filemanager
		if ( ! $saved['status'])
		{
			return ee()->filemanager->_upload_error(
				$saved['message'],
				array(
					'file_name'		=> $fileinfo['file_name'],
					'directory_id'	=> $dir['id']
				)
			);
		}
		
		// Merge in information from database
		$file_data = array_merge($file_data, $this->_file_info($saved['file_id']));
		
		// Stash upload directory prefs in case
		$file_data['upload_directory_prefs'] = $dir;
		$file_data['directory'] = $dir['id'];
		
		// Change file size to human readable
		ee()->load->helper('number');
		$file_data['file_size'] = byte_format($file_data['file_size']);
		
		return $file_data;
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * File Info Callback
	 * 
	 * Returns the file information for use when placing a file
	 * 
	 * @param integer $file_id The File's ID
	 */
	private function _file_info($file_id)
	{
		ee()->load->model('file_model');
		
		$file_info = ee()->file_model->get_files_by_id($file_id);
		$file_info = $file_info->row_array();
		
		$file_info['is_image'] = (strncmp('image', $file_info['mime_type'], '5') == 0) ? TRUE : FALSE;
		
		$thumb_info = ee()->filemanager->get_thumb($file_info['file_name'], $file_info['upload_location_id']);
		$file_info['thumb'] = $thumb_info['thumb'];
		
		return $file_info;
	}

	// --------------------------------------------------------------------
}