<?php
/**
 * The control file of dataview module of ZenTaoPMS.
 *
 * @copyright   Copyright 2009-2023 禅道软件（青岛）有限公司(ZenTao Software (Qingdao) Co., Ltd. www.cnezsoft.com)
 * @license     ZPL (http://zpl.pub/page/zplv12.html)
 * @author      Chenxuan Song <chunsheng@cnezsoft.com>
 * @package     dataview
 * @version     $Id: control.php 5086 2023-06-06 02:25:22Z
 * @link        http://www.zentao.net
 */
class dataview extends control
{
    /**
     * AJAX: Get field name.
     *
     * @access public
     * @return void
     */
    public function ajaxGetFieldName()
    {
        $fields        = $this->post->fields;
        $fieldSettings = $this->post->fieldSettings;

        foreach($fields as $field => $fieldName)
        {
            if(isset($fieldSettings[$field]))
            {
                if(empty($fieldSettings[$field]['object']) or empty($fieldSettings[$field]['field'])) continue;

                $relatedObject = $fieldSettings[$field]['object'];
                $relatedField  = $fieldSettings[$field]['field'];

                $this->app->loadLang($relatedObject);
                $fields[$field] = isset($this->lang->$relatedObject->$relatedField) ? $this->lang->$relatedObject->$relatedField : $field;
            }
        }

        return $this->send(array('result' => 'success', 'fields' => $fields));
    }
}
