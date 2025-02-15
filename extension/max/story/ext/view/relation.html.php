<?php
include $app->getModuleRoot() . 'common/view/header.lite.html.php';
?>
<div id='mainContent' class='main-content'>
  <div class='main-header'>
    <h2>
      <span><?php echo html::icon($lang->icons['report']);?></span>
      <?php echo ($storyType == 'requirement') ? $lang->story->story : $lang->story->requirement;?>
    </h2>
  </div>
  <div class='tasksList'>
    <table class='table table-fixed'>
      <thead>
      <tr class='text-center'>
        <th class='w-60px'><?php echo $lang->story->id;?></th>
        <th class='w-p30 text-left'><?php echo $lang->story->title;?></th>
        <th class='w-60px'><?php echo $lang->story->pri;?></th>
        <th class='w-p30'><?php echo $lang->story->openedBy?></th>
        <th class='w-p30'><?php echo $lang->story->assignedTo?></th>
        <th class='w-80px'><?php echo $lang->story->estimate?></th>
        <th class='w-80px'><?php echo $lang->story->status?></th>
      </tr>
      </thead>
      <tbody>
      <?php foreach($relation as $key => $story):?>
        <tr class='text-center'>
          <td><?php echo $story->id;?></td>
          <td class='text-left' title="<?php echo $story->title?>"><?php echo $story->title;?></td>
          <td><?php echo $story->pri;?></td>
          <td><?php echo zget($users, $story->openedBy);?></td>
          <td><?php echo zget($users, $story->assignedTo);?></td>
          <td><?php echo $story->estimate;?></td>
          <td><?php echo zget($lang->story->statusList, $story->status);?></td>
        </tr>
      <?php endforeach;?>
      </tbody>
    </table>
  </div>
</div>
<?php include $app->getModuleRoot() . 'common/view/footer.lite.html.php';?>
