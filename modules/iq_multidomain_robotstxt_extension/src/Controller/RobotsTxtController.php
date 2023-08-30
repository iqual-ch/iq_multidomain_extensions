<?php

namespace Drupal\iq_multidomain_robotstxt_extension\Controller;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\domain\DomainNegotiatorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

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
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

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
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param string $root
   *   The app root.
   */
  public function __construct(ConfigFactoryInterface $config, DomainNegotiatorInterface $domain_negotiator, LanguageManagerInterface $language_manager, string $root) {
    $this->config = $config->get('domain_site_settings.domainconfigsettings');
    $this->domainNegotiator = $domain_negotiator;
    $this->languageManager = $language_manager;
    $this->root = $root;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('domain.negotiator'),
      $container->get('language_manager'),
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
    $domainId = $this->domainNegotiator->getActiveId();
    $language = $this->languageManager->getCurrentLanguage()->getId();

    $domain_robotstxt_content = _iq_multidomain_extensions_get_config_property_value('robotstxt', $domainId, $language);
    $content = $domain_robotstxt_content ?? file_get_contents($this->root . '/robots.txt');

    return new Response($content, 200, ['Content-Type' => 'text/plain']);
  }

}
