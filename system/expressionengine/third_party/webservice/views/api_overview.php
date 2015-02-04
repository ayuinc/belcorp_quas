<div class="clear_left">&nbsp;</div>
<div id="save_settings">
    
    <?php
    
    $this->table->set_template($cp_pad_table_template);
    $this->table->set_heading(
        array('data' => lang(WEBSERVICE_MAP.'_api_label'), 'style' => 'width:75%;'),
        array('data' => lang(WEBSERVICE_MAP.'_un_install'), 'style' => 'width:15%;')
    );

    foreach ($apis as $key => $val)
    {
       $this->table->add_row($val->label.' '.(isset($val->version) ? '<small>(v'.$val->version.')</small>' : '' ), ($val->enabled ? lang('installed') : lang('uninstalled')));
        //$this->table->add_row($val->label.' '.(isset($val->version) ? '<small>(v'.$val->version.')</small>' : '' ), ($val->enabled ? '<a href="">'.lang('uninstall').'</a>' : '<a href="">'.lang('install').'</a>'));
    }
    echo $this->table->generate();
    $this->table->clear();

    ?>

    <?php $this->table->clear()?>
</div>