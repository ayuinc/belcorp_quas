<style>
	.api_settings {
		border-bottom: 1px solid #D0D7DF;
		border-left: 1px solid #D0D7DF;
	}
</style>

<script>
	$(function(){
		var new_tr = false;
		var settings = '';

		$('[name="member_id"]').change(function(){
			if($(this).val() != 0)
			{
				$('[name="membergroup_id"]').attr('disabled', true);
			} else {
				$('[name="membergroup_id"]').attr('disabled', false);
			}
		});

		$('[name="membergroup_id"]').change(function(){
			if($(this).val() != 0)
			{
				$('[name="member_id"]').attr('disabled', true);
			} else {
				$('[name="member_id"]').attr('disabled', false);
			}
		});

		$('form').submit(function(){
			$('[name="member_id"]').attr('disabled', false);
			$('[name="membergroup_id"]').attr('disabled', false);
		});

		
	});
</script>


<div class="clear_left">&nbsp;</div>

<?=form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=webservice'.AMP.'method=add_member')?>

<?php
$this->table->set_template($cp_pad_table_template);
$this->table->set_heading(
    array('data' => lang('channel_preference'), 'style' => 'width:50%;'),
    lang('setting')
);

//build the checkbox
$services = '';
foreach($connection_services as $key=>$val)
{
	$services .= '<span>'.form_checkbox('connection_services[]', $key).' '.$val.'</span> <i><u>('.($urls[$key]).')</u></i>&nbsp;&nbsp;&nbsp;&nbsp;<br/>';
}
$api = '';
foreach($apis as $key=>$val)
{
	$api .= form_checkbox('api[]', $key).' '.$val.'&nbsp;&nbsp;&nbsp;';
}
// $free_api = '';
// foreach($free_apis as $key=>$val)
// {
// 	$free_api .= form_checkbox('free_api[]', $key).' '.$val.'&nbsp;&nbsp;&nbsp;';
// }

$this->table->add_row(lang('Member', 'member'), form_dropdown( 'member_id', $members, ''));
$this->table->add_row(lang('Membergroup', 'Membergroup'), form_dropdown( 'membergroup_id', $membergroups, ''));
$this->table->add_row(lang('connection_type', 'connection_type'), $services);
$this->table->add_row(lang('apis', 'apis'), $api);
//$this->table->add_row(lang('free_apis', 'free_apis'), $free_api);
$this->table->add_row(lang('active', 'active'), form_dropdown( 'active', $active, ''));
if(ee()->webservice_settings->item('debug'))
{
	$this->table->add_row(lang('log', 'log'), form_dropdown('logging', $logging, ''));
}
$this->table->add_row(lang('Api Keys', 'api_keys'), form_textarea( 'api_keys', ''));

echo $this->table->generate();
?>

<p><?=form_submit('submit', lang('submit'), 'class="submit"')?></p>
<?php $this->table->clear()?>
<?=form_close()?>