=== Jigoshop ===
Contributors: Jigowatt
Tags: ecommerce, wordpress ecommerce, store, shop, shopping, cart, checkout, widgets, reports, shipping, tax, paypal, jigowatt, shipping, inventory, stock, online, sell, sales, weights, dimensions, configurable, variable, downloadable, external, affiliate, download, virtual, physical
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=paypal@jigowatt.co.uk&item_name=Donation+for+Jigoshop
Requires at least: 3.2.1
Tested up to: 3.4.2
Stable tag: 1.4.2

A feature-packed eCommerce plugin built upon WordPress core functionality ensuring excellent performance and customizability.

== Description ==

Jigoshop, a WordPress eCommerce plugin developed by professionals with years of experience delivering online shops for global brands.

= SETUP IN MINUTES =

Complete your shop in minutes with physical and downloadable products or even services. Jigoshop provides you with the features necessary to set up an eCommerce web site lickety-split.

With the option to create a multitude of product types and apply detailed attributes customers can easily refine your catalog, ensuring they find what they're looking for in just a couple of clicks.

= PRODUCT TYPES =

* Downloadable or Virtual products
* Variable products (eg, offer `Size: S,M,L` for one product)
* Affiliate (External) products (ie, link your `Add to cart` button off-site)
* Grouped products

= DETAILED REPORTS =

Inside the custom dashboard you get sortable sales graphs, incoming order / review notifications as well as stats on your stores performance.

= MANAGE STOCK =

Manage your stock levels and customer orders easily. Jigoshop has been engineered to make the boring parts of eCommerce, well, less boring!

= EXTEND YOUR SHOP =

Extend Jigoshop with Payment Gateways, Shipping Methods, and various other plugins:

http://jigoshop.com/product-category/extensions/

Premium themes optimised for Jigoshop:

http://jigoshop.com/product-category/themes

= MORE INFO =

Built upon the WordPress core you get all the benefits of this global leading platform: free, easy to use, secure, highly customisable and with a great support community to hold your hand.

Find out more on our official Jigoshop website:

http://jigoshop.com

== Installation ==

= Requirements =

* WordPress 3.3 or greater
* PHP version 5.2.4 or greater
* MySQL version 5.0 or greater
* The mod_rewrite Apache module (for permalinks)
* Some payment gateways require fsockopen support (for IPN access)

= Installation =

1.  Download the Jigoshop plugin file
2.  Unzip the file into a folder to your computer
3.  Upload the `/jigoshop/` folder to the `/wp-content/plugins/` folder on your site
4.  Visit the plugins page in WordPress Admin to activate the Jigoshop plugin

You can also navigate to the <a href="http://forum.jigoshop.com/kb/getting-started/installation">more in-depth installation or upgrade</a> guides.

= Setting up Jigoshop =

Take a look through our <a href="http://forum.jigoshop.com/kb/" title="Jigoshop usage guide">Jigoshop usage guides</a> to help you setup Jigoshop for the first time.

== Frequently Asked Questions ==

= Will Jigoshop work with X theme? =

Jigoshop will in theory work with any theme, but of course, certain parts may need to be styled using CSS to make them match up. We've added default styling for Twenty Ten (the WordPress default theme) and we also offer <a href="http://jigoshop.com/product-category/themes/">premium themes optimised for Jigoshop</a>.

If you need a theme built, or have a theme that needs styling, <a href="http://jigowatt.co.uk/contact/">give us a shout</a> and we may be able to assist.

= Can I have Jigoshop in my language =

Jigoshop comes with a .po file and is localisation ready in over 10 languages.
You can also <a href="http://forum.jigoshop.com/kb/customize-jigoshop/languages">create your own translations</a> for Jigoshop.

= Which payment gateways do you have? =

Take a look through <a href="http://forum.jigoshop.com/kb/jigoshop-settings/payment-gateways">our list of payment gateways</a>. There are some free ones that are included with Jigoshop, and a couple which <a href="http://jigoshop.com/product-category/extensions/">can be purchased as extensions</a>.

= Will tax settings work in my country? =

Jigoshop has a flexible tax rule system which allows you to define tax rates per country - it should allow you to do what you want.

= I need hosting! =

You're in luck! We offer <a href="http://jigowatt.co.uk">optimised hosting packages</a> starting from 10 GBP per month.

= I need help! =

We have a <a href="http://forum.jigoshop.com" title="Jigoshop support forum">community forum</a> for getting help from other users.
However, if you want priority, dedicated support from Jigoshop staff, we do offer <a href="http://jigoshop.com/support/" title="Jigoshop Premium Support">premium support packages</a>.

== Screenshots ==

1. Jigoshop admin dashboard
2. Admin product edit page
3. Jigoshop homepage on a premium theme
4. Standard customer checkout screen

== Changelog ==
= 1.4.2 - 2012-10-15 =
* Tweak: template content wrapping for theme twentytwelve
* Tweak: Updated Croatian language files courtesy of Ivica Delic
* Tweak: Added new WordPress roles and capabilities to Shop management for extensions to use
* Tweak: provide code filter for changing text of 'Demo Store' banner
* Fix: Coupon Codes for front end Cart use are now lowercase only based on the Coupon Title.  Multiple words are hyphenated.
* Fix: Coupon type Product % Discount no longer requires attached products, allows coupon categories to function
* Fix: When javascript is disabled state selectors in checkout wouldn't replace text ones
* Fix: Previous signed up customers state & country were still set to the base default state and country
* Fix: Coupons using cart min and max totals are now checked when cart item quantities are adjusted
* Fix: Saving new or updated external products now properly handles stock status, out of stock no longer displayed

= 1.4.1 - 2012-09-28 =
* Fix: Adding variations to cart
* Fix: Adding grouped products to cart

= 1.4 - 2012-09-27 =
* New: Additions to Core Shipping for new Premium Shipping extension available with release of Jigoshop 1.4
* Tweak: The DIBS payment gateway has been removed from the Jigoshop core.  It is updated with a number of enhancements and freely available as a separate plugin here:
	https://github.com/bazooka/jigoshop-dibs
* Tweak: Table Rates Shipping extension will require an update to be compatible with Jigoshop 1.4.  Download from your account and install first.
* Fix: Cart and Checkout out of base state tax calcs now work for 'catalog prices include tax'
* Fix: Allow product sale prices to use percentages instead of numbers only
* Fix: Checkout 'shipping address' showing HTML code on certain sites resolved
* Fix: Add customization to PayPal gateway for extension Jigoshop Product Addons - additional costs per product now shown to PayPal
* Fix: Tax classes are now saved with tax rules
* Fix: Tax charged to customers outside of tax country
* Fix: Tax calculations not working on configurations with totals including tax, catalogue includes tax

= 1.3.3 - 2012-09-06 =
* Tweak: improve variations out of stock display for backorders allowed
* Fix: Ensure taxes are calculated for Shipping when Shipping includes tax on the Cart and Checkout
* Fix: Allow Settings for Shipping and Payment Gateways to save for languages other than English
* Fix: repair 'update_checkout' javascript preventing 3rd party shipping extensions from bring back shipping rates
* Fix: repair 'Reports' to show completed Sales only and New Customers in the given time period.

= 1.3.2 - 2012-08-29 =
* Tweak: updated Spanish translations for v1.3 courtesy - Jorge Figueroa
* Tweak: Allow variable products 'add to cart' when parent product is Search only
* Fix: Danish translation now works thanks to @IvicaDelic
* Fix: Order Tracking page now shows correct order status completion time
* Fix: Bank Transfer gateway Account Holder value will now appear in emails
* Fix: The word 'Array' no longer appears on products in emails
* Fix: uninstalling Jigoshop will no longer produce a fatal error
* Fix: Installing Jigoshop will notify if unable to create needed database tables
* Fix: Allow Chrome to process floating point numbers on Product Sale Price and Weights with 'step = any'
* Fix: Cart Totals display for Firefox
* Fix: Shipping Tab in the Settings is now properly translated and shipping modules will install for all languages

= 1.3.1 - 2012-08-17 =
* Tweak: updated German translations for v1.3 courtesy @deckerweb
* Tweak: Minor translation string updates for translators
* Fix: Translations for the Jigoshop Settings will now appear in the Admin
* Fix: Floating point numbers can now be entered for Product weights and dimensions when Editing a product
* Fix: A number of minor PHP warnings resolved

= 1.3 - 2012-08-14 =
* New: Redesigned settings pages using the WordPress Settings API
	* new settings types including checkboxes, radio buttons, range sliders
	* each settings tab saves independently of other tabs
	* searchable pop-up selects for longer option lists
	* Redesigned Jigoshop Options simplify extension, payment gateway, and shipping method development
* New: Coupon features
	* Removed from settings, now a WordPress custom post type with a submenu under Jigoshop Menu
	* Show coupon input field on checkout page
	* Allow coupons to be removed from cart
	* Free shipping option
	* Limit amount of times coupon can be used, & show a counter
	* Min & max order subtotal
	* Specify products to allow / deny
	* Specify product categories to allow / deny
	* Specify payment methods to allow
* New: Reports
	* Show reports within a custom timeframe
	* List of most sales generated from products
	* List of most quantity sold from products
	* Total customers
	* Total orders
	* Total sales
* New: Category images
	* Add an image to your product categories
* New: Beta Tester updater
	* Enabling this checkbox in the settings will allow beta versions to appear in the WP Plugin Manager
	* Allows Jigoshop Beta Testers to update directly to a beta version
* New: Product on Sale shortcode
* New: Force users to login before downloading a purchased product
* New: Mark 1 month or older orders from 'pending' to 'on-hold' with an option in the Settings to enable
* New: Select multiple countries and states for tax rules
* New: Support for WordPress' admin classic theme
* New: Include dummy product .xml file for WordPress importer for testing and demonstration
* Tweak: Add shipping method to emails
* Tweak: Shop address is used for Shipping address for local_pickup in notification emails
* Tweak: Allow default tax classes to be translated
* Tweak: UI Sortable helper on attributes now has no border radius
* Tweak: Pages & CPT's now use menu_position. Fixes array key duplication
* Tweak: Emails are now utf-8 compatible
* Tweak: Subtotals and Shipping can now display either with or without tax on both the Cart and Checkout
* Tweak: Cart widget now allows user defined text to display on the Go to Cart/Checkout buttons
* Tweak: Provide easier means of altering Jigoshop frontend styles
	* create 'jigoshop' folder in theme folder, create style.css, add styles to over-ride Jigoshop frontend.css
	* both Jigoshop frontend.css and new style.css can be used together, just change a few things, no .less required
* Fix: Star Ratings width css calculation now works for Recent Reviews widget
* Fix: Paypal gateway now sends shipping address info instead of billing when setting enabled
* Fix: When uninstalling, will now only delete Jigoshop created pages instead of pages specified in settings
* Fix: Variable products now have proper titles, not "#xxx: Child Variaton" anymore
* Fix: Edit Address on My Account page now shows correct saved state
* Fix: Local Pickup shipping always charges Shop Base Country and State tax
* Fix: Backorder notification and other emails now show variation sku's
* Fix: When products are on backorder, customers are now emailed notification if product is set for 'notify'
* Fix: Coupons applied after taxes now works as expected
* Fix: Manual Order creation Calculate Totals now works

= 1.2.3 - 2012-05-28 =
* New: Add filters for new Jigoshop Product Addons extension
* New: Add CSS classes to product attributes on variations for the front end to allow special effects
* New: Personalized products can have a character limit
* Tweak: Each Product's variations can now be personalized
* Tweak: Enable product categories widget showing 'current-cat' CSS class on single products
* Tweak: Set the Shop Redirect page on fresh install to the base Shop page
* Fix: Internationalize comment/review dates
* Fix: Incorrect order calculation when adding a new order
* Fix: Guest bypassing registration when forced
* Fix: Disable guest purchases when setting is enabled
* Fix: Cross Sells & Up Sells extension - users can define number of cross sells to show via theme functions.php

= 1.2.2 - 2012-04-10 =
* New: Danish translations
* Fix: When tax was included in catalogue price and the quantity of the product increased, the product price only reflected
  one of the items rather than the quantity selected.
* Fix: Calculable shipping which caused errors on plugins such as UPS, etc.

= 1.2.1 - 2012-04-09 =
* Fix: Repairs tax applied to only one item on the Cart.

= 1.2 - 2012-04-08 =
* New: Permalink options:
	* Allow `%product_cat%` for permalinks
	* Prepend product urls with category
	* Prepend product urls with shop base page
	* Set your own product category / tag slugs
* New: Personalized products - customers may add their own information to appear on purchased products
* New: WordPress Bulk and Quick editing of both stock levels and prices on the Admin Product List
* New: Options to disable or enable WordPress image cropping on Shop images
* New: Ability to show tax # and business address information on order emails
* New: $0.01 charge on Paypal if configured and total is free
* New: Allow shipping to be taxed on compounding tax classes (all around shipping tax enhancements)
* New: Resend download links by clicking "Email Invoice" on an order
* New: Shipping tax can be applied (if taxable) without applying taxes to products
* Tweak: Load css & js only when absolutely necessary, for both admin & frontend
* Tweak: Emails for orders can now be completely customized
	* Email customizations can be made through a plugin
* Tweak: Allow debugging to be turned on for calculable shipping services through wordpress debug
* Tweak: Allow EU shop to tax other EU countries
* Tweak: Automatically include 0% tax rate for customers outside of tax base
* Tweak: Calculate tax based on billing address when item is not shippable or shipping is disabled
* Tweak: Reduce total image assets size by 28kb
* Fix: Prevent duplicate variations on one product
* Fix: Disables this message when editing orders: "Are you sure you want to leave this page?"
* Fix: Creating new orders will automatically fill in the customer billing & shipping details (on save)
* Fix: Ability to restore orders which are trashed
* Fix: Compound tax display. Tax displays after subtotal all of the time
* Fix: Allow discounts to be applied before or after taxes
* Fix: Multiple tax classes bug (tax class 1 applied to product 1, and tax class 2 applied to product 2)
	* If both products were added to the cart, only 1 tax class was applied and the other wasn't
* Fix: Tax amounts disappearing on orders when changing the status of the order
* Fix: Hiding and showing products on Shop upon stock reduction/increase with 'hide out of stock products' setting
	* product variations are enabled or disabled when stock threshold is hit upon reduction/increase
* Fix: Multiple tax classes applied to all-of selections on countries
* Fix: Rounding error on taxes with price includes tax option
* Fix: Sale prices on products will no longer be higher than regular price
* Fix: Skrill gateway to allow variable products
* Fix: Variable and grouped products now show both regular and sale price on the Shop

= 1.1.1 - 2012-02-29 =
* Adds new admin setting for different redirection options after adding to the cart
* Adds support for more than 5 related products
* Adds settings link to plugins admin page
* Updates Norwegian translations
* Updates Hungarian translations
* Fixes install & upgrade support for multisite shops
* Fixes price filter widget
* Fixes symbol_code_space formatting in jigoshop_price - thanks @leewillis77
* Fixes twenty eleven for firefox users
* Fixes single product attributes in firefox & internet explorer
* Fixes variation attribute sort by slug
* Fixes display of products below the stock threshhold
* Fixes small bug with product has_attribute method
* Fixes incorrect number formatting in PayPal
* Fixes instance when adding an attribute without name & slug causes "Attribute already exists" error
* Fixes incorrect title for attribute name
* Fixes attribute column order
* Fixes instance when adding a title-less attribute would fill in the slug
* Removes unused variables from admin screen

= 1.1 - 2012-02-13 =
* New gateways:
	* Cash On Delivery [payment gateway]
	* Local Pickup [shipping method]
* New languages:
	* Italian
	* Norwegian
	* Polish
	* Portuguese
	* Portuguese Brazilian
	* Turkish
* New shortcode:
	* Products Search
* New settings:
	* Show stock amounts [to show or hide stock left in a product on front-end]
	* Allow guest purchases
	* Show login form
	* Allow registration
* Allow products to be sorted in wp-admin All Products page by:
	* Featured
	* Price
	* Visibility [a new column]
* You can now fully uninstall Jigoshop
	* Updated uninstall.php to remove all database entries, pages & roles Jigoshop created on install
* Fields now saved to wordpress user profiles on checkout when creating an account:
	* First name
	* Last name
* Add prefix `jigoshop_` to all Jigoshop setting pages
* Bank transfer information now included with 'processing order' emails
* Images tab on settings page now takes up less space
* Reduced total Jigoshop file size by at least 1mb
* Jigoshop.pot updated include latest strings and remove obsolete strings
* Remove `bulk actions` on variable products that didn't have any variations
* update admin order page to use pretty titles for payment & shipping
* update Spain provinces (thank @dimitryz)
* update readme.txt and readme.md files
* fix order-status' not being allowed for translation
* fix widgets:
	* featured product widget
	* top rated widget
	* recently viewed widget not showing products
	* on sale widget not displaying correct amount of products
* fix variable products not showing a 'grouped' tab in order to set a 'sort order'
* fix taxes not being calculated correctly if your Base Country/State is set to "All of xxx"
* fix `price not announced` not being shown when `price` field is left empty
* fix product edit `sale` and `regular` price fields so they don't allow currency symbols (and non numeric values)
* fix pagination on shortcodes, now only displays if you tell it to by adding `pagination="yes"` to your shortcode
* fix emails not sending from the address specified for Jigoshop emails
* fix showing purchased variations in email
* fix emails not showing pretty titles for payment and shipping methods
	* ie, won't show "via bank_transfer" or "via flat_rate" anymore
	* instead, shows the Method Title you set in your settings page for those methods
* fix out of stock products appearing randomly on upgrade
* fix empty selectbox on Norway and some other countries for checkout page
* fix session conflicts with other plugins by forcing Jigoshop to start sessions first before all other plugins
* fix coupons not applying to variations
	* visit the variation to use the individual product ID for the coupon
* fix coupons showing 'no products in cart' on valid coupons
* fix some international users not being able to add shipping or tax rates (italian & french)
* removed dash from cart on downloadables until a more solid solution can be found
* fix attributes slug when editing attribute names
* fix for using the newer screen API on older WordPress'

= 1.0 - 2012-02-01 =
* New feature for Downloadable products:
	* External URL or Internal downloads, can specify http:// paths
* New widgets:
	* Jigoshop Login
	* Recently Viewed products
	* Recently Reviewed products
	* Best Selling products
	* Top Rated products
* New shortcodes:
	* Add to Cart Button
	* Add to Cart URL
* New tax features:
	* Multiple tax classes per product (great for Canadians!)
	* Define the tax label your customers see on checkout
	* Show tax percentage for customers
	* Compound taxes option (taxes can stack on each other)
* New dimension fields to products
	* Length / Width / Height
* Rewrote shipping class
	* Now allows developers to easily create plugins such as FedEx, UPS, USPS, etc.
* Filter products in wp-admin by:
	* product-category
	* produt type
* Currencies
	* Updated all symbols
	* Added a new currencies
* New languages:
	* Romanian (thanks Liviu!)
	* Croatian (thanks Ivica & Nives!)
* Now multi-lingual capable with the WordPress WPML plugin
* fixed cyrillic characters & accents in attribute / variation names
* fixed fancybox overlay
* fixed My Account page not showing Address 2
* fixed users creating a new account now only if WP registration is allowed
* fixed several 404 Jigoshop internal href links
* fixed default display of variations in admin
* fixed variation display for downloadable products in 'my account'
* fixed dimension frontend display for variations
* fixes sale icon sliding by positioning to the left

= 0.9.9.4 RC2 (private release) =
* added option for out of stock notification that sets products to hidden after Order quantity processing
* added support for backorders on variations
* added product dimensions for length, width, and height
* product thumbnails will now order themselves based on WordPress Gallery Order
* products can now display a percentage saved for products on sale
* changed datepicker to work with WordPress 3.3
* shipping is no longer always forced to the cheapest method
* fix downloadable products, no longer charged shipping either singly or in a mixed cart
* internal restructuring and code cleanup for shipping
* fixed several javascript errors preventing variations from working
* fixed 404 errors on checkout review order
* fixed successful paypal orders still marked as pending instead of processing
* fixed order complete Emails
* fixed add to cart and Select buttons on Shop page with incorrect URL's
* fixed tax calculations for taxable and non taxable products (minor tax no longer applied to non taxable)
* fixed coupon display in the Admin settings
* fixed coupon errors if no coupons in the Admin
* fix recent products shortcode
* fix certain browser attribute display problems on single product pages
* fix Grouped products not showing prices on Shop page
* cart now always shows correct totals without having to click update totals button
* enhancements to internal page determination functions for theme developers
* updated German translations
* a number of other small bug fixes
* a number of security enhancements

= 0.9.9.3 - 2011-11-09 =
* Fixed SVN error

= 0.9.9.2 - 2011-11-09 =
* Add new Product Category shortcode
* Hungarian Translation by Kristóf Gruber
* Hungarian, Croatian and Indian currencies
* Variations only show 'From' if prices actually vary
* Display applied coupons on Admin orders
* Improve installation and page creation, ignore trashed pages
* Repair Admin product list pagination
* Add program hooks to emails to allow output customisation
* Fixes for attributes and variations
* updated Spanish translations
* Added css classes to widget titles, prices for easier styling
* Repair and allow category and tag templates in themes
* Begin Tax rework, allow out of state tax and tax based on shipping address
* More internationalization for text translations
* numerous small bug fixes & code improvements

= 0.9.9.1 - 2011-10-07 =
* Added date ranges for coupons
* Display coupons used on the Cart
* Now saves coupon data in order panel
* Added body classes to track order page
* Added Russian Translation
* Fixed downloadable files not found
* Fixed install duplicates
* Added labels to bank transfer gateway
* Attributes are no longer using the slug
* Minor GUI Tweaks
* Other fixes (https://github.com/jigoshop/jigoshop/issues?milestone=7&state=closed)

= 0.9.9 - 2011-09-20 =
* Configurable Products
* Global image sizes can now be declared from the panel.
* Rows & Columns can now be set in the admin
* Added Bank transfers gateway
* Added Dutch translation
* Added Swedish translation
* Added German translation by deckerweb
* Revamped order items panel
* Settings strip slashes
* Improved French translations
* Added SSL auto detection for assets
* New DIBS gateway for nordic countries
* Support for direct checkout
* Fixes twenty/10/11 support
* Fixed permalink double save when changing the base page
* Moved update/remove from cart code so totals are updated and shipping is updated
* Fixed 'needs shipping' for downloadable products
* Grouped products can contain downloadable, simple, or virtual products
* Changed mail from/to for store
* Download limiter fix
* Made my account downloads respect order status
* Filter for ship to billing defaults
* Improvements to shipping class for table rate shipping
* Optimized scripts.js (no longer a php file)
* Changes to allow new product types to be added by plugins
* Twenty Eleven fixes
* Front page shop support
* virtual add to cart
* Shop page can show content
* SKU display options
* Fixes for default permalinks
* Better ajax handling
* Better shortcode handling with cache
* Filters added to email contents
* Various other minor bug fixes (https://github.com/jigoshop/jigoshop/issues?milestone=6&state=closed)

= 0.9.8 - 2011-07-01 =

* Major changes to template code in an attempt to make it more flexible and easier to theme from the plugin
* Form code changes making things more semantic
* Tweaked category order code
* Changed 'download remaining' database field into a varchar
* localisation issues
* ui.css cut down
* Fixed edit address and change password nonce fields
* Hook for add to cart redirect
* Fixes to sale dates logic
* New product preview shortcodes
* option to hide hidden products in recent products widget
* Breadcrumbs add shop base if chosen as a base
* Tweaked gateway/shipping loading code to work with plugins
* Demo store banner added
* postcode accepts hyphens

= 0.9.7.8 - 2011-06-22 =

* Download permissions bug with emails
* Lets you save download limit as blank

= 0.9.7.7 - 2011-06-22 =

* Fixed discount code logic
* Changed/improved nonces
* Tax amounts take base tax rate into consideration - should fix tax rates for other countries
* Localisation fixes
* Added JIGOSHOP_TEMPLATE_URL constant for moving the template folder within your theme (for better theme compatibility)
* Option in settings to turn off css
* Taxonomy ordering script to allow sorting of product categories using drag and drop
* Excluded shop order comments from front end widget
* per-page limit fix
* Added body classes based on page
* Unlimited download fix
* Lost password link on my-account login
* weight calc fix
* Fixes inconsistent page slugs (- instead of _)
* init changes
* options for foreign currencies
* Added german localization by AlistarMclean
* Removed IE6 stuff from fancybox to speed it up
* Added option to send shipping info to paypal

= 0.9.7.6 - 2011-06-14 =

* POT file added
* global option filtering
* Country name localisation
* 'Shop' page created on install
* Page select boxes in admin
* Options for different permalinks (with a base url)
* Security fixes
* One click featuring of products
* Options to configure page IDs
* Better support for child themes
* Better support for plugin folder names
* Localization of scripts

= 0.9.7.5 - 2011-06-13 =

* GITHUB setup for Jigoshop Project

= 0.9.7.4 - 2011-06-13 =

* Default SSL option changed
* Empty drop-ins folder fix
* Fixed localisation issues
* Option to disallow having a different shipping address to the billing address
* Fixed category dropdown widget
* Fallback for HTML5 placeholders

= 0.9.7.3 - 2011-06-01 =

* Tweaked how files are included to prevent an error

= 0.9.6 - 2011-04-10 =

* Public Beta Release

== Upgrade Notice ==

= 1.1
New payment and shipping gateways, widgets fixed, new shortcode, uninstaller added, and plenty of other bug fixes!