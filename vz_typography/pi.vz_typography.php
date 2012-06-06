<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * VZ Typography
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Plugin
 * @author		Eli Van Zoeren
 * @link		http://elivz.com
 * @license     http://creativecommons.org/licenses/by-sa/3.0/ Attribution-Share Alike 3.0 Unported
 */

$plugin_info = array(
	'pi_name'		=> 'VZ Typography',
	'pi_version'	=> '0.7',
	'pi_author'		=> 'Eli Van Zoeren',
	'pi_author_url'	=> 'http://elivz.com',
	'pi_description'=> 'Process text using Typogrify',
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

		// First run EE's typography filter to convert to curly quotes, etc.
		$this->EE->load->library('typography');
		$this->EE->typography->initialize();
		$in = str_replace('&quot;', '"', $in);
		$in = $this->EE->typography->format_characters($in);

		// Load the typography library
		$this->EE->load->library('php_typogrify');
		$this->EE->php_typogrify->setText($in);

		// Set options
        if ($enable = $this->EE->TMPL->fetch_param('enable'))
        {
            $enable = explode('|', $enable);
            $this->_set_options($enable, TRUE);
        }
		else
		{
			$disable = explode('|', $this->EE->TMPL->fetch_param('disable', ''));
			$this->_set_options($disable, FALSE);
		}

		// Titlecase it, if necessary
		if ($this->EE->TMPL->fetch_param('titlecase') == 'yes')
		{
			$this->EE->php_typogrify->title_case();
		}

		$this->return_data = $this->EE->php_typogrify->getText();
	}

	/**
	 * Set each filter on or off, depending on the parameters that were set
	 */
	protected function _set_options($options, $value)
	{
		if (in_array('widows', $options) == $value) $this->EE->php_typogrify->widont();
		if (in_array('ampersands', $options) == $value) $this->EE->php_typogrify->amp();
		if (in_array('caps', $options) == $value) $this->EE->php_typogrify->caps();
		if (in_array('quotes', $options) == $value) $this->EE->php_typogrify->initial_quotes();
		if (in_array('dashes', $options) == $value) $this->EE->php_typogrify->dash();
		if (in_array('exponents', $options) == $value) $this->EE->php_typogrify->exponents();
		if (in_array('ordinals', $options) == $value) $this->EE->php_typogrify->ordinals();
		if (in_array('marks', $options) == $value) $this->EE->php_typogrify->marks();
	}

	/**
	 * Title-case the string
	 */
	public function titlecase()
	{
	    $in = $this->EE->TMPL->tagdata;
	    if (!$in) return;

		$this->EE->load->library('php_typogrify');
		$this->EE->php_typogrify->setText($in);
		$this->EE->php_typogrify->title_case();

		return $this->EE->php_typogrify->getText();
	}
	
	// ----------------------------------------------------------------
	
	/**
	 * Plugin Usage
	 */
	public static function usage()
	{
		ob_start();
?>

VZ Typogrify is a thin wrapper around the library from <a href="http://blog.hamstu.com/2007/05/31/web-typography-just-got-better/">php-typogrify</a>. It provides lots of typographical niceties, including widow prevention, styling hooks for special characters, etc.

Tag Pairs
---------

{exp:vz_typography [enable|disable] [titlecase="yes"]}

Runs the typographic filters on the enclosed text. The enable and disable paramters accept a pipe-separated list of filters to run, or to not run. If you do not include either parameter, all filters will be run. Available filters are listed below. *Either* the enable or disable parameter can be set, but not both.

Set the `titlecase` parameter to `yes` if you also want the output to be titlecased (see below).

{exp:vz_typography:titlecase}

Title-cases the enclosed text by capitalizing the first letter of words, except for short words like "the" and "or". This filter uses a modified version of John Gruber's script (http://daringfireball.net/2008/05/title_case).

Available Filters
-----------------

`widows` - Inserts a `&nbsp;` between the last two words of each paragraph, to prevent widows.
`ampersands` - Wraps ampersands in `<span class="amp">&amp;</span>`.
`caps` - Wraps all-caps words like `<span class="caps">ABC</span>`.
`quotes` - Wraps opening quotes like `<span class="dquo">"</span>` or `<span class="quo">'</span>`.
`dashes` - Adds a &thinsp; before and after an &ndash or &mdash;.
`exponents` - Converts exponents to superscript. `4^2` becoms `4<sup>2</sup>`.
`ordinals` - Wraps ordinals in superscript tags like `2<sup>nd</sup>`.
`marks` - Converts `(c)`, `(r)`, `(p)`, `(tm)`, and `(sm)` to their proper entities.

Examples
--------

    {exp:vz_typography enable="widows|ampersands"}
        <p>This, that, & the other.</p>
    {/exp:vz_typography}

becomes:

    <p>This, that, <span class="amp">&amp;</span> the&nbsp;other.</p>

    {exp:vz_typography:titlecase}
        <p>This, that, & the other.</p>
    {/exp:vz_typography:titlecase}

becomes:

    <p>This, That, & the Other.</p>

<?php
		$buffer = ob_get_contents();
		ob_end_clean();
		return $buffer;
	}
}


/* End of file pi.vz_typography.php */
/* Location: /system/expressionengine/third_party/vz_typography/pi.vz_typography.php */