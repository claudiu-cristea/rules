<?php

/**
 * @file
 * Contains \Drupal\rules\Context\ContextDefinition.
 */

namespace Drupal\rules\Context;

use Drupal\Core\Plugin\Context\ContextDefinition as ContextDefinitionCore;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Extends the core context definition class with useful methods.
 */
class ContextDefinition extends ContextDefinitionCore implements ContextDefinitionInterface {

  /**
   * The mapping of config export keys to internal properties.
   *
   * @var array
   */
  protected static $nameMap = [
    'type' => 'dataType',
    'label' => 'label',
    'description' => 'description',
    'multiple' => 'isMultiple',
    'required' => 'isRequired',
    'default_value' => 'defaultValue',
    'constraints' => 'constraints',
    'allow_null' => 'allowNull',
    'assignment_restriction' => 'assignmentRestriction',
  ];

  /**
   * Whether the context value is allowed to be NULL or not.
   *
   * @var bool
   */
  protected $allowNull = FALSE;

  /**
   * The assignment restriction of this context.
   *
   * @see \Drupal\rules\Context\ContextDefinitionInterface::getAssignmentRestriction()
   *
   * @var string|null
   */
  protected $assignmentRestriction = NULL;

  /**
   * Exports the definition as an array.
   *
   * @return array
   *   An array with values for all definition keys.
   */
  public function toArray() {
    $values = [];
    $defaults = get_class_vars(__CLASS__);
    foreach (static::$nameMap as $key => $property_name) {
      // Only export values for non-default properties.
      if ($this->$property_name !== $defaults[$property_name]) {
        $values[$key] = $this->$property_name;
      }
    }
    return $values;
  }

  /**
   * Creates a definition object from an exported array of values.
   *
   * @param array $values
   *   The array of values, as returned by toArray().
   *
   * @return static
   *   The created definition.
   */
  public static function createFromArray($values) {
    if (isset($values['class']) && !in_array('Drupal\rules\Context\ContextDefinitionInterface', class_implements($values['class']))) {
      throw new \Exception('ContextDefinition class must implement \Drupal\rules\Context\ContextDefinitionInterface.');
    }

    // Annotation classes extract data from passed annotation classes directly
    // used in the classes they pass to.
    foreach (['label', 'description'] as $key) {
      // @todo Remove this workaround in https://www.drupal.org/node/2362727.
      if (isset($values[$key])) {
        if ($values[$key] instanceof TranslatableMarkup) {
          $values[$key] = (string) $values[$key];
        }
        elseif (!is_string($values[$key])) {
          $values[$key] = NULL;
        }
      }
    }

    // Default to Rules context definition class.
    $values['class'] = isset($values['class']) ? $values['class'] : '\Drupal\rules\Context\ContextDefinition';
    if (!isset($values['type'])) {
      $values['type'] = 'any';
    }

    $definition = $values['class']::create($values['type']);
    foreach (array_intersect_key(static::$nameMap, $values) as $key => $name) {
      $definition->$name = $values[$key];
    }
    return $definition;
  }

  /**
   * {@inheritdoc}
   */
  public function isAllowedNull() {
    return $this->allowNull;
  }

  /**
   * {@inheritdoc}
   */
  public function setAllowNull($null_allowed) {
    $this->allowNull = $null_allowed;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getAssignmentRestriction() {
    return $this->assignmentRestriction;
  }

  /**
   * {@inheritdoc}
   */
  public function setAssignmentRestriction($restriction) {
    $this->assignmentRestriction = $restriction;
    return $this;
  }

}
