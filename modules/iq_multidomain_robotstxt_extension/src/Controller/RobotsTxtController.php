<?php

namespace Drupal\iq_multidomain_robotstxt_extension\Controller;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\domain\DomainNegotiatorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Provides output robots.txt output.
 */
class RobotsTxtController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * The configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

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
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Configuration object factory.
   * @param \Drupal\domain\DomainNegotiatorInterface $domain_negotiator
   *   The domain negotiator.
   * @param string $root
   *   The app root.
   */
  public function __construct(ConfigFactoryInterface $config_factory, DomainNegotiatorInterface $domain_negotiator, string $root) {
    $this->configFactory = $config_factory;
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
    $domain_id = $this->domainNegotiator->getActiveId();
    $config = $this->configFactory->get('domain.config.' . $domain_id . '.robotstxt.settings');
    $domain_robotstxt_content = $config->get('content');
    $content = $domain_robotstxt_content ?? file_get_contents($this->root . '/robots.txt');

    return new Response($content, 200, ['Content-Type' => 'text/plain']);
  }

}
