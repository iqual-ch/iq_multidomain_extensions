<?php

namespace Drupal\iq_multidomain_extensions\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\Role;
use Drupal\user\Entity\User;
use Drupal\user\RoleInterface;

/**
 * Class RancherForm.
 *
 * @package Drupal\iq_multidomain_extensions\Form
 */
class RancherForm extends ConfigFormBase
{

    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return 'rancher_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $config = $this->config('iq_multidomain_extensions.rancher_settings');
//      $username = $config->get('username');
				$base_theme = $config->get('base_theme');
				$rancher_endpoint = $config->get('rancher_endpoint');

//      $password = $config->get('password');

/*        $form['username'] = [
            '#type' => 'textfield',
            '#title' => 'Username',
            '#description' => $this->t('Add a valid username for the Rancher API'),
            '#default_value' => isset($username) ? $username : '',
        ];
        $form['password'] = [
            '#type' => 'password',
            '#title' => 'Password',
            '#description' => $this->t('Add a valid password for the Rancher API'),
            '#default_value' => isset($password) ? $password : '',
        ];*/

				$form['base_theme'] = [
					'#type' => 'textfield',
					'#title' => 'Base theme',
					'#description' => $this->t('Name of the base theme to be copied.'),
					'#default_value' => isset($base_theme) ? $base_theme : '',
				];
				$form['rancher_endpoint'] = [
					'#type' => 'textfield',
					'#title' => 'Rancher endpoint',
					'#description' => $this->t('The rancher api endpoint.'),
					'#default_value' => isset($rancher_endpoint) ? $rancher_endpoint : '',
				];

        $form['actions']['#type'] = 'actions';
        $form['actions']['submit'] = [
            '#type' => 'submit',
            '#value' => $this->t('Save'),
            '#button_type' => 'primary',
        ];

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $this->config('iq_multidomain_extensions.rancher_settings')
//            ->set('username', $form_state->getValue('username'))
//            ->set('password', $form_state->getValue('password'))
						->set('base_theme', $form_state->getValue('base_theme'))
						->set('rancher_endpoint', $form_state->getValue('rancher_endpoint'))
            ->save();

        parent::submitForm($form, $form_state);
    }

    /**
     * Get Editable config names.
     *
     * @inheritDoc
     */
    protected function getEditableConfigNames()
    {
        return ['iq_multidomain_extensions.rancher_settings'];
    }

}
