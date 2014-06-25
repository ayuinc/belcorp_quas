<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Memberlist Class
 *
 * @package     ExpressionEngine
 * @category    Plugin
 * @author      Ricardo Díaz
 * @copyright   Copyright (c) 2014, Ricardo Díaz
 * @link        http://www.ayuinc.com/
 */

$plugin_info = array(
    'pi_name'         => 'Transfer',
    'pi_version'      => '1.0',
    'pi_author'       => 'Ricardo Díaz',
    'pi_author_url'   => 'http://www.ayuinc.com/',
    'pi_description'  => 'Allows states qualify Friends',
    'pi_usage'        => Transfer::usage()
);
            
class Transfer 
{

    var $return_data = "";
    // --------------------------------------------------------------------

        /**
         * Memberlist
         *
         * This function returns a list of members
         *
         * @access  public
         * @return  string
         */
    public function __construct(){
        $this->EE =& get_instance();
    }

    // --------------------------------------------------------------------

    /**
     * Usage
     *
     * This function describes how the plugin is used.
     *
     * @access  public
     * @return  string
     */
    public static function usage()
    {
        ob_start();  ?>
        The Memberlist Plugin simply outputs a
        list of 15 members of your site.

            {exp:transfer}

        This is an incredibly simple Plugin.
            <?php
        $buffer = ob_get_contents();
        ob_end_clean();
        return $buffer;
    }
    // END

    public function datatransf(){
        $query_member_group = ee()->db->select('group_id')->get_where('exp_member_groups', array('group_title' => 'Members'));
        $member_group = $query_member_group->result();
        $group_id = $member_group[0]->group_id;
        
        $str = "";
        ee()->db->select('*');
        $query = ee()->db->get('exp_usuarios');

        ee()->db->delete('exp_members', array('group_id' => $group_id));
        echo ee()->db->affected_rows()." rows were deleted.";

        ee()->load->library('auth');

        foreach($query->result() as $row) {
            ee()->extensions->call('member_member_register_start');
            if (ee()->extensions->end_script === TRUE) return;

            $hashed_password = ee()->auth->hash_password($row->DNI);

            $data = array(
                'username'      => trim_nbs($row->UsuarioRed),
                'password'      => $hashed_password['password'],
                'salt'          => $hashed_password['salt'],
                'crypt_key'     => ee()->functions->random('encrypt', 16),
                'ip_address'    => '127.0.0.1',
                'unique_id'     => ee()->functions->random('encrypt'),
                'join_date'     => ee()->localize->now,
                'email'         => trim_nbs($row->CorreoBelcorp),
                'screen_name'   => trim_nbs($row->Nombres . " " . $row->Apellidos),
                'group_id'      => $group_id,

                // overridden below if used as optional fields
                'language'      => (ee()->config->item('deft_lang')) ? 
                                        ee()->config->item('deft_lang') : 'english',
                'time_format'   => (ee()->config->item('time_format')) ? 
                                        ee()->config->item('time_format') : 'us',
                'timezone'      => ee()->config->item('default_site_timezone')
            );

            // Insert basic member data
            ee()->db->query(ee()->db->insert_string('exp_members', $data));

            $member_id = ee()->db->insert_id();

            ee()->extensions->call('member_member_register', $data, $member_id);
            if (ee()->extensions->end_script === TRUE) return;

            // Insert custom fields
            $cust_fields['member_id'] = $member_id;

            ee()->db->query(ee()->db->insert_string('exp_member_data', $cust_fields));

            // Create a record in the member homepage table
            // This is only necessary if the user gains CP access, 
            // but we'll add the record anyway.

            ee()->db->query(ee()->db->insert_string('exp_member_homepage', 
                                    array('member_id' => $member_id)));

        }
    }
} 
/* End of file pi.rating.php */
/* Location: ./system/expressionengine/third_party/rating/pi.rating.php */
