<?php

/*
=====================================================
 This ExpressionEngine add-on was created by Sekar Suresh
 - http://devot-ee.com/developers/sekar-suresh
=====================================================
 Copyright (c) Sekar Suresh
=====================================================
 This is commercial Software.
 One purchased license permits the use this Software on the SINGLE website.
 Unless you have been granted prior, written consent from Sekar Sureah, you may not:
 * Reproduce, distribute, or transfer the Software, or portions thereof, to any third party
 * Sell, rent, lease, assign, or sublet the Software or portions thereof
 * Grant rights to any other person
=====================================================
*/

$plugin_info = array(
						'pi_name'			=> 'Easy Auto Scroll Pagination',
						'pi_version'		=> '1.0',
						'pi_author'			=> 'Sekar Suresh',
						'pi_author_url'		=> 'http://devot-ee.com/developers/sekar-suresh',
						'pi_description'	=> 'Easy Auto Scroll Pagination is a Expressengine plugin allowing you to effortlessly implement infinite scrolling, lazy loading, or whatever catchy phrase you may know it as, within a template. 
I can create Ajax based auto loading script, which loads records when user scrolls to bottom of the page
 A real-world example of this behavior is your Facebook News Feed, which automatically loads content as you scroll down and reach the end of the page.

',
						'pi_usage'			=> Easy_autoscrollpagination ::usage()
					);

class Easy_autoscrollpagination  {
 
  
  function Easy_autoscrollpagination ()
  {
    $this->EE =& get_instance();
  }

  
  function bind()
  {    
    // Fetch the tagdata
    $tagdata = $this->EE->TMPL->tagdata;
    //echo '$tagdata: ['.$tagdata.']'.PHP_EOL;
    
    // fetch params
    $embed_template_link = $this->EE->TMPL->fetch_param('embed_template_link');
    $ajax_container = $this->EE->TMPL->fetch_param('ajax_container');
    $limit = $this->EE->TMPL->fetch_param('limit');
    $offset = $this->EE->TMPL->fetch_param('offset');
    
    // param "embed_template_link" is required
    if (!$embed_template_link)
    {
      echo 'ERROR! Parameter "embed_template_link" of exp:ajax_pagination tag must be defined!<br><br>'.PHP_EOL;;
    }
    // param "ajax_container" is required
    if (!$ajax_container)
    {
      echo 'ERROR! Parameter "ajax_container" of exp:ajax_pagination tag must be defined!<br><br>'.PHP_EOL;;
    }
    
    // form embed template URL
    $embed_template_link = $this->parse_parm_url($embed_template_link);
    // form javascript
      $js = $this->_js( $embed_template_link, $ajax_container, $limit, $offset);
   
	  $out = $js.$tagdata;
    
    
    return $out;
  }

  
 function parse_parm_url($parameter_value)
  {    
    // parse {site_id}, {site_url}, {site_index}, {homepage}
    $site_id = $this->EE->config->item('site_id');
    $site_url = trim(stripslashes($this->EE->config->item('site_url')), '/');
    $site_index = stripslashes($this->EE->config->item('site_index'));
    $homepage = $site_url.'/'.$site_index;
    
    $parameter_value = str_replace(LD.'site_id'.RD, $site_id, $parameter_value);
    $parameter_value = str_replace(LD.'site_url'.RD, $site_url, $parameter_value);
    $parameter_value = str_replace(LD.'site_index'.RD, $site_index, $parameter_value);
    $parameter_value = str_replace(LD.'homepage'.RD, $homepage, $parameter_value);
    
    $parameter_value = str_replace('&#47;', '/', $parameter_value);
    $parameter_value = trim($parameter_value, '/').'/';
    //echo '$parameter_value: ['.$parameter_value.']<br><br>'.PHP_EOL;
    
    return $parameter_value;
  }
  // END FUNCTION
  
  function _js($embed_template_link, $ajax_container,  $limit, $offset)
  {
    ob_start(); 
?>

<script type="text/javascript">

//<![CDATA[

$(document).ready(function() {

	$('#content').scrollPagination({

		nop     : <?= @$limit ?>, // The number of posts per scroll to be loaded
		offset  : <?= @$offset ?>, // Initial offset, begins at 0 in this case
		error   : 'No More Posts!', // When the user reaches the end this is the message that is
		                            // displayed. You can change this if you want.
		delay   : 500, // When you scroll down the posts will load after a delayed amount of time.
		               // This is mainly for usability concerns. You can alter this as you see fit
		scroll  : true // The main bit, if set to false posts will not load as the user scrolls. 
		               // but will still load if the user clicks.
		
	});
	
});

(function($) {

	$.fn.scrollPagination = function(options) {
		
		var settings = { 
			nop     : <?= @$limit ?>, // The number of posts per scroll to be loaded
			offset  : <?= @$offset ?>, // Initial offset, begins at 0 in this case
			error   : 'No More Posts!', // When the user reaches the end this is the message that is
			                            // displayed. You can change this if you want.
			delay   : 500, // When you scroll down the posts will load after a delayed amount of time.
			               // This is mainly for usability concerns. You can alter this as you see fit
			scroll  : true // The main bit, if set to false posts will not load as the user scrolls. 
			               // but will still load if the user clicks.
		}
		
		// Extend the options so they work with the plugin
		if(options) {
			$.extend(settings, options);
		}
		
		// For each so that we keep chainability.
		return this.each(function() {		
			
			// Some variables 
			$this = $(this);
			$settings = settings;
			var offset = $settings.offset;
			var busy = false; // Checks if the scroll action is happening 
			                  // so we don't run it multiple times
			
			// Custom messages based on settings
			if($settings.scroll == true) $initmessage = 'Scroll for more or click here';
			else $initmessage = 'Click for more';
			
			// Append custom messages and extra UI
			$this.append('<div class="content"></div><div class="loading-bar">'+$initmessage+'</div>');
			
			function getData() {
				
	
		
				// Post data to ajax.php
				$.get('<?= @$embed_template_link ?>', {
						
					action        : 'scrollpagination',
				    limit        : $settings.nop,
				    offset        : offset,
									    
				}, function(data) {
						
					// Change loading bar content (it may have been altered)
					$this.find('.loading-bar').html($initmessage);
						
					// If there is no data returned, there are no more posts to be shown. Show error
				
					if($.trim(data) == "") { 
						$this.find('.loading-bar').html($settings.error);	
					}
					else {
						
						// Offset increases
					    offset = offset+$settings.nop; 
						    
						// Append the data to the content div
					   	$this.find('.content').append(data);
						
						// No longer busy!	
						busy = false;
					}	
						
				});
					
			}	
			
			getData(); // Run function initially
			
			// If scrolling is enabled
			if($settings.scroll == true) {
				// .. and the user is scrolling
				$(window).scroll(function() {
					
					// Check the user is at the bottom of the element
					if($(window).scrollTop() + $(window).height() > $this.height() && !busy) {
						
						// Now we are working, so busy is true
						busy = true;
						
						// Tell the user we're loading posts
						$this.find('.loading-bar').html('Loading Posts');
						
						// Run the function to fetch the data inside a delay
						// This is useful if you have content in a footer you
						// want the user to see.
						setTimeout(function() {
							
							getData();
							
						}, $settings.delay);
							
					}	
				});
			}
			
			// Also content can be loaded by clicking the loading bar/
			$this.find('.loading-bar').click(function() {
			
				if(busy == false) {
					busy = true;
					getData();
				}
			
			});
			
		});
	}

})(jQuery);

//]]>
</script>

<?php
    $buffer = ob_get_contents();
    ob_end_clean(); 
    
    return $buffer;
  }
  // END FUNCTION
  
  // ----------------------------------------
  //  Plugin Usage
  // ----------------------------------------
  
  function usage()
  {
    ob_start(); 
?>

Easy Auto Scroll Pagination plugin that helps to implement Infinite Scrolling effect for your web page.
This Plugin is an useful allowing  that auto loads content via ajax as you scroll down to the bottom of current page.

THE TAG {exp:easy_autoscrollpagination:bind ajax_container="content" limit="2" offset="0" embed_template_link="{homepage}/article/embed_ajax_simple_scroll" parse="inward"}

Parameters:
===========================================================================

1) ajax_container - required. Allows you to specify Div id parameter which will act as the container to place load ajax data.

2) limit - required . Allows you specify number of  Records per scroll to be loaded

3) offset - required . Allows you specify Initial offset, begins at 0 in this case

4) embed_template_link - required. Allows you to specify URL of the embed template. 


BASIC USAGE

Include jQuery library  on the page

{exp:jquery:script_tag}

Main template (e.g. article/simplescroll):

{exp:easy_autoscrollpagination:bind ajax_container="content" limit="5" offset="0" embed_template_link="{homepage}/article/embed_ajax_simple_scroll"  parse="inward"}
<div id="content" class="content">
</div>
{/exp:easy_autoscrollpagination:bind}


Embed template (e.g. article/embed_ajax_simple_scroll):

{exp:channel:entries  channel="articles" limit="{get:limit}" offset="{get:offset}"  parse="inward"  }
 <div>
  <h1><a href="{path='home/details/{url_title}'}"> {title}</a> </h1>
                     <hr />
<p>{desc}</p>
</div>
{/exp:channel:entries}





<?php
    $buffer = ob_get_contents();
    	
    ob_end_clean(); 
    
    return $buffer;
  }
  // END FUNCTION
}
// END CLASS
?>