<?php
/**
 * The task mobile view file of user module of ZenTaoPMS.
 *
 * @copyright   Copyright 2009-2016 禅道软件（青岛）有限公司(ZenTao Software (Qingdao) Co., Ltd. www.cnezsoft.com)
 * @license     ZPL(http://zpl.pub/page/zplv12.html) or AGPL(https://www.gnu.org/licenses/agpl-3.0.en.html)
 * @author      Fei Chen <chenfei@cnezsoft.com>
 * @package     user
 * @version     $Id: index.html.php 3830 2016-05-18 09:34:17Z liugang $
 * @link        http://www.zentao.net
 */
?>

<?php
$bodyClass = 'with-menu-top';
include "../../common/view/m.header.html.php";
include "./m.featurebar.html.php";
?>

<nav id='subMenu' class='menu nav gray'>
<?php
$that = zget($lang->user->thirdPerson, $user->gender);
echo html::a(inlink('task', "userID={$user->id}&type=assignedTo"), sprintf($lang->user->assignedTo, $that));
echo html::a(inlink('task', "userID={$user->id}&type=openedBy"),   sprintf($lang->user->openedBy, $that));
echo html::a(inlink('task', "userID={$user->id}&type=finishedBy"), sprintf($lang->user->finishedBy, $that));
echo html::a(inlink('task', "userID={$user->id}&type=closedBy"),   sprintf($lang->user->closedBy, $that));
echo html::a(inlink('task', "userID={$user->id}&type=canceledBy"), sprintf($lang->user->canceledBy, $that));
?>
<a class='moreSubMenu hidden' data-display='dropdown' data-placement='beside-bottom'><?php echo $lang->more;?></a>
<div id='moreSubMenu' class='list dropdown-menu'></div>
</nav>

<section id='page' class='section list-with-pager'>
  <div class='box' data-page='<?php echo $pager->pageID ?>' data-refresh-url='<?php echo $this->createLink('user', 'task', "userID={$user->id}&type=$type&orderBy=%s&recTotal={$pager->recTotal}&recPerPage={$pager->recPerPage}&pageID={$pager->pageID}"); ?>'>
    <table class='table bordered'>
      <thead>
        <tr>
          <th><?php echo $lang->task->name;?> </th>
          <th class='text-center w-80px'><?php echo $lang->task->consumedAB;?> </th>
          <th class='text-center w-70px'><?php echo $lang->statusAB;?> </th>
        </tr>
      </thead>
      <?php foreach($tasks as $task):?>
      <tr class='text-center' data-url='<?php echo $this->createLink('task', 'view', "taskID={$task->id}");?>' data-id='<?php echo $task->id;?>'>
        <td class='text-left'><?php echo $task->name;?></td>
        <td><?php echo $task->consumed;?></td>
        <td class='task-<?php echo $task->status;?>'><?php echo zget($lang->task->statusList, $task->status);?></td>
      </tr>
      <?php endforeach;?>
    </table>
  </div>

  <nav class='nav justify pager'>
    <?php $pager->show($align = 'justify');?>
  </nav>
</section>

<script>
$('#<?php echo $methodName?>' + 'Tab').addClass('active');
$('#subMenu > a').removeClass('active').filter('[href*="<?php echo $type?>"]').addClass('active');
</script>
<?php include "../../common/view/m.footer.html.php"; ?>
