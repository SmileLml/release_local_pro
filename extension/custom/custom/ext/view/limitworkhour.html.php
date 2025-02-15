<?php
/**
 * The limittaskdate view file of custom module of ZenTaoPMS.
 * @copyright   Copyright 2009-2020 禅道软件（青岛）有限公司(ZenTao Software (Qingdao) Co., Ltd. www.cnezsoft.com)
 * @license     ZPL(http://zpl.pub/page/zplv12.html) or AGPL(https://www.gnu.org/licenses/agpl-3.0.en.html)
 * @author      Liyuchun <liyuchun@cnezsoft.com>
 * @package     custom
 * @version     $Id$
 * @link        http://www.zentao.net
 */
?>
<?php include $app->getModuleRoot() . 'common/view/header.html.php';?>
<div id='mainContent' class='main-row'>
  <?php include $app->getModulePath() . '/view/sidebar.html.php';?>
  <div class='main-col main-content'>
    <form class="load-indicator main-form form-ajax" method='post'>
      <div class='main-header'>
        <div class='heading'>
          <strong><?php echo $lang->custom->$module->fields['limitWorkHour'];?></strong>
        </div>
      </div>
      <table class='table table-form'>
        <tr>
          <th class='w-160px'><?php echo $lang->custom->limitWorkHourForDetails;?></th>
          <td class='w-160px'>
            <?php echo html::number('limitWorkHour', $config->limitWorkHour ?? 0, "class='form-control' min='0' max='24' oninput='if(!/^[0-9]+$/.test(value)) value=value.replace(/\D/g,\"\");if(value>24)value=24;if(value<0)value=null' " );?>
          </td>
          <td></td>
        </tr>
        <tr>
          <td colspan='2' class='form-actions text-center'>
            <?php echo html::submitButton();?>
          </td>
        </tr>
      </table>
    </form>
  </div>
</div>
<script>
  $(function() $('#mainMenu #taskTab').addClass('btn-active-text'); })
</script>
<?php include $app->getModuleRoot() . 'common/view/footer.html.php';?>