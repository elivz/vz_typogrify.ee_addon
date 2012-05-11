<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * VZ Typography
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Plugin
 * @author		Eli Van Zoeren
 * @link		http://elivz.com
 * @license   http://creativecommons.org/licenses/by-sa/3.0/ Attribution-Share Alike 3.0 Unported
 */

$plugin_info = array(
	'pi_name'		=> 'VZ Typography',
	'pi_version'	=> '0.5',
	'pi_author'		=> 'Eli Van Zoeren',
	'pi_author_url'	=> 'http://elivz.com',
	'pi_description'=> 'Process text using PHP Typography',
	'pi_usage'		=> Vz_typography::usage()
);


class Vz_typography {

	public $return_data;
    
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->EE =& get_instance();
	}
	
	// ----------------------------------------------------------------
	
	/**
	 * Plugin Usage
	 */
	public static function usage()
	{
		ob_start();
?>

VZ Typography is a thin wrapper around the <a href="http://kingdesk.com/projects/php-typography/">PHP Typography</a> library.  It provides lots of typographical niceties, including widow prevention, hyphenation, styling hooks for special characters, etc.
<?php
		$buffer = ob_get_contents();
		ob_end_clean();
		return $buffer;
	}
}


/* End of file pi.vz_typography.php */
/* Location: /system/expressionengine/third_party/vz_typography/pi.vz_typography.php */