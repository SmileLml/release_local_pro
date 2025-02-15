<?php
public function buildOperateMenu($bug, $type = 'view')
{
    if(!empty($bug->confirmeObject))
    {
        $method = $bug->confirmeObject['type'] == 'confirmedretract' ? 'confirmDemandRetract' : 'confirmDemandUnlink';
        return $this->buildMenu('bug', $method, "objectID=$bug->id&object=bug&extra={$bug->confirmeObject['id']}", $bug, $type, 'search', '', 'iframe', true);
    }
    return parent::buildOperateMenu($bug, $type);
}
