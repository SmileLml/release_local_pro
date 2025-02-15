<?php
/**
 * The flowchart view file of workfloweditor module of ZDOO.
 *
 * @copyright   Copyright 2009-2020 青岛易软天创网络科技有限公司(QingDao Nature Easy Soft Network Technology Co,LTD, www.cnezsoft.com)
 * @license     商业软件，非开源软件
 * @author      Hao Sun <sunhao@cnezsoft.com>
 * @package     workfloweditor
 * @version     $Id$
 * @link        http://www.zdoo.com
 */
?>
<?php include 'header.html.php';?>
<div class='main-row auto-height auto-fade-in fade columns' id='editor'>
  <div class='side-col column'>
    <div class='cell'>
      <div id='editorElements'>
        <?php
        foreach ($lang->workfloweditor->elementTypes as $name => $label)
        {
            $tips = '';
            if($name == 'decision' or $name == 'result') $tips = "data-toggle='tooltip' data-placement='right' title='{$this->lang->workflow->tips->flowchart}'";
            echo "<div class='editor-element editor-element-$name' draggable='true' data-type='$name' $tips><span>$label</span></div>";
        }
        ?>
      </div>
    </div>
  </div>
  <div class='main-col column'>
    <div class='cell'>
      <div id='editorToolbar'>
        <button type="button" class="btn btn-link" disabled><i class="icon icon-undo"></i></button>
        <button type="button" class="btn btn-link" disabled><i class="icon icon-redo"></i></button>
        <button type="button" class="btn btn-link" disabled id='editorDeleteBtn'><i class="icon icon-trash"></i></button>
        <button type="button" class="btn btn-link" disabled><i class="icon icon-minus-circle"></i></button>
        <span id='editorZoomLevel' class='text-muted'>100%</span>
        <button type="button" class="btn btn-link" disabled><i class="icon icon-plus-circle"></i></button>
      </div>
      <div id='editorCanvas'></div>
    </div>
  </div>
</div>
<div id='editorElementModal' class='modal-dialog'>
  <table class='table table-form'>
    <tbody>
      <tr>
        <th><?php echo $lang->workfloweditor->elementName; ?></th>
        <td>
          <div class='required required-wrapper'></div>
          <?php echo html::input('elementName', '', 'class="form-control"'); ?>
        </td>
      </tr>
      <tr>
        <th><?php echo $lang->workfloweditor->elementCode; ?></th>
        <td>
          <div class='required required-wrapper'></div>
          <?php echo html::input('elementCode', '', 'class="form-control" placeholder="' . $lang->workflowfield->placeholder->code . '"'); ?>
        </td>
      </tr>
      <tr>
        <th></th>
        <td>
          <button type='button' class='btn btn-primary' id='editorElementModalBtn'><?php echo $lang->confirm; ?></button>
        </td>
      </tr>
    </tbody>
  </table>
</div>
<?php
include '../../common/view/flowchart.html.php';
js::set('flowchartData', !empty($flow->flowchart) ? array_values(json_decode($flow->flowchart)) : $lang->workfloweditor->defaultFlowchartData);
js::set('isDefaultData', empty($flow->flowchart));
js::set('flowchartElementTypes', $lang->workfloweditor->elementTypes);
js::set('langNameAndCodeRequired', $lang->workfloweditor->nameAndCodeRequired);
js::set('langConfirmToDelete', $lang->workfloweditor->confirmToDelete);
js::set('langStartOrStopDuplicated', $lang->workfloweditor->startOrStopDuplicated);
js::set('leavePageTip', $lang->workfloweditor->leavePageTip);
js::set('defaultActions', $config->workflowaction->defaultActions);
?>
<?php include $app->getModuleRoot() . 'common/view/footer.html.php';?>
