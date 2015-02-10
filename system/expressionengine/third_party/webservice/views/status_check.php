<?php
	$base_url = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=webservice'.AMP;
?>

<div class="clear_left">&nbsp;</div>
<h3>XML-RPC</h3>
<p>XML-RPC is <b><?php if($xmlrpc):?>Enabled<?php else: ?>not Enabled (instead use the <a href="http://phpxmlrpc.sourceforge.net" target="_blank">PHPXMLRPC lib</a>)<?php endif;?></b></p>

<h3>SOAP</h3>
<p>SOAP is <b><?php if($soap):?>Enabled<?php else: ?>not Enabled<?php endif;?></b></p>

<h3>cURL</h3>
<p>cURL is <b><?php if($curl):?>Enabled<?php else: ?>not Enabled<?php endif;?></b></p>