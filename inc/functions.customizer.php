<?php

namespace toolbox\customizer;
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
 * Add Twig Filters used in this plugin
 */
add_filter( 'timber/twig' 						, __NAMESPACE__ . '\add_twig_filters' );
