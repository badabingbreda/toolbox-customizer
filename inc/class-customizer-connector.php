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

		// return a Theme Mod String
		\FLPageData::add_post_property( 'customizer_string', array(
				'label'   => __( 'Theme Mod String' , 'toolbox-customizer' ),
				'group'   => 'toolbox',
				'type'    => array( 'color','string' ),
				'getter'  => array( __CLASS__ , 'get_connection_string' ),
			) );

		// return a Theme Mod Photo
		\FLPageData::add_post_property( 'customizer_photo', array(
				'label'   => __( 'Theme Mod Photo' , 'toolbox-customizer' ),
				'group'   => 'toolbox',
				'type'    => array( 'photo' ),
				'getter'  => array( __CLASS__ , 'get_connection_photo' ),
			) );

		\FLPageData::add_post_property_settings_fields( 'customizer_string', array(
			// 'css'    => 'https://www.mysite.com/path-to-settings.css',
			// 'js'     => 'https://www.mysite.com/path-to-settings.js',
			'theme_mod' => array(
			    'type'          => 'text',
			    'label'         => __( 'Theme Mod Name', 'toolbox-customizer' ),
			    'default'       => '',
			    'placeholder'   => __( 'Enter name of the Theme Mod Field', 'toolbox-customizer' ),
			),
			'default_return' => array(
						    'type'          => 'text',
						    'label'         => __( 'Default Return-value', 'toolbox-customizer' ),
						    'default'		=> '',
						    'placeholder'   => __( 'Enter the default return-value', 'toolbox-customizer' ),
						    'help'          => __( 'Value that gets returned when value hasn\'t been set in customizer.', 'toolbox-customizer' ),
			),
			'append' => array(
			    'type'          => 'text',
			    'label'         => __( 'Append to value', 'toolbox-customizer' ),
			    'default'       => '',
			    'placeholder'   => __( 'Append to value, example: px, em, vw', 'toolbox-customizer' ),
			    'help'          => __( 'Optional string that gets appended directly after the returned value.', 'toolbox-customizer' ),
			),

		) );

		\FLPageData::add_post_property_settings_fields( 'customizer_photo', array(
			// 'css'    => 'https://www.mysite.com/path-to-settings.css',
			// 'js'     => 'https://www.mysite.com/path-to-settings.js',
			'theme_mod' => array(
			    'type'          => 'text',
			    'label'         => __( 'Theme Mod Name', 'toolbox-customizer' ),
			    'default'       => '',
			    'placeholder'   => __( 'Enter name of the Theme Mod Field', 'toolbox-customizer' ),
			),
			'size' => array(
				'type'        => 'select',
				'label'       => __( 'Image Size', 'toolbox-customizer' ),
				'default'	  => 'medium',
				'options'     => apply_filters( 'toolbox-customizer/helpers/imagesizes', array() ),
			),
			'default_return' => array(
						    'type'          => 'text',
						    'label'         => __( 'Default Return-value', 'toolbox-customizer' ),
						    'default'		=> '',
						    'placeholder'   => __( 'Enter the default return-value', 'toolbox-customizer' ),
						    'help'          => __( 'Value that gets returned when value hasn\'t been set in customizer.', 'toolbox-customizer' ),
			),
		) );

	}

	public static function get_connection_string( $settings , $property ) {

		$theme_mod = get_theme_mod( $settings->theme_mod );

		if ( !$theme_mod && $settings->default_return !== '' ) $theme_mod = $settings->default_return;

		$theme_mod = $theme_mod?$theme_mod:apply_filters( 'tb_theme_mod_' . $settings->theme_mod, $theme_mod );

		// // to speed this up we'll just asume that if the first character is a # that we are
		// // dealing with a color
		// //
		if ( is_string($theme_mod) && strpos( '#' , $theme_mod ) == 0 ) return substr( $theme_mod , 1 );

		return $theme_mod . $settings->append;
	}


	public static function get_connection_photo( $settings , $property ) {

		$theme_mod = get_theme_mod( $settings->theme_mod );

		if ( !$theme_mod && $settings->default_return !== '' ) $theme_mod = $settings->default_return;

		$theme_mod = $theme_mod?$theme_mod:apply_filters( 'tb_theme_mod_' . $settings->theme_mod, $theme_mod );

		if ( gettype( $theme_mod ) == 'integer' ) {

			return wp_get_attachment_image_url( $theme_mod , $settings->size );

		} elseif ( gettype( $theme_mod ) == 'array' ) {

			// try to match the theme_mod as an id (Kirki)
			if ( isset( $theme_mod[ 'id'] ) && gettype( $theme_mod[ 'id' ] ) == 'integer' ) {

				return wp_get_attachment_image_url( $theme_mod[ 'id' ] , $settings->size );

			}

		}

		// returns string or boolean (false) if this point is reached
		return $theme_mod;
	}


}

bb_connector::init();
