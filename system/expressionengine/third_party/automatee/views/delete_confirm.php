<?php echo form_open($form_action)?>
<?php foreach($damned as $cron):?>
	<?php echo form_hidden('delete[]', $cron['id']); ?>
<?php endforeach;?>

<p class="notice"><?php echo lang('action_can_not_be_undone')?></p>

<h3><?php echo lang($cron_delete_question); ?></h3>
<p>
<?php foreach($damned AS $cron): ?>
	<?php echo $cron['name'];?><br />
<?php endforeach; ?>
</p>

<p>
	<?php echo form_submit(array('name' => 'submit', 'value' => lang('delete'), 'class' => 'submit'))?>
</p>

<?php echo form_close()?>