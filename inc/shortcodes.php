<?php

namespace JPJULIAO\Elementor_Theme;

class Shortcodes {

	public function __construct() {
		add_shortcode( 'elementor_page', [ $this, 'render_elementor_page' ] );
	}

	/**
	 * Renders an Elementor page given its ID.
	 *
	 * This shortcode renders an Elementor page given its ID. It first checks
	 * if the Elementor plugin is activated and if a page ID is provided.
	 * If the page ID is valid, it renders the page using the Elementor
	 * frontend instance.
	 *
	 * @param array $atts The shortcode attributes.
	 * @return string The rendered Elementor page content.
	 */
	public function render_elementor_page( $atts ) {
		if ( ! isset( $atts['id'] ) ) {
			return 'Please provide a page ID.';
		}

		if ( ! class_exists( '\Elementor\Plugin' ) ) {
			return 'Elementor is not activated.';
		}

		$page_id = intval( $atts['id'] );

		if ( ! \Elementor\Plugin::$instance->documents->get( $page_id ) ) {
			return 'Elementor page not found.';
		}

		return \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $page_id );
	}
}
