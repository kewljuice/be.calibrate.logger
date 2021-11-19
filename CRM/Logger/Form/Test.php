<?php

use CRM_Logger_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/quickform/
 */
class CRM_Logger_Form_Test extends CRM_Core_Form {

  public function buildQuickForm() {

    // Set severity log limit
    $oClass = new ReflectionClass('Psr\Log\LogLevel');
    $levels = $oClass->getConstants();

    $this->add(
      'select',
      'logger_severity_limit',
      'Severity level',
      $levels,
      TRUE
    );

    $this->addButtons([
      [
        'type' => 'submit',
        'name' => E::ts('Test'),
        'isDefault' => TRUE,
      ],
    ]);

    // export form elements
    $this->assign('elementNames', $this->getRenderableElementNames());
    parent::buildQuickForm();
  }

  public function postProcess() {
    $values = $this->exportValues();
    $level = $values['logger_severity_limit'];
    CRM_Core_Session::setStatus(E::ts('You picked Severity level "%1"', [
      1 => $level,
    ]));
    \Civi::log()->{$level}("Executed test for be.calibrate.logger extension.");
    parent::postProcess();
  }

  /**
   * Get the fields/elements defined in this form.
   *
   * @return array (string)
   */
  public function getRenderableElementNames() {
    // The _elements list includes some items which should not be
    // auto-rendered in the loop -- such as "qfKey" and "buttons".  These
    // items don't have labels.  We'll identify renderable by filtering on
    // the 'label'.
    $elementNames = [];
    foreach ($this->_elements as $element) {
      /** @var HTML_QuickForm_Element $element */
      $label = $element->getLabel();
      if (!empty($label)) {
        $elementNames[] = $element->getName();
      }
    }
    return $elementNames;
  }

}
