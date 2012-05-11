<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

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
	'pi_version'	=> '0.5.0',
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

		// Load the typography library
		$this->EE->load->library('php_typography');
		$this->typo = new Php_typography();

		// Get the text we'll be formatting
		if (!empty($str))
			$in = $str;
		elseif (!empty($this->EE->TMPL->tagdata))
			$in = $this->EE->TMPL->tagdata;
		else
			return '';

		// Don't bother with filters that EE runs itself
		$this->typo->set_smart_quotes(FALSE);
		$this->typo->set_smart_dashes(FALSE);
		$this->typo->set_smart_ellipses(FALSE);

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

		// Run the filters
		$out = $this->typo->process($in);
		$out = htmlentities($out, NULL, "UTF-8", FALSE);

		$this->return_data = $out;
	}

	private function _set_options($options, $value)
	{
		$this->typo->set_hyphenation(in_array('hyphenation', $options) == $value);
		$this->typo->set_fraction_spacing(in_array('fractions', $options) == $value);
		$this->typo->set_unit_spacing(in_array('units', $options) == $value);
		$this->typo->set_dash_spacing(in_array('dash_spacing', $options) == $value);
		$this->typo->set_single_character_word_spacing(in_array('single_character_word', $options) == $value);
		$this->typo->set_space_collapse(in_array('space_collapse', $options) == $value);
		$this->typo->set_dewidow(in_array('widows', $options) == $value);
		$this->typo->set_wrap_hard_hyphens(in_array('hard_hyphens', $options) == $value);
		$this->typo->set_wrap_hard_hyphens(in_array('emails', $options) == $value);
		$this->typo->set_url_wrap(in_array('urls', $options) == $value);
		$this->typo->set_smart_diacritics(in_array('diacritics', $options) == $value);
		$this->typo->set_smart_marks(in_array('marks', $options) == $value);
		$this->typo->set_smart_math(in_array('math', $options) == $value);
		$this->typo->set_smart_exponents(in_array('exponents', $options) == $value);
		$this->typo->set_smart_fractions(in_array('fractions', $options) == $value);
		$this->typo->set_smart_ordinal_suffix(in_array('ordinals', $options) == $value);
		$this->typo->set_style_ampersands(in_array('spans', $options) == $value);
		$this->typo->set_style_caps(in_array('spans', $options) == $value);
		$this->typo->set_style_numbers(in_array('spans', $options) == $value);
		$this->typo->set_style_initial_quotes(in_array('spans', $options) == $value);
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