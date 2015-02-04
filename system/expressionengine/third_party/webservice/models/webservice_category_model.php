<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Category Model
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

class Webservice_category_model
{

	public function __construct(){}

	// ----------------------------------------------------------------------

	/**
	 * Update the category
	 *
	 * @param array $cat_ids
	 * @param int $entry_id
	 * @internal param $none
	 */
	public function update_category($cat_ids = array(), $entry_id = 0)
	{
		if(!empty($cat_ids) && $entry_id != 0 && $entry_id != '')
		{
			//remove all other referenties
			ee()->db->delete('category_posts', array(
				'entry_id' => (int) $entry_id
			));

			//insert the new record
			foreach($cat_ids as $cat_id)
			{
				//insert new record
				ee()->db->insert('category_posts', array(
					'cat_id' => (int) $cat_id,
					'entry_id' => (int) $entry_id
				));
			}
		}
	}

	// ----------------------------------------------------------------------
	
	/**
	 * Get the category
	 * 
	 * @param none
	 * @return void
	 */
	public function get_entry_categories($entry_ids, $return_ids = false)
	{
		$result = array();
		$entry_ids = (array) $entry_ids;

		if ( ! count($entry_ids) || $entry_ids == '' || $entry_ids == false)
		{
			return $result;
		}

		$sql = "SELECT c.cat_id as category_id, c.site_id as category_site_id, c.group_id as category_group_id, c.*, cp.entry_id, cg.field_html_formatting, fd.*
				FROM ".ee()->db->dbprefix."categories AS c
				LEFT JOIN ".ee()->db->dbprefix."category_posts AS cp ON c.cat_id = cp.cat_id
				LEFT JOIN ".ee()->db->dbprefix."category_field_data AS fd ON fd.cat_id = c.cat_id
				LEFT JOIN ".ee()->db->dbprefix."category_groups AS cg ON cg.group_id = c.group_id
				WHERE cp.entry_id IN (".implode(', ', $entry_ids).")
				ORDER BY c.group_id, c.parent_id, c.cat_order";

		$category_query = ee()->db->query($sql);

		if($category_query->num_rows > 0)
		{
			foreach ($category_query->result_array() as $row)
			{
				//how to format
				if($return_ids)
				{
					$result[] = $row['category_id'];
				}
				else
				{
					if ( ! isset($result[$row['entry_id']]))
					{
						$result[$row['entry_id']] = array();
					}

					$result[$row['entry_id']][] = $row;
				}
			}
		}

		if(count($entry_ids) == 1 && count($result) > 0 && isset($result[$entry_ids[0]]))
		{
			return $result[$entry_ids[0]];
		}

		return $result;
	}

	// ----------------------------------------------------------------------

} // END CLASS

/* End of file default_model.php  */
/* Location: ./system/expressionengine/third_party/default/models/default_model.php */