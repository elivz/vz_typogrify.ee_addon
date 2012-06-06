<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*===============================================================
 * php-typogrify
 ================================================================
 * Prettifies your web typography by preventing ugly quotes and 'widows' 
 * and providing CSS hooks to style some special cases.
 *
 * Modified by Eli Van Zoeren from Hamish Macpherson's port of the original 
 * Python code by Christian Metts.
 *
 *
 * Copyright (c) 2007, Hamish Macpherson
 * 
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:
 * 
 *     * Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.
 *     * Neither the name of the php-typogrify nor the names of its contributors may be used to endorse or promote products derived from this software without specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR
 * CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
 * EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
 * PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
 * PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
 * NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 * 
 *==============================================================*/

class Php_typogrify
{
    protected $text='';

    public function __construct()
    {
        $this->EE =& get_instance();
    }

    public function setText($text)
    {
        $this->text = $text;
    }

    public function getText() 
    {
        return $this->text;
    }

    /**
     * typogrify
     * 
     * The super typography filter.   
     * Applies the following filters: widont, smartypants, caps, amp, initial_quotes
     * Optionally choose to apply quote span tags to Gullemets as well.
     */
    public function run_all()
    {
        $this->amp();
        $this->widont();
        $this->caps();
        $this->initial_quotes();
        $this->dash();
        $this->exponents();
        $this->ordinals();
        $this->marks();
        
        return $this->text;
    }

    /**
     * amp
     * 
     * Wraps apersands in html with ``<span class="amp">`` so they can be
     * styled with CSS. Ampersands are also normalized to ``&amp;``. Requires 
     * ampersands to have whitespace or an ``&nbsp;`` on both sides.
     * 
     * It won't mess up & that are already wrapped, in entities or URLs
     */
    public function amp()
    {
        $amp_finder = "/(\s|&nbsp;)(&|&amp;|&\#38;|&#038;)(\s|&nbsp;)/";
        $this->text = preg_replace($amp_finder, '\\1<span class="amp">&amp;</span>\\3', $this->text);

        return $this;
    }

    /**
     * dash
     * 
     * Puts a &thinsp; before and after an &ndash or &mdash;
     * Dashes may have whitespace or an ``&nbsp;`` on both sides
     */
    public function dash()
    {
        $dash_finder = "/(\s|&nbsp;|&thinsp;)*(&mdash;|&ndash;|&#x2013;|&#8211;|&#x2014;|&#8212;)(\s|&nbsp;|&thinsp;)*/";
        $this->text = preg_replace($dash_finder, '&thinsp;\\2&thinsp;', $this->text);

        return $this;
    }

    /**
     * This is necessary to keep dotted cap strings to pick up extra spaces
     * used in preg_replace_callback later on
     */
    static function _cap_wrapper( $matchobj )
    {
        if ( !empty($matchobj[2]) )
        {
            return sprintf('<span class="caps">%s</span>', $matchobj[2]);
        }
        else 
        {
            $mthree = $matchobj[3];
            if ( ($mthree{strlen($mthree)-1}) == " " )
            {
                $caps = substr($mthree, 0, -1);
                $tail = ' ';
            }
            else
            {
                $caps = $mthree;
                $tail = '';
            }            
            return sprintf('<span class="caps">%s</span>%s', $caps, $tail);
        }
    }

    /**
     * caps
     *
     * Wraps multiple capital letters in ``<span class="caps">`` 
     * so they can be styled with CSS. 
     * 
     * Uses the smartypants tokenizer to not screw with HTML or with tags it shouldn't.
     */
    public function caps()
    {
        // Tokenize; see smartypants.php
        $tokens = $this->_tokenize_html($this->text);    
        $result = array();
        $in_skipped_tag = false;
        
        $cap_finder = "/(
                (\b[A-Z\d]*        # Group 2: Any amount of caps and digits
                [A-Z]\d*[A-Z]      # A cap string much at least include two caps (but they can have digits between them)
                [A-Z\d]*\b)        # Any amount of caps and digits
                | (\b[A-Z]+\.\s?   # OR: Group 3: Some caps, followed by a '.' and an optional space
                (?:[A-Z]+\.\s?)+)  # Followed by the same thing at least once more
                (?:\s|\b|$))/x";
        
        $tags_to_skip_regex = "/<(\/)?(?:pre|code|kbd|script|math)[^>]*>/i";
        
        foreach ($tokens as $token)
        {
            if ( $token[0] == "tag" )
            {
                // Don't mess with tags.
                $result[] = $token[1];
                $close_match = preg_match($tags_to_skip_regex, $token[1]);            
                if ( $close_match )
                {
                    $in_skipped_tag = true;
                }
                else
                {
                    $in_skipped_tag = false;
                }
            }
            else
            {
                if ( $in_skipped_tag )
                {
                    $result[] = $token[1];
                }
                else
                {
                    $result[] = preg_replace_callback($cap_finder, array($this, '_cap_wrapper'), $token[1]);
                }
            }
        }        
        $this->text = join("", $result);   

        return $this; 
    }

    /**
     * initial_quotes
     *
     * Wraps initial quotes in ``class="dquo"`` for double quotes or  
     * ``class="quo"`` for single quotes. Works in these block tags ``(h1-h6, p, li)``
     * and also accounts for potential opening inline elements ``a, em, strong, span, b, i``
     * Optionally choose to apply quote span tags to Gullemets as well.
     */
    public function initial_quotes( $do_guillemets = false )
    {
        $quote_finder = "/((<(p|h[1-6]|li)[^>]*>|^)                     # start with an opening p, h1-6, li or the start of the string
                        \s*                                             # optional white space! 
                        (<(a|em|span|strong|i|b)[^>]*>\s*)*)            # optional opening inline tags, with more optional white space for each.
                        ((\"|&ldquo;|&\#8220;)|('|&lsquo;|&\#8216;))    # Find me a quote! (only need to find the left quotes and the primes)
                                                                        # double quotes are in group 7, singles in group 8
                        /ix";
        
        if ($do_guillemets)
        {
            $quote_finder = "/((<(p|h[1-6]|li)[^>]*>|^)                                         # start with an opening p, h1-6, li or the start of the string
                            \s*                                                                 # optional white space! 
                            (<(a|em|span|strong|i|b)[^>]*>\s*)*)                                # optional opening inline tags, with more optional white space for each.
                            ((\"|&ldquo;|&\#8220;|\xAE|&\#171;|&laquo;)|('|&lsquo;|&\#8216;))    # Find me a quote! (only need to find the left quotes and the primes) - also look for guillemets (>> and << characters))
                                                                                                # double quotes are in group 7, singles in group 8
                            /ix";
        }
                        
        $this->text = preg_replace_callback($quote_finder, array($this, '_quote_wrapper'), $this->text);

        return $this;
    }

    /**
     * widont
     * 
     * Replaces the space between the last two words in a string with ``&nbsp;``
     * Works in these block tags ``(h1-h6, p, li)`` and also accounts for 
     * potential closing inline elements ``a, em, strong, span, b, i``
     * 
     * Empty HTMLs shouldn't error
     */
    public function widont()
    {
        // This regex is a beast, tread lightly
        $widont_finder = "/([^\s])\s+(((<(a|span|i|b|em|strong|acronym|caps|sub|sup|abbr|big|small|code|cite|tt)[^>]*>)*\s*[^\s<>]+)(<\/(a|span|i|b|em|strong|acronym|caps|sub|sup|abbr|big|small|code|cite|tt)>)*[^\s<>]*\s*(<\/(p|h[1-6]|li)>|$))/i";
                        
        $this->text = preg_replace($widont_finder, '$1&nbsp;$2', $this->text);

        return $this;
    }

    /**
     * exponents
     *
     * Converts exponents to superscript
     */
    public function exponents()
    {
        //handle exponents (ie. 4^2)
        $exponent_finder = "/\b(\d+)\^(\w+)\b/xu";
        $this->text = preg_replace($exponent_finder, '$1<sup>$2</sup>', $this->text);

        return $this;
    }

    /**
     * ordinals
     *
     * Makes ordinal suffixes superscript
     */
    public function ordinals()
    {
        //handle ordinals (ie. 2nd)
        $ordinal_finder = "/\b(\d+)(st|nd|rd|th)\b/";
        $this->text = preg_replace($ordinal_finder, '$1<sup>$2</sup>', $this->text);

        return $this;
    }

    /**
     * marks
     *
     * Turns various marks - (c), (tm), etc. - into their proper entities
     */
    public function marks()
    {
        $this->text = str_ireplace('(c)', '&copy;', $this->text);
        $this->text = str_ireplace('(r)', '&reg;', $this->text);
        $this->text = str_ireplace('(p)', '&#8471;', $this->text);
        $this->text = str_ireplace('(sm)', '&#8480;', $this->text);
        $this->text = str_ireplace('(tm)', '&trade;', $this->text);

        return $this;
    }

    /*
     * _tokenize_html
     *
     * Parameter:  String containing HTML markup.
     * Returns:    An array of the tokens comprising the input
     *             string. Each token is either a tag (possibly with nested,
     *             tags contained therein, such as <a href="<MTFoo>">, or a
     *             run of text between tags. Each element of the array is a
     *             two-element array; the first is either 'tag' or 'text';
     *             the second is the actual value.
     *
     *
     * Regular expression derived from the _tokenize() subroutine in 
     * Brad Choate's MTRegex plugin.
     * <http://www.bradchoate.com/past/mtregex.php>
     */
    protected function _tokenize_html($str) {
    #
        $index = 0;
        $tokens = array();

        $match = '(?s:<!(?:--.*?--\s*)+>)|'.    # comment
                 '(?s:<\?.*?\?>)|'.             # processing instruction
                                                # regular tags
                 '(?:<[/!$]?[-a-zA-Z0-9:]+\b(?>[^"\'>]+|"[^"]*"|\'[^\']*\')*>)'; 

        $parts = preg_split("{($match)}", $str, -1, PREG_SPLIT_DELIM_CAPTURE);

        foreach ($parts as $part) {
            if (++$index % 2 && $part != '') 
                $tokens[] = array('text', $part);
            else
                $tokens[] = array('tag', $part);
        }
        return $tokens;
    }

    private function _quote_wrapper( $matchobj )
    {
        if ( !empty($matchobj[7]) )
        {
            $classname = "dquo";
            $quote = $matchobj[7];
        }
        else
        {
            $classname = "quo";
            $quote = $matchobj[8];
        }
        return sprintf('%s<span class="%s">%s</span>', $matchobj[1], $classname, $quote);
    }

    // Original Title Case script © John Gruber <daringfireball.net>
    // JavaScript port © David Gouch <individed.com>
    // PHP port of the above by Kroc Camen <camendesign.com>
    // http://camendesign.com/code/title-case
    public function title_case() {
        $title = $this->text;

        //remove HTML, storing it for later
        //       HTML elements to ignore    | tags  | entities
        $regx = '/<(code|var)[^>]*>.*?<\/\1>|<[^>]+>|&\S+;/';
        preg_match_all ($regx, $title, $html, PREG_OFFSET_CAPTURE);
        $title = preg_replace ($regx, '', $title);
        
        //find each word (including punctuation attached)
        preg_match_all ('/[\w\p{L}&`\'‘’"“\.@:\/\{\(\[<>_]+-? */u', $title, $m1, PREG_OFFSET_CAPTURE);
        foreach ($m1[0] as &$m2) {
            //shorthand these- "match" and "index"
            list ($m, $i) = $m2;
            
            //correct offsets for multi-byte characters (`PREG_OFFSET_CAPTURE` returns *byte*-offset)
            //we fix this by recounting the text before the offset using multi-byte aware `strlen`
            $i = mb_strlen (substr ($title, 0, $i), 'UTF-8');
            
            //find words that should always be lowercase…
            //(never on the first word, and never if preceded by a colon)
            $m = $i>0 && mb_substr ($title, max (0, $i-2), 1, 'UTF-8') !== ':' && 
                !preg_match ('/[\x{2014}\x{2013}] ?/u', mb_substr ($title, max (0, $i-2), 2, 'UTF-8')) && 
                 preg_match ('/^(a(nd?|s|t)?|b(ut|y)|en|for|i[fn]|o[fnr]|t(he|o)|vs?\.?|via)[ \-]/i', $m)
            ?   //…and convert them to lowercase
                mb_strtolower ($m, 'UTF-8')
                
            //else: brackets and other wrappers
            : ( preg_match ('/[\'"_{(\[‘“]/u', mb_substr ($title, max (0, $i-1), 3, 'UTF-8'))
            ?   //convert first letter within wrapper to uppercase
                mb_substr ($m, 0, 1, 'UTF-8').
                mb_strtoupper (mb_substr ($m, 1, 1, 'UTF-8'), 'UTF-8').
                mb_substr ($m, 2, mb_strlen ($m, 'UTF-8')-2, 'UTF-8')
                
            //else: do not uppercase these cases
            : ( preg_match ('/[\])}]/', mb_substr ($title, max (0, $i-1), 3, 'UTF-8')) ||
                preg_match ('/[A-Z]+|&|\w+[._]\w+/u', mb_substr ($m, 1, mb_strlen ($m, 'UTF-8')-1, 'UTF-8'))
            ?   $m
                //if all else fails, then no more fringe-cases; uppercase the word
            :   mb_strtoupper (mb_substr ($m, 0, 1, 'UTF-8'), 'UTF-8').
                mb_substr ($m, 1, mb_strlen ($m, 'UTF-8'), 'UTF-8')
            ));
            
            //resplice the title with the change (`substr_replace` is not multi-byte aware)
            $title = mb_substr ($title, 0, $i, 'UTF-8').$m.
                 mb_substr ($title, $i+mb_strlen ($m, 'UTF-8'), mb_strlen ($title, 'UTF-8'), 'UTF-8')
            ;
        }
        
        //restore the HTML
        foreach ($html[0] as &$tag) $title = substr_replace ($title, $tag[0], $tag[1], 0);
        
        $this->text = $title;

        return $this;
    }

}

/* End of file ./libraries/php-typogrify.php */