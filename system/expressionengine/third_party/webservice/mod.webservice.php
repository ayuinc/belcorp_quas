<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * MOD file
 *
 * @package		webservice
 * @category	Modules
 * @author		Rein de Vries <info@reinos.nl>
 * @license  	http://reinos.nl/add-ons/commercial-license
 * @link        http://reinos.nl/add-ons//add-ons/entry-api
 * @copyright 	Copyright (c) 2014 Reinos.nl Internet Media
 */
 
// ------------------------------------------------------------------------

/**
 * @deprecated in v1.1.1
 * webservice is now working on full url instead of the ACT uri.
 */	

/**
 * Include the config file
 */
require_once PATH_THIRD.'webservice/config.php';
 
class Webservice {
	
	private $xmlrpc;
	
	private $soap;
	
	private $EE;
	
	// ----------------------------------------------------------------------
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		//load the Classes
		//$this->EE =& get_instance();
		
		//load webservice helper
		ee()->load->library('webservice_lib');

	}

	// ----------------------------------------------------------------

	/**
	 * Prepare channels to show
	 * 
	 * @return 	DB object
	 */
	public function ajax_cp()
	{
		
		switch($_GET['function'])
		{
			case 'get_channels' : echo json_encode(ee()->webservice_model->get_channels_for_member($_GET['member_id']));
				break;
			case 'show_queries' :
				$html = '';
				$result = ee()->webservice_model->get_all_logs($_GET['log_id']);
				if(!empty($result) && isset($result[0]->queries))
				{
					//check if the data is also base64_encoded
					if ( base64_decode($result[0]->queries, true) )
					{
						$result[0]->queries = base64_decode($result[0]->queries);
					}

					$data = unserialize($result[0]->queries);
					if(!empty($data))
					{
						$html = '<h3>Total of '.count($data).' Queries</h3>';
						foreach($data as $key=>$val)
						{
							if(strpos($val, '#CI') !== false)
							{
								$val = explode('#CI', $val);
							}
							else
							{
								$val = explode('#APP', $val);
							}

							$html .= '<div style="border-bottom:1px solid #aaa3a5;"><pre><code class="sql">' .$val[0] . (isset($val[1]) ? '<small><i>'.$val[1].'</i></small>' : '' ) .'</code></pre></div>';
						}
					}
				}
				$html .= '';
				echo $html;
				break;
		}


		exit;
	}
}


/* End of file mod.webservice.php */
/* Location: /system/expressionengine/third_party/webservice/mod.webservice.php */