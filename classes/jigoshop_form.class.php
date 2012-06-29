<?php
/**
 * Jigoshop Form Class for displaying various input types in forms
 *
 * DISCLAIMER
 *
 * Do not edit or add directly to this file if you wish to upgrade Jigoshop to newer
 * versions in the future. If you wish to customise Jigoshop core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package             Jigoshop
 * @category            Admin
 * @author              Jigowatt
 * @copyright           Copyright © 2011-2012 Jigowatt Ltd.
 * @license             http://jigoshop.com/license/commercial-edition
 */

class Jigoshop_Form extends Jigoshop_Base {

	public static function input( $field ) {
		global $post;

		$args = array(
			'id'            => null,
			'label'         => null,
			'after_label'   => null,
			'class'         => 'short',
			'desc'          => false,
			'tip'           => null,
			'value'         => null,
			'placeholder'   => null,
		);
		extract( wp_parse_args( $field, $args ) );

		$value = ($value) ? esc_attr( $value ) : get_post_meta( $post->ID, $id, true) ;

		$html  = '';

		$html .= "<p class='form-field {$id}_field'>";
		$html .= "<label for='{$id}'>$label{$after_label}</label>";
		$html .= "<input type='text' class='{$class}' name='{$id}' id='{$id}' value='{$value}' placeholder='{$placeholder}' />";

		if ( $desc ) {
			$html .= '<span class="description">'.$desc.'</span>';
		}

		$html .= "</p>";
		return $html;
	}

	public static function select( $field ) {
		global $post;

		$args = array(
			'id'            => null,
			'label'         => null,
			'after_label'   => null,
			'class'         => 'select short',
			'desc'          => false,
			'tip'           => null,
			'options'       => array(),
			'selected'      => false
		);
		extract( wp_parse_args( $field, $args ) );

		$selected = ($selected) ? $selected : get_post_meta($post->ID, $id, true);
		$desc  = ($desc)  ? esc_html( $desc ) : false;

		$html = '';

		$html .= "<p class='form-field {$id}_field'>";
		$html .= "<label for='{$id}'>$label{$after_label}</label>";
		$html .= "<select id='{$id}' name='{$id}' class='{$class}'>";

		foreach ( $options as $value => $label ) {
			$mark = '';

			// Not the best way but has to be done because selected() echos
			if ( $selected == $value ) {
				$mark = 'selected="selected"';
			}
			$html .= "<option value='{$value}' {$mark}>{$label}</option>";
		}

		$html .= "</select>";

		if ( $desc ) {
			$html .= "<span class='description'>$desc</span>";
		}

		$html .= "</p>";
		return $html;
	}

	public static function checkbox( $field ) {
		global $post;

		$args = array(
			'id'            => null,
			'label'         => null,
			'after_label'   => null,
			'class'         => 'checkbox',
			'desc'          => false,
			'tip'           => null,
			'value'         => false
		);
		extract( wp_parse_args( $field, $args ) );

		$value = ($value) ? $value : get_post_meta($post->ID, $id, true);
		$desc  = ($desc)  ? esc_html($desc) : false;

		$html = '';

		$mark = '';
		if ( $value ) {
			$mark = 'checked="checked"';
		}

		$html .= "<p class='form-field {$id}_field'>";
		$html .= "<label for='{$id}'>$label{$after_label}</label>";
		$html .= "<input type='checkbox' name='{$id}' value='1' class='{$class}' id='{$id}' {$mark} />";

		if ( $desc ) {
			$html .= "<label for='{$id}' class='description'>$desc</label>";
		}

		$html .= "</p>";
		return $html;
	}
}
