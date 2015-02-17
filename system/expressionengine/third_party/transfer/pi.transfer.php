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
        $query_member_group = ee()->db
        							->select('group_id')
        							->get_where('exp_member_groups', array('group_title' => 'Members'));
        							
        $member_group = $query_member_group->result();
        $group_id = $member_group[0]->group_id;

		$query_member_ids = ee()->db
								->select('member_id')
								->get_where('exp_members', array('group_id' => $group_id));
		$member_ids = array();
		foreach($query_member_ids->result() as $row) {
			array_push($member_ids, $row->member_id);
		}

		ee()->db->where_in('member_id', $member_ids)->delete('exp_member_data');
		echo ee()->db->affected_rows()." rows were deleted from exp_member_data.\n";
		
        ee()->db->delete('exp_members', array('group_id' => $group_id));
		echo ee()->db->affected_rows()." rows were deleted from exp_members.\n";

        ee()->load->library('auth');
        $query = ee()->db->get('exp_usuarios');

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

			// Define if the user belongs to CORPORACION or PAIS
			$pub = $row->PaisGasto == 'Corporación' ? $row->PaisGasto : 'País';
			$vp = str_replace("Vicepresidente", "", $row->Vicepresidencia);
			$vp = utf8_decode(trim($vp));
			
			if(is_null($vp) || $vp == 'Error silla 2451' || $vp == 'Gerente en Entrenamiento') {
				$vp = 'None';
			}
			
			if(is_null($row->PaisSociedad)) {
				$country = 'None';
			} else {
				$country = $row->PaisSociedad;
			}
			
			if(is_null($row->GrupoFuncional)) {
				$fg = 'None';
			} else {
				if(in_array($row->GrupoFuncional, array('Adm', 'Adm FFVV', 'Adm Planta', 'Adm Retail'))) {
					$fg = 'Administrativos';
				}
				
				if($row->GrupoFuncional == 'FFVV') { $fg = 'Fuerza de Ventas'; }
				if($row->GrupoFuncional == 'Planta') { $fg = 'Operarios'; }
				if($row->GrupoFuncional == 'Retail') { $fg = 'Retail'; }
			}
			
			
			// Remove accents
			/* $vp = $this->transliterateString($vp); */
			
            // Insert custom fields
            $cust_fields = array(
            				'member_id' => $member_id,
            				'm_field_id_1' => $vp,
            				'm_field_id_3' => $country,
            				'm_field_id_4' => $pub,
            				'm_field_id_5' => $fg
            			   );

            ee()->db->query(ee()->db->insert_string('exp_member_data', $cust_fields));

            // Create a record in the member homepage table
            // This is only necessary if the user gains CP access, 
            // but we'll add the record anyway.

            ee()->db->query(ee()->db->insert_string('exp_member_homepage', 
                                    array('member_id' => $member_id)));

        }
        
        print_r($this->transliterateString('aéíó'));
    }
    
    private function transliterateString($txt) {
	    $transliterationTable = array('á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u');
	    return str_replace(array_keys($transliterationTable), array_values($transliterationTable), $txt);
	}
} 
/* End of file pi.rating.php */
/* Location: ./system/expressionengine/third_party/rating/pi.rating.php */
