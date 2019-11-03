<?php

namespace toolbox\customizer;

class bb_connector {



	public static $mods = array(
								'string'	=> array(),
							);

	public static function init() {

		add_action( 'fl_page_data_add_properties' , __CLASS__ . "::add_connector" , 10 , 1 );

	}

	public static function add_connector() {

		\FLPageData::add_group( 'toolbox', array(
			'label' => 'Toolbox'
		) );

		\FLPageData::add_post_property( 'customizer_string', array(
				'label'   => 'Theme Mod String',
				'group'   => 'toolbox',
				'type'    => array( 'color','string' ),
				'getter'  => array( __CLASS__ , 'get_connection' ),
			) );


		\FLPageData::add_post_property_settings_fields( 'customizer_string', array(
			// 'css'    => 'https://www.mysite.com/path-to-settings.css',
			// 'js'     => 'https://www.mysite.com/path-to-settings.js',
			'theme_mod' => array(
			    'type'          => 'text',
			    'label'         => __( 'Theme Mod Name', 'textdomain' ),
			    'default'       => '',
			    'placeholder'   => __( 'Enter name of the Theme Mod Field', 'textdomain' ),
			),
			'default_return' => array(
						    'type'          => 'text',
						    'label'         => __( 'Default Return-value', 'textdomain' ),
						    'default'		=> '',
						    'placeholder'   => __( 'Enter the default return-value', 'textdomain' ),
						    'help'          => __( 'Value that gets returned when value hasn\'t been set in customizer.', 'textdomain' ),
			),
			'append' => array(
			    'type'          => 'text',
			    'label'         => __( 'Append to value', 'textdomain' ),
			    'default'       => '',
			    'placeholder'   => __( 'Append to value, example: px, em, vw', 'textdomain' ),
			    'help'          => __( 'Optional string that gets appended directly after the returned value.', 'textdomain' ),
			),

		) );


	}

	public static function get_connection( $settings , $property ) {

		$theme_mod = get_theme_mod( $settings->theme_mod );

		if ( !$theme_mod && $settings->default_return !== '' ) $theme_mod = $settings->default_return;

		$theme_mod = $theme_mod?$theme_mod:apply_filters( 'tb_theme_mod_' . $settings->theme_mod, $theme_mod );

		// // to speed this up we'll just asume that if the first character is a # that we are
		// // dealing with a color
		// //
		if ( is_string($theme_mod) && strpos( '#' , $theme_mod ) == 0 ) return substr( $theme_mod , 1 );

		return $theme_mod . $settings->append;
	}


}

bb_connector::init();
