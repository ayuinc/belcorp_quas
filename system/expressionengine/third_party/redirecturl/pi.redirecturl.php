<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * ExpressionEngine - by EllisLab
 *
 * @package		ExpressionEngine
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2003 - 2011, EllisLab, Inc.
 * @license		http://expressionengine.com/user_guide/license.html
 * @link		http://expressionengine.com
 * @since		Version 2.0
 * @filesource
 */
 
// ------------------------------------------------------------------------

/**
 * RedirectURL Plugin
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Plugin
 * @author		Rodrigo Passos
 * @link		http://rodrigopassos.me
 */

$plugin_info = array(
	'pi_name'		=> 'RedirectURL',
	'pi_version'	=> '0.2',
	'pi_author'		=> 'Rodrigo Passos',
	'pi_author_url'	=> 'http://rodrigopassos.me',
	'pi_description'=> 'Perform 301 Redirect to a given URL',
	'pi_usage'		=> Redirecturl::usage()
);


class Redirecturl {

	public $return_data;
    
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->EE =& get_instance();

		$this->EE->load->helper('url');

		$url = $this->EE->TMPL->fetch_param('url');
		$type = $this->EE->TMPL->fetch_param('type') ? intval($this->EE->TMPL->fetch_param('type')) : 301;

		redirect(prep_url($url), 'location', $type);
	}
	
	// ----------------------------------------------------------------
	
	/**
	 * Plugin Usage
	 */
	public static function usage()
	{
		ob_start();
?>

{exp:redirecturl url="http://your_url.com"}

Optional Parameters:
type="302"

<?php
		$buffer = ob_get_contents();
		ob_end_clean();
		return $buffer;
	}
}


/* End of file pi.redirecturl.php */
/* Location: /system/expressionengine/third_party/redirecturl/pi.redirecturl.php */