<?php
/*

Plugin Name: cyr-to-arabic
Plugin URI: http://www.janibek.kz/cyr-to-arabic
Description: Convert text from Kazakh Cyrillic to Arabic script in posts. 2014.1.1, Altai, China.
Author: Janibek Sheryazdan
Version: 0.3
Author URI: http://www.janibek.kz
*/

add_action('init', 'kk_set_lang');
add_action('wp_head', 'kk_css');
add_action('plugins_loaded', 'sidebar_widget');
add_action('do_convert', 'kk_convert');

// cookie
function kk_set_lang()
{
	$ln = "";
	$lang = "";

	if ( isset($_REQUEST['ln']) )
        {
		$lang = $_REQUEST['ln'];
		if ( $lang == "kk" || $lang == "ar" )
		{
			setcookie("lang", $lang);
		}
	}
}
// Arabic text left to right order
function kk_css()
{
	$is_css = "cyrl";
	
	if ( isset($_REQUEST['ln']) ) {
		$lang = $_REQUEST['ln'];
	} elseif ( isset($_COOKIE['lang']) ) {
		$lang = $_COOKIE['lang'];
	}
    if ($lang == "kk") {
        $is_css = "cyrl";
    }
    if ($lang == "ar") {
    	// font face and RTL (Right To Left script).
        $is_css = "tote";
    }
    wp_enqueue_style(  'default',  plugins_url('/'.$is_css.'.css', __FILE__) );
}

function sidebar_widget()
{
	register_sidebar_widget('KkConverter', 'kk_widget');
	register_widget_control('KkConverter', 'kk_widget_control');
}
function kk_widget_control() {
	echo "<p>".__("Жөндеу қажетсыз, автоматты түрде басқы бетте көрінеді.", "KkConverter")."</p>";
} 
// Functions to print widget in sidebar
function kk_widget($args)
{
	extract($args);

	$options = get_option("widget");
	if (!is_array( $options ))
	{
		$options = array(
		'style' => 'list'
		);
		update_option("widget", $options);
	}

	echo $before_widget;

	if ( $options['style'] == "list" )
        {
		lang_links();
	}
	echo $after_widget;
}

function lang_links()
{
	$page_url = $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
	if ( isset($_REQUEST['ln']) ) {
		$lang = $_REQUEST['ln'];
	} elseif ( isset($_COOKIE['lang']) ) {
		$lang = $_COOKIE['lang'];
	}
	if ( count($_GET) > 0 ) {
		if ( !$_GET['ln'] ) {
			$kk = '<a href="http://'.$page_url.'&ln=kk">';
			$arb = '<a href="http://'.$page_url.'&ln=ar">';
		} else {
			$kk = '<a href="http://'.str_replace( array("ln=ar", "ln=kk"), 'ln=kk', $page_url).'">';
			$arb = '<a href="http://'.str_replace( array("ln=ar", "ln=kk"), 'ln=ar', $page_url).'">';
		}
	}  else {
		$kk = '<a href="?ln=kk">';
		$arb = '<a href="?ln=ar">';
	}
	$oo1 = $oo2 = "</a>";
	
	switch($lang) {
		case "ar": $arb = "<strong>"; $oo1 = "</strong>"; break;
		default:   $kk = "<strong>"; $oo2 = "</strong>";
	}

print <<<EOF
<ul>
<li>${kk}&#x041A;&#x0438;&#x0440;&#x0438;&#x043B;${oo2}</li>
&nbsp;&nbsp;&nbsp;
<li>${arb}توتە${oo1}</li>
</ul>
EOF;
}

class kk_convert
{
	function kk_convert()
	{
		add_action('wp_head', array(&$this,'buffer_start'));
		add_action('wp_footer', array(&$this,'buffer_end'));
	}
	
	function buffer_start()
	{
		ob_start( array(&$this,"do_convert") );
	}
	 
	function buffer_end()
	{
		ob_end_flush();
	}
	function do_convert($text)
        {
        	// default language is Cyrillic "kk"
		$lang = "kk";

		if ( isset($_REQUEST['ln']) )
		{ 
			$lang = $_REQUEST['ln'];
		}
		elseif ( isset($_COOKIE['lang']) )
		{
			$lang = $_COOKIE['lang'];
		}
		if ( $lang == "ar" )
		{
			// Cyrillic to Arabic
			$arb = array(
				'/[аә]/ui' => 'ا', '/б/ui' => 'ب', '/в/ui' => 'ۆ', '/г/ui' => 'گ', '/ғ/ui' => 'ع', '/д/ui' => 'د', '/[еэ]/ui' => 'ە',
				'/ж/ui' => 'ج', '/з/ui' => 'ز', '/[ий]/ui' => 'ي', '/к/ui' => 'ك', '/қ/ui' => 'ق', '/л/ui' => 'ل', '/м/ui' => 'م',
				'/н/ui' => 'ن', '/ң/ui' => 'ڭ', '/[оө]/ui' => 'و', '/п/ui' => 'پ', '/р/ui' => 'ر', '/с/ui' => 'س', '/т/ui' => 'ت',
				'/у/ui' => 'ۋ', '/[ұү]/ui' => 'ۇ', '/ф/ui' => 'ف', '/х/ui' => 'ح', '/һ/ui' => 'ھ', '/ц/ui' => 'تس', '/ч/ui' => 'چ',
				'/ш/ui' => 'ش', '/[ыі]/ui' => 'ى', '/ё/ui' => 'يو', '/ю/ui' => 'يۋ', '/я/ui' => 'يا', '/щ/ui' => 'شش',
				'/[ъь]/ui' => '', '/\,/' => '،', '/џ/ui' => 'ۇ', '/يي/ui' => 'ي', '/ۇلى/ui' => ' ۇلى', '/ششش/ui' => 'شش', '/ۋۋ/ui' => 'ۋ',
				// symbol conversion between a-z0-9. [ ؟ -> ? ]
				'/\?/' => '؟', '/([A-Za-z0-9"\/])؟/' => '$1?',
			);
			// The next control HAMZA [ ء ]
			$matches = preg_split( '/[\b\s\-\.:,>«]+/', $text, -1, PREG_SPLIT_OFFSET_CAPTURE);
			$mstart = 0;
			$ret = '';
			foreach( $matches as $m ) {
				$ret .= substr( $text, $mstart, $m[1] - $mstart );
				if ( preg_match('/[әөүіӘӨҮІ]/u', $m[0]) && !preg_match('/[еэгғкқЕЭГҒКҚ]/u', $m[0]) )
				{
					$ret .= 'ء'.$m[0];
				} else {
					$ret .= $m[0];
				}
				$mstart = $m[1] + strlen($m[0]);
			}
			// Convert Text
			$text =& $ret;
			foreach( $arb as $k => $v ) {
				$text = preg_replace( $k, $v, $text );
			}
			// Arabic text results
			return $text;
		   } else {
		   	   // if there no need for transliteration, print out unchanged text
			return $text;
		}
	}
}

$_wp_kk_convert =& new kk_convert;

?>