<?php 
 $base_url = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=webservice'.AMP;
?>

<div class="clear_left">&nbsp;</div>

<?=form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=webservice'.AMP.'method=delete_member')?>

	<input type="hidden" name="confirm" value="ok"/>
	<input type="hidden" name="webservice_id" value="<?=$webservice_id?>"/>

	<p><strong><?=lang('webservice_delete_check')?></strong></p>
	<p class="notice"><?=lang('webservice_delete_check_notice')?></p>

	<input type="submit" class="submit" value="<?=lang('delete')?>" name="submit">
	</p>
</form>
