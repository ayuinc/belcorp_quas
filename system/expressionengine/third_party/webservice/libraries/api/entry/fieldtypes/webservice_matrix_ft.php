<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Matrix fieldtype file
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

class Webservice_matrix_ft
{
	public $name = 'matrix';

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
			
			//set the revision
			$revision = isset($data['trigger_revisions']) ? $data['trigger_revisions'] : 1;
		
			//get the col settings
			$matrix_settings = $this->get_matrix_settings($this->field_data['field_id']);
			
			//max rows?
			if($this->field_settings['max_rows'] != '' && (count($rows) > $this->field_settings['max_rows']) )
			{
				$rows = array_slice($rows, 0, $this->field_settings['max_rows']);
			}

			//if not new, an update (doh ;-), delete the old ones
			if($is_new == false)
			{
				ee()->db->where('entry_id', $entry_id);
				ee()->db->delete('matrix_data');
			}
			
			//set the order array
			$order_array = array();
			
			//set the insert array
			$insert_array = array();
			
			//loop over the items
			
			foreach($rows as $order => $row)
			{//echo $order.'-';
				//set the order
				$order_array[] = 'row_new_'.$order;
				
				//loop over the fields
				foreach($row as $key => $val)
				{
					//grid insert array goed maken
					$insert_array['row_new_'.$order]['col_id_'.$this->get_matrix_col_id($this->field_data['field_id'], $key)] = $val;
				}
			}
			
			//combine the data
			return  array_merge(array('row_order' => $order_array), $insert_array, array('trigger_revisions' => $revision));
		}
		
		//return nothing when there is nothing
		return '';
		
		//format data Matrix
		/*return array(
			'row_order' => array(
				'row_new_0',
				'row_new_1'
			),
			'row_new_0' => array(
				'col_id_7' => 'test veld 1',
				'col_id_8' => 'test veld 2'
			),
			'row_new_1' => array(
				'col_id_7' => 'test veld 1',
				'col_id_8' => 'test veld 2'
			),
			'trigger_revisions' => 1
		);*/
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
		if($this->field_settings['min_rows'] != '' && (count($data) < $this->field_settings['min_rows']) )
		{
			$this->validate_error = 'You must add a min of '.$this->field_settings['min_rows'].' rows';
			return false;
		}

		//validate the min rows
		if($this->field_settings['max_rows'] != '' && (count($data) > $this->field_settings['max_rows']) )
		{
			$this->validate_error = 'You reach the limit of '.$this->field_settings['max_rows'].' rows';
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
		ee()->db->where('entry_id', $entry_id);
		$query = ee()->db->get('matrix_data');

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
						$col_settings = $this->get_matrix_col_field_settings($this->field_data['field_id'], str_replace('col_id_', '', $k));

						$k = $this->get_matrix_col_field_name($this->field_data['field_id'], str_replace('col_id_', '', $k));
						
						//if $k is empty, skip this one
						if($k == '')
						{
							continue;
						}

						/* -------------------------------------------
						/* 'read_entry_matrix' hook.
						/*  - Added: 3.5.1
						*/
						$return[$key][$k] = Webservice_helper::add_hook('read_entry_matrix', $v, false, $col_settings);
						/** ---------------------------------------*/
					}
				}
			}

			return $return;
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
	public function get_matrix_settings($field_id = 0) 
	{
		ee()->db->where('field_id', $field_id);
		$query = ee()->db->get('matrix_cols');

		$matrix_settings = array();

		if($query->num_rows() > 0)
		{
			foreach($query->result() as $k => $row)
			{
				foreach($row as $key => $val)
				{
					$matrix_settings[$k][$key] = $key == 'col_settings' ? (array) json_decode($val) : $val;
				}
			}
		}

		return $matrix_settings;
	}

	// ----------------------------------------------------------------------
	
	/**
	 * Get the grid settings
	 * 
	 * @return void
	 */
	public function get_matrix_col_id($field_id = 0, $col_name = '') 
	{
		ee()->db->where('field_id', $field_id);
		ee()->db->where('col_name', $col_name);
		$query = ee()->db->get('matrix_cols');

		if($query->num_rows() > 0)
		{
			return $query->row()->col_id;
		}

		return '';
	}

	// ----------------------------------------------------------------------
	
	/**
	 * Get the matrix field_name
	 * 
	 * @return void
	 */
	public function get_matrix_col_field_name($field_id = 0, $col_id = 0) 
	{
		ee()->db->where('field_id', $field_id);
		ee()->db->where('col_id', $col_id);
		$query = ee()->db->get('matrix_cols');

		if($query->num_rows() > 0)
		{
			return $query->row()->col_name;
		}

		return '';
	}

	// ----------------------------------------------------------------------
	
	/**
	 * Get the Matrix field_type
	 * 
	 * @return void
	 */
	public function get_matrix_col_field_settings($field_id = 0, $col_id = 0) 
	{
		ee()->db->where('field_id', $field_id);
		ee()->db->where('col_id', $col_id);
		$query = ee()->db->get('matrix_cols');

		if($query->num_rows() > 0)
		{
			return $query->row_array();
		}

		return array();
	}

}