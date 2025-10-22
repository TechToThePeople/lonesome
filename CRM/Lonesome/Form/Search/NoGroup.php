<?php

/**
 * A custom contact search
 */
class CRM_Lonesome_Form_Search_NoGroup extends CRM_Contact_Form_Search_Custom_Base implements CRM_Contact_Form_Search_Interface {
  function __construct(&$formValues) {
    parent::__construct($formValues);

  }
    
  /**
   * Prepare a set of search fields
   *
   * @param CRM_Core_Form $form modifiable
   * @return void
   */
  function buildForm(&$form) {
      
    CRM_Utils_System::setTitle(ts('Contacts without groups and/or tags'));
    
    $form->addElement('checkbox', 'without_groups', ts('Contacts without groups'));
    $form->addElement('checkbox', 'without_tags', ts('Contacts without tags'));
    
    $form->setDefaults(array(
      'without_groups' => true,
      'without_tags' => true
    ));
    $form->assign('elements', array(
      'without_groups',
      'without_tags',
    ));
    
    $form->addFormRule(array('CRM_Lonesome_Form_Search_NoGroup', 'formRule'), $form);
  }

  /**
   * Get a list of summary data points
   *
   * @return mixed; NULL or array with keys:
   *  - summary: string
   *  - total: numeric
   */
  function summary() {
    return NULL;
  }

  /**
   * Get a list of displayable columns
   *
   * @return array, keys are printable column headers and values are SQL column names
   */
  function &columns() {
    // return by reference
    $columns = array(
      ts('Contact Sub&nbsp;Type') => 'contact_sub_type',
      ts('Name') => 'sort_name',
      ts('Source') => 'source',
      ts('Created') => 'created_date',
      ts('Modified') => 'modified_date',
    );
    return $columns;
  }

  /**
   * Construct a full SQL query which returns one page worth of results
   *
   * @param int $offset
   * @param int $rowcount
   * @param null $sort
   * @param bool $includeContactIDs
   * @param bool $justIDs
   * @return string, sql
   */
  function all($offset = 0, $rowcount = 0, $sort = NULL, $includeContactIDs = FALSE, $justIDs = FALSE) {
    if (empty($sort)) {
      $sort = ' created_date DESC ';
    }    
    return $this->sql($this->select(), $offset, $rowcount, $sort, $includeContactIDs, NULL);
  }

  /**
   * Construct a SQL SELECT clause
   *
   * @return string, sql fragment with SELECT arguments
   */
  function select() {
    return "
      contact_a.id AS contact_id,
      contact_a.contact_sub_type AS contact_sub_type,
      contact_a.sort_name AS sort_name,
      source,
      created_date,
      modified_date
      ";
  }
  
  /**
   * Construct a SQL FROM clause
   *
   * @return string, sql fragment with FROM and JOIN clauses
   */
  function from() {
    return "FROM civicrm_contact contact_a";
  }

  /**
   * Construct a SQL WHERE clause
   *
   * @param bool $includeContactIDs
   * @return string, sql fragment with conditional expressions
   */
  function where($includeContactIDs = FALSE) {
      
    $withoutGroups = $this->_formValues['without_groups'] ?? NULL;
    $whereWithoutGroups = '';
    if ($withoutGroups){
      $whereWithoutGroups = "
        AND NOT EXISTS (
            SELECT 1 FROM
            civicrm_group_contact c2
            WHERE c2.contact_id = contact_a.id)
        AND NOT EXISTS (
            SELECT 1 FROM
            civicrm_group_contact_cache c3
            WHERE c3.contact_id = contact_a.id)
        ";
    }
    
    $withoutTags = $this->_formValues['without_tags'] ?? NULL;
    $whereWithoutTags = '';
    if ($withoutTags) {
      $whereWithoutTags .= "
        AND NOT EXISTS (
          SELECT 1 
          FROM civicrm_entity_tag c4 
          WHERE c4.entity_table = 'civicrm_contact' AND c4.entity_id = contact_a.id
        )";
    }
    
    $where = "
    contact_a.contact_type = 'Individual' 
    AND contact_a.is_deleted = 0 ".$whereWithoutGroups.$whereWithoutTags;

    $params = array();
    return $this->whereClause($where, $params);
  }

  /**
   * Determine the Smarty template for the search screen
   *
   * @return string, template path (findable through Smarty template path)
   */
  function templateFile() {
    return 'CRM/Contact/Form/Search/Custom.tpl';
  } 
  
  /**
   * Global validation rules for the form.
   *
   * @param array $values
   *   Posted values of the form.
   *
   * @param $files
   * @param CRM_Core_Form $form
   *
   * @return array
   *   list of errors to be posted back to the form
   */
  public static function formRule($values, $files, $form) {
    $errors = array();
    if (!array_key_exists('without_groups', $values) &&
        !array_key_exists('without_tags', $values))
    {
      if (!array_key_exists('without_groups', $values)) {
        $errors['without_groups'] = ts('At least one checkbox have to be checked');
      }
      if (!array_key_exists('without_tags', $values)) {
        $errors['without_tags'] = ts('At least one checkbox have to be checked');
      }
    }
    
    return empty($errors) ? true : $errors;
  }
  
}
