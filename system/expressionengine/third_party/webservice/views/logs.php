<?php

	$base_url = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.WEBSERVICE_MAP.AMP;
?>
	<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/highlight.js/8.3/styles/default.min.css">
	<script src="//cdnjs.cloudflare.com/ajax/libs/highlight.js/8.3/highlight.min.js"></script>

<?php if (!isset($_GET['tbl_offset'])):?>
<div class="clear_left">&nbsp;</div>
	<p>
		<span class="button" style="float:right;"><a href="<?=$base_url?>method=logs&clear_log=1" class="less_important_bttn">Clear logs</a></span>
	<div class="clear"></div>
</p>
	<div style="display:none;" id="show_queries_popupt" title="Queries"></div>
	<script>
		$(function(){

			//show trash item
			$(document).on('click', '.show_queries', function(){
				$obj = $(this);
				//change icon to loader
				$obj.hide().parent().find('.loader').show();
				//ajax
				var url = '<?=$ajax_url?>&function=show_queries&log_id='+$obj.data('log-id');

				$.post(url, function(html){
					//load the dialog
					$('#show_queries_popupt').html(html).dialog({
						open: function(){
							$('pre code').each(function(i, block) {
								hljs.highlightBlock(block);
							});
						},
						modal: true,
						width:'80%',
						height: 500,
						buttons: {
							"Close": function() {
								$(this).dialog("close");
							}
						},
						close: function( event, ui ) {
							//change icon back
							$obj.show().parent().find('.loader').hide();
						}
					});
				});
			});
		});
	</script>
<?php endif;?>

<?php
$this->table->set_empty(lang(WEBSERVICE_MAP.'_nodata'));
$this->table->set_template($cp_table_template);

$this->table->set_columns($table_headers);
$data = $this->table->datasource('_logs_data');
echo $data['table_html'];
echo $data['pagination_html'];
?>