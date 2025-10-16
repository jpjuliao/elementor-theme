<?php

namespace JPJULIAO\Elementor_Theme;

class Scripts {

	public function __construct() {
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_styles' ], 10 );
		add_filter( 'locale_stylesheet_uri', [ $this, 'locale_stylesheet' ] );
	}

	/**
	 * Modifies the stylesheet URI for RTL languages.
	 *
	 * If the current language is RTL and the rtl.css file exists in
	 * the theme directory, this function will modify the stylesheet URI
	 * to point to that file. Otherwise, it will return the original URI.
	 *
	 * @param string $uri The original stylesheet URI.
	 *
	 * @return string The modified stylesheet URI.
	 */
	public function locale_stylesheet( $uri ) {
		if (
			empty( $uri )
			&& is_rtl()
			&& file_exists( get_template_directory() . '/rtl.css' )
		) {
			$uri = get_template_directory_uri() . '/rtl.css';
		}
		return $uri;
	}

	/**
	 * Enqueues the child theme stylesheet.
	 *
	 * This function enqueues the child theme's stylesheet and sets it to
	 * depend on the parent theme's stylesheet and the theme style
	 * stylesheet. It is hooked into the `wp_enqueue_scripts` action.
	 */
	public function enqueue_styles() {
		wp_enqueue_style(
			'jpjuliao-elementor-theme-style',
			trailingslashit( get_stylesheet_directory_uri() ) . 'style.css',
			[ 'hello-elementor', 'hello-elementor-theme-style' ]
		);
	}
}
