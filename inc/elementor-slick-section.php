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
   * Prints the initialization script for the slick slider.
   *
   * The function first prepares the document using
   * `prepare_for_frontend`. If the document is empty, it
   * returns early.
   *
   * Then, it checks if any of the elements in the document
   * have the slick slider enabled. If none do, it returns early.
   *
   * Next, it builds the slick slider configurations for all
   * elements in the document and stores them in the `$configs`
   * variable. If the `$configs` variable is empty, it returns
   * early.
   *
   * Finally, it builds the initialization script using the
   * configurations and prints it out. If the `wp_add_inline_script`
   * function is available and the slick-js script is already
   * enqueued, it uses that function to add the initialization
   * script. Otherwise, it simply echoes out the script.
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

    $configs = $this->build_slick_configs($elements);
    if (empty($configs)) {
      return;
    }

    $init_js = $this->build_init_js($configs);

    if (
      function_exists('wp_add_inline_script')
      && function_exists('wp_script_is')
      && wp_script_is('slick-js', 'enqueued')
    ) {
      wp_add_inline_script('slick-js', $init_js);
      return;
    }

    echo '<script type="text/javascript">' . $init_js . '</script>';
  }

  /**
   * Builds an array of slick configurations based on the given elements data.
   *
   * Iterates through the given elements data and checks if the 'activate_slick_slider' setting is enabled.
   * If enabled, it builds a slick configuration array with the 'slick_parent_class' selector and the 'slick_slides_to_show' setting (defaulting to 1).
   * The function also recursively iterates through the elements' children if they exist.
   *
   * @param array $elements The elements data to build the slick configurations from.
   * @return array The array of slick configurations.
   */
  private function build_slick_configs(array $elements)
  {
    $configs = [];
    $stack = $elements;

    while (! empty($stack)) {

      $element = array_shift($stack);
      $settings = isset($element['settings']) && is_array($element['settings']) ? $element['settings'] : [];
      $activated = ($settings['activate_slick_slider'] ?? $element['activate_slick_slider'] ?? '') === 'yes';

      if ($activated) {
        $selector = $this->normalize_selector('slick_parent_class', $element, $settings);

        if ($selector !== '') {
          $slides = isset($settings['slick_slides_to_show']) ? (int) $settings['slick_slides_to_show'] : 1;
          $configs[$selector] = [
            'slidesToShow' => $slides,
            'adaptiveHeight' => true,
          ];
        }
      }

      if (isset($element['elements']) && is_array($element['elements'])) {
        foreach ($element['elements'] as $child) {
          $stack[] = $child;
        }
      }
    }

    return $configs;
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
