<?php
	$base_url = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=webservice'.AMP;
?>

<div class="clear_left">&nbsp;</div>
<p>
	<?php foreach($apis as $api):?>
		<?php if(isset($api->test) && $api->test):?>
		<span class="button" style="float:right;"><a id="new-channel" href="<?=$base_url?>method=testing_tools&api=<?=$api->name?>" class="less_important_bttn"><?=$api->label?></a></span>
		<?php endif; ?>
	<?php endforeach;?>
	<div class="clear"></div>
</p>

