<?php
class jigoshop_script_loader {
 	
	private static $_instance;
	
	private $scripts_to_load = array();
	
	private $scripts_data = array();
	private $scripts_data_names = array();
	
	private $inline_scripts = array();
		
	/** constructor */
	private function __construct () {	
		
		// will be used when we will introduce dev/compressed scripts
		$suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '.dev' : '';
				
		wp_register_script( 'jigoshop_frontend', jigoshop::plugin_url() . '/assets/js/jigoshop_frontend.js', array('jquery'), '1.0' );
		wp_register_script( 'jigoshop_script', jigoshop::plugin_url() . '/assets/js/script.js', array('jquery'), '1.0' );
		wp_register_script( 'fancybox', jigoshop::plugin_url() . '/assets/js/jquery.fancybox-1.3.4.pack.js', array('jquery'), '1.0' );
		
		/* @TODO: we shouldn't include external scripts */
		wp_register_script( 'jqueryui', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.13/jquery-ui.min.js', array('jquery'), '1.0' );
		
		add_action( 'wp_footer', array($this, 'wp_footer') );
		
	}
	
	public static function instance () {
        if (!isset(self::$_instance)) {
            $c = __CLASS__;
            self::$_instance = new $c;
        }
        return self::$_instance;
    }
    
    /**
     * Static call of load_script class
     * 
     * @param string $handle The script handle
     * @param array $data The optional l10n data
     */
    static function load ( $handle, $data=array(), $data_object_name = null ) {
    	return self::instance()->load_script($handle, $data, $data_object_name);
    }
    
    /**
     * Add a javascript to load in footer
     * 
     * @param string $handle The script handle
     * @param array $data The optional l10n data
     */
    public function load_script ( $handle, $data=array(), $data_object_name = null ) {
    	
    	if( ! in_array($handle, $this->scripts_to_load) ) $this->scripts_to_load[] = $handle;
    	
    	if( ! empty($data) ) {
    		
    		if( isset($this->scripts_data[$handle]) ) $this->scripts_data[$handle] = array_merge($this->scripts_data[$handle], (array) $data);
    		else $this->scripts_data[$handle] = (array) $data;
    		
    		if( $data_object_name && ! isset($this->scripts_data_names[$handle]) ) $this->scripts_data_names[$handle] = $data_object_name;
    	}
    	
    	return $this;
    }
    
    public function add_inline ( $script ) {
    	
    	$this->inline_scripts[] = $script;
    	return $this;
    	
    }
    
    /**
     * Footer action to load scripts
     */
    public function wp_footer () {
    	
    	foreach ( $this->scripts_to_load as $script_handle ) {
    		
    		if( isset($this->scripts_data[$script_handle]) ) wp_localize_script($script_handle, !empty($this->scripts_data_names[$script_handle]) ? $this->scripts_data_names[$script_handle] : $script_handle, $this->scripts_data[$script_handle] );

    		wp_print_scripts( $script_handle );
    		
    	}
    	
    	if( sizeof($this->inline_scripts) ) {
?>    		
<script type="text/javascript">
/* <![CDATA[ */ 
<?php

		foreach ($this->inline_scripts as $script) echo $script;

?>    		
/* ]]> */
</script> 
<?php

    	}
    	
    }
}