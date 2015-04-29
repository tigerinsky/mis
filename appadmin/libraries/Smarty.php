<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* Smarty Class
*
* @package                CodeIgniter
* @subpackage        Libraries
* @category        Smarty
* @author                Kepler Gelotte
* @link                http://www.coolphptools.com/codeigniter-smarty
*/
require_once( BASEPATH.'third_party/smarty/libs/Smarty.class.php' );

class CI_Smarty extends Smarty {

        function __construct()
        {
                parent::__construct();
                $this->compile_dir = $_SERVER["LJSRV_CACHE_FILE_PATH"].$_SERVER["LJSRV_PROJECT_NAME"].'/appadmin/templates_c/';
                $this->cache_dir = $_SERVER["LJSRV_CACHE_FILE_PATH"].$_SERVER["LJSRV_PROJECT_NAME"].'/appadmin/cache/';
     			$this->template_dir = APPPATH . "views/templates";
                $this->config_dir = APPPATH."views/config";
                //$this->debugging = true;
                //$this->caching = true;
                //$this->cache_lifetime = 120;
                //$this->force_compile = false;
                $this->left_delimiter = '<{';
				$this->right_delimiter = '}>';

                $this->assign('APPPATH',APPPATH);
                $this->assign('BASEPATH',BASEPATH);
                // Assign CodeIgniter object by reference to CI
                if ( method_exists( $this, 'assignByRef') )
                {
                        $ci =& get_instance();
                        $ci->config->load('config',TRUE);
						$sys_config=$ci->config->item('config');
						define('SITE_URL',$sys_config['base_url']);
						define('SITE_PUB_URL',$sys_config['exten_pub_path']);
				        $this->assign('sys_config',$sys_config);
                        $this->assignByRef("ci", $ci);
                }
                log_message('debug', "Smarty Class Initialized");
        }


        /**
         *  Parse a template using the Smarty engine
         *
         * This is a convenience method that combines assign() and
         * display() into one step. 
         *
         * Values to assign are passed in an associative array of
         * name => value pairs.
         *
         * If the output is to be returned as a string to the caller
         * instead of being output, pass true as the third parameter.
         *
         * @access        public
         * @param        string
         * @param        array
         * @param        bool
         * @return        string
         */
        function view($template, $data = array(), $return = FALSE)
        {
        	if(is_array($data) and count($data)>0)
        	{
	            foreach ($data as $key => $val)
	            {
	                    $this->assign($key, $val);
	            }
        	}
            if ($return == FALSE)
            {
                    $CI =& get_instance();
                    if (method_exists( $CI->output, 'set_output' ))
                    {
                            $CI->output->set_output( $this->fetch($template) );
                    }
                    else
                    {
                            $CI->output->final_output = $this->fetch($template);
                    }
                    return;
            }
            else
            {
                    return $this->fetch($template);
            }
        }
}
// END Smarty Class
