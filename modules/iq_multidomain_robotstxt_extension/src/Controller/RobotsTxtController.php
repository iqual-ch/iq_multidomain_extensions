<?php

namespace Drupal\iq_multidomain_robotstxt_extension\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\domain\DomainNegotiatorInterface;

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
   * The domain negotiator.
   *
   * @var \Drupal\domain\DomainNegotiatorInterface
   */
  protected $domainNegotiator;

  /**
   * The app root.
   *
   * @var string
   */
  protected $root = '';

  /**
   * Constructs a RobotsTxtController object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   Configuration object factory.
   * @param \Drupal\domain\DomainNegotiatorInterface $domain_negotiator
   *   The domain negotiator.
   * @param string $root
   *   The app root.
   */
  public function __construct(ConfigFactoryInterface $config, DomainNegotiatorInterface $domain_negotiator, string $root) {
    $this->config = $config->get('domain_site_settings.domainconfigsettings');
    $this->domainNegotiator = $domain_negotiator;
    $this->root = $root;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('domain.negotiator'),
      $container->getParameter('app.root')
    );
  }

  /**
   * Serves the configured robots.txt file.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The robots.txt file as a response object with 'text/plain' content type.
   */
  public function content() {
    $content = file_get_contents($this->root . '/robots.txt');
    $domainId = $this->domainNegotiator->getActiveId();
    if ($this->config->get($domainId . '.domain_robotstxt') && !empty($this->config->get($domainId . '.domain_robotstxt'))) {
      $content = $this->config->get($domainId . '.domain_robotstxt');
    }

    return new Response($content, 200, ['Content-Type' => 'text/plain']);
  }

}
