<?php
/*

Plugin Name: cyr-to-arabic
Plugin URI: http://www.janibek.kz/cyr-to-arabic
Description: Convert text from Kazakh Cyrillic to Arabic script in posts. 2014.1.1, Altai, China.
Author: Janibek Sheryazdan
Version: 0.5
Author URI: http://www.janibek.kz
*/

function kk_set_lang()
{
	if ( isset($_REQUEST['ln']) && ( $_REQUEST['ln'] == "kk" || $_REQUEST['ln'] == "ar" || $_REQUEST['ln'] == "lat" ) )
		{
			setcookie("lang",  $_REQUEST['ln']);
		}
}
add_action('init', 'kk_set_lang');

function kk_css()
{
	if ( isset($_REQUEST['ln']) ) {
		$lang = $_REQUEST['ln'];
	} elseif ( isset($_COOKIE['lang']) ) {
		$lang = $_COOKIE['lang'];
	}
    if ($lang == "ar") {
    	// font face and RTL (Right To Left script).
        wp_enqueue_style(  'tote',  plugins_url('/fonts/tote.css', __FILE__) );
    }
    
}
add_action('wp_head', 'kk_css');

require_once('inc/widgets.php');

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
			$lat = '<a href="http://'.$page_url.'&ln=lat">';
		} else {
			$kk = '<a href="http://'.str_replace( array("ln=ar", "ln=lat", "ln=kk"), 'ln=kk', $page_url).'">';
			$arb = '<a href="http://'.str_replace( array("ln=ar", "ln=lat", "ln=kk"), 'ln=ar', $page_url).'">';
			$lat = '<a href="http://'.str_replace( array("ln=ar", "ln=kk"), 'ln=lat', $page_url).'">';
		}
	}  else {
		$kk = '<a href="?ln=kk">';
		$arb = '<a href="?ln=ar">';
		$lat = '<a href="?ln=lat">';
	}
	$cyr = $ar = $lt = "</a>";
	

print <<<EOF
	<style>.widget_kkconverter { text-align:center; } .widget_kkconverter ul li { width: auto; display:inline-block;}</style>
<ul>
<li>${kk}&#x041A;&#x0438;&#x0440;&#x0438;&#x043B;${cyr}</li>
&nbsp;&nbsp;&nbsp;
<li>${arb}توتە${ar}</li>
&nbsp;&nbsp;&nbsp;
<li>${lat}Latin${lt}</li>
</ul>
EOF;
}

class kk_convert
{
	function kk_convert()
	{
		add_action('wp_head', array(&$this,'convert_start'));
		add_action('wp_footer', array(&$this,'convert_end'));
	}
	
	function convert_start()
	{
		ob_start( array(&$this,"do_convert") );
	}
	 
	function convert_end()
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
		if ( $lang == "lat" )
		{
			$cyrl = array ("А", "Ә", "Б", "В", "Г", "Ғ", "Д", "Е", "Ж", "З", "И", "Й", "К", "Қ", "Л", "М","Н","Ң","О","Ө","П","Р","С","Т","У","Ұ","Ү","Ё","Ф","Х","Ц","Ч","Ш","Щ","Ъ","Ь","Һ","І","Ы","Э","Ю","Я","а", "ә", "б", "в", "г", "ғ", "д", "е", "ж", "з", "и", "й", "к", "қ", "л", "м","н","ң","о","ө","п","р","с","т","у","ұ","ү","ё","ф","х","ц","ч","ш","щ","ъ","ь","һ","і","ы","э","ю","я");
			$latin = array ("A", "Ä", "B", "V", "G", "Ğ", "D", "E", "J", "Z", "I", "Y", "K", "Q", "L", "M","N","Ñ","O","Ö","P","R","S","T","U","W","Ü","E","F","H","C","Ç","Ş","Ş","'","'","H","İ","I","E","YU","YA","a", "ä", "b", "v", "g", "ğ", "d", "e", "j", "z", "i", "y", "k", "q", "l", "m","n","ñ","o","ö","p","r","s","t","u","w","ü","e","f","h","c","ç","ş","ş","'","'","h","i","ı","e","yu","ya");
			return str_replace($cyrl, $latin, $text);
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
				'/[ъь]/ui' => '', '/џ/ui' => 'ۇ', '/يي/ui' => 'ي', '/ششش/ui' => 'شش', '/ۋۋ/ui' => 'ۋ',
				// symbol conversion between a-z0-9. [ ؟ -> ? ]
				'/\?/' => '؟', '/([A-Za-z0-9\'\-\"\)\>\*\$\|\+\/])؟/' => '$1?', '/\,/' => '،', '/([A-Za-z0-9\'\-\"\)\>\*\$\|\+\/])،/' => '$1,',
			);
			// The next control HAMZA [ ء ]
			$matche = preg_split( '/[\b\s\-\.:,>«]+/', $text, -1, PREG_SPLIT_OFFSET_CAPTURE);
			$start = 0;
			$res = '';
			foreach( $matche as $m ) {
				$res .= substr( $text, $start, $m[1] - $start );
				if ( preg_match('/[әөүіӘӨҮІ]/u', $m[0]) && !preg_match('/[еэгғкқЕЭГҒКҚ]/u', $m[0]) )
				{
					$res .= 'ء'.$m[0];
				} else {
					$res .= $m[0];
				}
				$start = $m[1] + strlen($m[0]);
			}
			// Convert Text
			$text =& $res;
			foreach( $arb as $k => $v ) {
				$text = preg_replace( $k, $v, $text );
			}
			// Arabic text results
			return $text;
		   } else {
			return $text;
		}
	}
}

new kk_convert;

?>