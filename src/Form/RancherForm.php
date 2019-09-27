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
        $username = $config->get('username');
        $password = $config->get('password');

        $form['username'] = [
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
            ->set('username', $form_state->getValue('username'))
            ->set('password', $form_state->getValue('password'))
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
