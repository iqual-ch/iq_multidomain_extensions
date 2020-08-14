<?php

namespace Drupal\iq_multidomain_extensions\Controller;

use Drupal\Core\Form\FormState;
use Drupal\iq_multidomain_extensions\IqualDomainListBuilder;
use Drupal\ui_patterns_library\Controller\PatternsLibraryController;

/**
 * Class PatternLibraryController.
 *
 * @package Drupal\ui_patterns\Controller
 */
class MultiDomainPatternsLibraryController extends PatternsLibraryController {

  /**
   * Render pattern library page.
   *
   * @return array
   *   Patterns overview page render array.
   */
  public function overview() {
    /** @var \Drupal\ui_patterns\Definition\PatternDefinition $definition */
    $definitions = [];

    foreach ($this->patternsManager->getDefinitions() as $id => $definition) {
      $pattern_definition = $definition->toArray();

      if ($pattern_definition['provider'] == \Drupal::service('theme.manager')->getActiveTheme()->getName()) {
        $definitions[$id] = $pattern_definition;
        $definitions[$id]['rendered']['#type'] = 'pattern_preview';
        $definitions[$id]['rendered']['#id'] = $definition->id();
        $definitions[$id]['meta']['#theme'] = 'patterns_meta_information';
        $definitions[$id]['meta']['#pattern'] = $pattern_definition;
      }
    }

    return [
      '#theme' => 'patterns_overview_page',
      '#patterns' => $definitions,
    ];
  }

}
