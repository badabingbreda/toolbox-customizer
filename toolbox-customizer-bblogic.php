<?php
/**
 * @package  Toolbox Customizer for Themer
 * @since 1.1.2
 */

/**
 * Check if the Theme Builder / Beaver Themer is 1.2 or higher
 */
if ( defined( 'FL_THEME_BUILDER_VERSION' ) && version_compare( FL_THEME_BUILDER_VERSION, '1.2', '>=' ) ) {

	/**
	 * Load the class for the rules
	 * @return void
	 */
	add_action( 'bb_logic_init'				, function() {
		require_once TOOLBOXCUSTOMIZER_DIR . 'rules/tbcustomizer/classes/class-bb-logic-rules-tb-customizer.php';
	});

	/**
	 * Load the class for the rest routes
	 * @return void
	 */
	// add_action( 'rest_api_init' 			, function() {
	// 	require_once TOOLBOXCUSTOMIZER_DIR . 'rest/classes/class-bb-logic-rest-tb-customizer.php';
	// } );

	/**
	 * Enqueue the script necessary for the tbcustomizer rules
	 * @return void
	 */
	add_action( 'bb_logic_enqueue_scripts'	, function() {

		wp_enqueue_script(
			'bb-logic-rules-tb-customizer',
			TOOLBOXCUSTOMIZER_URL . 'rules/tbcustomizer/build/index.js',
			array( 'bb-logic-core' ),
			TOOLBOXCUSTOMIZER_VERSION,
			true
		);

		/**
		 * Add translation for Beavers Conditional Logic Modal
		 * @return [type] [description]
		 */
		wp_localize_script(
		  'bb-logic-rules-tb-customizer',
		  'tb_customizer_js_translations',
		  [
		    '__' => [
		    	'tbcustomizer' => __( 'Toolbox Customizer' , 'toolbox-customizer' ),

			]
		  ]
		);


	} );

}
