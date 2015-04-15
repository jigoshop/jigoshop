<?php

require_once(JIGOSHOP_DIR.'/classes/jigoshop_user.class.php');

function jigoshop_admin_user_profile(WP_User $user){
	$customer = new jigoshop_user($user->ID);

	jrto_enqueue_script('admin', 'jigoshop-select2', JIGOSHOP_URL.'/assets/js/select2.min.js', array('jquery'));
	jrto_enqueue_style('admin', 'jigoshop-select2', JIGOSHOP_URL.'/assets/css/select2.css');

	jigoshop_render('admin/user-profile', array(
		'user' => $user,
		'customer' => $customer,
	));
}

function jigoshop_admin_user_profile_update($id){
	$customer = new jigoshop_user($id);
	@list($_POST['jigoshop']['billing_country'], $_POST['jigoshop']['billing_state']) = explode(':', $_POST['jigoshop']['billing_country']);
	@list($_POST['jigoshop']['shipping_country'], $_POST['jigoshop']['shipping_state']) = explode(':', $_POST['jigoshop']['shipping_country']);
	$customer->populate($_POST['jigoshop']);
	$customer->save();
}

add_action('edit_user_profile', 'jigoshop_admin_user_profile');
add_action('show_user_profile', 'jigoshop_admin_user_profile');

add_action('personal_options_update', 'jigoshop_admin_user_profile_update');
add_action('edit_user_profile_update', 'jigoshop_admin_user_profile_update');
