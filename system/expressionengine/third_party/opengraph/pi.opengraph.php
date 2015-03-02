<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Memberlist Class
 *
 * @package     ExpressionEngine
 * @category    Plugin
 * @author      Ricardo Díaz
 * @copyright   Copyright (c) 2014, Ricardo Díaz
 * @link        http://www.ayuinc.com/
 */

$plugin_info = array(
    'pi_name'         => 'OpenGraph',
    'pi_version'      => '1.0',
    'pi_author'       => 'Ricardo Díaz',
    'pi_author_url'   => 'http://www.ayuinc.com/',
    'pi_description'  => 'Allows states qualify Friends',
    'pi_usage'        => Opengraph::usage()
);
            
class Opengraph 
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
        $this->EE =& get_instance();
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

            {exp:opengraph}

        This is an incredibly simple Plugin.
            <?php
        $buffer = ob_get_contents();
        ob_end_clean();
        return $buffer;
    }
    // END

    public function query(){
	    $url = ee()->TMPL->fetch_param('url');
        if ($url != ""){
            $html = @file_get_contents($url);
    		if($html){
                libxml_use_internal_errors(true); // Yeah if you are so worried about using @ with warnings
        		$doc = new DomDocument();
        		$doc->loadHTML($html);
        		$xpath = new DOMXPath($doc);
        		$query = '//*/meta[starts-with(@property, \'og:\')]';
        		$metas = $xpath->query($query);
        		foreach ($metas as $meta) {
        		    $property = $meta->getAttribute('property');
        		    $content = $meta->getAttribute('content');
        		    $rmetas[$property] = $content;
        		}
        			
        		if(isset($rmetas)) {
	        		if (!isset($rmetas['og:url'])) {
	        			$rmetas['og:url'] = $url;
	        		}
	        		
	        		if(!empty($rmetas['og:image'])) {
		        		if (!preg_match("~^(?:f|ht)tps?://~i", $rmetas['og:image'])) {
		        			$parsedUrl = parse_url($rmetas['og:url']);
		                	$rmetas['og:image'] = $parsedUrl["scheme"] . "://" . $parsedUrl["host"] . "/" . $rmetas['og:image'];
		            	}
	        		} else {
		        		$rmetas['og:image'] = NULL;
	        		}

	            	$title_resume = strlen($rmetas['og:title']) > 50 ? substr($rmetas['og:title'], 0, 50) : $rmetas['og:title'];
	            	
	        		$variables[] = array(
	        	        'og_title' => $rmetas['og:title'] ?: "Título no disponible",
	        	        'og_title_resume' => $title_resume ?: 'Informacion no disponible',
	        	        'og_description' => $rmetas['og:description'] ?: 'Descripción no disponible',
	        	        'og_image' => $rmetas['og:image'] ?: '{site_url}images/imagenes-noticias/imgen-no-disponible.png',
	        	        'og_url' => $rmetas['og:url'] ?: '#',
	                    'og_available' => 'yes',
	        		);
        		} else {
	        			$variables[] = array(
		                    'og_title' => "Título no disponible",
		                    'og_title_resume' => 'Informacion no disponible',
		                    'og_description' => 'Descripción no disponible',
		                    'og_image' => '{site_url}images/imagenes-noticias/imgen-no-disponible.png',
		                    'og_url' => '#',
		                    'og_available' => 'no',
		                );
        		}
        		
        		$tagdata = $this->EE->TMPL->tagdata;
        		return $this->EE->TMPL->parse_variables($tagdata, $variables);
            }
            else{
                $variables[] = array(
                    'og_title' => "Título no disponible",
                    'og_title_resume' => 'Informacion no disponible',
                    'og_description' => 'Descripción no disponible',
                    'og_image' => '{site_url}images/imagenes-noticias/imgen-no-disponible.png',
                    'og_url' => '#',
                    'og_available' => 'no',
                );
                
                $tagdata = $this->EE->TMPL->tagdata;

                return $this->EE->TMPL->parse_variables($tagdata, $variables); 
            }
        }
            return "";  
    }
} 
/* End of file pi.rating.php */
/* Location: ./system/expressionengine/third_party/rating/pi.rating.php */
