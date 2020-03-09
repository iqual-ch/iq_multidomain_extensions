<?php

namespace Drupal\iq_multidomain_extensions\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class MultiDomainRouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  public function alterRoutes(RouteCollection $collection) {
    // Replace "some.route.name" below with the actual route you want to override.
    if ($route = $collection->get('ui_patterns.patterns.overview')) {
      $route->setDefaults([
        '_controller' => '\Drupal\iq_multidomain_extensions\Controller\MultiDomainPatternsLibraryController::overview',
      ]);
    }
  }

}
