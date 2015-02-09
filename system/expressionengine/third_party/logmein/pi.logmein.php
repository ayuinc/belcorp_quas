<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * ExpressionEngine LogMeIn Plugin
 *
 * @package		LogMeIn
 * @category	Plugins
 * @description	Logs a specified user in
 * @author		Ben Croker
 * @link		http://www.putyourlightson.net/logmein/
 */


$plugin_info = array(
				'pi_name'			=> 'LogMeIn',
				'pi_version'		=> '1.5',
				'pi_author'			=> 'Ben Croker',
				'pi_author_url'		=> 'http://www.putyourlightson.net/',
				'pi_description'	=> 'Logs a specified user in',
				'pi_usage'			=> Logmein::usage()
			);


class Logmein 
{	
	var $debug = 0;

	/**
	  *  Constructor
	  */
	function __construct()
	{
		$this->EE =& get_instance();
	}

	// --------------------------------------------------------------------
	
	/**
	  *  Log the user in
	  */
	function now()
	{
		// check if member group is correct if submitted
		if ($this->EE->TMPL->fetch_param('if_member_group') AND $this->EE->session->userdata['group_id'] != $this->EE->TMPL->fetch_param('if_member_group'))
		{
			return;
		}
		
		// check if username was submitted
		if (!$username = $this->EE->TMPL->fetch_param('username'))
		{
			if ($this->EE->TMPL->fetch_param('errors') AND $this->EE->TMPL->fetch_param('errors') == "true")
			{
				$this->EE->output->show_user_error('general', 'LogMeIn requires a username');
			}
			
			return;
		}
		
		
		// fetch member		
		$this->EE->db->where('username', $username);
		$query = $this->EE->db->get('members');
		
		
		// if invalid username		
		if (!$row = $query->row())
		{
			if ($this->EE->TMPL->fetch_param('errors') AND $this->EE->TMPL->fetch_param('errors') == "true")
			{
				$this->EE->output->show_user_error('general', 'Username does not exist');
			}
			
			return;
		}
		
		
		// load authentication library
		$this->EE->load->library('auth');
		
		
		// create authenticated session
		$sess = new Auth_result($row);
		
		
		// set default cookie expiration to one day
		$expire = $this->EE->TMPL->fetch_param('expire') ? $this->EE->TMPL->fetch_param('expire') : 60*60*24;
		$sess->remember_me($expire);
				
		// start session		

		$sess->start_session();
	}
	
	// --------------------------------------------------------------------

	/**
	  *  Log the user out
	  */
	function logout()
	{
		// kill the session and cookies		
		$this->EE->db->where('site_id', $this->EE->config->item('site_id'));
		$this->EE->db->where('ip_address', $this->EE->input->ip_address());
		$this->EE->db->where('member_id', $this->EE->session->userdata('member_id'));
		$this->EE->db->delete('online_users');		
		
		$this->EE->session->destroy();
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Plugin Usage
	 */
	function usage()
	{
		ob_start(); 
		?>

Put the following line into any template to log the current visitor in. 

{exp:logmein:now username="priveleged_guest" if_member_group="3" expire="3600"}

The username parameter is required.
The if_member_group parameter is optional and defines the member group id that the currently logged in user must belong to.
The expire parameter is optional and sets the expiration time in seconds of the cookie.

To log the current member out use:

{exp:logmein:logout}

		<?php
		$buffer = ob_get_contents();
			
		ob_end_clean(); 
		
		return $buffer;
	}
	
}
// END CLASS

/* End of file pi.logmein.php */
/* Location: ./system/expressionengine/third_party/logmein/pi.logmein.php */