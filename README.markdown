VZ Typography
=============

VZ Typogrify is a thin wrapper around the library from <a href="http://blog.hamstu.com/2007/05/31/web-typogrify-just-got-better/">php-typogrify</a>. It provides lots of typographical niceties, including widow prevention, styling hooks for special characters, etc.

Tag Pairs
---------

    {exp:vz_typogrify [enable|disable] [titlecase="yes"]}

Runs the typographic filters on the enclosed text. The `enable` and `disable` paramters accept a pipe-separated list of filters to run, or to not run. If you do not include either parameter, all filters will be run. Available filters are listed below. *Either* the enable or disable parameter can be set, but not both.

Set the `titlecase` parameter to `yes` if you also want the output to be titlecased (see below).

    {exp:vz_typogrify:titlecase}

Title-cases the enclosed text by capitalizing the first letter of words, except for short words like "the" and "or". This filter uses a modified version of John Gruber's script (http://daringfireball.net/2008/05/title_case).

Available Filters
-----------------

* `widows` - Inserts a `&nbsp;` between the last two words of each paragraph, to prevent widows.
* `ampersands` - Wraps ampersands in `<span class="amp">&amp;</span>`.
* `caps` - Wraps all-caps words like `<span class="caps">ABC</span>`.
* `quotes` - Wraps opening quotes like `<span class="dquo">"</span>` or `<span class="quo">'</span>`.
* `dashes` - Adds a &thinsp; before and after an &ndash or &mdash;.
* `exponents` - Converts exponents to superscript. `4^2` becoms `4<sup>2</sup>`.
* `ordinals` - Wraps ordinals in superscript tags like `2<sup>nd</sup>`.
* `marks` - Converts `(c)`, `(r)`, `(p)`, `(tm)`, and `(sm)` to their proper entities.

Examples
--------

    {exp:vz_typogrify enable="widows|ampersands"}
        <p>This, that, & the other.</p>
    {/exp:vz_typogrify}

becomes:

    <p>This, that, <span class="amp">&amp;</span> the&nbsp;other.</p>


    {exp:vz_typogrify:titlecase}
        <p>This, that, & the other.</p>
    {/exp:vz_typogrify:titlecase}

becomes:

    <p>This, That, & the Other.</p>