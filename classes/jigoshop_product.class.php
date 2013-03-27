<?php
/**
 * Product Class
 *
 * DISCLAIMER
 *
 * Do not edit or add directly to this file if you wish to upgrade Jigoshop to newer
 * versions in the future. If you wish to customise Jigoshop core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package             Jigoshop
 * @category            Catalog
 * @author              Jigoshop
 * @copyright           Copyright © 2011-2013 Jigoshop.
 * @license             http://jigoshop.com/license/commercial-edition
 */
class jigoshop_product extends Jigoshop_Base {

	// LEGACY
	private static $attribute_taxonomies = NULL;

	public $id;           // : jigoshop_template_functions.php on line 99 // This is just an alias for $this->ID
	public $ID;
	public $exists;       // : jigoshop_cart.class.php on line 66
	public $product_type; // : jigoshop_template_functions.php on line 271
	public $sku;          // : jigoshop_template_functions.php on line 246

	public $data;         // jigoshop_tax.class.php on line 186
	public $post;         // for get_title()

	public $meta;         // for get_child()

	protected $regular_price;
	protected $sale_price;
	private   $sale_price_dates_from;
	private   $sale_price_dates_to;

	private $weight;
	private $length;
	private $width;
	private $height;

	private $tax_status   = 'taxable';
	private $tax_class;

	public  $visibility   = 'visible'; // : admin/jigoshop-admin-post-types.php on line 168
	private $featured     = false;

	private $manage_stock = false;
	private $stock_status = 'instock';
	private $backorders;
	public  $stock;       // : admin/jigoshop-admin-post-types.php on line 180
	private $stock_sold;

	private	$attributes   = array();
	public  $children     = array(); // : jigoshop_template_functions.php on line 328
    protected $jigoshop_options; // : jigoshop_product_variation.php uses as well

	/**
	 * Loads all product data from custom fields
	 *
	 * @param   int               ID of the product to load
	 * @return  jigoshop_product
	 */
	public function __construct( $ID ) {

		// Grab the product ID & get the product meta data
		// TODO: Change to uppercase for consistency sake
		$this->ID = (int) $ID;
		$this->id = $this->ID;

		if ( ! $this->meta ) {
			$this->meta = get_post_custom( $this->ID );
		}

		$meta = $this->meta;

		// Check if the product has meta data attached
		// If not then it might not be a product
		$this->exists = (bool) $meta;

		// Get the product type, from the cache if we can
		$terms = current( (array) get_the_terms( $this->ID, 'product_type' ) );

		// Use slug as it is already santizied.
		$this->product_type = ( ! empty( $terms ) ) ? $terms->slug : 'simple';

		// Define data
		$this->regular_price         = isset($meta['regular_price'][0]) ? apply_filters( 'jigoshop_get_regular_price', $meta['regular_price'][0], $this) : null;
		$this->sale_price            = isset($meta['sale_price'][0]) 	? apply_filters( 'jigoshop_get_sale_price', $meta['sale_price'][0], $this) : null;
		$this->sale_price_dates_from = isset($meta['sale_price_dates_from'][0]) ? $meta['sale_price_dates_from'][0] : null;
		$this->sale_price_dates_to   = isset($meta['sale_price_dates_to'][0]) ? $meta['sale_price_dates_to'][0] : null;

		$this->weight                = isset($meta['weight'][0]) ? $meta['weight'][0] : null;
		$this->length                = isset($meta['length'][0]) ? $meta['length'][0] : null;
		$this->width                 = isset($meta['width'][0]) ? $meta['width'][0] : null;
		$this->height                = isset($meta['height'][0]) ? $meta['height'][0] : null;

		$this->tax_status            = isset($meta['tax_status'][0]) ? $meta['tax_status'][0] : null;
		$this->tax_class             = isset($meta['tax_class'][0]) ? $meta['tax_class'][0] : null;

		$this->sku                   = isset($meta['sku'][0]) ? $meta['sku'][0] : $this->ID;
		$this->visibility            = isset($meta['visibility'][0]) ? $meta['visibility'][0] : null;
		$this->featured              = isset($meta['featured'][0]) ? $meta['featured'][0] : null;

		$this->manage_stock          = isset($meta['manage_stock'][0]) ? $meta['manage_stock'][0] : null;
		$this->stock_status          = isset($meta['stock_status'][0]) ? $meta['stock_status'][0] : null;
		$this->backorders            = isset($meta['backorders'][0]) ? $meta['backorders'][0] : null;
		$this->stock                 = isset($meta['stock'][0]) ? $meta['stock'][0] : null;
		$this->stock_sold            = isset($meta['stock_sold'][0]) ? $meta['stock_sold'][0] : null;

		// filter for Paid Memberships Pro plugin courtesy @strangerstudios
		$this->sale_price = apply_filters( 'jigoshop_sale_price' , $this->sale_price, $this );

		return $this;
	}

	/**
	 * Get the main product image or parents image
	 *
	 * @return   html
	 **/
	public function get_image( $size = 'shop_thumbnail' ) {

		// Get the image size
		$size = jigoshop_get_image_size( $size );

		// If product has an image
		if( has_post_thumbnail( $this->ID ) )
		return get_the_post_thumbnail( $this->ID, $size );

		// If product has a parent and that has an image display that
		if( ($parent_ID = wp_get_post_parent_id( $this->ID )) && has_post_thumbnail( $parent_ID ) )
			return get_the_post_thumbnail( $this->ID, $size );

		// Otherwise just return a placeholder
			return '<img src="'.jigoshop::assets_url().'/assets/images/placeholder.png" alt="Placeholder" width="'.$image_size[0].'px" height="'.$image_size[1].'px" />';
	}

	/**
	 * Get SKU (Stock-keeping unit) - product uniqe ID
	 *
	 * @return   mixed
	 */
	public function get_sku() {
		return $this->sku;
	}

	/**
	 * Returns the product's children
	 *
	 * @return   array   Child IDs
	 */
	public function get_children() {

		// Check if the product type can hold child products
		if ( ! $this->is_type( array('variable', 'grouped') ) )
			return false;

		// Stop here if we already have the children
		if ( ! empty($this->children) )
			return $this->children;

		// Get the child IDs
		$this->children = get_posts(array(
			'post_parent'  => $this->ID,
			'post_type'    => ($this->is_type('variable')) ? 'product_variation' : 'product',
			'orderby'      => 'menu_order',
			'order'        => 'ASC',
			'fields'       => 'ids',
			'post_status'  => 'any',
			'numberposts'  => -1
		));

		return $this->children;
	}

	/**
	 * Return an instance of a child
	 *
	 * @param   int               Child Product ID
	 * @return  jigoshop_product
	 */
	public function get_child( $child_ID ) {

		if ( $this->is_type('variable') )
			return new jigoshop_product_variation( $child_ID );

		return new jigoshop_product( $child_ID );
	}

	/**
	 * Reduce stock level of the product
	 * Acts as an alias for modify_stock()
	 *
	 * @param   int   Amount to reduce by
	 * @return  int
	 */
	public function reduce_stock( $by = -1 ) {
		return $this->modify_stock( -$by );
	}

	/**
	 * Increase stock level of the product
	 * Acts as an alias for modify_stock()
	 *
	 * @param   int   Amount to increase by
	 * @return  int
	 */
	public function increase_stock( $by = 1 ) {
		return $this->modify_stock( $by );
	}

	/**
	 * Modifies the stock levels
	 *
	 * @param   int   Amount to modify
	 * @return  int
	 */
	public function modify_stock( $by ) {

		// Only do this if we're updating
		if ( ! $this->managing_stock() )
			return false;

		// +- = minus
		$this->stock = $this->stock + $by;
		$amount_sold = $this->stock_sold + $by;

		// Update & return the new value
		update_post_meta( $this->ID, 'stock', $this->stock );
		update_post_meta( $this->ID, 'stock_sold', $amount_sold );

		if ( self::get_options()->get_option('jigoshop_notify_no_stock_amount') >= 0
			&& self::get_options()->get_option('jigoshop_notify_no_stock_amount') >= $this->stock
			&& self::get_options()->get_option( 'jigoshop_hide_no_stock_product' ) == 'yes' ) {

			update_post_meta( $this->ID, 'visibility', 'hidden' );

		} else if ( $this->stock > self::get_options()->get_option('jigoshop_notify_no_stock_amount')
			&& $this->visibility == 'hidden'
			&& self::get_options()->get_option( 'jigoshop_hide_no_stock_product' )  == 'yes' ) {

			update_post_meta( $this->ID, 'visibility', 'visible' );
		}

		return $this->stock;
	}

	/**
	 * Checks if a product requires shipping
	 *
	 * @return   bool
	 */
	public function requires_shipping() {
		// If it's virtual or downloadable don't require shipping, same for subscriptions
		return  apply_filters('jigoshop_requires_shipping',(!($this->is_type( array('downloadable', 'virtual', 'subscription')))),$this->id);
	}
	/**
	 * Checks the product type
	 *
	 * @param   string   Type to check against
	 * @return  bool
	 */
	public function is_type( $type ) {

		if ( is_array($type) && in_array($this->product_type, $type) )
			return true;

		if ($this->product_type == $type)
			return true;

		return false;
	}

	/**
	 * Returns whether or not the product has any child product
	 *
	 * @return  bool
	 */
	public function has_child() {
		return (bool) $this->get_children();
	}

	/**
	 * Checks to see if a product exists
	 *
	 * @return  bool
	 */
	public function exists() {
		return (bool) $this->exists;
	}

	/**
	 * Returns whether or not the product is taxable
	 *
	 * @return  bool
	 */
	public function is_taxable() {
		return ( $this->tax_status == 'taxable' );
	}

	/**
	 * Returns whether or not the product shipping is taxable
	 *
	 * @return  bool
	 */
	public function is_shipping_taxable() {
		return ( $this->is_taxable() || $this->tax_status == 'shipping' );
	}

	/**
	 * Get the product's post data
	 * @deprecated Should be using WP native the_title() right? -Rob
	 * NOTE: Only used for get_title()
	 *
	 * @return  object
	 */
	public function get_post_data() {
		if (empty($this->post)) {
			$this->post = get_post( $this->ID );
		}

		return $this->post;
	}

	/**
	 * Get the product's post data
	 * @deprecated Should be using WP native the_title() right? -Rob
	 * NOTE: Only used for get_title()
	 *
	 * @return  string
	 */
	public function get_title() {
		$this->get_post_data();
		return apply_filters('jigoshop_product_title', get_the_title($this->post->ID), $this);
	}

	/**
	 * Get the add to url
	 *
	 * @return  mixed
	 */
	public function add_to_cart_url() {

		if ( $this->has_child() ) {
			$url = add_query_arg('add-to-cart', 'group');
			$url = add_query_arg('product', $this->ID, $url);

			if ($this->is_type('variable')) {
				$url = add_query_arg('add-to-cart', 'variation');
			}
		}
		else {
			$url = add_query_arg('add-to-cart', $this->ID);
		}

		$url = jigoshop::nonce_url( 'add_to_cart', $url );
		return $url;
	}

	/**
	 * Check if we are managing stock
	 *
	 * @return  bool
	 */
	public function managing_stock() {

		// If we're not managing stock at all
		if (self::get_options()->get_option('jigoshop_manage_stock') != 'yes')
			return false;

		return (bool) $this->manage_stock;
	}

	/**
	 * Returns whether or not the product is in stock
	 *
	 * @param   bool    Whether to compare against the global setting for no stock threshold
	 * @return  bool
	 */
	public function is_in_stock( $below_stock_threshold = false ) {

		// Always return in stock if product is in stock
		if (self::get_options()->get_option('jigoshop_manage_stock') != 'yes')
			return true;

		if ( $this->is_type( array('grouped', 'variable') ) ) {
			foreach( $this->get_children() as $child_ID ) {

				// Get the children
				$child = $this->get_child( $child_ID );

				// If one of our children is in stock then return true
				if ( $child->is_in_stock() )
					return true;
			}
		}

		// If we arent managing stock then it should always be in stock
		if( ! $this->managing_stock() && $this->stock_status == 'instock' )
			return true;

		// Check if we allow backorders
		if( $this->managing_stock() && $this->backorders_allowed() )
			return true;

		// Check if we have stock
		if( $this->managing_stock() && ($below_stock_threshold ? $this->stock >= self::get_options()->get_option('jigoshop_notify_no_stock_amount') : $this->stock > 0 ) )
			return true;

		return false;
	}

	/**
	 * Returns whether or not the product can be backordered
	 *
	 * @return  bool
	 */
	public function backorders_allowed() {

		if ( $this->backorders == 'yes' || $this->backorders_require_notification() )
			return true;

		return false;
	}

	/**
	 * Returns whether or not the product needs to notify the customer on backorder
	 *
	 * @return  bool
	 */
	public function backorders_require_notification() {

		return ($this->backorders == 'notify');
	}

	/**
	 * Check the stock levels to unsure we have enough to match request
	 *
	 * @param   int $quantity   Amount to verify that we have
	 * @return  bool
	 */
	public function has_enough_stock( $quantity ) {

		// always work from a new product to check actual stock available
		// this product instance could be sitting in a Cart for a user and
		// another customer purchases the last available
		$temp = new jigoshop_product( $this->ID );
		return ($this->backorders_allowed() || $temp->stock >= $quantity);
	}

	/**
	 * Returns number of items available for sale.
	 *
	 * @return  int
	 */
	public function get_stock() {
		return (int) $this->stock;
	}

	/**
	 * Returns a string representing the availability of the product
	 *
	 * @return  string
	 */
	public function get_availability() {


		// Do not display initial availability if we aren't managing stock or if variable or grouped
		if ( self::get_options()->get_option('jigoshop_manage_stock') != 'yes' || $this->is_type( array('grouped', 'variable')) )
			return false;

		// Start as in stock
		$notice = array(
			'availability'	=> __( 'In Stock', 'jigoshop' ),
			'class'			=> null,
		);

		// If stock is being managed & has stock
		if ( $this->managing_stock() && $this->is_in_stock() ) {
			if ( $this->stock <= 0 ) {
				$notice['availability'] = __( 'Out of Stock', 'jigoshop' );
				$notice['availability'] .= (self::get_options()->get_option('jigoshop_show_stock') == 'yes' && ! $this->has_child() ) ? " &ndash; {$this->stock} ".__(' available', 'jigoshop' ) : '';
			} else
				$notice['availability'] .= (self::get_options()->get_option('jigoshop_show_stock') == 'yes' && ! $this->has_child() ) ? " &ndash; {$this->stock} ".__(' available', 'jigoshop' ) : '';

			// If customers require backorder notification
			if ( $this->backorders_allowed() ) {
				$notice['availability'] = $notice['availability'] .' ('.__('backorders allowed','jigoshop').')';
			}
		}
		else if ( $this->managing_stock() && $this->backorders_allowed() ) {
			$notice['availability']	= __( 'Available on Backorder', 'jigoshop' );
		}

		// Declare out of stock if we don't have any stock
		if ( ! $this->is_in_stock() ) {
			$notice['availability']	= __( 'Out of Stock', 'jigoshop' );
			$notice['class']		= 'out-of-stock';
		}

		return apply_filters( 'jigoshop_product_availability', $notice, $this );
	}

	/**
	 * Returns whether or not the product is featured
	 *
	 * @return  bool
	 */
	public function is_featured() {
		return (bool) $this->featured;
	}

	/**
	 * Checks if the product is visibile
	 *
	 * @return  bool
	 */
	public function is_visible( ) {

		// Disabled due to incorrect stock handling -Rob
		//if( (bool) $this->stock )
		//	return false;

		switch($this->visibility) {
			case 'hidden':
				return false;
			break;
			case 'search':
				return is_search();
			break;
			case 'catalog':
				return ! is_search(); // don't display in search results
			break;
			default:
				return true; // By default always display a product
		}
	}

	/**
	 * Returns whether or not the product is on sale.
	 * If one of the child products is on sale, product is considered to be on sale
	 *
	 * @return  bool
	 */
	public function is_on_sale() {

		// Check child products for items on sale
		if ( $this->is_type( array('grouped', 'variable') ) ) {

			foreach( $this->get_children() as $child_ID ) {

				$child = $this->get_child( $child_ID );
				if( $child->is_on_sale() )
					return true;
			}
		}

		$time = current_time('timestamp');

		// Check if the sale is still in range (if we have a range)
		if ( $this->sale_price_dates_from	<= $time &&
			 $this->sale_price_dates_to		>= $time &&
			 $this->sale_price)
			return true;

		// Otherwise if we have a sale price
		if ( ! $this->sale_price_dates_to && $this->sale_price )
			return true;

		// Just incase return false
		return false;
	}

	/**
	 * Returns the product's weight
	 * @deprecated not required since we can just call $this->weight if the var is public
	 *
	 * @return  mixed   weight
	 */
	public function get_weight() {
		return $this->weight;
	}

	/**
     * Get the product total price excluding tax
     *
     * @param   int     $quantity
     *
     * $return  float   the total price of the product times the quantity without any tax included
     */
	function get_price_excluding_tax( $quantity = 1 ) {

        // to avoid rounding errors multiply by 100
        $price = $this->get_price() * 100;

        if ( self::get_options()->get_option('jigoshop_prices_include_tax') == 'yes' ) {

            $rates = (array) $this->get_tax_base_rate();

            if ( count( $rates > 0 )) {

                // rates array sorted so that taxes applied to retail value come first. To reverse taxes, need to reverse this array
                $new_rates = array_reverse($rates, true);

                $tax_totals = 0;

                $_tax = new jigoshop_tax(100);

                foreach ( $new_rates as $key=>$value ) :

                    if ($value['is_not_compound_tax']) :
                        $tax_totals += $_tax->calc_tax( $price * $quantity, $value['rate'], true );
                    else :
                        $tax_amount[$key] = $_tax->calc_tax( $price * $quantity, $value['rate'], true );
                        $tax_totals += $tax_amount[$key];
                    endif;

                endforeach;

                return round( ($price * $quantity - $tax_totals) / 100, 4 );

            }

        }

		return round( $price * $quantity / 100, 4 );

    }

	/**
     * Get the product total price including tax
     *
     * @param   int     $quantity
     *
     * $return  float   the total price of the product times the quantity and destination tax included
     */
	function get_price_with_tax( $quantity = 1 ) {

        // to avoid rounding errors multiply by 100
        $price = $this->get_price_excluding_tax() * 100;
		$tax_totals = 0;
		$rates = (array) $this->get_tax_destination_rate();

		if ( count( $rates > 0 )) {

			// rates array sorted so that taxes applied to retail value come first. To reverse taxes, need to reverse this array
			$new_rates = array_reverse( $rates, true );

			$tax_totals = 0;

			$_tax = new jigoshop_tax( 100 );

			foreach ( $new_rates as $key => $value ) {

				if ( $value['is_not_compound_tax'] ) {
					$tax_totals += $_tax->calc_tax( $price * $quantity, $value['rate'], false );
				} else {
					$tax_amount[$key] = $_tax->calc_tax( $price * $quantity, $value['rate'], false );
					$tax_totals += $tax_amount[$key];
				}
				$tax_totals = round( $tax_totals, 1 );  // without this we were getting rounding errors

			}

		}

		return round( ($price * $quantity + $tax_totals) / 100, 4 );

	}

	/**
	 * Returns the base Country and State tax rate
	 */
	public function get_tax_base_rate() {

		$rate = array();

        if ($this->is_taxable() && self::get_options()->get_option('jigoshop_calc_taxes') == 'yes') :
            $_tax = new jigoshop_tax();

            if ($_tax->get_tax_classes_for_base()) foreach ( $_tax->get_tax_classes_for_base() as $tax_class ) :

                if ( !in_array($tax_class, $this->get_tax_classes())) continue;
                $my_rate = $_tax->get_shop_base_rate($tax_class);

                if ($my_rate > 0) :
                    $rate[$tax_class] = array('rate'=>$my_rate, 'is_not_compound_tax'=>!$_tax->is_compound_tax());
                endif;

            endforeach;

        endif;

        return $rate;
	}

	/**
	 * Returns the destination Country and State tax rate
	 */
	public function get_tax_destination_rate() {

		$rate = array();

        if ( $this->is_taxable() && self::get_options()->get_option('jigoshop_calc_taxes') == 'yes' ) {

            $_tax = new jigoshop_tax();

            if ( $_tax->get_tax_classes_for_base() ) foreach ( $_tax->get_tax_classes_for_base() as $tax_class ) {

                if ( ! in_array( $tax_class, $this->get_tax_classes() )) continue;
                $my_rate = $_tax->get_rate( $tax_class );

                if ( $my_rate > 0 ) {
                    $rate[$tax_class] = array( 'rate' => $my_rate, 'is_not_compound_tax' => ! $_tax->is_compound_tax() );
                }

            }

        }

        return $rate;
	}

    /**
     * This function returns the tax rate for a particular tax_class applied to the product
     *
     * @param string tax_class the class of tax to find
     * @param array product_tax_rates the tax rates applied to the product
     * @return double the tax rate percentage
     */
    public static function get_product_tax_rate($tax_class, $product_tax_rates) {

        if ($tax_class && $product_tax_rates && is_array($product_tax_rates)) :
            return $product_tax_rates[$tax_class]['rate'];
        endif;

        return (double) 0;
    }

    /**
     * Returns true if the tax is not compounded.
     * @param string tax_class the tax class return value on
     * @param array product_tax_rates the array of tax rates on the product
     * @return bool true if tax class is not compounded. False otherwise. Default true.
     */
    public static function get_non_compounded_tax($tax_class, $product_tax_rates) {

        if ($tax_class && $product_tax_rates && is_array($product_tax_rates)) :
            return $product_tax_rates[$tax_class]['is_not_compound_tax'];
        endif;

        return true;  // default to true for non compound tax
    }

	/**
	 * Returns the percentage saved on sale products
	 * @note was called get_percentage()
	 *
	 * @return  string
	 */
	public function get_percentage_sale() {

		if ( $this->is_on_sale() ) {
			// 100% - sale price percentage over regular price
			$percentage = 100 - ( ($this->sale_price / $this->regular_price) * 100);

			// Round & return
			return round($percentage).'%';
		}
	}

	/**
	 * Returns the products regular price
	 *
	 * @return  float
	 */
	public function get_regular_price() {
		return $this->regular_price;
	}

	/**
	 * Returns the products current price, either regular or sale
	 *
	 * @return  int
	 */
	public function get_price() {

		$price = null;
		if ( $this->is_on_sale() ) {
			if ( strstr($this->sale_price,'%') ) {
				$price = round($this->regular_price * ( (100 - str_replace('%','',$this->sale_price) ) / 100 ), 4);
			} else if ( $this->sale_price ) {
				$price = $this->sale_price;
			}
		} else {
			$price = apply_filters('jigoshop_product_get_regular_price', $this->regular_price, $this->ID);
		}
		return apply_filters( 'jigoshop_product_get_price', $price, $this->ID );

    }

	/**
	 * Adjust the products price during runtime
	 *
	 * @param   mixed
	 * @return  void
	 */
	public function adjust_price( $new_price ) {

		// Only adjust sale price if we are on sale
		if ( $this->is_on_sale() )
			$this->sale_price = $this->get_price() + $new_price;
		else
			$this->regular_price += $new_price;
	}

	/**
	 * Returns the products sale value, either with or without a percentage
	 *
	 * @return html
	 */
	public function get_calculated_sale_price_html() {

		if ( $this->is_on_sale() ) :
			if ( strstr($this->sale_price,'%') )
				return '
					<del>' . jigoshop_price( $this->regular_price ) . '</del>' . jigoshop_price( $this->get_price() ) . '
					<br><ins>' . sprintf(__('%s off!', 'jigoshop'), $this->sale_price) . '</ins>';
			else
				return	'
					<del>' . jigoshop_price( $this->regular_price ) . '</del>
					<ins>' . jigoshop_price( $this->sale_price ) . '</ins>';

		endif;
		return '';
	}

	/**
	 * Returns the price in html format
	 *
	 * @return  html
	 */
	public function get_price_html() {

		$html = null;

		// First check if the product has child products
		if ( (($this->is_type( 'variable') )) || (($this->is_type('grouped'))&& ($this->regular_price == null))) {

			if ( ! ($children = $this->get_children()) )
				return __( 'Unavailable', 'jigoshop' );

			$array = array();
			$onsale = false;
			foreach ( $children as $child_ID ) {
				$child = $this->get_child($child_ID);

				// Only get prices that are in stock
				if ( $child->is_in_stock() ) {
					if ( $child->is_on_sale() ) $onsale = true; // signal at least one child is on sale
					// store product id for later, get regular or sale price if available
					$array[$child_ID] = $child->get_price();
				}
			}
			asort( $array );	// cheapest price first

			// only display 'From' if prices differ among them
			$sameprice = true;
			if ( count( $array ) >= 2 && reset( $array ) != end( $array ) ) $sameprice = false;
			if ( ! $sameprice ) :
				$html = '<span class="from">' . _x('From:', 'price', 'jigoshop') . '</span> ';
				reset( $array );
				$id = key( $array );
				$child = $this->get_child( $id );
				if ( $child->is_on_sale() )
					$html .= $child->get_calculated_sale_price_html();
				else
					$html .= jigoshop_price( $child->get_price() );
			elseif ( $onsale ) : // prices may be the same, but we could be on sale and need the 'From'
				$html = '<span class="from">' . _x('From:', 'price', 'jigoshop') . '</span> ';
				reset( $array );
				$id = key( $array );
				$child = $this->get_child( $id );
				if ( $child->is_on_sale() )
					$html .= $child->get_calculated_sale_price_html();
				else
					$html .= jigoshop_price( $child->get_price() );
			else :	// prices are the same
            	$html = jigoshop_price( reset( $array ) );
			endif;

			$html = empty( $array ) ? __( 'Price Not Announced', 'jigoshop' ) : $html;

			return apply_filters('jigoshop_product_get_price_html', $html, $this, $this->regular_price);
		}

		// For standard products

		if ( $this->is_on_sale() )
			$html = $this->get_calculated_sale_price_html();
		else
			$html = jigoshop_price( $this->get_price() );

		if ( $this->get_price() == 0 )
			$html = __( 'Free', 'jigoshop' );

		if ( $this->regular_price == '' )
			$html = __( 'Price Not Announced', 'jigoshop' );

        return apply_filters('jigoshop_product_get_price_html', $html, $this, $this->regular_price);
    }

	/**
	 * Returns the upsell product ids
	 *
	 * @return  mixed
	 */
	public function get_upsells() {
		$ids = get_post_meta( $this->id, 'upsell_ids' );
		if ( ! empty( $ids )) return $ids[0];
		else return array();
	}

	/**
	 * Returns the cross_sells product ids
	 *
	 * @return  mixed
	 */
	public function get_cross_sells() {
		$ids = get_post_meta( $this->id, 'crosssell_ids' );
		if ( ! empty( $ids )) return $ids[0];
		else return array();
	}

	/**
	 * Returns the product's length
	 * @deprecated not required since we can just call $this->weight if the var is public
	 *
	 * @return  mixed   length
	 */
	public function get_length() {
		return $this->length;
	}

	/**
	 * Returns the product's width
	 * @deprecated not required since we can just call $this->weight if the var is public
	 *
	 * @return  mixed   width
	 */
	public function get_width() {
		return $this->width;
	}

	/**
	 * Returns the product's height
	 * @deprecated not required since we can just call $this->weight if the var is public
	 *
	 * @return  mixed   height
	 */
	public function get_height() {
		return $this->height;
	}

    /**
     * Returns the tax classes
     * @return array the tax classes on the product
     */
    public function get_tax_classes() {
        return (array) get_post_meta($this->ID, 'tax_classes', true);
    }
	/**
	 * Returns the product categories
	 *
	 * @return  HTML
	 */
	public function get_categories( $sep = ', ', $before = '', $after = '' ) {
		return get_the_term_list($this->ID, 'product_cat', $before, $sep, $after);
	}

	/**
	 * Returns the product tags
	 *
	 * @return  HTML
	 */
	public function get_tags( $sep = ', ', $before = '', $after = '' ) {
		return get_the_term_list($this->ID, 'product_tag', $before, $sep, $after);
	}

	// Returns the product rating in html format
	// TODO: optimize this code
	public function get_rating_html( $location = '' ) {

		if( $location )
			$location = '_'.$location;
		$star_size = apply_filters('jigoshop_star_rating_size'.$location, 16);

		global $wpdb;

		// Do we really need this? -Rob
		$count = $wpdb->get_var( $wpdb->prepare("
			SELECT COUNT(meta_value) FROM $wpdb->commentmeta
			LEFT JOIN $wpdb->comments ON $wpdb->commentmeta.comment_id = $wpdb->comments.comment_ID
			WHERE meta_key = 'rating'
			AND comment_post_ID = %d
			AND comment_approved = '1'
			AND meta_value > 0
		", $this->id ) );

		$ratings = $wpdb->get_var( $wpdb->prepare("
			SELECT SUM(meta_value) FROM $wpdb->commentmeta
			LEFT JOIN $wpdb->comments ON $wpdb->commentmeta.comment_id = $wpdb->comments.comment_ID
			WHERE meta_key = 'rating'
			AND comment_post_ID = %d
			AND comment_approved = '1'
		", $this->id ) );

		// If we don't have any posts
		if ( ! (bool)$count )
			return false;

		// Figure out the average rating
		$average_rating = number_format($ratings / $count, 2);

		// If we don't have an average rating
		if( ! (bool)$average_rating )
			return false;

		// If all goes well echo out the html
		return '<div class="star-rating" title="'.sprintf(__('Rated %s out of 5', 'jigoshop'), $average_rating).'"><span style="width:'.($average_rating*$star_size).'px"><span class="rating">'.$average_rating.'</span> '.__('out of 5', 'jigoshop').'</span></div>';
	}

	/**
	 * Gets all products which have a common category or tag
	 * TODO: Add stock check?
	 *
	 * @return	array
	 */
	public function get_related( $limit = 5 ) {

		// Get the tags & categories
		$tags = wp_get_post_terms($this->ID, 'product_tag', array('fields' => 'ids'));
		$cats = wp_get_post_terms($this->ID, 'product_cat', array('fields' => 'ids'));

		// No queries if we don't have any tags -and- categories (one -or- the other should be queried)
		if( empty( $cats ) && empty( $tags ) )
			return array();

		// Only get related posts that are in stock & visible
		$query = array(
			'posts_per_page' => $limit,
			'post__not_in'   => array( $this->ID ),
			'post_type'      => 'product',
			'fields'         => 'ids',
			'orderby'        => 'rand',
			'meta_query'     => array(
				array(
					'key'       => 'visibility',
					'value'     => array( 'catalog', 'visible' ),
					'compare'   => 'IN',
				),
			),
			'tax_query'       => array(
				'relation'       => 'OR',
				array(
					'taxonomy'   => 'product_cat',
					'field'      => 'id',
					'terms'      => $cats
				),
				array(
					'taxonomy'   => 'product_tag',
					'field'      => 'id',
					'terms'      => $tags
				),
			),
		);

		// Run the query
		$q = get_posts( $query );
		wp_reset_postdata();

		return $q;
	}

	/**
	 * Gets a single product attribute
	 *
	 * @return  string|array
	 */
	public function get_attribute( $key ) {

		// Get the attribute in question & sanitize just incase
		$attributes = $this->get_attributes();
		$attr = $attributes[sanitize_title($key)];

		// If its a taxonomy return that
		if( $attr['is_taxonomy'] )
			return get_the_terms( $this->ID, 'pa_'.sanitize_title($attr['name']) );

		return $attr['value'];
	}

	/**
	 * Gets the attached product attributes
	 *
	 * @return  array
	 */
	public function get_attributes() {

		// Get the attributes
		if ( ! $this->attributes )
			if ( isset( $this->meta['product_attributes'] ) )
				$this->attributes = maybe_unserialize( $this->meta['product_attributes'][0] );

		return (array) $this->attributes;
	}

	/**
	 * Checks for any visible attributes attached to the product
	 *
	 * @return  boolean
	 */
	public function has_attributes() {
		$attributes = $this->get_attributes();

		// Quit early if there aren't any attributes
		if ( empty( $attributes ) )
			return false;

		// If we have attributes that are visible return true
		foreach ( $attributes as $attribute ) {
			if ( ! empty($attribute['visible']) )
				return true;
		}

		// By default we don't have any attributes
		return false;
	}

	/**
	 * Checks if the product has dimensions
	 *
     * @param boolean all_dimensions if true, then all dimensions have to be set
     * in order for has_dimensions to return true, otherwise if false, then just 1
     * of the dimensions has to be set for the function to return true.
	 * @return  bool
	 */
	public function has_dimensions($all_dimensions = false) {

		if ( self::get_options()->get_option('jigoshop_enable_dimensions') != 'yes' )
			return false;

		return ( $all_dimensions ? ($this->get_length() && $this->get_width() && $this->get_height()) :($this->get_length() || $this->get_width() || $this->get_height()));
	}

	/**
	 * Checks if the product has weight
	 *
	 * @return  bool
	 */
	public function has_weight() {

		if ( self::get_options()->get_option('jigoshop_enable_weight') != 'yes' )
			return false;

		return (bool) $this->get_weight();
	}

	/**
	 * Lists attributes in a html table
	 *
	 * @return  html
	 **/
	public function list_attributes() {


		// Check that we have some attributes that are visible
		if ( !( $this->has_attributes() || $this->has_dimensions() || $this->has_weight() ) )
			return false;

		// Start the html output
		$html = '<table class="shop_attributes">';

		// Output weight if we have it
		if (self::get_options()->get_option('jigoshop_enable_weight')=='yes' && $this->get_weight() ) {
			$html .= '<tr><th>'.__('Weight', 'jigoshop').'</th><td>'. $this->get_weight() . self::get_options()->get_option('jigoshop_weight_unit') .'</td></tr>';
		}

		// Output dimensions if we have it
		if (self::get_options()->get_option('jigoshop_enable_dimensions')=='yes') {
			if ( $this->get_length() )
				$html .= '<tr><th>'.__('Length', 'jigoshop').'</th><td>'. $this->get_length() . self::get_options()->get_option('jigoshop_dimension_unit') .'</td></tr>';
			if ( $this->get_width() )
				$html .= '<tr><th>'.__('Width', 'jigoshop').'</th><td>'. $this->get_width() . self::get_options()->get_option('jigoshop_dimension_unit') .'</td></tr>';
			if ( $this->get_height() )
				$html .= '<tr><th>'.__('Height', 'jigoshop').'</th><td>'. $this->get_height() . self::get_options()->get_option('jigoshop_dimension_unit') .'</td></tr>';
		}

		$attributes = $this->get_attributes();
		foreach( $attributes as $attr ) {

			// If attribute is invisible skip
			if ( empty( $attr['visible'] ) )
				continue;

			// Get Title & Value from attribute array
			$name = jigoshop_product::attribute_label('pa_'.$attr['name']);
			$value = null;

			if ( (bool) $attr['is_taxonomy'] ) {

				// Get the taxonomy terms
				$product_terms = wp_get_object_terms( $this->ID, 'pa_'.sanitize_title($attr['name']), array( 'orderby' => 'slug' ) );

				if ( is_wp_error( $product_terms )) {
					jigoshop_log( "product::list_attributes() - Attribute for invalid taxonomy = " . $attr['name'] );
					continue;
				}

				// Convert them into a array to be imploded
				$terms = array();

				foreach( $product_terms as $term ) {
					$terms[] = '<span class="val_'.$term->slug.'">'.$term->name.'</span>';
				}

				$value = apply_filters('jigoshop_product_attribute_value_taxonomy',implode(', ', $terms), $terms, $attr);
			}
			else {
				$value = apply_filters('jigoshop_product_attribute_value_custom',wptexturize($attr['value']), $attr);
			}

			// Generate the remaining html
			$html .= "
			<tr class=\"attr_".$attr['name']."\">
				<th>$name</th>
				<td>$value</td>
			</tr>";
		}

		$html .= '</table>';
		return $html;
	}

	/**
	 * Returns an array of available values for attributes used in product variations
	 *
	 * TODO: Note that this is 'variable product' specific, and should be moved to separate class
	 * with all 'variable product' logic form other methods in this class.
	 *
	 * @return   two dimensional array of attributes and their available values
	 */
	function get_available_attributes_variations() {

		if (!$this->is_type('variable') || !$this->has_child()) {
			return array();
		}

		$attributes = $this->get_attributes();

		if(!is_array($attributes)) {
			return array();
		}

		$available_attributes = array();
		$children = $this->get_children();


		foreach ($attributes as $attribute) {

			// If we don't have any variations
			if ( ! $attribute['variation']) continue;

			$values = array();

			$attr_name = 'tax_'.sanitize_title($attribute['name']);

			foreach ($children as $child) {

				// Check if variation is disabled
				if ( get_post_status( $child ) != 'publish' ) continue;

				// Get the variation & all attributes associated
				$child = $this->get_child( $child );
				$options = $child->get_variation_attributes();

				if ( is_array($options)) {
					foreach($options as $key => $value) {
						if ( $key == $attr_name )
							$values[] = $value;
					}
				}
			}

			//empty value indicates that all options for given attribute are available
			if( in_array('', $values) ) {

				if ( $attribute['is_taxonomy'] ) {
					$options = array();
					$terms = wp_get_object_terms( $this->ID, 'pa_'.sanitize_title($attribute['name']), array( 'orderby' => 'slug' ) );

					foreach($terms as $term) {
						$options[] = $term->slug;
					}
				}
				else {
					$options = explode(',', $attribute['value']);
				}

				$options = array_map('trim', $options);
				$values = array_unique($options);

			} else {

				if( ! $attribute['is_taxonomy'] ) {
					$options = explode(',', $attribute['value']);
					$options = array_map('trim', $options);
					$values = array_intersect( $options, $values );
				}

				$values = array_unique($values);
			}

			$values = array_unique($values);
			asort( $values );
			$available_attributes[$attribute['name']] = $values;
		}

		return $available_attributes;
	}

	/**
	 * Gets the default attributes for a variable product.
	 *
	 * @return array
	 */
	function get_default_attributes() {

		$default = isset( $this->meta['_default_attributes'][0] ) ? $this->meta['_default_attributes'][0] : '';

		return apply_filters( 'jigoshop_product_default_attributes', (array) maybe_unserialize( $default ), $this );
	}

	/**
	 * Get attribute taxonomies. Taxonomies are lazy loaded.
	 *
	 * @return  array of stdClass objects representing attributes
	 */
	public static function getAttributeTaxonomies() {
		global $wpdb;

		if(self::$attribute_taxonomies === NULL) {
			self::$attribute_taxonomies = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."jigoshop_attribute_taxonomies;");
		}

		return self::$attribute_taxonomies;
	}

	/**
	 * Get a product attributes label
	 */
	public function attribute_label( $name ) {
		global $wpdb;

		if (strstr( $name, 'pa_' )) :
			$name = str_replace( 'pa_', '', sanitize_title( $name ) );

			$label = $wpdb->get_var( $wpdb->prepare( "SELECT attribute_label FROM ".$wpdb->prefix."jigoshop_attribute_taxonomies WHERE attribute_name = %s;", $name ) );

			if ($label) return $label; else return ucfirst($name);
		else :
			return $name;
		endif;
	}

}
