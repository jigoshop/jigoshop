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

	public static function select( $ID, $label, $options, $selected = false,  $desc = FALSE, $class = 'select short' ) {
		global $post;

		$selected = ($selected) ? $selected : get_post_meta($post->ID, $ID, true);
		$desc  = ($desc)  ? esc_html($desc) : false;
		$label = __($label, 'jigoshop');
		$html = '';

		$html .= "<p class='form-field {$ID}_field'>";
		$html .= "<label for='{$ID}'>$label</label>";
		$html .= "<select id='{$ID}' name='{$ID}' class='{$class}'>";

		foreach( $options as $value => $label ) {
			$mark = '';

			// Not the best way but has to be done because selected() echos
			if( $selected == $value ) {
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

	public static function checkbox( $ID, $label, $value = FALSE, $desc = FALSE, $class = 'checkbox' ) {
		global $post;

		$value = ($value) ? $value : get_post_meta($post->ID, $ID, true);
		$desc  = ($desc)  ? esc_html($desc) : false;
		$label = __($label, 'jigoshop');
		$html = '';

		$mark = '';
		if( $value ) {
			$mark = 'checked="checked"';
		}

		$html .= "<p class='form-field {$ID}_field'>";
		$html .= "<label for='{$ID}'>$label</label>";
		$html .= "<input type='checkbox' name='{$ID}' value='1' class='{$class}' id='{$ID}' {$mark} />";

		if ( $desc ) {
			$html .= "<label for='{$ID}' class='description'>$desc</label>";
		}

		$html .= "</p>";
		return $html;
	}
}
