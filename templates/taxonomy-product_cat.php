<?php 

get_header();

do_action('before_shop');

global $taxonomy;

$taxonomy = 'product_cat';

include('product_taxonomy.php');

get_sidebar('shop');

get_footer(); 

?>
