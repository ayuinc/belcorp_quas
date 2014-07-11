<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$ip_de_la_pagina='http://192.241.228.248/';
$path_de_la_pagina='/opt/site/html/';

$config['app_version'] = '273';
$config['install_lock'] = "";
$config['license_number'] = "5633-4696-2443-5683";
$config['debug'] = '1';
$config['cp_url'] = $ip_de_la_pagina.'admin.php';
$config['doc_url'] = "http://ellislab.com/expressionengine/user-guide/";
$config['is_system_on'] = "y";
$config['allow_extensions'] = 'y';
$config['site_label'] = 'Belcorp';
$config['cookie_prefix'] = '';
$config['base_url']	= '';
$config['index_page'] = "";
$config['uri_protocol']	= 'AUTO';
$config['url_suffix'] = '';
$config['language']	= 'english';
$config['charset'] = 'UTF-8';
$config['enable_hooks'] = FALSE;
$config['subclass_prefix'] = 'EE_';
$config['permitted_uri_chars'] = 'a-z 0-9~%.:_\\-';
$config['enable_query_strings'] = FALSE;
$config['directory_trigger'] = 'D';
$config['controller_trigger'] = 'C';
$config['function_trigger'] = 'M';
$config['log_threshold'] = 0;
$config['log_path'] = '';
$config['log_date_format'] = 'Y-m-d H:i:s';
$config['cache_path'] = '';
$config['encryption_key'] = '';
$config['global_xss_filtering'] = FALSE;
$config['csrf_protection'] = FALSE;
$config['compress_output'] = FALSE;
$config['time_reference'] = 'local';
$config['rewrite_short_tags'] = TRUE;
$config['proxy_ips'] = "";


/*$conf['enable_throttling'] = "y";
$conf['max_page_loads'] = "250";
$conf['time_interval'] = "10";
$conf['lockout_time'] = "1";*/


//ASEGURAN MOVER SIN TRAQUEAR
$config['site_url'] = $ip_de_la_pagina;
$config['tmpl_file_basepath']   = $path_de_la_pagina."templates/";
$config['theme_folder_url'] = $ip_de_la_pagina."themes/";
$config['theme_folder_path'] = $path_de_la_pagina."themes/";
$config['captcha_url'] = $ip_de_la_pagina."images/captchas/";
$config['captcha_path'] = $path_de_la_pagina."images/captchas/";
$config['emoticon_url'] = $ip_de_la_pagina."images/smileys/";
$config['avatar_url'] = $ip_de_la_pagina."images/avatars/";
$config['avatar_path'] = $path_de_la_pagina."images/avatars/";
$config['photo_url'] = $ip_de_la_pagina."images/member_photos/";
$config['photo_path'] = $path_de_la_pagina."images/member_photos/";
$config['sig_img_url'] = $ip_de_la_pagina."images/signature_attachments/";
$config['sig_img_path'] = $path_de_la_pagina."images/signature_attachments/";
$config['upload_preferences'] = array(
    1 => array(                                                            // ID of upload destination
        'name'        => 'Image Uploads',                          // Display name in control panel
        'server_path' => $path_de_la_pagina.'images/uploads/', // Server path to upload directory
        'url'         => $ip_de_la_pagina.'images/uploads/'      // URL of upload directory
    )
);


/* End of file config.php */
/* Location: ./system/expressionengine/config/config.php */