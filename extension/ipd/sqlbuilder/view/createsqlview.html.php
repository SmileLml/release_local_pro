<?php
/**
 * The view file of sqlBuilder module of ZenTaoPMS.
 *
 * @copyright   Copyright 2009-2012 禅道软件（青岛）有限公司(ZenTao Software (Qingdao) Co., Ltd. www.cnezsoft.com)
 * @license     business(商业软件)
 * @author      Fei Chen <chenfei@cnezsoft.com>, Xiying Guan <guanxiying@cnezsoft.com>
 * @package     sqlBuilder
 * @version     $Id$
 * @link        http://www.zentao.net
 */
?>
<?php include $app->getModuleRoot() . 'common/view/header.html.php';?>
<?php js::set('sqlviewPrefix', $config->sqlbuilder->sqlviewPrefix);?>
<?php js::set('defaultAction', $config->sqlbuilder->defaultAction);?>
<div id='mainContent' class='main-content'>
  <div class='center-block'>
    <div class='main-header'>
      <h2><?php echo $lang->sqlbuilder->createSQLView;?></h2>
    </div>
    <form class="load-indicator main-form form-ajax" id="createViewForm" method="post" target='hiddenwin'>
      <table class="table table-form">
        <tbody>
          <tr>
            <th><?php echo $lang->sqlview->name;?></th>
            <td><?php echo html::input('name', '', "class='form-control'");?></td><td></td>
          </tr>
          <tr>
            <th><?php echo $lang->sqlview->code;?></th>
            <td><?php echo html::input('code', '', "class='form-control' placeholder='{$lang->sqlbuilder->tips->onlyLetter}'");?></td><td></td>
          </tr>
          <tr>
            <th><?php echo $lang->sqlbuilder->createSQL;?></th>
            <td colspan='2'>
              <div class='input-group'>
                <?php echo html::input('action', $config->sqlbuilder->defaultAction, "class='form-control' readonly");?>
                <?php if($this->config->db->driver == 'mysql'):?>
                <div class='input-group-addon'>
                  <?php echo html::a($this->createLink('sqlbuilder', 'ajaxBuildSQL', '', '', true), $lang->sqlbuilder->buildSQL, '', "data-toggle='modal' data-width='95%' data-type='iframe'");?>
                </div>
                <?php endif;?>
              </div>
              <?php echo html::textarea('sql', '', "class='form-control' rows='5'");?>
            </td>
          </tr>
          <tr>
            <th><?php echo $lang->sqlview->desc;?></th>
            <td colspan='2'><?php echo html::textarea('desc', '', "class='form-control' rows='5'");?></td>
          </tr>
          <tr>
            <td colspan='3' class='text-center form-actions'>
              <?php echo html::submitButton();?>
              <?php echo html::backButton();?>
            </td>
          </tr>
        </tbody>
      </table>
    </form>
  </div>
</div>
<?php include $app->getModuleRoot() . 'common/view/footer.html.php';?>
