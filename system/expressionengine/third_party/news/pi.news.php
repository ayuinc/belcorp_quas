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
    'pi_description'  => 'News module for Belcorp LMS',
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
        Use
            {exp:news:entries_by_preferences}
            	{vars}
            {/exp:news:entries_by_preferences}
            <?php
        $buffer = ob_get_contents();
        ob_end_clean();
        return $buffer;
    }
    // END
    
    public function entries_by_preferences() {
    	$member_fields = $this->get_member_fields();
    	$limit = ee()->TMPL->fetch_param('limit');
	    
	    $entries_by_country = array();
	    $entries_by_prefs = array();
	    $entries_by_vp = array();
	    $entries_by_user_type = array();
	    $entries_by_f_group = array();
	    
	    if(isset($member_fields['preferences'])) {
		    $preferences = explode(',', $member_fields['preferences']);
			$entries_by_prefs = $this->get_entries_by_preferences($preferences);
	    }
	    
	    if(!is_null($member_fields['country'])) {
		    $country = $this->get_country_id($member_fields['country']);
			$entries_by_country = $this->get_entries_by_country($country);
	    }
	    
	    if(!is_null($member_fields['vp']) || $member_fields['country'] == 'None') {
		    $vp = $this->get_vp_id($member_fields['vp']);
		    $entries_by_vp = $this->get_entries_by_vp($vp);
	    }
	    
	    if(isset($member_fields['type'])) {
		  	$user_type = $this->get_user_type_id($member_fields['type']);
		  	$entries_by_user_type = $this->get_entries_by_user_type($user_type);
	    }
	    
	    if(isset($member_fields['f_group'])) {
		    $f_group = $this->get_functional_group($member_fields['f_group']);
		    $entries_by_f_group = $this->get_entries_by_functional_group($f_group);
	    }
	    
	    $entries_id = array_intersect(
	    				$entries_by_country,
	    				$entries_by_prefs,
	    				$entries_by_vp,
	    				$entries_by_user_type,
	    				$entries_by_f_group
	    			);
	    
	    $variables = array();
	    
	    if(!empty($entries_id)) {
		    $q_entries_data = ee()->db
									->select('exp_channel_data.entry_id, title, field_id_85, field_id_86, field_id_89, field_id_88')
									->join('exp_channel_titles', 'exp_channel_data.entry_id = exp_channel_titles.entry_id')
									->where_in('exp_channel_data.entry_id', $entries_id)
									->order_by("entry_id", "desc")
									->limit($limit)
									->get('exp_channel_data');
	
			foreach ($q_entries_data->result() as $row) {	
			    $variable_row = array(
			    	'noticias_entry_id' => $row->entry_id,
			        'title'  => $row->title,
			        'noticias_url'    => $row->field_id_85,
			        'noticias_categoria_principal' => $row->field_id_86,
			        'noticias_otras_categorias' => $this->get_other_tags($row->entry_id),
			        'noticias_titulo' => $row->field_id_89,
			        'noticias_imagen' => $row->field_id_88
			    );
			
			    $variables[] = $variable_row;
			}
	    }
		
		return ee()->TMPL->parse_variables(ee()->TMPL->tagdata, $variables);
    }
    
    public function entries_by_vp() {
	    $member_fields = $this->get_member_fields();
	    $limit = ee()->TMPL->fetch_param('limit');
	    
	    $entries_by_country = array();
	    // $entries_by_prefs = array();
	    $entries_by_vp = array();
	    $entries_by_user_type = array();
	    $entries_by_f_group = array();
	    
	    // if(isset($member_fields['preferences'])) {
		//     $preferences = explode(',', $member_fields['preferences']);
		//     $entries_by_prefs = $this->get_entries_by_preferences($preferences);
	    // }
	    
	    if(!is_null($member_fields['country'])) {
		    $country = $this->get_country_id($member_fields['country']);
			$entries_by_country = $this->get_entries_by_country($country);
	    }
	    
	    if(!is_null($member_fields['vp']) || $member_fields['country'] == 'None') {
		    $vp = $this->get_vp_id($member_fields['vp']);
		    $entries_by_vp = $this->get_entries_by_vp($vp);
	    }
	    
	    if(isset($member_fields['type'])) {
		  	$user_type = $this->get_user_type_id($member_fields['type']);
		  	$entries_by_user_type = $this->get_entries_by_user_type($user_type);
	    }
	    
	    if(isset($member_fields['f_group'])) {
		    $f_group = $this->get_functional_group($member_fields['f_group']);
		    $entries_by_f_group = $this->get_entries_by_functional_group($f_group);
	    }
	    
	    $entries_id = array_intersect(
	    				$entries_by_country,
	    				// $entries_by_prefs,
	    				$entries_by_vp,
	    				$entries_by_user_type,
	    				$entries_by_f_group
	    			);
	    
	    $variables = array();
	    
	    if(!empty($entries_id)) {
		    $q_entries_data = ee()->db
									->select('exp_channel_data.entry_id, title, field_id_85, field_id_86, field_id_89, field_id_88')
									->join('exp_channel_titles', 'exp_channel_data.entry_id = exp_channel_titles.entry_id')
									->where_in('exp_channel_data.entry_id', $entries_id)
									->order_by("entry_id", "desc")
									->limit($limit)
									->get('exp_channel_data');
	
			foreach ($q_entries_data->result() as $row) {	
			    $variable_row = array(
			    	'noticias_entry_id' => $row->entry_id,
			        'title'  => $row->title,
			        'noticias_url'    => $row->field_id_85,
			        'noticias_categoria_principal' => $row->field_id_86,
			        'noticias_otras_categorias' => $this->get_other_tags($row->entry_id),
			        'noticias_titulo' => $row->field_id_89,
			        'noticias_imagen' => $row->field_id_88
			    );
			
			    $variables[] = $variable_row;
			}
	    }
		
		return ee()->TMPL->parse_variables(ee()->TMPL->tagdata, $variables);
    }
    
    public function all_entries() {
	    $member_fields = $this->get_member_fields();
	    $limit = ee()->TMPL->fetch_param('limit');
	    
	    $entries_by_country = array();
	    // $entries_by_prefs = array();
	    // $entries_by_vp = array();
	    // $entries_by_user_type = array();
	    // $entries_by_f_group = array();
	    
	    // if(isset($member_fields['preferences'])) {
		//     $preferences = explode(',', $member_fields['preferences']);
		//     $entries_by_prefs = $this->get_entries_by_preferences($preferences);
	    // }
	    
	    if(!is_null($member_fields['country'])) {
		    $country = $this->get_country_id($member_fields['country']);
			$entries_by_country = $this->get_entries_by_country($country);
	    }
	    
	    /* if(!is_null($member_fields['vp']) || $member_fields['country'] == 'None') {
		    $vp = $this->get_vp_id($member_fields['vp']);
		    $entries_by_vp = $this->get_entries_by_vp($vp);
	    }
	    
	    if(isset($member_fields['type'])) {
		  	$user_type = $this->get_user_type_id($member_fields['type']);
		  	$entries_by_user_type = $this->get_entries_by_user_type($user_type);
	    }
	    
	    if(isset($member_fields['f_group'])) {
		    $f_group = $this->get_functional_group($member_fields['f_group']);
		    $entries_by_f_group = $this->get_entries_by_functional_group($f_group);
	    } */
	    
	    /* $entries_id = array_intersect(
	    				$entries_by_country,
	    				// $entries_by_prefs,
	    				$entries_by_vp,
	    				$entries_by_user_type,
	    				$entries_by_f_group
	    			); */
	    $entries_id = $entries_by_country;
	    
	    $variables = array();
	    
	    if(!empty($entries_id)) {
		    $q_entries_data = ee()->db
									->select('exp_channel_data.entry_id, title, field_id_85, field_id_86, field_id_89, field_id_88')
									->join('exp_channel_titles', 'exp_channel_data.entry_id = exp_channel_titles.entry_id')
									->where_in('exp_channel_data.entry_id', $entries_id)
									->order_by("entry_id", "desc")
									->limit($limit)
									->get('exp_channel_data');
	
			foreach ($q_entries_data->result() as $row) {	
			    $variable_row = array(
			    	'noticias_entry_id' => $row->entry_id,
			        'title'  => $row->title,
			        'noticias_url'    => $row->field_id_85,
			        'noticias_categoria_principal' => $row->field_id_86,
			        'noticias_otras_categorias' => $this->get_other_tags($row->entry_id),
			        'noticias_titulo' => $row->field_id_89,
			        'noticias_imagen' => $row->field_id_88
			    );
			
			    $variables[] = $variable_row;
			}
	    }
		
		return ee()->TMPL->parse_variables(ee()->TMPL->tagdata, $variables);
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
    
    private function get_member_fields() {
	    $member_id = ee()->session->userdata('member_id');
	    $query_preferences = ee()->db->get_where('exp_member_data', array('member_id' => $member_id));
	    $row = $query_preferences->row();
	    
        return array(
        			'vp' => $row->m_field_id_1,
        			'preferences' => $row->m_field_id_2,
        			'country' => $row->m_field_id_3,
        			'type' => $row->m_field_id_4,
        			'f_group' => $row->m_field_id_5
        		);
    }
    
    private function get_country_id($country) {
	    $query = ee()->db
	    				->select('cat_id')
	    				->where('cat_name', $country)
	    				->where('group_id', 19)
	    				->get('exp_categories');
	    				
	    $row = $query->row();
	    return $row->cat_id;
    }
    
    private function get_vp_id($vp) {
	    $query = ee()->db
	    				->select('cat_id')
	    				->where('cat_name', $vp)
	    				->where('group_id', 20)
	    				->get('exp_categories');
	    				
	    $row = $query->row();
	    return $row->cat_id;
    }
    
    private function get_user_type_id($type) {
	    $query = ee()->db
	    				->select('cat_id')
	    				->where('cat_name', $type)
	    				->where('group_id', 21)
	    				->get('exp_categories');
	    				
	    $row = $query->row();
	    return $row->cat_id;
    }
    
    private function get_functional_group($group) {
	    $query = ee()->db
	    				->select('cat_id')
	    				->where('cat_name', $group)
	    				->where('group_id', 22)
	    				->get('exp_categories');
	    				
	    $row = $query->row();
	    return $row->cat_id;
    }
    
    private function get_entries_by_country($country) {
	    $query = ee()->db
	    				->select('entry_id')
	    				->where('cat_id', $country)
						->get('exp_category_posts');
		$arr = array();
		
		foreach($query->result() as $row) {
			array_push($arr, $row->entry_id);
		}

		return array_unique($arr);
    }
    
    private function get_entries_by_vp($vp) {
	    $query = ee()->db
	    				->select('entry_id')
	    				->where('cat_id', $vp)
						->get('exp_category_posts');
		$arr = array();
		
		foreach($query->result() as $row) {
			array_push($arr, $row->entry_id);
		}

		return array_unique($arr);
    }
    
    private function get_entries_by_user_type($type) {
	    $query = ee()->db
	    				->select('entry_id')
	    				->where('cat_id', $type)
						->get('exp_category_posts');
		$arr = array();
		
		foreach($query->result() as $row) {
			array_push($arr, $row->entry_id);
		}

		return array_unique($arr);
    }
    
    private function get_entries_by_functional_group($group) {
	    $query = ee()->db
	    				->select('entry_id')
	    				->where('cat_id', $group)
						->get('exp_category_posts');
		$arr = array();
		
		foreach($query->result() as $row) {
			array_push($arr, $row->entry_id);
		}

		return array_unique($arr);
    }
    
    private function get_entries_by_preferences($preferences) {
	    $q_tags = ee()->db
						->select('entry_id')
	    				->where_in('cat_id', $preferences)
	    				->get('exp_category_posts');
	    							    
	    $entries_tags = array();
	    foreach ($q_tags->result() as $row) {
			array_push($entries_tags, $row->entry_id);
		}
		
		$q_categories_names = ee()->db
	    							->select('cat_name')
	    							->where_in('cat_id', $preferences)
	    							->get('exp_categories');
	    $categories_names = array();
	    
	    foreach ($q_categories_names->result() as $row) {
		    array_push($categories_names, $row->cat_name);
	    }
	    
	    // var_dump($categories_names);
		
		$q_prefs = ee()->db
						->select('entry_id')
						->where_in('field_id_86', $categories_names)
						->get('exp_channel_data');
								
		$entries_prefs = array();
		foreach($q_prefs->result() as $row) {
			array_push($entries_prefs, $row->entry_id);
		}
		
		return array_unique(array_merge($entries_tags, $entries_prefs));
    }
}
