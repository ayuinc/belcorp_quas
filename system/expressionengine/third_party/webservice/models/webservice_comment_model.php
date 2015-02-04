<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Comment Model
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

class Webservice_comment_model
{

	public function __construct(){}

	// ----------------------------------------------------------------------
	
	// --------------------------------------------------------------------

	/**
	 * Insert New Comment
	 *
	 * @access	public
	 * @return	string
	 */
	function insert_new_comment($insert_data)
	{
		$default = array('member_id', 'name', 'email', 'url', 'comment', 'location', 'entry_id');

		foreach ($default as $val)
		{
			if ( ! isset($insert_data[$val]))
			{
				$insert_data[$val] = '';
			}
		}

		// No entry ID?  What the heck are they doing?
		if ( ! is_numeric($insert_data['entry_id']))
		{
			return FALSE;
		}

		/** ----------------------------------------
		/**  Fetch the comment language pack
		/** ----------------------------------------*/

		ee()->lang->loadfile('comment');

		//  No comment- let's end it here
		if (trim($insert_data['comment']) == '')
		{
			$error = ee()->lang->line('cmt_missing_comment');
			return ee()->output->show_user_error('submission', $error);
		}
		
		

		/** ----------------------------------------
		/**  Is the user banned?
		/** ----------------------------------------*/

		if (ee()->session->userdata['is_banned'] == TRUE)
		{
			return ee()->output->show_user_error('general', array(ee()->lang->line('not_authorized')));
		}

		/** ----------------------------------------
		/**  Is the IP address and User Agent required?
		/** ----------------------------------------*/

		if (ee()->config->item('require_ip_for_posting') == 'y')
		{
			if (ee()->input->ip_address() == '0.0.0.0' OR ee()->session->userdata['user_agent'] == "")
			{
				return ee()->output->show_user_error('general', array(ee()->lang->line('not_authorized')));
			}
		}

		/** ----------------------------------------
		/**  Is the nation of the user banend?
		/** ----------------------------------------*/
		ee()->session->nation_ban_check();

		/** ----------------------------------------
		/**  Can the user post comments?
		/** ----------------------------------------*/

		if (ee()->session->userdata['can_post_comments'] == 'n')
		{
			$error[] = ee()->lang->line('cmt_no_authorized_for_comments');

			return ee()->output->show_user_error('general', $error);
		}

		/** ----------------------------------------
		/**  Blacklist/Whitelist Check
		/** ----------------------------------------*/

		if (ee()->blacklist->blacklisted == 'y' && ee()->blacklist->whitelisted == 'n')
		{
			return ee()->output->show_user_error('general', array(ee()->lang->line('not_authorized')));
		}


		// -------------------------------------------
		// 'insert_comment_start' hook.
		//  - Allows complete rewrite of comment submission routine.
		//  - Or could be used to modify the POST data before processing
		//
			ee()->extensions->call('insert_comment_start');
			if (ee()->extensions->end_script === TRUE) return;
		//
		// -------------------------------------------

		/** ----------------------------------------
		/**  Fetch channel preferences
		/** ----------------------------------------*/

// Bummer, saw the hook after converting the query
/*
		ee()->db->select('channel_titles.title, channel_titles.url_title, channel_titles.channel_id, channel_titles.author_id,
						channel_titles.comment_total, channel_titles.allow_comments, channel_titles.entry_date, channel_titles.comment_expiration_date,
						channels.channel_title, channels.comment_system_enabled, channels.comment_max_chars, channels.comment_use_captcha,
						channels.comment_timelock, channels.comment_require_membership, channels.comment_moderate, channels.comment_require_email,
						channels.comment_notify, channels.comment_notify_authors, channels.comment_notify_emails, channels.comment_expiration'
		);

		ee()->db->from(array('channel_titles', 'channels'));
		ee()->db->where('channel_titles.channel_id = channels.channel_id');
		ee()->db->where('channel_titles.entry_id', $insert_data['entry_id']);
		ee()->db->where('channel_titles.status', 'closed');
*/
		$sql = "SELECT exp_channel_titles.title,
				exp_channel_titles.url_title,
				exp_channel_titles.entry_id,
				exp_channel_titles.channel_id,
				exp_channel_titles.author_id,
				exp_channel_titles.comment_total,
				exp_channel_titles.allow_comments,
				exp_channel_titles.entry_date,
				exp_channel_titles.comment_expiration_date,
				exp_channels.channel_title,
				exp_channels.comment_system_enabled,
				exp_channels.comment_max_chars,
				exp_channels.comment_use_captcha,
				exp_channels.comment_timelock,
				exp_channels.comment_require_membership,
				exp_channels.comment_moderate,
				exp_channels.comment_require_email,
				exp_channels.comment_notify,
				exp_channels.comment_notify_authors,
				exp_channels.comment_notify_emails,
				exp_channels.comment_expiration,
				exp_channels.channel_url,
				exp_channels.comment_url,
				exp_channels.site_id
			FROM	exp_channel_titles, exp_channels
			WHERE	exp_channel_titles.channel_id = exp_channels.channel_id
			AND	exp_channel_titles.entry_id = '".ee()->db->escape_str($insert_data['entry_id'])."'";

				//  Added entry_status param, so it is possible to post to closed title
				//AND	exp_channel_titles.status != 'closed' ";

		// -------------------------------------------
		// 'insert_comment_preferences_sql' hook.
		//  - Rewrite or add to the comment preference sql query
		//  - Could be handy for comment/channel restrictions
		//
			if (ee()->extensions->active_hook('insert_comment_preferences_sql') === TRUE)
			{
				$sql = ee()->extensions->call('insert_comment_preferences_sql', $sql);
				if (ee()->extensions->end_script === TRUE) return;
			}
		//
		// -------------------------------------------

		$query = ee()->db->query($sql);

		unset($sql);


		if ($query->num_rows() == 0)
		{
			return FALSE;
		}

		/** ----------------------------------------
		/**  Are comments allowed?
		/** ----------------------------------------*/
		if ($query->row('allow_comments')  == 'n' OR $query->row('comment_system_enabled')  == 'n')
		{
			return ee()->output->show_user_error('submission', ee()->lang->line('cmt_comments_not_allowed'));
		}

		/** ----------------------------------------
		/**  Has commenting expired?
		/** ----------------------------------------*/

		$force_moderation = $query->row('comment_moderate');

		if ($this->comment_expiration_mode == 0)
		{
			if ($query->row('comment_expiration_date')  > 0)
			{
				if (ee()->localize->now > $query->row('comment_expiration_date') )
				{
					if (ee()->config->item('comment_moderation_override') == 'y')
					{
						$force_moderation = 'y';
					}
					else
					{
						return ee()->output->show_user_error('submission', ee()->lang->line('cmt_commenting_has_expired'));
					}
				}
			}
		}
		else
		{
			if ($query->row('comment_expiration') > 0)
			{
			 	$days = $query->row('entry_date') + ($query->row('comment_expiration') * 86400);

				if (ee()->localize->now > $days)
				{
					if (ee()->config->item('comment_moderation_override') == 'y')
					{
						$force_moderation = 'y';
					}
					else
					{
						return ee()->output->show_user_error('submission', ee()->lang->line('cmt_commenting_has_expired'));
					}
				}
			}
		}


		/** ----------------------------------------
		/**  Is there a comment timelock?
		/** ----------------------------------------*/
		if ($query->row('comment_timelock') != '' AND $query->row('comment_timelock') > 0)
		{
			if (ee()->session->userdata['group_id'] != 1)
			{
				$time = ee()->localize->now - $query->row('comment_timelock') ;

				ee()->db->where('comment_date >', $time);
				ee()->db->where('ip_address', ee()->input->ip_address());

				$result = ee()->db->count_all_results('comments');

				if ($result  > 0)
				{
					return ee()->output->show_user_error('submission', str_replace("%s", $query->row('comment_timelock') , ee()->lang->line('cmt_comments_timelock')));
				}
			}
		}

		/** ----------------------------------------
		/**  Do we allow duplicate data?
		/** ----------------------------------------*/
		if (ee()->config->item('deny_duplicate_data') == 'y')
		{
			if (ee()->session->userdata['group_id'] != 1)
			{
				ee()->db->where('comment', $insert_data['comment']);
				$result = ee()->db->count_all_results('comments');

				if ($result > 0)
				{
					return ee()->output->show_user_error('submission', ee()->lang->line('cmt_duplicate_comment_warning'));
				}
			}
		}


		/** ----------------------------------------
		/**  Assign data
		/** ----------------------------------------*/
		$author_id				= $query->row('author_id') ;
		$entry_title			= $query->row('title') ;
		$url_title				= $query->row('url_title') ;
		$channel_title		 	= $query->row('channel_title') ;
		$channel_id			  	= $query->row('channel_id') ;
		$comment_total	 	 	= $query->row('comment_total')  + 1;
		$require_membership 	= $query->row('comment_require_membership') ;
		$comment_moderate		= (ee()->session->userdata['group_id'] == 1 OR ee()->session->userdata['exclude_from_moderation'] == 'y') ? 'n' : $force_moderation;
		$author_notify			= $query->row('comment_notify_authors') ;

		$comment_url			= $query->row('comment_url');
		$channel_url			= $query->row('channel_url');
		$entry_id				= $query->row('entry_id');
		$comment_site_id		= $query->row('site_id');


		$notify_address = ($query->row('comment_notify')  == 'y' AND $query->row('comment_notify_emails')  != '') ? $query->row('comment_notify_emails')  : '';


		/** ----------------------------------------
		/**  Start error trapping
		/** ----------------------------------------*/

		$error = array();

		if (ee()->session->userdata('member_id') != 0)
		{
			// If the user is logged in we'll reassign the POST variables with the user data

			 $insert_data['name']		= (ee()->session->userdata['screen_name'] != '') ? ee()->session->userdata['screen_name'] : ee()->session->userdata['username'];
			 $insert_data['email']	=  ee()->session->userdata['email'];
			 $insert_data['url']		=  (is_null(ee()->session->userdata['url'])) ? '' : ee()->session->userdata['url'];
			 $insert_data['location']	=  (is_null(ee()->session->userdata['location'])) ? '' : ee()->session->userdata['location'];
		}

		/** ----------------------------------------
		/**  Is membership is required to post...
		/** ----------------------------------------*/

		if ($require_membership == 'y')
		{
			// Not logged in

			if (ee()->session->userdata('member_id') == 0)
			{
				return ee()->output->show_user_error('submission', ee()->lang->line('cmt_must_be_member'));
			}

			// Membership is pending

			if (ee()->session->userdata['group_id'] == 4)
			{
				return ee()->output->show_user_error('general', ee()->lang->line('cmt_account_not_active'));
			}

		}
		else
		{
			/** ----------------------------------------
			/**  Missing name?
			/** ----------------------------------------*/

			if (trim($insert_data['name']) == '')
			{
				$error[] = ee()->lang->line('cmt_missing_name');
			}

			/** -------------------------------------
			/**  Is name banned?
			/** -------------------------------------*/

			if (ee()->session->ban_check('screen_name', $insert_data['name']))
			{
				$error[] = ee()->lang->line('cmt_name_not_allowed');
			}

			// Let's make sure they aren't putting in funky html to bork our screens
			$insert_data['name'] = str_replace(array('<', '>'), array('&lt;', '&gt;'), $insert_data['name']);

			/** ----------------------------------------
			/**  Missing or invalid email address
			/** ----------------------------------------*/

			if ($query->row('comment_require_email')  == 'y')
			{
				ee()->load->helper('email');

				if ($insert_data['email'] == '')
				{
					$error[] = ee()->lang->line('cmt_missing_email');
				}
				elseif ( ! valid_email($insert_data['email']))
				{
					$error[] = ee()->lang->line('cmt_invalid_email');
				}
			}
		}

		/** -------------------------------------
		/**  Is email banned?
		/** -------------------------------------*/

		if ($insert_data['email'] != '')
		{
			if (ee()->session->ban_check('email', $insert_data['email']))
			{
				$error[] = ee()->lang->line('cmt_banned_email');
			}
		}

		/** ----------------------------------------
		/**  Is comment too big?
		/** ----------------------------------------*/

		if ($query->row('comment_max_chars')  != '' AND $query->row('comment_max_chars')  != 0)
		{
			if (strlen($insert_data['comment']) > $query->row('comment_max_chars') )
			{
				$str = str_replace("%n", strlen($insert_data['comment']), ee()->lang->line('cmt_too_large'));

				$str = str_replace("%x", $query->row('comment_max_chars') , $str);

				$error[] = $str;
			}
		}

		/** ----------------------------------------
		/**  Do we have errors to display?
		/** ----------------------------------------*/

		if (count($error) > 0)
		{
			return ee()->output->show_user_error('submission', $error);
		}

		/** ----------------------------------------
		/**  Do we require CAPTCHA?
		/** ----------------------------------------*/

		/*if ($query->row('comment_use_captcha')  == 'y')
		{
			if (ee()->config->item('captcha_require_members') == 'y'  OR  (ee()->config->item('captcha_require_members') == 'n' AND ee()->session->userdata('member_id') == 0))
			{
				if ( ! isset($insert_data['captcha']) OR $insert_data['captcha'] == '')
				{
					return ee()->output->show_user_error('submission', ee()->lang->line('captcha_required'));
				}
				else
				{
					ee()->db->where('word', $insert_data['captcha']);
					ee()->db->where('ip_address', ee()->input->ip_address());
					ee()->db->where('date > UNIX_TIMESTAMP()-7200', NULL, FALSE);

					$result = ee()->db->count_all_results('captcha');

					if ($result == 0)
					{
						return ee()->output->show_user_error('submission', ee()->lang->line('captcha_incorrect'));
					}

					// @TODO: AR
					ee()->db->query("DELETE FROM exp_captcha WHERE (word='".ee()->db->escape_str($insert_data['captcha'])."' AND ip_address = '".ee()->input->ip_address()."') OR date < UNIX_TIMESTAMP()-7200");
				}
			}
		}*/

		/** ----------------------------------------
		/**  Build the data array
		/** ----------------------------------------*/

		ee()->load->helper('url');

		$notify = (ee()->input->post('notify_me')) ? 'y' : 'n';

 		$cmtr_name	= ee()->input->post('name', TRUE);
 		$cmtr_email	= ee()->input->post('email');
 		$cmtr_loc	= ee()->input->post('location', TRUE);
 		$cmtr_url	= ee()->input->post('url', TRUE);
		$cmtr_url	= prep_url($cmtr_url);

		$data = array(
			'channel_id'	=> $channel_id,
			'entry_id'		=> $insert_data['entry_id'],
			'author_id'		=> ee()->session->userdata('member_id'),
			'name'			=> $cmtr_name,
			'email'			=> $cmtr_email,
			'url'			=> $cmtr_url,
			'location'		=> $cmtr_loc,
			'comment'		=> ee()->security->xss_clean($insert_data['comment']),
			'comment_date'	=> ee()->localize->now,
			'ip_address'	=> ee()->input->ip_address(),
			'status'		=> ($comment_moderate == 'y') ? 'p' : 'o',
			'site_id'		=> $comment_site_id
		);

		// -------------------------------------------
		// 'insert_comment_insert_array' hook.
		//  - Modify any of the soon to be inserted values
		//
			if (ee()->extensions->active_hook('insert_comment_insert_array') === TRUE)
			{
				$data = ee()->extensions->call('insert_comment_insert_array', $data);
				if (ee()->extensions->end_script === TRUE) return;
			}
		//
		// -------------------------------------------

		//$return_link = ( ! stristr($insert_data['RET'],'http://') && ! stristr($insert_data['RET'],'https://')) ? ee()->functions->create_url($insert_data['RET']) : $insert_data['RET'];

		// Secure Forms check
		/*if (ee()->security->secure_forms_check(ee()->input->post('XID')) == FALSE)
		{
			ee()->functions->redirect(stripslashes($return_link));
		}*/


		//  Insert data
		$sql = ee()->db->insert_string('exp_comments', $data);
		ee()->db->query($sql);
		$comment_id = ee()->db->insert_id();

		if ($notify == 'y')
		{
			ee()->load->library('subscription');
			ee()->subscription->init('comment', array('entry_id' => $entry_id), TRUE);

			if ($cmtr_id = ee()->session->userdata('member_id'))
			{
				ee()->subscription->subscribe($cmtr_id);
			}
			else
			{
				ee()->subscription->subscribe($cmtr_email);
			}
		}


		if ($comment_moderate == 'n')
		{
			/** ------------------------------------------------
			/**  Update comment total and "recent comment" date
			/** ------------------------------------------------*/

			ee()->db->set('comment_total', $comment_total);
			ee()->db->set('recent_comment_date', ee()->localize->now);
			ee()->db->where('entry_id', $insert_data['entry_id']);

			ee()->db->update('channel_titles');

			/** ----------------------------------------
			/**  Update member comment total and date
			/** ----------------------------------------*/

			if (ee()->session->userdata('member_id') != 0)
			{
				ee()->db->select('total_comments');
				ee()->db->where('member_id', ee()->session->userdata('member_id'));

				$query = ee()->db->get('members');

				ee()->db->set('total_comments', $query->row('total_comments') + 1);
				ee()->db->set('last_comment_date', ee()->localize->now);
				ee()->db->where('member_id', ee()->session->userdata('member_id'));

				ee()->db->update('members');
			}

			/** ----------------------------------------
			/**  Update comment stats
			/** ----------------------------------------*/

			ee()->stats->update_comment_stats($channel_id, ee()->localize->now);

			/** ----------------------------------------
			/**  Fetch email notification addresses
			/** ----------------------------------------*/

			ee()->load->library('subscription');
			ee()->subscription->init('comment', array('entry_id' => $entry_id), TRUE);

			// Remove the current user
			$ignore = (ee()->session->userdata('member_id') != 0) ? ee()->session->userdata('member_id') : ee()->input->post('email');

			// Grab them all
			$subscriptions = ee()->subscription->get_subscriptions($ignore);
			ee()->load->model('comment_model');
			$recipients = ee()->comment_model->fetch_email_recipients($insert_data['entry_id'], $subscriptions);
		}

		/** ----------------------------------------
		/**  Fetch Author Notification
		/** ----------------------------------------*/

		if ($author_notify == 'y')
		{
			ee()->db->select('email');
			ee()->db->where('member_id', $author_id);

			$result = ee()->db->get('members');

			$notify_address	.= ','.$result->row('email');
		}

		/** ----------------------------------------
		/**  Instantiate Typography class
		/** ----------------------------------------*/

		ee()->load->library('typography');
		ee()->typography->initialize(array(
			'parse_images'		=> FALSE,
			'allow_headings'	=> FALSE,
			'smileys'			=> FALSE,
			'word_censor'		=> (ee()->config->item('comment_word_censoring') == 'y') ? TRUE : FALSE)
		);

		$comment = ee()->security->xss_clean($insert_data['comment']);
		$comment = ee()->typography->parse_type(
			$comment,
			array(
				'text_format'	=> 'none',
				'html_format'	=> 'none',
				'auto_links'	=> 'n',
				'allow_img_url' => 'n'
			)
		);

		$path = ($comment_url == '') ? $channel_url : $comment_url;

		$comment_url_title_auto_path = reduce_double_slashes($path.'/'.$url_title);

		/** ----------------------------
		/**  Send admin notification
		/** ----------------------------*/

		if ($notify_address != '')
		{
			$cp_url = ee()->config->item('cp_url').'?S=0&D=cp&C=addons_modules&M=show_module_cp&module=comment';

			$swap = array(
				'name'				=> $cmtr_name,
				'name_of_commenter'	=> $cmtr_name,
				'email'				=> $cmtr_email,
				'url'				=> $cmtr_url,
				'location'			=> $cmtr_loc,
				'channel_name'		=> $channel_title,
				'entry_title'		=> $entry_title,
				'comment_id'		=> $comment_id,
				'comment'			=> $comment,
				'comment_url'		=> reduce_double_slashes(ee()->input->remove_session_id(ee()->functions->fetch_site_index().'/'.$insert_data['URI'])),
				'delete_link'		=> $cp_url.'&method=delete_comment_confirm&comment_id='.$comment_id,
				'approve_link'		=> $cp_url.'&method=change_comment_status&comment_id='.$comment_id.'&status=o',
				'close_link'		=> $cp_url.'&method=change_comment_status&comment_id='.$comment_id.'&status=c',
				'channel_id'		=> $channel_id,
				'entry_id'			=> $entry_id,
				'url_title'			=> $url_title,
				'comment_url_title_auto_path' => $comment_url_title_auto_path
			);

			$template = ee()->functions->fetch_email_template('admin_notify_comment');

			$email_tit = ee()->functions->var_swap($template['title'], $swap);
			$email_msg = ee()->functions->var_swap($template['data'], $swap);

			// We don't want to send an admin notification if the person
			// leaving the comment is an admin in the notification list
			// For added security, we only trust the post email if the
			// commenter is logged in.

			if (ee()->session->userdata('member_id') != 0 && $insert_data['email'] != '')
			{
				if (strpos($notify_address, $insert_data['email']) !== FALSE)
				{
					$notify_address = str_replace($insert_data['email'], '', $notify_address);
				}
			}

			// Remove multiple commas
			$notify_address = reduce_multiples($notify_address, ',', TRUE);

			if ($notify_address != '')
			{
				/** ----------------------------
				/**  Send email
				/** ----------------------------*/

				ee()->load->library('email');

				$replyto = ($data['email'] == '') ? ee()->config->item('webmaster_email') : $data['email'];

				$sent = array();

				// Load the text helper
				ee()->load->helper('text');

				foreach (explode(',', $notify_address) as $addy)
				{
					if (in_array($addy, $sent))
					{
						continue;
					}

					ee()->email->EE_initialize();
					ee()->email->wordwrap = false;
					ee()->email->from(ee()->config->item('webmaster_email'), ee()->config->item('webmaster_name'));
					ee()->email->to($addy);
					ee()->email->reply_to($replyto);
					ee()->email->subject($email_tit);
					ee()->email->message(entities_to_ascii($email_msg));
					ee()->email->send();

					$sent[] = $addy;
				}
			}
		}


		/** ----------------------------------------
		/**  Send user notifications
		/** ----------------------------------------*/

		if ($comment_moderate == 'n')
		{
			$email_msg = '';

			if (count($recipients) > 0)
			{
				$action_id  = ee()->functions->fetch_action_id('Comment_mcp', 'delete_comment_notification');

				$swap = array(
					'name_of_commenter'	=> $cmtr_name,
					'channel_name'		=> $channel_title,
					'entry_title'		=> $entry_title,
					'site_name'			=> stripslashes(ee()->config->item('site_name')),
					'site_url'			=> ee()->config->item('site_url'),
					'comment_url'		=> reduce_double_slashes(ee()->input->remove_session_id(ee()->functions->fetch_site_index().'/'.$insert_data['URI'])),
					'comment_id'		=> $comment_id,
					'comment'			=> $comment,
					'channel_id'		=> $channel_id,
					'entry_id'			=> $entry_id,
					'url_title'			=> $url_title,
					'comment_url_title_auto_path' => $comment_url_title_auto_path
				);


				$template = ee()->functions->fetch_email_template('comment_notification');
				$email_tit = ee()->functions->var_swap($template['title'], $swap);
				$email_msg = ee()->functions->var_swap($template['data'], $swap);

				/** ----------------------------
				/**  Send email
				/** ----------------------------*/

				ee()->load->library('email');
				ee()->email->wordwrap = true;

				$cur_email = ($insert_data['email'] == '') ? FALSE : $insert_data['email'];

				if ( ! isset($sent)) $sent = array();

				// Load the text helper
				ee()->load->helper('text');

				foreach ($recipients as $val)
				{
					// We don't notify the person currently commenting.  That would be silly.

					if ( ! in_array($val['0'], $sent))
					{
						$title	 = $email_tit;
						$message = $email_msg;

						$sub	= $subscriptions[$val['1']];
						$sub_qs	= 'id='.$sub['subscription_id'].'&hash='.$sub['hash'];

						// Deprecate the {name} variable at some point
						$title	 = str_replace('{name}', $val['2'], $title);
						$message = str_replace('{name}', $val['2'], $message);

						$title	 = str_replace('{name_of_recipient}', $val['2'], $title);
						$message = str_replace('{name_of_recipient}', $val['2'], $message);

						$title	 = str_replace('{notification_removal_url}', ee()->functions->fetch_site_index(0, 0).QUERY_MARKER.'ACT='.$action_id.'&'.$sub_qs, $title);
						$message = str_replace('{notification_removal_url}', ee()->functions->fetch_site_index(0, 0).QUERY_MARKER.'ACT='.$action_id.'&'.$sub_qs, $message);

						ee()->email->EE_initialize();
						ee()->email->from(ee()->config->item('webmaster_email'), ee()->config->item('webmaster_name'));
						ee()->email->to($val['0']);
						ee()->email->subject($title);
						ee()->email->message(entities_to_ascii($message));
						ee()->email->send();

						$sent[] = $val['0'];
					}
				}
			}

			/** ----------------------------------------
			/**  Clear cache files
			/** ----------------------------------------*/

			ee()->functions->clear_caching('all', ee()->functions->fetch_site_index().$insert_data['URI']);

			// clear out the entry_id version if the url_title is in the URI, and vice versa
			if (preg_match("#\/".preg_quote($url_title)."\/#", $insert_data['URI'], $matches))
			{
				ee()->functions->clear_caching('all', ee()->functions->fetch_site_index().preg_replace("#".preg_quote($matches['0'])."#", "/{$data['entry_id']}/", $insert_data['URI']));
			}
			else
			{
				ee()->functions->clear_caching('all', ee()->functions->fetch_site_index().preg_replace("#{$data['entry_id']}#", $url_title, $insert_data['URI']));
			}
		}

		/** ----------------------------------------
		/**  Set cookies
		/** ----------------------------------------*/

		if ($notify == 'y')
		{
			ee()->functions->set_cookie('notify_me', 'yes', 60*60*24*365);
		}
		else
		{
			ee()->functions->set_cookie('notify_me', 'no', 60*60*24*365);
		}

		if (ee()->input->post('save_info'))
		{
			ee()->functions->set_cookie('save_info',	'yes',				60*60*24*365);
			ee()->functions->set_cookie('my_name',		$insert_data['name'],		60*60*24*365);
			ee()->functions->set_cookie('my_email',	$insert_data['email'],	60*60*24*365);
			ee()->functions->set_cookie('my_url',		$insert_data['url'],		60*60*24*365);
			ee()->functions->set_cookie('my_location',	$insert_data['location'],	60*60*24*365);
		}
		else
		{
			ee()->functions->set_cookie('save_info',	'no', 60*60*24*365);
			ee()->functions->set_cookie('my_name',		'');
			ee()->functions->set_cookie('my_email',	'');
			ee()->functions->set_cookie('my_url',		'');
			ee()->functions->set_cookie('my_location',	'');
		}

		// -------------------------------------------
		// 'insert_comment_end' hook.
		//  - More emails, more processing, different redirect
		//  - $comment_id added in 1.6.1
		//
			ee()->extensions->call('insert_comment_end', $data, $comment_moderate, $comment_id);
			if (ee()->extensions->end_script === TRUE) return;
		//
		// -------------------------------------------

		/** -------------------------------------------
		/**  Bounce user back to the comment page
		/** -------------------------------------------*/

		if ($comment_moderate == 'y')
		{
			$data = array(
				'title' 	=> ee()->lang->line('cmt_comment_accepted'),
				'heading'	=> ee()->lang->line('thank_you'),
				'content'	=> ee()->lang->line('cmt_will_be_reviewed'),
				'redirect'	=> $return_link,
				'link'		=> array($return_link, ee()->lang->line('cmt_return_to_comments')),
				'rate'		=> 3
			);

			ee()->output->show_message($data);
		}
		else
		{
			ee()->functions->redirect($return_link);
		}
	}


	// ----------------------------------------------------------------------

} // END CLASS

/* End of file default_model.php  */
/* Location: ./system/expressionengine/third_party/default/models/default_model.php */