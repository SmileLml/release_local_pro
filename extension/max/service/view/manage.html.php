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
    <?php common::printLink('service', 'browse', '', "<i class='icon-program'></i> {$this->lang->service->listView}", '', "class='btn btn-primary'");?>
    <?php common::printLink('service', 'create', '', "<i class='icon-plus'></i> {$this->lang->service->create}", '', "class='btn btn-primary'");?>
  </div>
</div>
<div id='mainContent' class='main-row'>
  <div class='side-col' id='sidebar'>
    <div class="sidebar-toggle"><i class="icon icon-angle-left"></i></div>
    <div class='cell'>
      <ul class='nav nav-tabs nav-stacked'>
        <?php foreach($topServices as $id => $topService):?>
        <li id='service_<?php echo $id?>' title='<?php echo $topService?>'><?php echo html::a(inlink('manage', "serviceID={$id}"), $topService)?></li>
        <?php endforeach;?>
      </ul>
    </div>
  </div>
  <div class='main-col'>
    <div class='cell'>
      <?php if($tree):?>
      <div id='servicesTreemap' oncontextmenu="return false;">
        <?php echo $tree;?>
        <ul id="treemapMenu" class="treemap-contextmenu dropdown-menu">
          <?php if(common::hasPriv('service', 'create')):?>
          <li class="visible-for-service visible-for-root"><a href="###" id="treemapAddService"><?php echo $lang->service->createDepend?></a></li>
          <li class="visible-for-service visible-for-component visible-for-root"><a href="###" id="treemapAddPeer"><?php echo $lang->service->createPeer?></a></li>
          <?php endif;?>
          <?php if(common::hasPriv('service', 'view')):?>
          <li class="visible-for-service visible-for-root visible-for-component"><a href="###" id="treemapView"><?php echo $lang->service->detail?></a></li>
          <?php endif;?>
          <?php if(common::hasPriv('service', 'edit')):?>
          <li class="visible-for-service visible-for-root visible-for-component"><a href="###" id="treemapEdit"><?php echo $lang->edit?></a></li>
          <?php endif;?>
          <?php if(common::hasPriv('service', 'delete')):?>
          <li class="visible-for-service visible-for-root visible-for-component"><a href="###" id="treemapDelete"><?php echo $lang->delete?></a></li>
          <?php endif;?>
        </ul>
      </div>
      <?php else:?>
      <div class="table-empty-tip">
        <p>
          <span class="text-muted"><?php echo $lang->service->empty;?></span>
          <?php if(common::hasPriv('service', 'create')) common::printLink('service', 'create', '', '<i class="icon icon-plus"></i> ' . $lang->service->create, '', 'class="btn btn-info"');?>
        </p>
      </div>
      <?php endif;?>
    </div>
  </div>
</div>
<script>
$(function()
{
    $('#service_<?php echo $serviceID?>').addClass('active');
    $('#servicesTreemap .treemap-data > li').attr('data-type', 'root');
})
</script>
<script>
$(function()
{
    /* Init treemap. */
    var $treemap = $('#servicesTreemap').treemap(
    {
        /* Set icon for node. */
        nodeTemplate: function(node, tree)
        {
            var $node = $('<div class="treemap-node"></div>');
            if(node.type)    $node.addClass('treemap-node-' + node.type);
            var background = node.color ? 'style="background:' + node.color +';color:#fff"' : '';
            $node.attr('data-service', node.service);
            $node.attr('data-parentid', node.parentid);
            $node.append('<a class="treemap-node-wrapper" ' + background + '>' + node.text + '</a>');
            return $node;
        },
        onNodeClick: function(node)
        {
            if(!node.children)
            {
                var serviceID = node.service;
                var url = createLink('service', 'view', "serviceID=" + serviceID, 'html', true);
                new $.zui.ModalTrigger({width:1000, type:'iframe', url:url}).show();
            }
        }
    });

    /* Right-click menu. */
    var $menu = $('#treemapMenu');
    var hideMenu = function()
    {
        if($menu.hasClass('show')) $menu.removeClass('show');
    };
    var showMenu = function(e, element)
    {
        if(!$menu.hasClass('show'))
        {
            var $node = $(element || this).closest('.treemap-node');
            if($node.hasClass('treemap-node-root')) type = 'root';
            if($node.hasClass('treemap-node-service')) type = 'service';
            if($node.hasClass('treemap-node-component')) type = 'component';
            var nodeOffset = $node.find('.treemap-node-wrapper').offset();
            var showTop    = nodeOffset.top + e.offsetY - $('#header').height() - 10;
            var showLeft   = nodeOffset.left + e.offsetX - 10;
            if(nodeOffset.left + e.offsetX + 10 + $menu.width() >= $(window).width()) showLeft = e.offsetX + nodeOffset.left - $menu.width();
            $menu.css(
            {
                top: showTop,
                left: showLeft
            }).addClass('show');
            var $li = $menu.children('li');
            $li.addClass('hide');
            $li.filter('.visible-for-' + type).removeClass('hide');
            $menu.data('id', $node.data('id'));
            $menu.data('service', $node.data('service'));
            $menu.data('parentid', $node.data('parentid'));
            e.stopPropagation();
            e.preventDefault();
        }
    };
    $(document).on('mousedown', function(){hideMenu();});
    $(document).on('mousedown', '.treemap-node-wrapper', function(e)
    {
        if(e.which === 3) showMenu(e, this);
    });
    $menu.on('mousedown', function(e){e.stopPropagation();});

    /* Process for click view button. */
    $('#treemapView').on('click', function()
    {
        var serviceID = $menu.data('service');
        var url = createLink('service', 'view', "serviceID=" + serviceID, 'html', true);
        new $.zui.ModalTrigger({width:1000, type:'iframe', url:url}).show();
    });

    /* Process for click edit button. */
    $('#treemapEdit').on('click', function()
    {
        var serviceID = $menu.data('service');
        var url = createLink('service', 'edit', "serviceID=" + serviceID, 'html', true);
        new $.zui.ModalTrigger({width:1000, type:'iframe', url:url}).show();
    });

    /* Process for click delete button. */
    $('#treemapDelete').on('click', function()
    {
        var serviceID = $menu.data('service');
        var url = createLink('service', 'delete', "serviceID=" + serviceID);
        hiddenwin.location.href = url;
    });

    /* Process for click add service button. */
    $('#treemapAddService').on('click', function()
    {
        var serviceID = $menu.data('service');
        var url = createLink('service', 'create', "parent=" +serviceID, 'html', true);
        new $.zui.ModalTrigger({width:1000, type:'iframe', url:url}).show();
    });

    /* Process for click add peer service button. */
    $('#treemapAddPeer').on('click', function()
    {
        var serviceID = $menu.data('parentid');
        var url = createLink('service', 'create', "parent=" +serviceID, 'html', true);
        new $.zui.ModalTrigger({width:1000, type:'iframe', url:url}).show();
    });

    var maxHeight = $(window).height() - $('#header').height() - $('#footer').height() - 55;
    $('#servicesTreemap').height(maxHeight);
    $('#serviceList').height(maxHeight);
    $('.outer').css({'padding-bottom':'0px', 'padding-right':'0px'});
});
</script>
<?php include $app->getModuleRoot() . 'common/view/footer.html.php';?>
