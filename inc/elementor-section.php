<?php

namespace JPJULIAO\Elementor_Theme;

abstract class Elementor_Section
{
  private $documents = null;

  /**
   * Retrieves the documents for the Elementor frontend.
   *
   * @access private
   * @return null|\Elementor_Document
   */
  protected function get_documents()
  {
    if ($this->documents !== null) {
      return $this->documents;
    }

    if (! class_exists('\Elementor\Plugin')) {
      $this->documents = null;
      return null;
    }

    $this->documents = \Elementor\Plugin::$instance->documents;
    return $this->documents;
  }

  /**
   * Prepare Elementor frontend.
   *
   * @access private
   * @return null|\Elementor_Document
   */
  protected function prepare_for_frontend()
  {
    if (is_admin()) {
      return null;
    }

    if (! is_singular()) {
      return null;
    }

    global $post;
    if (empty($post)) {
      return null;
    }

    $documents = $this->get_documents();
    if (! $documents) {
      return null;
    }

    $document = $documents->get($post->ID);
    if (
      ! $document
      || ! method_exists($document, 'is_built_with_elementor')
      || ! $document->is_built_with_elementor()
    ) {
      return null;
    }

    return $document;
  }

  /**
   * Normalize a CSS selector string by prepending a unique prefix and handling
   * the parent class string.
   *
   * @param string $parent_class The parent class string from the settings or
   * element.
   * @param array $element The current element being processed.
   * @param array $settings The settings array for the current element.
   * @return string The normalized CSS selector string.
   */
  protected function normalize_selector(
    string $parent_class,
    array $element,
    array $settings
  ) {
    $parent_class = trim((string) ($settings[$parent_class] ?? $element[$parent_class] ?? ''));

    if ($parent_class === '') {
      return '';
    }

    if ($parent_class[0] !== '.' && $parent_class[0] !== '#') {
      $normalized_parent = '.' . ltrim($parent_class, '.#');
    } else {
      $normalized_parent = $parent_class;
    }

    $unique_prefix = '';

    if (! empty($element['id'])) {
      $unique_prefix = '.elementor-element-' . $element['id'];
    }

    return trim($unique_prefix . ' ' . $normalized_parent);
  }

  /**
   * Builds the initialization JavaScript code for the given configurations.
   *
   * @param array $configs The configurations to be used for initializing the
   * slick slider.
   * @return string The initialization JavaScript code.
   */
  protected function build_init_js(string $widget, array $configs)
  {
    $configs_json = wp_json_encode(
      $configs,
      JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
    );

    return "
      jQuery(function($){
        function init() {
          var cfg=" . $configs_json . ";
          for (var s in cfg) {
            try { $(s).not('.slick-initialized').slick(cfg[s]); } 
            catch(e) {}
          }
        }
        $(document).ready(init());
        elementorFrontend.hooks.addAction(
          'frontend/element_ready/" . $widget . "', (scope) => {
            init();
          }
        );
      });
    ";
  }
}
