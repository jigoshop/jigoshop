=== Jigoshop ===
Contributors: Jigoshop
License: GNU General Public License v3
Tags: 2checkout, 2co, affiliate, authorize, cart, checkout, commerce, coupons, e-commerce,ecommerce, gifts, moneybookers, online, online shop, online store, paypal, paypal advanced,Paypal Express, paypal pro, physical, reports, sagepay, sales, sell, shipping, shop,shopping, stock, stock control, store, tax, virtual, weights, widgets, wordpress ecommerce, wp e-commerce, woocommerce
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=sales%40jigoshop%2ecom&lc=US&item_name=Jigoshop%20%2d%20Wordpress%2eorg%20donation%20link&no_note=0&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHostedGuest
Requires at least: 3.8
Tested up to: 4.0.1
Stable tag: 1.13.3

A feature-packed eCommerce plugin built upon WordPress core functionality ensuring excellent performance and customizability.

== Description ==

Use <a href="https://www.jigoshop.com">Jigoshop</a> to turn your WordPress website into a dynamic eCommerce store.
Jigoshop is led by a motivated development team with years of experience with delivering professional online shops
for global brands. Our number one priority is to make it easy to get professional results for WordPress eCommerce solution.

With the Jigoshop plugin for WordPress you have your very own web store for your website. You have complete control
of your eCommerce shop.

= SETUP IN MINUTES =

Setup your web store in minutes with an extensive amount of shop settings including base country, currency,
catalog options, stock management, unlimited tax settings, shipping and payment gateways out of the box.
Plus there are hundreds of additional extensions to build up Jigoshop to be an even more powerful WP eCommerce
solution.

= PRODUCT TYPES =

Jigoshop includes several product type options for your eCommerce shop. They include:

* Downloadable or Virtual products
* Variable products (e.g. offer Size: S,M,L for one product)
* Affiliate (External) products (i.e. link your Add to cart button off-site)
* Grouped products

= DETAILED REPORTS =

Within the Jigoshop eCommerce plugin are various reporting features to give you real-time insight of your
shops performance. Features include sortable sales graphs and incoming order/review notifications.

= STOCK MANAGEMENT =

Jigoshop has the ability to manage your shops stock. Included is an option to allow Jigoshop to inform you
of low stock once it reaches your set threshold so that your shop never runs out of stock.

= EXTEND YOUR SHOP! =

Sure, Jigoshop runs out of the box! But Jigoshop’s functionality doesn’t have end there. We have over
one hundred extensions for Jigoshop available that will further extend the power of the best eCommerce
plugin ever! They include more Payment Gateways, more Shipping methods and much more.

Jigoshop eCommerce strives to maintain its status as the best WordPress eCommerce plugin ever. We hope you’ll
choose the best to power your eCommerce shop and help us prove to you that we really are the best!

You can take a look at our official extensions here: http://www.jigoshop.com/product-category/extensions/

And our Jigoshop-optimized themes here: http://www.jigoshop.com/product-category/themes/

== Upgrade Notice ==

[Click here for complete changelog](https://wordpress.org/plugins/jigoshop/changelog/ "Jigoshop Changelog")

== Installation ==

= Requirements =

* WordPress 3.8 or greater
* PHP version 5.3 or greater
* MySQL version 5.0 or greater
* The mod_rewrite Apache module (for permalinks)
* Some payment gateways require fsockopen support (for IPN access)
* Some extensions require allow_url_fopen enabled (for remote files fetching)

= Installation =

1.  Download the Jigoshop plugin file
2.  Unzip the file into a folder to your computer
3.  Upload the `/jigoshop/` folder to the `/wp-content/plugins/` folder on your site
4.  Visit the plugins page in WordPress Admin to activate the Jigoshop plugin

You can also navigate to the <a href="https://www.jigoshop.com/documentation/installation/">more in-depth installation or upgrade</a> guides.

= Setting up Jigoshop =

Take a look through our <a href="https://www.jigoshop.com/documentation" title="Jigoshop usage guide">Jigoshop usage guides</a> to help you setup Jigoshop for the first time.

== Frequently Asked Questions ==

= Will Jigoshop work with X theme? =

Jigoshop will in theory work with any theme, but of course, certain parts may need to be styled using CSS to make them match up. We've added default styling for Twenty Ten (the WordPress default theme) and we also offer <a href="http://www.jigoshop.com/product-category/themes/">premium themes optimised for Jigoshop</a>.

If you need a theme built, or have a theme that needs styling, <a href="https://www.jigoshop.com/contact/">give us a shout</a> and we may be able to assist.

= Can I have Jigoshop in my language =

Jigoshop comes with a .po file and is localisation ready in over 10 languages.
You can also <a href="https://www.jigoshop.com/documentation/localization-tutorial/">create your own translations</a> for Jigoshop.

= Which payment gateways do you have? =

Take a look through <a href="https://www.jigoshop.com/documentation/payment-gateways/">our list of payment gateways</a>. There are some free ones that are included with Jigoshop, and even more are available <a href="http://www.jigoshop.com/product-category/payment-gateways/">on jigoshop.com</a>.

= Will tax settings work in my country? =

Jigoshop has a flexible tax rule system which allows you to define tax rates per country - it should allow you to do what you want.

= I need hosting! =

You're in luck! We offer <a href="http://www.jigoshop.com/">optimised hosting packages</a> starting from 10 GBP per month.

= I need help! =

We have <a href="https://www.jigoshop.com/documentation/" title="Jigoshop Documentation">documentation</a> for seeking information.
However, if you want priority, dedicated support from Jigoshop staff, we dp offer <a href="http://www.jigoshop.com/support/" title="Jigoshop Premium Support">premium support packages</a>.

== Screenshots ==

1. Jigoshop admin dashboard
2. Admin product edit page
3. Jigoshop homepage on a premium theme
4. Standard customer checkout screen

== Changelog ==

= 1.13.3 - 2014-12-01 =
* Improved: [shipping] variable was divided into [shipping_cost] and [shipping_method].
* Improved: Order item is now passed to `jigoshop_order_product_title` filter as 3rd argument. Thanks to @ermx
* Fix: Default emails now install properly on jigoshop update.
* Fix: Urls for dummy products.

= 1.13.2 - 2014-11-26 =
* Improved: Additional email variables `[total_tax]`, `[is_local_pickup]`, `[checkout_url]`, `[payment_method]`.
* Improved: Coupons now can be added or removed in checkout.
* Fix: Some html errors.
* Fix: Typo in default email
* Fix: Removed ex. tax label from subtotal in cart when shop admin decide to show prices without tax.
* Fix: Generate default emails button no longer generates warning.

= 1.13.1 - 2014-11-21 =
* Fix: Warnings in email module.
* Fix: Email templates now installs properly after jigoshop activation.

= 1.13 - 2014-11-21 =
* New: Ability to select whether to show prices with or without tax in cart.
* New: Ability to select user when creating new order manually in admin panel.
* New: Brand, GTIN, MPN fields for product.
* New: Shortcode product_tag.
* Improved: Disabled options in select now are hidden.
* Improved: Stock status shows ':' instead of dash.
* Improved: Sku variable is no longer showing in emails when sku is disabled.
* Improved: Shop administrator is able to not set price for variables.
* Improved: Shop emails are now customizable.
* Fix: Disappearing items from cart after login.

= 1.12.3 - 2014-10-27 =
* Improved: Automatic plugin update mechanism uses as low HTTP requests as possible.

= 1.12.2 - 2014-10-17 =
* Improved: Show plugin updates even when licence is not activated.
* Improved: Checking for updates is now 5 times faster.
* Fix: Changing state or country in checkout will properly trigger recalculation of cart taxes.
* Fix: Countries with not defined states, will properly shown as selected.
* Fix: Email notifications about product stock status can be properly disabled.
* Fix: JS problems on admin user profile page.
* Fix: Date on Reports Page can be properly picked.
* Fix: Redirecting from my account pages will no longer generate errors.
* Fix: Worldpay payment page now will be correctly loaded.
* Fix: Coupon warnings about division by 0.
* Fix: Adding new tax will show properly buttons which are assigned to country/state select.

= 1.12.1 - 2014-10-07 =
* Fix: Phone number in order email.
* Fix: Updated polish translation.
* Fix: Triggering `jigoshop.cart.update` now properly passes data.
* Fix: `jigoshop.cart.update` gets called before data is updated (or removed).
* Fix: Pass properly rounded discount values to PayPal.

= 1.12 - 2014-09-30 =
* New: User fields in user's profile.
* New: Support for disabled elements in admin settings.
* New: `Jigoshop_Options` new methods `get`, `set`, `add`, `delete` and `exists` - replaces ones with `_option` in the name.
* New: `jigoshop_enqueue_settings_scripts` action.
* New: `jigoshop.cart.update` on `.form-cart-items` after Ajax cart update.
* New: Cart quantity changes are immediately saved!
* New: Ability to select exact hour when sales starts and ends.
* New: WordPress memory limit check.
* New: Actions in each product panel for additional fields.
* New: Customer email notification after placing order when getting to on-hold status.
* Improved: Emails: shop details header, tax number in company details.
* Improved: Formatted code of jigoshop emails.
* Improved: Removed invalid email about processing order when going to on-hold status.
* Improved: Grouped products are checking parent group for sales dates.
* Improved: Shipping calculator always works with data set in My Account page.
* Improved: Products do not need to have tax classes selected.
* Fix: Variation SKU fetching.
* Fix: Order total minimum requirement for coupons.
* Fix: Shipping taxes are calculated for each applicable tax class.
* Fix: Proper calculation of taxes after coupons have been applied.
* Fix: Memory checking when provided lowercase.
* Fix: Monthly report and reports are showing the same data now.

= 1.11.9 - 2014-09-16 =
* Fix: EU VAT handling for same country orders.
* Fix: Tax for shipping is properly added on new orders using PayPal standard.

= 1.11.8 - 2014-09-12 =
* Fix: Paying for pending orders.
* Fix: Proper checkbox saving in admin panel.
* Fix: Adding variations JavaScripts.
* Fix: Notice when related products are enabled.
* Improved: `get_sku()` method on product variation object will now return variation SKU (if not available - product SKU).
* Improved: Added `number` option type.
* Improved: Replaced `range` items with `number` ones - better to use (visible values).

= 1.11.7 - 2014-09-09 =
* Fix: Select2 errors on product, order and coupon pages.
* Fix: Notice about `WYSIJA` constant.
* Fix: Re-add `jigoshop_form` class for Groups integration plugin.
* Fix: Clearing multiple select fields.
* Improved: Add "Allow URL fopen" to System Info page.
* Improved: Handling of Jigoshop Settings scripts.

= 1.11.6 - 2014-09-05 =
* Fix: PayPal invalid amounts.
* Fix: JWOS with WordPress 4.0 compatibility.
* Fix: Admin styles with WordPress 4.0
* Improved: Preventing from displaying the same data twice with `jigoshop_get_formatted_variation()` function.
* Improved: Flush rewrite rules as earlier update introduced small changes.
* Improved: Update checkout on load to ensure tax is properly calculated.

= 1.11.5 - 2014-09-04 =
* Fix: Warning when free shipping is selected.
* Fix: Free shipping method will correctly calculate minimum value to let it work.
* Improved: Saving order tax information into database.
* Improved: Added short open tag check to System Info page.
* Improved: Reformatted write panels admin file with removal of deprecated classes and functions.
* Improved: Link to support in footer of every Jigoshop page.

= 1.11.4 - 2014-08-28 =
* Fix: Unknown postcode rules are not invalid.
* Fix: Permalink options now works properly.
* Fix: Remove all items sorting, it leaves only categories ordering working.
* Improved: Strengthened postcode validation to exact match.
* Improved: Compatibility with WooCommerce themes not created by WooThemes.
* Improved: Update prettyPhoto to 3.1.5

= 1.11.3 - 2014-08-21 =
* Fix: Problems with styling of posts not on Jigoshop pages.
* Fix: Warnings when adding or editing product attributes.
* Fix: Problems with line breaks inside tags on checkout.
* Fix: Redirection problems when using checkout without JavaScript.
* Improved: Ability to select whether to use billing or shipping for taxing. Thanks for the tip @elitistdogg

= 1.11.2 - 2014-08-19 =
* Fix: Removed duplicated "Settings" link in plugins panel.
* Fix: Proper handling of errors on checkout.
* Fix: Proper total tax fetching. Thanks to @newash
* Fix: Double `product_type` parameter when editing categories and tags from admin.
* Fix: Overlapping Y-axis values in Jigoshop Report.
* Improved: Hide shipping and tax from cart if customer country is not set.
* Improved: Jigoshop toolbar items based on user capabilities.
* Improved: `jigoshop_get_order` filter also gets `$this` as 3rd parameter. Thanks to @newash

= 1.11.1- 2014-08-07 =
* Fix: Proper selecting of shipping rate.
* Fix: Proper grouped and variable product price displaying.
* Fix: Removing price from products is now available again.
* Improved: Ability to set when messages and error disappear.

= 1.11 - 2014-08-06 =
* New: Compatibility with WooThemes themes.
* New: Check for PHP accelerators as they might cause problems.
* New: Support for variable products in Price Filter widget.
* New: `jigoshop_report_widgets` action to add custom report boxes.
* New: `jQuery.payment()` function to ease payment redirection.
* New: Ability to always select "All of" in country dropdown.
* New: Replaced old ThickBox with WordPress Media Gallery.
* Improved: "Edit Product Category" and "Edit Product Tag" admin bar links now works properly.
* Improved: Better message and error disappearing times.
* Fix: Invalid formatting of shipping dropdown.
* Fix: Displaying multiple select fields.
* Fix: Properly calculate tax for shipping.
* Fix: Licence validator now checks if plugin URL is correct.

= 1.10.6 - 2014-07-30 =
* Fix: Security issue on comments feed.
* Fix: Add obeying validate postcode setting in JavaScript validation.
* Fix: Validating of GB postcodes.
* Fix: Properly check EU VAT for billing country.

= 1.10.5 - 2014-07-28 =
* Fix: States changing in Edit Address and Cart pages.
* Fix: Small typo in `my_account` shortcode template when user is not logged in.
* Fix: Page jumping when messages are shown.
* Improved: Jigoshop widgets reformatting.
* Improved: Add ability to Jigoshop widgets to work together (i.e. Price Filter and Search).

= 1.10.4 - 2014-07-24 =
* Improved: Reformat and fix states changing script.
* Improved: Ability to check if current page is payment confirmation, "Thank you" and "My account" page.
* Improved: Edit address shortcode now has back button.
* Improved: Shortened and simplified JavaScript for checkout.
* Fix: Postcode validation.
* Fix: After address save page renders properly. Thanks to Jeff Grossman
* Fix: Product Categories widget properly handles showing counts option.

= 1.10.3 - 2014-07-21 =
* Fix: Memory checking typo.
* Fix: Stock status checking for products.
* Fix: PHP pre-5.3 main file compatible (for proper PHP version checking).
* Fix: Invalid shortcode attribute managing in add to cart shortcode (thanks to Josh Virkler).
* Improved: Memory checking error message is just a warning.

= 1.10.2 - 2014-07-21 =
* Fix: Memory check is not a fatal error anymore - plugin will continue to work.
* Fix: As memory is not a fatal error - required memory is downgraded to 64 MB.

= 1.10.1 - 2014-07-21 =
* Fix: Memory checking for some users. Thanks to freyaluna for finding it.

= 1.10 - 2014-07-21 =
* New: `jigoshop_countries::get_countries()` function - returns alphabetically sorted list of translated country names.
* New: `jigoshop_countries::has_country()` and `jigoshop_countries::has_state()` methods introduced.
* New: `jigoshop_render()` and `jigoshop_render_result()` functions - easy templates rendering.
* New: `jigoshop_product_list` shortcode.
* New: Check for minimum, required PHP version.
* New: Check for minimum, required WordPress version.
* New: Check for minimum, required memory size - currently 128 MB.
* New: Support and Docs links in Plugins list.
* New: Ability to define default customer country.
* New: Introduce Jigoshop menu to WordPress admin toolbar.
* New: `jigoshop_remove_script()` function and its support in JWOS.
* New: Provinces for Poland and Philippines. Thanks to Kristoffer Cheng
* New: `JIGOSHOP_URL` constant - for easy access to Jigoshop files from other plugins.
* New: `jigoshop_is_minimum_version()` function - for checking if Jigoshop matches at least specified version using `version_compare()` PHP function.
* New: `jigoshop_add_required_version_notice()`function - for adding preformatted notice when plugin requires higher version of Jigoshop.
* Improved: JWOS now supports PHP 5.3 with `short_open_tag` disabled.
* Improved: Reformatted main Jigoshop file.
* Improved: Proper variation price sanitization.
* Improved: Removed use of deprecated methods from Jigoshop cart, introduced payment methods template.
* Improved: Extracted account templates from shortcodes - now users can override them in their templates!
* Improved: PayPal decimal errors for HUF, JPN and TWD currencies. Thanks to newash
* Improved: Default look of checkout form.
* Improved: "Tel" and "County" are now "Phone" and "Province" in admin panel.
* Improved: Updating order status is at the end of saving the order. Thanks to newash
* Improved: Admin settings uses JWOS now.
* Improved: Jigoshop class information functions returns values from constants as they should.
* Improved: Reformatted WorldPay gateway class.
* Improved: New Google Analytics code using Universal Analytics. Thanks to Ragnar Karlsson for a tip!
* Fix: Warning when saving product meta.
* Fix: Removed Wordpress TwentyFourteen theme fix as it causes problems with real shops.
* Fix: HTTPS warnings for external fonts removed.
* Fix: Strict standards warning on edit address page.
* Fix: Reports chart properly scales Y-Axis ticks.

= 1.9.6 - 2014.06.11 =
* New: Add version constant to `jigoshop` class for easy checking in plugins.
* New: Javascript triggers `jigoshop.update_checkout` on body element when `update_checkout()` method is called. Useful for payment gateways.
* Fix: Properly convert asset URLs to directory paths in JWOS.
* Fix: Tax warnings when country is with states and no taxes are available for it.
* Fix: Properly include ThickBox for uploads.
* Fix: Taxes are applied to billing country instead of shipping country if product is shippable.
* Fix: Checking if specific countries are set properly before updating tax classes.
* Improved: Jigoshop styles on TwentyFourteen.
* Improved: Better recognition of SSL usage.
* Improved: Better recognition of available country and state on checkout.
* Improved: Review order template fixes.
* Improved: Jigoshop Countries class - now it has `get_country($country_code)` and `get_state($country_code, $state_code)` functions.
* Improved: Check if there is shipping and payment method before displaying it in orders list.
* Improved: Reformat of PayPal Standard gateway.
* Improved: Introduced `JIGOSHOP_VERSION` and `JIGOSHOP_DB_VERSION` (old `JIGOSHOP_VERSION`) constants - use them instead of jigoshop::jigoshop_version() function.
* Improved: Removed deprecated qualifier on product's `get_title()` function and updated the function.

= 1.9.5 - 2014.05.28 =
* Fix: Variation data disappearing in emails.
* Fix: Saving taxes.
* Improve: Hide infinite availability for variable products.
* Improve: Code cleaning.
* Improve: Update POT file for translations.

= 1.9.4 - 2014-05-26 =
* New: Checking for valid variation price (with proper error message).
* Fix: Add BlockUI JavaScript in header for proper PayPal Standard support.
* Fix: Minor code updates to PayPal Standard gateway.
* Fix: Saving options in specific circumstances.
* Fix: Warnings when no tax defined.
* Fix: Proper checking for tax state correctness. Thanks to Karl Engstrom
* Fix: Update variation formatting to use built-in values and selections as well.
* Improve: Minor update of PayPal gateway.

= 1.9.3.1 - 2014-05-18 =
* Fix: Quick fix for invalid use of `jigoshop_get_formatted_variation()`

= 1.9.3 - 2014-05-15 =
* New: "New" order status.
* Fix: First activation warnings.
* Fix: Taxes are calculated even when not set for base country.
* Fix: Database version checking on PHP 5.5.
* Fix: Ability to add taxes to single state. Thanks to elitistdogg!
* Fix: Order email warnings.
* Fix: Properly display variation details. Thanks to Jared Weiss!
* Fix: `jigoshop_localize_script()` now works properly.
* Improve: Remove lots of backwards compatibility code from Jigoshop_Options class. WARNING: Old plugins may stop working!
* Improve: Use `jigoshop_localize_script()` in order to avoid problems with external jQuery versions.

= 1.9.2 - 2014-05-13 =
* New: System Info icon.
* Improved: Code formatting of settings and tax classes.
* Fix: Saving multiple taxes - fixes issue where some states were not saved thus resulting in 0% tax.
* Fix: Calculating taxes in cart and checkout
* Fix: Properly displaying tax values when coupons are used and tax is applied after coupons.

= 1.9.1 - 2014-05-12 =
* Fix: Checking for shipping and billing state and country correctness.

= 1.9 - 2014-05-12 =
* New: Jigoshop Web Optimisation System - ability to combine CSS and JavaScript into a single files.
* New: Brand new look of Jigoshop Dashboard.
* New: API for localizing JavaScript files in order to work with Jigoshop Web Optimisation extension.
* New: Multipart form in admin settings to use with user-defined input fields. Thanks to Andrei Neamtu
* New: Ability to select all countries in tax class.
* New: More detailed information about low in stock variable products.
* New: Validating if customer shipping country and state is allowed for taxing purposes.
* Improved: Updated "Useful links" section.
* Improved: Load jQuery UI Sortable plugin by default.
* Improved: Load tim of admin dashboard for shops with many items.
* Fix: Specific countries with tax caused orders to pass without adding required tax cost. Thanks to Naomi Taylor
* Fix: Strict standards on `attribute_label()` method in `jigoshop_product` class.
* Fix: Licence validator now properly deactivates licences.
* Fix: Calculating taxes is always performed.
* Fix: `jigoshop::plugin_url()` now returns proper URL.

= 1.8.6 - 2014-04-24 =
* New: Checking for product type when loading products on sale.
* New: jQuery `jigoshop_add_variation` action after adding new variation in admin panel.
* Fix: Password type field on Checkout. Thanks to jlalunz
* Fix: Different user meta used for `address 2` line in checkout and `my_account` shortcode. Thanks to robselway

= 1.8.5 - 2014-04-15 =
* New: Checking if widgets has titles - otherwise skipping displaying them. Thanks to Stephen Cronin
* New: Action `jigoshop_user_edit_address` after address edition. Thanks to robselway
* Fix: Product statuses now matches these from Google Feed.

= 1.8.4 - 2014-04-01 =
* Fix: Category dropdown is now single selection. Thanks to Riccardo F
* Fix: Closing tags in `jigoshop_customer` class are now correctly included. Thanks to robselway

= 1.8.3 - 2014-03-31 =
* New: Orders in admin panel can be filtered by date
* Tweak: Updated licences
* Tweak: Moved country-states javascript to separate file
* Fix: Categories widget can redirect to "All" page too

= 1.8.2 - 2014-03-28 =
* New: Jigoshop manages its assets through new API `jigoshop_add_style()` and `jigoshop_add_script()`
* Tweak: PayPal landing page will now show Credit Card entry fields by default
* Tweak: Remove duplicated values from System Info
* Tweak: Redirect after error while adding product into cart
* Fix: Use calculated order price when using PayPal Standard gateway
* Fix: Force 0.01 charge on free orders at PayPal to allow it to process through PayPal
* Fix: Jigoshop categories widget will again use a pop-up select when 'dropdown' setting is enabled
* Fix: Resolve problems with Ukrainian translation
* Languages: New Chinese Taiwan translation courtesy of Eason Chen
* Languages: Updated Danish translation courtesy of Tine Kristensen
* Languages: Updated German translation courtesy of Andy Jordan
* Languages: Updated Polish translation courtesy of OptArt

= 1.8.1 - 2014-01-03 =
* Tweak: Variations that are all priced the same will no longer show the secondary price when selected
* Tweak: Free Shipping module only activates on totals after applied coupon amounts
* Tweak: Provide total quantity products sold for Reports
* Fix: Reports include orders from beginning day of date range
* Fix: After coupon removal on Cart, totals recalcualted
* Fix: Products on sale shortcode now uses default loop-shop template to allow pagination
* Fix: Products on sale shortcode won't show all products if non actually on sale
* Fix: Clicks on Checkout's 'ship to billing' will force a recalc for selected states and taxes
* Fix: Numerous fixes for PHP Strict warnings
* Languages: Updated pot file for translators
* Languages: Updated Ukranian translation courtesy of Anatolii Sakhnik
* Languages: Updated Croatian translation courtesy of Ivica Delic
* Languages: Updated German translation courtesy of Andy Jordan
* Languages: New Slovenian translation courtesy of David Bratuša

= 1.8 - 2013-10-10 =
* New: WorldPay payment gateway added to Jigoshop core
* New: Settings->General->`Complete processing Orders` option for 'processing' orders older than 30 days
* New: Implement Jigoshop Request API for extensions and gateways
* New: Javascript Checkout field validation to enhance payment conversion. Shows correct and incorrect fields.
	* Orders won't be placed until all Checkout fields required data are input and validated
* Tweak: Revamped all Jigoshop frontend javascript for modularity and efficiency
	* all Jigoshop javascript loads in footer for improved performance
* Tweak: Updated several external javascript libraries (jQuery blockUI, select2)
* Tweak: Removed large jQuery UI library from front end loading, loads required bits as needed (Price Filter)
* Tweak: Jigoshop now only loads one CSS file from all internal sources for efficiency
* Tweak: Add a codeblock option type in the settings for extensions to use internally
* Tweak: Combine Edit Order variation attributes with product addons extension in one panel for Orders
* Tweak: Add some filters for Jigoshop WPML extension to allow more translated items
* Tweak: Jigoshop Reports pie chart cleanup with separate legend that won't over write charts
* Tweak: Jigoshop Reports pie chart products now show with 5% share
* Fix: Jigoshop Reports pie chart for 'Most Sold' per period now accurately reflects top products sold
* Fix: Repair Google Analytics function for tracking code to load in header where it's required
* Fix: Repair Google eCommerce Product tracking for Thank You page
* Fix: Unpaid 'on-hold' orders from cash or cheque gateways will no longer be overwritten with another order
* Fix: FuturePay gateway will not be selectable on the Checkout for Orders over $500.00 (current credit limit)
* Fix: Test to ensure PayPal payment amounts and addresses matches initially submitted order as a security check
* Fix: Remove filter that was overriding Contact Form 7 or other mail extensions for 'From' name on emails
* Fix: Variations that use Parent Product for stock tracking, Parent will now reduce stock upon order payment
* Languages: Updated .pot file for translators
* Languages: Updated Brazilian translation courtesy of Raphael Suzuki
* Languages: Updated Czech translation courtesy of Jaroslav Ondra
