<?php

namespace JPJULIAO\Elementor_Theme;

class Elementor_Widgets
{

  /**
   * Elementor widgets constructor.
   *
   * Initializes Elementor widgets by registering custom widgets with Elementor.
   */
  public function __construct()
  {
    add_action('elementor/widgets/register', [$this, 'register_widgets']);
  }

  /**
   * Registers custom Elementor widgets.
   *
   * This function includes the custom widget files and registers
   * them with Elementor.
   *
   * @param \Elementor\Widgets_Manager $widgets_manager The Elementor widgets manager.
   */
  public function register_widgets($widgets_manager)
  {
    require_once get_stylesheet_directory() . '/inc/elementor-widgets/slick-slider.php';

    $widgets_manager->register(new \JPJULIAO\Elementor_Theme\Slick_Slider());
  }
}
