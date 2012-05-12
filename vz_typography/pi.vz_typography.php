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
	public function __construct($str=NULL)
	{
		$this->EE =& get_instance();

		// Get the text we'll be formatting
		if (!empty($str))
			$in = $str;
		elseif (!empty($this->EE->TMPL->tagdata))
			$in = $this->EE->TMPL->tagdata;
		else
			return '';

		// Load the typography library
		$this->EE->load->library('php_typogrify');
		$this->EE->php_typogrify->setText($in);

		// Set options
		if ($this->EE->TMPL->fetch_param('disable'))
		{
			$disable = explode('|', $this->EE->TMPL->fetch_param('disable'));
			$this->_set_options($disable, FALSE);
		}
		if ($this->EE->TMPL->fetch_param('enable'))
		{
			$enable = explode('|', $this->EE->TMPL->fetch_param('enable'));
			$this->_set_options($enable, TRUE);
		}

		$this->return_data = $this->EE->php_typogrify->getText();
	}

	private function _set_options($options, $value)
	{
		if (in_array('ampersands', $options) == $value) $this->EE->php_typogrify->amp();
		if (in_array('widows', $options) == $value) $this->EE->php_typogrify->widont();
		if (in_array('caps', $options) == $value) $this->EE->php_typogrify->caps();
		if (in_array('quotes', $options) == $value) $this->EE->php_typogrify->initial_quotes();
		if (in_array('dashes', $options) == $value) $this->EE->php_typogrify->dash();
		if (in_array('exponents', $options) == $value) $this->EE->php_typogrify->exponents();
		if (in_array('ordinals', $options) == $value) $this->EE->php_typogrify->ordinals();
		if (in_array('marks', $options) == $value) $this->EE->php_typogrify->marks();
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