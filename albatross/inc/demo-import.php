<?php

/**
 *
 * Demo data
 *
 **/

function albatross_ocdi_after_import_setup($selected_import)
{
	// Assign menus to their locations.
	$menu1 = get_term_by('slug', 'contacts', 'nav_menu');
	$menu2 = get_term_by('slug', 'socials', 'nav_menu');
	$menu3 = get_term_by('slug', 'primary', 'nav_menu');

	set_theme_mod('nav_menu_locations', array(
			'menu-1' => $menu1->term_id,
			'menu-2' => $menu2->term_id,
			'menu-3' => $menu3->term_id,
		)
	);

	$menu4 = get_term_by('name', 'Footer', 'nav_menu');
	$nav_menu_widget = get_option('widget_nav_menu');

	if ($nav_menu_widget) {
		if ($menu4 && !empty($nav_menu_widget[2])) {
			$nav_menu_widget[2]['nav_menu'] = $menu4->term_id;
		}
	}

	// Assign front page and posts page (blog page).
	$front_page_id = get_page_by_title('Home');
	$blog_page_id = get_page_by_title('Blog');

	update_option('show_on_front', 'page');
	update_option('page_on_front', $front_page_id->ID);
	update_option('page_for_posts', $blog_page_id->ID);

	// Assign Hotel Booking default pages.
	$search_results_page = get_page_by_title('Search Results');
	$booking_confirmation_page = get_page_by_title('Booking Confirmation');
	$terms_conditions_page = get_page_by_title('Terms & Conditions');
	$booking_confirmed_page = get_page_by_title('Booking Confirmed');
	$booking_cancelled_page = get_page_by_title('Booking Cancelled');

	update_option('mphb_search_results_page', $search_results_page->ID);
	update_option('mphb_checkout_page', $booking_confirmation_page->ID);
	update_option('mphb_terms_and_conditions_page', $terms_conditions_page->ID);
	update_option('mphb_booking_confirmation_page', $booking_confirmed_page->ID);
	update_option('mphb_user_cancel_redirect_page', $booking_cancelled_page->ID);

	// skip hotel booking wizard
	update_option( 'mphb_wizard_passed', true);

	update_option('elementor_disable_color_schemes', true);
	update_option('elementor_disable_typography_schemes', true);

	if (class_exists('\Elementor\Core\Kits\Manager')) {
		$kit_manager = new \Elementor\Core\Kits\Manager();

		$kit_manager->update_kit_settings_based_on_option('system_colors', [
			[
				"_id" => "primary",
				"title" => "Primary",
				"color" => "#455D58"
			],
			[
				"_id" => "secondary",
				"title" => "Secondary",
				"color" => "#455D58"
			],
			[
				"_id" => "text",
				"title" => "Text",
				"color" => "#455D58"
			],
			[
				"_id" => "accent",
				"title" => "Accent",
				"color" => "#FC9285"
			],
		]);

		$kit_manager->update_kit_settings_based_on_option('container_width', [
			"unit" => "px",
			"size" => 1340,
			"sizes" => []
		]);
	}

	//update taxonomies
	$update_taxonomies = array(
		'post_tag',
		'category'
	);

	foreach ($update_taxonomies as $taxonomy) {
		albatross_ocdi_update_taxonomy($taxonomy);
	}

	//set site default logo
	$logo = albatross_get_attachment_by_name('logo-dark');
	if ($logo) {
		set_theme_mod('custom_logo', $logo->ID);
	}
	$logo = albatross_get_attachment_by_name('logo-light');
	if ($logo) {
		set_theme_mod('albatross_light_logo', $logo->ID);
	}

}

add_action('pt-ocdi/after_import', 'albatross_ocdi_after_import_setup');

// Disable generation of smaller images (thumbnails) during the content import
//add_filter( 'pt-ocdi/regenerate_thumbnails_in_content_import', '__return_false' );

// Disable the branding notice
add_filter('pt-ocdi/disable_pt_branding', '__return_true');

function albatross_ocdi_update_taxonomy($taxonomy)
{
	$get_terms_args = array(
		'taxonomy' => $taxonomy,
		'fields' => 'ids',
		'hide_empty' => false,
	);

	$update_terms = get_terms($get_terms_args);
	if ($taxonomy && $update_terms) {
		wp_update_term_count_now($update_terms, $taxonomy);
	}
}

function albatross_make_existed_widgets_inactive()
{
	$widgets = get_option('sidebars_widgets');

	for ($i = 1; $i <= 9; $i++) {
		if (is_active_sidebar('sidebar-' . $i)) {
			$widgets['wp_inactive_widgets'] = array_merge($widgets['wp_inactive_widgets'], $widgets['sidebar-' . $i]);
			$widgets['sidebar-' . $i] = [];
		}
	}

	update_option('sidebars_widgets', $widgets);
}

add_action('pt-ocdi/widget_importer_before_widgets_import', 'albatross_make_existed_widgets_inactive');


function albatross_get_attachment_by_name($name)
{
	$args = array(
		'post_type' => 'attachment',
		'name' => $name,
		'posts_per_page' => 1,
		'post_status' => 'inherit',
	);

	$image = get_posts($args);

	return $image ? array_pop($image) : null;
}