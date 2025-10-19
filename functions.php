<?php

namespace JPJULIAO\Elementor_Theme;

use Dom\Element;

if (!defined('ABSPATH')) exit;

// Theme Scripts
require_once 'inc/scripts.php';
new Scripts();

// Theme Settings
require_once 'inc/settings.php';
new Settings();

// Theme Shortcodes
require_once 'inc/shortcodes.php';
new Shortcodes();

// Elementor Section
require_once 'inc/elementor-section.php';

// Elementor Addon
require_once 'inc/elementor-slick-section.php';
new Elementor_Slick_Section();


// Elementor Widgets
require_once 'inc/elementor-widgets.php';
new Elementor_Widgets();