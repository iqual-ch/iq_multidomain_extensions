<?php

namespace Drupal\iq_multidomain_robotstxt_extension\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Cache\CacheableResponse;

/**
 * Provides output robots.txt output.
 */
class RobotsTxtController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * RobotsTxt module 'robotstxt.settings' configuration.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * Constructs a RobotsTxtController object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   Configuration object factory.
   */
  public function __construct(ConfigFactoryInterface $config) {
    $this->config = $config->get('domain_site_settings.domainconfigsettings');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
    );
  }

  /**
   * Serves the configured robots.txt file.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The robots.txt file as a response object with 'text/plain' content type.
   */
  public function content() {
    $content = file_get_contents(\Drupal::root() . '/robots.txt');
    $domainId = \Drupal::service('domain.negotiator')->getActiveId();
    if ($this->config->get($domainId . '.domain_robotstxt') && count($this->config->get($domainId . '.domain_robotstxt'))) {
      $content = $this->config->get($domainId . '.domain_robotstxt');
    }

    $response = new CacheableResponse($content, Response::HTTP_OK, ['content-type' => 'text/plain']);
    $meta_data = $response->getCacheableMetadata();
    $meta_data->addCacheTags(['robotstxt']);
    return $response;
  }

}
