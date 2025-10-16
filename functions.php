<?php

namespace JPJULIAO\Elementor_Theme;

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
