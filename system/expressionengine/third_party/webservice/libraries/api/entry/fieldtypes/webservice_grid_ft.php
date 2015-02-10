<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Grid fieldtype file
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

class Webservice_grid_ft
{
	public $name = 'grid';

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
		if(!empty($data) && is_array($data))
		{
			//set the rows
			$rows = isset($data['rows']) ? $data['rows'] : array();

			//max rows?
			if($this->field_settings['grid_max_rows'] != '' && (count($rows) > $this->field_settings['grid_max_rows']) )
			{
				$rows = array_slice($rows, 0, $this->field_settings['grid_max_rows']);
			}

			//if not new, an update (doh ;-), delete the old ones
			if($is_new == false)
			{
				if(ee()->db->table_exists('channel_grid_field_'.$this->field_data['field_id']))
				{
					ee()->db->where('entry_id', $entry_id);
					ee()->db->delete('channel_grid_field_'.$this->field_data['field_id']);
				}
			}
			
			//get te grid settings
			$grid_settings = $this->get_grid_settings($this->field_data['field_id']);
			
			//set the insert array
			$insert_array = array();
			
			//loop over the items
			foreach($rows as $order => $row)
			{
				//loop over the fields
				foreach($row as $key  => $val)
				{
					//grid insert array goed maken
					$insert_array['new_row_'.$order]['col_id_'.$this->get_grid_col_id($this->field_data['field_id'], $key)] = $val;
				}
			}
			
			return $insert_array;
		}
		
		//return nothing when there is nothing
		return '';
		
		/*
			Array
(
    [new_row_2] =&gt; Array
        (
            [col_id_3] =&gt; asdf
            [col_id_4] =&gt; sdfsf
        )

)

		*/
	}

	// ----------------------------------------------------------------

	/**
	 * Validate the field
	 * 
	 * @param  mixed $data  
	 * @param  bool $is_new
	 * @return void            
	 */
	public function webservice_validate($data = null, $is_new = false)
	{
		//validate the min rows
		if($this->field_settings['grid_min_rows'] != '' && (count($data) < $this->field_settings['grid_min_rows']) )
		{
			$this->validate_error = 'You must add a min of '.$this->field_settings['grid_min_rows'].' rows';
			return false;
		}

		//validate the min rows
		if($this->field_settings['grid_max_rows'] != '' && (count($data) > $this->field_settings['grid_max_rows']) )
		{
			$this->validate_error = 'You reach the limit of '.$this->field_settings['grid_max_rows'].' rows';
			return false;
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
		//get the data
		if(ee()->db->table_exists('channel_grid_field_'.$this->field_data['field_id']))
		{
			//get the data
			ee()->db->where('entry_id', $entry_id);
			$query = ee()->db->get('channel_grid_field_'.$this->field_data['field_id']);

			$return = array();

			//format the data
			if($query->num_rows() > 0)
			{
				foreach($query->result_array() as $key=>$val)
				{
					//print_r($val);
					foreach($val as $k=>$v)
					{
						//set the name
						if(preg_match('/col_id_/', $k))
						{
							//get settings
							$col_settings = $this->get_grid_col_field_settings($this->field_data['field_id'], str_replace('col_id_', '', $k));

							//$k = $grid_labels[$k];
							$k = $this->get_grid_col_field_name($this->field_data['field_id'], str_replace('col_id_', '', $k));
						
							//if $k is empty, skip this one
							if($k == '')
							{
								continue;
							}

							/* -------------------------------------------
							/* 'read_entry_grid' hook.
							/*  - Added: 3.5.1
							*/
							$return[$key][$k] = Webservice_helper::add_hook('read_entry_grid', $v, false, $col_settings);
							/** ---------------------------------------*/
						}	
					}
				}

				return $return;
			}
		}

		// return $data;
		return $data;
	}

	// ----------------------------------------------------------------------
	
	/**
	 * Get the grid settings
	 * 
	 * @return void
	 */
	public function get_grid_settings($field_id = 0) 
	{
		ee()->db->where('field_id', $field_id);
		$query = ee()->db->get('grid_columns');

		$grid_settings = array();

		if($query->num_rows() > 0)
		{
			foreach($query->result() as $k => $row)
			{
				foreach($row as $key => $val)
				{
					$grid_settings[$k][$key] = $key == 'col_settings' ? (array) json_decode($val) : $val;
				}
			}
		}

		return $grid_settings;
	}

	// ----------------------------------------------------------------------
	
	/**
	 * Get the grid settings
	 * 
	 * @return void
	 */
	public function get_grid_col_id($field_id = 0, $col_name = '') 
	{
		ee()->db->where('field_id', $field_id);
		ee()->db->where('col_name', $col_name);
		$query = ee()->db->get('grid_columns');

		if($query->num_rows() > 0)
		{
			return $query->row()->col_id;
		}

		return '';
	}

	// ----------------------------------------------------------------------
	
	/**
	 * Get the Gridd field_name
	 * 
	 * @return void
	 */
	public function get_grid_col_field_name($field_id = 0, $col_id = 0) 
	{
		ee()->db->where('field_id', $field_id);
		ee()->db->where('col_id', $col_id);
		$query = ee()->db->get('grid_columns');

		if($query->num_rows() > 0)
		{
			return $query->row()->col_name;
		}

		return '';
	}

	// ----------------------------------------------------------------------
	
	/**
	 * Get the Grid field_type
	 * 
	 * @return void
	 */
	public function get_grid_col_field_settings($field_id = 0, $col_id = 0) 
	{
		ee()->db->where('field_id', $field_id);
		ee()->db->where('col_id', $col_id);
		$query = ee()->db->get('grid_columns');

		if($query->num_rows() > 0)
		{
			return $query->row_array();
		}

		return array();
	}

}