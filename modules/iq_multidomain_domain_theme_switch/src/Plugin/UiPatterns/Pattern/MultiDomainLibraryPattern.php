<?php

namespace Drupal\iq_multidomain_domain_theme_switch\Plugin\UiPatterns\Pattern;

use Drupal\ui_patterns_library\Plugin\UiPatterns\Pattern\LibraryPattern;

/**
 * The UI Pattern plugin.
 *
 * ID is set to "yaml" for backward compatibility reasons.
 *
 * @UiPattern(
 *   id = "MultiDomainLibraryPattern",
 *   label = @Translation("MultiDomain Library Pattern"),
 *   description = @Translation("Pattern defined using a YAML file."),
 *   deriver = "\Drupal\iq_multidomain_domain_theme_switch\Plugin\Deriver\MultiDomainLibraryDeriver"
 * )
 */
class MultiDomainLibraryPattern extends LibraryPattern {

}
