<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Webservice
 *
 * @package		Webservice
 * @category	Modules
 * @author		Rein de Vries <info@reinos.nl>
 * @link		http://reinos.nl/add-ons/webservice
 * @license  	http://reinos.nl/add-ons/commercial-license
 * @copyright 	Copyright (c) 2014 Reinos.nl Internet Media
 */

/**
 * Include the config file
 */
require_once PATH_THIRD.'webservice/config.php';

class Webservice_entry
{
	public $limit;
	public $offset;
	public $total_results;
	public $absolute_results;

	//-------------------------------------------------------------------------

	/**
     * Constructor
    */
	public function __construct()
	{
		// load the stats class because this is not loaded because of the use of the extension
		ee()->load->library('stats'); 
				
		/** ---------------------------------------
		/** load the api`s
		/** ---------------------------------------*/
		ee()->load->library('api');
		ee()->api->instantiate('channel_entries');
		ee()->api->instantiate('channel_fields');	
		ee()->load->library('api/entry/fieldtypes/webservice_fieldtype');

		//require the default settings
        require PATH_THIRD.'webservice/settings.php';
	}

	//-------------------------------------------------------------------------

	/**
	 * Create a entry
	 * 
	 * @param  string $auth 
	 * @param  array  $post_data 
	 * @return array            
	 */
	public function create_entry($post_data = array())
	{
		/* -------------------------------------------
		/* 'webservice_create_entry_start' hook.
		/*  - Added: 3.2.1
		*/
		$post_data = Webservice_helper::add_hook('create_entry_start', $post_data);
		/** ---------------------------------------*/

		/** ---------------------------------------
		/**  Validate data
		/** ---------------------------------------*/
		$data_errors = array();
		
		/** ---------------------------------------
		/**  Title is for a insert always required
		/** ---------------------------------------*/
		if(!isset($post_data['title']) || $post_data['title'] == '')
		{
			$data_errors[] = 'title';
		}
		if(!isset($post_data['channel_name']) || $post_data['channel_name'] == '')
		{
			$data_errors['channel_name'] = 'channel_name';
		}
//		if(!isset($post_data['channel_id']) || $post_data['channel_id'] == '')
//		{
//			$data_errors['channel_id'] = 'channel_id';
//		}

		/** ---------------------------------------
		/**  in strict site id mode, expect a site_id
		/** ---------------------------------------*/
		if(ee()->webservice_settings->item('site_id_strict') && !isset($post_data['site_id']))
		{
			$data_errors[] = 'site_id';
		}

		/** ---------------------------------------
		/**  Set the site_id is empty
		/** ---------------------------------------*/
		if(!isset($post_data['site_id']) || $post_data['site_id'] == '')
		{
			$post_data['site_id'] = 1;
		}

		/** ---------------------------------------
		/**  Return error when there are fields who are empty en shoulnd`t
		/** ---------------------------------------*/
		if(!empty($data_errors) || count($data_errors) > 0)
		{
			//generate error
			return array(
				'message' => 'The following fields are not filled in: '.implode(', ',$data_errors)
			);
		}

		/** ---------------------------------------
		/**  Parse Out Channel Information and check if the use is auth for the channel
		/** ---------------------------------------*/
		$channel_check = $this->_parse_channel($post_data['channel_name'], false);
		if( ! $channel_check['success'] )
		{
			return $channel_check;
		}

		/** ---------------------------------------
		/**  Check if the site_id are a match
		/** ---------------------------------------*/
		if($post_data['site_id'] != $this->channel['site_id'])
		{
			//generate error
			return array(
				'message' => 'The site_id for this channel is wrong'
			);
		}

		/** ---------------------------------------
		/**  Check the other fields witch are required
		/** ---------------------------------------*/
		if(!empty($this->fields))
		{
			foreach($this->fields as $key=>$val)
			{
				if($val['field_required'] == 'y')
				{
					if(!isset($post_data[$val['field_name']]) || $post_data[$val['field_name']] == '')
					{
						$data_errors[] = $val['field_name'];
					}
				}
			}
		}		
		
		/** ---------------------------------------
		/**  Return error when there are fields who are empty en shoulnd`t
		/** ---------------------------------------*/
		if(!empty($data_errors) || count($data_errors) > 0)
		{
			//generate error
			return array(
				'message' => 'The following fields have errors: '.implode(', ',$data_errors)
			);
		}
				
		/** ---------------------------------------
		/**  validate fields by the fieldtype parser
		/** ---------------------------------------*/
		$validate_errors = array();
		if(!empty($this->fields))
		{
			foreach($this->fields as $key=>$val)
			{
				if(isset($post_data[$val['field_name']])) 
				{
					//validate the data
					$validate_field = ee()->webservice_fieldtype->validate($post_data[$val['field_name']], $val['field_type'], $val['field_name'], $val, $this->channel, true, 0);
					
					/*if($validate_field == false)
					{
						$validate_errors[] = $val['field_name'].' : '.ee()->webservice_fieldtype->validate_error;
					}*/

					//do we got some errors?
					if($validate_field !== true && $validate_field != null && $validate_field != '' && !is_array($validate_field) )
					{
						$error = ee()->webservice_fieldtype->validate_error != '' ? ee()->webservice_fieldtype->validate_error : $validate_field;
						$validate_errors[] = $val['field_name'].' : '.$error;
					}

					//if the validate function return an array?
					if(isset($validate_field['error']))
					{
						$validate_errors[] = $val['field_name'].' : '.$validate_field['error'];
					}

					//if the validate only holds the new value
					if(isset($validate_field['value']))
					{
						$post_data[$val['field_name']] = $validate_field['value'];
					}
				}
			}
		}

		/** ---------------------------------------
		/**  Return the errors from the validate functions
		/** ---------------------------------------*/
		if(!empty($validate_errors) || count($validate_errors) > 0)
		{
			//generate error
			return array(
				'message' => 'The following fields have errors: '.implode(', ',$validate_errors)
			);
		}

		/** ---------------------------------------
		/**  default Entry data
		/** ---------------------------------------*/
		$entry_date = isset($post_data['entry_date']) ? $post_data['entry_date'] : time();
		$entry_data = array(
			'site_id'				=> $post_data['site_id'],
			'entry_id'				=> 0,
			'channel_id'			=> $this->channel['channel_id'],
			'author_id'				=> ee()->session->userdata('member_id'),
			'title'					=> $post_data['title'],
			'ip_address'			=> ee()->input->ip_address(),
			'entry_date'			=> $entry_date,
			'edit_date'				=> gmdate("YmdHis", $entry_date),
			'year'					=> gmdate('Y', $entry_date),
			'month'					=> gmdate('m', $entry_date),
			'day'					=> gmdate('d', $entry_date),
			'status'				=> isset($post_data['status']) ? $post_data['status'] : $this->channel['deft_status'],
			'allow_comments'		=> $this->channel['deft_comments'],
			'ping_servers'			=> array(),
			'versioning_enabled'	=> $this->channel['enable_versioning'],
			'sticky'				=> isset($post_data['sticky']) ? $post_data['sticky'] : 'n',
			'allow_comments'		=> isset($post_data['allow_comments']) ? $post_data['allow_comments'] : $this->channel['deft_comments'],
		);

		//** ---------------------------------------
		/**  Publisher support
		/** ---------------------------------------*/
		if(isset($post_data['publisher_lang_id']))
		{
			$entry_data['publisher_lang_id'] = $post_data['publisher_lang_id'];
		}
		if(isset($post_data['publisher_status']))
		{
			$entry_data['publisher_status'] = $post_data['publisher_status'];
		}

		//** ---------------------------------------
		/**  Fill out the other fields
		/** ---------------------------------------*/
		if(!empty($this->fields))
		{
			foreach($this->fields as $key=>$val)
			{
				if(isset($post_data[$val['field_name']])) 
				{
					//set the data
					$entry_data['field_ft_'.$val['field_id']]  = $val['field_fmt'];	
					$entry_data['field_id_'.$val['field_id']]  = ee()->webservice_fieldtype->save($post_data[$val['field_name']], $val['field_type'], $val['field_name'], $val, $this->channel, true);
				}	
			}
		}	
		
		/** ---------------------------------------
		/**  set the channel setting 
		/** ---------------------------------------*/
		ee()->api_channel_fields->setup_entry_settings($this->channel['channel_id'], $entry_data);
		
		/** ---------------------------------
		/**  add the entry data
		/** ---------------------------------*/		
		if ( ! ee()->api_channel_entries->save_entry($entry_data, $this->channel['channel_id']))
		{
			//return een fout bericht met de errors
			$errors = ee()->api_channel_entries->get_errors();

			//generate error
			return array(
				'message' => array_shift($errors)
			);
		}

		/** ---------------------------------------
		/** Okay, now lets add a new category 
		/** ---------------------------------------*/
		if(isset($post_data['category']))
		{
			//$cat_ids = explode('|', $post_data['category']);
			ee()->webservice_category_model->update_category((array) $post_data['category'], ee()->api_channel_entries->entry_id);
		}

		/** ---------------------------------------
		/**  Post save callback
		/** ---------------------------------------*/
		if(!empty($this->fields))
		{
			foreach($this->fields as $key=>$val)
			{
				if(isset($post_data[$val['field_name']])) 
				{
					//validate the data
					ee()->webservice_fieldtype->post_save($entry_data['field_id_'.$val['field_id']], $val['field_type'], $val['field_name'], $val, $this->channel, $entry_data, ee()->api_channel_entries->entry_id);
				}
			}
		}

		/* -------------------------------------------
		/* 'webservice_create_entry_end' hook.
		/*  - Added: 2.2
		*/
		Webservice_helper::add_hook('create_entry_end', ee()->api_channel_entries->entry_id);
		/** ---------------------------------------*/
	
		/** ---------------------------------------
		/** return response
		/** ---------------------------------------*/
		$this->service_error['succes_create']['id'] = ee()->api_channel_entries->entry_id; //@deprecated
		$this->service_error['succes_create']['metadata'] = array(
			'id' => ee()->api_channel_entries->entry_id
		);
		$this->service_error['succes_create']['success'] = true;
		return $this->service_error['succes_create'];
	}

	// ----------------------------------------------------------------

	/**
	 * Read a entry
	 * @param  string $auth 
	 * @param  array  $post_data 
	 * @return array            
	 */
	public function read_entry($post_data = array())
	{
		//just call the search method
		return $this->search_entry($post_data, 'read_entry');
	}
	
	// ----------------------------------------------------------------

	/**
	 * build a entry data array for a new entry
	 *
	 * @return 	void
	 */
	public function update_entry($post_data = array())
	{
		/* -------------------------------------------
		/* 'webservice_update_entry_start' hook.
		/*  - Added: 3.2.1
		*/
		$post_data = Webservice_helper::add_hook('update_entry_start', $post_data);

		/** ---------------------------------------
		/**  Validate data
		/** ---------------------------------------*/
		$data_errors = array();
		
		/** ---------------------------------------
		/**  entry_id is always required for a select
		/** ---------------------------------------*/
		if(!isset($post_data['entry_id']) || $post_data['entry_id'] == '') {
			$data_errors[] = 'entry_id';
		}

		/** ---------------------------------------
		/**  Return error when there are fields who are empty en shoulnd`t
		/** ---------------------------------------*/
		if(!empty($data_errors) || count($data_errors) > 0)
		{
			//generate error
			return array(
				'message' => 'The following fields are not filled in: '.implode(', ',$data_errors)
			);
		}

		/** ---------------------------------------
		/**  get the entry data and check if the entry exists
		/** ---------------------------------------*/
		$entry_data = $this->get_entry($post_data['entry_id']);
				
		//check the data
		if ( empty($entry_data))
		{
			//generate error
			return array(
				'message' => 'No Entry found'
			);
		}

		//** ---------------------------------------
		/**  Publisher support
		/** ---------------------------------------*/
		if(isset($post_data['publisher_lang_id']))
		{
			$entry_data['publisher_lang_id'] = $post_data['publisher_lang_id'];
		}
		if(isset($post_data['publisher_status']))
		{
			$entry_data['publisher_status'] = $post_data['publisher_status'];
		}

		/** ---------------------------------------
		/**  Parse Out Channel Information and check if the use is auth for the channel
		/** ---------------------------------------*/
		$channel_check = $this->_parse_channel($post_data['entry_id']);
		if( ! $channel_check['success'])
		{
			return $channel_check;
		}
		
		/** ---------------------------------------
		/**  Check the other fields witch are required
		/** ---------------------------------------*/
		if(!empty($this->fields))
		{
			foreach($this->fields as $key=>$val)
			{
				if($val['field_required'] == 'y')
				{
					if(!isset($post_data[$val['field_name']]) || $post_data[$val['field_name']] == '') {
						$data_errors[] = $val['field_name'];
					}
				}
			}
		}		
		
		/** ---------------------------------------
		/**  Return error when there are fields who are empty en shoulnd`t
		/** ---------------------------------------*/
		if(!empty($data_errors) || count($data_errors) > 0)
		{
			//generate error
			return array(
				'message' => 'The following fields are not filled in: '.implode(', ',$data_errors)
			);
		}
		
		/** ---------------------------------------
		/**  check if the given channel_id match the channel_id of the entry
		/** ---------------------------------------*/
		if($entry_data['channel_id'] != $this->channel['channel_id'])
		{
			//generate error
			return array(
				'message' => 'Specified entry does not appear in the specified channel'
			);
		}

		/** ---------------------------------------
		/**  validate fields by the fieldtype parser
		/** ---------------------------------------*/
		$validate_errors = array();
		if(!empty($this->fields))
		{
			foreach($this->fields as $key=>$val)
			{
				if(isset($post_data[$val['field_name']])) 
				{
					//validate the data
					$validate_field = (bool) ee()->webservice_fieldtype->validate($post_data[$val['field_name']], $val['field_type'], $val['field_name'], $val, $this->channel, false, $post_data['entry_id']);
					
					if($validate_field == false)
					{
						$validate_errors[] = $val['field_name'].' : '.ee()->webservice_fieldtype->validate_error;
					}
				}
			}
		}

		/** ---------------------------------------
		/**  Return the errors from the validate functions
		/** ---------------------------------------*/
		if(!empty($validate_errors) || count($validate_errors) > 0)
		{
			//generate error
			return array(
				'message' => 'The following fields have errors: '.implode(', ',$validate_errors)
			);
		}
		
		/** ---------------------------------------
		/**  default data
		/** ---------------------------------------*/
		$entry_data['title'] = isset($post_data['title']) ? $post_data['title'] : $entry_data['title'] ;
		$entry_data['status'] = isset($post_data['status']) ? $post_data['status'] : $entry_data['status'] ;
		$entry_data['edit_date'] = time();
		$entry_data['sticky'] = isset($post_data['sticky']) ? $post_data['sticky'] : $entry_data['sticky'] ;
		$entry_data['allow_comments'] = isset($post_data['allow_comments']) ? $post_data['allow_comments'] : $entry_data['allow_comments'] ;
		$entry_data['entry_date'] = isset($post_data['entry_date']) ? $post_data['entry_date'] : $entry_data['entry_date'] ;
		
		//** ---------------------------------------
		/**  Fill out the other custom fields
		/** ---------------------------------------*/
		if(!empty($this->fields))
		{
			foreach($this->fields as $key=>$val)
			{
				if(isset($post_data[$val['field_name']])) 
				{
					//set the data
					$entry_data['field_ft_'.$val['field_id']]  = $val['field_fmt'];	
					$entry_data['field_id_'.$val['field_id']]  = ee()->webservice_fieldtype->save($post_data[$val['field_name']], $val['field_type'], $val['field_name'], $val, $this->channel, false, $entry_data['entry_id']);
				}	
			}
		}

		/** ---------------------------------------
		/**  Get the old assigned categories or the new one
		/** ---------------------------------------*/
		$assigned_categories = isset($post_data['category']) ? $post_data['category'] : ee()->webservice_category_model->get_entry_categories($entry_data['entry_id'], true);

		/** ---------------------------------------
		/**  set the channel setting 
		/** ---------------------------------------*/
		ee()->api_channel_fields->setup_entry_settings($this->channel['channel_id'], $entry_data);
		
		/** ---------------------------------------
		/**  update entry
		/** ---------------------------------------*/
		$r = ee()->api_channel_entries->save_entry($entry_data, null, $entry_data['entry_id']);
		
		//Any errors?
		if ( ! $r)
		{
			//return een fout bericht met de errors
			$errors = implode(', ', ee()->api_channel_entries->get_errors());

			//generate error
			return array(
				'message' => $errors
			);
		}

		/** ---------------------------------------
		/** Okay, now lets add a new category or update is. Just after saving the data
		/** ---------------------------------------*/
		ee()->webservice_category_model->update_category((array) $assigned_categories, ee()->api_channel_entries->entry_id);

		/** ---------------------------------------
		/**  Post save callback
		/** ---------------------------------------*/
		if(!empty($this->fields))
		{

			foreach($this->fields as $key=>$val)
			{
				if(isset($post_data[$val['field_name']])) 
				{
					//validate the data
					ee()->webservice_fieldtype->post_save($post_data[$val['field_name']], $val['field_type'], $val['field_name'], $val, $this->channel, $entry_data, ee()->api_channel_entries->entry_id);
				}
			}
		}

		/* -------------------------------------------
		/* 'webservice_update_entry_end' hook.
		/*  - Added: 2.2
		*/
		Webservice_helper::add_hook('update_entry_end', $entry_data);
		// -------------------------------------------
	
		/** ---------------------------------------
		/** return response
		/** ---------------------------------------*/
		$this->service_error['succes_update']['id'] = $entry_data['entry_id']; //@deprecated
		$this->service_error['succes_update']['metadata'] = array(
			'id' => $entry_data['entry_id']
		);
		$this->service_error['succes_update']['success'] = true;
		return $this->service_error['succes_update'];
	}
	
	// ----------------------------------------------------------------

	/**
	 * build a entry data array for a new entry
	 *
	 * @return 	void
	 */
	public function delete_entry($post_data = array())
	{
		/* -------------------------------------------
		/* 'webservice_delete_entry_start' hook.
		/*  - Added: 3.2.1
		*/
		$post_data = Webservice_helper::add_hook('delete_entry_start', $post_data);

		/** ---------------------------------------
		/**  Validate data
		/** ---------------------------------------*/
		$data_errors = array();
		
		/** ---------------------------------------
		/**  entry_id is always required for a select
		/** ---------------------------------------*/
		if(!isset($post_data['entry_id']) || $post_data['entry_id'] == '') {
			$data_errors[] = 'entry_id';
		}

		/** ---------------------------------------
		/**  Return error when there are fields who are empty en shoulnd`t
		/** ---------------------------------------*/
		if(!empty($data_errors) || count($data_errors) > 0)
		{
			//generate error
			return array(
				'message' => 'The following fields are not filled in: '.implode(', ',$data_errors)
			);
		}

		/** ---------------------------------------
		/**  get the entry data and check if the entry exists
		/** ---------------------------------------*/
		$entry_data = $this->get_entry($post_data['entry_id']);
		
		//check the data
		if ( empty($entry_data))
		{
			//generate error
			return array(
				'message' => 'No Entry found'
			);
		}

		/** ---------------------------------------
		/**  Parse Out Channel Information and check if the use is auth for the channel
		/** ---------------------------------------*/
		$channel_check = $this->_parse_channel($post_data['entry_id']);
		if( ! $channel_check['success'])
		{
			return $channel_check;
		}

		$post_data['entry_id'] = isset($post_data['entry_id']) ? $post_data['entry_id'] : '';
		//$post_data['url_title'] = isset($post_data['url_title']) ? $post_data['url_title'] : '';
		
		/** ---------------------------------------
		/**  check if the given channel_id match the channel_id of the entry
		/** ---------------------------------------*/
		if($entry_data['channel_id'] != $this->channel['channel_id'])
		{
			//generate error
			return array(
				'message' => 'Specified entry does not appear in the specified channel'
			);
		}
		
		/** ---------------------------------------
		/**  Call the fieldtype delete function per field
		/** ---------------------------------------*/
		if(!empty($this->fields))
		{
			foreach($this->fields as $key=>$val)
			{
				if(isset($entry_data[$val['field_name']])) 
				{
					ee()->webservice_fieldtype->delete($entry_data[$val['field_name']], $val['field_type'], $val['field_name'], $val, $this->channel, $entry_data['entry_id']);
				}
			}
		}
		
		/** ---------------------------------------
		/**  delete entry
		/** ---------------------------------------*/
		$r = ee()->api_channel_entries->delete_entry($entry_data['entry_id']);
		
		//Any errors?
		if ( ! $r)
		{
			$errors = implode(', ', ee()->api_channel_entries->get_errors());
			if(!empty($errors)) 
			{
				//generate error
				return array(
					'message' => $errors
				);

			} else {
				//generate error
				return array(
					'message' => 'Entry could not be removed'
				);
			}
		}

		/** ---------------------------------------
		/**  Call the fieldtype post_delete function per field
		/** ---------------------------------------*/
		if(!empty($this->fields))
		{
			foreach($this->fields as $key=>$val)
			{
				if(isset($entry_data[$val['field_name']])) 
				{
					ee()->webservice_fieldtype->post_delete($entry_data[$val['field_name']], $val['field_type'], $val['field_name'], $val, $this->channel, $entry_data['entry_id']);
				}
			}
		}

		/* -------------------------------------------
		/* 'webservice_delete_entry_end' hook.
		/*  - Added: 2.2
		*/
		Webservice_helper::add_hook('delete_entry_end', $entry_data['entry_id']);
		// -------------------------------------------
	
		/** ---------------------------------------
		/** return response
		/** ---------------------------------------*/
		$this->service_error['succes_delete']['id'] = $entry_data['entry_id']; //@deprecated
		$this->service_error['succes_delete']['metadata'] = array(
			'id' => $entry_data['entry_id']
		);
		$this->service_error['succes_delete']['success'] = true;
		return $this->service_error['succes_delete'];
	}

	// ----------------------------------------------------------------

	/**
	 * Search a entry
	 * @param  array $post_data
	 * @param string $method
	 * @return array
	 */
	public function search_entry($post_data = array(), $method = 'search_entry')
	{
		/* -------------------------------------------
		/* 'webservice_search_entry_start' hook.
		/*  - Added: 3.2.1
		*/
		$post_data = Webservice_helper::add_hook('search_entry_start', $post_data);

		/** ---------------------------------------
		/**  Validate data
		/** ---------------------------------------*/
		$data_errors = array();

		/** ---------------------------------------
		/**  Set the site_id is empty
		/** ---------------------------------------*/
		if(!isset($post_data['site_id']) || $post_data['site_id'] == '') {
			$post_data['site_id'] = 1;
		}

		/** ---------------------------------------
		/**  Set the show_fields param
		/** ---------------------------------------*/
		if(!isset($post_data['show_fields']) || $post_data['show_fields'] == '') {
			$post_data['show_fields'] = array();
		}
		else
		{
			$post_data['show_fields'] = explode("|", $post_data['show_fields']);
		}

		/** ---------------------------------------
		/**  Search the entry entry
		/** ---------------------------------------*/
		$search_result = $this->_search_entry($post_data, ee()->session->userdata('username'), $method);

		/** ---------------------------------------
		/**  Get the fields
		/** ---------------------------------------*/
		$this->fields = $this->_get_fieldtypes();


		if(!$search_result || !is_array($search_result))
		{
			/** ---------------------------------------
			/** return response
			/** ---------------------------------------*/
			if(!$search_result)
			{
				return array(
					'message' => 'No Entry found'
				);
			}
			else
			{
				return array(
					'message' => 'The following fields are not filled in: '.$search_result
				);
			}
		}
		else
		{
			$return_entry_data = array();

			foreach($search_result as $data)
			{
				$entry_id = $data['entry_id'];
				
				/** ---------------------------------------
				/**  get the entry data and check if the entry exists
				/** ---------------------------------------*/
				$entry_data = $this->get_entry($entry_id, $post_data['show_fields']);

				/** ---------------------------------------
				/** Get the categories
				/** ---------------------------------------*/
				$entry_data['categories'] = (ee()->webservice_category_model->get_entry_categories(array($entry_data['entry_id'])));

				/** ---------------------------------------
				/**  Process the data per field
				/** ---------------------------------------*/
				if(!empty($this->fields))
				{
					foreach($this->fields as $key=>$val)
					{
						if(isset($entry_data[$val['field_name']])) 
						{
							$entry_data[$val['field_name']] = ee()->webservice_fieldtype->pre_process($entry_data[$val['field_name']], $val['field_type'], $val['field_name'], $val, null, 'search_entry', $entry_id);
						}
					}
				}
				/** ---------------------------------------
				/** set the data correct
				/** ---------------------------------------*/
				$entry_data = $this->_format_read_result($entry_data);

				/* -------------------------------------------
				/* 'webservice_search_entry_end' hook.
				/*  - Added: 3.2
				*/
				$entry_data = Webservice_helper::add_hook('search_entry_per_entry', $entry_data, false, $this->fields);
				// -------------------------------------------

				/* -------------------------------------------
				/* 'webservice_entry_row' hook.
				/*  - Added: 3.5
				*/
				$entry_data = Webservice_helper::add_hook('entry_row', $entry_data, false, $this->fields);
				// -------------------------------------------
				
				//assign the data to the array
				$return_entry_data[] = $entry_data; 
				
			};

			/* -------------------------------------------
			/* 'webservice_search_entry_end' hook.
			/*  - Added: 2.2
			*/
			$return_entry_data = Webservice_helper::add_hook('search_entry_end', $return_entry_data);
			// -------------------------------------------

			/** ---------------------------------------
			/** Lets collect all the entry_ids so we can return
			/** ---------------------------------------*/
			$entry_ids = array();
			foreach($return_entry_data as $row)
			{
				$entry_ids[] = $row['entry_id'];
			}

			/** ---------------------------------------
			/** return response
			/** ---------------------------------------*/
			$this->service_error['succes_read']['id'] = implode('|', $entry_ids); //@deprecated
			$this->service_error['succes_read']['metadata'] = array(
				'id' => implode('|', $entry_ids),
				'limit' => $this->limit,
				'offset' => $this->offset,
				'total_results' => $this->total_results,
				'absolute_results' => $this->absolute_results
			);
			$this->service_error['succes_read']['success'] = true;
			$this->service_error['succes_read']['data'] = $return_entry_data;
			return $this->service_error['succes_read'];
		}		
	}
	

	// ----------------------------------------------------------------
	// PRIVATE FUNCTIONS
	// ----------------------------------------------------------------
	
	/**
	 * Parses out received channel parameters
	 *
	 * @access	public
	 * @param	int
	 * @return	void
	 */
	private function _parse_channel($entry_channel_id = '', $entry_based = true, $method = '')
	{
		//get the channel data
		ee()->db->select('*')->from('channels');
		//select based on entry_id
		if($entry_based)
		{
			ee()->db->where('channel_titles.entry_id', $entry_channel_id);
			ee()->db->join('channel_titles', 'channels.channel_id = channel_titles.channel_id', 'left');
		}
		//based on channelname
		else
		{
			if(is_numeric($entry_channel_id))
			{
				ee()->db->where('channel_id', $entry_channel_id);
			}
			else
			{
				ee()->db->where('channel_name', $entry_channel_id);
			}
		}
		
		$query = ee()->db->get();

		//no result?
		if ($query->num_rows() == 0)
		{	
			return array(
				'success' => false,
				'message' => 'Given channel does not exist'
			);
		}

		//channel data array
		$this->channel = array();

		//check if the channel id is assigned to the user
		//Only do this if there is no free access
		if($method == '' || ee()->webservice_lib->has_free_access($method, ee()->session->userdata('username')) == 0)
		{	
			foreach ($query->result_array() as $key=>$row)
			{
				if ( ! array_key_exists($row['channel_id'], ee()->session->userdata('assigned_channels')) && ee()->session->userdata('group_id') != '1')
				{
					//no rights to the channel
					return array(
						'success' => false,
						'message' => 'You are not authorized to use this channel'
					);
				}
				
				//assign to var
				$this->channel = $row;
			}
		}
		else if($method != '')
		{
			$this->channel = true;
		}

		//Find Fields
		// ee()->db->select('f.*')
		// 		->from('channel_fields f, channels c')
		// 		->where('c.field_group = f.group_id')
		// 		->where('c.channel_id', $this->channel['channel_id'])
		// 		->order_by('f.field_order');
		// $query = ee()->db->get();
		
		// //save the fields
		// $this->fields = array();
		// if ($query->num_rows() != 0)
		// {	
		// 	foreach($query->result_array() as $row)
		// 	{
		// 		$this->fields[$row['field_id']] = $row;
		// 	}
		// }
		
		if(empty($this->channel))
		{
			//no rights to the channel
			return array(
				'success' => false,
				'message' => 'You are not authorized to use this channel'
			);
		}

		$this->fields = $this->_get_fieldtypes();
		
		//everything is ok
		return array('success'=>true);
	}

	// ----------------------------------------------------------------
	
	/**
	 * Search an entry based on the given values
	 *
	 * @access	public
	 * @param	parameter list
	 * @return	void
	 */
	private function _get_fieldtypes()
	{
		$channel_id = isset($this->channel['channel_id']) ? $this->channel['channel_id'] : null ;
		$channel_fields = ee()->channel_data->get_channel_fields($channel_id)->result_array();
		$fields = ee()->channel_data->utility->reindex($channel_fields, 'field_name');	
		return $fields;
	}

	// ----------------------------------------------------------------
	
	/**
	 * Search an entry based on the given values
	 *
	 * @access	public
	 * @param	parameter list
	 * @return	void
	 */
	private function _search_entry($values, $username = '', $method = '')
	{
		$field_sql = '';
		$sql_conditions = '';
		$operator 	=  'AND';
		$operators = array();
		$i = 0;
		$this->offset = $offset = 0;
		$this->limit = $limit = 9999;
		$allowed_orderby = array('title', 'entry_data');
		$orderby = 'wt.entry_date';
		$sort = 'desc';
		$results = null;
		$this->total_results = $total_results = 0;
		$this->absolute_results = $absolute_results = 0;
		$start_on = null;
		$stop_before = null;
		$search_cat = $search_cat_fields = false;

		//get the limit and offset from the values
		if(isset($values['limit']))
		{
			$this->limit = $limit = $values['limit'];
			unset($values['limit']);
		}
		if(isset($values['offset']))
		{
			$this->offset = $offset = $values['offset'];
			unset($values['offset']);
		}
		if(isset($values['orderby']))
		{
			if(in_array($values['orderby'], $allowed_orderby))
			{
				$orderby = $values['orderby'];
			}

			unset($values['orderby']);
		}
		if(isset($values['sort']))
		{
			$sort = $values['sort'] == 'desc' ? 'desc' : 'asc' ;
			unset($values['sort']);
		}

		if(isset($values['start_on']))
		{
			$start_on = strtotime($values['start_on']);
			unset($values['start_on']);
		}
		if(isset($values['stop_before']))
		{
			$stop_before = strtotime($values['stop_before']);
			unset($values['stop_before']);
		}

		// words to ignore in search terms
		include(APPPATH.'config/stopwords'.EXT);	
		$this->_ignore = $ignore;

		$this->_fetch_custom_channel_fields();

		foreach($values as $field_name=>$terms)
		{	
			//if array, skip this one
			if(is_array($terms))
			{
				continue;
			}

			//get the operator
           	if(preg_match('/\[OR\]/', $terms, $match))
           	{
           		$operator = 'OR';
           		$operators[] = 'OR';
           		$terms = trim(str_replace('[OR]', '', $terms));
           	} 
           	else
           	{
           		$operator = 'AND';
           		$operators[] = 'AND';
           		$terms = trim(str_replace('[AND]', '', $terms));
           	}

	 		// search channel custom fields
			if (isset($this->fields_name[$field_name]))
			{
				$field_sql = 'wd.field_id_'.$this->fields_name[$field_name];
			}

			// search channel titles
			else if ($field_name == "title") 
			{
				$field_sql = 'wt.title';
			}

			// search channel titles
			else if ($field_name == "url_title") 
			{
				$field_sql = 'wt.url_title';
			}

			// search channel titles
			else if ($field_name == "channel_id") 
			{
				$field_sql = 'wt.channel_id';
				$terms = '='.$terms;
			}

			// search channel names
			else if ($field_name == "channel" || $field_name == "channel_name") 
			{
				$field_sql = 'wl.channel_name';
			}

			// search entry_ids
			else if ($field_name == "entry_id")
			{
				$field_sql = 'wt.entry_id';
				$terms = '='.$terms;
			}

			// search author_id
			else if ($field_name == "author_id")
			{
				$field_sql = 'wt.author_id';
				$terms = '='.$terms;
			}

			// search category titles
			else if ($field_name =="cat_name")
			{
				$field_sql = 'ct.cat_name';
				$search_cat = true;
			}

			// search category titles
			else if ($field_name =="cat_id")
			{
				$field_sql = 'ct.cat_id';
				$search_cat = true;
			}

			// search category description
			else if ($field_name =="cat_description")
			{
				$field_sql = 'ct.cat_description';
				$search_cat = true;
			}

			// search category custom fields
			else if (!!strstr($field_name,'cat_'))
			{
				// get available custom category fields
				$this->_fetch_custom_category_fields();

				if (isset($this->_cat_fields[$values['site_id']][ltrim($field_name,'cat_')]))
				{
					$field_sql = 'cd.field_id_'.$this->_cat_fields[$values['site_id']][ltrim($field_name,'cat_')];
					$search_cat_fields = true;
					$search_cat = true;
				}
			}

			// can't search this field because it doesn't exist
			else
			{
				$field_sql = '';
			}

			if ($field_sql !== '' && $terms !== '' )
			{
				
				if (strncmp($terms, '=', 1) ==  0)
				{
					/** ---------------------------------------
					/**  Exact Match e.g.: search:body="=pickle"
					/** ---------------------------------------*/
					
					$terms = substr($terms, 1);
					
					// special handling for IS_EMPTY
					if (strpos($terms, 'IS_EMPTY') !== FALSE)
					{
						$terms = str_replace('IS_EMPTY', '', $terms);
						$terms = $this->_sanitize_search_terms($terms, TRUE);
						
						$add_search = ee()->functions->sql_andor_string($terms, $field_sql);
						
						// remove the first AND output by ee()->functions->sql_andor_string() so we can parenthesize this clause
						$add_search = substr($add_search, 3);
            	
						$conj = ($add_search != '' && strncmp($terms, 'not ', 4) != 0) ? 'OR' : 'AND';
            	
						if (strncmp($terms, 'not ', 4) == 0)
						{
							$sql_conditions .= $operator.' ('.$add_search.' '.$conj.' '.$field_sql.' != "") ';
						}
						else
						{
							$sql_conditions .= $operator.' ('.$add_search.' '.$conj.' '.$field_sql.' = "") ';
						}
					}
					else
					{
						$condition = ee()->functions->sql_andor_string($terms, $field_sql).' ';	
						// replace leading AND/OR with desired operator
						$condition =  preg_replace('/^AND|OR/', $operator, $condition,1);
						$sql_conditions.=$condition;					
					}
				}
				else
				{
					/** ---------------------------------------
					/**  "Contains" e.g.: search:body="pickle"
					/** ---------------------------------------*/
					
					if (strncmp($terms, 'not ', 4) == 0)
					{
						$terms = substr($terms, 4);
						$like = 'NOT LIKE';
					}
					else
					{
						$like = 'LIKE';
					}
					
					if (strpos($terms, '&&') !== FALSE)
					{
						$terms = explode('&&', $terms);
						$andor = (strncmp($like, 'NOT', 3) == 0) ? 'OR' : 'AND';
					}
					else
					{
						$terms = explode('|', $terms);
						$andor = (strncmp($like, 'NOT', 3) == 0) ? 'AND' : 'OR';
					}

					$sql_conditions .= ''.(isset($operators[$i-1]) ? $operators[$i-1] : '' ).' (';
					
					foreach ($terms as $term)
					{
						if ($term == 'IS_EMPTY')
						{
							$sql_conditions .= ' '.$field_sql.' '.$like.' "" '.$andor;
						}
						elseif (strpos($term, '\W') !== FALSE) // full word only, no partial matches
						{
							$not = ($like == 'LIKE') ? ' ' : ' NOT ';
							$term = $this->_sanitize_search_terms($term, TRUE);
							$term = '([[:<:]]|^)'.addslashes(preg_quote(str_replace('\W', '', $term))).'([[:>:]]|$)';
							$sql_conditions .= ' '.$field_sql.$not.'REGEXP "'.ee()->db->escape_str($term).'" '.$andor;
						}
						else
						{
							$term = $this->_sanitize_search_terms($term);
							$sql_conditions .= ' '.$field_sql.' '.$like.' "%'.ee()->db->escape_like_str($term).'%" '.$andor;								
						}
					}
					$sql_conditions = substr($sql_conditions, 0, -strlen($andor)).') ';
				}
			} 
			$i++;
		}

		// check that we actually have some conditions to match
		if ($sql_conditions == '')
		{
			//no TMPL?
			if(!isset(ee()->TMPL))
			{
				require_once APPPATH.'libraries/Template.php';
				ee()->TMPL = new EE_Template();
			}

			// no valid fields to search	
			//$this->return_data = ee()->TMPL->no_results();
			//return; // end the process here
		}

		// let's build the query
		$sql = "SELECT distinct(wt.entry_id)
		FROM ".ee()->db->dbprefix."channel_titles AS wt
		LEFT JOIN ".ee()->db->dbprefix."channel_data AS wd
			ON wt.entry_id = wd.entry_id
		LEFT JOIN ".ee()->db->dbprefix."channels AS wl
			ON wt.channel_id = wl.channel_id
		";

		if ($search_cat)
		{
			$sql .="LEFT JOIN ".ee()->db->dbprefix."category_posts as cp
					ON wt.entry_id = cp.entry_id
					LEFT JOIN ".ee()->db->dbprefix."categories as ct
					ON cp.cat_id = ct.cat_id
					";

			// join category field table, again only if required
			if ($search_cat_fields)
			{
				$sql .="LEFT JOIN ".ee()->db->dbprefix."category_field_data as cd
				ON ct.cat_id = cd.cat_id
				";
			}
		}

		//the channels where the user may search
		//do not check when te user has free access
		$channel_id_query = '';
		if(ee()->webservice_lib->has_free_access($method, $username) == 0)
		{
			$assigned_channels = ee()->session->userdata('assigned_channels');

			if(is_array($assigned_channels) && !empty($assigned_channels))
			{
				$channel_ids = implode(',',array_keys(ee()->session->userdata('assigned_channels')));
				$channel_id_query = "wl.channel_id IN(".$channel_ids.") AND";
			}

			else
			{
				return false;
			}
		}

		// limit search to specific site and channels
		$site_id_sql = "(wt.site_id = ".$values['site_id']." AND wd.site_id = ".$values['site_id']." AND wl.site_id = ".$values['site_id'].")";
		//combine
		$sql .= "WHERE (".$channel_id_query." ".$site_id_sql." )"."\n";

		//is the cat_id set
		/*if($cat_id != null)
		{
			$sql .= " AND exp_categories.cat_id = '".ee()->db->escape_str($cat_id)."' ";
		}*/



		// add search conditions
		if(!empty($sql_conditions)) 
		{
			//add where
			$sql = $sql.'AND ('.$sql_conditions.')';

			//add date selection
			if($start_on != null)
			{
				$sql .= ' AND wt.entry_date > "'.$start_on.'"';
			}
			if($stop_before != null)
			{
				$sql .= ' AND wt.entry_date < "'.$stop_before.'"';
			}
		
			//set the order and the sort
			$sql = $sql . " ORDER BY ".$orderby." ".strtoupper($sort)." ";

			//add limits
			//@deprectaed, we do this now with PHP
			//$sql = $sql.'LIMIT '.$offset.', '.$limit;

			//short fix for http://devot-ee.com/add-ons/support/entry-api/viewthread/10684
			//@todo, need to be a better fix
			$sql = str_replace(' (AND ', ' ( ', $sql);

			$results = ee()->db->query($sql);
		}

		//add a hook that return some entry_ids
		//* -------------------------------------------
		/* 'webservice_modify_search' hook.
		/*  - Added: 3.5.1
		*/
		$more_results_ = $more_results = array();
		$more_results_[] = Webservice_helper::add_hook('modify_search', $values, false, $this->fields);
		$it = new RecursiveIteratorIterator(new RecursiveArrayIterator($more_results_));
		foreach($it as $v) 
		{
	  		$more_results[] = $v;
		}
		// -------------------------------------------
		
		// run the query
		if (($results != null && $results->num_rows() == 0) && empty($more_results))
        {
			// no results
            return false;
        }  
   		else
		{
        	// loop through found entries     
	  		$found_ids = array();
	  		if($results != null && $results->num_rows())
	  		{
		        foreach($results->result_array() as $row)
		        { 
					$found_ids[] = $row;
				}
			}

			//add more result
			if(!empty($more_results))
			{
				foreach($more_results as $val)
				{
					$found_ids[] = array('entry_id' => $val);
				}
			}

			//lets do the limit and offset stuff here
			$found_ids_final = array_slice($found_ids, $offset, $limit);

			//set the total and absolute_totals
			$this->absolute_results = count($found_ids);
			$this->total_results = count($found_ids_final);

			return $found_ids_final;
		}
	}

	/**
	 * Fetches custom category fields from page flash cache.
	 * If not cached, runs query and caches result.
	 * @access private
	 * @return boolean
	 */
	private function _fetch_custom_category_fields()
	{
		if (isset(ee()->session->cache['webservice']['custom_category_fields']))
		{
			$this->_cat_fields = ee()->session->cache['webservice']['custom_category_fields'];
			return true;
		}

		// not found so cache them
		$sql = "SELECT field_id, field_name, site_id
        		FROM exp_category_fields";

		$query = ee()->db->query($sql);

		if ($query->num_rows > 0)
		{
			foreach ($query->result_array() as $row)
			{
				// assign standard fields
				$this->_cat_fields[$row['site_id']][$row['field_name']] = $row['field_id'];
				return true;
			}
			ee()->session->cache['webservice']['custom_category_fields'] = $this->_cat_fields;
		}
		else
		{
			return false;
		}
	}
	
	// ----------------------------------------------------------------
	
	/**
	 * Get entry based on entry_id
	 *
	 * @access	public
	 * @param	parameter list
	 * @return	void
	 */
	function get_entry($entry_id = 0, $fields = array())
	{
		//$selecting_fields = array();
		$selecting_fields = array('*');

//		//format the fields
//		if(empty($fields))
//		{
//			$selecting_fields = array('*');
//		}
//		else
//		{
//			foreach($fields as $key=>$field)
//			{
//				switch($field)
//				{
//					case 'entry_id':
//						$selecting_fields[] = 'channel_titles.entry_id';
//					break;
//
//					default:
//						$selecting_fields[] = $field;
//					break;
//				}
//			}
//		}

		//get the entry
		$entry_data_query = ee()->channel_data->get_entry($entry_id, array('select' => $selecting_fields));

		if(!$entry_data_query || $entry_data_query->num_rows() == 0)
		{
			return array();
		}

		$entry = $entry_data_query->row_array();

		//also get the channel data
		$entry = array_merge($entry, $this->_get_channel_data($entry['channel_id']));

		return $entry;
	}

	// ----------------------------------------------------------------

	/**
	 * Get the channel data
	 *
	 * @access    public
	 * @param int $channel_id
	 * @internal param int $entry_id
	 * @internal param \list $parameter
	 * @return    void
	 */
	private function _get_channel_data($channel_id = 0)
	{
		ee()->db->select('channel_name, channel_title');
		ee()->db->where('channel_id', $channel_id);
		$q = ee()->db->get('channels');

		if($q->num_rows() > 0)
		{
			return $q->row_array();
		}

		return array();
	}

	/** 
	* Fetches custom channel fields from page flash cache. 
	* If not cached, runs query and caches result.
	* @access private
	* @return boolean
	*/
    private function _fetch_custom_channel_fields()
    {

		ee()->db->select('field_id, field_type, field_name, site_id');
		ee()->db->from('channel_fields');
		ee()->db->where('field_type !=', 'date');
		ee()->db->where('field_type !=', 'rel'); 
		            
		$query = ee()->db->get();

		$this->fields = array();
		$this->fields_name = array();

		if ($query->num_rows() > 0)
		{
			foreach ($query->result_array() as $row)
			{
				// assign standard custom fields
				$this->fields_name[$row['field_name']] = $row['field_id'];
				$this->fields[] = $row;
			}
			//ee()->session->cache['channel']['custom_channel_fields'] = $this->fields;
			return true;
		}
		else
		{
			return false;
		}       
    }

    // ----------------------------------------------------------------

    /** 
	 * Sanitize earch terms
	 * 
	 * @access private
	 * @param string $keywords
	 * @param boolean $exact_keyword
	 * @return boolean
	 */
	private function _sanitize_search_terms($keywords, $exact_keyword = false)
	{
		$this->min_length = 1;

		/** ----------------------------------------
		/**  Strip extraneous junk from keywords
		/** ----------------------------------------*/
		if ($keywords != "")		
		{
			// Load the search helper so we can filter the keywords
			ee()->load->helper('search');

			$keywords = sanitize_search_terms($keywords);
			
			/** ----------------------------------------
			/**  Is the search term long enough?
			/** ----------------------------------------*/
	
			if (strlen($keywords) < $this->min_length)
			{
				$text = ee()->lang->line('search_min_length');
				
				$text = str_replace("%x", $this->min_length, $text);
							
				return ee()->output->show_user_error('general', array($text));
			}

			// Load the text helper
			ee()->load->helper('text');

			$keywords = (ee()->config->item('auto_convert_high_ascii') == 'y') ? ascii_to_entities($keywords) : $keywords;
			
			
			/** ----------------------------------------
			/**  Remove "ignored" words
			/** ----------------------------------------*/
		
			if (!$exact_keyword)
			{		
				$parts = explode('"', $keywords);
				
				$keywords = '';
				
				foreach($parts as $num => $part)
				{
					// The odd breaks contain quoted strings.
					if ($num % 2 == 0)
					{
						foreach ($this->_ignore as $badword)
						{    
							$part = preg_replace("/\b".preg_quote($badword, '/')."\b/i","", $part);
						}
					}
					$keywords .= ($num != 0) ? '"'.$part : $part;
				}
		
				if (trim($keywords) == '')
				{
					return ee()->output->show_user_error('general', array(ee()->lang->line('search_no_stopwords')));
				}
			}
		}
		
		// finally, double spaces
		$keywords = str_replace("  ", " ", $keywords);
			
		return $keywords;
	}
	
	// ----------------------------------------------------------------
	
	private function _load_channel_settings($channel_id, $type = '')
	{
		//ee()->webservice_lib->get_member_based_on_username($this->userdata->member('username'));
		return '';

		//get the channels
		ee()->db->select('webservice_channel_settings.type, channels.deft_status, channels.deft_comments, webservice_channel_settings.entry_status, webservice_channel_settings.active, webservice_channel_settings.data, channels.channel_id, channels.channel_name, channels.channel_title');
		ee()->db->from('channels');
		ee()->db->join('webservice_channel_settings', 'channels.channel_id = webservice_channel_settings.channel_id', 'left');
		
		//build where query
		$where = array();
		$where['channels.channel_id'] = $channel_id;
		
		//the type
		if(!empty($type))
		{
			$where['webservice_channel_settings.type'] = $type;
		}
		
		ee()->db->where($where);
		$query = ee()->db->get();

		$channel = array();
						
		//format a array
		if ($query->num_rows() > 0)
		{	
			$channel = $query->row();
			$channel->entry_status = $channel->entry_status != '' ? $channel->entry_status : $channel->deft_status ;
			return $channel;
		}
		return '';
	}	

	// ----------------------------------------------------------------

	//format an result for a get 
	private function _format_read_result($result)
	{
		if(!empty($result))
		{
			foreach($result as $key=>$val)
			{
				if(substr($key, 0, 9) == 'field_ft_' || substr($key, 0, 9) == 'field_id_')
				{
					unset($result[$key]);
				}
			}
		}
		return $result;
	}

}

