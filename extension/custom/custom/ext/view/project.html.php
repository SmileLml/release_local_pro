<?php
/**
 * The kanban view file of custom module of ZenTaoPMS.
 * @copyright   Copyright 2009-2020 禅道软件（青岛）有限公司(ZenTao Software (Qingdao) Co., Ltd. www.cnezsoft.com)
 * @license     ZPL(http://zpl.pub/page/zplv12.html) or AGPL(https://www.gnu.org/licenses/agpl-3.0.en.html)
 * @author      Liyuchun <liyuchun@cnezsoft.com>
 * @package     custom
 * @version     $Id$
 * @link        http://www.zentao.net
 */
?>
<?php include $app->getModuleRoot() . 'common/view/header.html.php';?>
<div id='mainContent' class='main-content'>
  <form class="load-indicator main-form form-ajax" method='post'>
    <div class='main-header'>
      <div class='heading'>
        <strong><?php echo $lang->custom->product->fields['product'];?></strong>
      </div>
    </div>
    <table class='table table-form'>
      <tr>
        <th class='w-150px'><?php echo $lang->custom->closedProject;?></th>
        <td class='w-300px text-left'>
          <?php $checkedKey = isset($config->CRProject) ? $config->CRProject : 1;?>
          <?php foreach($lang->custom->CRProject as $key => $value):?>
            <label class="radio-inline"><input type="radio" name="project" value="<?php echo $key?>"<?php echo $key == $checkedKey ? " checked='checked'" : ''?> id="project<?php echo $key;?>"><?php echo $value;?></label>
          <?php endforeach;?>
        </td>
        <td></td>
      </tr>
      <tr>
        <th></th>
        <td class='form-actions'>
          <?php echo html::submitButton();?>
        </td>
      </tr>
    </table>
</form>
</div>
<script>
    $(function()
    {
        $('#mainMenu #kanbanTab').addClass('btn-active-text');
    })
</script>
<?php include $app->getModuleRoot() . 'common/view/footer.html.php';?>