## Changelog

* 1.17.5:
    * Improved: Optimized scripts.
    * Improved: Replaced old screenshots.
    * Fix: Email download link.
* 1.17.4 - 2015.04.28:
    * Improved: Possibility to show reports for last 30 days and for all orders.
    * Improved: Possibility to show reports for specified order statuses.
    * Improved: Possibility to sort available variations on products page.
    * Improved: Product reports show sold quantity.
    * Improved: Category reports show horizontal lines.
    * Improved: Reports page displays properly values.
    * Improved: Custom themes compatibility.
    * Fix: JS error in Checkout page.
    * Fix: Hidden login box in my account page.
    * Fix: `[order_items_table]` shortcode.
* 1.17.3 - 2015-04-20:
    * Improved: Reports page.
    * Fix: Scripts and compatibility with JRTO.
    * Fix: Doubled tip messages.
* 1.17.2 - 2015-04-18:
    * Fix: Scripts and compatibility with JRTO.
* 1.17.1 - 2015-04-17:
    * Improved: COD gateway allows to select status which should be set to order. 
    * Fix: Scripts and compatibility with JRTO.
    * Fix: Notices, warnings and error on reports page.
* 1.17 - 2015-04-16:
    * New: Improved Reports page.
    * New: Improved System Info page.
    * New: Jigoshop Extensions page.
    * New: Remove Jigoshop Web Optimization System in favor of new Jigoshop Round Trip Optimizer extension.
    * Improved: Ability to set handling fee for Local Pickup.
    * Improved: Reformatted and checked Free Shipping and Local Pickup shipping methods.
    * Improved: Display "Out of stock" for variable products without any available variation.
    * Improved: PHP Execution Time in System Info page.
    * Improved: Cart items check before displaying.
    * Improved: All out of stock products, can be properly removed from shop page. 
    * Improved: Invalid license key can be properly deactivated.
    * Improved: Updated www.jigoshop.com links.
    * Fixed: "Apply coupon" button in checkout no longer places the order.
* 1.16.1 - 2015-03-18:
    * Improved: Ability to enable/disable HTML emails.
    * Improved: `[order_items_table]` code for HTML order items table. Used by default now.
    * Fixed: Improper items formatting in new HTML emails.
* 1.16 - 2015-03-16:
    * New: Completely new default HTML emails.
    * New: "Waiting for payment" transaction status for Cash on Delivery, Bank Transfer and Cheque payments.
    * New: More actions for emails - now available all actions for both customers and admins.
    * New: Default email templates are now translatable.
    * New: Order status variable for emails - included by default in admin new order email.
    * New: Better support for Cash on Delivery, Bank Transfer and Cheque payment methods in emails.
    * New: Better support for Local Pickup shipping methods in emails.
    * New: Ability to add multiple fields to product category (only text or number). Thanks to newash!
    * Improved: Proper HTML for sale prices `<del>Old price</del><ins>New price</ins>` with `<span class="discount">Discount</span>` if applicable.
    * Improved: Trimming spaces from memory values before checking.
    * Improved: Remove ID from Jigoshop nonce fields.
    * Improved: More variables for your emails!
    * Improved: Nice icon for Jigoshop Emails.
    * Improved: Link to "Manage licences" page for not activated products.
    * Improved: Properly size and align product thumbnails on product list page.
    * Improved: Jigoshop Reports page shows properly product value with taxes.
    * Improved: Jigoshop Reports page indicates that Total Sales are with discounts, taxes and shipping included.
    * Improved: Jigoshop images were optimized. Thanks to @Dade88 (#1036)
    * Fix: Selected values issue on checkout and edit address pages. Thanks to @ipatenco!
    * Fix: Show "Password changed" message after successful change.
    * Fix: Warning for network installations of Jigoshop.
    * Fix: Change name of SplClassLoader to avoid issues with other plugins/themes.
    * Fix: "Order failed" untranslatable text is not translatable!
    * Fix: Bigger headers in Jigoshop Settings.
    * Fix: Double loading of `frontend.css` when custom theme used.
    * Fix: Behaviour of product thumbnails.
* 1.15.5 - 2015-02-09:
    * Improved: JavaScript action is triggered after variation is shown: `jigoshop.variation.show` on `div.single_variation`.
    * Improved: Better loading of checkout JavaScript files.
    * Improved: cURL checking in System Info page.
    * Fix: Users can now properly select default tax classes and default taxing status for new products.
* 1.15.4 - 2015-01-12:
    * Improved: [is_bank_transfer], [bank_info], [billing_euvatno] and [all_tax_classes] variables in order emails. Thanks to newash!
    * Improved: Email shortcode parser to work with new variable.
		* Fix: Bulk edit now can change price to not announced. Thanks to newash!
* 1.15.3 - 2015-01-06:
    * Fix: Missing vendor files.
    * Fix: JWOS for ssl websites.
* 1.15.2 - 2014-12-23:
    * Fix: JS error in tinymce shortcodes.
    * Improved: Rewrote favicon cart count module.
* 1.15.1 - 2014-12-19:
    * Fix: Fatal error on cart page.
    * Fix: JS error in favicon cart notification.
* 1.15 - 2014-12-18:
    * New: Favicon cart count notification
    * New: Easy add Jigoshop shortcodes in TinyMCE
    * Improved: Downloadable products links are showing as hyperlinks.
    * Fix: Download links now shows only in processing or completed order email notifications.
    * Fix: Reset pending Orders and Complete processing Orders will no longer send emails.
    * Fix: Video played via prettyPhoto now loads properly.
    * Fix: `shipping_dropdown.php` and `payment_methods.php` now can be replaced in theme files.
    * Fix: Product total price after ajax update in cart now is calculated properly.
* 1.14 - 2014-12-11:
    * New: Used Coupon column on Orders page.
    * New: Email variables.
    * New: Draggable categories.
    * New: Option in general tab, 'Use custom product category order'.
    * Improved: Report is generated based on completed orders.
    * Improved: Email templates are showing now customer note.
    * Improved: Recent orders in admin dashboard shows order number instead of order id.
    * Fixed: Creating orders in `jigoshop_orders` class. Thanks to @newash for pointing out.
    * Fixed: Allow to install emails only on Jigoshop Settings page in admin panel. Thanks to @newash.
    * Fixed: Load emails data on admin only. Thanks to @newash.
* 1.13.3 - 2014-12-01:
    * Improved: [shipping] variable was divided into [shipping_cost] and [shipping_method].
    * Improved: Order item is now passed to `jigoshop_order_product_title` filter as 3rd argument. Thanks to @ermx
    * Fix: Default emails now install properly on jigoshop update.
    * Fix: Urls for dummy products.
* 1.13.2 - 2014-11-26:
    * Improved: Additional email variables `[total_tax]`, `[is_local_pickup]`, `[checkout_url]`, `[payment_method]`.
    * Improved: Coupons now can be added or removed in checkout.
    * Fix: Some html errors.
    * Fix: Typo in default email
    * Fix: Removed ex. tax label from subtotal in cart when shop admin decide to show prices without tax.
    * Fix: Generate default emails button no longer generates warning.
* 1.13.1 - 2014-11-21:
    * Fix: Warnings in email module. 
    * Fix: Email templates now installs properly after jigoshop activation.
* 1.13 - 2014-11-21:
    * New: Ability to select whether to show prices with or without tax in cart.
    * New: Ability to select user when creating new order manually in admin panel.
    * New: Brand, GTIN, MPN fields for product.
    * New: Shortcode `product_tag`.
    * Improved: Disabled options in select now are hidden.
    * Improved: Stock status shows ':' instead of dash.
    * Improved: Sku variable is no longer showing in emails when sku is disabled.
    * Improved: Shop administrator is able to not set price for variables.
    * Improved: Shop emails are now customizable.
    * Fix: Disappearing items from cart after login.
* 1.12.4 - 2014-11-10:
    * Improved: `jigoshop_remove_style()` with support in JWOS.
* 1.12.3 - 2014-10-27:
    * Improved: Automatic plugin update mechanism uses as low HTTP requests as possible.
* 1.12.2 - 2014-10-17:
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
* 1.12.1 - 2014-10-07:
    * Fix: Phone number in order email.
    * Fix: Updated polish translation.
    * Fix: Triggering `jigoshop.cart.update` now properly passes data.
    * Fix: `jigoshop.cart.update` gets called before data is updated (or removed). 
    * Fix: Pass properly rounded discount values to PayPal.
* 1.12 - 2014-09-30:
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
* 1.11.9 - 2014-09-16:
    * Fix: EU VAT handling for same country orders.
    * Fix: Tax for shipping is properly added on new orders using PayPal standard.
* 1.11.8 - 2014-09-12:
    * Fix: Paying for pending orders.
    * Fix: Proper checkbox saving in admin panel.
    * Fix: Adding variations JavaScripts.
    * Fix: Notice when related products are enabled.
    * Improved: `get_sku()` method on product variation object will now return variation SKU (if not available - product SKU).
    * Improved: Added `number` option type.
    * Improved: Replaced `range` items with `number` ones - better to use (visible values).
* 1.11.7 - 2014-09-09:
    * Fix: Select2 errors on product, order and coupon pages.
    * Fix: Notice about `WYSIJA` constant.
    * Fix: Re-add `jigoshop_form` class for Groups integration plugin.
    * Fix: Clearing multiple select fields.
    * Improved: Add "Allow URL fopen" to System Info page.
    * Improved: Handling of Jigoshop Settings scripts.
* 1.11.6 - 2014-09-05:
    * Fix: PayPal invalid amounts.
    * Fix: JWOS with WordPress 4.0 compatibility.
    * Fix: Admin styles with WordPress 4.0
    * Improved: Preventing from displaying the same data twice with `jigoshop_get_formatted_variation()` function.
    * Improved: Flush rewrite rules as earlier update introduced small changes.
    * Improved: Update checkout on load to ensure tax is properly calculated.
* 1.11.5 - 2014-09-04:
    * Fix: Warning when free shipping is selected.
    * Fix: Free shipping method will correctly calculate minimum value to let it work.
    * Improved: Saving order tax information into database.
    * Improved: Added short open tag check to System Info page.
    * Improved: Reformatted write panels admin file with removal of deprecated classes and functions.
    * Improved: Link to support in footer of every Jigoshop page.
* 1.11.4 - 2014-08-28:
    * Fix: Unknown postcode rules are not invalid.
    * Fix: Permalink options now works properly.
    * Fix: Remove all items sorting, it leaves only categories ordering working.
    * Improved: Strengthened postcode validation to exact match.
    * Improved: Compatibility with WooCommerce themes not created by WooThemes.
    * Improved: Update prettyPhoto to 3.1.5
* 1.11.3 - 2014-08-21:
    * Fix: Problems with styling of posts not on Jigoshop pages.
    * Fix: Warnings when adding or editing product attributes.
    * Fix: Problems with line breaks inside tags on checkout.
    * Fix: Redirection problems when using checkout without JavaScript.
    * Improved: Ability to select whether to use billing or shipping for taxing. Thanks for the tip @elitistdogg
* 1.11.2 - 2014-08-19:
    * Fix: Removed duplicated "Settings" link in plugins panel.
    * Fix: Proper handling of errors on checkout.
    * Fix: Proper total tax fetching. Thanks to @newash
    * Fix: Double `product_type` parameter when editing categories and tags from admin.
    * Fix: Overlapping Y-axis values in Jigoshop Report.
    * Improved: Hide shipping and tax from cart if customer country is not set.
    * Improved: Jigoshop toolbar items based on user capabilities.
    * Improved: `jigoshop_get_order` filter also gets `$this` as 3rd parameter. Thanks to @newash
* 1.11.1- 2014-08-07:
    * Fix: Proper selecting of shipping rate.
    * Fix: Proper grouped and variable product price displaying.
    * Fix: Removing price from products is now available again.
    * Improved: Ability to set when messages and error disappear.
* 1.11 - 2014-08-06:
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
* 1.10.6 - 2014-07-30:
    * Fix: Security issue on comments feed.
    * Fix: Add obeying validate postcode setting in JavaScript validation.
    * Fix: Validating of GB postcodes.
    * Fix: Properly check EU VAT for billing country.
* 1.10.5 - 2014-07-28:
    * Fix: States changing in Edit Address and Cart pages.
    * Fix: Small typo in `my_account` shortcode template when user is not logged in.
    * Fix: Page jumping when messages are shown.
    * Improved: Jigoshop widgets reformatting.
    * Improved: Add ability to Jigoshop widgets to work together (i.e. Price Filter and Search).
* 1.10.4 - 2014-07-24:
    * Improved: Reformat and fix states changing script.
    * Improved: Ability to check if current page is payment confirmation, "Thank you" and "My account" page.
    * Improved: Edit address shortcode now has back button.
    * Improved: Shortened and simplified JavaScript for checkout.
    * Fix: Postcode validation.
    * Fix: After address save page renders properly. Thanks to Jeff Grossman
    * Fix: Product Categories widget properly handles showing counts option.
* 1.10.3 - 2014-07-21:
    * Fix: Memory checking typo.
    * Fix: Stock status checking for products.
    * Fix: PHP pre-5.3 main file compatible (for proper PHP version checking).
    * Fix: Invalid shortcode attribute managing in add to cart shortcode (thanks to Josh Virkler).
    * Improved: Memory checking error message is just a warning.
* 1.10.2 - 2014-07-21:
    * Fix: Memory check is not a fatal error anymore - plugin will continue to work.
    * Fix: As memory is not a fatal error - required memory is downgraded to 64 MB.
* 1.10.1 - 2014-07-21:
    * Fix: Memory checking for some users. Thanks to freyaluna for finding it.
* 1.10 - 2014-07-21:
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
* 1.9.6 - 2014-06-11:
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
* 1.9.5 - 2014.05.28
    * Fix: Variation data disappearing in emails.
    * Fix: Saving taxes.
    * Improve: Hide infinite availability for variable products.
    * Improve: Code cleaning.
    * Improve: Update POT file for translations.
* 1.9.4 - 2014-05-26
    * New: Checking for valid variation price (with proper error message).
    * Fix: Add BlockUI JavaScript in header for proper PayPal Standard support.
    * Fix: Minor code updates to PayPal Standard gateway.
    * Fix: Saving options in specific circumstances.
    * Fix: Warnings when no tax defined.
    * Fix: Proper checking for tax state correctness. Thanks to Karl Engstrom
    * Fix: Update variation formatting to use built-in values and selections as well.
    * Improve: Minor update of PayPal gateway.
* 1.9.3.1 - 2014-05-18
    * Fix: Quick fix for invalid use of `jigoshop_get_formatted_variation()`
* 1.9.3 - 2014-05-15
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
* 1.9.2 - 2014-05-13
    * New: System Info icon.
    * Improved: Code formatting of settings and tax classes.
    * Fix: Saving multiple taxes - fixes issue where some states were not saved thus resulting in 0% tax.
    * Fix: Calculating taxes in cart and checkout
    * Fix: Properly displaying tax values when coupons are used and tax is applied after coupons.
* 1.9.1 - 2014-05-12
    * Fix: Checking for shipping and billing state and country correctness.
* 1.9 - 2014-05-12
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
