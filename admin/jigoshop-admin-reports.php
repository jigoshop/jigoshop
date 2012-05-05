<?php
/**
 * Functions used for displaying the jigoshop dashboard
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

/**
 * Function for showing the dashboard
 *
 * The dashboard shows widget for things such as:
 *		- Products
 *		- Sales
 *		- Recent reviews
 *
 * @since 		1.0
 * @usedby 		jigoshop_admin_menu()
 */

if (!function_exists ('add_action')) {
		header('Status: 403 Forbidden');
		header('HTTP/1.1 403 Forbidden');
		exit();
}

class Jigoshop_reports {

	function __construct() {

		add_filter( 'posts_where', array(&$this, 'orders_within_range') );
		$this->orders = $this->jigoshop_get_orders();
		remove_filter( 'posts_where', array(&$this, 'orders_within_range') );

		$this->on_show_page();

	}

	/**
	 * Orders for range filter function
	 */
	function orders_within_range( $where = '' ) {
		global $start_date, $end_date;

		$after  = date('Y-m-d', $start_date);
		$before = date('Y-m-d', strtotime('+1 day', $end_date));

		$where .= " AND post_date > '$after'";
		$where .= " AND post_date < '$before'";

		return $where;
	}

	function jigoshop_get_orders() {
		global $start_date, $end_date;

		$start_date = !empty($_POST['start_date'])
					  ? strtotime($_POST['start_date'])
					  : strtotime(date('Ymd', strtotime( date('Ym', current_time('timestamp')).'01' )));

		$end_date	= !empty($_POST['end_date'])
					  ? strtotime($_POST['end_date'])
					  : strtotime(date('Ymd', current_time('timestamp')));

		$args = array(
			'numberposts'     => -1,
			'orderby'         => 'post_date',
			'order'           => 'ASC',
			'post_type'       => 'shop_order',
			'post_status'     => 'publish' ,
			'suppress_filters'=> 0,
			'tax_query' => array(
				array(
					'taxonomy'=> 'shop_order_status',
					'terms'   => array('completed', 'processing', 'on-hold'),
					'field'   => 'slug',
					'operator'=> 'IN'
				)
			)
		);

		return get_posts( $args );

	}

	function on_show_page() {

		global $start_date, $end_date;

		?>
		<div id="jigoshop-metaboxes-main" class="wrap">
			<div class="icon32 jigoshop_icon"><br/></div>
			<h2><?php _e('Jigoshop Reports','jigoshop'); ?></h2>

			<form method="post" action="admin.php?page=jigoshop_reports">
				<p><label for="from"><?php _e('From:', 'jigoshop'); ?></label> <input class="date-pick" type="date" name="start_date" id="from" readonly="readonly" value="<?php echo esc_attr( date('Y-m-d', $start_date) ); ?>" /> <label for="to"><?php _e('To:', 'jigoshop'); ?></label> <input type="date" class="date-pick" name="end_date" id="to" readonly="readonly" value="<?php echo esc_attr( date('Y-m-d', $end_date) ); ?>" /> <input type="submit" class="button" value="<?php _e('Show', 'jigoshop'); ?>" /></p>
			</form>

			<style>
[class*="span"]{float:left;margin-left:20px;}
.span3{width:220px;}
.span2{width:140px;}
.span1{width:60px;}
table{max-width:100%;background-color:transparent;border-collapse:collapse;border-spacing:0;}
.table{width:100%;margin-bottom:18px;}.table th,.table td{padding:8px;line-height:18px;text-align:left;vertical-align:top;border-top:1px solid #dddddd;}
.table th{font-weight:bold;}
.table thead th{vertical-align:bottom;}
.table caption+thead tr:first-child th,.table caption+thead tr:first-child td,.table colgroup+thead tr:first-child th,.table colgroup+thead tr:first-child td,.table thead:first-child tr:first-child th,.table thead:first-child tr:first-child td{border-top:0;}
.table tbody+tbody{border-top:2px solid #dddddd;}
.table-condensed th,.table-condensed td{padding:4px 5px;}
.table tbody tr:hover td,.table tbody tr:hover th{background-color:#f5f5f5;}
h1,h2,h3,h4,h5,h6{margin:0;font-family:inherit;font-weight:bold;color:inherit;text-rendering:optimizelegibility;}h1 small,h2 small,h3 small,h4 small,h5 small,h6 small{font-weight:normal;color:#999999;}
h1{font-size:30px;line-height:36px;}h1 small{font-size:18px;}
h2{font-size:24px;line-height:36px;}h2 small{font-size:18px;}
h3{font-size:18px;line-height:27px;}h3 small{font-size:14px;}
h4,h5,h6{line-height:18px;}
h4{font-size:14px;}h4 small{font-size:12px;}
h5{font-size:12px;}
h6{font-size:11px;color:#999999;text-transform:uppercase;}
.thumbnail h4, .thumbnail h3, .thumbnail h2, .thumbnail h1 {text-align:center;}
.thumbnail{display: block;padding: 4px;line-height: 1;border: 1px solid #DDD;-webkit-border-radius: 4px;-moz-border-radius: 4px;border-radius: 4px;-webkit-box-shadow: 0 1px 1px  rgba(0, 0, 0, 0.075);-moz-box-shadow: 0 1px 1px rgba(0, 0, 0, 0.075);box-shadow: 0 1px 1px  rgba(0, 0, 0, 0.075);}
			</style>

			<div id="report-widgets" class="metabox-holder">

				<div class='thumbnail mainGraph' style=''>
					<h1>Sales</h1>
					<?php $this->jigoshop_dash_monthly_report(); ?>
				</div>

				<br class="clear"/>

				<div class="span3 thumbnail">
					<h2>Top Earners</h2>
					<div id="top_earners_pie" style="height:300px"></div>
					<?php $this->jigoshop_top_earners(); ?>
					<?php echo $this->jigoshop_pie_charts('top_earners_pie'); ?>
					<div id="plothover"></div>
				</div>

				<div class="span3 thumbnail">
					<h2>Most Sold</h2>
					<div id="most_sold_pie" style="height:300px"></div>
					<?php $this->jigoshop_most_sold(); ?>
					<?php echo $this->jigoshop_pie_charts('most_sold_pie'); ?>
				</div>

				<div class="span3 thumbnail">
					<h1><?php echo $this->jigoshop_total_customers(); ?></h1>
					<h3>Total Customers</h3>
				</div>

				<div class="span3 thumbnail">
					<h1><?php echo $this->jigoshop_total_orders(); ?></h1>
					<h3>Total Orders</h3>
				</div>

				<div class="span3 thumbnail">
					<h1><?php echo $this->jigoshop_total_sales(); ?></h1>
					<h3>Total Sales</h3>
				</div>

			</div>
		</div>

<?php }

function jigoshop_pie_charts($id = '') {

	if (empty($id)) return false;

//	$count = count($this->pie_products);
	$total = array_sum($this->pie_products);

	$values = array();
	foreach ($this->pie_products as $name => $sales) $values[] = '{ label: "'.$name.'", data: '. (round($sales/$total, 3)*100).'}';

?>
<script type="text/javascript">
/* <![CDATA[ */
jQuery(function(){

	function pieHover(event, pos, obj) {

		if (!obj) return;
		percent = parseFloat(obj.series.percent).toFixed(2);
		jQuery("#plothover").html('<span style="font-weight: bold; color: '+obj.series.color+'">'+obj.series.label+' ('+percent+'%)</span>');
	}

	var data = [

		<?php echo implode(',', $values); ?>

	];
	jQuery.plot(jQuery("#<?php echo $id; ?>"), data, {
		series: {
			pie: {
				show: true,
				combine: {
					color: '#999',
					threshold: 0.08
				},
				radius: 1,
				label: {
					show: true,
					radius: 2/3,
					formatter: function(label, series){
						return '<div style="font-size:8pt;text-align:center;padding:2px;color:white;">'+label+'<br/>'+Math.round(series.percent)+'%</div>';
					},
				}
			}
		},
		legend: {
		show: false
		}
	});
	//jQuery("#top_earners_pie").bind("plothover", pieHover);
	//jQuery("#top_earners_pie").bind("plotclick", pieClick);
});
/* ]]> */
</script>
<?php

}

	function jigoshop_total_orders() {
		global $wpdb, $start_date, $end_date;

		$total_orders = $wpdb->get_row("
			SELECT COUNT(posts.ID) AS total_orders FROM {$wpdb->posts} AS posts

			LEFT JOIN {$wpdb->postmeta} AS meta ON posts.ID = meta.post_id
			LEFT JOIN {$wpdb->term_relationships} AS rel ON posts.ID=rel.object_ID
			LEFT JOIN {$wpdb->term_taxonomy} AS tax USING( term_taxonomy_id )
			LEFT JOIN {$wpdb->terms} AS term USING( term_id )

			WHERE 	meta.meta_key 		= 'order_data'
			{$this->orders_within_range()}
			AND 	posts.post_type 	= 'shop_order'
			AND 	posts.post_status 	= 'publish'
			AND 	tax.taxonomy		= 'shop_order_status'
			AND		term.slug			IN ('completed', 'processing', 'on-hold')
		");

		return $total_orders->total_orders;

	}

	function jigoshop_most_sold() {

		global $start_date, $end_date;

		$found_products = array();

		if ($this->orders) :
			foreach ($this->orders as $order) :
				$order_items = (array) get_post_meta( $order->ID, 'order_items', true );
				foreach ($order_items as $item) :
					$row_cost = $item['qty'];
					$found_products[$item['id']] = isset($found_products[$item['id']]) ? $found_products[$item['id']] + $row_cost : $row_cost;
				endforeach;
			endforeach;
		endif;

		asort($found_products);
		$found_products = array_reverse($found_products, true);
		$found_products = array_slice($found_products, 0, 25, true);
		reset($found_products);

		?>

		<table class="table table-condensed">
			<thead>
				<tr>
					<th><?php _e('Product', 'jigoshop'); ?></th>
					<th><?php _e('Quantity', 'jigoshop'); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php
					foreach ($found_products as $product_id => $qty) :
						$product = get_post($product_id);
						$this->pie_products[$product->post_title] = $qty;
						$product_name = !empty($product) ? '<a href="'.get_permalink($product->ID).'">'.$product->post_title.'</a>' : __('Product no longer exists', 'jigoshop'); ?>
						<tr>
							<td><?php echo $product_name; ?></td>
							<td><?php echo $qty; ?></td>
						</tr>
					<?php endforeach; ?>

			</tbody>
		</table>
		<script type="text/javascript">
			jQuery(function(){
				jQuery('.date-pick').datepicker( {dateFormat: 'yy-mm-dd', gotoCurrent: true} );
			});
		</script>
	<?php

	}

	function jigoshop_total_sales() {
		global $wpdb;

		$row_cost = array();

		if ($this->orders) :
			foreach ($this->orders as $order) :
				$order_items = (array) get_post_meta( $order->ID, 'order_items', true );
				foreach ($order_items as $item) :
					$row_cost[] = $item['cost'] * $item['qty'];
				endforeach;
			endforeach;
		endif;

		return jigoshop_price(array_sum($row_cost));

	}

	function jigoshop_total_customers() {
		global $wpdb;

		$users_query = new WP_User_Query( array(
			'fields' => array('user_registered'),
			'role'   => 'customer',
		) );

		$customers = $users_query->get_results();
		return (int) sizeof($customers);

	}


	function jigoshop_top_earners() {

		global $start_date, $end_date;

		$found_products = array();

		if ($this->orders) :
			foreach ($this->orders as $order) :
				$order_items = (array) get_post_meta( $order->ID, 'order_items', true );
				foreach ($order_items as $item) :
					$row_cost = $item['cost'] * $item['qty'];
					$found_products[$item['id']] = isset($found_products[$item['id']]) ? $found_products[$item['id']] + $row_cost : $row_cost;
				endforeach;
			endforeach;
		endif;

		asort($found_products);
		$found_products = array_reverse($found_products, true);
		$found_products = array_slice($found_products, 0, 25, true);
		reset($found_products);

		$this->pie_products = array();

		?>

		<table class="table table-condensed">
			<thead>
				<tr>
					<th><?php _e('Product', 'jigoshop'); ?></th>
					<th><?php _e('Sales', 'jigoshop'); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($found_products as $product_id => $sales) :
						$product = get_post($product_id);
						$this->pie_products[$product->post_title] = $sales;
						$product_name = !empty($product) ? '<a href="'.get_permalink($product->ID).'">'.$product->post_title.'</a>' : __('Product no longer exists', 'jigoshop');
				?>
						<tr>
							<td><?php echo $product_name; ?></td>
							<td><?php echo jigoshop_price($sales); ?></td>
						</tr>
					<?php endforeach; ?>

			</tbody>
		</table>
		<script type="text/javascript">
			jQuery(function(){
				jQuery('.date-pick').datepicker( {dateFormat: 'yy-mm-dd', gotoCurrent: true} );
			});
		</script>
	<?php
	}



	/**
	*
	*	Right Now
	*
	*/

	function jigoshop_dash_right_now() { ?>

	<div id="jigoshop_right_now" class="jigoshop_right_now">
		<div class="table table_content">
			<p class="sub"><?php _e('Shop Content', 'jigoshop'); ?></p>
			<table>
				<tbody>
					<tr class="first">
						<td class="first b"><a href="edit.php?post_type=product"><?php
							$num_posts = wp_count_posts( 'product' );
							$num = number_format_i18n( $num_posts->publish );
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
							echo count( jigoshop_product::getAttributeTaxonomies());
						?></a></td>
						<td class="t"><a href="admin.php?page=jigoshop_attributes"><?php _e('Attribute taxonomies', 'jigoshop'); ?></a></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div class="table table_discussion">
			<p class="sub"><?php _e('Orders', 'jigoshop'); ?></p>
			<table>
				<tbody>
					<?php $jigoshop_orders = new jigoshop_orders(); ?>
					<tr class="first">
						<td class="b"><a href="edit.php?post_type=shop_order&shop_order_status=pending"><span class="total-count"><?php echo $jigoshop_orders->pending_count; ?></span></a></td>
						<td class="last t"><a class="pending" href="edit.php?post_type=shop_order&shop_order_status=pending"><?php _e('Pending', 'jigoshop'); ?></a></td>
					</tr>
					<tr>
						<td class="b"><a href="edit.php?post_type=shop_order&shop_order_status=on-hold"><span class="total-count"><?php echo $jigoshop_orders->on_hold_count; ?></span></a></td>
						<td class="last t"><a class="onhold" href="edit.php?post_type=shop_order&shop_order_status=on-hold"><?php _e('On-Hold', 'jigoshop'); ?></a></td>
					</tr>
					<tr>
						<td class="b"><a href="edit.php?post_type=shop_order&shop_order_status=processing"><span class="total-count"><?php echo $jigoshop_orders->processing_count; ?></span></a></td>
						<td class="last t"><a class="processing" href="edit.php?post_type=shop_order&shop_order_status=processing"><?php _e('Processing', 'jigoshop'); ?></a></td>
					</tr>
					<tr>
						<td class="b"><a href="edit.php?post_type=shop_order&shop_order_status=completed"><span class="total-count"><?php echo $jigoshop_orders->completed_count; ?></span></a></td>
						<td class="last t"><a class="complete" href="edit.php?post_type=shop_order&shop_order_status=completed"><?php _e('Completed', 'jigoshop'); ?></a></td>
					</tr>
				</tbody>
			</table>
		</div>
		<br class="clear"/>
		<div class="versions">
			<p id="wp-version-message"><?php _e('You are using', 'jigoshop'); ?>
				<strong>JigoShop <?php echo jigoshop_get_plugin_data(); ?></strong>
			</p>
		</div>
	</div>
		<?php
	}

	/**
	*
	*	Recent Orders
	*
	*/

	function jigoshop_dash_recent_orders() {
		$args = array(
			'numberposts'	=> 10,
			'orderby'		=> 'post_date',
			'order'			=> 'DESC',
			'post_type'		=> 'shop_order',
			'post_status'	=> 'publish'
		);
		$orders = get_posts( $args );
		if ($orders) :
			echo '<ul class="recent-orders">';
			foreach ($orders as $order) :

				$this_order = new jigoshop_order( $order->ID );

				echo '
				<li>
					<span class="order-status '.sanitize_title($this_order->status).'">'.ucwords(__($this_order->status, 'jigoshop')).'</span> <a href="'.admin_url('post.php?post='.$order->ID).'&action=edit">'.get_the_time(__('l jS \of F Y h:i:s A', 'jigoshop'), $order->ID).'</a><br />
					<small>'.sizeof($this_order->items).' '._n('item', 'items', sizeof($this_order->items), 'jigoshop').' <span class="order-cost">'.__('Total: ', 'jigoshop').jigoshop_price($this_order->order_total).'</span></small>
				</li>';

			endforeach;
			echo '</ul>';
		endif;
	}

	/**
	*
	*	Stock Reports
	*
	*/

	function jigoshop_dash_stock_report() {
		if (get_option('jigoshop_manage_stock')=='yes') :

			$lowstockamount = get_option('jigoshop_notify_low_stock_amount');
			if (!is_numeric($lowstockamount)) $lowstockamount = 1;

			$nostockamount = get_option('jigoshop_notify_no_stock_amount');
			if (!is_numeric($nostockamount)) $nostockamount = 1;

			$outofstock = array();
			$lowinstock = array();
			$args = array(
				'post_type'	=> 'product',
				'post_status' => 'publish',
				'ignore_sticky_posts'	=> 1,
				'posts_per_page' => -1
			);
			$my_query = new WP_Query($args);
			if ($my_query->have_posts()) : while ($my_query->have_posts()) : $my_query->the_post();

				$_product = new jigoshop_product( $my_query->post->ID );
				if (!$_product->managing_stock()) continue;

				$thisitem = '<tr class="first">
					<td class="first b"><a href="post.php?post='.$my_query->post->ID.'&action=edit">'.$_product->stock.'</a></td>
					<td class="t"><a href="post.php?post='.$my_query->post->ID.'&action=edit">'.$my_query->post->post_title.'</a></td>
				</tr>';

				if ($_product->stock<=$nostockamount) :
					$outofstock[] = $thisitem;
					continue;
				endif;

				if ($_product->stock<=$lowstockamount) $lowinstock[] = $thisitem;

			endwhile; endif;
			wp_reset_query();

			if (sizeof($lowinstock)==0) :
				$lowinstock[] = '<tr><td colspan="2">'.__('No products are low in stock.', 'jigoshop').'</td></tr>';
			endif;
			if (sizeof($outofstock)==0) :
				$outofstock[] = '<tr><td colspan="2">'.__('No products are out of stock.', 'jigoshop').'</td></tr>';
			endif;
			?>
			<div id="jigoshop_right_now" class="jigoshop_right_now">
				<div class="table table_content">
					<p class="sub"><?php _e('Low Stock', 'jigoshop'); ?></p>
					<table>
						<tbody>
							<?php echo implode('', $lowinstock); ?>
						</tbody>
					</table>
				</div>
				<div class="table table_discussion">
					<p class="sub"><?php _e('Out of Stock/Backorders', 'jigoshop'); ?></p>
					<table>
						<tbody>
							<?php echo implode('', $outofstock); ?>
						</tbody>
					</table>
				</div>
				<br class="clear"/>
			</div>
	<?php endif;
	}

	/**
	*
	*	Recent Reviews
	*
	*/

	function jigoshop_dash_recent_reviews() {
		global $wpdb;
		$comments = $wpdb->get_results("SELECT *, SUBSTRING(comment_content,1,100) AS comment_excerpt
		FROM $wpdb->comments
		LEFT JOIN $wpdb->posts ON ($wpdb->comments.comment_post_ID = $wpdb->posts.ID)
		WHERE comment_approved = '1'
		AND comment_type = ''
		AND post_password = ''
		AND post_type = 'product'
		ORDER BY comment_date_gmt DESC
		LIMIT 5" );
		?><div class="inside jigoshop-reviews-widget"><?php
		if ($comments) :
			echo '<ul>';
			foreach ($comments as $comment) :

				echo '<li>';

				echo get_avatar($comment->comment_author, '32');

				$rating = get_comment_meta( $comment->comment_ID, 'rating', true );

				echo '<div class="star-rating" title="'.esc_attr($rating).'">
					<span style="width:'.($rating*16).'px">'.$rating.' '.__('out of 5', 'jigoshop').'</span></div>';

				echo '<h4 class="meta"><a href="'.get_permalink($comment->ID).'#comment-'.$comment->comment_ID .'">'.$comment->post_title.'</a> reviewed by ' .strip_tags($comment->comment_author) .'</h4>';
				echo '<blockquote>'.strip_tags($comment->comment_excerpt).' [...]</blockquote></li>';

			endforeach;
			echo '</ul>';
		else :
			echo '<p>'.__('There are no product reviews yet.', 'jigoshop').'</p>';
		endif;
		?></div><?php
	}

	/**
	*
	*	Monthly Report
	*
	*/

	function jigoshop_dash_monthly_report() {

		global $start_date, $end_date;

		$current_month_offset = (int) date('m'); ?>
		<div class="stats" id="jigoshop-stats">

	<div class="inside">
		<div id="placeholder" style="width:100%; height:300px; position:relative;"></div>
		<script type="text/javascript">
			/* <![CDATA[ */

			jQuery(function(){

				function weekendAreas(axes) {
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
					} while (i < axes.xaxis.max);

					return markings;
				}

				<?php

					add_filter( 'posts_where', array(&$this, 'orders_within_range') );

					$args = array(
						'numberposts'     => -1,
						'orderby'         => 'post_date',
						'order'           => 'DESC',
						'post_type'       => 'shop_order',
						'post_status'     => 'publish' ,
						'suppress_filters'=> false,
						'tax_query' => array(
							array(
								'taxonomy' => 'shop_order_status',
								'terms' => array('completed', 'processing', 'on-hold', 'pending'),
								'field' => 'slug',
								'operator' => 'IN'
							)
						)
					);
					$orders = get_posts( $args );

					$order_counts = array();
					$order_amounts = array();

					$count = 0;
					$days = ($end_date - $start_date) / (60 * 60 * 24);

					if ($days==0) $days = 1;

					while ($count < $days) :

						$time = strtotime(date('Ymd', strtotime('+ '.$count.' DAY', $start_date))).'000';
						$order_counts[$time] = 0;
						$order_amounts[$time] = 0;
						$count++;

					endwhile;

					if ($orders) :
						foreach ($orders as $order) :

							$order_data = new jigoshop_order($order->ID);
							$time = strtotime(date('Ymd', strtotime($order->post_date))).'000';

							if (isset($order_counts[$time])) :
								$order_counts[$time]++;
							else :
								$order_counts[$time] = 1;
							endif;

							if (isset($order_amounts[$time])) :
								$order_amounts[$time] = $order_amounts[$time] + $order_data->order_total;
							else :
								$order_amounts[$time] = (float) $order_data->order_total;
							endif;

						endforeach;
					endif;

					remove_filter( 'posts_where', array(&$this, 'orders_within_range') );
				?>

				var d = [
					<?php
						$values = array();
						foreach ($order_counts as $key => $value) $values[] = "[$key, $value]";
						echo implode(',', $values);
					?>
				];

				for (var i = 0; i < d.length; ++i) d[i][0] += 60 * 60 * 1000;

				var d2 = [
					<?php
						$values = array();
						foreach ($order_amounts as $key => $value) $values[] = "[$key, $value]";
						echo implode(',', $values);
					?>
				];

				for (var i = 0; i < d2.length; ++i) d2[i][0] += 60 * 60 * 1000;

				var plot = jQuery.plot(jQuery("#placeholder"), [ { label: "Number of sales", data: d }, { label: "Sales amount", data: d2, yaxis: 2 } ], {
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
					yaxes: [ { min: 0, tickSize: 1, tickDecimals: 0 }, { position: "right", min: 0, tickDecimals: 2 } ],
					colors: ["#21759B", "#ed8432"]
				});

				function showTooltip(x, y, contents) {
					jQuery('<div id="tooltip">' + contents + '</div>').css( {
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
				jQuery("#placeholder").bind("plothover", function (event, pos, item) {
					if (item) {
						if (previousPoint != item.dataIndex) {
							previousPoint = item.dataIndex;

							jQuery("#tooltip").remove();

							if (item.series.label=="Number of sales") {

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

?>
