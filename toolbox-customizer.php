<?php
/**
 Plugin Name: Toolbox Customizer
 Plugin URI: https://www.beaverplugins.com/toolbox-customizer/
 Description: Create plugin based customizer styles for your themes and work
 Version: 1.6.5
 Author: BadabingBreda
 Text Domain: toolbox-customizer
 Domain Path: /languages
 Author URI: https://www.badabing.nl
 */

define( 'TOOLBOXCUSTOMIZER_VERSION'   , '1.6.5' );
define( 'TOOLBOXCUSTOMIZER_DIR'     , plugin_dir_path( __FILE__ ) );
define( 'TOOLBOXCUSTOMIZER_FILE'    , __FILE__ );
define( 'TOOLBOXCUSTOMIZER_URL'     , plugins_url( '/', __FILE__ ) );

include_once( 'toolbox-customizer-bblogic.php' );

require_once( 'inc/class-toolbox-customizer-css.php' );

require_once( 'inc/class-customizer-connector.php' );

require_once( 'inc/functions.customizer.php' );
