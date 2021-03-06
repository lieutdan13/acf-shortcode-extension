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
		'fields'         => '*',
		'post_id'        => false,
		'format_type'    => 'table',
	), $atts ) );

	// create an array of comma separated fields
	$fields_array = explode ( ',', $fields );

	// initialize an array of label => value
	$values_array = array();

	// get all field objects for this post
	$field_objects = get_field_objects( $post_id );

	$content_header = '';
	foreach ( $field_objects as $field_object ) {
		if ( preg_match( '/^_/', $field_object['name']) ) {
			if ( $field_object['name'] == '_group_header' ) {
				$content_header = '<div class="header">' . $field_object['value'] . '</div>';
			}
			continue;
		}
		if ( isset( $field_object['label'] ) && isset ( $field_object['value'] ) ) {
			if ( '*' == $fields || in_array ( $field_object['name'], $fields_array ) ) {
				$values_array[ $field_object['order_no'] ] = array(
					'type'  => $field_object['type'],
					'label' => $field_object['label'],
					'value' => acfx_get_formatted_value( $field_object, $format_type, $post_id ),
				);
			}
		}
	}
	ksort( $values_array, SORT_NUMERIC );

	$content = "";
	if ( 'debug' == $format_type ) {
		$content .= '<pre>';
	} else if ( 'table' == $format_type ) {
		$content .= '<table>';
	} else {
		$content .= '<div>';
	}
	foreach ( $values_array as $data ) {
		if ( 'text' == $format_type || 'debug' == $format_type ) {
			$content .= $data['label'] . ': ' . $data['value'] . '<br/>';
		} elseif ( 'table' == $format_type ) {
			if ( 'google_map' == $data['type'] ) {
				$content .= '<tr>' .
					'<td class="label" colspan="2">' . $data['label'] . '</td>' .
					'</tr><tr>' .
					'<td class="data spanned" colspan="2">' . $data['value'] . '</td>' .
					'</tr>';
			} else {
				$content .= '<tr>' .
					'<td class="label">' . $data['label'] . '</td>' .
					'<td class="data">' . $data['value'] . '</td>' .
					'</tr>';
			}
		}
	}
	if ( 'debug' == $format_type ) {
		$content .= '</pre>';
	} elseif ( 'table' == $format_type ) {
		$content .= '</table>';
	} else {
		$content .= '</div>';
	}
	if ( $content_header ) {
		$content = $content_header . $content;
	}
	$content = '<div class="acfx_data">' . $content . '</div>';
	return $content;
}
add_shortcode( 'acfx', 'acfx_shortcode' );

/*
*  acfx_get_formatted_value()
*
*  This function returns the formatted value based on field type and format type
*
*  @type	function
*  @since	0.1
*  @date	03/04/2014
*
*  @param	array	$field_object: an array of field object attributes
*  @param	string  $format_type: the format of the value to be returned
*  @param	mixed   $post_id: database ID of the post
*
*  @return	string: the formatted value
*/

function acfx_get_formatted_value( $field_object, $format_type, $post_id ) {
	if ( 'debug' == $format_type ) {
		return print_r( $field_object, true );
	} elseif ( 'text' == $format_type ) {
		if ( 'google_map' == $field_object['type'] ) {
			return  $field_object['value']['lat'] . ', ' .
				$field_object['value']['lng'] . ' (' .
				$field_object['value']['address'] . ')';
		} elseif ( is_array ( $field_object['value'] ) ) {
			return implode ( ',', $field_object['value'] );
		} else {
			return $field_object['value'];
		}
	} elseif ( 'table' == $format_type ) {
		if ( 'google_map' == $field_object['type'] ) {
			$params = array(
				't' => 'p',
				'q' => $field_object['value']['lat'] . ',' . $field_object['value']['lng'],
				'z' => 14,
				'output' => 'embed',
			);
			$value = '<div class="google-map"><iframe src="//maps.google.com/maps?' . http_build_query( $params ) . '"></iframe></div>';
			return $value;
		} elseif ( 'date_picker' == $field_object['type'] ) {
			$date = DateTime::createFromFormat('Ymd', $field_object['value'] );
			return $date->format('m/d/Y');
		} else {
			return acfx_get_formatted_value( $field_object, 'text', $post_id );
		}
	}
}

/*
*  acfx_scripts()
*
*  This function loads the plugin's stylesheet
*
*  @type	function
*  @since	0.1
*  @date	03/06/2014
*/

function acfx_scripts() {
	wp_register_style( 'acfx_styles', plugins_url( 'styles.css', __FILE__ ) );
	wp_enqueue_style( 'acfx_styles' );
}
add_action( 'wp_enqueue_scripts', 'acfx_scripts' );
