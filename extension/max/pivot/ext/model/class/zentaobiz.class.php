<?php
class zentaobizPivot extends pivotModel
{
    /**
     * Get pivot filed list by pivot design.
     *
     * @param  int    $pivotID
     * @access public
     * @return array
     */
    public function getPivotFieldList($pivotID)
    {
        $pivot     = $this->getByID($pivotID);
        $fieldList = array();
        if(isset($pivot->fieldSettings) and is_array($pivot->fieldSettings))
        {
            foreach($pivot->fieldSettings as $field => $fieldSetting)
            {
                $fieldList[$field] = $fieldSetting->name;
            }
        }

        return $fieldList;
    }

    /**
     * Create pivot.
     *
     * @param  int    $dimensionID
     * @access public
     * @return int
     */
    public function create($dimensionID)
    {
        $pivot = fixer::input('post')
            ->setDefault('dimension', $dimensionID)
            ->setDefault('group', '')
            ->setDefault('createdBy', $this->app->user->account)
            ->setDefault('createdDate', helper::now())
            ->setForce('name', '')
            ->setForce('desc', '')
            ->setForce('sql', '')
            ->join('group', ',')
            ->get();

        $pivot = $this->processNameAndDesc($pivot);

        $this->dao->insert(TABLE_PIVOT)->data($pivot)
            ->batchCheck($this->config->pivot->create->requiredFields, 'notempty')
            ->autoCheck()
            ->exec();

        return $this->dao->lastInsertID();
    }

    /**
     * Save query a pivot.
     *
     * @param int $pivotID
     * @access public
     * @return bool | int
     */
    public function querySave($pivotID)
    {
        $post = fixer::input('post')->skipSpecial('fields,objects,sql')->get();

        $this->dao->update(TABLE_PIVOT)
            ->set('sql')->eq($post->sql)
            ->set('fields')->eq($post->fields)
            ->set('settings')->eq('')
            ->where('id')->eq($pivotID)
            ->exec();

        if(dao::isError()) return false;
    }

    /**
     * Edit pivot basic field.
     *
     * @param  int $pivotID
     * @param  int $step
     * @access public
     * @return void
     */
    public function edit($pivotID)
    {
        $pivot = fixer::input('post')
            ->setDefault('group', '')
            ->setForce('name', '')
            ->setForce('desc', '')
            ->join('group', ',')
            ->get();

        $pivot = $this->processNameAndDesc($pivot);

        $this->dao->update(TABLE_PIVOT)
            ->data($pivot)
            ->batchCheck($this->config->pivot->design->requiredFields, 'notempty')
            ->where('id')->eq($pivotID)
            ->exec();

        if(dao::isError()) return false;
    }

    /**
     * Update pivot.
     *
     * @param  int $pivotID
     * @param  int $step
     * @access public
     * @return void
     */
    public function update($pivotID)
    {
        $pivot = fixer::input('post')
            ->skipSpecial('sql')
            ->remove('sqlChange,searchFilters')
            ->get();

        if(isset($pivot->settings['summary']) and $pivot->settings['summary'] == 'notuse')
        {
            unset($pivot->settings['group1']);
            unset($pivot->settings['group2']);
            unset($pivot->settings['group3']);
            unset($pivot->settings['columns']);
        }

        $_POST['name'] = $pivot->names;
        $_POST['desc'] = $pivot->descs;
        $pivot = $this->processNameAndDesc($pivot);

        $data = new stdclass();
        $data->name     = $pivot->name;
        $data->group    = $pivot->group;
        $data->desc     = $pivot->desc;
        $data->stage    = $pivot->stage;
        $data->settings = json_encode($pivot->settings);
        $data->fields   = json_encode($pivot->fieldSettings);
        $data->sql      = $pivot->sql;
        $data->langs    = !empty($pivot->langs) ? json_encode($pivot->langs) : '';
        $data->filters  = isset($pivot->filters) ? json_encode($pivot->filters) : '[]';

        if(isset($data->fields)) $data->fields = html_entity_decode($data->fields);

        $this->dao->update(TABLE_PIVOT)
            ->data($data)
            ->where('id')->eq($pivotID)
            ->batchCheck($this->config->pivot->design->requiredFields, 'notempty')
            ->exec();

        if(dao::isError()) return false;
    }

    /**
     * Process a pivot's name and description from the post data.
     *
     * @param  obejct $data
     * @access public
     * @return object
     */
    public function processNameAndDesc($data)
    {
        $clientLang = $this->app->getClientLang();

        if(empty($_POST['name'][$clientLang])) dao::$errors['name' . $clientLang][] = zget($this->config->langs, $clientLang) . sprintf($this->lang->error->notempty, $this->lang->pivot->name);
        if(dao::isError()) return $data;

        $names = array();
        foreach($this->post->name as $langKey => $name)
        {
            $name = trim($name);
            $names[$langKey] = htmlspecialchars($name);
        }

        if($names) $data->name = json_encode($names);

        $descs = array();
        foreach($this->post->desc as $langKey => $desc)
        {
            $desc = trim($desc);
            if(empty($desc)) continue;

            $descs[$langKey] = strip_tags($desc, $this->config->allowedTags);
        }
        if($descs) $data->desc = json_encode($descs);

        return $data;
    }

    /**
     * Get pivot column pairs.
     *
     * @param  array  $fieldSettings
     * @param  array  $langs
     * @access public
     * @return array
     */
    public function getCommonColumn($fieldSettings, $langs)
    {
        $fieldPairs = array();
        $clientLang = $this->app->getClientLang();
        foreach($fieldSettings as $field => $fieldList)
        {
            $fieldObject  = $fieldList['object'];
            $relatedField = $fieldList['field'];

            $this->app->loadLang($fieldObject);
            $fieldPairs[$field] = isset($this->lang->$fieldObject->$relatedField) ? $this->lang->$fieldObject->$relatedField : $field;

            if(!isset($langs[$field])) continue;
            if(!empty($langs[$field][$clientLang])) $fieldPairs[$field] = $langs[$field][$clientLang];
        }

        return $fieldPairs;
    }
}
