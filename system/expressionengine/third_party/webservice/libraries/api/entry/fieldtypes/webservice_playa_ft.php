<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Playa fieldtype file
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

class Webservice_playa_ft
{
	public $name = 'playa';

	private $tmp_entry_id = 42949672;
	
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
		//print_r($this->field_settings);return;
		if(!empty($data) && is_array($data))
		{
			//only one item allowed
			if($this->field_settings['multi'] != 'y')
			{
				$data = array_slice($data, 0, 1);
			}

			//if not new, an update (doh ;-), delete the old ones
			if($is_new == false)
			{
				ee()->db->where('parent_entry_id', $entry_id);
				ee()->db->delete('playa_relationships');
			}
			
			//set the insert data
			$insert_data = array();

			//loop over the items
			foreach($data as $order => $row)
			{
				$insert_data['selections'][] = $row;
			}
			
			return $insert_data;
		}

		//return nothing, when there is nothing
		return '';
		
		/*
			Array ( [selections] => Array ( [0] => [1] => 37 [2] => 38 [3] => 36 [4] => 42 ) )
		*/
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
		//get the data
		ee()->db->select('child_entry_id as `entry_id`, rel_order as `order`');
		ee()->db->where('parent_entry_id', $entry_id);
		//ee()->db->save_queries = true;
		$query = ee()->db->get('playa_relationships');
		//echo ee()->db->last_query();

		if($query->num_rows() > 0)
		{
			return $query->result_array();
		}

		return $data;
	}

	// ----------------------------------------------------------------------
	
	/**
	 * get the channel id for an entry_id
	 * 
	 */
	public function channel_id($entry_id = 0) 
	{
		ee()->db->select('channel_id');
		ee()->db->where('entry_id', $entry_id);
		$query = ee()->db->get('channel_titles');

		if($query->num_rows() > 0)
		{
			return $query->row()->channel_id;
		}

		return false;
	}
	
	// ----------------------------------------------------------------------
	
	/**
	 * Get the entry title
	 * 
	 */
	public function get_entry_title($entry_id = 0) 
	{
		ee()->db->select('title');
		ee()->db->where('entry_id', $entry_id);
		$query = ee()->db->get('channel_titles');

		if($query->num_rows() > 0)
		{
			return $query->row()->title;
		}

		return false;
	}
}