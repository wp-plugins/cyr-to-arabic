<?php
/////
class kkconverter extends WP_Widget {
     function kkconverter() {
         $widget_ops = array('description' => 'Қазақ кирил, төте, латын әріптерін өзара алмастыру программасы');
         $this->WP_Widget('kkconverter', 'Сәйкестіргіш', $widget_ops);
     }
     function widget($args) {
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

}
add_action( 'widgets_init', function(){
	register_widget( 'kkconverter' );
});
?>