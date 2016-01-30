<?php

/**
 * @file
 * Contains \Drupal\rules\Core\ExecutablePluginTrait.
 */

namespace Drupal\rules\Core;

use Drupal\rules\Exception\RulesInvalidPluginDefintionException;

/**
 * Offers common methods for executable plugins.
 */
trait ExecutablePluginTrait {

  /**
   * Get the translated label from the plugin definition.
   *
   * @throws \Drupal\rules\Exception\RulesInvalidPluginDefintionException
   *   Thrown if the label is not defined for the plugin.
   *
   * @return string
   *   The label of the plugin.
   */
  protected function getLabelValue() {
    $definition = $this->getPluginDefinition();
    if (empty($definition['label'])) {
      throw new RulesInvalidPluginDefintionException('The label is not defined for plugin ' . $this->getPluginId() . ' (' . __CLASS__ . ').');
    }
    // The label can be an instance of
    // \Drupal\Core\StringTranslation\TranslationWrapper here, so make sure to
    // always return a primitive string representation here.
    return (string) $definition['label'];
  }

  /**
   * Get the translated summary from the label annotation.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   *   Thrown if a summary was not set.
   *
   * @return string
   *   The summary of the plugin.
   */
  public function summary() {
    return $this->getLabelValue();
  }

}
