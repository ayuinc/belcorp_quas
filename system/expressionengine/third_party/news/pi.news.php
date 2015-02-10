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
	    $preferences = $this->get_user_preferences();
	    $preferences = explode(',', $preferences);
	    
	    $q_categories_names = ee()->db->select('cat_name')->where_in('cat_id', $preferences)->get('exp_categories');
	    $categories_names = array();
	    
	    foreach($q_categories_names->result() as $row) {
		    array_push($categories_names, $row->cat_name);
	    }
	    			    
	    $q_entries_id = ee()->db->where_in('cat_id', $preferences)->get('exp_category_posts');
	    $entries_id = array();
	    foreach ($q_entries_id->result() as $row)
		{
			array_push($entries_id, $row->entry_id);
		}
		
		print_r($categories_names);
				
		$q_entries_data = ee()->db
								->select('exp_channel_data.entry_id, title, field_id_85, field_id_86')
								->join('exp_channel_titles', 'exp_channel_data.entry_id = exp_channel_titles.entry_id')
								->where_in('exp_channel_data.entry_id', $entries_id)
								->or_where_in('exp_channel_data.field_id_86', $categories_names)
								->get('exp_channel_data');
				
		$variables = array();

		foreach ($q_entries_data->result() as $row)
		{	
		    $variable_row = array(
		        'title'  => $row->title,
		        'noticias_url'    => $row->field_id_85,
		        'noticias_categoria_principal' => $row->field_id_86,
		        'noticias_otras_categorias' => $this->get_other_tags($row->entry_id)
		    );
		
		    $variables[] = $variable_row;
		}
		
		print_r($variables);
		
		return ee()->TMPL->parse_variables(ee()->TMPL->tagdata, $variables);
    }
    
    public function entries_by_vp() {
	    
    }
    
    public function all_entries() {
	    
    }
    
    private function get_other_tags($entry_id) {
	    $q_categories_names = ee()->db
	    							->select('cat_name')
									->join('exp_categories', 'exp_categories.cat_id = exp_category_posts.cat_id')
									->where('exp_category_posts.entry_id', $entry_id)
									->get('exp_category_posts');
	    
		$cats = array();
	    foreach($q_categories_names->result() as $row) {
		    $cats[] = $row->cat_name;
	    }
	    
	    return $cats;
    }
    
    private function get_user_preferences() {
	    $member_id = ee()->session->userdata('member_id');
	    $query_preferences = ee()->db
	    							->select('m_field_id_2')
	    							->get_where('exp_member_data', array('member_id' => $member_id));
        $preferences = $query_preferences->result();
        $res = $preferences[0]->m_field_id_2;
        
        return $res;
    }
    
    private function get_country() {
	    
    }
    
    private function get_vp() {
	    
    }
}
