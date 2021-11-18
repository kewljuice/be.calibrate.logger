<?php

use CRM_Logger_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_Logger_Form_Settings extends CRM_Core_Form {

  public function buildQuickForm() {
    $defaults = Civi::settings()->get('logger-settings');
    if ($defaults != NULL) {
      $values = json_decode(utf8_decode($defaults), TRUE);
    }
    else {
      $values = [];
      $values['logger_severity_limit'] = NULL;
    }

    \Civi::log()->alert("Settings page loaded");

    // Set severity log limit
    $oClass = new ReflectionClass('Psr\Log\LogLevel');
    $levels = $oClass->getConstants();

    $select = $this->add(
      'select',
      'logger_severity_limit',
      'Severity level to log from',
      $levels,
      TRUE
    );
    $select->setSelected(($values['logger_severity_limit'] !== NULL) ?: 'DEBUG');

    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => E::ts('Submit'),
        'isDefault' => TRUE,
      ),
    ));

    $this->assign('elementNames', $this->getRenderableElementNames());
    parent::buildQuickForm();
  }

  /**
   * Save the settings form.
   */
  public function postProcess() {
    // Get the submitted values as an array
    $values = $this->controller->exportValues($this->_name);
    $credentials['logger_severity_limit'] = $values['logger_severity_limit'];
    $encode = json_encode($credentials);
    try {
      Civi::settings()->set('logger-settings', $encode);
    } catch (Exception $e) {
      \Civi::log()
        ->debug("CRM_Eavesdropper_Form_Settings Error: " . $e->getMessage());
    }
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
    $elementNames = array();
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
