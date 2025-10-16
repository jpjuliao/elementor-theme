<?php

/**
 * The template for displaying the header
 *
 * This is the template that displays all of the <head> section, opens the <body> tag and adds the site's header.
 *
 * @package HelloElementor
 */
if (! defined('ABSPATH')) {
  exit; // Exit if accessed directly.
}

$viewport_content = apply_filters('hello_elementor_viewport_content', 'width=device-width, initial-scale=1');
$enable_skip_link = apply_filters('hello_elementor_enable_skip_link', true);
$skip_link_url = apply_filters('hello_elementor_skip_link_url', '#content');
?>
<!doctype html>
<html <?php language_attributes(); ?>>

<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="<?php echo esc_attr($viewport_content); ?>">
  <link rel="profile" href="https://gmpg.org/xfn/11">
  <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

  <?php wp_body_open(); ?>

  <?php if ($enable_skip_link) { ?>
    <a class="skip-link screen-reader-text" href="<?php echo esc_url($skip_link_url); ?>"><?php echo esc_html__('Skip to content', 'hello-elementor'); ?></a>
  <?php } ?>

  <?php
  if (get_option('jpjuliao_elementor_theme_display_header') === '') {
    if (! function_exists('elementor_theme_do_location') || ! elementor_theme_do_location('header')) {
      if (hello_elementor_display_header_footer()) {
        if (did_action('elementor/loaded') && hello_header_footer_experiment_active()) {
          get_template_part('template-parts/dynamic-header');
        } else {
          get_template_part('template-parts/header');
        }
      }
    }
  } else {
  ?>
    <header id="site-header" class="site-header">
      <?php
      $id = get_option('jpjuliao_elementor_theme_display_header');
      if (is_numeric($id) && \Elementor\Plugin::instance()->documents->get($id)) {
        echo \Elementor\Plugin::instance()->frontend->get_builder_content_for_display($id);
      } else {
        echo '<p style="color:red;">' . esc_html__('Please set a valid Elementor post ID for the header in the Theme Settings.', 'jpjuliao-elementor-theme') . '</p>';
      }
      ?>
    </header>
  <?php
  }
