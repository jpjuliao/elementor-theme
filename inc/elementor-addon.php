<?php

namespace JPJULIAO\Elementor_Theme;

use Elementor\Controls_Manager;

class Elementor_Addon
{
  private $documents = null;

  /**
   * Class constructor.
   *
   * Adds actions to enqueue scripts, add slick carousel section,
   * and localize slick settings script.
   */
  public function __construct()
  {
    add_action(
      'elementor/element/container/section_layout/after_section_end',
      [$this, 'add_slick_carousel_section'],
      10,
      2
    );

    add_action(
      'wp_enqueue_scripts',
      [$this, 'enqueue_slick_assets']
    );

    add_action(
      'wp_footer',
      [$this, 'print_slick_init_script']
    );
  }

  /**
   * Returns the documents manager.
   *
   * @return \Elementor\Core\Settings\Base\Documents\Manager The documents manager.
   */
  private function get_documents()
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
   * Adds a slick carousel section to the element.
   *
   * @param Elementor\Widget_Base $element
   * @param string $section_id
   * @param array $args
   */
  public function add_slick_carousel_section($element, $args)
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

  /**
   * Enqueue slick carousel assets if the current page is singular and has an elementor shortcode.
   *
   * The function checks if the current page is singular and if the page content has an elementor shortcode.
   * If both conditions are met, the function enqueues the slick carousel CSS and JavaScript files, as well as a custom
   * init script that will be loaded in the footer.
   */
  public function enqueue_slick_assets()
  {
    if (is_admin()) {
      return;
    }

    if (! is_singular()) {
      return;
    }

    global $post;
    if (empty($post)) {
      return;
    }

    $documents = $this->get_documents();
    $document = $documents ? $documents->get($post->ID) : null;

    if (
      ! $document
      || ! method_exists($document, 'is_built_with_elementor')
      || ! $document->is_built_with_elementor()
    ) {
      return;
    }

    if (! $this->has_slick_enabled($post->ID)) {
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
   * Check whether any element in the Elementor document for $post_id
   * has the activate_slick_slider control set to 'yes'.
   *
   * @param int $post_id
   * @return bool
   */
  private function has_slick_enabled($post_id)
  {
    $documents = $this->get_documents();

    if (! $documents) {
      return false;
    }

    $document = $documents->get($post_id);

    if (! $document) {
      return false;
    }

    $elements = $document->get_elements_data();
    if (empty($elements) || ! is_array($elements)) {
      return false;
    }

    $stack = $elements;
    while (! empty($stack)) {
      $element = array_shift($stack);

      // settings usually live under 'settings'
      if (isset($element['settings']) && isset($element['settings']['activate_slick_slider']) && $element['settings']['activate_slick_slider'] === 'yes') {
        return true;
      }

      // in case settings are at top-level (unlikely) check there
      if (isset($element['activate_slick_slider']) && $element['activate_slick_slider'] === 'yes') {
        return true;
      }

      // push nested elements to stack
      if (isset($element['elements']) && is_array($element['elements'])) {
        foreach ($element['elements'] as $child) {
          $stack[] = $child;
        }
      }
    }

    return false;
  }

  /**
   * Prints the slick initialization script in the footer.
   *
   * This function iterates through the collected slick slider settings and
   * generates the necessary JavaScript to initialize each carousel with its
   * specific configuration.
   */
  public function print_slick_init_script()
  {


    if (is_admin()) {
      return;
    }

    if (! is_singular()) {
      return;
    }

    global $post;
    if (empty($post)) {
      return;
    }

    $documents = $this->get_documents();
    if (! $documents) {
      return;
    }

    $document = $documents->get($post->ID);
    if (
      ! $document
      || ! method_exists($document, 'is_built_with_elementor')
      || ! $document->is_built_with_elementor()
    ) {
      return;
    }

    if (! $this->has_slick_enabled($post->ID)) {
      return;
    }

    $elements = $document->get_elements_data();
    if (empty($elements) || ! is_array($elements)) {
      return;
    }

    // Collect unique slider configs keyed by normalized selector.
    $configs = [];
    $stack = $elements;
    while (! empty($stack)) {
      $element = array_shift($stack);

      $settings = isset($element['settings']) && is_array($element['settings']) ? $element['settings'] : [];

      $activated = ($settings['activate_slick_slider'] ?? $element['activate_slick_slider'] ?? '') === 'yes';
      if ($activated) {
        $parent_class = trim((string) ($settings['slick_parent_class'] ?? $element['slick_parent_class'] ?? ''));

        // If no parent class provided, skip (can't target an element).
        if ($parent_class !== '') {
          // normalize to a selector: if it doesn't start with '.' or '#' assume class
          if ($parent_class[0] !== '.' && $parent_class[0] !== '#') {
            $selector = '.' . ltrim($parent_class, '.#');
          } else {
            $selector = $parent_class;
          }

          $slides = isset($settings['slick_slides_to_show']) ? (int) $settings['slick_slides_to_show'] : 1;

          // You can expand options here to include more settings from controls.
          $options = [
            'slidesToShow' => $slides,
            'adaptiveHeight' => true,
          ];

          // Last encountered config for a selector wins.
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

    // Build JS to initialize each unique selector.
    $inits = [];
    foreach ($configs as $selector => $opts) {
      $selector_js = json_encode($selector);
      $opts_js = wp_json_encode($opts, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
      // avoid re-initializing already initialized sliders
      $inits[] = "try{jQuery({$selector_js}).not('.slick-initialized').slick({$opts_js});}catch(e){}";
    }

    $init_js = 'jQuery(function($){' . implode('', $inits) . '});';

    // Prefer attaching inline script to the slick-js handle so it prints after the library.
    if (function_exists('wp_add_inline_script') && function_exists('wp_script_is') && wp_script_is('slick-js', 'enqueued')) {
      wp_add_inline_script('slick-js', $init_js);
      return;
    }

    // Fallback: echo the script directly in footer.
    echo '<script type="text/javascript">' . $init_js . '</script>';
  }
}
