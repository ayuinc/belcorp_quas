<?php
	$base_url = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=webservice'.AMP;
?>

<div class="clear_left">&nbsp;</div>
<p>
	<span class="button" style="float:right;"><a id="new-channel" href="<?=$base_url?>method=add_member" class="less_important_bttn">Add API user</a></span>
	<div class="clear"></div>
</p>

<?php
$this->table->set_empty(lang('entry_id_nodata'));
$this->table->set_template($cp_table_template);
$this->table->set_heading(
		lang('webservice_member').'/'.lang('webservice_membergroup'),
		lang('webservice_services'),
		lang('webservice_apis'),
		lang('webservice_free_apis'),
		lang('webservice_active'),
		''
);

if(!empty($members))
{
	foreach($members as $key=>$val)
	{
		$this->table->add_row(
			$val['username'].$val['group_title'],
			str_replace('|', ', ', $val['services']),
			str_replace('|', ', ', $val['apis']),
			str_replace('|', ', ', $val['free_apis']),
			$val['active'],
			'<a href="'.$base_url.'method=show_member'.AMP.'webservice_id='.$val['webservice_id'].'">'.lang('webservice_show_channel').'</a> / <a href="'.$base_url.'method=delete_member'.AMP.'webservice_id='.$val['webservice_id'].'">'.lang('webservice_delete_channel').'</a>'
		);
	}
}
else 
{
	$this->table->add_row('','','','', '');
	
}
?>
<?=$this->table->generate();?>