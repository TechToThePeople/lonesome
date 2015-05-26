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
      
    CRM_Utils_System::setTitle(ts('Contacts without groups or/and tags'));
    
    // todo add validation: at least one of them have to be checked
    $form->addElement('checkbox', 'without_groups', ts('Contacts without groups'));
    $form->addElement('checkbox', 'without_tags', ts('Contacts without tags'));
    
    $form->assign('elements', array(
      'without_groups',
      'without_tags',
    ));
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
    // return array(
    //   'summary' => 'This is a summary',
    //   'total' => 50.0,
    // );
  }

  /**
   * Get a list of displayable columns
   *
   * @return array, keys are printable column headers and values are SQL column names
   */
  function &columns() {
    // return by reference
    $columns = array(
      ts('Contact Id') => 'contact_id',
      ts('Contact Type') => 'contact_sub_type',
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
    if (!$justIDs || !$sort) {
      $sort = 'created_date DESC';
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
      contact_a.id           as contact_id  ,
      contact_a.contact_sub_type as contact_sub_type,
      contact_a.sort_name    as sort_name,
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
      
    $withoutGroups = CRM_Utils_Array::value('without_groups',
      $this->_formValues
    );
    $whereWithoutGroups = '';
    if ($withoutGroups){
      $whereWithoutGroups = "
        AND NOT EXISTS (
            SELECT 1 FROM
            civicrm_group_contact c2
            WHERE c2.contact_id = contact_a.id)
        AND NOT EXISTS ( -- dont forget to check smart groups
            SELECT 1 FROM
            civicrm_group_contact_cache c3
            WHERE c3.contact_id = contact_a.id)
        ";
    }
    
    $withoutTags = CRM_Utils_Array::value('without_tags',
      $this->_formValues
    );
    $whereWithoutTags = '';
    if ($withoutTags) {
      $whereWithoutTags .= " AND NOT EXISTS (SELECT 1 FROM civicrm_entity_tag c4 WHERE c4.entity_table = 'civicrm_contact' AND c4.entity_id = contact_a.id)";
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
   * Modify the content of each row
   *
   * @param array $row modifiable SQL result row
   * @return void
   */
/*  function alterRow(&$row) {
    $row['sort_name'] .= ' ( altered )';
  }
 */
}
