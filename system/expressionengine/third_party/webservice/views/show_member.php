<div class="clear_left">&nbsp;</div>

<?=form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=webservice'.AMP.'method=show_member'.AMP.'webservice_id='.$webservice_user->webservice_id, '', $hidden)?>

<?php
$this->table->set_template($cp_pad_table_template);
$this->table->set_heading(
    array('data' => lang('channel_preference'), 'style' => 'width:50%;'),
    lang('setting')
);

//build the checkbox
$services = '';
$services_selected = explode('|',$webservice_user->services);

foreach($connection_services as $key=>$val)
{
	$services .= '<span>'.form_checkbox('connection_services[]', $key, (in_array($key, $services_selected))).' '.$val.'</span><i><u>('.($urls[$key]).')</u></i>&nbsp;&nbsp;&nbsp;&nbsp;<br/>';
}
$api = '';
$apis_selected = explode('|',$webservice_user->apis);
foreach($apis as $key=>$val)
{
	$api .= form_checkbox('api[]', $key, (in_array($key, $apis_selected))).' '.$val.'&nbsp;&nbsp;&nbsp;';
}

// $free_api = '';
// $free_apis_selected = explode('|',$webservice_user->free_apis);
// foreach($free_apis as $key=>$val)
// {
// 	$free_api .= form_checkbox('free_api[]', $key, (in_array($key, $free_apis_selected))).' '.$val.'&nbsp;&nbsp;&nbsp;';
// }

if(isset($member->username))
{
	$this->table->add_row(lang('member', 'member'), '<b>'.$member->username.'</b>');
}
if(isset($membergroup->group_title))
{
	$this->table->add_row(lang('membergroup', 'membergroup'), '<b>'.$membergroup->group_title.'</b>');
}
$this->table->add_row(lang('connection_type', 'connection_type'), $services);
$this->table->add_row(lang('apis', 'apis'), $api);
//$this->table->add_row(lang('free_apis', 'free_apis'), $free_api);
$this->table->add_row(lang('active','active'), form_dropdown( 'active', $active, $webservice_user->active));
if(ee()->webservice_settings->item('debug'))
{
	$this->table->add_row(lang('log', 'log'), form_dropdown( 'logging', $logging, $webservice_user->logging));
}
$this->table->add_row(lang('Api Keys', 'api_keys'), form_textarea( 'api_keys', ($webservice_user->api_keys)));
// $this->table->add_row(lang('debug', 'debug'), form_dropdown( 'debug', $debug, $member->debug)); //@tmp disabled, not yet implemented

echo $this->table->generate();
?>

<p><?=form_submit('submit', lang('submit'), 'class="submit"')?></p>
<?php $this->table->clear()?>
<?=form_close()?>