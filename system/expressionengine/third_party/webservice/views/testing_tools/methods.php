<?php
$base_url = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=webservice'.AMP;
$_base_url = 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=webservice'.AMP;
?>

<div class="clear_left">&nbsp;</div>
<p>
	<span class="button" style="float:right;"><a id="new-channel" href="<?=$base_url?>method=testing_tools" class="less_important_bttn">Back</a></span>
	<?php foreach($methods as $method):?>
		<span class="button" style="float:right;"><a id="new-channel" href="<?=$base_url?>method=testing_tools&api=<?=$_GET['api']?>&api_method=<?=$method->method?>" class="less_important_bttn"><?=$method->name?></a></span>
	<?php endforeach;?>
<div class="clear"></div>
</p>