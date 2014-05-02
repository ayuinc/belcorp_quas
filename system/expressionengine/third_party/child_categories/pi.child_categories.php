<?php

/*
=====================================================

 The original ExpressionEngine 1 plugin was created by Laisvunas
 - http://expressionengine.com/forums/member/38398/
 
 The functionality to to display only child categories 
 with specific category custom field equal to 
 (or not equal to) specific value was developed by Baniaczek
 -http://expressionengine.com/forums/member/157849/
 
 The plugin was ported to ExpressionEngine 2 by Nico De Gols
 - http://www.pixelclub.be
 
=====================================================
 Licensed under Creative Commons Attribution-Share Alike 3.0 Unported licence
 - http://creativecommons.org/licenses/by-sa/3.0/
=====================================================
 File: pi.child_categories.php
-----------------------------------------------------
 Purpose: Find all child categories after specifying parent or
 sibling category. Display lists of entries posted to child
 categories.
=====================================================
*/

$plugin_info = array(
						'pi_name'			=> 'EE2 Child Categories',
						'pi_version'		=> '3',
						'pi_author'			=> 'Nico De Gols',
						'pi_author_url'		=> 'http://www.pixelclub.be',
						'pi_description'	=> 'Allows you to find all child categories after specifying parent or
 sibling category and display lists of entries posted to child categories.',
						'pi_usage'			=> child_categories::usage()
					);

class Child_categories {

  var $return_data = '';

  function Child_categories()
  {
  
  	$this->EE =& get_instance();
  	
    // Fetch the tagdata
	$tagdata = $this->EE->TMPL->tagdata;

    // Define variables
    $siteclause = '';
    $channelclause = '';
    $sortorderclause_parentcategories = '';
    $sortorderclause_categories = '';
    $sortorderclause_entries = '';
    $statusclause = '';
    $limitclause = '';
    $parent_cat_name = '';
    $parent_cat_url_title = '';
    $parent_cat_description = '';
    $parent_cat_image = '';
    $parent_cat_children_count = 0;
    $child_category_id = '';
    $entries_total = '';
    $child_category_name = '';
    $child_category_url_title = '';
    $child_category_order_num = '';
    $channel_name = '';
    $channel_url = '';
    $title = '';
    $url_title = '';
    $entry_id = '';
    $entry_date = '';
    $tagdatanew = '';
    $parentdatanew = '';
    $childdatanew = '';
    $entriesdatanew = '';
    $parentdataexists = '';
    $childdataexists = '';
    $entriesdataexists = '';
    $entrieswrappertop_dataexists = '';
    $entrieswrapperbottom_dataexists = '';
    $childwrapperbottom_dataexists = '';
    $parentwrapperbottom_dataexists = '';
    $entrieswrappertop_data = '';
    $entrieswrapperbottom_data = '';
    $childwrapperbottom_data = '';
    $parentwrapperbottom_data = '';
    $found_invalid = FALSE;
    $parentarray = array();
    $current_time=time();
    $fieldclause = '';																								// gd

		  // Fetch params
    $parent = $this->EE->TMPL->fetch_param('parent');
    $sibling = $this->EE->TMPL->fetch_param('sibling');
    $site = $this->EE->TMPL->fetch_param('site');
    $child_categories_sort_by = $this->EE->TMPL->fetch_param('child_categories_sort_by');
    $child_categories_sort_direction = $this->EE->TMPL->fetch_param('child_categories_sort_direction');
    $show_empty = $this->EE->TMPL->fetch_param('show_empty');
    $entries_sort_by = $this->EE->TMPL->fetch_param('entries_sort_by');
    $entries_sort_direction = $this->EE->TMPL->fetch_param('entries_sort_direction');
    $entries_limit = $this->EE->TMPL->fetch_param('entries_limit');
    $date_format = $this->EE->TMPL->fetch_param('date_format');
    $channel = $this->EE->TMPL->fetch_param('channel');
    $invalid_input = $this->EE->TMPL->fetch_param('invalid_input');
    $status = $this->EE->TMPL->fetch_param('status');
    $show_expired = $this->EE->TMPL->fetch_param('show_expired');
    $category_group = $this->EE->TMPL->fetch_param('category_group');
    $parent_categories_sort_by = $this->EE->TMPL->fetch_param('parent_categories_sort_by');
    $parent_categories_sort_direction = $this->EE->TMPL->fetch_param('parent_categories_sort_direction');
    $custom_field_name  = $this->EE->TMPL->fetch_param('custom_field_name');		// gd
    $custom_field_value = $this->EE->TMPL->fetch_param('custom_field_value');   // gd
    $custom_field       = $this->EE->TMPL->fetch_param('custom_field');         // gd

	
    // At least one out of "parent" and "sibling" parameters must be defined
    if ($parent === FALSE AND $sibling === FALSE AND $category_group === FALSE)
    {
      if ($invalid_input === 'alert')
      {
        echo 'Error! At least one out of "parent", "sibling" and "category_group" parameters of exp:child_categories tag must be defined.<br><br>';
      }
      $found_invalid = TRUE;
    }
    
    // in case "category_group" parameter contains category group name, find category group ID, if none found display error message
    if ($category_group !== FALSE AND !is_numeric($category_group))
    {
      $sql_group_id = "SELECT group_id FROM exp_category_groups WHERE group_name = '".$category_group."'";
      $query_group_id = $this->EE->db->query($sql_group_id);
      if ($query_group_id->num_rows() == 1)
      {
        $category_group = $query_group_id->row['group_id'];
      }
      else
      {
        if ($invalid_input === 'alert')
        {
          echo 'ERROR! Parameter "category_group" of "exp:child_categories" tag contains invalid category group name "'.$category_group.'".<br><br>';
        }
        $found_invalid = TRUE;
      }
    }

    // Parameter "child_categories_sort_by" accepts only "name", "id" or "custom" as its value
    if ($child_categories_sort_by !== FALSE AND $child_categories_sort_by !== 'name' AND $child_categories_sort_by !== 'id' AND $child_categories_sort_by !== 'custom')
    {
      if ($invalid_input === 'alert')
      {
        echo 'Error! Parameter "child_categories_sort_by" of exp:child_categories tag accepts only "name", "id" or "custom" as its value.<br><br>';
      }
      $found_invalid = TRUE;
    }

    // Parameter "child_categories_sort_direction" accepts only "asc" or "desc" as its value
    if ($child_categories_sort_direction !== FALSE AND $child_categories_sort_direction !== 'asc' AND $child_categories_sort_direction !== 'desc')
    {
      if ($invalid_input === 'alert')
      {
        echo 'Error! Parameter "child_categories_sort_direction" of exp:child_categories tag accepts only "asc" or "desc" as its value.<br><br>';
      }
      $found_invalid = TRUE;
    }

    // Parameter "show_empty" accepts only "yes" or "no" as its value
    if ($show_empty !== FALSE AND $show_empty !== 'yes' AND $show_empty !== 'no')
    {
      if ($invalid_input === 'alert')
      {
        echo 'Error! Parameter "show_empty" of exp:child_categories tag accepts only "yes" or "no" as its value.<br><br>';
      }
      $found_invalid = TRUE;
    }

    // Parameter "entries_sort_by" accepts only "date" or "title" as its value
    if ($entries_sort_by !== FALSE AND $entries_sort_by !== 'date' AND $entries_sort_by !== 'title')
    {
      if ($invalid_input === 'alert')
      {
        echo 'Error! Parameter "entries_sort_by" of exp:child_categories tag accepts only "date" or "title" as its value.<br><br>';
      }
      $found_invalid = TRUE;
    }

    // Parameter "entries_sort_direction" accepts only "asc" or "desc" as its value
    if ($entries_sort_direction !== FALSE AND $entries_sort_direction !== 'asc' AND $entries_sort_direction !== 'desc')
    {
      if ($invalid_input === 'alert')
      {
        echo 'Error! Parameter "entries_sort_direction" of exp:child_categories tag accepts only "asc" or "desc" as its value.<br><br>';
      }
      $found_invalid = TRUE;
    }

    if ($found_invalid === FALSE)
    {
      // Form channel clause
      if($channel !== FALSE)
      {
        // Clean whitespace from "channel" parameter value
        $channel = str_replace(' ', '', $channel);
        // Check if "channel" param contains "not"
        if (strpos($channel, 'not')===0)
        {
          // In case "channel" param contains "not" form SQL clause using "AND" and "!=" operators
          $channel = substr($channel, 3);
          $channel_shortnames_array = explode('|', $channel);
          foreach($channel_shortnames_array as $shortname)
          {
            $channelclause .= " AND exp_channels.channel_name!='".$shortname."' ";
          }
        }
        else
        {
          // In case "channel" param does not contain "not" form SQL clause using "OR" and "=" operators
          $channel_shortnames_array = explode('|', $channel);
          if (count($channel_shortnames_array)==1)
          {
            $channelclause = " AND exp_channels.channel_name='".$channel_shortnames_array[0]."' ";
          }
          else
          {
            foreach($channel_shortnames_array as $shortname)
            {
              $channelclause .= " OR exp_channels.channel_name='".$shortname."' ";
            }
            $channelclause = substr($channelclause, 4);
            $channelclause = " AND (".$channelclause.")";
          }
        }
      }
      //echo $channelclause.'<br>';

      // Form site clause
      if ($site !== FALSE)
      {
        $siteclause = " AND exp_categories.site_id='".$site."' ";
        //echo $siteclause.'<br>';
      }

      // Form child categories sort order clause
      if ($child_categories_sort_by === FALSE OR $child_categories_sort_by === 'name')
      {
        $sortorderclause_categories = ' ORDER BY cat_name ';
      }
      elseif ($child_categories_sort_by === 'id')
      {
        $sortorderclause_categories = ' ORDER BY cat_id ';
      }
      elseif ($child_categories_sort_by === 'custom')
      {
        $sortorderclause_categories = ' ORDER BY cat_order ';
      }
      if ($child_categories_sort_direction === FALSE OR $child_categories_sort_direction === 'asc')
      {
        $sortorderclause_categories .= ' ASC ';
      }
      elseif ($child_categories_sort_direction === 'desc')
      {
        $sortorderclause_categories .= ' DESC ';
      }
      //echo $sortorderclause_categories.'<br>';

      // Form parent categories sort order clause
      if ($parent_categories_sort_by === FALSE OR $parent_categories_sort_by === 'name')
      {
        $sortorderclause_parentcategories = ' ORDER BY cat_name ';
      }
      elseif ($parent_categories_sort_by === 'id')
      {
        $sortorderclause_parentcategories = ' ORDER BY cat_id ';
      }
      elseif ($parent_categories_sort_by === 'custom')
      {
        $sortorderclause_parentcategories = ' ORDER BY cat_order ';
      }
      if ($parent_categories_sort_direction === FALSE OR $parent_categories_sort_direction === 'asc')
      {
        $sortorderclause_parentcategories .= ' ASC ';
      }
      elseif ($parent_categories_sort_direction === 'desc')
      {
        $sortorderclause_parentcategories .= ' DESC ';
      }
      //echo $sortorderclause_parentcategories.'<br>';

      // Supply default value for child categories show_empty parameter
      if ($show_empty === FALSE)
      {
        $show_empty ='no';
      }

      // Form entries sort order clause
      if ($entries_sort_by === FALSE OR $entries_sort_by === 'date')
      {
        $sortorderclause_entries = ' ORDER BY exp_channel_titles.entry_date ';
      }
      elseif ($entries_sort_by === 'title')
      {
        $sortorderclause_entries = ' ORDER BY exp_channel_titles.title ';
      }
      if ($entries_sort_direction === FALSE OR $entries_sort_direction === 'desc')
      {
        $sortorderclause_entries .= ' DESC ';
      }
      elseif ($entries_sort_direction === 'asc')
      {
        $sortorderclause_entries .= ' ASC ';
      }
      //echo $sortorderclause_entries.'<br>';

      // Form status clause
      if ($status !== FALSE)
      {
        // Check if "status" param contains "not"
        if (strpos($status, 'not')===0)
        {
          // In case "status" param contains "not" form SQL clause using "AND" and "!=" operators
          $status = substr($status, 3);
          $statusarray = explode('|', $status);
          foreach($statusarray as $statusname)
          {
            $statusname = trim($statusname);
            $statusclause .= " AND exp_channel_titles.status!='".$statusname."' ";
          }
          //echo '$statusclause: '.$statusclause;
        }
        else
        {
          // In case "status" param does not contain "not" form SQL clause using "OR" and "=" operators
          $statusarray = explode('|', $status);
          if (count($statusarray)==1)
          {
            $statusclause = " AND exp_channel_titles.status='".$statusarray[0]."' ";
          }
          else
          {
            foreach($statusarray as $statusname)
            {
              $statusname = trim($statusname);
              $statusclause .= " OR exp_channel_titles.status='".$statusname."' ";
            }
            $statusclause = substr($statusclause, 4);
            $statusclause = " AND (".$statusclause.") ";
          }
        }
        //echo '$statusclause: '.$statusclause.'<br><br>';
      }

      // Form entries limit clause
      if ($entries_limit === FALSE)
      {
        $entries_limit = 10;
        $limitclause = ' LIMIT 0, 10';
      }
      elseif ($entries_limit === 'none')
      {
        $limitclause = '';
      }
      else
      {
        $limitclause = ' LIMIT 0, '.$entries_limit;
      }
      //echo $limitclause.'<br>';

      // Form default date format string
      if ($date_format === FALSE)
      {
        $date_format = 'Y-m-d';
      }
      
      // Form expired entries clause
      $expiredentriesclause = " AND (exp_channel_titles.expiration_date = '0' OR exp_channel_titles.expiration_date > '".$this->EE->localize->now."') ";
      
      // Form future entries clause
      $futureentriesclause = " AND exp_channel_titles.entry_date < '".$this->EE->localize->now."' ";

      // I. Finding data enclosed between {parent_category_start} and {parent_category_end},
      // {child_category_start} and {child_category_end},
      // {entries_start} and {entries_end},
      // {entries_wrapper_top_start} and {entries_wrapper_top_end},
      // {entries_wrapper_bottom_start} and {entries_wrapper_bottom_end} tag pairs

      // Find if there are tags {parent_category_start} and {parent_category_end}
      $closingtagpos = strpos($tagdata, '{parent_category_end}');
      //echo $closingtagpos.'<br>';
      $openingtagpos = strpos($tagdata, '{parent_category_start}');
      //echo $openingtagpos.'<br>';

      // In case there are tags {parent_category_start} and {parent_category_end}
      // find data enclosed between them
      if ($closingtagpos !== FALSE AND $openingtagpos !== FALSE)
      {
        $parentdataexists = TRUE;
        $parentdata = substr($tagdata, 0, $closingtagpos);
        $openingtagpos += 23;
        $parentdata = substr($parentdata, $openingtagpos);
        //echo $parentdata.'<br>';
      }

      // Find if there are tags {child_category_start} and {child_category_end}
      $closingtagpos = strpos($tagdata, '{child_category_end}');
      //echo $closingtagpos.'<br>';
      $openingtagpos = strpos($tagdata, '{child_category_start}');
      //echo $openingtagpos.'<br>';

      // In case there are tags {child_category_start} and {child_category_end}
      // find data enclosed between them
      if ($closingtagpos !== FALSE AND $openingtagpos !== FALSE)
      {
        $childdataexists = TRUE;
        $childdata = substr($tagdata, 0, $closingtagpos);
        $openingtagpos += 22;
        $childdata = substr($childdata, $openingtagpos);
        //echo $childdata.'<br>';
      }

      // Find if there are tags {entries_start} and {entries_end}
      $closingtagpos = strpos($tagdata, '{entries_end}');
      //echo $closingtagpos.'<br>';
      $openingtagpos = strpos($tagdata, '{entries_start}');
      //echo $openingtagpos.'<br>';

      // In case there are tags {entries_start} and {entries_end}
      // find data enclosed between them
      if ($closingtagpos !== FALSE AND $openingtagpos !== FALSE)
      {
        $entriesdataexists = TRUE;
        $entriesdata = substr($tagdata, 0, $closingtagpos);
        $openingtagpos += 15;
        $entriesdata = substr($entriesdata, $openingtagpos);
        //echo $entriesdata.'<br>';
      }

      // Find if there are tags {entries_wrapper_top_start} and {entries_wrapper_top_end}
      $closingtagpos = strpos($tagdata, '{entries_wrapper_top_end}');
      //echo $closingtagpos.'<br>';
      $openingtagpos = strpos($tagdata, '{entries_wrapper_top_start}');
      //echo $openingtagpos.'<br>';

      // In case there are tags {entries_wrapper_top_start} and {entries_wrapper_top_end}
      // find data enclosed between them
      if ($closingtagpos !== FALSE AND $openingtagpos !== FALSE)
      {
        $entrieswrappertop_dataexists = TRUE;
        $entrieswrappertop_data = substr($tagdata, 0, $closingtagpos);
        //echo $entrieswrappertop_data.'<br>';
        $openingtagpos += 27;
        $entrieswrappertop_data = substr($entrieswrappertop_data, $openingtagpos);
        //echo $entrieswrappertop_data.'<br>';
      }

      // Find if there are tags {entries_wrapper_bottom_start} and {entries_wrapper_top_end}
      $closingtagpos = strpos($tagdata, '{entries_wrapper_bottom_end}');
      //echo $closingtagpos.'<br>';
      $openingtagpos = strpos($tagdata, '{entries_wrapper_bottom_start}');
      //echo $openingtagpos.'<br>';

      // In case there are tags {entries_wrapper_bottom_start} and {entries_wrapper_bottom_end}
      // find data enclosed between them
      if ($closingtagpos !== FALSE AND $openingtagpos !== FALSE)
      {
        $entrieswrapperbottom_dataexists = TRUE;
        $entrieswrapperbottom_data = substr($tagdata, 0, $closingtagpos);
        //echo $entrieswrapperbottom_data.'<br>';
        $openingtagpos += 30;
        $entrieswrapperbottom_data = substr($entrieswrapperbottom_data, $openingtagpos);
        //echo $entrieswrapperbottom_data.'<br>';
      }

      // Find if there are tags {child_wrapper_bottom_start} and {child_wrapper_bottom_end}
      $closingtagpos = strpos($tagdata, '{child_wrapper_bottom_end}');
      //echo $closingtagpos.'<br>';
      $openingtagpos = strpos($tagdata, '{child_wrapper_bottom_start}');
      //echo $openingtagpos.'<br>';
      // In case there are tags {child_wrapper_bottom_start} and {child_wrapper_bottom_end}
      // find data enclosed between them
      if ($closingtagpos !== FALSE AND $openingtagpos !== FALSE)
      {
        $childwrapperbottom_dataexists = TRUE;
        $childwrapperbottom_data = substr($tagdata, 0, $closingtagpos);
        //echo $childwrapperbottom_data.'<br><br>';
        $openingtagpos += 28;
        $childwrapperbottom_data = substr($childwrapperbottom_data, $openingtagpos);
        //echo $childwrapperbottom_data.'<br><br>';
      }

      // Find if there are tags {parent_wrapper_bottom_start} and {parent_wrapper_bottom_end}
      $closingtagpos = strpos($tagdata, '{parent_wrapper_bottom_end}');
      //echo $closingtagpos.'<br>';
      $openingtagpos = strpos($tagdata, '{parent_wrapper_bottom_start}');
      //echo $openingtagpos.'<br>';
      // In case there are tags {parent_wrapper_bottom_start} and {parent_wrapper_bottom_end}
      // find data enclosed between them
      if ($closingtagpos !== FALSE AND $openingtagpos !== FALSE)
      {
        $parentwrapperbottom_dataexists = TRUE;
        $parentwrapperbottom_data = substr($tagdata, 0, $closingtagpos);
        //echo $parentwrapperbottom_data.'<br>';
        $openingtagpos += 29;
        $parentwrapperbottom_data = substr($parentwrapperbottom_data, $openingtagpos);
        //echo $parentwrapperbottom_data.'<br>';
      }

      // II. Dealing with parent category

      // In case "parent" parameter is defined check if it provides
      // one or more parent category ids
      if ($parent !== FALSE)
      {
        // Clean whitespace from "parent" parameter value
        $parent = str_replace(' ', '', $parent);
        // Split value of "parent" parameter using "|" symbol as separator
        $parentarray = explode('|', $parent);
        //echo $parentarray[0].'<br>';
        //echo $parentarray[1].'<br>';
      }
      // In case "parent" parameter is not defined check "sibling" parameter and
      // in case it is defined, find parent category id
      elseif ($sibling !== FALSE)
      {
        // Create SQL query string to find parent category id
        $todoinit = "SELECT parent_id FROM exp_categories WHERE exp_categories.cat_id='".$sibling."' ".$siteclause." LIMIT 0, 1";
        //echo $todoinit.'<br>';

        // Perform SQL query
        $queryinit = $this->EE->db->query($todoinit);
        //echo $queryinit->num_rows.'<br>';

        // Find parent category id
        if ($queryinit->num_rows() === 1)
        {
          $parent = $queryinit->row('parent_id');
          //echo $parent.'<br>';

          // Place parent category id into ids array
          $parentarray[0] = $parent;
          //echo $parentarray[0].'<br>';
        }
      }
      // In case "parent" and "sibling" parameters are not defined check "category_group" parameter and
      // in case it is defined, find all parent category id numbers
      elseif ($category_group !== FALSE)
      {        
        // Create SQL query string to find from that category group all highest level categories
        $todoinit = "SELECT cat_id FROM exp_categories WHERE exp_categories.group_id='".$category_group."' AND exp_categories.parent_id='0' ".$siteclause.$sortorderclause_parentcategories;

        // Perform SQL query
        $queryinit = $this->EE->db->query($todoinit);
        //echo '$queryinit->num_rows: '.$queryinit->num_rows().'<br>';

        foreach($queryinit->result_array() as $row)
        {
          array_push($parentarray, $row['cat_id']);
        }
      }

      if (count($parentarray) > 0)
      {
        $parent_category_count = 1;
        foreach($parentarray as $member)
        {
          $parent = $member;

          // Create SQL query string to find parent category name, parent category url_title, parent category description, parent category image
          $todoinit2 = "SELECT cat_name, cat_url_title, cat_description, cat_image FROM exp_categories WHERE exp_categories.cat_id='".$parent."' ".$siteclause." LIMIT 0, 1";
          //echo $todoinit2.'<br>';

          // Perform SQL query
          $queryinit2 = $this->EE->db->query($todoinit2);
          //echo $queryinit2->num_rows().'<br>';

          // Find parent category name, parent category url_title, parent category description, parent category image
          if ($queryinit2->num_rows() === 1)
          {
            $parent_cat_name = $queryinit2->row('cat_name');
            $parent_cat_url_title = $queryinit2->row('cat_url_title');
            $parent_cat_description = $queryinit2->row('cat_description');
            $parent_cat_image = $queryinit2->row('cat_image');
            //echo $parent_cat_name.' '.$parent_cat_url_title.' '..'<br>';
          }

          // If there are {parent_category_start}, {parent_category_end} tags, then
          // manipulate data enclosed between them
          if ($parentdataexists === TRUE)
          {
            $parentdatanew = $parentdata;
            $parentdatanew = str_replace('{parent_category_id}', $parent, $parentdatanew);
            $parentdatanew = str_replace('{parent_category_name}', $parent_cat_name, $parentdatanew);
            $parentdatanew = str_replace('{parent_category_url_title}', $parent_cat_url_title, $parentdatanew);
            $parentdatanew = str_replace('{parent_category_description}', $parent_cat_description, $parentdatanew);
            $parentdatanew = str_replace('{parent_category_image}', $parent_cat_image, $parentdatanew);
            $parentdatanew = str_replace('{parent_category_count}', $parent_category_count, $parentdatanew);
            $parentdatanew = str_replace('{parent_category_total}', count($parentarray), $parentdatanew);
            //echo $parentdatanew.'<br>';
            $tagdatanew .= $parentdatanew;
          }

          // IIa working with category_field
          // use tag params:
          //   custom_field_name       = "somtehing" menu: CP Home › Admin › channel Administration › Category Groups › Custom Category Fields
          //   custom_field_value      = "any value" menu: CP Home › Admin › channel Administration › Category Groups › Category Management › Edit Category
          //   (optional) custom_field =  "exclude" or "!"    select only categories with custom_field_name set to custom_field_value.
          //                              "like"              select only categories with custom_field_name LIKE custom_field_value - USE SQL LIKE notation.
          //                              Anything else means select only categories with custom_field_name NOT set to custom_field_value.
          //                              default is "include"
          // example:
          //  {exp:child_categories parent="{segment_3_category_id}" custom_field_name="right_panel" custom_field_value="Yes" child_categories_sort_by="custom" show_empty="yes" entries_limit="5" site="1" channel="{achannel}" parse="inward"}
          //  {exp:child_categories parent="{segment_3_category_id}" custom_field_name="right_panel" custom_field_value="Yes" custom_field="exclude" child_categories_sort_by="custom" show_empty="yes" entries_limit="5" site="1" channel="{achannel}" parse="inward"}
          //  {exp:child_categories parent="{segment_3_category_id}" custom_field_name="right_panel" custom_field_value="%Y%" custom_field="like" child_categories_sort_by="custom" show_empty="yes" entries_limit="5" site="1" channel="{achannel}" parse="inward"}

          if ( $custom_field_name != '' )																																				// gd
          {																																																			// gd
          	$field_id = 0;                                                                                      // gd

          	$fields = "SELECT field_id FROM exp_category_fields WHERE field_name='".$custom_field_name."'";     // gd
	          //echo $fields;
	          $query = $this->EE->db->query($fields);                                                                       // gd

	          if ( $query->num_rows() == 1 ) {                                                                      // gd
	          	$result = $query->result_array();
	          	$field_id = $result[0]['field_id'];                                                        // gd
				  	}                                                                                                   // gd

          	$compare = '=';                                                                                     // gd
          	if ( $custom_field === "exclude" OR $custom_field === "!" )                                         // gd
          		$compare = '!=';                                                                                  // gd
          	if ( $custom_field === "like" )                                                                     // gd
          		$compare = ' LIKE ';                                                                              // gd

          	$fieldclause = " AND exp_categories.cat_id IN (SELECT cat_id FROM exp_category_field_data where field_id_" . $field_id . $compare . "'" . $custom_field_value . "') ";	// gd
        	}                                                                                                     // gd

          // III. Dealing with child categories

          // Create SQL query string to find child categories ids, names, url_titles, descriptions, images
          $todo = "SELECT cat_id, cat_name, cat_url_title, cat_description, cat_image, cat_order FROM exp_categories WHERE exp_categories.parent_id='".$parent."' ".$siteclause.$fieldclause.$sortorderclause_categories; // gd - modification - added $fieldclause.
          
          //MULTIPLE LEVELS DEEP
          // $todo = "SELECT cat_id, cat_name, cat_url_title, cat_description, cat_image, cat_order FROM exp_categories WHERE (exp_categories.parent_id='".$parent."' OR exp_categories.parent_id IN (SELECT cat_id FROM exp_categories WHERE exp_categories.parent_id='".$parent."' ".$siteclause.$fieldclause.$sortorderclause_categories." )) ".$siteclause.$fieldclause.$sortorderclause_categories; // gd - modification - added $fieldclause.
          // echo $todo.'<br>';

          // Perform SQL query
          $query = $this->EE->db->query($todo);

          // Find number of child categories
          if ($show_empty !== 'no')
          {
            $parent_cat_children_count = $query->num_rows();
          }
          else
          {
            foreach ($query->result_array() as $onerow)
            {
              $child_category_id = $onerow['cat_id'];
              // Create SQL query string to find total number of entries posted into child category
              $todonext = "SELECT exp_channel_titles.entry_id, exp_channel_titles.url_title, exp_channel_titles.title, exp_channel_titles.entry_date, exp_channel_titles.site_id, exp_channel_titles.status, exp_channel_titles.expiration_date, exp_channels.channel_name, exp_channels.channel_title, exp_channels.channel_url, exp_categories.cat_name, exp_categories.cat_id, exp_categories.cat_url_title  FROM exp_category_posts, exp_channel_titles, exp_channels, exp_categories WHERE exp_category_posts.entry_id=exp_channel_titles.entry_id AND exp_channel_titles.channel_id=exp_channels.channel_id AND exp_category_posts.cat_id=exp_categories.cat_id AND exp_channel_titles.site_id=exp_categories.site_id AND exp_category_posts.cat_id='".$child_category_id."' ".$channelclause.$statusclause.$siteclause.$expiredentriesclause.$futureentriesclause.$sortorderclause_entries;
              //echo $todonext.'<br>';
              // Perform SQL queries
              $querynext = $this->EE->db->query($todonext);
              if ($querynext->num_rows() > 0)
              {
                $parent_cat_children_count++;
              }
            }
          }

          $tagdatanew = str_replace('{parent_category_children_count}', $parent_cat_children_count, $tagdatanew);

          $child_category_count = 1;

          foreach ($query->result_array() as $onerow)
          {
            // Find child category id, name, url_title
            $child_category_id = $onerow['cat_id'];
            $child_category_name = $onerow['cat_name'];
            $child_category_url_title = $onerow['cat_url_title'];
            $child_category_description = $onerow['cat_description'];
            $child_category_image = $onerow['cat_image'];
            $child_category_order_num = $onerow['cat_order'];
            //echo $child_category_order_num.' '.$child_category_id.' '.$child_category_name.' '.$child_category_url_title.'<br>';

            // Create SQL query string to find total number of entries posted into child category
            $todonext = "SELECT exp_channel_titles.entry_id, exp_channel_titles.url_title, exp_channel_titles.title, exp_channel_titles.entry_date, exp_channel_titles.site_id, exp_channel_titles.status, exp_channel_titles.expiration_date, exp_channels.channel_name, exp_channels.channel_title, exp_channels.channel_url, exp_categories.cat_name, exp_categories.cat_id, exp_categories.cat_url_title  FROM exp_category_posts, exp_channel_titles, exp_channels, exp_categories WHERE exp_category_posts.entry_id=exp_channel_titles.entry_id AND exp_channel_titles.channel_id=exp_channels.channel_id AND exp_category_posts.cat_id=exp_categories.cat_id AND exp_channel_titles.site_id=exp_categories.site_id AND exp_category_posts.cat_id='".$child_category_id."' ".$channelclause.$statusclause.$siteclause.$expiredentriesclause.$futureentriesclause.$sortorderclause_entries;
            //echo $todonext.'<br>';

            // Perform SQL queries
            $querynext = $this->EE->db->query($todonext);

            // Find total number of entries posted into child category

            // the case "show_expired" parameter is not set to "no"
            if ($show_expired !== 'no')
            {
              $entries_total = $querynext->num_rows();
            }
            // the case "show_expired" parameter is set to "no"
            else
            {
              $entries_total = 0;
              foreach($querynext->result_array() as $rownext2)
              {
                //echo 'current_time: '.$current_time.' expiration_date: '.$rownext2['expiration_date'].'<br><br>';
                if ($current_time < $rownext2['expiration_date'] OR $rownext2['expiration_date'] == 0)
                {
                  $entries_total++;
                }
              }
            }

            //echo $entries_total.'<br>';

            // In case "show_empty" parameter has the value "no" and total number of entries posted into child category is 0 do not display such category
            if ($entries_total > 0 OR $show_empty === 'yes')
            {

              // If there are tags {child_category_start} and {child_category_end}
              // manipulate data enclosed between them
              if ($childdataexists === TRUE)
              {
                $childdatanew = $childdata;
                $childdatanew = str_replace('{parent_category_id}', $parent, $childdatanew);
                $childdatanew = str_replace('{parent_category_name}', $parent_cat_name, $childdatanew);
                $childdatanew = str_replace('{parent_category_description}', $parent_cat_description, $childdatanew);
                $childdatanew = str_replace('{parent_category_image}', $parent_cat_image, $childdatanew);
                $childdatanew = str_replace('{parent_category_url_title}', $parent_cat_url_title, $childdatanew);
                $childdatanew = str_replace('{parent_category_children_count}', $parent_cat_children_count, $childdatanew);
                $childdatanew = str_replace('{parent_category_count}', $parent_category_count, $childdatanew);
                $childdatanew = str_replace('{parent_category_total}', count($parentarray), $childdatanew);
                $childdatanew = str_replace('{child_category_id}', $child_category_id, $childdatanew);
                $childdatanew = str_replace('{child_category_name}', $child_category_name, $childdatanew);
                $childdatanew = str_replace('{child_category_url_title}', $child_category_url_title, $childdatanew);
                $childdatanew = str_replace('{child_category_description}', $child_category_description, $childdatanew);
                $childdatanew = str_replace('{child_category_image}', $child_category_image, $childdatanew);
                $childdatanew = str_replace('{child_category_order_num}', $child_category_order_num, $childdatanew);
                $childdatanew = str_replace('{entries_total}', $entries_total, $childdatanew);
                $childdatanew = str_replace('{child_category_count}', $child_category_count, $childdatanew);
                $tagdatanew .= $childdatanew;
                if ($entriesdataexists === TRUE AND $entries_total > 0 AND $entrieswrappertop_dataexists === TRUE AND $entrieswrapperbottom_dataexists === TRUE)
                {
                  $entrieswrappertop_datanew = $entrieswrappertop_data;
                  $entrieswrappertop_datanew = str_replace('{entries_total}', $entries_total, $entrieswrappertop_datanew);
                  $entrieswrappertop_datanew = str_replace('{child_category_id}', $child_category_id, $entrieswrappertop_datanew);
                  $tagdatanew .= $entrieswrappertop_datanew;
                }
              }

              // IV. Dealing with titles posted into child categories

              // Create SQL query string to find channel_name, channel_url, titles,
              // url_titles and dates of entries posted into child category
              $todolast = $todonext.$limitclause;
              //echo $todolast.'<br>';

              // Perform SQL query
              $querylast = $this->EE->db->query($todolast);

              // Reset incrementer
              $incrementer = 1;

              // If there are tagpair {entries_start}{entries_end}, then,
              // first, for every title posted into child category find its
              // channel name
              // channel url
              // title
              // url title
              // entry date
              // entry id
              // manipulate data enclosed between them
              // second, manipulate data enclosed between that tagpair
              if ($entriesdataexists === TRUE)
              {
                foreach ($querylast->result_array() as $rowlast)
                {
                  if ($show_expired !== 'no' OR $rowlast['expiration_date'] == 0 OR $current_time < $rowlast['expiration_date'])
                  {
                    $channel_short_name = $rowlast['channel_name'];
                    $channel_name = $rowlast['channel_title'];
                    $channel_url = $rowlast['channel_url'];
                    $title = $rowlast['title'];
                    $url_title = $rowlast['url_title'];
                    $entry_id = $rowlast['entry_id'];
                    $entry_date = date($date_format, $rowlast['entry_date']);
                    //echo $channel_name.' '.$channel_url.' '.$title.' '.$url_title.' '.$entry_date.'<br>';
                    $entriesdatanew = $entriesdata;
                    $entriesdatanew = str_replace('{parent_category_id}', $parent, $entriesdatanew);
                    $entriesdatanew = str_replace('{parent_category_name}', $parent_cat_name, $entriesdatanew);
                    $entriesdatanew = str_replace('{parent_category_description}', $parent_cat_description, $entriesdatanew);
                    $entriesdatanew = str_replace('{parent_category_image}', $parent_cat_image, $entriesdatanew);
                    $entriesdatanew = str_replace('{parent_category_url_title}', $parent_cat_url_title, $entriesdatanew);
                    $entriesdatanew = str_replace('{parent_category_children_count}', $parent_cat_children_count, $entriesdatanew);
                    $entriesdatanew = str_replace('{parent_category_count}', $parent_category_count, $entriesdatanew);
                    $entriesdatanew = str_replace('{parent_category_total}', count($parentarray), $entriesdatanew);
                    $entriesdatanew = str_replace('{child_category_id}', $child_category_id, $entriesdatanew);
                    $entriesdatanew = str_replace('{child_category_name}', $child_category_name, $entriesdatanew);
                    $entriesdatanew = str_replace('{child_category_url_title}', $child_category_url_title, $entriesdatanew);
                    $entriesdatanew = str_replace('{child_category_description}', $child_category_description, $entriesdatanew);
                    $entriesdatanew = str_replace('{child_category_image}', $child_category_image, $entriesdatanew);
                    $entriesdatanew = str_replace('{child_category_order_num}', $child_category_order_num, $entriesdatanew);
                    $entriesdatanew = str_replace('{entries_total}', $entries_total, $entriesdatanew);
                    $entriesdatanew = str_replace('{child_category_count}', $child_category_count, $entriesdatanew);
                    $entriesdatanew = str_replace('{channel_short_name}', $channel_short_name, $entriesdatanew);
                    $entriesdatanew = str_replace('{channel_name}', $channel_name, $entriesdatanew);
                    $entriesdatanew = str_replace('{channel_url}', $channel_url, $entriesdatanew);
                    $entriesdatanew = str_replace('{title}', $title, $entriesdatanew);
                    $entriesdatanew = str_replace('{url_title}', $url_title, $entriesdatanew);
                    $entriesdatanew = str_replace('{entry_id}', $entry_id, $entriesdatanew);
                    $entriesdatanew = str_replace('{entry_date}', $entry_date, $entriesdatanew);
                    $entriesdatanew = str_replace('{count}', $incrementer, $entriesdatanew);
                    $tagdatanew .= $entriesdatanew;

                    if (($incrementer === $entries_total OR $incrementer == $entries_limit) AND $entrieswrappertop_dataexists === TRUE AND $entrieswrapperbottom_dataexists === TRUE)
                    {
                      $entrieswrapperbottom_datanew = $entrieswrapperbottom_data;
                      $entrieswrapperbottom_datanew = str_replace('{entries_total}', $entries_total, $entrieswrapperbottom_data);
                      $entrieswrapperbottom_datanew = str_replace('{child_category_id}', $child_category_id, $entrieswrapperbottom_data);
                      $tagdatanew .= $entrieswrapperbottom_datanew;
                    }
                    //$tagdatanew .= '<br> incrementer: '.$incrementer.' entries_total: '.$entries_total.' entries_limit: '.$entries_limit.' entrieswrapperbottom_data: '.$entrieswrapperbottom_data.'<br>';
                    $incrementer++;
                  }
                }
              }
              if ($childwrapperbottom_dataexists === TRUE)
              {
                $childwrapperbottom_datanew = $childwrapperbottom_data;
                $childwrapperbottom_datanew = str_replace('{entries_total}', $entries_total, $childwrapperbottom_datanew);
                $childwrapperbottom_datanew = str_replace('{child_category_id}', $child_category_id, $childwrapperbottom_datanew);
                $tagdatanew .= $childwrapperbottom_datanew;
              }
              $child_category_count++;
            }
          }
          //echo $tagdatanew.'<br>';
          if ($parentwrapperbottom_dataexists === TRUE)
          {
            $parentwrapperbottom_datanew = $parentwrapperbottom_data;
            $parentwrapperbottom_datanew = str_replace('{parent_category_children_count}', $parent_cat_children_count, $parentwrapperbottom_datanew);
            $parentwrapperbottom_datanew = str_replace('{parent_category_id}', $parent, $parentwrapperbottom_datanew);
            $tagdatanew .= $parentwrapperbottom_datanew;
          }
          $parent_category_count++;
        }
        // Output transformed tagdata
        $this->return_data = $tagdatanew;
      }
    }
  }
  // END FUNCTION

  // ----------------------------------------
  //  Plugin Usage
  // ----------------------------------------
  // This function describes how the plugin is used.
  //  Make sure and use output buffering

  function usage()
  {
  ob_start();
  ?>

  PARAMETERS:

  1) parent - Optional. Allows you to specify parent category id number
  (the id number of each category is displayed in the Control Panel).
  You can stack parent categories using pipe character e.g. parent="3|16|28".

  2) sibling - Optional. Allows you to specify child category id number
  (the id number of each category is displayed in the Control Panel).

  3) category_group - Optional. Allows you to specify category group id number
  (the id number of each category group is displayed in the Control Panel) or
  category group name. In case this parameter is defined, all highest level 
  categories of that category group will be treated by plugin as parent categories.

  Either "parent" or "sibling" or "category_group" parameter MUST BE defined.

  4) channel - Optional. Use it to specify channel name.
  Pipeline character and “not” are supported.

  5) site - Optional. Allows you to specify site id number.

  6) parent_categories_sort_by - Optional. For use together with "category_group"
  parameter. Allows you to specify sort order
  of parent categories. This parameter accepts three values: "name" (parent
  categories will be sorted by name), "id" (parent categories will be sorted
  by id number), and "custom" (parent categories will be sorted using custom order
  as defined in control panel). Default value is "name".

  7) parent_categories_sort_direction - Optional. For use together with "category_group"
  parameter. Allows you to specify sort direction
  of parent categories. This parameter accepts two values: "asc" and "desc".
  Default value is "asc".

  8) child_categories_sort_by - Optional. Allows you to specify sort order
  of child categories. This parameter accepts three values: "name" (child
  categories will be sorted by name), "id" (child categories will be sorted
  by id number), and "custom" (child categories will be sorted using custom order
  as defined in control panel). Default value is "name".

  9) child_categories_sort_direction - Optional. Allows you to specify sort direction
  of child categories. This parameter accepts two values: "asc" and "desc".
  Default value is "asc".

  10) show_empty - Optional. Allows you to specify if child categories having no
  entries should be displayed or not. This parameter accepts two values: "yes" and "no".
  Default value is "no".

  11) entries_sort_by - Optional. Allows you to specify sort order of entries.
  This parameter accepts two values: "title" and "date". Default value is "date".

  12) entries_sort_direction - Optional. Allows you to specify sort direction
  of entries. This parameter accepts two values: "asc" and "desc".
  Default value is "desc".

  13) entries_limit - Optional. Allows you to specify how many entries posted into
  child category should be displayed. This parameter accepts as its value an integer
  or "none". Default value is "10". Value "none" means that all entries will be
  displayed.

  14) date_format - Optional. Allows you to specify PHP date format string
  (Not ExpressionEngine's date format string!). Default value is "Y-m-d".

  15) show_expired - Optional. Allows you to specify if you wish expired entries
  to be displayed. If the value is "yes", expired entries will be displayed; if the
  value is "no", expired entries will not be displayed. Default value is "yes".

  16) status - Optional. Allows you to specify status of entries.
  You can stack statuses using pipe character to get entries
  having any of those statuses, e.g. status="open|draft". Or use "not"
  (with a space after it) to exclude statuses,
  e.g. status="not submitted|processing|closed".

  17) invalid_input - Optional. Accepts two values: “alert” and “silence”.
  Default value is “silence”. If the value is “alert”, then in cases when some
  parameter’s value is invalid plugin exits and PHP alert is being shown;
  if the value is “silence”, then in cases when some parameter’s value
  is invalid plugin finishes its work without any alert being shown.
  Set this parameter to “alert” for development, and to “silence” - for deployment.
  
  18) custom_field_name - Optional. Used when there is a need to display child categories
  with specific category custom field equal to or not equal to or like  
  specific value.
  
  19) custom_field_value - Optional. Used when there is a need to display child categories
  with specific category custom field equal to or not equal to or like specific value.
  
  20) custom_field - Optional. Used when there is a need to display child categories with specific category custom field
   *not* equal or *like* to specific value. Acceps the value "include", "exclude" and "like" (only categories 
  with custom_field_name LIKE custom_field_value will be displayed - SQL LIKE notation will be used). 
  Default value is "include".
  

  VARIABLE PAIRS:

  1) {parent_category_start}{parent_category_end} - Allows you to specify portion of
  code which will be iterated as many times as there are parent categories.
  Single variables available for use inside this variable pair:
  {parent_category_id}
  {parent_category_name}
  {parent_category_url_title}
  {parent_category_description}
  {parent_category_image}
  {parent_category_children_count}
  {parent_category_count}
  {parent_category_total}

  2) {child_category_start}{child_category_end} - Allows you to specify portion of
  code which will be iterated as many times as there are child categories.
  Single variables available for use inside this variable pair:
  {parent_category_id}
  {parent_category_name}
  {parent_category_url_title}
  {parent_category_description}
  {parent_category_image}
  {parent_category_children_count}
  {parent_category_count}
  {parent_category_total}
  {child_category_id}
  {child_category_name}
  {child_category_url_title}
  {child_category_description}
  {child_category_image}
  {child_category_order_num}
  {child_category_count}
  {entries_total}

  3) {entries_start}{entries_end} - Allows you to specify portion of
  code which will be iterated as many times as there are entries posted into child
  category. Single variables available for use inside this variable pair:
  {parent_category_id}
  {parent_category_name}
  {parent_category_url_title}
  {parent_category_description}
  {parent_category_image}
  {parent_category_children_count}
  {parent_category_count}
  {parent_category_total}
  {child_category_id}
  {child_category_name}
  {child_category_url_title}
  {child_category_description}
  {child_category_image}
  {child_category_order_num}
  {child_category_count}
  {entries_total}
  {channel_name}
  {channel_short_name}
  {channel_url}
  {title}
  {url_title}
  {entry_id}
  {entry_date}
  {count}

  4) {entries_wrapper_top_start}{entries_wrapper_top_end} - Allows you to specify top part of
  the code with which you want to wrap output of {entries_start}{entries_end} variable pair.
  Single variables available for use inside this variable pair:
  {entries_total}
  {child_category_id}

  5) {entries_wrapper_bottom_start}{entries_wrapper_bottom_end} - Allows you to specify bottom part of
  the code with which you want to wrap output of {entries_start}{entries_end} variable pair.
  Single variables available for use inside this variable pair:
  {entries_total}
  {child_category_id}

  6) {child_wrapper_bottom_start}{child_wrapper_bottom_end} - Allows you to specify bottom part of
  the code with which you want to wrap all data of child category.
  Single variables available for use inside this variable pair:
  {entries_total}
  {child_category_id}

  7) {parent_wrapper_bottom_start}{parent_wrapper_bottom_end} - Allows you to specify bottom part of
  the code with which you want to wrap all data of parent category.
  Single variables available for use inside this variable pair:
  {parent_category_children_count}
  {parent_category_id}

  The tag {exp:child_categories} MUST contain at least one variable pair  out of
  {parent_category_start}{parent_category_end}, {child_category_start}{child_category_end} and
  {entries_start}{entries_end} variable pairs and each single variable MUST BE inside
  relevant variable pair.

  SINGLE VARIABLES:

  1) {parent_category_id} - outputs id number of parent category.

  2) {parent_category_name} - outputs name of parent category.

  3) {parent_category_url_title} - outputs url title of parent category.

  4) {parent_category_description} - outputs description title of parent category.

  5) {parent_category_image} - outputs url of the image of parent category.

  6) {parent_category_children_count} - outputs number of child categories of parent category.

  7) {parent_category_count} - outputs count number, i.e. 1, 2, 3, etc., of the
  parent category.
  
  8) {parent_category_total} - outputs number of parent categories.

  9) {child_category_id} - outputs id number of child category.

  10) {child_category_name} - outputs name of child category.

  11) {child_category_url_title} - outputs url title of child category.

  12) {child_category_description} - outputs description title of child category.

  13) {child_category_image} - outputs url of the image of child category.

  14) {child_category_order_num} - outputs number used for custom ordering of
  categories.

  15) {child_category_count} - outputs outputs count number, i.e. 1, 2, 3, etc., of the
  child category. Empty child categories are counted in case "show_empty" parameter has the
  value "yes".

  16) {entries_total} - outputs total number of entries posted into child category.

  17) {channel_name} - outputs full channel name into which entry is posted.

  18) {channel_short_name} - outputs short channel name into which entry is posted.

  19) {channel_url} - outputs channel url as specified in control panel.

  20) {title} - outputs title of entry.

  21) {url_title} - outputs url title of entry.

  22) {entry_date} - outputs entry date of entry.

  23) {count} - outputs order number of entry.

  EXAMPLE OF USAGE:

  {exp:child_categories parent="18|29" child_categories_sort_by="custom" child_categories_sort_direction="asc" show_empty="yes" entries_sort_by="date" entries_sort_direction="asc" entries_limit="3" site="1"}
  {parent_category_start}
  
  <h1><a href="{homepage}/category/{parent_category_url_title}/">{parent_category_name}</a></h1>
  {parent_category_end}
  {child_category_start}
  <h2><a href="{homepage}/category/{child_category_url_title}/">{child_category_name}</a></h2>
  Total entries: {entries_total}<br>
  {child_category_end}
  {entries_wrapper_top_start}< div style="border: 1px solid red;" >{entries_wrapper_top_end}
  {entries_start}
  <a href="{channel_url}{url_title}">{title}</a> channel: {channel_name}, posted: {entry_date}<br>
  {entries_end}
  {entries_wrapper_bottom_start}< /div >{entries_wrapper_bottom_end}
  {/exp:child_categories}

  This code will output:

  Parent category 18
    Child category
    Total entries
       Entry
       Entry
       Entry
    Child category
    Total entries
       Entry
       Entry
       Entry
    Child category
    Total entries
       Entry
       Entry
       Entry
  Parent category 29
    Child category
    Total entries
       Entry
       Entry
       Entry
    Child category
    Total entries
       Entry
       Entry
       Entry

  Place the tag {exp:child_categories} in any of your templates.

  <?php
  $buffer = ob_get_contents();

  ob_end_clean();

  return $buffer;
  }
  // END USAGE

}
// END CLASS
?>