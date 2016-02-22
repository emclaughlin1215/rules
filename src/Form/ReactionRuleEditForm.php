<?php

/**
 * @file
 * Contains \Drupal\rules\Form\ReactionRuleEditForm.
 */

namespace Drupal\rules\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\rules\Core\RulesEventManager;
use Drupal\rules\Core\RulesUiConfigHandler;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form to edit a reaction rule.
 */
class ReactionRuleEditForm extends RulesComponentFormBase {

  /**
   * The event plugin manager.
   *
   * @var \Drupal\rules\Core\RulesEventManager
   */
  protected $eventManager;

  /**
   * The RulesUI handler of the currently active UI.
   *
   * @var \Drupal\rules\Core\RulesUiConfigHandler
   */
  protected $rulesUiHandler;

  /**
   * Constructs a new object of this class.
   *
   * @param \Drupal\rules\Core\RulesEventManager $event_manager
   *   The event plugin manager.
   */
  public function __construct(RulesEventManager $event_manager) {
    $this->eventManager = $event_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('plugin.manager.rules_event'));
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, RulesUiConfigHandler $rules_ui_handler = NULL) {
    // Overridden such we can receive further route parameters.
    $this->rulesUiHandler = $rules_ui_handler;
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function prepareEntity() {
    parent::prepareEntity();
    // Replace the config entity with the latest entity from temp store, so any
    // interim changes are picked up.
    $this->entity = $this->rulesUiHandler->getConfig();
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $event_name = $this->entity->getEvent();
    $event_definition = $this->eventManager->getDefinition($event_name);
    $form['event']['#markup'] = $this->t('Event: @label (@name)', [
      '@label' => $event_definition['label'],
      '@name' => $event_name,
    ]);
    $form = $this->rulesUiHandler->getForm()->buildForm($form, $form_state);
    return parent::form($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    $this->rulesUiHandler->getForm()->validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);
    $actions['submit']['#value'] = $this->t('Save');
    $actions['cancel'] = [
      '#type' => 'submit',
      '#limit_validation_errors' => [['locked']],
      '#value' => $this->t('Cancel'),
      '#submit' => ['::cancel'],
    ];
    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $this->rulesUiHandler->getForm()->submitForm($form, $form_state);

    // Persist changes by saving the entity.
    parent::save($form, $form_state);

    // Also remove the temporarily stored component, it has been persisted now.
    $this->rulesUiHandler->clearTemporaryStorage();

    drupal_set_message($this->t('Reaction rule %label has been updated.', ['%label' => $this->entity->label()]));
  }

  /**
   * Form submission handler for the 'cancel' action.
   */
  public function cancel(array $form, FormStateInterface $form_state) {
    $this->rulesUiHandler->clearTemporaryStorage();
    drupal_set_message($this->t('Canceled.'));
    $form_state->setRedirect('entity.rules_reaction_rule.collection');
  }

  /**
   * Title callback: also display the rule label.
   */
  public function getTitle($rules_reaction_rule) {
    return $this->t('Edit reaction rule "@label"', ['@label' => $rules_reaction_rule->label()]);
  }

}
