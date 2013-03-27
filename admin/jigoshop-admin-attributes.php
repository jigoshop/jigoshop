<?php
/**
 * Functions used for the attributes section in WordPress Admin
 *
 * The attributes section lets users add custom attributes to assign to products - they can also be used in the layered nav widgets.
 *
 * DISCLAIMER
 *
 * Do not edit or add directly to this file if you wish to upgrade Jigoshop to newer
 * versions in the future. If you wish to customise Jigoshop core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package             Jigoshop
 * @category            Admin
 * @author              Jigoshop
 * @copyright           Copyright © 2011-2013 Jigoshop.
 * @license             http://jigoshop.com/license/commercial-edition
 */

/**
 * Shows the created attributes and lets you add new ones.
 * The added attributes are stored in the database and can be used for layered navigation.
 *
 * @since 		1.0
 * @usedby 		jigoshop_admin_menu2()
 */
function jigoshop_attributes() {

	global $wpdb;
    $jigoshop_options = Jigoshop_Base::get_options();

	if (isset($_POST['add_new_attribute']) && $_POST['add_new_attribute']) :

		check_admin_referer( 'jigoshop-add-attribute', '_jigoshop_csrf' );

		$attribute_label = (string) strip_tags( stripslashes( $_POST['attribute_label'] ) );
		$attribute_name = !$_POST['attribute_name']
							? sanitize_title(sanitize_user($attribute_label, $strict = true))
							: sanitize_title(sanitize_user($_POST['attribute_name'], $strict = true));
		$attribute_type = (string) $_POST['attribute_type'];

		if ( (empty($attribute_name) && empty($attribute_label)) || empty($attribute_label) ):
			print_r('<div id="message" class="error"><p>'.__('Please enter an attribute label.', 'jigoshop' ).'</p></div>');
		elseif ($attribute_name && strlen($attribute_name)<30 && $attribute_type && !taxonomy_exists('pa_'.sanitize_title($attribute_name))) :

			$wpdb->insert( $wpdb->prefix . "jigoshop_attribute_taxonomies", array( 'attribute_name' => $attribute_name, 'attribute_label' => $attribute_label, 'attribute_type' => $attribute_type ), array( '%s', '%s' ) );

			do_action( 'jigoshop_attribute_admin_add_after_save', $attribute_name, $attribute_label, $attribute_type);
      
			$jigoshop_options->set_option('jigowatt_update_rewrite_rules', '1');

			wp_safe_redirect( get_admin_url() . 'edit.php?post_type=product&page=jigoshop_attributes' );
			exit;
		else :
			print_r('<div id="message" class="error"><p>'.__('That attribute already exists, no additions were made.', 'jigoshop' ).'</p></div>');
		endif;

	elseif (isset($_POST['save_attribute']) && $_POST['save_attribute'] && isset($_GET['edit'])) :

		$edit = absint($_GET['edit']);

		check_admin_referer( 'jigoshop-edit-attribute_' . $edit, '_jigoshop_csrf' );

		if ($edit>0) :

			$attribute_type = $_POST['attribute_type'];
			$attribute_label = (string) strip_tags( stripslashes( $_POST['attribute_label'] ) );

			$wpdb->update( $wpdb->prefix . "jigoshop_attribute_taxonomies", array( 'attribute_type' => $attribute_type, 'attribute_label' => $attribute_label ), array( 'attribute_id' => $_GET['edit'] ), array( '%s', '%s') );

			do_action( 'jigoshop_attribute_admin_edit_after_update', $edit, $attribute_label, $attribute_type);
      
		endif;

		wp_safe_redirect( get_admin_url() . 'edit.php?post_type=product&page=jigoshop_attributes' );
		exit;

	elseif (isset($_GET['delete'])) :

		$delete = absint($_GET['delete']);

		check_admin_referer( 'jigoshop-delete-attribute_' . $delete );

		if ($delete>0) :

			$att_name = $wpdb->get_var( $wpdb->prepare( "SELECT attribute_name FROM " . $wpdb->prefix . "jigoshop_attribute_taxonomies WHERE attribute_id = %d", $delete ) );

			if ($att_name && $wpdb->query( $wpdb->prepare( "DELETE FROM " . $wpdb->prefix . "jigoshop_attribute_taxonomies WHERE attribute_id = %d", $delete ) ) ) :

				$taxonomy = 'pa_'.sanitize_title($att_name);

				// Old taxonomy prefix left in for backwards compatibility
				if (taxonomy_exists($taxonomy)) :

					$terms = get_terms($taxonomy, 'orderby=name&hide_empty=0');
					foreach ($terms as $term) {
						wp_delete_term( $term->term_id, $taxonomy );
					}

				endif;

				do_action( 'jigoshop_attribute_admin_delete_after', $delete, $att_name);
        
				wp_safe_redirect( get_admin_url() . 'edit.php?post_type=product&page=jigoshop_attributes' );
				exit;

			endif;

		endif;

	endif;

	if (isset($_GET['edit']) && $_GET['edit'] > 0) :
		jigoshop_edit_attribute();
	else :
		jigoshop_add_attribute();
	endif;

}

/**
 * Edit Attribute admin panel
 *
 * Shows the interface for changing an attributes type between select, multiselect and text
 *
 * @since 		1.0
 * @usedby 		jigoshop_attributes()
 */
function jigoshop_edit_attribute() {

	global $wpdb;

	$edit = absint($_GET['edit']);

	$att_type = $wpdb->get_var( $wpdb->prepare( "SELECT attribute_type FROM " . $wpdb->prefix . "jigoshop_attribute_taxonomies WHERE attribute_id = %d", $edit ) );
	$att_label = $wpdb->get_var( $wpdb->prepare( "SELECT attribute_label FROM " . $wpdb->prefix . "jigoshop_attribute_taxonomies WHERE attribute_id = %d", $edit ) );
	?>
	<div class="wrap jigoshop">
		<div class="icon32 icon32-attributes" id="icon-jigoshop"><br/></div>
	    <h2><?php _e('Attributes','jigoshop') ?></h2>
	    <br class="clear" />
	    <div id="col-container">
	    	<div id="col-left">
	    		<div class="col-wrap">
	    			<div class="form-wrap">
	    				<h3><?php _e('Edit Attribute','jigoshop') ?></h3>
	    				<p><?php _e('Attribute taxonomy names cannot be changed; you may only change an attributes type.','jigoshop') ?></p>
	    				<form action="admin.php?page=jigoshop_attributes&amp;edit=<?php echo esc_attr( $edit ); ?>" method="post">
								<?php wp_nonce_field( 'jigoshop-edit-attribute_' . absint( $edit ), '_jigoshop_csrf' ); ?>
							<div class="form-field">
								<label for="attribute_label"><?php _e('Attribute Label', 'jigoshop'); ?></label>
								<input name="attribute_label" id="attribute_label" type="text" value="<?php echo esc_attr( $att_label ); ?>" />
								<p class="description"><?php _e('The label is how it appears on your site.', 'jigoshop'); ?></p>
							</div>
							<div class="form-field">
								<label for="attribute_type"><?php _e('Attribute type', 'jigoshop'); ?></label>
								<select name="attribute_type" id="attribute_type" style="width: 100%;">
									<option value="select" <?php if ($att_type=='select') echo 'selected="selected"'; ?>><?php _e('Select','jigoshop') ?></option>
									<option value="multiselect" <?php if ($att_type=='multiselect') echo 'selected="selected"'; ?>><?php _e('Multiselect','jigoshop') ?></option>
									<option value="text" <?php if ($att_type=='text') echo 'selected="selected"'; ?>><?php _e('Text','jigoshop') ?></option>
								</select>
							</div>

							<?php do_action('jigoshop_attribute_admin_edit_before_submit', $edit, $att_type, $att_label) ?>
              
							<p class="submit"><input type="submit" name="save_attribute" id="submit" class="button" value="<?php esc_html_e('Save Attribute', 'jigoshop'); ?>"></p>
	    				</form>
	    			</div>
	    		</div>
	    	</div>
	    </div>
	</div>
	<?php

}

/**
 * Add Attribute admin panel
 *
 * Shows the interface for adding new attributes
 *
 * @since 		1.0
 * @usedby 		jigoshop_attributes()
 */
function jigoshop_add_attribute() {
	?>
	<div class="wrap jigoshop">
		<div class="icon32 icon32-attributes" id="icon-jigoshop"><br/></div>
	    <h2><?php _e('Attributes','jigoshop') ?></h2>
	    <br class="clear" />
	    <div id="col-container">
	    	<div id="col-right">
	    		<div class="col-wrap">
		    		<table class="widefat fixed" style="width:100%">
				        <thead>
				            <tr>
				                <th scope="col"><?php _e('Label','jigoshop') ?></th>
				                <th scope="col"><?php _e('Slug','jigoshop') ?></th>
				                <th scope="col"><?php _e('Type','jigoshop') ?></th>
				                <th scope="col" colspan="2"><?php _e('Terms','jigoshop') ?></th>
				            </tr>
				        </thead>
				        <tbody>
				        	<?php
				        		$attribute_taxonomies = jigoshop_product::getAttributeTaxonomies();
				        		if ( $attribute_taxonomies ) :
				        			foreach ($attribute_taxonomies as $tax) :
				        				$att_title = $tax->attribute_name;
				        				if ( isset( $tax->attribute_label ) ) { $att_title = $tax->attribute_label; }
				        				?><tr>

				        					<td><a href="edit-tags.php?taxonomy=pa_<?php echo sanitize_title($tax->attribute_name); ?>&amp;post_type=product"><?php echo esc_html( ucwords( $att_title ) ); ?></a>

														<div class="row-actions"><span class="edit"><a href="<?php echo esc_url( add_query_arg('edit', $tax->attribute_id, 'admin.php?page=jigoshop_attributes') ); ?>"><?php _e('Edit', 'jigoshop'); ?></a> | </span><span class="delete"><a class="delete" href="<?php echo esc_url( wp_nonce_url( add_query_arg('delete', $tax->attribute_id, 'admin.php?page=jigoshop_attributes'), 'jigoshop-delete-attribute_' . $tax->attribute_id ) ); ?>"><?php _e('Delete', 'jigoshop'); ?></a></span></div>
				        					</td>
				        					<td><?php echo $tax->attribute_name; ?></td>
				        					<td><?php echo esc_html ( ucwords( $tax->attribute_type ) ); ?></td>
				        					<td><?php
				        						if (taxonomy_exists('pa_'.sanitize_title($tax->attribute_name))) :
					        						$terms_array = array();
					        						$terms = get_terms( 'pa_'.sanitize_title($tax->attribute_name), 'orderby=name&hide_empty=0' );
					        						if ($terms) :
						        						foreach ($terms as $term) :
															$terms_array[] = $term->name;
														endforeach;
														echo implode(', ', $terms_array);
													else :
														echo '<span class="na">&ndash;</span>';
													endif;
												else :
													echo '<span class="na">&ndash;</span>';
												endif;
				        					?></td>
				        					<td><a href="edit-tags.php?taxonomy=pa_<?php echo sanitize_title( $tax->attribute_name ); ?>&amp;post_type=product" class="button alignright"><?php _e('Configure&nbsp;terms', 'jigoshop'); ?></a></td>
				        				</tr><?php
				        			endforeach;
				        		else :
				        			?><tr><td colspan="5"><?php _e('No attributes currently exist.', 'jigoshop') ?></td></tr><?php
				        		endif;
				        	?>
				        </tbody>
				    </table>
	    		</div>
	    	</div>
	    	<div id="col-left">
	    		<div class="col-wrap">
	    			<div class="form-wrap">
	    				<h3><?php _e('Add New Attribute','jigoshop') ?></h3>
	    				<form action="edit.php?post_type=product&page=jigoshop_attributes" method="post">
								<?php wp_nonce_field( 'jigoshop-add-attribute', '_jigoshop_csrf' ); ?>
							<div class="form-field">
								<label for="attribute_label"><?php _e('Attribute Label', 'jigoshop'); ?></label>
								<input name="attribute_label" id="attribute_label" type="text" value="" />
								<p class="description"><?php _e('The label is how it appears on your site.', 'jigoshop'); ?></p>
							</div>
							<div class="form-field">
								<label for="attribute_name"><?php _e('Attribute Slug', 'jigoshop'); ?></label>
								<input name="attribute_name" id="attribute_name" type="text" value="" />
								<p class="description"><?php _e('Slug for your attribute (optional).', 'jigoshop'); ?></p>
							</div>
							<div class="form-field">
								<label for="attribute_type"><?php _e('Attribute type', 'jigoshop'); ?></label>
								<select name="attribute_type" id="attribute_type" class="postform">
									<option value="multiselect"><?php _e('Multiselect','jigoshop') ?></option>
									<option value="select"><?php _e('Select','jigoshop') ?></option>
									<option value="text"><?php _e('Text','jigoshop') ?></option>
								</select>
							</div>

							<?php do_action('jigoshop_attribute_admin_add_before_submit') ?>
              
							<p class="submit"><input type="submit" name="add_new_attribute" id="submit" class="button" value="<?php esc_html_e('Add Attribute', 'jigoshop'); ?>"></p>
	    				</form>
	    			</div>
	    		</div>
	    	</div>
	    </div>
	    <script type="text/javascript">
		/* <![CDATA[ */

			jQuery('a.delete').click(function(){
	    		var answer = confirm ("<?php _e('Are you sure you want to delete this?', 'jigoshop'); ?>");
				if (answer) return true;
				return false;
	    	});

		/* ]]> */
		</script>
	</div>
	<?php
}