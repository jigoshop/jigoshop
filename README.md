# [Jigoshop](http://www.jigoshop.com)

Jigoshop is a feature-packed eCommerce plugin built upon Wordpress core functionality ensuring excellent performance and customizability.

## Quick start

To get started, [check out our installation guide](http://forum.jigoshop.com/kb/getting-started/installation)!

1. You can also clone the git repo:

	```
	git clone git://github.com/jigoshop/jigoshop.git
	```

2. Or download it into your WordPress plugin directory:

	https://github.com/jigoshop/jigoshop/zipball/master

## Bug tracker

Have a bug? Please create an issue here on GitHub!

https://github.com/jigoshop/jigoshop/issues

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md)

Anyone and everyone is welcome to contribute. Jigoshop wouldn't be what it is today without the github community.

There are several ways you can help out:

* Raising [issues](https://github.com/jigoshop/jigoshop/issues) on GitHub.
* Submit bug fixes or offer new features / improvements by sending [pull requests](http://help.github.com/send-pull-requests/).
* Offering [your own translations](http://forum.jigoshop.com/kb/shortcodes/languages).

## Change Log

See [CHANGELOG.md](CHANGELOG.md)

## Project information

* Web: http://www.jigoshop.com
* Docs: http://forum.jigoshop.com/kb
* Twitter: http://twitter.com/jigoshop
* Source: http://github.com/jigoshop/jigoshop

## Shortcodes description

* `jigoshop_product_list` - shortcode used to display list of product in column or row format, fetched by specified taxonomy and terms. Parameters:
    * `number` - number of products to download, default: `jigoshop_catalog_per_page` option,
    * `order_by` - ordering of the products, default: "date",
    * `order` - type of ordering of the products, default: "desc",
    * `orientation` - orientation of items, default: "rows",
    * `taxonomy` - taxonomy to restrict products to, default: "product_cat",
    * `terms` - list of term slugs to fetch, can be an array or space separated string, default: empty,
    * `thumbnails` - decides whether to show thumbnails or not, default: "show",
    * `sku` - decides whether to show SKU value or not, default: "hide"
