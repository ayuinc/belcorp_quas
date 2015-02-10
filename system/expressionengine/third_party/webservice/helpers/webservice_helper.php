<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');/** * Webservice Helper * * @package		webservice * @category	Modules * @author		Rein de Vries <info@reinos.nl> * @link		http://reinos.nl/add-ons/webservice * @license  	http://reinos.nl/add-ons/commercial-license * @copyright 	Copyright (c) 2014 Reinos.nl Internet Media */// ------------------------------------------------------------------------/** * Functie object_to_array * * Convert a object to an array recursive * * @param  	$d * @access	public * @return	array */if ( ! function_exists('object_to_array')){	function object_to_array($d)	{		if (is_object($d)) 		{			// Gets the properties of the given object			// with get_object_vars function			$d = get_object_vars($d);		} 		if (is_array($d)) 		{			/*			* Return array converted to object			* Using __FUNCTION__ (Magic constant)			* for recursive call			*/			return array_map(__FUNCTION__, $d);		}		else 		{			// Return array			return $d;		}	}}/** * _format_readed_data function * * Posible types: list|entry *  *  * @param  [type] $data  * @return [type]        */if ( ! function_exists('webservice_format_data')){	function webservice_format_data($data = array(), $type = '')	{		$return = array();		if(!empty($data))		{			foreach($data as $key => $val)			{				if(!empty($val))				{					foreach($val as $k => $v)					{						if($type == 'soap')						{							foreach($v as $_k => $_v)							{								$return[$key][$k][$_k] = is_array($_v) ? json_encode($_v) : $_v;							}						}						else						{							$return[$key][$k] = is_array($v) ? json_encode($v) : $v;						}											}				}			}		}		return $return;	}}// ----------------------------------------------------------------------/** * _format_readed_data function * * Posible types: list|entry *  *  * @param  [type] $data  * @return [type]        */if ( ! function_exists('webservice_format_soap_data')){	function webservice_format_soap_data($data, $type = 'entry')	{		//grab the data and assign it to a tmp var		if(isset($data))		{			$data_ = $data;			$data = array();		}		else		{			$data_ = array();		}				//create the structures		if(!empty($data_))		{			$i = $ii = 0;			foreach($data_ as $key=>$val)			{					// one entry				if($type == "entry")				{					//assign					$data[$i] = array('key'=>$key, 'value'=>$val);					}				//multiple entries				else if($type == "entry_list")				{					if(!empty($val))					{						foreach($val as $k=>$v)						{							//assign							$data[$i][$ii] = array('key'=>$k, 'value'=>$v);							$ii++;						}					}				}				$i++;			}		}		else		{			$data = array();		}		return (array) $data;	}}/* End of file webservice_helper.php *//* Location: /system/expressionengine/third_party/webservice/helpers/webservice_helper.php */