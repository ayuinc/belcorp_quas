<div class="clear_left">&nbsp;</div>

<?=form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=webservice'.AMP.'method=settings')?>

<?php
$this->table->set_template($cp_pad_table_template);
$this->table->set_heading(
    array('data' => lang('webservice_preference'), 'style' => 'width:50%;'),
    lang('setting')
);
foreach ($settings['backup'] as $key => $val)
{
	//subtext
	$subtext = '';
	if(is_array($val))
	{
		$subtext = isset($val[1]) ? '<div class="subtext">'.$val[1].'</div>' : '' ;
		$val = $val[0];
	}
    $this->table->add_row(lang($key, $key).$subtext, $val);
}
echo $this->table->generate();
?>


<p><?=form_submit('submit', lang('submit'), 'class="submit"')?></p>
<?php $this->table->clear()?>
<?=form_close()?>