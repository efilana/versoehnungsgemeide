<?php
// Get Left Content
$topbar_left = is_active_sidebar( 'topbar-left' );
$topbar_right = is_active_sidebar( 'topbar-right' );

// Template modification Hook
do_action( 'hoot_template_before_topbar', $topbar_left, $topbar_right );

// Display Topbar
if ( !empty( $topbar_left ) || !empty( $topbar_right ) ) :

	?>
	<div <?php hoot_attr( 'topbar', '', 'inline_nav hgrid-stretch' ); ?>>
		<div class="hgrid">
			<div class="hgrid-span-12">

				<div class="table">
					<?php if ( $topbar_left ): ?>
						<div id="topbar-left" class="table-cell-mid">
							<?php dynamic_sidebar( 'topbar-left' ); ?>
						</div>
					<?php endif; ?>

					<?php if ( $topbar_right ): ?>
						<div id="topbar-right" class="table-cell-mid">
							<div id="topbar-right-inner">
								<?php dynamic_sidebar( 'topbar-right' ); ?>
							</div>
						</div>
					<?php endif; ?>
				</div>

			</div>
		</div>
	</div>
	<?php

endif;

// Template modification Hook
do_action( 'hoot_template_after_topbar', $topbar_left, $topbar_right );