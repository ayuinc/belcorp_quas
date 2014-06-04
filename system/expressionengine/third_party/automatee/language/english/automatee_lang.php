<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

 /**
 * mithra62 - Automat:ee
 *
 * @package		mithra62:Automatee
 * @author		Eric Lamb
 * @copyright	Copyright (c) 2012, mithra62, Eric Lamb.
 * @link		http://mithra62.com/projects/view/automat-ee/
 * @version		1.2
 * @filesource 	./system/expressionengine/third_party/automatee/
 */

$lang = array(

// Required for MODULES page

'automatee_module_name'		=> 'Automat:ee',
'automatee_module_description'	=> 'Interface to create Cron jobs within ExpressionEngine. ',

//----------------------------------------

// Additional Key => Value pairs go here

// END
'description' => 'Description',
'description_instructions' => 'Use this for whatever you want ;)',
'name' => 'Name',
'status_instructions' => 'Making the status Inactive will remove this Cron from the activity queue.',
'inactive' => 'Inactive',
'active' => 'Active',
'last_ran' => 'Last Ran',
'total_runs' => 'Total Runs',
'status' => 'Status',
'name_instructions' => 'Simply: what you\'re calling your cron. Be descriptive because once you set it and go about your life you\'re guaranteed to forget the details.',
'schedule' => 'Schedule',
'schedule_instructions' => 'When do you want your action to be activated? If you need a custom time frame choose "Custom" from the list and use standard Cron syntax for the frequency.',
'type' => 'Cron Type',
'type_instructions' => 'What do you want Automatee to do?',
'configure' => 'Configure',
'cron_notify_emails' => 'Notification Emails',
'cron_notify_emails_instructions' => 'Put a single email address on each line that you want to be notified on completion of each cron job. If no email addresses are entered no notification will be sent. Invalid email addresses will be stripped.',
'license_number' => 'License Number',
'missing_license_number' => 'Please enter your license number. <a href="#config_url#">Enter License</a> or <a href="http://devot-ee.com/add-ons/automatee/">Buy A License</a>',
'module_instructions' => '',
'view_crons' => 'View Crons',
'add_cron' => 'Add Cron',
'settings' => 'Settings',
'cli_crons' => 'Command Line Crons',
'url_crons' => 'Get URL Crons',
'no_crons' => 'No Cron Jobs Found.',
'delete_selected' => 'Delete Selected',
'command' => 'Command',
'get_url_address' => 'Get URL Address',
'cli_command' => 'CLI Command',
'cron_not_found' => 'Cron couldn\'t be found...',
'cron_details' => 'Cron Details',
'delete_crons' => 'Delete Crons',
'plugin' => 'Plugin',
'command_line' => 'Command Line',
'get_url' => 'Get URL',
'get_url_instructions' => 'Enter the full URL. Note that the request is done using the standard GET method and, if logging is enabled, the return will be saved. ',
'cli_command_instructions' => 'Enter the command exactly as you would from the *nix terminal. Note that this function does not work on Windows systems and is contingent upon your system having the exec() function available. ',
'choose_module' => 'Choose Module and Method',
'choose_module_instructions' => 'Select the Module you want to run. If there is a particular method you want executed (outside of the controller) enter the name in the cooresponding text field. Keep in mind that you can not pass arguments to the methods at this time.',
'choose_plugin_instructions' => 'Select the Plugin you want to run. If there is a particular method you want executed (outside of the controller) enter the name in the cooresponding text field. Keep in mind that you can not pass arguments to the methods at this time.',
'choose_plugin' => 'Choose Plugin and Method',
'cron_updated' => 'Cron Updated',
'allowed_access_levels' => 'Allowed Access Levels',
'allowed_access_levels_instructions' => 'Automatee will initially only allow Super Admins access but if you need to allow other groups select them from the list.',
'log_cron_start' => '##cron_name## Started.',
'log_cron_sucess' => '##cron_name## Completed.',
'log_module_not_installed' => 'Module Not Installed',
'log_module_not_exist' => 'Module does not exist.',
'log_plugin_not_exist' => 'Plugin does not exist.',
'instructions' => 'Instructions',
'installation' => 'Installation',
'installation_installation' => '
	You have a couple options when it comes to installation. Both have their pros and cons depending on your needs so 
	choose the option that works best for your situation. <br /><br /><strong>Pseudo Cron</strong><br />
	To enable pseudo cron you have to include the below template tag within a template that gets executed frequently 
	(preferably at the bottom of the HTML page like a footer template). Using the below will insert an image bug that will check for 
	crons and run any if needed. <br /><br />
	<code>{exp:automatee:pseudo_cron}</code><br /><br />
	Note that using the above requires latitude between when a Cron is scheduled and when it is expected to run. 
	If pin point accuracy is required then use the below option.<br /><br />
	<strong>True Cron</strong><br />
	This is the ideal installation method. To enable just set up a Cron job at the server level to execute the below command:
	<br /><br />
	<code>wget "##cron_url##" >/dev/null 2>&1 </code><br /><br />
	True Cron would be the ideal installation for when accuracy on execution matters and you have the technical know how to get it set up. 
	
',
'adding_crons' => 'Adding Crons',

'cron_delete_question' => 'Are you sure you want to delete the below Cron jobs?',
'module_instructions' => 'Automatee is an interface to automate your ExpressionEngine site. With Automatee you can set your installed plugins and modules to execute on a schedule as well as create standard Cron routines like shell commands and URL requests all using the standard Cron syntax and your ExpressionEngine administration panel. ',

'debug_settings' => 'Debug Settings',
'enable_debug' => 'Enable Debug',
'enable_debug_instructions' => 'When enabled debug mode will send email notifications if any issue is detected for a Cron. Depending on how your Crons are scheduled you may recieve a LOT of email.',

'debug_email' => 'Debug Email',
'debug_email_instructions' => 'The email address you want notifications sent to.',

'status_url_response_500' => '500 Server Failure',
'status_url_response_404' => '404 Page Not Found',
'status_url_response_0' => 'Server Not Found; Check URL',
'log_module_method_not_exist' => 'Module Method does not exist',
'log_calc_last_run_fail' => "Unable to calculate LastRan for Cron '#cron_name# (#cron_id#)'. Attempting to fix automagically...",
'log_last_run_fail' => "'#cron_name# (#cron_id#)' failed to complete on the previous run through. Running now...",

'log_email_subject' => 'An Automat:ee Error Happened',
'log_email_message' => "An error happened processing Cron '#cron_name# (#cron_id#)'. The specific issue was '#error_issue#' Below is any output that may be available (if any).\n\n------------------------------------\n\n",


'log_cron_start' => 'Cron Started',
'log_cron_sucess' => 'Cron Completed Successfully',
''=>''
);