<?php
/*
Plugin Name: Sourcedome Matrix Visualization
Plugin URI: https://github.com/burkhardm/sourcedome-plugins
Description: Visualizes nodes & links as m-by-n matrix using D3.js.
Version: 0.1
Author: Martin Burkhard
Author URI: http://www.sourcedome.de
License: The MIT License (MIT)
*/

/* Add shortcode for inserting plugin on Wordpress pages */
add_shortcode( 'sd_matrixviz', 'sd_createMatrixViz' );

/* Create visualization frameworks */
function sd_createMatrixViz($atts) {

	/* extract shortcode parameters */
	extract( shortcode_atts( array(
		'data' => 'vizframeworks.json', /* default: vizframeworks.json */
	), $atts ) );
		
	if(sd_beginsWith($data, 'http://')) {
		$data_path = $data;
	}
	else {
		$data_path = plugins_url( $data , __FILE__ );
	}
		
	/* begin PHP buffer */
	ob_start();
	
	/* read visualization contents */
	include(plugin_dir_path(__FILE__) . "d3matrixviz.php");
	
	/* write buffered contents to position of shortcode /*/
	return ob_get_clean();	
}

/* returns true if $str begins with $substr */
function sd_beginsWith( $str, $substr ) {
    return ( substr( $str, 0, strlen( $substr ) ) == $substr );
}

?>