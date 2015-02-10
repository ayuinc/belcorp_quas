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
    'pi_name'         => 'News',
    'pi_version'      => '1.0',
    'pi_author'       => 'Ricardo Díaz',
    'pi_author_url'   => 'http://www.ayuinc.com/',
    'pi_description'  => 'Allows states qualify Friends',
    'pi_usage'        => News::usage()
);
            
class News 
{

    var $return_data = "";

    public function __construct(){
        $this->EE =& get_instance();
    }

    public static function usage()
    {
        ob_start();  ?>
            {exp:news}
            <?php
        $buffer = ob_get_contents();
        ob_end_clean();
        return $buffer;
    }
    // END
    
    public function entries_by_preferences() {
	    $preferences = $this->preferences();
	    $preferences = split(',', $preferences);
	    
	    $query_categories = ee()->db->select('cat_id')->get_where();
    }
    
    public function entries_by_vp() {
	    
    }
    
    public function all_entries() {
	    
    }
    
    public function preferences() {
	    $member_id = ee()->session->userdata('member_id');
	    $query_preferences = ee()->db->select('m_field_id_2')->get_where('exp_member_data', array('member_id' => $member_id));
        $preferences = $query_preferences->result();
        $res = $preferences[0]->m_field_id_2;
        
        return $res;
    }
}
