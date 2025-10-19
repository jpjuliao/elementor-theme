<?php

namespace JPJULIAO\Elementor_Theme;

use Elementor\Controls_Manager;

class Elementor_Slick_Section extends Elementor_Section
{
  public function __construct()
  {
    add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    add_action('wp_footer', [$this, 'print_init_script']);
    add_action(
      'elementor/element/container/section_layout/after_section_end',
      [$this, 'add_section'],
      10,
      2
    );
  }

  /**
   * Check if the given document has at least one element that has the Slick slider enabled.
   *
   * @param \Elementor\Plugin\Documents\Document $document The document object.
   *
   * @return bool True if at least one element in the document has Slick slider enabled, false otherwise.
   */
  private function has_slick_enabled($document)
  {
    if (! $document) {
      return false;
    }

    if (! method_exists($document, 'get_elements_data')) {
      return false;
    }

    $elements = $document->get_elements_data();
    if (empty($elements) || ! is_array($elements)) {
      return false;
    }

    $stack = $elements;
    while (! empty($stack)) {
      $element = array_shift($stack);

      if (isset($element['settings']) && isset($element['settings']['activate_slick_slider']) && $element['settings']['activate_slick_slider'] === 'yes') {
        return true;
      }

      if (isset($element['activate_slick_slider']) && $element['activate_slick_slider'] === 'yes') {
        return true;
      }

      if (isset($element['elements']) && is_array($element['elements'])) {
        foreach ($element['elements'] as $child) {
          $stack[] = $child;
        }
      }
    }

    return false;
  }

  /**
   * Enqueue Slick carousel assets.
   *
   * Checks if the current page has at least one element with Slick slider enabled,
   * and if so, enqueues the Slick carousel CSS and JS assets.
   *
   * @since 1.0.0
   *
   * @access public
   */
  public function enqueue_assets()
  {
    $document = $this->prepare_for_frontend();
    if (! $document) {
      return;
    }

    if (! $this->has_slick_enabled($document)) {
      return;
    }

    wp_enqueue_style(
      'slick-css',
      'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css',
      [],
      '1.8.1'
    );

    wp_enqueue_script(
      'slick-js',
      'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js',
      ['jquery'],
      '1.8.1',
      true
    );

    wp_enqueue_script(
      'jpjuliao-slick-init',
      false,
      ['jquery', 'slick-js'],
      false,
      true
    );
  }

  /**
   * Prints the Slick carousel init JavaScript code.
   *
   * The function will not do anything if the current page does not have at least one element with Slick slider enabled.
   *
   * The function will iterate through all the elements in the page, and for each element that has Slick slider enabled, it will generate the necessary JavaScript code to initialize the Slick carousel.
   *
   * The function will then print the generated JavaScript code to the page.
   *
   * @since 1.0.0
   */
  public function print_init_script()
  {
    $document = $this->prepare_for_frontend();

    if (! $document) {
      return;
    }

    if (! $this->has_slick_enabled($document)) {
      return;
    }

    $elements = $document->get_elements_data();
    if (empty($elements) || ! is_array($elements)) {
      return;
    }

    $configs = [];
    $stack = $elements;
    while (! empty($stack)) {
      $element = array_shift($stack);

      $settings = isset($element['settings']) && is_array($element['settings']) ? $element['settings'] : [];

      $activated = ($settings['activate_slick_slider'] ?? $element['activate_slick_slider'] ?? '') === 'yes';
      if ($activated) {
        $parent_class = trim((string) ($settings['slick_parent_class'] ?? $element['slick_parent_class'] ?? ''));

        if ($parent_class !== '') {
          if ($parent_class[0] !== '.' && $parent_class[0] !== '#') {
            $normalized_parent = '.' . ltrim($parent_class, '.#');
          } else {
            $normalized_parent = $parent_class;
          }

          $unique_prefix = '';
          if (! empty($element['id'])) {
            $unique_prefix = '.elementor-element-' . $element['id'];
          }

          $selector = trim($unique_prefix . ' ' . $normalized_parent);

          $slides = isset($settings['slick_slides_to_show']) ? (int) $settings['slick_slides_to_show'] : 1;

          $options = [
            'slidesToShow' => $slides,
            'adaptiveHeight' => true,
          ];

          $configs[$selector] = $options;
        }
      }

      if (isset($element['elements']) && is_array($element['elements'])) {
        foreach ($element['elements'] as $child) {
          $stack[] = $child;
        }
      }
    }

    if (empty($configs)) {
      return;
    }

    $inits = [];
    foreach ($configs as $selector => $opts) {
      $selector_js = json_encode($selector);
      $opts_js = wp_json_encode($opts, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
      $inits[] = "try{jQuery({$selector_js}).not('.slick-initialized').slick({$opts_js});}catch(e){}";
    }

    $init_js = 'jQuery(function($){' . implode('', $inits) . '});';

    if (function_exists('wp_add_inline_script') && function_exists('wp_script_is') && wp_script_is('slick-js', 'enqueued')) {
      wp_add_inline_script('slick-js', $init_js);
      return;
    }

    echo '<script type="text/javascript">' . $init_js . '</script>';
  }

  /**
   * Add a section to the Advanced tab with controls to activate the Slick Slider carousel.
   *
   * @param Elementor\Controls_Base_Element $element The element to add the controls section to.
   * @param array $args The arguments to pass to the controls section.
   */
  public function add_section($element, $args)
  {
    $element->start_controls_section(
      'slick_carousel_section',
      [
        'label' => __('Slick Carousel', 'jpjuliao-elementor-theme'),
        'tab' => Controls_Manager::TAB_ADVANCED,
      ]
    );

    $element->add_control(
      'activate_slick_slider',
      [
        'label'        => __('Activate Slick Slider', 'jpjuliao-elementor-theme'),
        'type'         => Controls_Manager::SWITCHER,
        'label_on'     => __('Yes', 'jpjuliao-elementor-theme'),
        'label_off'    => __('No', 'jpjuliao-elementor-theme'),
        'return_value' => 'yes',
        'default'      => '',
      ]
    );

    $element->add_control(
      'slick_parent_class',
      [
        'label'     => __('Parent Class', 'jpjuliao-elementor-theme'),
        'type'      => Controls_Manager::TEXT,
        'default'   => '',
        'condition' => [
          'activate_slick_slider' => 'yes',
        ],
      ]
    );

    $element->add_control(
      'slick_slides_to_show',
      [
        'label'     => __('slidesToShow', 'jpjuliao-elementor-theme'),
        'type'      => Controls_Manager::NUMBER,
        'default'   => 1,
        'min'       => 1,
        'max'       => 10,
        'condition' => [
          'activate_slick_slider' => 'yes',
        ],
      ]
    );

    $element->end_controls_section();
  }
}
