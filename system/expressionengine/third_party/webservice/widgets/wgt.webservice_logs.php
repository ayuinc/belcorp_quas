<?php

/**
 * Webservice Widget
 *
 * @package		webservice
 * @category	Modules
 * @author		Rein de Vries <info@reinos.nl>
 * @link		http://reinos.nl/add-ons/webservice
 * @license  	http://reinos.nl/add-ons/commercial-license
 * @copyright 	Copyright (c) 2014 Reinos.nl Internet Media
 */


require_once PATH_THIRD.'webservice/config.php';

class Wgt_webservice_logs
{
	// widget name and description can be defined here or in the module language file
	public $widget_title		= 'Webservice Logs';
	public $widget_description	= 'Show the latest logs';

	public $title;		// title displayed at top of widget
	public $settings;	// array of widget settings (required for dynamic widgets only)
	public $wclass;		// class name for additional styling capabilities
	
	public function __construct()
	{	
		$this->EE =& get_instance();

		ee()->lang->loadfile('webservice');

		$this->settings = array(
			'title' => lang('wgt_webservice_logs_title'),
			'nr' => 5
		);
		$this->title  	= lang('wgt_webservice_logs_title');
		$this->wclass 	= 'contentMenu';		
	}
	

	/**
	 * Permissions Function
	 *
	 * Defines permissions needed for user to be able to add widget.
	 *
	 * @access 		public
	 * @return 		bool
	 */
	public function permissions()
	{
		// add any additional custom permission checking here and 
		// return FALSE if user doesn't have permission

		return TRUE;
	}

	/**
	 * Index Function
	 *
	 * @access 		public
	 * @return 		string
	 */
	public function index($settings = NULL)
	{
		$this->title = $settings->title;

		//get the logs
		ee()->db->select('*');
		ee()->db->from('webservice_logs');
		ee()->db->where('site_id', ee()->config->item('site_id'));
		ee()->db->where('method', 'Webservice_service::create_entry');
		ee()->db->or_where('method', 'Webservice_service::update_entry');
		ee()->db->or_where('method', 'Webservice_service::delete_entry');
		ee()->db->limit($settings->nr);
		ee()->db->order_by('log_id', 'desc');
		$result = ee()->db->get();

		if($result->num_rows() > 0)
		{
			$vars['logs'] = $result->result_array();
			return $this->EE->load->view('widget_logs', $vars, TRUE);
		}
		else
		{
			return '<p>No logs available</p>';
		}
	}

	/**
	 * Settings Form Function
	 *
	 * HTML for widget settings form.
	 *
	 * @access		public
	 * @param		object
	 * @return 		string
	 */
	/*public function settings_form($settings)
	{
		return form_open('', array('class' => 'dashForm')).'
			
			<p><label for="nr">Number of logs</label>
			<input type="text" name="nr" value="'.$settings->nr.'" /></p>
			
			<p><input type="submit" value="Save" /></p>
			
			'.form_close();
	}*/

	
	
	

}