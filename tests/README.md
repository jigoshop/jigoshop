[Jigoshop](http://jigoshop.com) Tests
=================

This test-suite uses phpunit to ensure Jigoshop's code quality.

Quick start
-----------

Clone the repo, best into a local wordpress plugin folder.

    cd my-local-wp/wp-content/plugins
    git clone git://github.com/jigoshop/jigoshop.git

    cd jigoshop
    # init submodules to grab wordpress test helper
    git submodule init & git submodule update


Copy & edit wordpress test environment file

    cp vendor/wordpress-tests/unittests-config-sample.php vendor/wordpress-tests/unittests-config.php

Now edit unittests-config.php in your favorite editor. Make sure to have an empty database ready(all data will die) and
that your path to wordpress is correct (eg. my-local-wp/)

    <?php
    /* Path to the WordPress codebase you'd like to test. Add a backslash in the end. */
    define( 'ABSPATH', 'path-to-WP/' );

    define( 'DB_NAME', 'trunk_test' );
    define( 'DB_USER', 'user' );
    define( 'DB_PASSWORD', 'password' );

    # .. more you probably don't need to edit


Run the test from jiigoshop plugin root folder

    phpunit




Install phpunit on Ubuntu
-----------

In case your are using ubuntu(12+), install phpunit like this:

    sudo apt-get install pear
    sudo pear config-set auto_discover 1
    sudo pear install pear.phpunit.de/PHPUnit
