<?php $this->load->view('errors'); ?>
<?php 
echo lang('module_instructions'); ?>
<div class="clear_left shun"></div>

<?php echo form_open($query_base.'delete_cron_confirm', array('id'=>'my_accordion')); ?>

<div id="cli_crons">
	<?php 
if(count($crons) > 0): 
	$this->table->set_template($cp_pad_table_template);
	$this->table->set_heading(
		lang('name'),
		lang('schedule'),
		lang('status'),
		lang('last_ran'),
		lang('total_runs'),
		form_checkbox('select_all', 'true', FALSE, 'class="toggle_all_cron" id="select_all"').NBS.lang('delete', 'select_all')
	);
	
	
	foreach($crons as $cron)
	{
		$toggle = array(
				  'name'		=> 'toggle[]',
				  'id'		=> 'edit_box_'.$cron['id'],
				  'value'		=> $cron['id'],
				  'class'		=>'toggle_cron'
				  );
		
		$cron_details = $this->automatee_lib->parse_cron_string($cron['schedule']);
		$this->table->add_row(
				'<a href="'.$url_base.'view'.AMP.'id='.$cron['id'].'" rel="'.$cron['name'].'" id="cron_title_'.$cron['id'].'">'.$cron['name'].'</a>',
				(array_key_exists($cron['schedule'], $schedule_options) ? $schedule_options[$cron['schedule']] : '('.lang('Custom').') '.$cron['schedule']),
				($cron['active'] == '1' ? lang('active') : lang('inactive')),
				'<span id="date_'.$cron['id'].'">'.($cron['total_runs'] == '0' ? 'N/A' : date('F j, Y, g:i a', $cron['ran_at'])).'</span>',
				'<span id="total_'.$cron['id'].'" >'.$cron['total_runs'].'</span> <a href="'.$test_action_url.$cron['id'].'" id="run_cron_'.$cron['id'].'" rel="'.$cron['id'].'" class="test_cron">(Run)</a> <img src="'.$animated_url.'" id="animated_'.$cron['id'].'" style="display:none" />',
				form_checkbox($toggle)
		);
	}
	
	echo $this->table->generate();
	?>
	<?php else: ?>
	<p><?php echo lang('no_crons')?></p>
	<?php endif; ?>
</div>


<br />
<div class="tableFooter">
	<div class="tableSubmit">
		<?php echo form_submit(array('name' => 'submit', 'value' => lang('delete_selected'), 'class' => 'submit'));?>
	</div>
</div>	
<?php echo form_close()?>