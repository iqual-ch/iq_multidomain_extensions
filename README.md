# iq_multidomain_extensions

Contains extensions, config etc. for a multidomain setup

Installation guide

1. get domain modules
composer require drupal/domain
composer require drupal/domain_theme_switch
composer require drupal/domain_site_settings

2. uninstall UI patterns Library & Ui patterns for pagedesigner.

3. Install iq_multidomain_extensions

4. add domain records
/admin/config/domain

5. choose themes for domains
/admin/config/domain/domain_theme_switch/config

IMPORTANT NOTES
- Make sure that the pattern ids and template names have to be identical (Pay attention to - and _)
