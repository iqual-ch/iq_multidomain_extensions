<?php

namespace Drupal\iq_multidomain_extensions\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Change routes for ui_patterns.
 */
class MultiDomainRouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  public function alterRoutes(RouteCollection $collection) {
    if ($route = $collection->get('ui_patterns.patterns.overview')) {
      $route->setDefaults([
        '_controller' => '\Drupal\iq_multidomain_extensions\Controller\MultiDomainPatternsLibraryController::overview',
      ]);
    }
  }

}
