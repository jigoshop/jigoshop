<?php

if (!defined('ABSPATH')) {
	exit;
}

abstract class Jigoshop_Admin_Report
{
	public $chart_interval;
	public $group_by_query;
	public $barwidth;
	public $chart_groupby;
	public $start_date;
	public $end_date;

	/**
	 * Prepares a sparkline to show sales in the last X days
	 *
	 * @param  int|string $id ID of the product to show. Blank to get all orders.
	 * @param  int $days Days of stats to get.
	 * @param  string $type Type of sparkline to get. Ignored if ID is not set.
	 * @return string
	 */
	public function sales_sparkline($id = '', $days = 7, $type = 'sales')
	{
		if ($id) {
			$meta_key = $type == 'sales' ? '_line_total' : '_qty';
			$data = $this->get_order_report_data(array(
				'data' => array(
					'_product_id' => array(
						'type' => 'order_item_meta',
						'order_item_type' => 'line_item',
						'function' => '',
						'name' => 'product_id'
					),
					$meta_key => array(
						'type' => 'order_item_meta',
						'order_item_type' => 'line_item',
						'function' => 'SUM',
						'name' => 'sparkline_value'
					),
					'post_date' => array(
						'type' => 'post_data',
						'function' => '',
						'name' => 'post_date'
					),
				),
				'where' => array(
					array(
						'key' => 'post_date',
						'value' => date('Y-m-d', strtotime('midnight -'.($days - 1).' days', current_time('timestamp'))),
						'operator' => '>'
					),
					array(
						'key' => 'order_item_meta__product_id.meta_value',
						'value' => $id,
						'operator' => '='
					)
				),
				'group_by' => 'YEAR(posts.post_date), MONTH(posts.post_date), DAY(posts.post_date)',
				'query_type' => 'get_results',
				'filter_range' => false
			));
		} else {
			$data = $this->get_order_report_data(array(
				'data' => array(
					'_order_total' => array(
						'type' => 'meta',
						'function' => 'SUM',
						'name' => 'sparkline_value'
					),
					'post_date' => array(
						'type' => 'post_data',
						'function' => '',
						'name' => 'post_date'
					),
				),
				'where' => array(
					array(
						'key' => 'post_date',
						'value' => date('Y-m-d', strtotime('midnight -'.($days - 1).' days', current_time('timestamp'))),
						'operator' => '>'
					)
				),
				'group_by' => 'YEAR(posts.post_date), MONTH(posts.post_date), DAY(posts.post_date)',
				'query_type' => 'get_results',
				'filter_range' => false
			));
		}

		$total = 0;
		foreach ($data as $d) {
			$total += $d->sparkline_value;
		}

		if ($type == 'sales') {
			$tooltip = sprintf(__('Sold %s worth in the last %d days', 'jigoshop'), strip_tags(jigoshop_price($total)), $days);
		} else {
			$tooltip = sprintf(_n('Sold 1 item in the last %d days', 'Sold %d items in the last %d days', $total, 'jigoshop'), $total, $days);
		}

		$sparkline_data = array_values($this->prepare_chart_data($data, 'post_date', 'sparkline_value', $days - 1, strtotime('midnight -'.($days - 1).' days', current_time('timestamp')), 'day'));

		return '<span class="jigoshop_sparkline '.($type == 'sales' ? 'lines' : 'bars').' tips" data-color="#777" data-tip="'.esc_attr($tooltip).'" data-barwidth="'. 60 * 60 * 16 * 1000 .'" data-sparkline="'.esc_attr(json_encode($sparkline_data)).'"></span>';
	}

	/**
	 * Get report totals such as order totals and discount amounts.
	 * Data example:
	 * '_order_total' => array(
	 *     'type'     => 'meta',
	 *     'function' => 'SUM',
	 *     'name'     => 'total_sales'
	 * )
	 *
	 * @param  array $args
	 * @return array|string depending on query_type
	 */
	public function get_order_report_data($args = array())
	{
		global $wpdb;

		$default_args = array(
			'data' => array(),
			'where' => array(),
			'where_meta' => array(),
			'query_type' => 'get_row',
			'group_by' => '',
			'order_by' => '',
			'limit' => '',
			'filter_range' => false,
			'nocache' => false,
			'debug' => false,
			'order_types' => 'shop_order',
			'order_status' => array('completed', 'processing', 'on-hold'),
			'parent_order_status' => false,
		);
		$args = apply_filters('jigoshop_reports_get_order_report_data_args', $args);
		$args = wp_parse_args($args, $default_args);

		if (empty($args['data'])) {
			return '';
		}

		$order_status = apply_filters('jigoshop_reports_order_statuses', $args['order_status']);

		$query = array();
		$select = array();

		foreach ($args['data'] as $key => $value) {
			$distinct = '';

			if (isset($value['distinct'])) {
				$distinct = 'DISTINCT';
			}

			if ($value['type'] == 'meta') {
				$get_key = "meta_{$key}.meta_value";
			} elseif ($value['type'] == 'post_data') {
				$get_key = "posts.{$key}";
			} else {
				continue;
			}

			if (isset($value['function'])) {
				$get = "{$value['function']}({$distinct} {$get_key})";
			} else {
				$get = "{$distinct} {$get_key}";
			}

			$select[] = "{$get} as {$value['name']}";
		}

		$query['select'] = "SELECT ".implode(',', $select);
		$query['from'] = "FROM {$wpdb->posts} AS posts";

		// Joins
		$joins = array();

		foreach ($args['data'] as $key => $value) {
			if ($value['type'] == 'meta') {
				$joins["meta_{$key}"] = "LEFT JOIN {$wpdb->postmeta} AS meta_{$key} ON posts.ID = meta_{$key}.post_id";
			}
		}

		foreach ($args['where_meta'] as $value) {
			if (!is_array($value)) {
				continue;
			}

			$key = is_array($value['meta_key']) ? $value['meta_key'][0].'_array' : $value['meta_key'];
			$joins["meta_{$key}"] = "LEFT JOIN {$wpdb->postmeta} AS meta_{$key} ON posts.ID = meta_{$key}.post_id";
		}

		if (!empty($args['parent_order_status'])) {
			$joins["parent"] = "LEFT JOIN {$wpdb->posts} AS parent ON posts.post_parent = parent.ID";
		}

		$query['join'] = implode(' ', $joins);
		$query['where'] = "
			WHERE posts.post_type IN ('".implode("','", $args['order_types'])."')
			";

		if (!empty($order_status)) {
			$query['join'] .= " LEFT JOIN {$wpdb->term_relationships} ostr ON posts.ID = ostr.object_id";
			$query['join'] .= " LEFT JOIN {$wpdb->term_taxonomy} ostt ON ostr.term_taxonomy_id = ostt.term_taxonomy_id";
			$query['join'] .= " LEFT JOIN {$wpdb->terms} ost ON ostt.term_id = ost.term_id";
			$query['where'] .= "
				AND ost.name IN ( '".implode("','", $order_status)."')
			";
		}

		if (!empty($parent_order_status)) {
			$query['join'] .= " LEFT JOIN {$wpdb->term_relationships} postr ON parent.ID = postr.object_id";
			$query['join'] .= " LEFT JOIN {$wpdb->term_taxonomy} postt ON postr.term_taxonomy_id = postt.term_taxonomy_id";
			$query['join'] .= " LEFT JOIN {$wpdb->terms} post ON postt.term_id = post.term_id";
			$query['where'] .= "
				AND post.name IN ( '".implode("','", $parent_order_status)."')
			";
		}

		if ($args['filter_range']) {
			$query['where'] .= "
				AND posts.post_date >= '".date('Y-m-d', $this->start_date)."'
				AND posts.post_date < '".date('Y-m-d', strtotime('+1 DAY', $this->end_date))."'
			";
		}

		foreach ($args['data'] as $key => $value) {
			if ($value['type'] == 'meta') {
				$query['where'] .= " AND meta_{$key}.meta_key = '{$key}'";
			}
		}

		if (!empty($args['where_meta'])) {
			$relation = isset($where_meta['relation']) ? $where_meta['relation'] : 'AND';
			$query['where'] .= " AND (";

			foreach ($args['where_meta'] as $index => $value) {
				if (!is_array($value)) {
					continue;
				}

				$key = is_array($value['meta_key']) ? $value['meta_key'][0].'_array' : $value['meta_key'];
				if (strtolower($value['operator']) == 'in') {
					if (is_array($value['meta_value'])) {
						$value['meta_value'] = implode("','", $value['meta_value']);
					}

					if (!empty($value['meta_value'])) {
						$where_value = "IN ('{$value['meta_value']}')";
					}
				} else {
					$where_value = "{$value['operator']} '{$value['meta_value']}'";
				}

				if (!empty($where_value)) {
					if ($index > 0) {
						$query['where'] .= ' '.$relation;
					}

					if (is_array($value['meta_key'])) {
						$query['where'] .= " ( meta_{$key}.meta_key IN ('".implode("','", $value['meta_key'])."')";
					} else {
						$query['where'] .= " ( meta_{$key}.meta_key = '{$value['meta_key']}'";
					}

					$query['where'] .= " AND meta_{$key}.meta_value {$where_value} )";
				}
			}

			$query['where'] .= ")";
		}

		foreach ($args['where'] as $value) {
			if (strtolower($value['operator']) == 'in') {
				if (is_array($value['value'])) {
					$value['value'] = implode("','", $value['value']);
				}

				if (!empty($value['value'])) {
					$where_value = "IN ('{$value['value']}')";
				}
			} else {
				$where_value = "{$value['operator']} '{$value['value']}'";
			}

			if (!empty($where_value)) {
				$query['where'] .= " AND {$value['key']} {$where_value}";
			}
		}

		if ($args['order_by']) {
			$query['order_by'] = "ORDER BY {$args['order_by']}";
		}

		if ($args['limit']) {
			$query['limit'] = "LIMIT {$args['limit']}";
		}

		$query = apply_filters('jigoshop_reports_get_order_report_query', $query);
		$query = implode(' ', $query);
		$query_hash = md5($args['query_type'].$query);
		$cached_results = get_transient(strtolower(get_class($this)));

		if ($args['debug']) {
			echo '<pre>';
			print_r($query);
			echo '</pre>';
		}

		$args['debug'] = true;
		if ($args['debug'] || $args['nocache'] || false === $cached_results || !isset($cached_results[$query_hash])) {
			$cached_results[$query_hash] = apply_filters('jigoshop_reports_get_order_report_data', $wpdb->{$args['query_type']}($query), $args['data']);

			// Process results
			foreach ($args['data'] as $key => $value) {
				if (!isset($value['process']) || $value['process'] !== true) {
					continue;
				}

				switch ($value['name']) {
					case 'order_data':
						$results = array();
						foreach ($cached_results[$query_hash] as $item) {
							if (!isset($results[$item->post_date])) {
								$result = new stdClass;
								$result->post_date = $item->post_date;
								$result->total_sales = 0.0;
								$result->total_shipping = 0.0;
								$result->total_tax = 0.0;
								$result->total_shipping_tax = 0.0;
								$results[$item->post_date] = $result;
							}

							$data = maybe_unserialize($item->order_data);
							$results[$item->post_date]->total_sales += $data['order_total'];
							$results[$item->post_date]->total_shipping += $data['order_shipping'];
							$results[$item->post_date]->total_tax += $data['order_tax_no_shipping_tax'];
							$results[$item->post_date]->total_shipping_tax += $data['order_shipping_tax'];
						}

						$cached_results[$query_hash] = $results;
						break;
					case 'order_item_count':
						$results = array();
						foreach ($cached_results[$query_hash] as $item) {
							if (!isset($results[$item->post_date])) {
								$result = new stdClass;
								$result->post_date = $item->post_date;
								$result->order_item_count = 0;
								$results[$item->post_date] = $result;
							}

							$data = maybe_unserialize($item->order_item_count);
							$data = $this->filterItem($data, $value);
							$results[$item->post_date]->order_item_count += count($data);
						}

						$cached_results[$query_hash] = $results;
						break;
					case 'discount_amount':
						$results = array();
						foreach ($cached_results[$query_hash] as $item) {
							if (!isset($results[$item->post_date])) {
								$result = new stdClass;
								$result->post_date = $item->post_date;
								$result->discount_amount = 0.0;
								$result->coupons_used = 0;
								$results[$item->post_date] = $result;
							}

							$data = maybe_unserialize($item->discount_amount);
							$data = $this->filterItem($data, $value);

							if (!empty($data)) {
								$results[$item->post_date]->coupons_used += count($data['order_discount_coupons']);

								foreach ($data['order_discount_coupons'] as $coupon) {
									$results[$item->post_date]->discount_amount += $coupon['amount'];
								}
							}
						}

						$cached_results[$query_hash] = $results;
						break;
					case 'order_coupons':
						$coupons = array();
						$results = array();
						foreach ($cached_results[$query_hash] as $item) {
							if (!isset($results[$item->post_date])) {
								$coupons[$item->post_date] = array();
								$results[$item->post_date] = new stdClass();
								$results[$item->post_date]->post_date = $item->post_date;
								$results[$item->post_date]->coupons = array();
								$results[$item->post_date]->usage = array();
							}

							$data = maybe_unserialize($item->order_coupons);
							foreach ($data['order_discount_coupons'] as $coupon) {
								if (!in_array($coupon['code'], $coupons[$item->post_date])) {
									$results[$item->post_date]->coupons[] = JS_Coupons::get_coupon($coupon['code']);
									$results[$item->post_date]->usage[$coupon['code']] = 1;
									$coupons[$item->post_date][] = $coupon['code'];
								} else {
									$results[$item->post_date]->usage[$coupon['code']] += 1;
								}
							}
						}

						$cached_results[$query_hash] = $results;
						break;
					case 'order_item_amount':
						$results = array();
						foreach ($cached_results[$query_hash] as $item) {
							if (!isset($results[$item->post_date])) {
								$result = new stdClass;
								$result->post_date = $item->post_date;
								$result->order_item_amount = 0.0;
								$results[$item->post_date] = $result;
							}

							$data = maybe_unserialize($item->order_item_amount);
							$data = $this->filterItem($data, $value);
							$results[$item->post_date]->order_item_amount += array_sum(array_map(function($product){
								return $product['qty'] * $product['cost'];
							}, $data));
						}

						$cached_results[$query_hash] = $results;
						break;
					case 'top_products':
						$results = array();
						foreach ($cached_results[$query_hash] as $item) {
							$data = maybe_unserialize($item->top_products);
							$data = $this->filterItem($data, $value);
							foreach ($data as $product) {
								if (!isset($results[$product['id']])) {
									$result = new stdClass;
									$result->product_id = $product['id'];
									$result->order_item_qty = 0;
									$result->order_item_total = 0;

									if (isset($item->post_date)) {
										$result->post_date = $item->post_date;
									}

									$results[$product['id']] = $result;
								}

								$results[$product['id']]->order_item_qty += $product['qty'];
								$results[$product['id']]->order_item_total += $product['qty'] * $product['cost'];
							}
						}

						if (isset($value['order'])) {
							switch($value['order']) {
								case 'most_sold':
									usort($results, function($a, $b){
										return $b->order_item_qty - $a->order_item_qty;
									});
									break;
								case 'most_earned':
									usort($results, function($a, $b){
										return $b->order_item_total - $a->order_item_total;
									});
									break;
							}
						}

						if (isset($value['limit'])) {
							$results = array_slice($results, 0, $value['limit']);
						}

						$cached_results[$query_hash] = $results;
						break;
				}
			}

			set_transient(strtolower(get_class($this)), $cached_results, DAY_IN_SECONDS);
		}

		return $cached_results[$query_hash];
	}

	protected function filterItem($item, $value)
	{
		if (isset($value['where'])) {
			switch($value['where']['type']) {
				case 'item_id':
					$item = array_filter($item, function($product) use ($value){
						$result = false;
						foreach ($value['where']['keys'] as $key) {
							$result |= in_array($product[$key], $value['where']['value']);
						}

						return $result;
					});
					break;
				case 'comparison':
					$item = array_filter($item, function($product) use ($value){
						switch ($value['where']['operator']) {
							case '<>':
							case '!=':
								return $product[$value['where']['key']] != $value['where']['value'];
							case '=':
								return $product[$value['where']['key']] == $value['where']['value'];
							case '<':
								return $product[$value['where']['key']] < $value['where']['value'];
							case '>':
								return $product[$value['where']['key']] > $value['where']['value'];
							case '<=':
								return $product[$value['where']['key']] <= $value['where']['value'];
							case '>=':
								return $product[$value['where']['key']] >= $value['where']['value'];
							case 'in':
								return in_array($product[$value['where']['key']], $value['where']['value']);
							case 'intersection':
								$intersection = array_intersect($product[$value['where']['key']], $value['where']['value']);
								return !empty($intersection);
						}

						return false;
					});
					break;
				case 'object_comparison':
					switch ($value['where']['operator']) {
						case '<>':
						case '!=':
							if ($item[$value['where']['key']] != $value['where']['value']) {
								return $item;
							}
						case '=':
							if ($item[$value['where']['key']] == $value['where']['value']) {
								return $item;
							}
						case '<':
							if ($item[$value['where']['key']] < $value['where']['value']) {
								return $item;
							}
						case '>':
							if ($item[$value['where']['key']] > $value['where']['value']) {
								return $item;
							}
						case '<=':
							if ($item[$value['where']['key']] <= $value['where']['value']) {
								return $item;
							}
						case '>=':
							if ($item[$value['where']['key']] >= $value['where']['value']) {
								return $item;
							}
						case 'in':
							if (in_array($item[$value['where']['key']], $value['where']['value'])) {
								return $item;
							}
						case 'intersection':
							$source = $item[$value['where']['key']];
							if (isset($value['where']['map'])) {
								$source = array_map($value['where']['map'], $source);
							}

							$intersection = array_intersect($source, $value['where']['value']);
							if (!empty($intersection)) {
								return $item;
							};
					}

					return false;
			}
		}

		return $item;
	}

	/**
	 * Put data with post_date's into an array of times
	 *
	 * @param  array $data array of your data
	 * @param  string $date_key key for the 'date' field. e.g. 'post_date'
	 * @param  string $data_key key for the data you are charting
	 * @param  int $interval
	 * @param  string $start_date
	 * @param  string $group_by
	 * @return array
	 */
	public function prepare_chart_data($data, $date_key, $data_key, $interval, $start_date, $group_by)
	{
		$prepared_data = array();

		// Ensure all days (or months) have values first in this range
		for ($i = 0; $i <= $interval; $i++) {
			switch ($group_by) {
				case 'hour' :
					$time = strtotime(date('YmdHi', strtotime($start_date)))+$i*3600000;
					break;
				case 'day' :
					$time = strtotime(date('Ymd', strtotime("+{$i} DAY", $start_date))).'000';
					break;
				case 'month' :
				default :
					$time = strtotime(date('Ym', strtotime("+{$i} MONTH", $start_date)).'01').'000';
					break;
			}

			if (!isset($prepared_data[$time])) {
				$prepared_data[$time] = array(esc_js($time), 0);
			}
		}

		foreach ($data as $d) {
			switch ($group_by) {
				case 'hour' :
					$time = (date('H', strtotime($d->$date_key))*3600).'000';
					break;
				case 'day' :
					$time = strtotime(date('Ymd', strtotime($d->$date_key))).'000';
					break;
				case 'month' :
				default :
					$time = strtotime(date('Ym', strtotime($d->$date_key)).'01').'000';
					break;
			}

			if (!isset($prepared_data[$time])) {
				continue;
			}

			if ($data_key) {
				$prepared_data[$time][1] += $d->$data_key;
			} else {
				$prepared_data[$time][1]++;
			}
		}

		return $prepared_data;
	}

	/**
	 * Get the current range and calculate the start and end dates
	 *
	 * @param  string $current_range
	 */
	public function calculate_current_range($current_range)
	{
		switch ($current_range) {
			case 'custom' :
				$this->start_date = strtotime(sanitize_text_field($_GET['start_date']));
				$this->end_date = strtotime('midnight', strtotime(sanitize_text_field($_GET['end_date'])));

				if (!$this->end_date) {
					$this->end_date = current_time('timestamp');
				}

				$interval = 0;
				$min_date = $this->start_date;

				while (($min_date = strtotime("+1 MONTH", $min_date)) <= $this->end_date) {
					$interval++;
				}

				// 3 months max for day view
				if ($interval > 3) {
					$this->chart_groupby = 'month';
				} else {
					$this->chart_groupby = 'day';
				}
				break;
			case 'year' :
				$this->start_date = strtotime(date('Y-01-01', current_time('timestamp')));
				$this->end_date = strtotime('midnight', current_time('timestamp'));
				$this->chart_groupby = 'month';
				break;
			case 'last_month' :
				$first_day_current_month = strtotime(date('Y-m-01', current_time('timestamp')));
				$this->start_date = strtotime(date('Y-m-01', strtotime('-1 DAY', $first_day_current_month)));
				$this->end_date = strtotime(date('Y-m-t', strtotime('-1 DAY', $first_day_current_month)));
				$this->chart_groupby = 'day';
				break;
			case 'month' :
				$this->start_date = strtotime(date('Y-m-01', current_time('timestamp')));
				$this->end_date = strtotime('midnight', current_time('timestamp'));
				$this->chart_groupby = 'day';
				break;
			case '7day' :
				$this->start_date = strtotime('-6 days', current_time('timestamp'));
				$this->end_date = strtotime('midnight', current_time('timestamp'));
				$this->chart_groupby = 'day';
				break;
			case 'today' :
				$this->start_date = strtotime('midnight', current_time('timestamp'));
				$this->end_date = strtotime('+1 hour', current_time('timestamp'));
				$this->chart_groupby = 'hour';
				break;
		}

		// Group by
		switch ($this->chart_groupby) {
			case 'hour' :
				$this->group_by_query = 'YEAR(posts.post_date), MONTH(posts.post_date), DAY(posts.post_date), HOUR(posts.post_date)';
				$this->chart_interval = ceil(max(0, ($this->end_date - $this->start_date) / (60 * 60)));
				$this->barwidth = 60 * 60 * 1000;
				break;
			case 'day' :
				$this->group_by_query = 'YEAR(posts.post_date), MONTH(posts.post_date), DAY(posts.post_date)';
				$this->chart_interval = ceil(max(0, ($this->end_date - $this->start_date) / (60 * 60 * 24)));
				$this->barwidth = 60 * 60 * 24 * 1000;
				break;
			case 'month' :
				$this->group_by_query = 'YEAR(posts.post_date), MONTH(posts.post_date)';
				$this->chart_interval = 0;
				$min_date = $this->start_date;

				while (($min_date = strtotime("+1 MONTH", $min_date)) <= $this->end_date) {
					$this->chart_interval++;
				}

				$this->barwidth = 60 * 60 * 24 * 7 * 4 * 1000;
				break;
		}
	}

	/**
	 * Return currency tooltip JS based on jigoshop currency position settings.
	 *
	 * @return string
	 */
	public function get_currency_tooltip()
	{
		$options = Jigoshop_Base::get_options();
		switch ($options->get('jigoshop_currency_pos')) {
			case 'right':
				$currency_tooltip = 'append_tooltip: "'.get_jigoshop_currency_symbol().'"';
				break;
			case 'right_space':
				$currency_tooltip = 'append_tooltip: "&nbsp;'.get_jigoshop_currency_symbol().'"';
				break;
			case 'left':
				$currency_tooltip = 'prepend_tooltip: "'.get_jigoshop_currency_symbol().'"';
				break;
			case 'left_space':
			default:
				$currency_tooltip = 'prepend_tooltip: "'.get_jigoshop_currency_symbol().'&nbsp;"';
				break;
		}

		return $currency_tooltip;
	}

	/**
	 * Get the main chart
	 *
	 * @return string
	 */
	public function get_main_chart()
	{
	}

	/**
	 * Get the legend for the main chart sidebar
	 *
	 * @return array
	 */
	public function get_chart_legend()
	{
		return array();
	}

	/**
	 * [get_chart_widgets description]
	 *
	 * @return array
	 */
	public function get_chart_widgets()
	{
		return array();
	}

	/**
	 * Get an export link if needed
	 */
	public function get_export_button()
	{
	}

	/**
	 * Output the report
	 */
	abstract public function output();
}
