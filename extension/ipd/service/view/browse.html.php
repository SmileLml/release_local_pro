<?php
/**
 * The manage view file of service module of ZenTaoPMS.
 *
 * @copyright   Copyright 2009-2015 禅道软件（青岛）有限公司(ZenTao Software (Qingdao) Co., Ltd. www.cnezsoft.com)
 * @license     ZPL(http://zpl.pub/page/zplv12.html) or AGPL(https://www.gnu.org/licenses/agpl-3.0.en.html)
 * @author      Yidong Wang <yidong@cnezsoft.com>
 * @package     service
 * @version     $Id$
 * @link        http://www.zentao.net
 */
?>
<?php include $app->getModuleRoot() . 'common/view/header.html.php';?>
<?php include '../../common/ext/view/treemap.html.php';?>
<div id='mainMenu' class='clearfix'>
  <div class='pull-right btn-toolbar'>
    <?php common::printLink('service', 'manage', '', "<i class='icon-program'></i> {$this->lang->service->TreeView}", '', "class='btn btn-primary'");?>
    <?php common::printLink('service', 'create', '', "<i class='icon-plus'></i> {$this->lang->service->createTop}", '', "class='btn btn-primary'");?>
  </div>
</div>
<div id='mainContent' class='main-row'>
   <div class='main-col'>
    <div class='cell'>
      <?php if($serviceList):?>

      <div id='mainContent' class='main-table'>
      <table class='table has-sort-head' id='serviceList'>
        <thead>
          <?php $vars = "browseType=$browseType&param=$param&orderBy=%s&recTotal={$pager->recTotal}&recPerPage={$pager->recPerPage}";?>
          <tr>
            <th class='w-60px'><?php common::printOrderLink('id',          $orderBy, $vars, $lang->idAB);?></th>
            <th class='w-100px'><?php common::printOrderLink('name',        $orderBy, $vars, $lang->service->name);?></th>
            <th class='w-90px'><?php common::printOrderLink('softName',       $orderBy, $vars, $lang->service->softName);?></th>
            <th class='w-100px'><?php common::printOrderLink('hosts',    $orderBy, $vars, $lang->service->host);?></th>
            <th class='w-100px'><?php common::printOrderLink('entry',    $orderBy, $vars, $lang->service->entry);?></th>
            <th class='w-100px'> <?php common::printOrderLink('port', $orderBy, $vars, $lang->service->port);?></th>
            <th class='w-90px'><?php common::printOrderLink('deploy',    $orderBy, $vars, $lang->service->deploy);?></th>
            <th class='w-60px'><?php common::printOrderLink('external',  $orderBy, $vars, $lang->service->external);?></th>
            <th class='w-100px'><?php echo $lang->actions?></th>
          </tr>
        </thead>
        <?php if(!empty($serviceList)):?>
        <tbody>
          <?php foreach($serviceList as $service):?>
          <tr>
            <td><?php echo $service->id;?></td>
            <td class='text-ellipsis' title='<?php echo $service->name?>'><?php echo html::a($this->inlink('view', "id=$service->id", 'html', true), $service->name, '', "class='iframe'");?></td>
            <td class='text-ellipsis' title='<?php echo $service->softName?>'><?php echo $service->softName?></td>
            <?php
            $serviceHosts = '';
            if($service->hosts)
            {
                foreach(explode(',', $service->hosts) as $host) $serviceHosts = zget($hosts, $host, '') . ' ';
            }
            ?>
            <td class='text-ellipsis' title='<?php echo $serviceHosts;?>'><?php echo $serviceHosts;?></td>
            <td class='text-ellipsis' title='<?php echo $service->entry;?>'><?php echo $service->entry;?></td>
            <td class='text-ellipsis' title='<?php echo $service->port;?>'><?php echo $service->port;?></td>
            <td class='text-ellipsis' title='<?php echo $service->deploy;?>'><?php echo $service->deploy;?></td>
            <?php $serviceType = $service->external ? $lang->service->isTrue : $lang->service->isFalse;?>
            <td class='text-ellipsis' title='<?php echo $serviceType;?>'><?php echo $serviceType;?></td>
            <td class='c-actions'>
            <?php
            common::printLink('service', 'edit', "serviceID={$service->id}", "<i class='icon-edit'></i> ", '', "class='btn' title='{$lang->edit}'");

            if(common::hasPriv('service', 'delete', $service))
            {
                common::printLink('service', 'delete', "serviceID={$service->id}", "<i class='icon-trash'></i> ", 'hiddenwin', "class='btn' title='{$lang->delete}'");
            }
            ?></td>
          </tr>
          <?php endforeach;?>
        </tbody>
        <?php endif;?>
      </table>
  <div class='table-footer'>
    <?php $pager->show('right', 'pagerjs');?>
  </div>
      <?php else:?>
      <p class='text-center pdt-20'>
        <?php common::printLink('service', 'create', '', "<i class='icon-plus'></i> " . $lang->service->create, '', "class='iframe btn btn-lg' data-width='1000'", true, true);?>
      </p>
      <?php endif;?>
    </div>
  </div>
</div>
<?php include $app->getModuleRoot() . 'common/view/footer.html.php';?>
