<?php
$this->load->view('form_cron',array('cron' => $cron_data, 'form_action' => 'view'.AMP.'id='.$cron_data['id']));