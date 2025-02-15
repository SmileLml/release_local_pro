<?php include $app->getModuleRoot() . 'common/view/header.html.php';?>
<?php js::set('articleID', $template->id);?>
<div id="mainMenu" class="clearfix">
  <div class="btn-toolbar pull-left">
    <?php echo html::a(inlink('template'), '<i class="icon icon-back icon-sm"></i> ' . $lang->goback, '', "class='btn btn-secondary'");?>
    <div class="divider"></div>
    <div class="page-title">
    <span class="label label-id"><?php echo $template->id?></span>
    <span class="text" title="<?php echo $template->title;?>"><?php echo $template->title;?></span>
    </div>
  </div>
</div>
<?php if($template->type == 'book' || $template->template):?>
<div class="main-row fade split-row" id="mainRow">
  <div class='side-col' data-min-width='235'>
    <div class='cell'>
      <div class='tabs'>
        <div class="tab-content books">
          <div class="tab-pane active" id="book">
            <ul data-name="docsTree" data-ride="tree" data-initial-state="preserve" class="tree no-margin" data-idx="0">
            <?php echo $catalog;?>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-spliter"></div>
  <?php if($template->template):?>
  <div class="main-col" data-min-width="400">
    <div class='cell'>
      <div class="detail">
        <div class="detail-title"><?php echo $lang->baseline->view;?></div>
        <div class="detail-content article-content">
        <?php echo $template->content;?>
        </div>
    </div>
  </div>
  <?php else:?>
  <div class="table-empty-tip">
    <p>
      <span class="text-muted"><?php echo $lang->doc->bookBrowseTip;?></span>
      <?php echo html::a(helper::createLink('baseline', 'manageBook', "template={$template->id}&nodeID=0"), "<i class='icon icon-plus'></i>" . $lang->baseline->manageBook, '',"class='btn btn-info'");?>
    <p>
  </div>
  <?php endif;?>
</div>
<?php else:?>
<div id="mainContent" class="main-row">
  <div class="main-col col-8">
    <div class='cell'>
      <div class="detail">
        <div class="detail-title"><?php echo $lang->baseline->view;?></div>
        <div class="detail-content article-content">
        <?php echo $template->content;?>
        </div>
      </div>
    </div>
  </div>
  <div class="side-col col-4">
    <div class='cell'>
      <summary class="detail-title"><?php echo $lang->baseline->baseInfo;?></summary>
      <div class="detail-content">
        <table class="table table-data">
          <tr>
            <th><?php echo $lang->baseline->templateType;?></th>
            <td><?php echo zget($lang->baseline->objectList, $template->templateType)?></td>
          </tr>
          <tr>
            <th><?php echo $lang->baseline->docType;?></th>
            <td><?php echo zget($lang->baseline->docTypeList, $template->type)?></td>
          </tr>
          <tr>
            <th><?php echo $lang->baseline->addedBy;?></th>
            <td><?php echo zget($users, $template->addedBy)?></td>
          </tr>
          <tr>
            <th><?php echo $lang->baseline->addedDate;?></th>
            <td><?php echo $template->addedDate?></td>
          </tr>
        </table>
      </div>
    </div>
  </div>
</div>
<?php endif;?>
<?php include $app->getModuleRoot() . 'common/view/footer.html.php';?>
