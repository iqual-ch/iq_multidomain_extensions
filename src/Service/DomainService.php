<?php

namespace Drupal\iq_multidomain_extensions\Service;

use Drupal\Core\Messenger\MessengerInterface;
use Tyldar\Rancher\Rancher;
use Drupal\Core\Form\FormStateInterface;
use Drupal\system\Entity\Menu;
use Drupal\styling_profiles\Entity\StylingProfile;

/**
 *
 */
class DomainService {

  /**
   * The drupal messenger.
   *
   * @var Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;
  /**
   * The drupal messenger.
   *
   * @var Drupal\Core\Messenger\MessengerInterface
   */
  protected $client;
  /**
   * The drupal messenger.
   *
   * @var Drupal\Core\Messenger\MessengerInterface
   */
  protected $domainBase;


  protected $domainNamespace = '';
  protected $domainService = '';
  protected $projectId = '';
  protected $notificationEmail = '';

  /**
   * Create a new instance.
   *
   * @param Drupal\Core\Messenger\MessengerInterface $messenger
   *   The drupal messenger.
   * @param \Drupal\Core\Config\ImmutableConfig $config
   *   The configuration.
   */
  public function __construct(MessengerInterface $messenger) {
    $this->messenger = $messenger;

    $this->configuration = \Drupal::config('iq_multidomain_extensions.rancher_settings');

    $this->themeDirectory = $this->configuration->get('directory_path');
    $this->baseTheme = $this->configuration->get('base_theme');
    $this->notificationEmail = $this->configuration->get('notification_email');
    $this->domainBase = getenv('DOMAIN_BASE');
    $endpoint = $this->configuration->get('rancher_endpoint');

    $RANCHER_TOKEN = getenv("RANCHER_API_TOKEN");
    if (strrpos($RANCHER_TOKEN, ":") > 0) {
      $username = explode(":", $RANCHER_TOKEN)[0];
      $password = explode(":", $RANCHER_TOKEN)[1];
    }
    else {
      $this->messenger->addMessage('Rancher token is missing from the environment variables. Could not connect to rancher environment.', 'error');
      return $this;
    }
    try {
      $this->client = new Rancher($endpoint, $username, $password);
    }
    catch (\Exception $e) {
      $this->messenger->addMessage('There was an error on connecting to the rancher environment.', 'error');
      return $this;
    }
    if (getenv('RANCHER_PROJECT_ID') && getenv('RANCHER_SERVICE_ID')) {
      $this->projectId = getenv('RANCHER_PROJECT_ID');
      $this->domainService = getenv('RANCHER_SERVICE_ID');
      $this->domainNamespace = explode(':', getenv('RANCHER_SERVICE_ID'))[0];
    }
  }

  /**
   *
   */
  protected function getMetaInfo($currentHost) {
    $projects = $this->rancher->projects()->getAll();
    foreach ($projects as $project) {
      $ingresses = $this->rancher->ingresses($project->id)->getAll();
      foreach ($ingresses as $ingress) {
        if (isset($this->domainNamespace)) {
          break;
        }
        foreach ($ingress->publicEndpoints as $publicEndpoint) {
          if (isset($this->domainNamespace)) {
            break;
          }
          if ($publicEndpoint->hostname == $currentHost) {
            $this->domainNamespace = $ingress->namespaceId;
            $this->domainService = $publicEndpoint->serviceId;
            $this->projectId = $project->id;
            break;
          }
        }
      }
    }
  }

  /**
   *
   */
  public function addMenu(string $label, string $id, $contentTypes = []) {
    // Add a menu.
    $menu = Menu::load($id);
    if ($menu == NULL) {
      $menu = Menu::create([
        'id' => $id,
        'label' => $label . ' - Main navigation',
        'description' => 'Main navigation menu for ' . $label,
      ]);
      $menu->save();
    }
    foreach ($contentTypes as $contentTypeId => $label) {
      $contentType = \Drupal::service('entity.manager')->getStorage('node_type')->load($contentTypeId);
      $menus = $contentType->getThirdPartySetting('menu_ui', 'available_menus');
      if (!in_array($id, $menus)) {
        $menus[] = $id;
        $contentType->setThirdPartySetting('menu_ui', 'available_menus', $menus);
        $contentType->save();
      }
    }
    if (\Drupal::moduleHandler()->moduleExists('pagetree')) {
      $pagetree_settings = \Drupal::configFactory()->getEditable('pagetree.settings');
      $pagetree_settings->set('menus', array_merge($pagetree_settings->get('menus'), [$menu->id() => $menu->id()]));
      $pagetree_settings->save();
    }
  }

  /**
   *
   */
  public function createIngress(string $hostname) {
    if (empty($this->domainService) || empty($this->projectId)) {
      $this->getMetaInfo($_SERVER['HTTP_HOST']);
    }
    /**
     * Create the ingress.
     */
    $ingressName = str_replace(".", "-", $hostname) . '-ingress';
    $ingresses = $this->client->ingresses($this->projectId)->getAll();
    $ingressExists = FALSE;
    foreach ($ingresses as $ingress) {
      $ingressHostname = json_decode(json_encode($ingress->rules), TRUE)[0]['host'];
      if ($ingress->name == $ingressName && $ingressHostname == $hostname) {
        $ingressExists = TRUE;
        break;
      }
    }
    if (!$ingressExists) {
      $ingress = $this->client->helpers()->createIngress($ingressName, $this->domainNamespace, $this->projectId, $hostname, $this->domainService, $this->domainNamespace . ":" . $ingressName . "-autogen");
      $this->messenger->addMessage('New ingress created on rancher.');
    }
    else {
      // TODO: Check whether we want to remove ingresses automatically
      // $this->client->ingresses($project_id)->remove($ingress->id);
      // $ingress = $this->client->helpers()->createIngress($ingressName, $this->domainNamespace, $this->projectId, $hostname, $this->domainService, $this->domainNamespace . ":" . $ingressName . "-autogen");.
    }
  }

  /**
   *
   */
  public function copyBaseTheme(string $newThemeName) {
    /** @var \Drupal\Core\Extension\ThemeHandler $themeHandler */
    $themeHandler = \Drupal::service('theme_handler');
    $themeHandler->refreshInfo();
    if (!is_dir($this->themeDirectory . '/' . $this->baseTheme)) {
      $this->messenger->addMessage('Base theme ' . $this->themeDirectory . '/' . $this->baseTheme . ' is missing. New theme cannot be created.', 'error');
      return;
    }

    if (!is_dir($this->themeDirectory . '/' . $newThemeName)) {
      $this->recursiveCopy($this->themeDirectory . '/' . $this->baseTheme, $this->themeDirectory . '/' . $newThemeName, $this->baseTheme, $newThemeName);
      $themeInfo = $form['name']['#value'];
      $config = \Drupal::service('config.factory')->getEditable('domain_site_settings.domainconfigsettings');
      $config->set($form['id']['#value'] . '.site_name', $themeInfo);
      $mail = \Drupal::configFactory()->get('system.site')->get('mail');
      if (!isset($mail) || empty($mail)) {
        $mail = 'info@docker.iqual.ch';
      }
      $config->set($form['id']['#value'] . '.site_mail', $mail);
      $config->save();
      $this->changeThemeInfo($this->themeDirectory . '/' . $newThemeName . '/' . $newThemeName . '.info.yml', $themeInfo);
      // Sleep 2 seconds in order for theme to be fully moved.
      sleep(2);
      try {
        $themeHandler->getTheme($newThemeName);
      }
      catch (UnknownExtensionException $exception) {
        /** @var \Drupal\Core\Extension\ThemeInstaller $themeInstaller */
        $themeInstaller = \Drupal::service('theme_installer');
        try {
          $themeInstaller->install([$newThemeName]);
        }
        catch (EntityStorageException $e) {
          $this->messenger->addMessage(t('Theme is already stored.'), 'warning');
        }
        catch (UnknownExtensionException $e) {
          $this->messenger->addMessage(t('There was a problem with installing the theme.'), 'error');
        }
      }
      $config = \Drupal::service('config.factory')->getEditable('domain_theme_switch.settings');
      $config->set($form['id']['#value'] . '_site', $newThemeName);
      $config->save();

      // Copy all the settings from the previous to the new theme.
      $data = \Drupal::configFactory()->get('iq_custom.settings')->getRawData();
      \Drupal::configFactory()->getEditable($newThemeName . '.settings')->setData($data)->save();
    }
  }

  /**
   * Function to copy a theme folder.
   *
   * @param $src
   *   The source of the theme.
   * @param $dst
   *   The destination of the theme copy.
   * @param $old_name
   *   Old name of the theme.
   * @param $new_name
   *   New name for the theme copy.
   */
  public function recursiveCopy($src, $dst, $old_name, $new_name) {
    $dir = opendir($src);
    mkdir($dst);
    while (FALSE !== ($file = readdir($dir))) {
      if (($file != '.') && ($file != '..')) {
        if (is_dir($src . '/' . $file)) {
          $this->recurseCopy($src . '/' . $file, $dst . '/' . $file, $old_name, $new_name);
        }
        else {
          $file_contents = file_get_contents($src . '/' . $file);
          $file_contents = str_replace($old_name, $new_name, $file_contents);
          if (strpos($file, $old_name) != -1) {
            $file = str_replace($old_name, $new_name, $file);
          }
          file_put_contents($dst . '/' . $file, $file_contents);
        }
      }
    }
    closedir($dir);
  }

  /**
   * Renames the theme name of the new theme copy for proper functionality.
   *
   * @param $file_name
   *   The file name in which the theme will be renamed.
   * @param $theme_name
   *   The new theme name.
   */
  public function changeThemeInfo($file_name, $theme_name) {
    $theme_info = Yaml::parse(file_get_contents($file_name));
    $theme_info['name'] = $theme_name . ' (Multidomain)';
    $theme_info['description'] = $theme_name . ' theme, created from multidomain module.';
    $yaml = Yaml::dump($theme_info);
    @file_put_contents($file_name, $yaml);
  }

  /**
   *
   */
  public function createStylingProfile(string $label, string $id) {
    $profile = StylingProfile::create(['id' => $id, 'label' => $label]);
    $profile->save();
    $stylingProfileThemeSwitch = $moduleHandler->moduleExists('styling_profiles_domain_switch');
    if ($stylingProfileThemeSwitch) {
      $config = \Drupal::service('config.factory')->getEditable('styling_profiles_domain_switch.settings');
      $config->set($id, $profile->id());
      $config->save(); 
    }
  }

  /**
   *
   */
  public function processDomainForm($form, FormStateInterface $form_state) {
    if ($form['#isnew']) {
      $label = $form_state->getValue('name');
      $id = $form_state->getValue('id');
      $hostname = $form_state->getValue('hostname');
      try {
        $this->createIngress($hostname);
      }
      catch (Exception $e) {
        $this->messenger->addMessage('Could not find or create ingress.');
        $this->messenger->addMessage($e->getResponse()->getBody()->getContents());
      }

      if ($form_state->getValue('create_menu')) {
        $this->addMenu($label, 'multidomain_' . $id, $form_state->getValue('menu_content_types'));
      }

      $moduleHandler = \Drupal::service('module_handler');
      $domainThemeSwitch = $moduleHandler->moduleExists('domain_theme_switch');
      $stylingProfileThemeSwitch = $moduleHandler->moduleExists('styling_profiles_domain_switch');
      if ($form_state->getValue('create_styling_profile') && $stylingProfileThemeSwitch) {
        $this->createStylingProfile($label, $id . '_site');
      }
      if ($form_state->getValue('copy_theme') && $domainThemeSwitch) {
        // $this->copyBaseTheme('multidomain_' . $id);
      }

      if (!empty($this->notificationEmail)) {
        // Send an email to notify about setting up the new domain.
        $mailManager = \Drupal::service('plugin.manager.mail');
        $user = \Drupal::currentUser();
        $module = 'iq_multidomain_extensions';
        $key = 'q_multidomain_extensions_create_domain';
        $to = $this->notificationEmail;
        $baseDomain = \Drupal::service('entity_type.manager')->getStorage('domain')->loadDefaultDomain();
        $params['message'] = 'A new domain has been created on ' . $baseDomain->getHostname() . '. <br />New hostname ' . $hostname . ' and label ' . $label . '.';
        $params['domain_title'] = $label;
        if (isset($to) && !empty($to)) {
          $result = $mailManager->mail($module, $key, $to, 'en', $params, NULL, TRUE);
          if ($result['result'] !== TRUE) {
            $this->messenger->addMessage(t('There was a problem sending your message and it was not sent.'), 'error');
          }
          else {
            $this->messenger->addMessage(t('An email has been sent for the newly created domain.'));
          }
        }
      }
    }
  }

}
