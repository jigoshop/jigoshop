=== Jigoshop - WordPress eCommerce ===
Contributors: Jigowatt
Tags: ecommerce, wordpress ecommerce, store, shop, shopping, cart, checkout, widgets, reports, shipping, tax, paypal, jigowatt, shipping, inventory, stock, online, sell, sales, weights, dimensions, configurable, variable, downloadable, external, affiliate, download, virtual, physical
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=paypal@jigowatt.co.uk&item_name=Donation+for+Jigoshop
Requires at least: 3.1
Tested up to: 3.3.1
Stable tag: 1.0

A feature-packed eCommerce plugin built upon WordPress core functionality ensuring excellent performance and customisability.

== Description ==

= SETUP IN MINUTES =

Complete your shop in minutes with physical and downloadable products or even services. Jigoshop provides you with the features necessary to set up an eCommerce web site lickety-split.

With the option to create a multitude of product types and apply detailed attributes customers can easily refine your catalog, ensuring they find what they're looking for in just a couple of clicks.

= PRODUCT TYPES =

* Downloadable or Virtual products
* Variable products (eg, offer `Size: S,M,L` for one product)
* Affiliate (External) products
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

Take a look through our <a href="http://forum.jigoshop.com/kb/" title="Jigoshop usage guide">Jigoshop usage guide</a> to help you setup Jigoshop for the first time.

== Frequently Asked Questions ==

= Will Jigoshop work with X theme? =

Jigoshop will in theory work with any theme, but of course, certain parts may need to be styled using CSS to make them match up. We've added default styling for Twenty Ten (the WordPress default theme) and we also offer <a href="http://jigoshop.com/product-category/themes/">premium themes optimised for Jigoshop</a>.

If you need a theme built, or have a theme that needs styling, <a href="http://jigowatt.co.uk/contact/">give us a shout</a> and we may be able to assist.

= Can I have Jigoshop in my language =

Jigoshop comes with a .po file and is localisation ready in over 10 languages.
You can also <a href="http://forum.jigoshop.com/kb/shortcodes/languages">create your own translations</a> for Jigoshop.

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

= 1.0.1 - 2012-02-06 =
* add new payment gateway: cash on delivery
* add new shipping gateway: local pickup
* add "visibility" column to "All Products" screen on admin
* add sort by featured, price, visibility columns on "All Products" screen on admin
* add Turkish, Italian, Portuguese, Norwegian, Portuguese Brazilian translations
* add new shortcode: Products Search
* add bank information to outgoing order email if payment method is bank transfer
* add new setting: show stock amounts (to show or hide stock left in a product for customers)
* update all translation files to include latest strings and remove obsolete strings
* update uninstall.php to remove jigoshop database entries when deleting the plugin through plugin manager
* update included screenshots to reduce total file size
* update Spain provinces (thank @dimitryz)
* update readme.txt and readme.md files
* update "images" tab on Jigoshop settings to take up less space
* fix variable products not showing a 'grouped' tab to set a 'sort order'
* fix taxes not being calculated correctly if your Base Country/State is set to "All of xxx"
* fix `price not announced` not being shown
* fix product edit `sale` and `regular` price fields so they don't allow currency symbols (and non numeric values)
* fix bulk actions on variations when no variations are given
* fix pagination on shortcodes, now displays only if you tell it to by adding pagination="yes" to your shortcode(s)
* fix emails not sending from the address specified for Jigoshop emails
* fix variation display in emails
* fix emails not showing pretty titles for payment and shipping methods (ie, won't show "via bank_transfer" or "via flat_rate" anymore)
* fix out of stock products
* fix empty selectbox on Norway and some other countries for checkout page
* fix featured product widget
* fix top rated widget
* fix recently viewed widget
* fix on sale widget not displaying correct amount of products
* fix session conflicts with other plugins by activating Jigoshop plugin first
* fix coupons not applying to variations -- visit the variation to use the individual product ID in the coupon
* fix coupons showing 'no products in cart' on valid coupons
* fix some international users not being able to add shipping or tax rates (italian & french)

= 1.0 - 2012-02-01 =
* added new feature for downloadable products: external URL or internal downloads
* added new widget Jigoshop Login
* added new widget Recently Viewed products
* added new widget Recently Reviewed products
* added new widget Best Selling products
* added new widget Top Rated products
* added new shortcode Add to Cart Button
* added new shortcode Add to Cart URL
* added new dimension options to products (Length / Width / Height)
* added Admin Product List display products by Category
* added currency symbols for all countries
* added a couple new currencies
* added Romanian and Croatian translations
* added multi-lingual functionality with the WordPress WPML plugin
* fixed cyrillic characters & accents in attribute / variation names
* fixed fancybox overlay
* fixed users creating a new account now only if WP registration is allowed
* fixed several 404 Jigoshop links
* fixed My Account page not showing Address 2
* added calculable shipping so that services such as FedEx, UPS, etc. can be plugged in
* added brand new tax features including
* multi-tax classes can be specified on product (good for Canadian, US tax laws)
* ability to define tax label for view
* shows tax percentage in view

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

= 1.0.1
Widgets fixed, new shortcode, uninstaller added, and plenty of other bug fixes!