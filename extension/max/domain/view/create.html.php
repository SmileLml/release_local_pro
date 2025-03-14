<?php
/**
 * The create view file of domain module of ZenTaoPMS.
 *
 * @copyright   Copyright 2009-2015 禅道软件（青岛）有限公司(ZenTao Software (Qingdao) Co., Ltd. www.cnezsoft.com)
 * @license     ZPL(http://zpl.pub/page/zplv12.html) or AGPL(https://www.gnu.org/licenses/agpl-3.0.en.html)
 * @author      Jiangxiu Peng <pengjiangxiu@cnezsoft.com>
 * @package     domain
 * @version     $Id$
 * @link        http://www.zentao.net
 */
?>
<?php include '../../common/view/header.modal.html.php';?>
<?php include $app->getModuleRoot() . 'common/view/datepicker.html.php';?>
<form id='ajaxForm' method='post' action='<?php echo inlink('create');?>'>
  <table class='table table-form'>
    <tr>
      <th class='w-100px'><?php echo $lang->domain->domain?></th>
      <td class='required'><?php echo html::input('domain', '', "class='form-control'")?></td>
    </tr>
    <tr>
      <th><?php echo $lang->domain->adminURI?></th>
      <td class='required'><?php echo html::input('adminURI', '', "class='form-control'")?></td>
    </tr>
    <tr>
      <th><?php echo $lang->domain->resolverURI?></th>
      <td class='required'><?php echo html::input('resolverURI', '', "class='form-control'")?></td>
    </tr>
    <tr>
      <th><?php echo $lang->domain->register?></th>
      <td class='required'><?php echo html::input('register', '', "class='form-control'")?></td>
    </tr>
    <tr>
      <th><?php echo $lang->domain->expiredDate;?></th>
      <td class='required'><?php echo html::input('expiredDate', '', "class='form-control form-date'")?></td>
    </tr>
    <tr>
      <th><?php echo $lang->domain->renew;?></th>
      <td class='required'><?php echo html::select('renew', $lang->domain->renewMethod, '', "class='form-control'")?></td>
    </tr>
    <tr>
      <th><?php echo $lang->domain->account;?></th>
      <td><?php echo html::select('account', $users, '', "class='form-control'")?></td>
    </tr>
    <tr>
      <td colspan='2' class='text-center form-actions'>
        <?php echo html::submitButton();?>
        <?php if(!isonlybody()) echo html::backButton();?>
      </td>
    </tr>
  </table>
 </form>
<?php include '../../common/view/footer.modal.html.php';?>
