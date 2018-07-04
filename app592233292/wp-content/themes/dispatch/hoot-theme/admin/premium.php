<?php
/**
 * Premium Theme Options displayed in admin
 *
 * @package hoot
 * @subpackage dispatch
 * @since dispatch 1.0
 * @return array Return Hoot Options array to be merged to the original Options array
 */

$hoot_options_premium = array();
$imagepath =  trailingslashit( HOOT_THEMEURI ) . 'admin/images/';
$hoot_cta_top = '<a class="button button-primary button-buy-premium" href="https://wphoot.com/themes/dispatch/" target="_blank">' . __( 'Click here to know more', 'dispatch' ) . '</a>';
$hoot_cta_top = $hoot_cta = '<a class="button button-primary button-buy-premium" href="https://wphoot.com/themes/dispatch/" target="_blank">' . __( 'Buy Dispatch Premium', 'dispatch' ) . '</a>';
$hoot_cta_demo = '<a class="button button-secondary button-view-demo" href="https://demo.wphoot.com/dispatch/" target="_blank">' . __( 'View Demo Site', 'dispatch' ) . '</a>';
$hoot_cta_url = 'https://wphoot.com/themes/dispatch/';
$hoot_cta_demo_url = 'https://demo.wphoot.com/dispatch/';

$hoot_intro = array(
	'name' => __('Upgrade to <span>Dispatch <strong>Premium</strong></span>', 'dispatch'),
	'desc' => __("If you've enjoyed using Dispatch, you're going to love <strong>Dispatch Premium</strong>.<br>It's a robust upgrade to Dispatch that gives you many useful features.", 'dispatch'),
	);

$hoot_options_premium[] = array(
	'name' => __('Complete <br />Style <strong>Customization</strong>', 'dispatch'),
	'desc' => __('Different looks and styles. Choose from an extensive range of customization features in Dispatch Premium to setup your own unique front page. Give youe site the personality it deserves.', 'dispatch'),
	'img' => $imagepath . 'premium-style.jpg',
	'style' => 'hero-bottom',
	);

$hoot_options_premium[] = array(
	'name' => __('Unlimited Colors', 'dispatch'),
	'desc' => __('Dispatch Premium lets you select unlimited colors for different sections of your site. Select pre-existing backgrounds for site sections like header, footer etc. or upload your own background images/patterns.', 'dispatch'),
	'img' => $imagepath . 'premium-colors.jpg',
	);

$hoot_options_premium[] = array(
	'name' => __('Fonts and <span>Typography Control</span>', 'dispatch'),
	'desc' => __('Assign different typography (fonts, text size, font color) to menu, topbar, logo, content headings, sidebar, footer etc.', 'dispatch'),
	'img' => $imagepath . 'premium-typography.jpg',
	);

$hoot_options_premium[] = array(
	'name' => __('Unlimites Sliders, Unlimites Slides', 'dispatch'),
	'desc' => __('Dispatch Premium allows you to create unlimited sliders with as many slides as you need.<hr>You can use these sliders on your Homepage widgetized template, or add them anywhere using shortcodes - like in your Posts, Sidebars or Footer.', 'dispatch'),
	'img' => $imagepath . 'premium-sliders.jpg',
	);

$hoot_options_premium[] = array(
	'name' => __('600+ Google Fonts', 'dispatch'),
	'desc' => __("With the integrated Google Fonts library, you can find the fonts that match your site's personality, and there's over 600 options to choose from.", 'dispatch'),
	'img' => $imagepath . 'premium-googlefonts.jpg',
	);

$hoot_options_premium[] = array(
	'name' => __('Shortcodes with <span>Easy Generator</span>', 'dispatch'),
	'desc' => __('Enjoy the flexibility of using shortcodes throughout your site with Dispatch premium. These shortcodes were specially designed for this theme and are very well integrated into the code to reduce loading times, thereby maximizing performance!<hr>Use shortcodes to insert buttons, sliders, tabs, toggles, columns, breaks, icons, lists, and a lot more design and layout modules.<hr>The intuitive Shortcode Generator has been built right into the Edit screen, so you dont have to hunt for shortcode syntax.', 'dispatch'),
	'img' => $imagepath . 'premium-shortcodes.jpg',
	);

$hoot_options_premium[] = array(
	'name' => __('Image Carousels', 'dispatch'),
	'desc' => __('Add carousels to your post, in your sidebar, on your frontpage or in your footer. A simple drag and drop interface allows you to easily create and manage carousels.', 'dispatch'),
	'img' => $imagepath . 'premium-carousels.jpg',
	);

$hoot_options_premium[] = array(
	'name' => __("Floating <br /><span>'Sticky' Header</span> &amp; <span>'Goto Top'</span> button (optional)", 'dispatch'),
	'desc' => __("The floating header follows the user down your page as they scroll, which means they never have to scroll back up to access your navigation menu, improving user experience.<hr>Or, use the 'Goto Top' button appears on the screen when users scroll down your page, giving them a quick way to go back to the top of the page.", 'dispatch'),
	'img' => $imagepath . 'premium-header-top.jpg',
	);

$hoot_options_premium[] = array(
	'name' => __('3 Blog Layouts (including pinterest <span>type mosaic)</span>', 'dispatch'),
	'desc' => __('Dispatch Premium gives you the option to display your post archives in 3 different layouts including a mosaic type layout similar to pinterest.', 'dispatch'),
	'img' => $imagepath . 'premium-blogstyles.jpg',
	);

$hoot_options_premium[] = array(
	'name' => __('Custom Widgets', 'dispatch'),
	'desc' => __('Custom widgets crafted and designed specifically for Dispatch Premium Theme to give you the flexibility of adding stylized content.', 'dispatch'),
	'img' => $imagepath . 'premium-widgets.jpg',
	);

$hoot_options_premium[] = array(
	'name' => __('Menu Icons', 'dispatch'),
	'desc' => __('Select from over 500 icons for your main navigation menu links.', 'dispatch'),
	'img' => $imagepath . 'premium-menuicons.jpg',
	);

$hoot_options_premium[] = array(
	'name' => __('Premium Background Patterns (CC0)', 'dispatch'),
	'desc' => __('Dispatch Premium comes with many additional premium background patterns. You can always upload your own background image/pattern to match your site design.', 'dispatch'),
	'img' => $imagepath . 'premium-backgrounds.jpg',
	);

$hoot_options_premium[] = array(
	'name' => __('Automatic Image Lightbox and <span>WordPress Gallery</span>', 'dispatch'),
	'desc' => __('Automatically open image links on your site with the integrates lightbox in Dispatch Premium.<hr>Automatically convert standard WordPress galleries to beautiful lightbox gallery slider.', 'dispatch'),
	'img' => $imagepath . 'premium-lightbox.jpg',
	);

$hoot_options_premium[] = array(
	'name' => __('Developers <br />love {LESS}', 'dispatch'),
	'desc' => __('CSS is passe. Developers love the modularity and ease of using LESS, which is why Dispatch Premium comes with properly organized LESS files for the main stylesheet.', 'dispatch'),
	'img' => $imagepath . 'premium-lesscss.jpg',
	);

$hoot_options_premium[] = array(
	'name' => __('Easy Import/Export', 'dispatch'),
	'desc' => __('Moving to a new host? Or applying a new child theme? Easily import/export your customizer settings with just a few clicks - right from the backend.', 'dispatch'),
	'img' => $imagepath . 'premium-import-export.jpg',
	);

$hoot_options_premium[] = array(
	'style' => 'aside',
	'blocks' => array(
		array(
			'name' => __('Custom Javascript &amp; <span>Google Analytics</span>', 'dispatch'),
			'desc' => __("Easily insert any javascript snippet to your header without modifying the code files. This helps in adding scripts for Google Analytics, Adsense or any other custom code.", 'dispatch'),
			'img' => $imagepath . 'premium-customjs.jpg',
			),
		array(
			'name' => __('Continued <br />Updates', 'dispatch'),
			'desc' => __("You will help support the continued development of Dispatch - ensuring it works with future versions of WordPress for years to come.", 'dispatch'),
			'img' => $imagepath . 'premium-updates.jpg',
			),
		),
	);

$hoot_options_premium[] = array(
	'name' => __('Premium <br />Priority Support', 'dispatch'),
	'desc' => __("Need help setting up Dispatch? Upgrading to Dispatch Premium gives you prioritized support. We have a growing support team ready to help you with your questions.<hr>Need small modifications? If you are not a developer yourself, you can count on our support staff to help you with CSS snippets to get the look you're after.", 'dispatch'),
	'img' => $imagepath . 'premium-support.jpg',
	);