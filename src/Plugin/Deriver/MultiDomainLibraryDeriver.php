<?php

namespace Drupal\iq_multidomain_extensions\Plugin\Deriver;

use Drupal\ui_patterns_library\Plugin\Deriver\LibraryDeriver;
use Drupal\Component\Serialization\Yaml;

/**
 * Class MultiDomainLibraryDeriver.
 *
 * @package Drupal\ui_patterns_library\Deriver
 */
class MultiDomainLibraryDeriver extends LibraryDeriver {

  /**
   * {@inheritdoc}
   */
  public function getPatterns() {
    $patterns = [];
    $directories = $this->getDirectories();
    $domain_storage = \Drupal::service('entity_type.manager')->getStorage('domain');

    $installedThemes = array_keys(\Drupal::service('theme_handler')->listInfo());
    foreach ($domain_storage->loadMultipleSorted() as $domain) {
      $theme_name = \Drupal::config('domain_theme_switch.settings')->get($domain->id() . '_site');
      if (!array_key_exists($theme_name, $directories) && in_array($theme_name, $installedThemes)) {
        $directories[$theme_name] = [
          'use_prefix' => TRUE,
          'directory' => DRUPAL_ROOT . '/' . \Drupal::service('extension.list.theme')->getPath($theme_name),
        ];
      }
    }
    foreach ($directories as $provider => $directory) {
      $use_prefix = FALSE;
      if (is_array($directory)) {
        if ($directory['use_prefix']) {
          $use_prefix = TRUE;
        }
        $directory = $directory['directory'];
      }

      foreach ($this->fileScanDirectory($directory) as $file_path => $file) {

        $host_extension = $this->getHostExtension($file_path);
        if ($host_extension == FALSE || $host_extension == $provider) {
          $content = file_get_contents($file_path);
          foreach (Yaml::decode($content) as $id => $definition) {

            if ($use_prefix) {
              $definition['id'] = $provider . '_' . $id;
            }
            else {
              $definition['id'] = $id;
            }

            $definition['base path'] = dirname($file_path);
            $definition['file name'] = basename($file_path);
            $definition['provider'] = $provider;

            $new_pattern = $this->getPatternDefinition($definition);

            if ($use_prefix) {
              $new_pattern['template'] = 'pattern-' . $id;
            }

            $patterns[] = $new_pattern;
          }
        }
      }
    }

    return $patterns;
  }

}
