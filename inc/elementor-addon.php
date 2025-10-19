<?php

namespace JPJULIAO\Elementor_Theme;

use Elementor\Controls_Manager;

class Elementor_Addon
{

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
    if (empty($post) || !\Elementor\Plugin::$instance->db->is_built_with_elementor($post->ID)) {
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
   * Prints the slick initialization script in the footer.
   *
   * This function iterates through the collected slick slider settings and
   * generates the necessary JavaScript to initialize each carousel with its
   * specific configuration.
   */
  public function print_slick_init_script()
  {
  }
}
