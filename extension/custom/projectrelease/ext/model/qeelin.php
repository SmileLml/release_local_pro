<?php
/**
 * Build project release view action menu.
 *
 * @param  object $release
 * @access public
 * @return string
 */
public function buildOperateViewMenu($release)
{
    $canBeChanged = common::canBeChanged('projectrelease', $release);
    if($release->deleted || !$canBeChanged || isonlybody()) return '';

    $menu   = '';
    $params = "releaseID=$release->id";

    if(common::hasPriv('projectrelease', 'changeStatus', $release))
    {
        $changedStatus = $release->status == 'normal' ? 'terminate' : 'normal';
        $menu .= html::a(inlink('changeStatus', "$params&status=$changedStatus"), '<i class="icon-' . ($release->status == 'normal' ? 'pause' : 'play') . '"></i> ' . $this->lang->release->changeStatusList[$changedStatus], 'hiddenwin', "class='btn btn-link' title='{$this->lang->release->changeStatusList[$changedStatus]}'");
    }

    $menu .= "<div class='divider'></div>";
    $menu .= $this->buildFlowMenu('release', $release, 'view', 'direct');
    if(!empty($release->project))
    {
        $project = $this->loadModel('project')->getById($release->project);
        if(!empty($project) && !common::canModify('project', $project)) return $menu;
    }
    $menu .= "<div class='divider'></div>";
    $editClickable   = $this->buildMenu('projectrelease', 'edit',   $params, $release, 'view', '', '', '', '', '', '', false);
    $deleteClickable = $this->buildMenu('projectrelease', 'delete', $params, $release, 'view', '', '', '', '', '', '', false);
    if(common::hasPriv('projectrelease', 'edit')   and $editClickable)   $menu .= html::a(helper::createLink('projectrelease', 'edit', $params), "<i class='icon-common-edit icon-edit'></i> " . $this->lang->edit, '', "class='btn btn-link' title='{$this->lang->edit}'");
    if(common::hasPriv('projectrelease', 'delete') and $deleteClickable) $menu .= html::a(helper::createLink('projectrelease', 'delete', $params), "<i class='icon-common-delete icon-trash'></i> " . $this->lang->delete, '', "class='btn btn-link' title='{$this->lang->delete}' target='hiddenwin'");


    return $menu;
}