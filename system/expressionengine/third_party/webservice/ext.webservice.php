<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Webservice Extension
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
 
class Webservice_ext
{	
	
	public $name			= WEBSERVICE_NAME;
	public $description		= WEBSERVICE_DESCRIPTION;
	public $version			= WEBSERVICE_VERSION;
	public $settings 		= array();
	public $docs_url		= WEBSERVICE_DOCS;
	public $settings_exist	= 'n';
	public $required_by 	= array('Webservice Module');
	
	private $EE;
	
	/**
	 * Constructor
	 *
	 * @param 	mixed	Settings array or empty string if none exist.
	 */
	public function __construct($settings = '')
	{
		//get the instance of the EE object
		//$this->EE =& get_instance();		
	}

	/**
	 * sessions_start
	 *
	 * @param 	mixed	Settings array or empty string if none exist.
	 */
	function sessions_start($ee)
	{
		ee()->load->helper('webservice_helper');

		//set the session to the var
		ee()->session = $ee;

		//just an page request?
		if (REQ == 'PAGE' && !empty($ee))
		{		
			//load the lib
			ee()->load->library('webservice_lib');
	
			//get the trigger
			$url_trigger = ee()->webservice_settings->item('url_trigger', 'webservice');

			//is the first segment 'webservice'
			$is_webservice = ee()->uri->segment(1) == $url_trigger ? true : false;
			
			//is the request a page and is the first segment webservice?
			//than we need to trigger te services
			if($is_webservice)
			{
				//set agent if missing
				$_SERVER['HTTP_USER_AGENT'] = ee()->input->user_agent() == false ? '0' : ee()->input->user_agent();

				//debug?
				if(ee()->webservice_settings->item('debug'))
				{
					//show better error reporting
					error_reporting(E_ALL);
					ini_set('display_errors', '1');

					//set the DB to save the queries
					ee()->db->save_queries = true;
				}
				     			
				//service request
				$service_request = ee()->uri->segment(2);
				
				//load the route class
				include_once PATH_THIRD .'webservice/libraries/webservice_route.php';
				
				//call the class
				$this->webservice = new Webservice_route($service_request);
					
				//stop the whole process because we will not show futher more 
				ee()->extensions->end_script = true;
				die();	
			}	
			
		}
		//print_r(ee()->session->userdata);	
	}

	/**
	 * sessions_start
	 *
	 * @param 	mixed	Settings array or empty string if none exist.
	 */
	// function publisher_set_language()
	// {
	// 	//echo 1;
	// 	//webservice_helper::log_array(array('asdf'));
	// }

	
	function debug_string_backtrace() 
	{ 
		ob_start(); 
		debug_print_backtrace(); 
		$trace = ob_get_contents(); 
		ob_end_clean(); 

		// Remove first item from backtrace as it's this function which 
		// is redundant. 
		$trace = preg_replace ('/^#0\s+' . __FUNCTION__ . "[^\n]*\n/", '', $trace, 1); 

		// Renumber backtrace items. 
		$trace = preg_replace ('/^#(\d+)/me', '\'#\' . ($1 - 1)', $trace); 

		return $trace; 
    } 

    // ----------------------------------------------------------------------
	
	/**
	 * webservice_modify_search
	 *
	 * @return void
	 */
	public function webservice_modify_search($values, $fields)
	{
		if(!empty($values))
		{
			foreach($values as $key=>$value)
			{
				//is an array, we got 
				if(is_array($value) && !empty($value))
				{
					$field = key($value);
					$value = $value[$field];
					
					//make sure we also check some grid or matrix fields
					if(!empty($fields))
					{
						foreach($fields as $field_data)
						{
							//grid, we are gonna search also in the grid data
							if($field_data['field_type'] == 'grid')
							{
								if(ee()->db->table_exists('channel_grid_field_'.$field_data['field_id']))
								{

									ee()->db->where('col_name', $field);
									ee()->db->from('grid_columns');
								 	$query = ee()->db->get();

								 	if($query->num_rows())
								 	{
								 		ee()->db->select('entry_id');
								 		if (strncmp($value, '=', 1) ==  0)
										{
											$value = substr($value, 1);
											ee()->db->where('col_id_'.$query->row()->col_id, $value);
										}
										else
										{
											ee()->db->like('col_id_'.$query->row()->col_id, $value);
										}
										
										ee()->db->from('channel_grid_field_'.$field_data['field_id']);
										$query = ee()->db->get();

										$entry_id = array();
										if($query->num_rows())
										{

											foreach($query->result() as $entry)
											{
												$entry_id[$entry->entry_id] = $entry->entry_id;
											}
										}
										
										return $entry_id;
								 	}
								}
							}

							//Matrix, we are gonna search in the matrix data
							if($field_data['field_type'] == 'matrix')
							{
								ee()->db->where('col_name', $field);
								ee()->db->from('matrix_cols');
							 	$query = ee()->db->get();

							 	if($query->num_rows())
							 	{
							 		ee()->db->select('entry_id');
							 		if (strncmp($value, '=', 1) ==  0)
									{
										$value = substr($value, 1);
										ee()->db->where('col_id_'.$query->row()->col_id, $value);
									}
									else
									{
										ee()->db->like('col_id_'.$query->row()->col_id, $value);
									}
									
									ee()->db->from('matrix_data');
									$query = ee()->db->get();

									$entry_id = array();
									if($query->num_rows())
									{

										foreach($query->result() as $entry)
										{
											$entry_id[$entry->entry_id] = $entry->entry_id;
										}
									}
									
									return $entry_id;
							 	}
							}
						}
					}
				}
			}
		}

		return array();
	}	
	
	// ----------------------------------------------------------------------
	
	/**
	 * Activate Extension
	 *
	 * This function enters the extension into the exp_extensions table
	 *
	 * @see http://codeigniter.com/user_guide/database/index.html for
	 * more information on the db class.
	 *
	 * @return void
	 */
	public function activate_extension()
	{
		//the module will install the extension if needed
		return true;
	}	
	
	// ----------------------------------------------------------------------

	/**
	 * Disable Extension
	 *
	 * This method removes information from the exp_extensions table
	 *
	 * @return void
	 */
	function disable_extension()
	{
		//the module will disable the extension if needed
		return true;
	}

	// ----------------------------------------------------------------------

	/**
	 * Update Extension
	 *
	 * This function performs any necessary db updates when the extension
	 * page is visited
	 *
	 * @return 	mixed	void on update / false if none
	 */
	function update_extension($current = '')
	{
		//the module will update the extension if needed
		return true;
	}	
	
	// ----------------------------------------------------------------------
}

/* End of file ext.webservice.php */
/* Location: /system/expressionengine/third_party/webservice/ext.webservice.php */