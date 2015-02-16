<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Memberlist Class
 *
 * @package     ExpressionEngine
 * @category    Plugin
 * @author      Gianfranco Montoya 
 * @copyright   Copyright (c) 2014, Gianfranco Montoya 
 * @link        http://www.ayuinc.com/
 */

$plugin_info = array(
    'pi_name'         => 'Authlms',
    'pi_version'      => '1.0',
    'pi_author'       => 'Gianfranco Montoya ',
    'pi_author_url'   => 'http://www.ayuinc.com/',
    'pi_description'  => 'Allow sign_in on the LMS',
    'pi_usage'        => Authlms::usage()
);
            
class Authlms 
{

    var $return_data = "";
    // --------------------------------------------------------------------

        /**
         * Memberlist
         *
         * This function returns a list of members
         *
         * @access  public
         * @return  string
         */
    public function __construct(){
    }

    // --------------------------------------------------------------------

    /**
     * Usage
     *
     * This function describes how the plugin is used.
     *
     * @access  public
     * @return  string
     */
    public static function usage()
    {
        ob_start();  ?>
        The Memberlist Plugin simply outputs a
        list of 15 members of your site.

            {exp:Authlms}

        This is an incredibly simple Plugin.
            <?php
        $buffer = ob_get_contents();
        ob_end_clean();
        return $buffer;
    }
    // END

    public function auth_lms_first(){
        /*
include_once 'nusoap/lib/nusoap.php';
        $username= ee()->TMPL->fetch_param('username');
        //$username= "pellanoire";
        $id_curse= ee()->TMPL->fetch_param('id_curse');
        //instantiate the NuSOAP class and define the web service URL:
        $client = new nusoap_client('http://miscursosucb.belcorp.biz/auth/belcorpws/belcorpws_server.php?wsdl', 'WSDL');
        //check if there were any instantiation errors, and if so stop execution with an error message:
        $error = $client->getError();
        if ($error) {
          die("client construction error: {$error}\n");
        }
        $param = array('username' => $username);
        //perform a function call without parameters:
        $answer = $client->call('login_usuario', $param);
        //check if there were any call errors, and if so stop execution with some error messages:
        $error = $client->getError();
        if ($error) {
          print_r($client->response);
          print_r($client->getDebug());
          die();
        }
        $url='http://miscursosucb.belcorp.biz/auth/belcorpws/client/client.php?usuario='.$username.'&token='.$answer.'&curso='.$id_curse;
*/
		
		$username= ee()->TMPL->fetch_param('username');
		$id_course= ee()->TMPL->fetch_param('id_curse');
		
		$query = ee()->db
	    				->select('DNI')
	    				->where('UsuarioRed', $username)
						->get('exp_usuarios');
		$user = $query->row();
		$dni = $user->DNI;

        $useragent = $_SERVER['HTTP_USER_AGENT'];
        $date = date("d-H");
        $token = hash('sha256', $dni.$useragent.$date);
        $url = 'http://qasucb.cyzone.com/moodlecolaboradores/auth/belcorpsso/login/login.php?usuario=' . $username . '&token=' . $token . '&course=' . $id_course;
        
        //output the response (in the form of a multidimensional array) from the function call:
        return '{exp:redirecturl url="'.$url.'"}';
        //header('Location: http://miscursosucb.belcorp.biz/auth/belcorpws/client/client.php?usuario=peppinedo&token=ABCD&curso=24' );
        /*
        $data_string = json_encode($data, true);
        $url = 'http://190.41.151.102/Infhotel/ServiceReservaWeb.svc/InsertReserva';
        //  Initiate curl
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data_string)); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);     
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_HTTPHEADER,array(
            'Content-Type: application/json', 'charset=utf-8')
        ); 
        $result = curl_exec($ch);
        curl_close($ch);
        return $result."  ".$data_string; */
    }

    public function auth_lms_access_profile(){
        // include_once 'nusoap/lib/nusoap.php';
        // $username= ee()->TMPL->fetch_param('username');
        // //$username= "mariagomez";
        // //$username= "pechvillaran";
        // $screen_name= ee()->TMPL->fetch_param('screen_name');
        // //instantiate the NuSOAP class and define the web service URL:
        // $client = new nusoap_client('http://miscursosucb.belcorp.biz/auth/belcorpws/belcorpws_server.php?wsdl', 'WSDL');
        // //check if there were any instantiation errors, and if so stop execution with an error message:
        // $error = $client->getError();
        // if ($error) {
        //   die("client construction error: {$error}\n");
        // }
        // $param = array('username' => $username);
        // //perform a function call without parameters:
        // $answer = $client->call('login_usuario', $param);
        // //check if there were any call errors, and if so stop execution with some error messages:
        // $error = $client->getError();
        // if ($error) {
        //   print_r($client->response);
        //   print_r($client->getDebug());
        //   die();
        // }
        // $url='http://miscursosucb.belcorp.biz/auth/belcorpws/client/clientprofile.php?usuario='.$username.'&token='.$answer;
        // //$url='http://miscursosucb.belcorp.biz/auth/belcorpws/client/clientprofile.php?usuario=peppinedo&token='.$answer;
        // //output the response (in the form of a multidimensional array) from the function call:
        // return '{exp:redirecturl url="'.$url.'"}';


    	$username= ee()->TMPL->fetch_param('username');
		
		$query = ee()->db
	    				->select('DNI')
	    				->where('UsuarioRed', $username)
						->get('exp_usuarios');
		$user = $query->row();
		$dni = $user->DNI;

        $useragent = $_SERVER['HTTP_USER_AGENT'];
        $date = date("d-H");
        $token = hash('sha256', $dni.$useragent.$date);
        $url = 'http://qasucb.cyzone.com/moodlecolaboradores/auth/belcorpsso/login/login.php?usuario=' . $username . '&token=' . $token;
        
        //output the response (in the form of a multidimensional array) from the function call:
        return '{exp:redirecturl url="'.$url.'"}';

        //header('Location: http://miscursosucb.belcorp.biz/auth/belcorpws/client/client.php?usuario=peppinedo&token=ABCD&curso=24' );
        /*
        $data_string = json_encode($data, true);
        $url = 'http://190.41.151.102/Infhotel/ServiceReservaWeb.svc/InsertReserva';
        //  Initiate curl
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data_string)); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);     
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_HTTPHEADER,array(
            'Content-Type: application/json', 'charset=utf-8')
        ); 
        $result = curl_exec($ch);
        curl_close($ch);
        return $result."  ".$data_string; */
    }

    public function auth_lms_access_profile_head(){
        // include_once 'nusoap/lib/nusoap.php';
        // $username= ee()->TMPL->fetch_param('username');
        // //$username= "kcastro";
        // //$username= "pechvillaran";
        // $screen_name= ee()->TMPL->fetch_param('screen_name');
        // //instantiate the NuSOAP class and define the web service URL:
        // $client = new nusoap_client('http://miscursosucb.belcorp.biz/auth/belcorpws/belcorpws_server.php?wsdl', 'WSDL');
        // //check if there were any instantiation errors, and if so stop execution with an error message:
        // $error = $client->getError();
        // if ($error) {   
        //   die("client construction error: {$error}\n");
        // }
        // $param = array('username' => $username);
        // //perform a function call without parameters:
        // $answer = $client->call('login_usuario', $param);
        // //check if there were any call errors, and if so stop execution with some error messages:
        // $error = $client->getError();
        // if ($error) {
        //   print_r($client->response);
        //   print_r($client->getDebug());
        //   die();
        // }
        // $url='http://miscursosucb.belcorp.biz/auth/belcorpws/client/clientprofile.php?usuario='.$username.'&token='.$answer;
        // //$url='http://miscursosucb.belcorp.biz/auth/belcorpws/client/clientprofile.php?usuario=peppinedo&token='.$answer;
        // //output the response (in the form of a multidimensional array) from the function call:
        // return '{exp:redirecturl url="'.$url.'"}';

    	$username= ee()->TMPL->fetch_param('username');
		
		$query = ee()->db
	    				->select('DNI')
	    				->where('UsuarioRed', $username)
						->get('exp_usuarios');
		$user = $query->row();
		$dni = $user->DNI;

        $useragent = $_SERVER['HTTP_USER_AGENT'];
        $date = date("d-H");
        $token = hash('sha256', $dni.$useragent.$date);
        $url = 'http://qasucb.cyzone.com/moodlecolaboradores/auth/belcorpsso/login/login.php?usuario=' . $username . '&token=' . $token;
        
        //output the response (in the form of a multidimensional array) from the function call:
        return '{exp:redirecturl url="'.$url.'"}';

        //header('Location: http://miscursosucb.belcorp.biz/auth/belcorpws/client/client.php?usuario=peppinedo&token=ABCD&curso=24' );
        /*
        $data_string = json_encode($data, true);
        $url = 'http://190.41.151.102/Infhotel/ServiceReservaWeb.svc/InsertReserva';
        //  Initiate curl
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data_string)); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);     
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_HTTPHEADER,array(
            'Content-Type: application/json', 'charset=utf-8')
        ); 
        $result = curl_exec($ch);
        curl_close($ch);
        return $result."  ".$data_string; */
    }
}

/* End of file pi.infhotel.php */
/* Location: ./system/expressionengine/third_party/infhotel/pi.infhotel.php */