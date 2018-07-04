<?php
/**
 * Upsell page
 *
 * @package hoot
 * @subpackage framework
 * @since hoot 2.1.6
 */

/** Determine whether to load upsell subpage **/
$premium_features_file = trailingslashit( HOOT_THEMEDIR ) . 'admin/premium.php';
$hoot_load_upsell_subpage = apply_filters( 'hoot_load_upsell_subpage', file_exists( $premium_features_file ) );
if ( !$hoot_load_upsell_subpage )
	return;

/* Add the admin setup function to the 'admin_menu' hook. */
add_action( 'admin_menu', 'hoot_appearance_subpage' );

/**
 * Sets up the Appearance Subpage
 *
 * @since 4.1
 * @access public
 * @return void
 */
function hoot_appearance_subpage() {

	add_theme_page(
		__( 'Dispatch Premium', 'dispatch' ),	// Page Title
		__( 'Premium Options', 'dispatch' ),	// Menu Title
		'edit_theme_options',					// capability
		'dispatch-premium',						// menu-slug
		'hoot_theme_appearance_subpage'			// function name
		);

	add_action( 'admin_enqueue_scripts', 'hoot_admin_enqueue_upsell_styles' );

}

/**
 * Enqueue CSS
 *
 * @since 4.1
 * @access public
 * @return void
 */
function hoot_admin_enqueue_upsell_styles( $hook ) {
	if ( $hook == 'appearance_page_dispatch-premium' )
		wp_enqueue_style( 'hoot-admin-upsell', trailingslashit( HOOT_THEMEURI ) . 'admin/css/upsell.css', array(),  HOOT_VERSION );
}

/**
 * Display the Appearance Subpage
 *
 * @since 4.1
 * @access public
 * @return void
 */
function hoot_theme_appearance_subpage() {
	/** Load Premium Features Data **/
	include( trailingslashit( HOOT_THEMEDIR ) . 'admin/premium.php' );

	/** Display Premium Teasers **/
	$hoot_cta = ( empty( $hoot_cta ) ) ? '<a class="button button-primary button-buy-premium" href="https://wphoot.com/" target="_blank">' . __( 'Click here', 'dispatch' ) . '</a>' : $hoot_cta;
	$hoot_cta_top = ( empty( $hoot_cta_top ) ) ? $hoot_cta : $hoot_cta_top;
	$hoot_intro = ( !empty( $hoot_intro ) && is_array( $hoot_intro ) ) ? $hoot_intro : array();
	$hoot_intro = wp_parse_args( $hoot_intro, array(
		'name' => __('Upgrade to Premium', 'dispatch'),
		'desc' => '',
		) );
	?>
	<div id="hoot-upsell" class="wrap">
		<h1 class="centered"><?php echo $hoot_intro['name']; ?></h1>
		<p class="hoot-upsell-intro centered"><?php echo $hoot_intro['desc']; ?></p>
		<p class="hoot-upsell-cta centered"><?php if ( !empty( $hoot_cta_demo ) ) echo $hoot_cta_demo; echo $hoot_cta_top; ?></p>
		<?php if ( !empty( $hoot_options_premium ) && is_array( $hoot_options_premium ) ): ?>
			<div class="hoot-upsell-sub">
				<?php foreach ( $hoot_options_premium as $key => $feature ) : ?>
					<?php $style = empty( $feature['style'] ) ? 'info' : $feature['style']; ?>
					<div class="section-premium <?php
						if ( $style == 'hero-top' || $style == 'hero-bottom' ) echo "premium-hero premium-{$style}";
						elseif ( $style == 'side' ) echo 'premium-sideinfo';
						elseif ( $style == 'aside' ) echo 'premium-asideinfo';
						else echo "premium-{$style}";
						?>">

						<?php if ( $style == 'hero-top' || $style == 'hero-bottom' ) : ?>
							<?php if ( $style == 'hero-top' ) : ?>
								<h4 class="heading"><?php echo $feature['name']; ?></h4>
								<?php if ( !empty( $feature['desc'] ) ) echo '<div class="premium-hero-text">' . $feature['desc'] . '</div>'; ?>
							<?php endif; ?>
							<?php if ( !empty( $feature['img'] ) ) : ?>
								<div class="premium-hero-img">
									<img src="<?php echo esc_url( $feature['img'] ); ?>" />
								</div>
							<?php endif; ?>
							<?php if ( $style == 'hero-bottom' ) : ?>
								<h4 class="heading"><?php echo $feature['name']; ?></h4>
								<?php if ( !empty( $feature['desc'] ) ) echo '<div class="premium-hero-text">' . $feature['desc'] . '</div>'; ?>
							<?php endif; ?>

						<?php elseif ( $style == 'side' ) : ?>
							<div class="premium-side-wrap">
								<div class="premium-side-img">
									<img src="<?php echo esc_url( $feature['img'] ); ?>" />
								</div>
								<div class="premium-side-textblock">
									<?php if ( !empty( $feature['name'] ) ) : ?>
										<h4 class="heading"><?php echo $feature['name']; ?></h4>
									<?php endif; ?>
									<?php if ( !empty( $feature['desc'] ) ) echo '<div class="premium-side-text">' . $feature['desc'] . '</div>'; ?>
								</div>
								<div class="clear"></div>
							</div>

						<?php elseif ( $style == 'aside' ) : ?>
							<?php if ( !empty( $feature['blocks'] ) ) : ?>
								<div class="premium-aside-wrap">
								<?php foreach ( $feature['blocks'] as $key => $block ) {
									echo '<div class="premium-aside-block premium-aside-'.($key+1).'">';
										if ( !empty( $block['img'] ) ) : ?>
											<div class="premium-aside-img">
												<img src="<?php echo esc_url( $block['img'] ); ?>" />
											</div>
										<?php endif;
										if ( !empty( $block['name'] ) ) : ?>
											<h4 class="heading"><?php echo $block['name']; ?></h4>
										<?php endif;
										if ( !empty( $block['desc'] ) ) echo '<div class="premium-aside-text">' . $block['desc'] . '</div>';
									echo '</div>';
								} ?>
								<div class="clear"></div>
								</div>
							<?php endif; ?>

						<?php else : ?>
							<?php if ( !empty( $feature['img'] ) ) : ?>
								<div class="premium-info-img">
									<img src="<?php echo esc_url( $feature['img'] ); ?>" />
								</div>
							<?php endif; ?>
							<div class="premium-info-textblock">
								<?php if ( !empty( $feature['name'] ) ) : ?>
									<div class="premium-info-heading"><h4 class="heading"><?php echo $feature['name']; ?></h4></div>
								<?php endif; ?>
								<?php if ( !empty( $feature['desc'] ) ) echo '<div class="premium-info-text">' . $feature['desc'] . '</div>'; ?>
								<div class="clear"></div>
							</div>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
				<div class="section-premium hoot-upsell-cta centered"><?php if ( !empty( $hoot_cta_demo ) ) echo $hoot_cta_demo; echo $hoot_cta; ?></p>
			</div>
		<?php endif; ?>
		<a class="hoot-premium-top" href="#wpbody"><span class="dashicons dashicons-arrow-up-alt"></span></a>
	</div>
	<?php
}