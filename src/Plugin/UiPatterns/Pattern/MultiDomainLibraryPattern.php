<?php

namespace Drupal\iq_multidomain_extensions\Plugin\UiPatterns\Pattern;

use Drupal\ui_patterns_library\Plugin\UiPatterns\Pattern\LibraryPattern;


/**
 * The UI Pattern plugin.
 *
 * ID is set to "yaml" for backward compatibility reasons.
 *
 * @UiPattern(
 *   id = "yaml",
 *   label = @Translation("MultiDomain Library Pattern"),
 *   description = @Translation("Pattern defined using a YAML file."),
 *   deriver = "\Drupal\iq_multidomain_extensions\Plugin\Deriver\MultiDomainLibraryDeriver"
 * )
 */

class MultiDomainLibraryPattern extends LibraryPattern {

  public function __construct(array $configuration, $plugin_id, $plugin_definition, $root, ModuleHandlerInterface $module_handler, ThemeHandlerInterface $theme_handler) {

    echo $plugin_id;
    print_r( $plugin_definition );

    die();

    parent::__construct($configuration, $plugin_id, $plugin_definition, $root, $module_handler, $theme_handler);



  }

}
