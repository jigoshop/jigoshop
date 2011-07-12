<?php 

get_header();

do_action('before_shop');
	  
global $taxonomy;

$taxonomy = 'product_tag';

include('product_taxonomy.php');

get_sidebar('shop');

get_footer(); 

?>