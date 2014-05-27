<?php $this->load->view('errors'); ?>
<?php 

$tmpl = array (
	'table_open'          => '<table class="mainTable" border="0" cellspacing="0" cellpadding="0">',

	'row_start'           => '<tr class="even">',
	'row_end'             => '</tr>',
	'cell_start'          => '<td style="width:50%;">',
	'cell_end'            => '</td>',

	'row_alt_start'       => '<tr class="odd">',
	'row_alt_end'         => '</tr>',
	'cell_alt_start'      => '<td>',
	'cell_alt_end'        => '</td>',

	'table_close'         => '</table>'
);

$this->table->set_template($tmpl); 
$this->table->set_empty("&nbsp;");
?>
<div class="clear_left shun"></div>

<?php 
if(!isset($form_action))
{
	$form_action = 'add_cron';
}
echo form_open($query_base.$form_action, array('id'=>'my_accordion'));
?>
<input type="hidden" value="yes" name="go_cron_form" />

	<?php 
	
	//set form defaults
	$defaults = array();
	$defaults['name'] = (isset($cron['name']) ? $cron['name'] : FALSE);
	$defaults['description'] = (isset($cron['description']) ? $cron['description'] : FALSE);
	$defaults['status'] = (isset($cron['active']) ? $cron['active'] : '1');
	$defaults['type'] = (isset($cron['type']) ? $cron['type'] : 'url');
	$defaults['command'] = (isset($cron['command']) ? $cron['command'] : FALSE);
	$defaults['cron_module'] = (isset($cron['cron_module']) ? $cron['cron_module'] : FALSE);
	$defaults['cron_plugin'] = (isset($cron['cron_plugin']) ? $cron['cron_plugin'] : FALSE);
	$defaults['cron_method'] = (isset($cron['cron_method']) ? $cron['cron_method'] : FALSE);
	
	if(isset($cron['schedule']))
	{
		if(!array_key_exists($cron['schedule'], $schedule_options))
		{
			$defaults['schedule_custom'] = $cron['schedule'];
			$defaults['schedule'] = 'custom';
		}
		else
		{
			$defaults['schedule'] = $cron['schedule'];
			$defaults['schedule_custom'] = '';
		}
	}
	else
	{
		$defaults['schedule'] = '0 0 * * *';
		$defaults['schedule_custom'] = '';
	}

	$this->table->set_heading('&nbsp;',' ');
	$this->table->add_row('<label for="name">'.lang('name').'</label><div class="subtext">'.lang('name_instructions').'</div>', form_input('name', $defaults['name'], 'id="name"'). form_error('name'));
	$this->table->add_row('<label for="status">'.lang('status').'</label><div class="subtext">'.lang('status_instructions').'</div>', form_dropdown('status', $statuses, $defaults['status'], 'id="status"'). form_error('status'));
	$this->table->add_row('<label for="schedule">'.lang('schedule').'</label><div class="subtext">'.lang('schedule_instructions').'</div>', form_dropdown('schedule', $schedule_options, $defaults['schedule'], 'id="schedule"'). form_error('schedule') .form_input('schedule_custom', $defaults['schedule_custom'], 'id="schedule_custom" style="display:none; width:40%; margin-left:10px;"'));
	$this->table->add_row('<label for="type">'.lang('type').'</label><div class="subtext">'.lang('type_instructions').'</div>', form_dropdown('type', $cron_types, $defaults['type'], 'id="type"'). form_error('type'));	
	$this->table->add_row('<label for="command" id="command_label">'.lang('get_url_address').'</label><div class="subtext" id="command_instructions">'.lang('get_url_instructions').'</div>', 
		form_input('command', $defaults['command'], 'id="command"'). form_error('command').
		form_dropdown('cron_module', $installed_modules, $defaults['cron_module'], 'id="module" style="display:none"'). form_error('module').
		form_dropdown('cron_plugin', $installed_plugins, $defaults['cron_plugin'], 'id="plugin" style="display:none"'). form_error('plugin').
		form_input('cron_method', $defaults['cron_method'], 'id="method" style="display:none; width:40%; margin-left:10px;"'). form_error('method')
	);
	$this->table->add_row('<label for="description">'.lang('description').'</label><div class="subtext">'.lang('description_instructions').'</div>', form_textarea('description', $defaults['description'], 'id="description"'). form_error('description'));
	echo $this->table->generate();
	$this->table->clear();
	?>
<br />
<div class="tableFooter">
	<div class="tableSubmit">
		<?php echo form_submit(array('name' => 'submit', 'value' => lang('submit'), 'class' => 'submit'));?>
	</div>
</div>	
<?php echo form_close()?>