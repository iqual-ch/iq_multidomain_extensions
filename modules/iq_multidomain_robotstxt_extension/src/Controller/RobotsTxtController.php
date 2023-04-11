<?php

namespace Drupal\iq_multidomain_robotstxt_extension\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

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
    if ($this->config->get($domainId . '.domain_robotstxt') && !empty($this->config->get($domainId . '.domain_robotstxt'))) {
      $content = $this->config->get($domainId . '.domain_robotstxt');
    }

    return new Response($content, 200, ['Content-Type' => 'text/plain']);
  }

}
