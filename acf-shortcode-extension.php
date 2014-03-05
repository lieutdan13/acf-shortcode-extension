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

	// create an array of comma separated fields
	$fields_array = explode ( ',', $fields );

	// initialize an array of label => value
	$values_array = array();

	// get all field objects for this post
	$field_objects = get_field_objects( $post_id );

	foreach ( $field_objects as $field_object ) {
		if ( isset( $field_object['label'] ) && isset ( $field_object['value'] ) ) {
			if ( '*' == $fields || in_array ( $field_object['name'], $fields_array ) ) {
				if ( 'google_map' == $field_object['type'] ) {
					$values_array[ $field_object['label'] ] = $field_object['value']['lat'] . ', ' .
						$field_object['value']['lng'] . ' (' .
						$field_object['value']['address'] . ')';
				} elseif ( 'select' == $field_object['type'] ) {
					$values_array[ $field_object['label'] ] = implode ( ',', $field_object['value'] );
				} elseif ( is_array ( $field_object['value'] ) ) {
					$values_array[ $field_object['label'] ] = implode ( ',', $field_object['value'] );
				} else {
					$values_array[ $field_object['label'] ] = $field_object['value'];
				}
			}
		}
	}

	$content = '<pre>' . print_r( $values_array, true ) . '</pre>';
	return $content;
}
add_shortcode( 'acfx', 'acfx_shortcode' );
