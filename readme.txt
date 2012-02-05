=== Jigoshop - WordPress eCommerce ===
Tags: ecommerce, wordpress ecommerce, store, shop, shopping, cart, checkout, widgets, reports, shipping, tax, paypal
Requires at least: 3.1
Tested up to: 3.3.1
Stable tag: 1.0

A feature packed eCommerce plugin built upon WordPress core functionality ensuring excellent performance and customisability.

== Description ==

Set up shop in minutes with physical and downloadable products or even services. Jigoshop provides you with the features necessary to set up an eCommerce web site lickety-split.

With the option to create a multitude of product types and apply detailed attributes customers can easily refine your catalog, ensuring they find what they're looking for in just a couple of clicks.

There are integrated worldwide payment and shipping options to cater for a global audience.

Inside the custom dashboard you get sortable sales graphs, incoming order / review notifications as well as stats on your stores performance.

Manage your stock levels and customer orders easily. Jigoshop has been engineered to make the boring parts of eCommerce, well, less boring!

Built upon the WordPress core you get all the benefits of this global leading platform: free, easy to use, secure, highly customisable and with a great support community to hold your hand.

Styled to work with Twenty Ten, setting up a clean stylish store is easy.

Find out more on the official <a href="http://jigoshop.com" title="WordPress eCommerce">Jigoshop web site</a>.

[vimeo http://vimeo.com/21797311]

= Jigoshop core features: =

* Sell physical, digital and virtual products
* Simple, grouped and configurable products
* Discount coupon management
* Automatic related products
* Product reviews / hreviews
* Tax by location
* Currency options
* Multiple shipping options
* Layered product navigation
* Customer account area
* Inventory tracking
* Detailed order management
* Product import / export
* Custom widgets - recent / featured products, shopping cart, product search
* PayPal standard
* Moneybookers / Skrill
* Cheque payments

= Official Jigoshop themes =

* <a href="http://jigoshop.com/themes/jigotheme/" title="Premium WordPress eComemrce theme for Jigoshop">Jigotheme</a> - Our flagship premium theme complete with mobile optimisation.
* <a href="http://jigoshop.com/themes/origin/" title="Premium WordPress eComemrce theme for Jigoshop">Origin</a> - A clean, minimalist theme for WordPress/Jigoshop.

= Official Jigoshop Extensions =

* <a href="http://jigoshop.com/extensions/sagepay-form/" title="SagePay Form payment gateway extension of Jigoshop">SagePay Form</a> - SagePay Form payment gateway.
* <a href="http://jigoshop.com/extensions/jigoshop-html-email/" title="Jigoshop HTML Emails">Jigoshop HTML Emails</a> - Give your Jigoshop emails a makeover
* <a href="http://jigoshop.com/extensions/jigoshop-meta-tags/" title="Simple SEO Meta Tags">Simple SEO Meta Tags</a> - Add meta data to your individual product pages
* <a href="http://jigoshop.com/extensions/table-rate-shipping/" title="Table Rate Shipping">Table Rate Shipping</a> - Define separate shipping rates for regions based on either price, weight or the number of items in a cart
* <a href="http://jigoshop.com/extensions/jigoshop-up-sell-cross-sell/" title="Up-sell and Cross-sells">Up-sells &amp; Cross-sells</a> - Maximise your stores potential and increase average shopping cart totals by up-selling and cross-selling your products


= Minimum Requirements =

* A WordPress install!
* PHP version 5.2.4 or greater
* MySQL version 5.0 or greater
* The mod_rewrite Apache module (for permalinks)
* fsockopen support (for payment gateway IPN access)
* We recommend a Linux based server rather than a Windows server (Windows servers can have PHP configuration problems, especially with mail).

== Installation ==

= To Install: =

1.  Download the Jigoshop plugin file
2.  Unzip the file into a folder on your hard drive
3.  Upload the `/jigoshop/` folder to the `/wp-content/plugins/` folder on your site
4.  Visit the plugins page in admin and activate it
5.	Re-save your permalink settings to ensure custom post types are installed

= Upgrading Jigoshop =

After upgrading Jigoshop plugin files, be sure to re-activate the plugin to ensure new components are installed correctly.

= Setting up and configuring Jigoshop =

You can find the Jigoshop usage guide <a href="http://jigoshop.com/user-guide/" title="Jigoshop usage guide">on our web site</a>.

== Frequently Asked Questions ==

= Will Jigoshop work with X theme? =

Jigoshop will in theory work with any theme, but of course, certain parts may need to be styled using CSS to make them match up. We've added default styling for Twenty Ten (the WordPress default theme) and we also provide a few bespoke themes optimised for Jigoshop.

If you need a theme built, or have a theme that needs styling, give us a shout and we may be able to assist (see http://jigowatt.co.uk/contact/).

= Can I have Jigoshop in my language =

Jigoshop comes with a .po file and is localisation ready. If you'd like to share your localisation with us please get in touch!

= Do you have an X payment gateway =

We will be introducing payment gateways incrementally, however, not all will be in the free version. If you want to request a payment gateway, or you have built one and you would like to share it with us, please get in touch.

= Do I have access to my order and product data? =

Orders and products are stored as custom post types in the WordPress database; you have full control of them and can import/export using WordPress' functions.

= Will tax settings work in my country? =

Jigoshop has a flexible tax rule system which allows you to define tax rates per country - it should allow you to do what you want.

= I need hosting! =

We offer optimised hosting packages starting from 10 GBP per month at http://jigowatt.co.uk

= I need support! =

We have a <a href="http://forum.jigoshop.com" title="Jigoshop support forum">community forum</a> for getting help from other users.
However, if you want priority, dedicated support from Jigoshop staff, we do offer <a href="http://jigoshop.com/support/" title="Jigoshop Premium Support">support packages</a>.

== Screenshots ==

1. Jigoshop Dashboard
2. Jigoshop Settings
3. Shipping settings
4. Products
5. Product details
6. Orders
7. Homepage
8. Cart
9. Checkout

== Changelog ==

= 1.0.1 =
* update all translation files to include latest strings and remove obsolete strings
* add Turkish, Italian, Portuguese, Norwegian, Portuguese Brazilian translations
* add new shortcode: Products Search
* add bank information to outgoing order email if payment method is bank transfer
* add new setting: show stock amounts (to show or hide stock left in a product for customers)
* fix pagination on shortcodes, now displays only if you tell it to by adding pagination="yes" to your shortcode(s)
* fix emails not sending from the address specified for Jigoshop emails
* fix emails not showing pretty titles for payment and shipping methods (ie, won't show "via bank_transfer" or "via flat_rate" anymore)
* fix out of stock products
* fix empty selectbox on Norway and some other countries for checkout page
* fix featured product widget
* fix top rated widget
* fix recently viewed widget
* fix on sale widget not displaying correct amount of products
* fix session conflicts with other plugins by activating Jigoshop plugin first
* fix coupons showing 'no products in cart' on valid coupons
* fix some international users not being able to add shipping or tax rates (italian & french)

= 1.0 =
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

= 0.9.9.4 RC2 (not publicly released via WordPress) =
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

= 0.9.9.3 =
* Fixed SVN error

= 0.9.9.2 =
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

= 0.9.9.1 =
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

= 0.9.9 =
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

= 0.9.8 =

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

= 0.9.7.8 =

* Download permissions bug with emails
* Lets you save download limit as blank

= 0.9.7.7 =

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

= 0.9.7.6 =

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

= 0.9.7.5 =

* GITHUB setup for Jigoshop Project

= 0.9.7.4 =

* Default SSL option changed
* Empty drop-ins folder fix
* Fixed localisation issues
* Option to disallow having a different shipping address to the billing address
* Fixed category dropdown widget
* Fallback for HTML5 placeholders

= 0.9.7.3 =

* Tweaked how files are included to prevent an error

= 0.9.6 =

* Public Beta Release