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
  
}
