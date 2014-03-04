<?php
/**
 * Plugin Name: ACF Shortcode Extension
 * Plugin URI: https://github.com/lieutdan13/acf-shortcode-extension
 * Description: An extension to the shortcode feature of the Advanced Custom Fields Wordpress plugin
 * Version: 0.1
 * Author: Dan Schaefer
 * Author URI: http://www.schaeferzone.net
 * License: WTFPL (http://www.wtfpl.net/)
 */

/*
*  acfx_shortcode()
*
*  This function is used to add advanced shortcode support for the ACF plugin
*
*  @type	function
*  @since	0.1
*  @date	03/04/2014
*
*  @param	array	$atts: an array holding the shortcode options
*			string + fields: comma separated list of fields
*			mixed  + post_id: the id of the post from which to load the fields
*
*  @return	string	$content: the formatted content
*/

function acfx_shortcode( $atts ) {
	// extract attributes
	extract( shortcode_atts( array(
		'fields'  => '*',
		'post_id' => false,
	), $atts ) );

	// get all field objects for this post
	$field_objects = get_field_objects( $post_id );

	$content = '<pre>' . print_r( $field_objects ) . '</pre>';
	return $content;
}
add_shortcode( 'acfx', 'acfx_shortcode' );
