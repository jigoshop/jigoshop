<?php
/**
 * Functions used for displaying the jigoshop dashboard
 * DISCLAIMER
 * Do not edit or add directly to this file if you wish to upgrade Jigoshop to newer
 * versions in the future. If you wish to customise Jigoshop core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package             Jigoshop
 * @category            Admin
 * @author              Jigoshop
 * @copyright           Copyright © 2011-2014 Jigoshop.
 * @license             GNU General Public License v3
 */

/**
 * Function for showing the dashboard
 * The dashboard shows widget for things such as:
 *    - Products
 *    - Sales
 *    - Recent reviews
 *
 * @since    1.0
 * @usedby    jigoshop_admin_menu()
 */

if(!function_exists('add_action')){
	header('Status: 403 Forbidden');
	header('HTTP/1.1 403 Forbidden');
	exit();
}

class jigoshop_dashboard {
	function __construct(){
		$this->page = 'toplevel_page_jigoshop';

		$this->on_load_page();
		$this->on_show_page();
	}

	function on_load_page(){
		wp_enqueue_script('common');
		wp_enqueue_script('wp-lists');
		wp_enqueue_script('postbox');

		add_meta_box('jigoshop_dash_right_now', __('Right Now', 'jigoshop'), array($this, 'jigoshop_dash_right_now'),
			$this->page, 'side', 'core');
		add_meta_box('jigoshop_dash_recent_orders', jigoshop_prepare_dashboard_title(__('Recent Orders', 'jigoshop')), array($this, 'jigoshop_dash_recent_orders'),
			$this->page, 'side', 'core');
		add_meta_box('jigoshop_dash_stock_report', jigoshop_prepare_dashboard_title(__('Stock Report', 'jigoshop')), array($this, 'jigoshop_dash_stock_report'),
			$this->page, 'side', 'core');
		add_meta_box('jigoshop_dash_monthly_report', jigoshop_prepare_dashboard_title(__('Monthly Report', 'jigoshop')), array($this, 'jigoshop_dash_monthly_report'),
			$this->page, 'normal', 'core');
		add_meta_box('jigoshop_dash_recent_reviews', jigoshop_prepare_dashboard_title(__('Recent Reviews', 'jigoshop')), array($this, 'jigoshop_dash_recent_reviews'),
			$this->page, 'normal', 'core');
		add_meta_box('jigoshop_dash_latest_news', jigoshop_prepare_dashboard_title(__('Latest News', 'jigoshop')), array($this, 'jigoshop_dash_latest_news'),
			$this->page, 'normal', 'core');
		add_meta_box('jigoshop_dash_useful_links', jigoshop_prepare_dashboard_title(__('Useful Links', 'jigoshop')), array($this, 'jigoshop_dash_useful_links'),
			$this->page, 'normal', 'core');
	}

	function on_show_page(){
		global $screen_layout_columns; ?>
		<div id="jigoshop-metaboxes-main" class="wrap">
			<form action="admin-post.php" method="post">
				<h3><?php _e('Jigoshop Dashboard', 'jigoshop'); ?></h3>

				<p id="wp-version-message"><?php _e('You are using', 'jigoshop'); ?>
					<strong>Jigoshop <?php echo jigoshop_get_plugin_data(); ?></strong>
				</p>

				<?php wp_nonce_field('jigoshop-metaboxes-main'); ?>
				<?php wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false); ?>
				<?php wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false); ?>

				<div class="pages">
					<ul class="pages">
						<?php global $submenu; ?>
						<?php foreach($submenu['jigoshop'] as $item): ?>
							<li><a href="<?php echo (strpos($item[2], 'edit.php') === false ? 'admin.php?page=' : '').$item[2]; ?>"><?php echo $item[0]; ?></a></li>
						<?php endforeach; ?>
					</ul>
				</div>

				<div id="dashboard-widgets" class="metabox-holder">
					<div id="postbox-container-1" class="postbox-container" style="width:50%;">
						<?php do_meta_boxes($this->page, 'side', null); ?>
					</div>
					<div id="post-body" class="has-sidebar">
						<div id="postbox-container-2" class="postbox-container" style="width:50%;">
							<?php do_meta_boxes($this->page, 'normal', null); ?>
						</div>
					</div>
					<br class="clear" />
				</div>
			</form>
		</div>
		<script type="text/javascript">
			//<![CDATA[
			jQuery(document).ready(function($){
				// close postboxes that should be closed
				$('.if-js-closed').removeClass('if-js-closed').addClass('closed');
				// postboxes setup
				postboxes.add_postbox_toggles('<?php echo $this->page; ?>');
			});
			//]]>
		</script>
	<?php
	}

	/**
	 * Right Now
	 */
	function jigoshop_dash_right_now(){
		?>

		<div id="jigoshop_right_now" class="jigoshop_right_now">
			<div class="table table_content">
				<p class="sub"><?php echo jigoshop_prepare_dashboard_title(__('Shop Content', 'jigoshop')); ?></p>
				<table>
					<tbody>
					<tr class="first">
						<td class="first b"><a href="edit.php?post_type=product"><?php
								$num_posts = wp_count_posts('product');
								$num = number_format_i18n($num_posts->publish);
								echo $num;
								?></a></td>
						<td class="t"><a href="edit.php?post_type=product"><?php _e('Products', 'jigoshop'); ?></a></td>
					</tr>
					<tr>
						<td class="first b"><a href="edit-tags.php?taxonomy=product_cat&post_type=product"><?php
								echo wp_count_terms('product_cat');
								?></a></td>
						<td class="t"><a href="edit-tags.php?taxonomy=product_cat&post_type=product"><?php _e('Product Categories', 'jigoshop'); ?></a></td>
					</tr>
					<tr>
						<td class="first b"><a href="edit-tags.php?taxonomy=product_tag&post_type=product"><?php
								echo wp_count_terms('product_tag');
								?></a></td>
						<td class="t"><a href="edit-tags.php?taxonomy=product_tag&post_type=product"><?php _e('Product Tag', 'jigoshop'); ?></a></td>
					</tr>
					<tr>
						<td class="first b"><a href="admin.php?page=jigoshop_attributes"><?php
								echo count(jigoshop_product::getAttributeTaxonomies());
								?></a></td>
						<td class="t"><a href="admin.php?page=jigoshop_attributes"><?php _e('Attribute taxonomies', 'jigoshop'); ?></a></td>
					</tr>
					</tbody>
				</table>
			</div>
			<div class="table table_discussion">
				<p class="sub"><?php jigoshop_prepare_dashboard_title(__('Orders', 'jigoshop')); ?></p>
				<table>
					<tbody>
					<?php $jigoshop_orders = new jigoshop_orders(); ?>
					<tr class="first pending-orders">
						<td class="b"><a href="edit.php?post_type=shop_order&shop_order_status=pending"><span class="total-count"><?php echo $jigoshop_orders->pending_count; ?></span></a></td>
						<td class="last t"><a class="pending" href="edit.php?post_type=shop_order&shop_order_status=pending"><?php _e('Pending', 'jigoshop'); ?></a></td>
					</tr>
					<tr class="on-hold-orders">
						<td class="b"><a href="edit.php?post_type=shop_order&shop_order_status=on-hold"><span class="total-count"><?php echo $jigoshop_orders->on_hold_count; ?></span></a></td>
						<td class="last t"><a class="onhold" href="edit.php?post_type=shop_order&shop_order_status=on-hold"><?php _e('On-Hold', 'jigoshop'); ?></a></td>
					</tr>
					<tr class="processing-orders">
						<td class="b"><a href="edit.php?post_type=shop_order&shop_order_status=processing"><span
									class="total-count"><?php echo $jigoshop_orders->processing_count; ?></span></a></td>
						<td class="last t"><a class="processing" href="edit.php?post_type=shop_order&shop_order_status=processing"><?php _e('Processing', 'jigoshop'); ?></a></td>
					</tr>
					<tr class="completed-orders">
						<td class="b"><a href="edit.php?post_type=shop_order&shop_order_status=completed"><span class="total-count"><?php echo $jigoshop_orders->completed_count; ?></span></a>
						</td>
						<td class="last t"><a class="complete" href="edit.php?post_type=shop_order&shop_order_status=completed"><?php _e('Completed', 'jigoshop'); ?></a></td>
					</tr>
					</tbody>
				</table>
			</div>
			<br class="clear" />
		</div>
	<?php
	}

	/**
	 * Recent Orders
	 */
	function jigoshop_dash_recent_orders(){
		$args = array(
			'numberposts' => 10,
			'orderby' => 'post_date',
			'order' => 'DESC',
			'post_type' => 'shop_order',
			'post_status' => 'publish'
		);
		$orders = get_posts($args);
		if($orders){
			echo '<ul class="recent-orders">';
			foreach($orders as $order){
				$this_order = new jigoshop_order($order->ID);
				$user = get_userdata($this_order->user_id);
				if($user){
					$user = array(
						'link' => get_edit_user_link($user->ID),
						'name' => $user->display_name,
					);
				} else {
					$user = array(
						'link' => '',
						'name' => __('guest', 'jigoshop'),
					);
				}

				$total_items = 0;
				foreach($this_order->items as $index => $item){
					$total_items += $item['qty'];
				}

				echo '
				<li>
					<span class="order-status '.sanitize_title($this_order->status).'">'.ucwords(__($this_order->status, 'jigoshop')).'</span> <a href="'.admin_url('post.php?post='.$order->ID).'&action=edit">'.$this_order->get_order_number().'</a>
					<span class="order-time">'.get_the_time(__('M d, Y', 'jigoshop'), $order->ID).'</span> <span class="order-customer"><a href="'.$user['link'].'">'.$user['name'].'</a></span>
					<small>'.sizeof($this_order->items).' '._n('Item', 'Items', sizeof($this_order->items), 'jigoshop').', <span class="total-quantity">'.__('Total Quantity', 'jigoshop').' '.$total_items.'</span> <span class="order-cost">'.jigoshop_price($this_order->order_total).'</span></small>
				</li>';

			}
			echo '</ul>';
		}
	}

	/**
	 * Stock Reports
	 */
	function jigoshop_dash_stock_report(){
		if(Jigoshop_Base::get_options()->get('jigoshop_manage_stock') == 'yes'){
			$lowstockamount = Jigoshop_Base::get_options()->get('jigoshop_notify_low_stock_amount');
			if(!is_numeric($lowstockamount)){
				$lowstockamount = 1;
			}

			$nostockamount = Jigoshop_Base::get_options()->get('jigoshop_notify_no_stock_amount');
			if(!is_numeric($nostockamount)){
				$nostockamount = 0;
			}

			$outofstock = array();
			$lowinstock = array();

			/** @var $wpdb WPDB */
			global $wpdb;
			// Download low in stock products
			$query = $wpdb->prepare("SELECT DISTINCT p.ID, p.post_parent FROM {$wpdb->posts} p
			LEFT JOIN {$wpdb->postmeta} pmm ON p.ID = pmm.post_id
			LEFT JOIN {$wpdb->postmeta} pms ON p.ID = pms.post_id
			LEFT JOIN {$wpdb->postmeta} pmb ON p.ID = pmb.post_id
			WHERE p.post_status = %s AND ((p.post_type = %s AND pmm.meta_value = 1 AND pmm.meta_key = %s AND pmb.meta_key = %s) OR p.post_type = %s) AND pms.meta_key = %s AND
				((pms.meta_value <= %d AND pms.meta_value > %d) OR (pms.meta_value = %d AND pmb.meta_value = %s))
			", array('publish', 'product', 'manage_stock', 'backorders', 'product_variation', 'stock', $lowstockamount, $nostockamount, $nostockamount, 'yes'));
			$results = $wpdb->get_results($query);

			foreach($results as $item){
				$id = $item->post_parent == 0 ? $item->ID : $item->post_parent;
				$lowinstock[] = array(
					'link' => get_edit_post_link($id),
					'title' => get_the_title($item->ID),
				);
			}

			// Download out of stock products
			$query = $wpdb->prepare("SELECT p.ID FROM {$wpdb->posts} p
			LEFT JOIN {$wpdb->postmeta} pmm ON p.ID = pmm.post_id AND pmm.meta_key = %s
			LEFT JOIN {$wpdb->postmeta} pms ON p.ID = pms.post_id AND pms.meta_key = %s
			LEFT JOIN {$wpdb->postmeta} pmb ON p.ID = pmb.post_id AND pmb.meta_key = %s
			WHERE p.post_type = %s AND p.post_status = %s AND pmm.meta_value = 1 AND pms.meta_value = %d AND pmb.meta_value <> %s
			", array('manage_stock', 'stock', 'backorders', 'product', 'publish', $nostockamount, 'yes'));
			$results = $wpdb->get_results($query);

			foreach($results as $id){
				$product = new jigoshop_product($id->ID);

				if(!$product->is_in_stock(true)){
					$outofstock[] = array(
						'link' => get_edit_post_link($id->ID),
						'title' => get_the_title($id->ID),
					);
				}
			}

			$outofstock = array_splice($outofstock, 0, 20);
			$lowinstock = array_splice($lowinstock, 0, 20);
			?>
			<div id="jigoshop_right_now" class="jigoshop_right_now">
				<div class="table table_content">
					<p class="sub"><?php _e('Low Stock', 'jigoshop'); ?></p>
					<ol>
						<?php if(count($lowinstock) > 0): ?>
							<?php foreach($lowinstock as $item): ?>
								<li><a href="<?php echo $item['link']; ?>"><?php echo $item['title']; ?></a></li>
							<?php endforeach; ?>
						<?php else: ?>
							<li><?php echo __('No products are low in stock.', 'jigoshop'); ?></li>
						<?php endif; ?>
					</ol>
				</div>
				<div class="table table_discussion">
					<p class="sub"><?php _e('Out of Stock/Backorders', 'jigoshop'); ?></p>
					<ol>
						<?php if(count($outofstock) > 0): ?>
							<?php foreach($outofstock as $item): ?>
								<li><a href="<?php echo $item['link']; ?>"><?php echo $item['title']; ?></a></li>
							<?php endforeach; ?>
						<?php else: ?>
							<li><?php echo __('No products are out of stock.', 'jigoshop'); ?></li>
						<?php endif; ?>
					</ol>
				</div>
				<br class="clear" />
			</div>
		<?php
		}
	}

	/**
	 * Recent Reviews
	 */
	function jigoshop_dash_recent_reviews(){
		global $wpdb;
		$comments = $wpdb->get_results("SELECT *, SUBSTRING(comment_content,1,100) AS comment_excerpt
		FROM $wpdb->comments
		LEFT JOIN $wpdb->posts ON ($wpdb->comments.comment_post_ID = $wpdb->posts.ID)
		WHERE comment_approved = '1'
		AND comment_type = ''
		AND post_password = ''
		AND post_type = 'product'
		ORDER BY comment_date_gmt DESC
		LIMIT 5");
		?>
		<div class="inside jigoshop-reviews-widget"><?php
		if($comments) :
			echo '<ul>';
			foreach($comments as $comment) :

				echo '<li>';

				echo get_avatar($comment->comment_author, '32');

				$rating = get_comment_meta($comment->comment_ID, 'rating', true);

				echo '<div class="star-rating" title="'.esc_attr($rating).'">
					<span style="width:'.($rating * 16).'px">'.$rating.' '.__('out of 5', 'jigoshop').'</span></div>';

				echo '<h4 class="meta"><a href="'.get_permalink($comment->ID).'#comment-'.$comment->comment_ID.'">'.$comment->post_title.'</a>'.__(' reviewed by ', 'jigoshop').''.strip_tags($comment->comment_author).'</h4>';
				echo '<blockquote>'.strip_tags($comment->comment_excerpt).' [...]</blockquote></li>';

			endforeach;
			echo '</ul>';
		else :
			echo '<p>'.__('There are no product reviews yet.', 'jigoshop').'</p>';
		endif;
		?></div><?php
	}

	/**
	 * Latest News
	 */
	function jigoshop_dash_latest_news(){
		if(file_exists(ABSPATH.WPINC.'/class-simplepie.php')){

			include_once(ABSPATH.WPINC.'/class-simplepie.php');

			$rss = fetch_feed('https://www.jigoshop.com/feed/');

			if(!is_wp_error($rss)){
				$maxitems = $rss->get_item_quantity(5);
				$rss_items = $rss->get_items(0, $maxitems);

				if($maxitems > 0){
					echo '<ul>';

					foreach($rss_items as $item){
						$title = wptexturize($item->get_title(), ENT_QUOTES, "UTF-8");
						$link = $item->get_permalink();
						$date = $item->get_date('U');

						if((abs(time() - $date)) < 86400){ // 1 Day
							$human_date = sprintf(__('%s ago', 'jigoshop'), human_time_diff($date));
						} else {
							$human_date = date(__('F jS Y', 'jigoshop'), $date);
						}

						echo '<li><a href="'.esc_url($link).'">'.$title.'</a> &ndash; <span class="rss-date">'.$human_date.'</span></li>';
					}
					echo '</ul>';
				} else {
					echo '<ul><li>'.__('No items found.', 'jigoshop').'</li></ul>';
				}
			} else {
				echo '<ul><li>'.__('No items found.', 'jigoshop').'</li></ul>';
			}
		}
	}

	/**
	 * Useful Links
	 */
	function jigoshop_dash_useful_links(){
		?>
		<div class="jigoshop-links-widget">
			<div class="links">
				<ul>
					<li><a href="https://www.jigoshop.com/tour/"><?php _e('Tour', 'jigoshop'); ?></a> &ndash; <?php _e('Take a tour of the plugin', 'jigoshop'); ?></li>
					<li><a href="https://www.jigoshop.com/product-category/extensions/"><?php _e('Extensions', 'jigoshop'); ?></a> &ndash; <?php _e('Extend Jigoshop with extra plugins and modules.', 'jigoshop'); ?></li>
					<li><a href="https://www.jigoshop.com/product-category/themes/"><?php _e('Themes', 'jigoshop'); ?></a> &ndash; <?php _e('Extend Jigoshop with themes.', 'jigoshop'); ?></li>
					<li><a href="http://twitter.com/#!/jigoshop"><?php _e('@Jigoshop', 'jigoshop'); ?></a> &ndash; <?php _e('Follow us on Twitter.', 'jigoshop'); ?></li>
					<li><a href="https://github.com/jigoshop/jigoshop"><?php _e('Jigoshop on Github', 'jigoshop'); ?></a> &ndash; <?php _e('Help extend Jigoshop.', 'jigoshop'); ?></li>
					<li><a href="https://wordpress.org/plugins/jigoshop/"><?php _e('Jigoshop on WordPress.org', 'jigoshop'); ?></a> &ndash; <?php _e('Leave us a rating!', 'jigoshop'); ?></li>
				</ul>
			</div>
			<div class="social">
				<div>
					<h4 class="first"><?php _e('Jigoshop Project', 'jigoshop') ?></h4>
					<p><?php echo __('Our team is available to help you with implementation of additional requirements or to provide you with custom development. Please contact our sales team to obtain a quote', 'jigoshop'); ?>
						: <span class="jigo-email"><a href="mailto:sales@jigoshop.com">sales@jigoshop.com</a></span></p>
					<p><?php _e('Join our growing developer community today, contribute to the jigoshop project via GitHub.', 'jigoshop') ?>: <a href="https://github.com/jigoshop/jigoshop">Fork</a>
					</p>
					<h4><?php _e('Jigoshop Social', 'jigoshop'); ?></h4>
					<ul id="jigo-social">
						<li><a href="https://www.facebook.com/Jigoshop?fref=ts" target="_blank" id="fb-icon">Facebook</a></li>
						<li><a href="https://twitter.com/Jigoshop" target="_blank" id="tw-icon">Twitter</a></li>
						<li><a href="https://plus.google.com/111168393814065931328/posts" target="_blank" id="gp-icon">G-plus</a></li>
					</ul>
				</div>
			</div>
			<div class="links jigo-links">
				<ul>
					<li><a href="https://www.jigoshop.com/"><?php _e('Learn more about the Jigoshop plugin', 'jigoshop'); ?></a></li>
					<li><a href="https://www.jigoshop.com/documentation/"><?php _e('Stuck? Read the plugin\'s documentation.', 'jigoshop'); ?></a></li>
					<li><a href="https://www.jigoshop.com/support/"><?php _e('Support', 'jigoshop'); ?></a></li>
				</ul>
			</div>
		</div>
	<?php
	}

	/**
	 * Monthly Report
	 */
	function jigoshop_dash_monthly_report(){
		global $current_month_offset;

		$current_month_offset = (int)date('m');

		if(isset($_GET['month'])){
			$current_month_offset = (int)$_GET['month'];
		} ?>
		<div class="stats" id="jigoshop-stats">
			<p>
				<?php if($current_month_offset != date('m')) : ?>
					<a href="admin.php?page=jigoshop&amp;month=<?php echo $current_month_offset + 1; ?>" class="next"><?php _e('Next Month &rarr;', 'jigoshop'); ?></a>
				<?php endif; ?>
				<a href="admin.php?page=jigoshop&amp;month=<?php echo $current_month_offset - 1; ?>" class="previous"><?php _e('&larr; Previous Month', 'jigoshop'); ?></a>
			</p>

			<div class="inside">
				<div id="placeholder" style="width:100%; height:300px; position:relative;"></div>
				<script type="text/javascript">
					/* <![CDATA[ */
					jQuery(function(){
						function weekendAreas(axes){
							var markings = [];
							var d = new Date(axes.xaxis.min);
							// go to the first Saturday
							d.setUTCDate(d.getUTCDate() - ((d.getUTCDay() + 1) % 7))
							d.setUTCSeconds(0);
							d.setUTCMinutes(0);
							d.setUTCHours(0);
							var i = d.getTime();
							do {
								// when we don't set yaxis, the rectangle automatically
								// extends to infinity upwards and downwards
								markings.push({ xaxis: { from: i, to: i + 2 * 24 * 60 * 60 * 1000 } });
								i += 7 * 24 * 60 * 60 * 1000;
							} while(i < axes.xaxis.max);
							return markings;
						}

						<?php
							function orders_this_month( $where = '' ) {
								global $current_month_offset;

								$month = $current_month_offset;
								$year = (int) date('Y');

								$first_day = strtotime("{$year}-{$month}-01");
								$last_day = strtotime('-1 second', strtotime('+1 month', $first_day));

								$after = date('Y-m-d H:i:s', $first_day);
								$before = date('Y-m-d H:i:s', $last_day);

								$where .= " AND post_date >= '$after'";
								$where .= " AND post_date <= '$before'";

								return $where;
							}
							add_filter( 'posts_where', 'orders_this_month' );

							$args = array(
								'numberposts' => -1,
								'orderby' => 'post_date',
								'order' => 'DESC',
								'post_type' => 'shop_order',
								'post_status' => 'publish',
								'suppress_filters'=> false,
							);
							$orders = get_posts( $args );

							$order_counts = array();
							$order_amounts = array();

							// Blank date ranges to begin
							$month = $current_month_offset;
							$year = (int) date('Y');

							$first_day = strtotime("{$year}-{$month}-01");
							$last_day = strtotime('-1 second', strtotime('+1 month', $first_day));

							if ((date('m') - $current_month_offset)==0) :
								$up_to = date('d', strtotime('NOW'));
							else :
								$up_to = date('d', $last_day);
							endif;
							$count = 0;

							while ($count < $up_to){

								$time = strtotime(date('Ymd', strtotime('+ '.$count.' DAY', $first_day))).'000';

								$order_counts[$time] = 0;
								$order_amounts[$time] = 0;

								$count++;
							}

							if ($orders){
								foreach ($orders as $order){

									$order_data = new jigoshop_order($order->ID);

									if ($order_data->status=='cancelled' || $order_data->status=='refunded') {continue;
}

									$time = strtotime(date('Ymd', strtotime($order->post_date))) . '000';

									if (isset($order_counts[$time])){
										$order_counts[$time]++;
									} else {
										$order_counts[$time] = 1;
									}

									if (isset($order_amounts[$time])){
										$order_amounts[$time] = $order_amounts[$time] + $order_data->order_total;
									} else {
										$order_amounts[$time] = (float) $order_data->order_total;
									}

								}
							}

							remove_filter( 'posts_where', 'orders_this_month' );
						?>
						var d = [
							<?php
								$values = array();
								foreach ($order_counts as $key => $value){ $values[] = "[$key, $value]"; }
								echo implode(',', $values);
							?>
						];
						for(var i = 0; i < d.length; ++i) d[i][0] += 60 * 60 * 1000;
						var d2 = [
							<?php
								$values = array();
								foreach ($order_amounts as $key => $value) { $values[] = "[$key, $value]"; }
								echo implode(',', $values);
							?>
						];
						for(var i = 0; i < d2.length; ++i) d2[i][0] += 60 * 60 * 1000;
						var plot = jQuery.plot(jQuery("#placeholder"), [
							{ label: "<?php __('Number of sales','jigoshop'); ?>", data: d },
							{ label: "<?php __('Sales amount','jigoshop'); ?>", data: d2, yaxis: 2 }
						], {
							series: {
								lines: { show: true },
								points: { show: true }
							},
							grid: {
								show: true,
								aboveData: false,
								color: '#ccc',
								backgroundColor: '#fff',
								borderWidth: 2,
								borderColor: '#ccc',
								clickable: false,
								hoverable: true,
								markings: weekendAreas
							},
							xaxis: {
								mode: "time",
								timeformat: "%d %b",
								tickLength: 1,
								minTickSize: [1, "day"]
							},
							yaxes: [
								{ min: 0, tickDecimals: 0 },
								{ position: "right", min: 0, tickDecimals: 2 }
							],
							colors: ["#21759B", "#ed8432"]
						});

						function showTooltip(x, y, contents){
							jQuery('<div id="tooltip">' + contents + '</div>').css({
								position: 'absolute',
								display: 'none',
								top: y + 5,
								left: x + 5,
								border: '1px solid #fdd',
								padding: '2px',
								'background-color': '#fee',
								opacity: 0.80
							}).appendTo("body").fadeIn(200);
						}

						var previousPoint = null;
						jQuery("#placeholder").bind("plothover", function(event, pos, item){
							if(item){
								if(previousPoint != item.dataIndex){
									previousPoint = item.dataIndex;
									jQuery("#tooltip").remove();
									if(item.series.label == "<?php __('Number of sales','jigoshop'); ?>"){
										var y = item.datapoint[1];
										showTooltip(item.pageX, item.pageY, item.series.label + " - " + y);
									} else {
										var y = item.datapoint[1].toFixed(2);
										showTooltip(item.pageX, item.pageY, item.series.label + " - <?php echo get_jigoshop_currency_symbol(); ?>" + y);
									}
								}
							}
							else {
								jQuery("#tooltip").remove();
								previousPoint = null;
							}
						});
					});
					/* ]]> */
				</script>
			</div>
		</div>
	<?php
	}
}
