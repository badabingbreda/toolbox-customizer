<?php
/**
 * Server side processing for ACF rules.
 *
 * @since 0.1
 */
final class BB_Logic_Rules_TB_Customizer {
	/**
	 * Sets up callbacks for conditional logic rules.
	 *
	 * @since  0.1
	 * @return void
	 */
	static public function init() {

		BB_Logic_Rules::register( array(
			'tbcustomizer/theme_mod' 		=> __CLASS__ . '::theme_mod',
		) );

	}
	/**
	 * Process an Esay ACF rule based on the object ID of the
	 * field location.
	 *
	 * @since  0.1
	 * @param string $object_id
	 * @param object $rule
	 * @return bool
	 */
	static public function evaluate_rule( $object_id , $rule ) {

		$value = get_theme_mod( $rule->theme_mod );

		return BB_Logic_Rules::evaluate_rule( array(
			'value' 	=> $value,
			'operator' 	=> $rule->operator,
			'compare' 	=> $rule->compare,
			'isset' 	=> $value,
		) );
	}

	/**
	 * Field Compare rule.
	 *
	 * @since  0.1
	 * @param object $rule
	 * @return bool
	 */
	static public function theme_mod( $rule ) {
		global $post;
		$id = is_object( $post ) ? $post->ID : 0;

		return self::evaluate_rule( $id, $rule );
	}

}
BB_Logic_Rules_TB_Customizer::init();
