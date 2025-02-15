<?php
if(!empty($case->confirmeObject))
{
    $method = $case->confirmeObject['type'] == 'confirmedretract' ? 'confirmDemandRetract' : 'confirmDemandUnlink';
    return $this->buildMenu('testcase', $method, "objectID=$case->id&object=case&extra={$case->confirmeObject['id']}", $case, 'view', 'search', '', 'iframe', true);
}
