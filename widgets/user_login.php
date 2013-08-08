<?php
/**
 * User Login Widget
 *
 * DISCLAIMER
 *
 * Do not edit or add directly to this file if you wish to upgrade Jigoshop to newer
 * versions in the future. If you wish to customise Jigoshop core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package             Jigoshop
 * @category            Widgets
 * @author              Jigoshop
 * @copyright           Copyright © 2011-2013 Jigoshop.
 * @license             http://jigoshop.com/license/commercial-edition
 */

class Jigoshop_Widget_User_Login extends WP_Widget {

	/**
	 * Constructor
	 *
	 * Setup the widget with the available options
	 * Add actions to clear the cache whenever a post is saved|deleted or a theme is switched
	 */
	public function __construct() {
		$options = array(
			'classname'	=> 'widget_user_login',
			'description'	=> __( 'Displays a handy login form for users', 'jigoshop' )
		);

		parent::__construct( 'user-login', __( 'Jigoshop: Login', 'jigoshop' ), $options );
	}

	/**
	 * Widget
	 *
	 * Display the widget in the sidebar
	 * Save output to the cache if empty
	 *
	 * @param	array	sidebar arguments
	 * @param	array	instance
	 */
	public function widget( $args, $instance ) {

		extract( $args );

		// Print the widget wrapper
		echo $before_widget;

		if( is_user_logged_in() ) {

			// Get current user instance
			global $current_user;

			// Print title
			$title = ( $instance['title_user'] ) ? $instance['title_user'] : __( 'Hey %s!', 'jigoshop' );
			echo $before_title . sprintf( $title, ucwords( $current_user->display_name ) ) . $after_title;

			// Create the default set of links
			$links = apply_filters( 'jigoshop_widget_logout_user_links' , array(
				__( 'My Account', 'jigoshop' )     => get_permalink( jigoshop_get_page_id('myaccount') ),
				__( 'Change Password', 'jigoshop' )=> get_permalink( jigoshop_get_page_id('change_password') ),
				__( 'Logout', 'jigoshop' )         => wp_logout_url( home_url() ),
			));

		} else {

			// Print title
			$title = ( $instance['title_guest'] ) ? $instance['title_guest'] : __( 'Login', 'jigoshop' );
			echo $before_title . $title . $after_title;

			do_action( 'jigoshop_widget_login_before_form' );

			// Get redirect URI
			$redirect_to = apply_filters( 'jigoshop_widget_login_redirect', get_permalink( jigoshop_get_page_id('myaccount') ) );
			$user_login = isset( $user_login ) ? $user_login : null;

			echo "<form action='".esc_url(wp_login_url( $redirect_to ))."' method='post' class='jigoshop_login_widget'>";

			// Username
			echo "
			<p>
				<label for='log'>".__( 'Username', 'jigoshop' )."</label>
				<input type='text' name='log' id='log' class='input-text username' />
			</p>
			";

			// Password
			echo "
			<p>
				<label for='pwd'>".__( 'Password', 'jigoshop' )."</label>
				<input type='password' name='pwd' id='pwd' class='input-text password' />
			</p>
			";

			echo "
			<p>
				<input type='submit' name='submit' value='".__( 'Login', 'jigoshop' )."' class='input-submit' />
				<a class='forgot' href='".esc_url(wp_lostpassword_url( $redirect_to ))."'>".__( 'Forgot it?', 'jigoshop' )."</a>
			</p>
			";

			if (Jigoshop_Base::get_options()->get_option( 'jigoshop_enable_signup_form' ) == 'yes' ) {
				echo '<p class="register">';
				wp_register(__('New user?','jigoshop') . ' ' , '');
				echo '</p>';
			}
			
			echo "</form>";

			do_action( 'jigoshop_widget_login_after_form' );

			$links = apply_filters( 'jigoshop_widget_login_user_links', array() );
		}

		// Loop & print out the links
		if( $links ) {
			echo "
			<nav role='navigation'>
				<ul class='pagenav'>";

				foreach( $links as $title => $href ) {
					$href = esc_url( $href );
					echo "<li><a title='Go to {$title}' href='{$href}'>{$title}</a></li>";
				}

			echo "
				</ul>
			</nav>";
		}

		// Print closing widget wrapper
		echo $after_widget;
	}

	/**
	 * Update
	 *
	 * Handles the processing of information entered in the wordpress admin
	 * Flushes the cache & removes entry from options array
	 *
	 * @param	array	new instance
	 * @param	array	old instance
	 * @return	array	instance
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		// Save the new values
		$instance['title_guest']	= strip_tags( $new_instance['title_guest'] );
		$instance['title_user']	= strip_tags( $new_instance['title_user'] );

		return $instance;
	}

	/**
	 * Form
	 *
	 * Displays the form for the wordpress admin
	 *
	 * @param	array	instance
	 */
	public function form( $instance ) {

		// Get instance data
		$title_guest 	= isset( $instance['title_guest'] ) ? esc_attr( $instance['title_guest'] ) : null;
		$title_user	= isset( $instance['title_user'] ) ? esc_attr( $instance['title_user'] ) : null;

		// Title for Guests
		echo "
		<p>
			<label for='{$this->get_field_id('title_guest')}'>".__( 'Title (Logged Out):', 'jigoshop' )."</label>
			<input class='widefat' id='{$this->get_field_id('title_guest')}' name='{$this->get_field_name('title_guest')}' type='text' value='{$title_guest}' />
		</p>
		";

		// Title for Users
		echo "
		<p>
			<label for='{$this->get_field_id('title_user')}'>".__( 'Title (Logged In):', 'jigoshop' )."</label>
			<input class='widefat' id='{$this->get_field_id('title_user')}' name='{$this->get_field_name('title_user')}' type='text' value='{$title_user}' />
		</p>
		";
	}

} // class Jigoshop_Widget_Recent_Products
