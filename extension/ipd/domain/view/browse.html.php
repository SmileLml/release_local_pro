<?php
/**
 * The browse view file of domain module of ZenTaoPMS.
 *
 * @copyright   Copyright 2009-2015 禅道软件（青岛）有限公司(ZenTao Software (Qingdao) Co., Ltd. www.cnezsoft.com)
 * @license     ZPL(http://zpl.pub/page/zplv12.html) or AGPL(https://www.gnu.org/licenses/agpl-3.0.en.html)
 * @author      Jiangxiu Peng <pengjiangxiu@cnezsoft.com>
 * @package     domain
 * @version     $Id$
 * @link        http://www.zentao.net
 */
?>
<?php include $app->getModuleRoot() . 'common/view/header.html.php';?>
<?php js::set('confirmDelete', $lang->domain->confirmDelete)?>
<?php js::set('browseType', $browseType)?>
<div id='mainMenu' class='clearfix'>
  <div class='pull-left btn-toolbar'>
    <?php echo html::a($this->createLink('domain', 'browse'), "<span class='text'>{$lang->domain->all}</span>", '', "class='btn btn-link btn-active-text'");?>
    <a href='#' id='bysearchTab' class='btn btn-link querybox-toggle'><i class='icon-search icon'></i>&nbsp;<?php echo $lang->domain->byQuery;?></a>
  </div>

  <?php if(common::hasPriv('domain', 'create')):?>
  <div class="btn-toolbar pull-right" id='createActionMenu'>
    <?php
    $misc = "class='btn btn-primary' data-toggle='modal'";
    $link = $this->createLink('domain', 'create', '', '', true);
    echo html::a($link, "<i class='icon icon-plus'></i>" . $lang->domain->create, '', $misc);
    ?>
  </div>
  <?php endif;?>
</div>
<div id='queryBox' class='cell <?php if($browseType =='bysearch') echo 'show';?>' data-module='domain'></div>
<div id='mainContent' class='main-table'>
  <?php if(empty($domainList)):?>
  <div class="table-empty-tip">
    <p>
      <span class="text-muted"><?php echo $lang->domain->empty;?></span>
      <?php if(common::hasPriv('domain', 'create')) common::printLink('domain', 'create', '', '<i class="icon icon-plus"></i> ' . $lang->domain->create, '', 'class="btn btn-info" data-toggle="modal"');?>
    </p>
  </div>
  <?php else:?>
  <table class='table has-sort-head' id='domainList'>
    <thead>
      <?php $vars = "browseType=$browseType&param=$param&orderBy=%s&recTotal={$pager->recTotal}&recPerPage={$pager->recPerPage}";?>
      <tr>
        <th class='w-60px'><?php common::printOrderLink('id',          $orderBy, $vars, $lang->idAB);?></th>
        <th><?php common::printOrderLink('domain',        $orderBy, $vars, $lang->domain->domain);?></th>
        <th class='w-150px'><?php common::printOrderLink('adminURI',    $orderBy, $vars, $lang->domain->adminURI);?></th>
        <th class='w-150px'> <?php common::printOrderLink('resolverURI', $orderBy, $vars, $lang->domain->resolverURI);?></th>
        <th class='w-90px'><?php common::printOrderLink('register',    $orderBy, $vars, $lang->domain->register);?></th>
        <th class='w-90px'><?php common::printOrderLink('expiredDate',  $orderBy, $vars, $lang->domain->expiredDate);?></th>
        <th class='w-90px'><?php common::printOrderLink('renew',       $orderBy, $vars, $lang->domain->renew);?></th>
        <th class='w-90px'><?php common::printOrderLink('account',     $orderBy, $vars, $lang->domain->account);?></th>
        <th class='w-100px'><?php common::printOrderLink('createdBy',   $orderBy, $vars, $lang->domain->createdBy);?></th>
        <th class='w-150px'><?php common::printOrderLink('createdDate', $orderBy, $vars, $lang->domain->createdDate);?></th>
        <th class='w-80px'><?php echo $lang->actions?></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($domainList as $domain):?>
      <tr>
        <td><?php echo $domain->id;?></td>
        <td title='<?php echo $domain->domain?>'><?php echo html::a($this->inlink('view', "id=$domain->id", 'html'), $domain->domain);?></td>
        <td title='<?php echo $domain->adminURI?>'><?php echo $domain->adminURI?></td>
        <td title='<?php echo $domain->resolverURI?>'><?php echo $domain->resolverURI?></td>
        <td title='<?php echo $domain->register;?>'><?php echo $domain->register;?></td>
        <td><?php echo substr($domain->expiredDate, 0, 10);?></td>
        <td><?php echo zget($lang->domain->renewMethod, $domain->renew);?></td>
        <td><?php echo zget($users, $domain->account);?></td>
        <td><?php echo zget($users, $domain->createdBy);?></td>
        <td><?php echo $domain->createdDate;?></td>
        <td class='c-actions'>
          <?php
          common::printLink('domain','edit', "id={$domain->id}", "<i class='icon-common-edit icon-edit'> </i>", '', "data-toggle='modal' class='btn'", '', true);
          if(common::hasPriv('domain', 'delete', $domain))
          {
              $deleteURL = $this->createLink('domain', 'delete', "id=$domain->id");
              echo html::a("javascript:ajaxDelete(\"$deleteURL\", \"mainContent\", confirmDelete)", '<i class="icon-trash"></i>', '', "class='btn' title='{$lang->domain->delete}'");
          }
          ?>
        </td>
      </tr>
      <?php endforeach;?>
    </tbody>
  </table>
  <div class='table-footer'>
    <?php $pager->show('right', 'pagerjs');?>
  </div>
  <?php endif;?>
</div>
<script>
$(function()
{
    $('#<?php echo $browseType?>Tab').addClass('active');
    if(browseType == 'bysearch') $.toggleQueryBox(true);
})
</script>
<?php include $app->getModuleRoot() . 'common/view/footer.html.php';?>
