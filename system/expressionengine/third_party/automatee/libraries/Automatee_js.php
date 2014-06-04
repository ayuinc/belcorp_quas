<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

 /**
 * mithra62 - Automat:ee
 *
 * @package		mithra62:Automatee
 * @author		Eric Lamb
 * @copyright	Copyright (c) 2012, mithra62, Eric Lamb.
 * @link		http://mithra62.com/projects/view/automat-ee/
 * @version		1.2
 * @filesource 	./system/expressionengine/third_party/automatee/
 */
 
 /**
 * Automat:ee - jQuery Library
 *
 * Jquery library
 *
 * @package 	mithra62:Automatee
 * @author		Eric Lamb
 * @filesource 	./system/expressionengine/third_party/automatee/libraries/Automatee_js.php
 */
class Automatee_js
{
	
	public function __construct()
	{
		$this->EE =& get_instance();
	}
	
	public function get_accordian_css()
	{
		return ' $("#my_accordion").accordion({autoHeight: false,header: "h3"}); ';
	}
	
	public function get_check_toggle()
	{
		return array(
			'$(".toggle_all_cron").toggle(
				function(){
					$("input.toggle_cron").each(function() {
						this.checked = true;
					});
				}, function (){
					var checked_status = this.checked;
					$("input.toggle_cron").each(function() {
						this.checked = false;
					});
				}
			);'		
		);		
	}

	public function get_test_cron($action_url)
	{
		//we have to include the script HTML
		return array(
		'

		$(".test_cron").click(function () {
			var cron_id = $(this).attr("rel");
			var image_id = "#animated_" + cron_id;
			var div_id = "#run_cron_" + cron_id;
			var total_id = "#total_" + cron_id;
			var cron_title_div = "#cron_title_" + cron_id;
			var cron_title = $(cron_title_div).attr("rel");
			
			$(image_id).show();
			$(div_id).hide();		
			$.ajax({
				url: "'.$action_url.'"+cron_id,
				context: document.body,
				success: function(xhr){
					alert(cron_title+" Cron: Complete");
					$(image_id).hide();
					$(div_id).show();

					var total = parseFloat($(total_id).html());
					$(total_id).html(total+1);
				},
				error: function(data, status, errorThrown) {
					alert(cron_title + " Cron: Failed with status "+ data.status +"\n" +errorThrown );
					$(image_id).hide();
					$(div_id).show();
					var total = parseFloat($(total_id).html());
					$(total_id).html(total+1);										
				}
			});			
			
			return false;
			
		});
		');
	}
		
	public function get_jquery_cron()
	{
		//we have to include the script HTML 
		return array(
		
					);
	}
	
	public function get_form_cron()
	{
		return array(
		'
		if($("#schedule").val() == "custom")
		{
			$("#schedule_custom").show();
		}
		if($("#type").val() == "module")
		{
			$("#command").hide();
			$("#plugin").hide();
			$("#module").show();
			$("#method").show();
			$("#command_label").html("'.lang('choose_module').'");
			$("#command_instructions").html("'.lang('choose_module_instructions').'");														
		}
		
		if($("#type").val() == "plugin")
		{
			$("#command").hide();
			$("#module").hide();
			$("#plugin").show();
			$("#method").show();
			$("#command_label").html("'.lang('choose_plugin').'");
			$("#command_instructions").html("'.lang('choose_plugin_instructions').'");														
		}
		
		if($("#type").val() == "cli")
		{
			$("#command_label").html("'.lang('cli_command').'");
			$("#command_instructions").html("'.lang('cli_command_instructions').'");
		}
		
		var def_assign = "0";
		$("#schedule").change(function(){
			var new_assign = $("#schedule").val();
			if(new_assign == def_assign || new_assign != "custom")
			{
				$("#schedule_custom").hide();
				$("#schedule_custom").val("");
			}
			else
			{
				$("#schedule_custom").show();
			}
		});
		
		var def_assign = "0";
		$("#type").change(function(){
			var type_val = $("#type").val();
			if(type_val == "cli")
			{
				$("#command_label").html("'.lang('cli_command').'");
				$("#command_instructions").html("'.lang('cli_command_instructions').'");
				$("#module").hide();
				$("#module").val("");
				$("#plugin").hide();
				$("#plugin").val("");
				$("#method").hide();
				$("#method").val("");
				$("#command").show();
				$("#command").val("");									
			}
			
			if(type_val == "url")
			{
				$("#command_label").html("'.lang('get_url_address').'");
				$("#command_instructions").html("'.lang('get_url_instructions').'");
				$("#module").hide();
				$("#module").val("");
				$("#plugin").hide();
				$("#plugin").val("");
				$("#method").hide();
				$("#method").val("");
				$("#command").show();
				$("#command").val("");					
			}
			
			if(type_val == "module")
			{
				$("#command_label").html("'.lang('choose_module').'");
				$("#command_instructions").html("'.lang('choose_module_instructions').'");
				$("#command").hide();
				$("#plugin").hide();
				$("#module").show();
				$("#method").show();
				$("#command").val("");	
			}
			
			if(type_val == "plugin")
			{
				$("#command_label").html("'.lang('choose_plugin').'");
				$("#command_instructions").html("'.lang('choose_plugin_instructions').'");
				$("#command").hide();
				$("#module").hide();
				$("#plugin").show();
				$("#method").show();
				$("#command").val("");	
			}							
		});	
		
		'

	);
	}

}