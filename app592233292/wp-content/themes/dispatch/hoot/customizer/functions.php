<?php
/**
 * Customizer Functions
 *
 * @package hoot
 * @subpackage hoot-customizer
 * @since hoot 2.0.0
 */

/**
 * Function to modify the settings array and prepare it for Customizer Library Interface functions
 * 
 * @since 2.0.0
 * @access public
 * @param array $settings
 * @return array
 */
function hoot_customizer_prepare_settings( $settings ){

	// Return array
	$value = array();

	// Unique count to create id
	static $count = 1;

	foreach ( $settings as $key => $setting ) {
		if ( isset( $setting['type'] ) ) :

			$new_value = array();
			$new_value = apply_filters( 'hoot_customizer_prepare_settings', $new_value, $key, $setting, $count );
			if ( !empty( $new_value ) )
				$value = array_merge( $value, $new_value );
			else
				$value[ $key ] = $setting;

			$count++;

		else:

			// Add setting as is
			$value[ $key ] = $setting;

		endif;
	}

	// Return prepared settings array
	return $value;
}
add_filter( 'hoot_customizer_add_settings', 'hoot_customizer_prepare_settings', 999 );

/**
 * Helper function to return defaults
 *
 * @since 2.0.0
 * @param string
 * @return mixed $default
 */
function hoot_customizer_get_default( $setting ) {

	$hoot_customizer = Hoot_Customizer::get_instance();
	$settings = $hoot_customizer->get_options('settings');

	if ( isset( $settings[$setting]['default'] ) )
		return $settings[$setting]['default'];
	else
		return '';

}

/**
 * Helper function to return the theme mod value.
 * If no value has been saved, it returns $default.
 * If no $default provided, it checks the default options array.
 * 
 * @since 2.0.0
 * @access public
 * @param string $name
 * @param mixed $default
 * @return mixed
 */
function hoot_get_mod( $name, $default = NULL ) {

	// is_customize_preview() can be used since WordPress 4.0, else use global $wp_customize;
	global $wp_customize;

	if ( ( function_exists( 'is_customize_preview' ) && is_customize_preview() ) || isset( $wp_customize ) ) {

		$default = ( $default !== NULL ) ? $default : hoot_customizer_get_default( $name );
		$mod = get_theme_mod( $name, $default );
		// We cannot use empty as this will become false for empty values, and hence fallback to default. isset() is not an option either as $mod is always set. Hence we calculate the default here, and supply it as second argument to get_theme_mod()
		// if ( !empty( $mod ) )
			return apply_filters( 'hoot_get_mod_customizer', $mod, $name, $default );

	} else {

		/*** Return value if set ***/

		// Cache
		static $mods = NULL;

		// Get the values from database
		if ( !$mods ) {
			$mods = get_theme_mods();
			$mods = apply_filters( 'hoot_get_mod', $mods );
		}

		if ( isset( $mods[$name] ) ) {
			// Filter applied as in get_theme_mod() core function
			$returnvalue = apply_filters( "theme_mod_{$name}", $mods[$name] );
			return apply_filters( 'hoot_sanitize_get_mod', $returnvalue, $name );
		}

		/*** Return default if provided ***/
		if ( $default !== NULL )
			return apply_filters( "theme_mod_{$name}", $default );

		/*** Return default theme option value ***/
		$default = hoot_customizer_get_default( $name );
		if ( !empty( $default ) )
			return apply_filters( "theme_mod_{$name}", $default );

	}

	/*** We dont have anything! ***/
	return false;
}

/**
 * Helper function to return choices
 *
 * @since 2.0.0
 * @param string
 * @return mixed $default
 */
function hoot_customizer_get_choices( $setting ) {

	$hoot_customizer = Hoot_Customizer::get_instance();
	$settings = $hoot_customizer->get_options('settings');

	if ( isset( $settings[$setting]['choices'] ) ) {
		return $settings[$setting]['choices'];
	}

}

/**
 * Helper function to remove all custom theme mods
 *
 * @since 2.0.0
 * @param string
 * @return mixed $default
 */
function hoot_remove_theme_mods() {

	$hoot_customizer = Hoot_Customizer::get_instance();
	$settings = $hoot_customizer->get_options('settings');

	if ( !empty( $settings ) && is_array( $settings ) ) {
		foreach( $settings as $id => $setting ) {
			remove_theme_mod( $id );
		}
	}
}

/**
 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
 *
 * @since 2.0.0
 * @return void
 */
function hoot_customizer_preview_js() {

	if ( file_exists( trailingslashit( HOOT_THEMEDIR ) . 'admin/js/customizer-preview.js' ) )
		wp_enqueue_script( 'hoot_customizer_preview', trailingslashit( HOOT_THEMEURI ) . 'admin/js/customizer-preview.js', array( 'customize-preview' ), HOOT_VERSION, true );

}
add_action( 'customize_preview_init', 'hoot_customizer_preview_js' );