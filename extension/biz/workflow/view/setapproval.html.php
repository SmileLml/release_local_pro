<?php include '../../workflow/view/header.html.php';?>
<?php include './coverconfirm.html.php';?>
<?php js::set('cover', $lang->workflow->cover);?>
<?php js::set('module', $flow->module);?>
<?php js::set('approvalCount', count($approvalFlows));?>
<div class='space space-sm'></div>
<div class='main-row'>
  <div class='side-col'>
    <?php include '../../workflow/view/side.html.php';?>
  </div>
  <div class='main-col'>
    <div class='panel'>
      <div class='panel-heading'>
        <strong><?php echo $lang->workflow->setApproval;?></strong>
      </div>
      <div class='panel-body'>
        <form id='setForm' method='post' action='<?php echo inlink('setApproval', "module=$flow->module");?>'>
          <div>
            <div>
              <table class='table table-form' id='relationTable' style="width:<?php echo $lang->workflowrelation->tableWidth;?>px">
                <tbody>
                  <tr>
                    <th class='w-60px'><?php echo $this->lang->workflow->status;?></th>
                    <td class='w-300px'><?php echo html::radio('approval', $lang->workflowapproval->approvalList, $flow->approval);?></td>
                    <td></td>
                  </tr>
                  <?php if(!empty($approvalFlows)):?>
                  <tr class='approval-select hide'>
                    <th><?php echo $this->lang->workflowapproval->approvalFlow;?></th>
                    <td><?php echo html::select('approvalFlow', array('') + $approvalFlows, $approvalFlow, "class='form-control chosen'");?></td>
                    <td></td>
                  </tr>
                  <?php endif;?>
                  <tr class='submit-box'>
                    <th></th>
                    <td class='form-actions'><?php echo baseHTML::submitButton();?></td>
                    <td></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <?php if(empty($approvalFlows)):?>
          <div class='alert alert-warning approval-select hide'>
          <?php
            echo $this->lang->workflowapproval->noApproval;
            if(commonModel::hasPriv('approvalflow', 'browse'))
            {
                echo $this->lang->workflowapproval->createTips[0];
                echo baseHTML::a(helper::createLink('approvalflow', 'browse', 'type=workflow'), $this->lang->workflowapproval->createApproval, "target='_blank' class='btn btn-default margin-left'");
            }
            else
            {
                echo $this->lang->workflowapproval->createTips[1];
            }
          ?>
          </div>
          <?php endif;?>
        </form>
      </div>
    </div>
  </div>
</div>
<?php include $app->getModuleRoot() . 'common/view/footer.html.php';?>
