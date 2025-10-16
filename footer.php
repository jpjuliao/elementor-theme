<?php

/**
 * The template for displaying the footer.
 *
 * Contains the body & html closing tags.
 *
 * @package HelloElementor
 */
if (! defined('ABSPATH')) {
  exit; // Exit if accessed directly.
}

if (get_option('jpjuliao_elementor_theme_display_footer') === '') {
  if (! function_exists('elementor_theme_do_location') || ! elementor_theme_do_location('footer')) {
    if (hello_elementor_display_header_footer()) {
      if (did_action('elementor/loaded') && hello_header_footer_experiment_active()) {
        get_template_part('template-parts/dynamic-footer');
      } else {
        get_template_part('template-parts/footer');
      }
    }
  }
} else {
?>
  <footer id="site-footer" class="site-footer">
    <?php
    $id = get_option('jpjuliao_elementor_theme_display_footer');
    if (is_numeric($id) && \Elementor\Plugin::instance()->documents->get($id)) {
      echo \Elementor\Plugin::instance()->frontend->get_builder_content_for_display($id);
    } else {
      echo '<p style="color:red;">' . esc_html__('Please set a valid Elementor post ID for the footer in the Theme Settings.', 'jpjuliao-elementor-theme') . '</p>';
    }
    ?>
  </footer>
<?php
}
?>

<?php wp_footer(); ?>

</body>

</html>