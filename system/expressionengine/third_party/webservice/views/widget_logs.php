<ul>
<?php

foreach($logs as $item)
{
	//get the extra data
	$data = unserialize($item['data']);

	$link = $item['method'] != 'Webservice_service::delete_entry' ? cp_url('content_publish/entry_form', array('entry_id' => $data['id'])) : '#';

	echo '
	<li class="item"><a href="'.$link .'">
	Entry ID '.$data['id'].' '.$item['msg'].' 	
	</a></li>'
	;	
}

?>
</ul>