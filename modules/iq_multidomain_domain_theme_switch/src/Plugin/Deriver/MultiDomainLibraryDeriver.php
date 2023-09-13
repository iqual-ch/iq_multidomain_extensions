<?php

namespace Drupal\iq_multidomain_domain_theme_switch\Plugin\Deriver;

use Drupal\ui_patterns_library\Plugin\Deriver\LibraryDeriver;

/**
 * The deriver for ui_patterns.
 *
 * @todo Move pattern deriving to separate module.
 */
class MultiDomainLibraryDeriver extends LibraryDeriver {

  /**
   * {@inheritdoc}
   */
  public function getPatterns() {
    $patterns = parent::getPatterns();
    $prefix_themes = [];
    $domain_config_settings = \Drupal::config('domain_config_ui.settings');
    if (strpos($domain_config_settings->get('path_pages'), '/admin/appearance') !== FALSE) {
      // Collect all secondary themes to prefix the pattern ids.
      $domain_storage = \Drupal::service('entity_type.manager')->getStorage('domain');
      /** @var \Drupal\domain\Entity\Domain $domain */
      foreach ($domain_storage->loadMultipleSorted() as $domain) {
        $domain_config = \Drupal::config('domain.config.' . $domain->id() . '.system.theme');
        if (!$domain->isDefault() && !empty($domain_config->get('default'))) {
          $prefix_themes[] = $domain_config->get('default');
        }
      }
    }

    // Loop over all patterns and prefix patterns provided by secondary domain themes.
    /** @var \Drupal\ui_patterns\Definition\PatternDefinition $definition */
    foreach ($patterns as $delta => $definition) {
      $provider = $definition->getProvider();
      if (in_array($provider, $prefix_themes)) {
        $arrayDefinition = $definition->toArray();
        $arrayDefinition['id'] = $provider . '_' . $arrayDefinition['id'];
        $newDefinition = $this->getPatternDefinition($arrayDefinition);
        $patterns[$delta] = $newDefinition;
      }
    }
    return $patterns;
  }

}
