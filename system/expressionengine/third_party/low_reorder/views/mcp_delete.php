<form method="post" action="<?=$base_url?>&amp;method=delete">
	<div>
		<input type="hidden" name="set_id" value="<?=$set_id?>" />
		<input type="hidden" name="<?=$csrf_token_name?>" value="<?=$csrf_token_value?>" />
	</div>
	<p>
		<?=sprintf(lang('delete_set_confirm_message'), $set_label)?>
	</p>
	<p>
		<input type="submit" class="submit" value="<?=lang('delete_set_confirm')?> &ldquo;<?=htmlspecialchars($set_label)?>&rdquo;" />
		<a style="margin-left:20px" class="cancel" href="<?=$base_url?>"><?=lang('cancel_go_back')?></a>
	</p>
</form>