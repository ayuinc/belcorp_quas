<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Static Entry API
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

class Webservice_entry_static
{
	// ----------------------------------------------------------------------
	
	/**
	 * Insert a entry in the database
	 *
	 * @param none
	 * @return void
	 */
	static function create_entry($data, $type = '')
	{		
		//load the entry class
		ee()->load->library('api/entry/webservice_entry');

		//post the data to the service
		$return_data = ee()->webservice_entry->create_entry($data);
		
		//format the array, because we cannot do nested arrays
		if($type != 'rest' && isset($return_data['data']))
		{
			$return_data['data'] = webservice_format_data($return_data['data'], $type);
		}

		//format soap
		if($type == 'soap')
		{
			if(isset($return_data['metadata']))
			{
				$return_data['metadata'] = webservice_format_soap_data($return_data['metadata']);
			}
		}

		//return result
		return $return_data;
	}

	// ----------------------------------------------------------------------
	
	/**
	 * read a entry
	 *
	 * @param none
	 * @return void
	 */
	static function read_entry($data, $type = '')
	{	
		//load the entry class
		ee()->load->library('api/entry/webservice_entry');

		//post the data to the service
		$return_data = ee()->webservice_entry->read_entry($data);

		//var_dump($return_data);exit;
		if($type == 'soap')
		{
			if(isset($return_data['data']))
			{	
				$return_data['data'] = webservice_format_soap_data($return_data['data'], 'entry_list');
			}
			if(isset($return_data['metadata']))
			{
				$return_data['metadata'] = webservice_format_soap_data($return_data['metadata']);
			}
		}		
		
		//format the array, because we cannot do nested arrays
		if($type != 'rest' && isset($return_data['data']))
		{
			$return_data['data'] = webservice_format_data($return_data['data'], $type);
		}

		//return result
		return $return_data;
	}	
	
	// ----------------------------------------------------------------------
	
	/**
	 * Update a entry in the database
	 *
	 * @param none
	 * @return void
	 */
	static function update_entry($data, $type = '')
	{		
		//load the entry class
		ee()->load->library('api/entry/webservice_entry');
		
		//post the data to the service
		$return_data = ee()->webservice_entry->update_entry($data);

		//format the array, because we cannot do nested arrays
		if($type != 'rest'  && isset($return_data['data']))
		{
			$return_data['data'] = webservice_format_data($return_data['data'], $type);
		}

		if($type == 'soap')
		{
			if(isset($return_data['metadata']))
			{
				$return_data['metadata'] = webservice_format_soap_data($return_data['metadata']);
			}
		}

		//return result
		return $return_data;
	}	 
	
	// ----------------------------------------------------------------------
	
	/**
	 * Delete a entry
	 *
	 * @param none
	 * @return void
	 */
	static function delete_entry($data, $type = '')
	{	
		//load the entry class
		ee()->load->library('api/entry/webservice_entry');

		//post the data to the service
		$return_data = ee()->webservice_entry->delete_entry($data);
		
		//format the array, because we cannot do nested arrays
		if($type != 'rest' && isset($return_data['data']))
		{
			$return_data['data'] = webservice_format_data($return_data['data'], $type);
		}

		if($type == 'soap')
		{
			if(isset($return_data['metadata']))
			{
				$return_data['metadata'] = webservice_format_soap_data($return_data['metadata']);
			}
		}
		
		//return result
		return $return_data;
	}	

	// ----------------------------------------------------------------------
	
	/**
	 * search a entry
	 *
	 * @param none
	 * @return void
	 */
	static function search_entry($data, $type = '')
	{	
		//load the entry class
		ee()->load->library('api/entry/webservice_entry');

		//post the data to the service
		$return_data = ee()->webservice_entry->search_entry($data);

		//rename the key
		if(isset($return_data['entries']))
		{
			//soap
			if($type == 'soap')
			{
				$return_data['data'] = webservice_format_soap_data($return_data['entries'], 'entry_list');
				$return_data['metadata'] = webservice_format_soap_data($return_data['metadata']);
			}

			//rest
			else
			{
				$return_data['data'] = $return_data['entries'];
			}
			
			unset($return_data['entries']);
		}

		//format the array, because we cannot do nested arrays
		if($type != 'rest' && isset($return_data['data']))
		{
			$return_data['data'] = webservice_format_data($return_data['data'], $type);
		}

		//return result
		return $return_data;
	}
}