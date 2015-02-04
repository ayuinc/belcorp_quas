<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Base fieldtype file
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

class Webservice_fieldtype
{

	/*
		@todo Use the ExpressionEngine API instead of own calls.
	*/

	private $_bundled_celltypes = array(
		'default',
		'file',
		'text',
		'relationship',
		'grid',
		'matrix',
		'playa',
		'zoo_visitor',
	);

	public function __construct(){}

	// ----------------------------------------------------------------------
	
	/**
	 * Preps the data for saving
	 * 
	 * @param  mixed $data  
	 * @param  string $field_type  
	 * @param  string  $field_name
	 * @param  array $field_data
	 * @param  array $channel_settings
	 * @param  bool $is_new
	 * @param  int $entry_id
	 * @return mixed string
	 */
	public function save($data = null, $field_type = null, $field_name = null, $field_data = array(), $channel_settings = array(), $is_new = false, $entry_id = 0) 
	{
		//get the fieldtype
		$obj = $this->_get_fieldtype($field_type, $field_data, $entry_id);

		//set the field_name
		$obj->field_name = $field_name;
		//set the field_id
		$obj->field_id = $field_data['field_id'];
		//set the field data
		$obj->field_data = $field_data;
		//set the field settings
		$obj->field_settings = unserialize(base64_decode($field_data['field_settings']));
		//set the channel_settings
		$obj->channel_settings = $channel_settings;
		//set the type
		$obj->field_type = $field_type;

		//is the class exists
		if(method_exists($obj, 'webservice_save'))
		{
			return $obj->webservice_save($data, $is_new, $entry_id);
		}

		return $data;
	}

	// ----------------------------------------------------------------------
	
	/**
	 * validates the field input
	 * 
	 * @param  mixed $data  
	 * @param  string $field_type  
	 * @param  string  $field_name
	 * @param  array $field_data
	 * @param  array $channel_settings
	 * @param  bool $is_new
	 * @return bool
	 */
	public function validate($data = null, $field_type = null, $field_name = null, $field_data = array(), $channel_settings = array(), $is_new = false, $entry_id = 0) 
	{
		//get the fieldtype
		$obj = $this->_get_fieldtype($field_type, $field_data, $entry_id);

		//set the field_name
		$obj->field_name = $field_name;
		//set the field_id
		$obj->field_id = $field_data['field_id'];
		//set the field data
		$obj->field_data = $field_data;
		//set the field settings
		$obj->field_settings = unserialize(base64_decode($field_data['field_settings']));
		//set the channel_settings
		$obj->channel_settings = $channel_settings;
		//set the type
		$obj->field_type = $field_type;

		//is the class exists
		if(method_exists($obj, 'webservice_validate'))
		{
			//reset errror msg
			$this->validate_error = '';

			//validate field
			$validated = $obj->webservice_validate($data, $is_new, $entry_id);

			//set the error msg
			if(isset($obj->validate_error))
			{
				$this->validate_error = $obj->validate_error;
			}

			return $validated;
		}

		return true;
	}

	// ----------------------------------------------------------------------
	
	/**
	 * Handles any custom logic after an entry is saved.
	 * 
	 * @param  mixed $data  
	 * @param  string $field_type  
	 * @param  string  $field_name
	 * @param  array $field_data
	 * @param  array $channel_settings
	 * @param  array $inserted_data
	 * @param  int $entry_id
	 * @return void
	 */
	public function post_save($data = null, $field_type = null, $field_name = null, $field_data = array(), $channel_settings = array(), $inserted_data = array(), $entry_id = 0) 
	{
		//get the fieldtype
		$obj = $this->_get_fieldtype($field_type, $field_data, $entry_id);

		//set the field_name
		$obj->field_name = $field_name;
		//set the field_id
		$obj->field_id = $field_data['field_id'];
		//set the field data
		$obj->field_data = $field_data;
		//set the field settings
		$obj->field_settings = unserialize(base64_decode($field_data['field_settings']));
		//set the channel_settings
		$obj->channel_settings = $channel_settings;
		//set the type
		$obj->field_type = $field_type;

		//is the class exists
		if(method_exists($obj, 'webservice_post_save'))
		{
			$obj->webservice_post_save($data, $inserted_data, $entry_id);
		}	
	}

	// ----------------------------------------------------------------------
	
	/**
	 * Preprocess the data to be returned
	 * 
	 * @param  mixed $data  
	 * @param  string $field_type  
	 * @param  string  $field_name
	 * @param  array $field_data
	 * @param  array $channel_settings
	 * @param  array $method (for free access)
	 * @param  int $entry_id
	 * @return mixed string
	 */
	public function pre_process($data = null, $field_type = null, $field_name = null, $field_data = array(), $channel_settings = array(), $method = '', $entry_id = 0) 
	{
		//get the fieldtype
		$obj = $this->_get_fieldtype($field_type, $field_data, $entry_id);

		//set the field_name
		$obj->field_name = $field_name;
		//set the field_id
		$obj->field_id = $field_data['field_id'];
		//set the field data
		$obj->field_data = $field_data;
		//set the field settings
		$obj->field_settings = unserialize(base64_decode($field_data['field_settings']));
		//set the channel_settings
		$obj->channel_settings = $channel_settings;
		//set the type
		$obj->field_type = $field_type;
		
		//is the class exists
		if(method_exists($obj, 'webservice_pre_process'))
		{
			return $obj->webservice_pre_process($data, in_array($method, ee()->webservice_settings->item('free_apis')), $entry_id);
		}

		return $data;
	}

	// ----------------------------------------------------------------------
	
	/**
	 * delete field data, before the entry is deleted
	 * 
	 * @param  mixed $data  
	 * @param  string $field_type  
	 * @param  string  $field_name
	 * @param  array $field_data
	 * @param  array $channel_settings
	 * @param  int $entry_id
	 * @return void
	 */
	public function delete($data = null, $field_type = null, $field_name = null, $field_data = array(), $channel_settings = array(), $entry_id = 0) 
	{
		//get the fieldtype
		$obj = $this->_get_fieldtype($field_type, $field_data, $entry_id);

		//set the field_name
		$obj->field_name = $field_name;
		//set the field_id
		$obj->field_id = $field_data['field_id'];
		//set the field data
		$obj->field_data = $field_data;
		//set the field settings
		$obj->field_settings = unserialize(base64_decode($field_data['field_settings']));
		//set the channel_settings
		$obj->channel_settings = $channel_settings;
		//set the type
		$obj->field_type = $field_type;

		//is the class exists
		if(method_exists($obj, 'webservice_delete'))
		{
			$obj->webservice_delete($data, $entry_id);
		}
	}
	
	// ----------------------------------------------------------------------
	
	/**
	 * delete field data, after the entry is deleted
	 * 
	 * @param  mixed $data  
	 * @param  string $field_type  
	 * @param  string  $field_name
	 * @param  array $field_data
	 * @param  array $channel_settings
	 * @param  int $entry_id
	 * @return void
	 */
	public function post_delete($data = null, $field_type = null, $field_name = null, $field_data = array(), $channel_settings = array(), $entry_id = 0) 
	{
		//get the fieldtype
		$obj = $this->_get_fieldtype($field_type, $field_data, $entry_id);

		//set the field_name
		$obj->field_name = $field_name;
		//set the field_id
		$obj->field_id = $field_data['field_id'];
		//set the field data
		$obj->field_data = $field_data;
		//set the field settings
		$obj->field_settings = unserialize(base64_decode($field_data['field_settings']));
		//set the channel_settings
		$obj->channel_settings = $channel_settings;
		//set the type
		$obj->field_type = $field_type;

		//is the class exists
		if(method_exists($obj, 'webservice_post_delete'))
		{
			$obj->webservice_post_delete($data, $entry_id);
		}
	}


	// ---------------------------------------------------------------------- 
	
	/**
	 * get the class of the fieldata
	 * 
	 * @param none
	 * @return void
	 */
	private function _get_fieldtype($name = '', $field_data = array(), $entry_id = 0) 
	{
		static $classCache = array();

		if (!empty($classCache[$name]))
		{
			$class = $classCache[$name];
		}
		else
		{
			// is this a bundled celltype?
			if (in_array($name, $this->_bundled_celltypes))
			{
				$class = 'Webservice_'.$name."_ft";

				if (! class_exists($class))
				{
					// load it from entryapi/fieldtypes/
					require_once PATH_THIRD.'webservice/libraries/api/entry/fieldtypes/webservice_'.$name."_ft.php";
				}
			}
			else
			{
				//load the fieldtype
				$this->_load_fieldtype($field_data, $entry_id);
				//dirty hack to get the class
				$class = ee()->api_channel_fields->field_types[$name];

				//$class = ucfirst($name);
				// ee()->load->library('api');
				// ee()->api->instantiate('channel_fields');
				// $class = ee()->api_channel_fields->include_handler($name);
				// ee()->api_channel_fields->setup_handler($field_type);
			}
		}

		// set the class correct
		//$class = (string) $class;

		//return the fieldtype class
		if(is_object($class))
		{
			return $class;
		}

		//return the default Webservice fieldtype class
		else if (class_exists((string)$class))
		{
			$ft = new $class();
			$classCache[$name] = $ft;
			return $ft;
		}

		//return the base default class
		else
		{
			ee()->load->library('fieldtypes/webservice_default_ft');

			return new Webservice_default_ft();
		}
	}

	// --------------------------------------------------------------------

	/**
	* Function _load_fieldtype
	* Load the fieldtype based on id
	* @return	array 	Fieldtype data
	*/
	private function _load_fieldtype($field_data, $entry_id)
	{

		//	----------------------------------------
		//	Set the default vars
		//	----------------------------------------
		$ft_custom_settings	= unserialize(base64_decode($field_data['field_settings']));

		unset($field_data['field_settings']);

		$field_data 		= array_merge($field_data, $ft_custom_settings);
		$field_type			= $field_data['field_type'];
		$field_name			= 'field_id_' . $field_data['field_id'];
		$field_id 			= $field_data['field_id'];


		//	----------------------------------------
		//	Load fieldtype
		//	----------------------------------------
		$class = ee()->api_channel_fields->include_handler($field_type);
		ee()->api_channel_fields->setup_handler($field_type);

		//	----------------------------------------
		// 	Tell which fieldtype we're dealing with
		//	----------------------------------------
		ee()->api_channel_fields->field_type = $field_type;

		//	----------------------------------------
		// 	Attach the settings associated with the fieldtype
		//	----------------------------------------
		ee()->api_channel_fields->field_types[$field_type]->settings = $field_data;

		//	----------------------------------------
		//	Add entry_id and other useful data to settings array. 
		//	Used for post_save.
		//	----------------------------------------
		ee()->api_channel_fields->field_types[$field_type]->settings['entry_id'] = $entry_id;
		ee()->api_channel_fields->field_types[$field_type]->settings['field_name'] = 'field_id_'.$field_id;

		// Can't see where this is getting set properly in the API,
		// so setting the "field"_id here since I have it handy
		ee()->api_channel_fields->apply('_init', array(
			array(
				'id' => $field_id,
				'field_id'		=> $field_id,
				'field_name'	=> 'field_id_'.$field_id,
				'content_id'	=> 0,
				'content_type'	=> 'channel'
			)
		));

		return $class;
	}

	// --------------------------------------------------------------------

	/**
	* Function _get_field_ids
	* Retrieve field id, fieldtypes, name, and more
	* @return	array 	Fieldtype data
	*/
	private function _get_field_ids()
	{
		// Return data if already cached
		if(ee()->session->cache('bulk_edit', 'field_ids'))
		{
			return ee()->session->cache('bulk_edit', 'field_ids');
		}

		$output = array();
		
		$results = ee()->db->query("/* Bulk Edit _get_field_ids */ \n SELECT exp_channels.channel_id, 
			 exp_channel_fields.field_id,
			 exp_channel_fields.group_id, 
			 exp_channel_fields.field_name,
			 exp_channel_fields.field_label,
			 exp_channel_fields.field_type,
			 exp_channel_fields.field_settings,
			 exp_channel_fields.field_text_direction
			 FROM exp_channels, exp_channel_fields 
			 WHERE exp_channel_fields.group_id = exp_channels.field_group
			 AND exp_channel_fields.site_id = ".ee()->config->item('site_id') . "
			 ORDER BY exp_channel_fields.field_order ASC"
			 );
	 
	 	if($results->num_rows() > 0)
		{
			foreach($results->result_array() as $row)
			{
				$ch_id = $row['channel_id'];
				
				// Basic field data
				$output['field'][$row['field_id']]					= $row['field_label'];
				$output['field_name'][$row['field_id']]				= $row['field_name'];
				$output['fieldtype'][$row['field_id']]				= $row['field_type'];
				$output['id'][$row['field_id']]						= $row['field_id'];
				$output['field_group_id'][$row['field_id']]			= $row['group_id'];
				$output['field_text_direction'][$row['field_id']]	= $row['field_text_direction'];
				$output['settings'][$row['field_id']]				= unserialize(base64_decode($row['field_settings']));
				
				// Basic field data, per channel
				$output[$ch_id]['field'][$row['field_id']]					= $row['field_label'];
				$output[$ch_id]['field_name'][$row['field_id']]				= $row['field_name'];
				$output[$ch_id]['fieldtype'][$row['field_id']]				= $row['field_type'];
				$output[$ch_id]['id'][$row['field_id']]						= $row['field_id'];
				$output[$ch_id]['field_group_id'][$row['field_id']]			= $row['group_id'];
				$output[$ch_id]['field_text_direction'][$row['field_id']]	= $row['field_text_direction'];
				$output[$ch_id]['settings'][$row['field_id']]				= unserialize(base64_decode($row['field_settings']));

			}
		}	
			
		$results->free_result();

		ee()->session->set_cache('bulk_edit', 'field_ids', $output);
		
		return $output;
			
	} // END _get_field_ids

	// --------------------------------------------------------------------

	/**
	 * Retrieve field information from field_id
	 * @param  int $field_id The field_id to check
	 * @return array           Array of field settings
	 */
	private function _get_field_settings($field_id = 0)
	{
		if(ee()->session->cache('bulk_edit', 'field_settings_'.$field_id))
		{
			return ee()->session->cache('bulk_edit', 'field_settings_'.$field_id);
		}

		if( empty($field_id) )
		{
			$sql = ee()->db->query("SELECT * FROM exp_channel_fields");
		} else {
			$sql = ee()->db->query("SELECT * FROM exp_channel_fields WHERE field_id = " . ee()->db->escape_str($field_id));
		}

		if($sql->num_rows() > 0)
		{
			if( empty($field_id) )
			{
				foreach($sql->result_array() as $row)
				{
					$results[$row['field_id']] = $row;		
				}

				ee()->session->set_cache('bulk_edit', 'field_settings_'.$field_id, $results);

				return $results;
			
			} else {
			
				foreach($sql->result_array() as $row)
				{
					ee()->session->set_cache('bulk_edit', 'field_settings_'.$field_id, $row);

					return $row;
				}	
			}
			
		}
	} // END _get_field_settings

	// ---------------------------------------------------------------------- 
	
	
} // END CLASS

/* End of file default_model.php  */
/* Location: ./system/expressionengine/third_party/default/models/default_model.php */