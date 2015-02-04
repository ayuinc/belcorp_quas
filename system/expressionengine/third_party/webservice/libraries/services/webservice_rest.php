<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * REST server class
 *
 * @package		webservice
 * @category	Modules
 * @author		Rein de Vries <info@reinos.nl>
 * @license  	http://reinos.nl/add-ons/commercial-license
 * @link        http://reinos.nl/add-ons//add-ons/entry-api
 * @copyright 	Copyright (c) 2014 Reinos.nl Internet Media
 */
 
/**
 * Include the config file
 */
require_once PATH_THIRD.'webservice/config.php';

class Webservice_rest
{	
	/*
	*	The username
	*/
	public $username;
	
	/*
	*	the password
	*/
	public $password;
	
	/*
	*	The postdata
	*/
	public $post_data;
	
	/*
	*	the channel
	*/
	public $post_data_channel;
	
	/*
	*	REST server
	*/
	private $server;

	/*
	*	Method using
	*/
	private $method;

	/*
	*	Class calling
	*/
	private $calling_class;
	
	/**
     * List all supported methods, the first will be the default format
     *
     * @var array
     */
    protected $_supported_formats = array(
        'xml' => 'application/xml',
        'json' => 'application/json',
        //'jsonp' => 'application/javascript',
        'serialized' => 'application/vnd.php.serialized',
        'php' => 'text/plain',
        //'html' => 'text/html',
        //'csv' => 'application/csv'
    );
	
	/**
     * The arguments for the GET request method
     *
     * @var array
     */
    protected $_get_args = array();

    /**
     * The arguments for the POST request method
     *
     * @var array
     */
    protected $_post_args = array();

    /**
     * The arguments for the PUT request method
     *
     * @var array
     */
    protected $_put_args = array();

    /**
     * The arguments for the DELETE request method
     *
     * @var array
     */
    protected $_delete_args = array();

    /**
     * The arguments from GET, POST, PUT, DELETE request methods combined.
     *
     * @var array
     */
    protected $_args = array();
	
	 /**
     * Determines if output compression is enabled
     *
     * @var boolean
     */
    protected $_zlib_oc = FALSE;

	// ----------------------------------------------------------------------
	
	/**
	 * Constructor
	 */
	public function __construct()
	{	
		ee()->load->library('webservice_lib');
        ee()->load->library('webservice_base_api');
		
		//load the helper
		ee()->load->helper('webservice_helper');
		ee()->load->helper('url');	

        //require the default settings
        require PATH_THIRD.'webservice/settings.php';

		$this->call_method(ee()->uri->segment(3));
	}

	// --------------------------------------------------------------------

	/**
	 * Call Method
	 *
	 * @param string $method
	 */
    public function call_method($method='')
    {        
        // start output buffer so we can catch any errors
        ob_start();

        //load all the methods 
        $api_name = ee()->webservice_base_api->api_type = ee()->webservice_lib->search_api_method_class($method);

		//get the api settings
		$api_settings =  ee()->webservice_lib->get_api($api_name);

		//no settings, no api
		if(!$api_settings)
		{
			//return response
			$this->response('API does not exist', 400);
		}

		//set the class
        $class = 'webservice_'.$api_name.'_static';

		//load from the webservice packaged
		if(strstr($api_settings->path, 'webservice/libraries/api/') != false)
		{
			//check if the file exists
			if(!file_exists($api_settings->path.'/'.$class.'.php'))
			{
				//return response
				$this->response('API does not exist', 400);
			}

			//load the api class
			ee()->load->library('api/'.$api_name.'/'.$class);
		}

		//we deal with a third party api for the webservice
		else
		{
			//set the class
			$class = 'webservice_'.$api_name.'_api_static';

			//check if the file exists
			if(!file_exists($api_settings->path.'/libraries/'.$class.'.php'))
			{
				//return response
				$this->response('API does not exist', 400);
			}

			//load the package path
			ee()->load->add_package_path($api_settings->path.'/');
			//load the api class
			ee()->load->library($class);
		}

        // check if method exists
        if (!method_exists(ucfirst($class), $method))
        {
            $this->response('Method does not exist', 400);
        }

        /** ---------------------------------------
        /** From here we do some Specific things
        /** ---------------------------------------*/
        //-----------------------------------------------------------------------------------------------------------------------------

        // let's learn about the request
        $this->request = new stdClass();
        
        // Is it over SSL?
        $this->request->ssl = $this->_detect_ssl();
        
        // How is this request being made? POST, DELETE, GET, PUT?
        $this->request->method = $this->_detect_method();
        
        // Create argument container, if nonexistent
        if ( ! isset($this->{'_'.$this->request->method.'_args'}))
        {
            $this->{'_'.$this->request->method.'_args'} = array();
        }
        
        // This library is bundled with REST_Controller 2.5+, but will eventually be part of CodeIgniter itself
        ee()->load->library('format');
        
        // Set up our GET variables
        $this->_get_args = array_merge($this->_get_args, ee()->uri->ruri_to_assoc());

        // Try to find a format for the request (means we have a request body)
        $this->request->format = $this->_detect_input_format();

        // Some Methods cant have a body
        $this->request->body = NULL;
        
        $this->{'_parse_' . $this->request->method}();

        // Now we know all about our request, let's try and parse the body if it exists
        if ($this->request->format and $this->request->body)
        {
            $this->request->body = ee()->format->factory($this->request->body, $this->request->format)->to_array();
            // Assign payload arguments to proper method container
            $this->{'_'.$this->request->method.'_args'} = $this->request->body;
        }
        
         // Merge both for one mega-args variable
        $this->_args = array_merge($this->_get_args, $this->_put_args, $this->_post_args, $this->_delete_args, $this->{'_'.$this->request->method.'_args'});

        // Which format should the data be returned in?
        $this->response = new stdClass();
        $this->response->format = $this->_detect_output_format();

        // Which format should the data be returned in?
        $this->response->lang = $this->_detect_lang();
  
        //parse the vars
        $require = array('data');
        if($this->request->method == 'get')
        {
           $require = array(); 
        }
        $vars = $this->_get_vars($this->_detect_method(), $require);
        $vars['auth'] = isset($vars['auth']) ? $vars['auth'] : array();
        $vars['data'] = isset($vars['data']) ? $vars['data'] : array();
        
        // Auth via http
        $auth_vars = $this->_prepare_basic_auth();
        if($auth_vars != false)
        {
            $vars['auth'] = $auth_vars;
        }

        /** ---------------------------------------
        /** End of the specific things
        /** ---------------------------------------*/
        //-----------------------------------------------------------------------------------------------------------------------------

        $error_auth = false;
        $return_data = array(
            'message'           => '',
            'code_http'         => 200,
			'success'			=> false
        );

        //set the site_id
        $site_id = $vars['data']['site_id'] = isset($vars['data']['site_id']) ? $vars['data']['site_id'] : 1;

        //if the api needs to be auth, do it here
        if(isset($api_settings->auth) && (bool) $api_settings->auth)
        {
            /** ---------------------------------------
            /**  Run some default checks
            /**  if the site id is given then switch to that site, otherwise use site_id = 1
            /** ---------------------------------------*/
            $default_checks = ee()->webservice_base_api->default_checks($vars['auth'], $method, $site_id);
     
            if( ! $default_checks['succes'])
            { 
                $error_auth = true;
                $return_data = array_merge($return_data, $default_checks['message']);
            }
        }
		//then the first array is not auth but data
		//not needed in rest, just in soap
//		else
//		{
//			$vars['data'] = $vars['auth'];
//		}

        if($error_auth === false)
        {
            //call the method
            $result = call_user_func(array($class, $method), $vars['data'], 'rest');

            //unset the response txt
            if(isset($result['response']))
			{
				unset($result['response']);
			}

            //merge with the default values
            $return_data = array_merge($return_data, $result);   
        }

        //add a log
        ee()->webservice_model->add_log(ee()->session->userdata('username'), $return_data, $method, 'rest', ee()->webservice_base_api->servicedata);

        //unset the http code
        if(isset($return_data['code_http']))
        {
            $http_code = $return_data['code_http'];
            unset($return_data['code_http']);
        }

		//set succes as int
		//$return_data['success'] = (int)$return_data['success'];

        //return
        $this->response($return_data, $http_code);
    }

    // --------------------------------------------------------------------
        
    /**
     * Get Variables
     */
    private function _get_vars($method, $required=array(), $defaults=array())
    {
        $vars = array();

        // populate the variables
        foreach ($this->_args as $key => $val) 
        {
            $vars[$key] = $this->{$method}($key);
        }

        $missing = array();

        // check if any required variables are not set or blank
        foreach ($required as $key) 
        {
            if (!isset($vars[$key]) OR $vars[$key] == '')
            {
                $missing[] = $key;
            }
        }

        if (count($missing))
        {
            $this->response('Required variables missing: '.implode(', ', $missing), 400);
        }

        // populate fields with defaults if not set
        foreach ($defaults as $key => $val) 
        {
            if (!isset($vars[$key]))
            {
                $vars[$key] = $val;
            }
        }

        return $vars;
    }
	
	/**
     * Response
     *
     * Takes pure data and optionally a status code, then creates the response.
     *
     * @param array $data
     * @param null|int $http_code
     */
    public function response($data = array(), $http_code = null)
    {

        // If data is empty and not code provide, error and bail
        if (empty($data) && $http_code === null)
        {
            $http_code = 404;

            // create the output variable here in the case of $this->response(array());
            $output = NULL;
        }

        // If data is empty but http code provided, keep the output empty
        else if (empty($data) && is_numeric($http_code))
        {
            $output = NULL;
        }

        // Otherwise (if no data but 200 provided) or some data, carry on camping!
        else
        {
            // Is compression requested?
            if (ee()->config->item('compress_output') === TRUE && $this->_zlib_oc == FALSE)
            {
                if (extension_loaded('zlib'))
                {
                    if (isset($_SERVER['HTTP_ACCEPT_ENCODING']) AND strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== FALSE)
                    {
                        ob_start('ob_gzhandler');
                    }
                }
       		}

            is_numeric($http_code) OR $http_code = 200;

            //no data 
            if(!isset($this->response->format))
            {
                $output = $data;
            }
            else
            {
                // If the format method exists, call and return the output in that format
                if (method_exists($this, '_format_'.$this->response->format))
                {
                    // Set the correct format header
                    header('Content-Type: '.$this->_supported_formats[$this->response->format]);

                    $output = $this->{'_format_'.$this->response->format}($data);
                }

                // If the format method exists, call and return the output in that format
                elseif (method_exists(ee()->format, 'to_'.$this->response->format))
                {
                    // Set the correct format header
                    header('Content-Type: '.$this->_supported_formats[$this->response->format]);

                    $output = ee()->format->factory($data)->{'to_'.$this->response->format}();
                }

                // Format not supported, output directly
                else
                {
                    $output = $data;
                }
            }
        }

        header('HTTP/1.1: ' . $http_code);
        header('Status: ' . $http_code);

        // If zlib.output_compression is enabled it will compress the output,
        // but it will not modify the content-length header to compensate for
        // the reduction, causing the browser to hang waiting for more data.
        // We'll just skip content-length in those cases.
        if ( ! $this->_zlib_oc && ! ee()->config->item('compress_output'))
        {
            header('Content-Length: ' . strlen($output));
        }

        //set the header if set from the cp
        if(ee()->webservice_settings->item('rest_output_header') != '')
        {
            header(ee()->webservice_settings->item('rest_output_header'));
        }

        exit($output);
    }
	
	 // ----------------------------------------------------------------------

	 /**
	 * Detect the method
	 */
	protected function _detect_method()
    {
		$method = strtolower(ee()->input->server('REQUEST_METHOD'));
		
		if (ee()->config->item('enable_emulate_request')) 
		{
			if (ee()->input->post('_method')) 
			{
				$method = strtolower(ee()->input->post('_method'));
			} 
			else if (ee()->input->server('HTTP_X_HTTP_METHOD_OVERRIDE')) 
			{
				$method = strtolower(ee()->input->server('HTTP_X_HTTP_METHOD_OVERRIDE'));
			}      
		}
		
		if (in_array($method, array('get', 'delete', 'post', 'put'))) 
		{
			return $method;
		}
		return 'get';
	}
	
	/*
     * Detect SSL use
     *
     * Detect whether SSL is being used or not
     */
    protected function _detect_ssl()
    {
        return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on");
    }
	
	/**
     * Retrieve a value from the GET request arguments.
     *
     * @param string $key The key for the GET request argument to retrieve
     * @param boolean $xss_clean Whether the value should be XSS cleaned or not.
     * @return string The GET argument value.
     */
    public function get($key = NULL, $xss_clean = TRUE)
    {
        if ($key === NULL)
        {
            return $this->_get_args;
        }

        return array_key_exists($key, $this->_get_args) ? $this->_xss_clean($this->_get_args[$key], $xss_clean) : FALSE;
    }

    /**
     * Retrieve a value from the POST request arguments.
     *
     * @param string $key The key for the POST request argument to retrieve
     * @param boolean $xss_clean Whether the value should be XSS cleaned or not.
     * @return string The POST argument value.
     */
    public function post($key = NULL, $xss_clean = TRUE)
    {
        if ($key === NULL)
        {
            return $this->_post_args;
        }

        return array_key_exists($key, $this->_post_args) ? $this->_xss_clean($this->_post_args[$key], $xss_clean) : FALSE;
    }

    /**
     * Retrieve a value from the PUT request arguments.
     *
     * @param string $key The key for the PUT request argument to retrieve
     * @param boolean $xss_clean Whether the value should be XSS cleaned or not.
     * @return string The PUT argument value.
     */
    public function put($key = NULL, $xss_clean = TRUE)
    {
        if ($key === NULL)
        {
            return $this->_put_args;
        }

        return array_key_exists($key, $this->_put_args) ? $this->_xss_clean($this->_put_args[$key], $xss_clean) : FALSE;
    }

    /**
     * Retrieve a value from the DELETE request arguments.
     *
     * @param string $key The key for the DELETE request argument to retrieve
     * @param boolean $xss_clean Whether the value should be XSS cleaned or not.
     * @return string The DELETE argument value.
     */
    public function delete($key = NULL, $xss_clean = TRUE)
    {
        if ($key === NULL)
        {
            return $this->_delete_args;
        }

        return array_key_exists($key, $this->_delete_args) ? $this->_xss_clean($this->_delete_args[$key], $xss_clean) : FALSE;
    }
	
	 /*
     * Detect input format
     *
     * Detect which format the HTTP Body is provided in
     */
    protected function _detect_input_format()
    {
        if (ee()->input->server('CONTENT_TYPE'))
        {
            // Check all formats against the HTTP_ACCEPT header
            foreach ($this->_supported_formats as $format => $mime)
            {
                if (strpos($match = ee()->input->server('CONTENT_TYPE'), ';'))
                {
                    $match = current(explode(';', $match));
                }

                if ($match == $mime)
                {
                    return $format;
                }
            }
        }

        return NULL;
    }
	
	/**
     * Parse GET
     */
    protected function _parse_get()
    {
        // Grab proper GET variables
        parse_str(parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY), $get);

        // Merge both the URI segments and GET params
        $this->_get_args = array_merge($this->_get_args, $get);
    }

    /**
     * Parse POST
     */
    protected function _parse_post()
    {
        $this->_post_args = $_POST;

        $this->request->format and $this->request->body = file_get_contents('php://input');
    }

    /**
     * Parse PUT
     */
    protected function _parse_put()
    {
        // It might be a HTTP body
        if ($this->request->format)
        {
            $this->request->body = file_get_contents('php://input');
        }

        // If no file type is provided, this is probably just arguments
        else
        {
            parse_str(file_get_contents('php://input'), $this->_put_args);
        }
    }

    /**
     * Parse DELETE
     */
    protected function _parse_delete()
    {
        // Set up out DELETE variables (which shouldn't really exist, but sssh!)
        parse_str(file_get_contents('php://input'), $this->_delete_args);
    }
	
	 /**
     * Process to protect from XSS attacks.
     *
     * @param string $val The input.
     * @param boolean $process Do clean or note the input.
     * @return string
     */
    protected function _xss_clean($val, $process)
    {
        return $process ? ee()->security->xss_clean($val) : $val;
    }
	
	/**
     * Detect format
     *
     * Detect which format should be used to output the data.
     *
     * @return string The output format.
     */
    protected function _detect_output_format()
    {
        //pattern = '/\.('.implode('|', array_keys($this->_supported_formats)).')$/';

        // Check if a file extension is used
        // if (preg_match($pattern, ee()->uri->uri_string(), $matches))
        // {
            // return $matches[1];
        // }
        $pattern = array_keys($this->_supported_formats);
        if(in_array(ee()->uri->segment(4), $pattern))
		{
			return ee()->uri->segment(4);
		}

        // Check if a file extension is used
        // elseif ($this->_get_args AND !is_array(end($this->_get_args)) AND preg_match($pattern, end($this->_get_args), $matches))
        // {
            // // The key of the last argument
            // $last_key = end(array_keys($this->_get_args));
// 
            // // Remove the extension from arguments too
            // $this->_get_args[$last_key] = preg_replace($pattern, '', $this->_get_args[$last_key]);
            // $this->_args[$last_key] = preg_replace($pattern, '', $this->_args[$last_key]);
// 
            // return $matches[1];
        // }

        // A format has been passed as an argument in the URL and it is supported
        // if (isset($this->_get_args['format']) AND array_key_exists($this->_get_args['format'], $this->_supported_formats))
        // {
            // return $this->_get_args['format'];
        // }

        // Otherwise, check the HTTP_ACCEPT (if it exists and we are allowed)
        // if ($this->config->item('rest_ignore_http_accept') === FALSE AND $this->input->server('HTTP_ACCEPT'))
        // {
            // // Check all formats against the HTTP_ACCEPT header
            // foreach (array_keys($this->_supported_formats) as $format)
            // {
                // // Has this format been requested?
                // if (strpos($this->input->server('HTTP_ACCEPT'), $format) !== FALSE)
                // {
                    // // If not HTML or XML assume its right and send it on its way
                    // if ($format != 'html' AND $format != 'xml')
                    // {
// 
                        // return $format;
                    // }
// 
                    // // HTML or XML have shown up as a match
                    // else
                    // {
                        // // If it is truly HTML, it wont want any XML
                        // if ($format == 'html' AND strpos($this->input->server('HTTP_ACCEPT'), 'xml') === FALSE)
                        // {
                                // return $format;
                        // }
// 
                        // // If it is truly XML, it wont want any HTML
                        // elseif ($format == 'xml' AND strpos($this->input->server('HTTP_ACCEPT'), 'html') === FALSE)
                        // {
                                // return $format;
                        // }
                    // }
                // }
            // }
        // } // End HTTP_ACCEPT checking

        // Well, none of that has worked! Let's see if the controller has a default
        // if ( ! empty($this->rest_format))
        // {
            // return $this->rest_format;
        // }

        // Just use the default format
        return 'json';
    }

 	/**
     * Detect language(s)
     *
     * What language do they want it in?
     *
     * @return null|string The language code.
     */
    protected function _detect_lang()
    {
        if ( ! $lang = ee()->input->server('HTTP_ACCEPT_LANGUAGE'))
        {
                return NULL;
        }

        // They might have sent a few, make it an array
        if (strpos($lang, ',') !== FALSE)
        {
            $langs = explode(',', $lang);

            $return_langs = array();
            $i = 1;
            foreach ($langs as $lang)
            {
                // Remove weight and strip space
                list($lang) = explode(';', $lang);
                $return_langs[] = trim($lang);
            }

            return $return_langs;
        }

        // Nope, just return the string
        return $lang;
    }
	
	 /**
     * @todo document this.
     */
    protected function _prepare_basic_auth()
    {
        // If whitelist is enabled it has the first chance to kick them out
        // if (config_item('rest_ip_whitelist_enabled'))
        // {
                // $this->_check_whitelist_auth();
        // }

        $username = '';
        $password = '';

        // mod_php
        if (ee()->input->server('PHP_AUTH_USER'))
        {
            $username = ee()->input->server('PHP_AUTH_USER');
            $password = ee()->input->server('PHP_AUTH_PW');
        }

        // most other servers
        elseif (ee()->input->server('HTTP_AUTHENTICATION'))
        {
            if (strpos(strtolower(ee()->input->server('HTTP_AUTHENTICATION')), 'basic') === 0)
            {
                list($username, $password) = explode(':', base64_decode(substr(ee()->input->server('HTTP_AUTHORIZATION'), 6)));
            }
        }

        if($username != '' && $password != '')
        {
            return array('username' => $username, 'password' => $password);
        }

        return false;
        
    }


}
/* End of file webservice_rest.php */
/* Location: /system/expressionengine/third_party/webservice/libraries/webservice_rest.php */