<?php

namespace JPJULIAO\Elementor_Theme;

class Settings
{
  /**
   * Class constructor.
   *
   * Adds the action to add the Child Settings submenu page to the admin_menu hook.
   *
   * @since 1.0.0
   */
  public function __construct()
  {
    add_action('admin_menu', [$this, 'add_submenu_page']);
    add_action('admin_init', [$this, 'render_submenu_page']);
  }

  /**
   * Add the Child Settings submenu page.
   *
   * This function adds the Child Settings submenu page under the Appearance
   * menu item, and assigns the render_submenu_page method to handle the
   * rendering of the page.
   *
   * @since 1.0.0
   */
  public function add_submenu_page()
  {
    add_submenu_page(
      'themes.php',
      'Theme Settings',
      'Theme Settings',
      'manage_options',
      'theme-settings',
      [$this, 'settings_page_html']
    );
  }

  /**
   * Render the Child Settings submenu page.
   *
   * This function is responsible for rendering the Child Settings
   * submenu page.
   */
  public function render_submenu_page()
  {

    register_setting(
      'theme-settings',
      'jpjuliao_elementor_theme_display_header'
    );

    register_setting(
      'theme-settings',
      'jpjuliao_elementor_theme_display_footer'
    );

    add_settings_section(
      'header_footer_settings_section',
      'Header & Footer Settings',
      '__return_false',
      'theme-settings'
    );

    add_settings_field(
      'jpjuliao_elementor_theme_display_header_field',
      'Header',
      [$this, 'render_display_header_field'],
      'theme-settings',
      'header_footer_settings_section'
    );

    add_settings_field(
      'jpjuliao_elementor_theme_display_footer_field',
      'Footer',
      [$this, 'render_display_footer_field'],
      'theme-settings',
      'header_footer_settings_section'
    );
  }


  /**
   * Renders the form field for the "Display Header" option.
   *
   * @since 1.0.0
   */
  public function render_display_header_field()
  {
    $value = get_option('jpjuliao_elementor_theme_display_header');
    echo '<input type="text" name="jpjuliao_elementor_theme_display_header" value="' . esc_attr($value) . '" class="regular-text" />';
    echo '<p class="description">' . esc_html__('Leave empty to use default Elementor header or set the ID of an Elementor post.', 'jpjuliao-elementor-theme') . '</p>';
  }


  /**
   * Renders the form field for the "Display Footer" option.
   *
   * This function is responsible for rendering the form field for the
   * "Display Footer" option, which is used to toggle the display of
   * the footer section.
   *
   * @since 1.0.0
   */
  public function render_display_footer_field()
  {
    $value = get_option('jpjuliao_elementor_theme_display_footer');
    echo '<input type="text" name="jpjuliao_elementor_theme_display_footer" value="' . esc_attr($value) . '" class="regular-text" />';
    echo '<p class="description">' . esc_html__('Leave empty to use default Elementor footer or set the ID of an Elementor post.', 'jpjuliao-elementor-theme') . '</p>';
  }

  /**
   * Renders the HTML for the Child Settings page.
   *
   * This function renders the HTML for the Child Settings page, which
   * includes a heading, a form with fields for the "Display Header" and
   * "Display Footer" options, and a submit button.
   *
   * @since 1.0.0
   */
  function settings_page_html()
  {
    echo '<div class="wrap">';
    echo '<h1>Theme Settings</h1>';
    echo '<form method="post" action="options.php">';
    settings_fields('theme-settings');
    do_settings_sections('theme-settings');
    submit_button();
    echo '</form>';
    echo '</div>';
  }
}
