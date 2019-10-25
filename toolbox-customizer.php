<?php
/**
 Plugin Name: Toolbox Customizer
 Plugin URI: https://www.beaverplugins.com/toolbox-customizer/
 Description: Create plugin based customizer styles for your themes and work
 Version: 1.1.0
 Author: BadabingBreda
 Text Domain: textdomain
 Domain Path: /languages
 Author URI: https://www.badabing.nl
 */

define( 'TOOLBOXCUSTOMIZER_VERSION'   , '1.1.0' );
define( 'TOOLBOXCUSTOMIZER_DIR'     , plugin_dir_path( __FILE__ ) );
define( 'TOOLBOXCUSTOMIZER_FILE'    , __FILE__ );
define( 'TOOLBOXCUSTOMIZER_URL'     , plugins_url( '/', __FILE__ ) );

require_once( 'inc/class-toolbox-customizer-css.php' );