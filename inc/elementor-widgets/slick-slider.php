<?php

namespace JPJULIAO\Elementor_Theme;

class Slick_Slider extends \Elementor\Widget_Base
{

  public function get_name()
  {
    return 'slick_slider';
  }

  public function get_title()
  {
    return __('Slick Slider', 'jpjuliao-elementor-theme');
  }

  public function get_icon()
  {
    return 'eicon-slider-push';
  }

  public function get_categories()
  {
    return ['general'];
  }

  protected function register_controls()
  {
    $this->start_controls_section(
      'content_section',
      [
        'label' => __('Content', 'jpjuliao-elementor-theme'),
        'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
      ]
    );

    $this->add_control(
      'slides',
      [
        'label' => __('Slides', 'jpjuliao-elementor-theme'),
        'type' => \Elementor\Controls_Manager::REPEATER,
        'fields' => [
          [
            'name' => 'slide_image',
            'label' => __('Slide Image', 'jpjuliao-elementor-theme'),
            'type' => \Elementor\Controls_Manager::MEDIA,
            'default' => [
              'url' => \Elementor\Utils::get_placeholder_image_src(),
            ],
          ],
        ],
        'default' => [],
        'title_field' => '{{{ slide_image.url }}}',
      ]
    );

    $this->end_controls_section();
  }

  protected function render()
  {
    $settings = $this->get_settings_for_display();
    if (!empty($settings['slides'])) {
      echo '<div class="slick-slider">';
      foreach ($settings['slides'] as $slide) {
        echo '<div class="slide">';
        echo '<img src="' . $slide['slide_image']['url'] . '">';
        echo '</div>';
      }
      echo '</div>';
    }
  }
}
