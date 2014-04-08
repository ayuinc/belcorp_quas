<?php

/*
=====================================================
 This ExpressionEngine plugin was created by Laisvunas
 - http://devot-ee.com/developers/ee/laisvunas/
=====================================================
 Copyright (c) Laisvunas
=====================================================
 Purpose: Provides loop functionality in templates
=====================================================
*/

$plugin_info = array(
						'pi_name'			=> 'Loop Plus',
						'pi_version'		=> '1.3',
						'pi_author'			=> 'Laisvunas',
						'pi_author_url'		=> 'http://devot-ee.com/developers/ee/laisvunas/',
						'pi_description'	=> 'Provides loop functionality in templates',
						'pi_usage'			=> Loop_plus::usage()
					);

class Loop_plus {

 	var $return_data;
 
 	function Loop_plus()
 	{
  		$this->EE =& get_instance();
  		
  		// get parameters and assign defaults if not set
  		$start = ($this->EE->TMPL->fetch_param('start') !== false) ? $this->EE->TMPL->fetch_param('start') : 1;
  		$end = ($this->EE->TMPL->fetch_param('end') !== false) ? $this->EE->TMPL->fetch_param('end') : 3;
  		$increment = ($this->EE->TMPL->fetch_param('increment') !== false) ? $this->EE->TMPL->fetch_param('increment') : 1;
  		
  		// Fetch tagdata
  		$tagdata = $this->EE->TMPL->tagdata;
  		
  		// Define variables
  		$count = 1;
  		$max_count = 100000; // so the loop doesn't time-out
  		$return_data = '';
  		$i = $start;
  		
  		// are we incrementing or decrementing
  		$ascending = true;
  		if ($increment < 0)
  		{
  			 $ascending = false;
  		}
  		
  		// I. Finding if there is at least one {loop_area}{/loop_area}
    // variable pair
    
    $opening_tag_count = substr_count($tagdata, LD.'loop_area'.RD);
    //echo '$opening_tag_count: '.$opening_tag_count.'<br><br>';
    $closing_tag_count = substr_count($tagdata, LD.'/loop_area'.RD);
    //echo '$closing_tag_count: '.$closing_tag_count.'<br><br>';
    
    // II. The case there is no {loop_area}{/loop_area}
    // variable pair
    if ($opening_tag_count === 0 OR $closing_tag_count === 0)
    {
      for ($i = $start; (($ascending && $i <= $end) || (!$ascending && $i >= $end)) && $count < $max_count; $i = $i + $increment)
      {
        $tagdatanew = $tagdata;
        $conds['index'] = $i;
        $conds['loop_count'] = $count;
        $tagdatanew = $this->EE->TMPL->swap_var_single('index', $conds['index'], $tagdatanew);
        $tagdatanew = $this->EE->TMPL->swap_var_single('loop_count', $conds['loop_count'], $tagdatanew);
        $tagdatanew = $this->EE->functions->prep_conditionals($tagdatanew, $conds);
     			$count++;
     			$return_data .= $tagdatanew;
      }
      $this->return_data = $return_data;
    }
    // III. The case there is at least one {loop_area}{/loop_area}
    // variable pair
    else
    {
      // III.1. Finding data inside the last {loop_area}{/loop_area}
      // variable pair
      while (strpos($tagdata, LD.'loop_area'.RD) !== FALSE AND strpos($tagdata, LD.'/loop_area'.RD) !== FALSE)
      {
        $opening_tag_last_pos = strrpos($tagdata, LD.'loop_area'.RD);
        //echo 'opening_tag_last_pos: '.$opening_tag_last_pos.'<br><br>';
        if ($opening_tag_last_pos !== FALSE)
        {
          $tagdata_first_half = substr($tagdata, 0, $opening_tag_last_pos);
          //echo 'tagdata_first_half:<br><br>['.$tagdata_first_half.']<br><br>';
          $opening_tag_last_pos = $opening_tag_last_pos + 11;
          $tagdata_second_half = substr($tagdata, $opening_tag_last_pos);
          //echo 'tagdata_second_half:<br><br>['.$tagdata_second_half.']<br><br>';
          $tagdata_second_half_splitted = explode(LD.'/loop_area'.RD, $tagdata_second_half, 2);
          //echo 'count tagdata_second_half_splitted: '.count($tagdata_second_half_splitted).'<br><br>';
          
          // the case there are both opening and closing part of
          // {replace_area}{/replace_area} variable pair
          if (count($tagdata_second_half_splitted) >= 2)
          {
            //echo 'tagdata_second_half_splitted 0: <br><br>['.$tagdata_second_half_splitted[0].']<br><br>';
            //echo 'tagdata_second_half_splitted 1: <br><br>['.$tagdata_second_half_splitted[1].']<br><br>';
            $loop_area_data = $tagdata_second_half_splitted[0];
            $count = 1;
            $loop_area_datafinal = '';
            for ($i = $start; (($ascending && $i <= $end) || (!$ascending && $i >= $end)) && $count < $max_count; $i = $i + $increment)
            {
              $loop_area_datanew = $loop_area_data;
              $conds['index'] = $i;
              $conds['loop_count'] = $count;
              $loop_area_datanew = $this->EE->TMPL->swap_var_single('index', $conds['index'], $loop_area_datanew);
              $loop_area_datanew = $this->EE->TMPL->swap_var_single('loop_count', $conds['loop_count'], $loop_area_datanew);
              $loop_area_datanew = $this->EE->functions->prep_conditionals($loop_area_datanew, $conds);
              $count++;
              $loop_area_datafinal .= $loop_area_datanew;
            }
            //echo '$loop_area_datafinal: <br><br>['.$loop_area_datafinal.']<br><br>';
            $tagdata = $tagdata_first_half.$loop_area_datafinal.$tagdata_second_half_splitted[1];
          }
        }
      }
      $this->return_data = $tagdata;
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
Use as follows:

{exp:loop_plus start="5" end="10" increment="1"}
This loop has been executed {loop_count} times and is now on iteration number {index}.
{/exp:loop_plus}

Or use {loop_area}{/loop_area} variable pair to mark an area which should be looped:

{exp:loop_plus start="5" end="10" increment="1"}
Some code
{loop_area}
This loop has been executed {loop_count} times and is now on iteration number {index}.
{/loop_area}
Some code
{/exp:loop_plus}

You can even use several {loop_area}{/loop_area} variable pairs inside one {exp:loop_plus} tag.

Conditionals are supported.

<?php
$buffer = ob_get_contents();
	
ob_end_clean(); 

return $buffer;
}
// END USAGE
}
// END CLASS
?>