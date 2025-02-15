<?php
/**
 * Get menu of a flow.
 *
 * @param  object $flow
 * @param  array  $labels
 * @param  array  $categories
 * @access public
 * @return void
 */
public function getModuleMenu($flow, $labels = array(), $categories = array())
{
    return $this->loadExtension('flow')->getModuleMenu($flow, $labels, $categories);
}

/**
 * Post data of a flow.
 *
 * @param  object $flow
 * @param  object $action
 * @param  int    $dataID
 * @param  string $prevModule
 * @access public
 * @return array
 */
public function post($flow, $action, $dataID = 0, $prevModule = '')
{
    return $this->loadExtension('flow')->post($flow, $action, $dataID, $prevModule);
}

/**
 * Print workflow defined fields for view and form page.
 *
 * @access public
 * @param  string $moduleName
 * @param  string $methodName
 * @param  object $object
 * @param  string $type
 * @param  string $extras
 * @return void
 */
public function printFields($moduleName, $methodName, $object, $type, $extras = '')
{
    return $this->loadExtension('flow')->printFields($moduleName, $methodName, $object, $type, $extras);
}

/**
 * Get field value.
 *
 * @param  string $field
 * @param  object $object
 * @access public
 * @return string
 */
public function getFieldValue($field, $object)
{
    return $this->loadExtension('flow')->getFieldValue($field, $object);
}

/**
 * Print workflow defined fields for browse page.
 *
 * @access public
 * @param  string $module
 * @param  object $object
 * @param  string $id
 * @param  bool   $returnResult  true|false
 * @return void
 */
public function printFlowCell($module, $object, $id, $returnResult = false)
{
    return $this->loadExtension('flow')->printFlowCell($module, $object, $id, $returnResult);
}

/**
 * Import from excel.
 *
 * @param  object $flow
 * @access public
 * @return array
 */
public function import($flow)
{
    return $this->loadExtension('flow')->import($flow);
}

/**
 * Get extend fields.
 *
 * @param  string $module
 * @param  string $method
 * @access public
 * @return array
 */
public function getExtendFields($module, $method)
{
    return $this->loadExtension('flow')->getExtendFields($module, $method);
}

/**
 * getFieldControl
 *
 * @param  object $field
 * @param  object $object
 * @param  string $controlName
 * @param  string $chosen
 * @access public
 * @return string
 */
public function getFieldControl($field, $object, $controlName = '', $picker = 'picker-select')
{
    return $this->loadExtension('flow')->getFieldControl($field, $object, $controlName, $picker);
}

public function buildControl($field, $fieldValue, $element = '', $childModule = '', $emptyValue = false, $preview = false)
{
    return $this->loadExtension('flow')->buildControl($field, $fieldValue, $element, $childModule, $emptyValue, $preview);
}

public function checkLabel($flow, $labels, $label)
{
    if($flow->buildin) return true;
    return parent::checkLabel($flow, $labels, $label);
}

public function getDataByID($flow, $dataID, $decode = true)
{
    return $this->loadExtension('flow')->getDataByID($flow, $dataID, $decode);
}

public function buildOperateMenu($flow, $data, $type = 'browse')
{
    return $this->loadExtension('flow')->buildOperateMenu($flow, $data, $type);
}

public function buildActionMenu($moduleName, $action, $data, $type = 'browse', $relations = array())
{
    return $this->loadExtension('flow')->buildActionMenu($moduleName, $action, $data, $type, $relations);
}

public function getDataList($flow, $mode = 'browse', $label = 0, $categoryQuery = '', $parentID = 0, $orderBy = '', $pager = null, $extraQuery = '')
{
    return $this->loadExtension('flow')->getDataList($flow, $mode, $label, $categoryQuery, $parentID, $orderBy, $pager, $extraQuery);
}

public function processDBData($module, $data, $decode = true)
{
    return $this->loadExtension('flow')->processDBData($module, $data, $decode);
}

public function setFlowChild($module, $action, $fields, $dataID = 0)
{
    return $this->loadExtension('flow')->setFlowChild($module, $action, $fields, $dataID);
}

public function sendNotice($flow, $action, $result)
{
    return $this->loadExtension('flow')->sendNotice($flow, $action, $result);
}

public function sendmail($flow, $method, $noticeUsers, $dataID, $actionID)
{
    return $this->loadExtension('flow')->sendmail($flow, $method, $noticeUsers, $dataID, $actionID);
}

public function checkRules($fields, $data, $dao, $dataID = 0)
{
    return $this->loadExtension('flow')->checkRules($fields, $data, $dao, $dataID);
}

public function checkPrivilege($flow, $action)
{
    return $this->loadExtension('flow')->checkPrivilege($flow, $action);
}

public function formatDataByType($chart, $datas, $fieldList)
{
    return $this->loadExtension('flow')->formatDataByType($chart, $datas, $fieldList);
}

/**
 * Build batch actions of a flow.
 *
 * @param  string $moduleName
 * @access public
 * @return string
 */
public function buildBatchActions($moduleName)
{
    return $this->loadExtension('flow')->buildBatchActions($moduleName);
}

/**
 * Get script by linkages of an action.
 *
 * @param  object $action
 * @param  array  $fields
 * @access public
 * @return string
 */
public function getLinkageScript($action, $fields)
{
    return $this->loadExtension('flow')->getLinkageScript($action, $fields);
}
