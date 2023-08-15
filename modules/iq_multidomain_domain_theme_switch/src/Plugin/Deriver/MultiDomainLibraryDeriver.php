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

    // Collect all secondary themes to prefix the pattern ids.
    $domain_storage = \Drupal::service('entity_type.manager')->getStorage('domain');
    if ($domain_theme_switch_config = \Drupal::config('domain_theme_switch.settings')) {
      /** @var \Drupal\domain\Entity\Domain $domain */
      foreach ($domain_storage->loadMultipleSorted() as $domain) {
        $theme_name = (string) $domain_theme_switch_config->get($domain->id() . '_site');
        if (!$domain->isDefault() && !empty($theme_name)) {
          $prefix_themes[] = $theme_name;
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
