<?php

/**
 * BasecsSettingsActions 
 * 
 * @uses autocsSettingsActions
 * @package 
 * @version $id$
 * @copyright 2006-2007 Brent Shaffer
 * @author Brent Shaffer <bshaffer@centresource.com>
 * @license See LICENSE that came packaged with this software
 */
class BasecsSettingActions extends AutocsSettingActions
{ 
  public function executeIndex(sfWebRequest $request)
  {
    $this->form = new SettingsListForm();
    return parent::executeIndex($request);
  }
  
  public function executeListSaveSettings(sfWebRequest $request)
  {
    //print_r($_POST);
    //die();
    //self::executeIndex($request);
    $changed = 0;
    
    if ($settings = $request->getParameter('cs_setting'))
    {
      foreach (Doctrine::getTable('csSetting')->findAllForList() as $setting)
      {
	if (isset($settings[$setting->getSlug()]))
	{
	  $form = new csSettingAdminForm($setting);
	    $setting->setValue($form->getSettingValidator()->clean($settings[$setting->getSlug()]));
	    $setting->save();
	    $changed = 1;
	}
      }
    }
    
    if ($changed)
      $this->getUser()->setFlash('notice', 'Your settings have been saved.');
    
    $this->redirect($request->getReferer());
    
      /*$this->form = new SettingsListForm();
      foreach ($settings as $key => $value)
      {
	$this->form[$key]->setDefault($value); /*foreach($this->form->getValues() as $slug => $value)
        {
          $setting = Doctrine::getTable('csSetting')->findOneBySlug($slug);
          if ($setting) 
          {
            // https://github.com/bshaffer/csSettingsPlugin/issues/8 workaround
            if (!is_array($value)) {
              $setting->setValue($value);
              $setting->save();
            }
            $setting->setValue($value);
            $setting->save();
          }
        }* /
      }
      //$this->form->bind($settings, $request->getfiles('cs_setting'));
      /*if ($this->form->isValid()) 
      {
        
        echo 'valid';
        /*if($files = $request->getFiles('cs_setting'))
        {
          $this->processUpload($settings, $files);
        }* /
        
        // Update form with new values
        //$this->form = new SettingsListForm();
/*
        
      }
      else
      {
        $this->getUser()->setFlash('error', 'Your form contains some errors');
      }
    }
    $this->setTemplate('index');*/
  }
  
  public function executeListRestoreDefault(sfWebRequest $request)
  {
    Doctrine::getTable('csSetting')->restoreDefault($request->getParameter('id'));
    
    $this->redirect($request->getReferer());
  }
  
  public function executeRestoreAllDefaults(sfWebRequest $request)
  {
    Doctrine::getTable('csSetting')->restoreAllDefaults();
    
    $this->redirect($request->getReferer());
  }
  
  public function processUpload($settings, $files)
  {
    $default_path = csSettings::getDefaultUploadPath();
    
    foreach ($files as $slug => $file) 
    {
      if ($file['name']) 
      {
        $setting = Doctrine::getTable('csSetting')->findOneBySlug($slug);
        
        $target_path = $setting->getOption('upload_path');
        
        $target_path = $target_path ? $target_path : $default_path;
        
        //If target path does not exist, attempt to create it
        if(!file_exists($target_path))
        {
          $target_path = mkdir($target_path) ? $target_path : 'uploads';
        }
        
        $target_path = $target_path . DIRECTORY_SEPARATOR . basename( $file['name']); 
        
        if(!move_uploaded_file($file['tmp_name'], $target_path)) 
        {
          $this->getUser()->setFlash('error', 'There was a problem uploading your file!');
        }
        else
        {  
          $setting->setValue(basename($file['name']));
          $setting->save();
        }
      }
      elseif (isset($settings[$slug.'_delete'])) 
      {
        $setting = Doctrine::getTable('csSetting')->findOneBySlug($slug);
        unlink($setting->getUploadPath().'/'.$setting->getValue());
        $setting->setValue('');
        $setting->save();
      }
    }
  }
}
