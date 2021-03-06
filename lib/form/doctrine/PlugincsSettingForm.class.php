<?php

/**
 * PlugincsSetting form.
 *
 * @package    form
 * @subpackage csSetting
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 6174 2007-11-27 06:22:40Z fabien $
 */
abstract class PlugincsSettingForm extends BasecsSettingForm
{
  public function getSettingWidget()
  {
    $type = $this->getObject()->getType();
    $name = $this->getObject()->getName();
    
    // See if there is a widget specific to this setting
    $method = 'get'.sfInflector::camelize($name).'SettingWidget';
    if (method_exists($this, $method))
    {
      return $this->$method();
    }
    // Else, see if there is a widget specific to this setting's type
    $method = 'get'.sfInflector::camelize($type).'SettingWidget';
    if (method_exists($this, $method))
    {
      return $this->$method();
    }
    // Return a generic Widget
    return new sfWidgetFormInput(array(), $this->getObject()->getOptionsArray());
  }

  public function getSettingValidator()
  {
    $type = $this->getObject()->getType();
    $name = $this->getObject()->getName();
    // See if there is a validator specific to this setting
    $method = 'get'.sfInflector::camelize($name).'SettingValidator';
    if (method_exists($this, $method))
    {
      return $this->$method();
    }
    // Else, see if there is a validator specific to this setting's type
    $method = 'get'.sfInflector::camelize($type).'SettingValidator';
    if (method_exists($this, $method))
    {
      return $this->$method();
    }
    // Return a generic Validator    
    return new sfValidatorString(array('required' => false));
  }

  public function getRichTextSettingWidget()
  {
    if(class_exists('sfWidgetFormCKEditor'))
     return new sfWidgetFormCKEditor(array(), $this->getObject()->getOptionsArray());
    else
      return new sfWidgetFormTextarea(array(), $this->getObject()->getOptionsArray());
  }
  
  //Type Textarea
  public function getTextareaSettingWidget()
  {
    return new sfWidgetFormTextarea(array(), $this->getObject()->getOptionsArray());
  }
  
  // Type Checkbox
  public function getCheckboxSettingWidget()
  {
    return new sfWidgetFormInputCheckbox(array(), $this->getObject()->getOptionsArray());
  }

  // Type Date
  public function getDateTimeSettingWidget()
  {
    return new sfWidgetFormDateTime($this->getObject()->getOptionsArray());
  }
  public function getDateTimeSettingValidator()
  {
    return new sfValidatorDateTime(array('required' => false));
  }
  
  // Type Yesno
  public function getYesnoSettingWidget()
  {
    return new sfWidgetFormSelectRadio(array('choices' => array('yes' => 'Yes', 'no' => 'No')), $this->getObject()->getOptionsArray());
  }
  public function getYesnoSettingValidator()
  {
    return new sfValidatorChoice(array('choices' => array('yes', 'no'), 'required' => false));
  }
  
  //Type Select List
  public function getSelectSettingWidget()
  {
    return new sfWidgetFormSelect(array('choices' => $this->getObject()->getOptionsArray()));
  }
  public function getSelectSettingValidator()
  {
    return new sfValidatorChoice(array('choices' => array_keys($this->getObject()->getOptionsArray()), 'required' => false));
  }
  
  //Type Model
  public function getModelSettingWidget()
  {
    return new sfWidgetFormDoctrineChoice(array_intersect_key($this->getObject()->getOptionsArray(), array_count_values(array('model', 'add_empty', 'method', 'key_method', 'order_by', 'query', 'multiple', 'table_method'))));
  }
  public function getModelSettingValidator()
  {
    return new sfValidatorDoctrineChoice(array_intersect_key($this->getObject()->getOptionsArray(), array_count_values(array('model', 'query', 'column', 'multiple', 'min', 'max'))));
  }
  
  //Type Upload
  public function getUploadSettingWidget()
  {
    $path = $this->getObject()->getUploadPath() . '/' . $this->getObject()->getValue();
    $options = array(
          'file_src' => $this->getObject()->getValue(),
          'template' => "%file%<br />%input%<br />%delete% %delete_label%",
      );
    
    // If you want to pass the widget custom settings, you can override in your setting's options  
    $options = array_merge($options, $this->getObject()->getOptionsArray());
    unset($options['upload_path'], $options['mime_types']);
    
    return new sfWidgetFormInputFileEditable($options);
  }
  
  public function getUploadSettingValidator()
  {
    $mime_types = 'web_images';
    $opts = $this->getObject()->getOptionsArray();
    if (isset($opts['mime_types']))
      $mime_types = is_array($opts['mime_types']) ? $opts['mime_types'] : array($opts['mime_types']);
    return new sfValidatorFile(array(
      'path' => $this->getObject()->getUploadPath(),
      'mime_types' => $mime_types,
      'required' => false,
      ));
  }
  
  // Overriding Bind in this case allows us to have the form field "setting_group_new" for usability
  public function bind(array $taintedValues = null, array $taintedFiles = null)
  {
    $taintedValues['setting_group'] = (isset($taintedValues['setting_group_new']) && $taintedValues['setting_group_new']) ?  $taintedValues['setting_group_new'] : $taintedValues['setting_group'];
    unset($taintedValues['setting_group_new']);
    $ret = parent::bind($taintedValues, $taintedFiles);
    return $ret;
  }
}