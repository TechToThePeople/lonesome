<?php
// This file declares a managed database record of type "CustomSearch".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// http://wiki.civicrm.org/confluence/display/CRMDOC42/Hook+Reference
return array (
  0 => 
  array (
    'name' => 'CRM_Lonesome_Form_Search_NoGroup',
    'entity' => 'CustomSearch',
    'params' => 
    array (
      'version' => 3,
      'label' => 'NoGroup',
      'description' => 'Find contacts without groups or without tags',
      'class_name' => 'CRM_Lonesome_Form_Search_NoGroup',
    ),
  ),
);