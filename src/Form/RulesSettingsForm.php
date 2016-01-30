<?php

/**
 * @file
 * Contains \Drupal\rules\Form\RulesSettingsForm.
 */

namespace Drupal\rules\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfigFormBase;
use Psr\Log\LogLevel;

/**
 * Provides rules settings form.
 */
class RulesSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'rules_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['rules.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('rules.settings');

    $form['log'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Log debug information to the system log'),
      '#default_value' => $config->get('log'),
    ];
    $form['log_level'] = [
      '#type' => 'radios',
      '#title' => $this->t('Log level'),
      '#options' => [
        LogLevel::WARNING => $this->t('Log all warnings and errors'),
        LogLevel::ERROR => $this->t('Log errors only'),
      ],
      '#default_value' => $config->get('log_level') ? $config->get('log_level') : LogLevel::WARNING,
      '#description' => $this->t('Evaluations errors are logged to available loggers.'),
      '#states' => [
        // Hide the log_level radios when the debug log is disabled.
        'invisible' => [
          'input[name="log"]' => ['checked' => FALSE],
        ],
      ],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('rules.settings')
      ->set('log', $form_state->getValue('log'))
      ->set('log_level', $form_state->getValue('log_level'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
