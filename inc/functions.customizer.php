<?php

namespace toolbox\customizer;

/**
 * Add Twig Filters used in this plugin
 */
add_filter( 'timber/twig'                       , __NAMESPACE__ . '\add_twig_filters' );

/**
 * Filter to get imagesizes
 */
add_filter( 'toolbox-customizer/helpers/imagesizes' , __NAMESPACE__ . '\get_image_sizes' );

/**
 * Callback that add a twig filter to Twig
 * @param [type] $twig [description]
 */
function add_twig_filters( $twig ) {

       /* this is where you can add your own functions to twig */
        $twig->addExtension( new \Twig_Extension_StringLoader() );


        /**
         * Get a theme_mod value for use inside a twig template
         * @var [type]
         */
	    $twig->addFunction( new \Timber\Twig_Function( 'toolbox_gtm'  , function( $theme_mod , $default = null ) {

	    	if ( $default ) return get_theme_mod( $theme_mod , $default );

	    	return get_theme_mod( $theme_mod );

	    } ) );

	return $twig;
}

/**
 * Get all intermediate image sizes currently registered in WP
 *
 * @type   function
 * @since  1.6.3
 * @param  array    $array  function for filter, passed in array
 * @return array
 */
function get_image_sizes( $array ) {

    $sizes = get_intermediate_image_sizes();

    foreach ($sizes as $size ) {
        $data[$size] = $size;
    }

    $data[ 'full' ] = __( 'full' , 'toolbox-customizer' );

    return $data;

}

