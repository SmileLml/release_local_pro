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
<?php $hiddenClass = $type == 'sqlview' ? 'hidden' : '';?>
<div id='mainContent' class='main-content'>
  <div class='center-block'>
    <div class='main-header'>
      <h2><?php echo $lang->sqlbuilder->create;?></h2>
    </div>
    <form class='form-horizontal not-watch' method='post' target='hiddenwin'>
      <div id='tableBox'>
        <div class='form-group'>
          <div class='col-sm-3'>
            <div class='input-group'>
              <span class='input-group-addon'><?php echo $lang->sqlbuilder->mainTable;?></span>
              <?php echo html::select('mainTable', $tables, '', "class='form-control chosen'");?>
            </div>
          </div>
        </div>

        <div class='form-box'>
          <div class='form-group'>
            <div class='col-sm-3'>
              <div class='input-group'>
                <span class='input-group-addon'><?php echo $lang->sqlbuilder->leftJoin;?></span>
                <span class='input-group-addon'><?php echo $lang->sqlbuilder->table;?></span>
                <?php echo html::select('slaveTable', $tables, '', "class='form-control chosen'");?>
              </div>
            </div>
            <div class='col-sm-8'>
              <div class='input-group'>
                <span class='input-group-addon'><?php echo $lang->sqlbuilder->on;?></span>
                <span class='input-group-addon'><?php echo $lang->sqlbuilder->table;?></span>
                <div class='input-group-btn'><?php echo html::select('firstTable[]', $tables, '', "class='form-control chosen'");?></div>
                <span class='input-group-addon'><?php echo $lang->sqlbuilder->field;?></span>
                <div class='input-group-btn'><?php echo html::select('firstField[]', $fields, '', "class='form-control chosen'");?></div>
                <span class='input-group-addon'><?php echo $lang->sqlbuilder->equal;?></span>
                <span class='input-group-addon'><?php echo $lang->sqlbuilder->table;?></span>
                <div class='input-group-btn'><?php echo html::select('secondTable[]', $tables, '', "class='form-control chosen'");?></div>
                <span class='input-group-addon'><?php echo $lang->sqlbuilder->field;?></span>
                <div><?php echo html::select('secondField[]', $fields, '', "class='form-control chosen'");?></div>
              </div>
            </div>
            <div class='col-sm-1'>
              <div class='input-group'>
                <a href='javascript:;' class='btn btn-link'><i class='icon-plus'></i></a>
                <a href='javascript:;' class='btn btn-link'><i class='icon icon-close'></i></a>
              </div>
            </div>
          </div>
        </div>
      </div>

      <hr />

      <div id='fieldBox'>
        <div class='form-box'>
          <div class='main-field-row form-group'>
            <div class='col-sm-11'>
              <div class='input-group'>
                <span class='input-group-addon'><?php echo $lang->sqlbuilder->query;?></span>
                <span class='input-group-addon'><?php echo $lang->sqlbuilder->table;?></span>
                <div class='input-group-btn'><?php echo html::select('queryTable[]', $tables, '', "class='form-control chosen'");?></div>
                <span class='input-group-addon'><?php echo $lang->sqlbuilder->field;?></span>
                <div class='input-group-btn'><?php echo html::select('queryField[]', $fields, '', "class='form-control chosen'");?></div>
                <span class='input-group-addon'><?php echo $lang->sqlbuilder->funcOperate;?></span>
                <?php echo html::select('funcOperate[]', $lang->sqlbuilder->funcOperateList, '', "class='form-control nochosen'");?>
                <span class='input-group-addon'><?php echo $lang->sqlbuilder->computeOperate;?></span>
                <?php echo html::select('computeOperate[]', $lang->sqlbuilder->computeOperateList, '', "class='form-control nochosen'");?>
                <span class='input-group-addon'><?php echo $lang->sqlbuilder->alias;?></span>
                <?php echo html::input('aliasName[]', '', "class='form-control w-80px'");?>
              </div>
            </div>
            <div class='col-sm-1'>
              <div class='input-group'>
                <a href='javascript:;' class='btn btn-link'><i class='icon-plus'></i></a>
                <a href='javascript:;' class='btn btn-link'><i class='icon icon-close'></i></a>
              </div>
            </div>
          </div>

          <div class='slave-field-row hidden form-group'>
            <div class='col-sm-10'>
              <div class='input-group'>
                <span class='input-group-addon'><?php echo $lang->sqlbuilder->query;?></span>
                <span class='input-group-addon'><?php echo $lang->sqlbuilder->table;?></span>
                <div class='input-group-btn'><?php echo html::select('queryTable[]', $tables, '', "class='form-control chosen'");?></div>
                <span class='input-group-addon'><?php echo $lang->sqlbuilder->field;?></span>
                <div class='input-group-btn'><?php echo html::select('queryField[]', $fields, '', "class='form-control chosen'");?></div>
                <span class='input-group-addon'><?php echo $lang->sqlbuilder->funcOperate;?></span>
                <?php echo html::select('funcOperate[]', $lang->sqlbuilder->funcOperateList, '', "class='form-control nochosen w-80px'");?>
                <span class='input-group-addon'><?php echo $lang->sqlbuilder->alias;?></span>
                <?php echo html::input('aliasName[]', '', "class='form-control'");?>
              </div>
            </div>
            <div class='col-sm-2'>
              <div class='decimal-box input-group hidden'>
                <span class='input-group-addon'><?php echo $lang->sqlbuilder->reserve;?></span>
                <?php echo html::select('decimal[]', $lang->sqlbuilder->roundList, 0, "class='form-control nochosen'");?>
                <span class='input-group-addon'><?php echo $lang->sqlbuilder->decimal;?></span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <hr />

      <div id='conditionBox'>
        <div class='form-box'>
          <div class='form-group'>
            <div class='col-sm-11'>
              <div class='input-group'>
                <span class='input-group-addon'><?php echo $lang->sqlbuilder->operate;?></span>
                <?php echo html::input('firstOperate[]', '', "class='form-control'");?>

                <span class='input-group-addon'><?php echo $lang->sqlbuilder->table;?></span>
                <div><?php echo html::select('conditionTable[]', $tables, '', "class='form-control chosen'");?></div>
                <span class='input-group-addon'><?php echo $lang->sqlbuilder->field;?></span>
                <div><?php echo html::select('conditionField[]', $fields, '', "class='form-control chosen'");?></div>

                <span class='input-group-addon'><?php echo $lang->sqlbuilder->condition;?></span>
                <div><?php echo html::select('conditionOperate[]', $lang->sqlbuilder->conditionOperateList, '', "class='form-control nochosen'");?></div>

                <span class='input-group-addon'><?php echo $lang->sqlbuilder->value;?></span>
                <?php echo html::input('conditionValue[]', '', "class='form-control'");?>

                <span class='input-group-addon'><?php echo $lang->sqlbuilder->operate;?></span>
                <?php echo html::input('secondOperate[]', '', "class='form-control'");?>

                <span class='input-group-addon'></span>
                <div><?php echo html::select('andOr[]', $lang->sqlbuilder->andOrList, '', "class='form-control nochosen'");?></div>
              </div>
            </div>
            <div class='col-sm-1'>
              <div class='input-group'>
                <a href='javascript:;' class='btn btn-link'><i class='icon-plus'></i></a>
                <a href='javascript:;' class='btn btn-link'><i class='icon icon-close'></i></a>
              </div>
            </div>
          </div>
        </div>
      </div>

      <hr />

      <div id='orderByBox' class='<?php echo $hiddenClass?>'>
        <div class='form-box'>
          <div class='form-group'>
            <div class='col-sm-9'>
              <div class='input-group'>
                <span class='input-group-addon'><?php echo $lang->sqlbuilder->orderBy;?></span>
                <span class='input-group-addon'><?php echo $lang->sqlbuilder->table;?></span>
                <div class='input-group-btn'><?php echo html::select('orderTable[]', $tables, '', "class='form-control chosen'");?></div>
                <span class='input-group-addon'><?php echo $lang->sqlbuilder->field;?></span>
                <div class='input-group-btn'><?php echo html::select('orderField[]', $fields, '', "class='form-control chosen'");?></div>
                <span class='input-group-addon'><?php echo $lang->sqlbuilder->orderRule;?></span>
                <?php echo html::select('orderRule[]', $lang->sqlbuilder->orderRuleList, '', "class='form-control chosen'");?>
              </div>
            </div>
            <div class='col-sm-1'>
              <div class='input-group'>
                <a href='javascript:;' class='btn btn-link'><i class='icon-plus'></i></a>
                <a href='javascript:;' class='btn btn-link'><i class='icon icon-close'></i></a>
              </div>
            </div>
          </div>
        </div>
      </div>

      <hr />

      <div id='groupAndLimtBox'>
        <div class='form-box'>
          <div class='form-group'>
            <div class='col-sm-7'>
              <div class='input-group'>
                <span class='input-group-addon'><?php echo $lang->sqlbuilder->groupBy;?></span>
                <span class='input-group-addon'><?php echo $lang->sqlbuilder->table;?></span>
                <div class='input-group-btn'><?php echo html::select('groupTable', $tables, '', "class='form-control chosen'");?></div>
                <span class='input-group-addon'><?php echo $lang->sqlbuilder->field;?></span>
                <div><?php echo html::select('groupField', $fields, '', "class='form-control chosen'");?></div>
              </div>
            </div>
          </div>

          <div class='form-group <?php echo $hiddenClass?>'>
            <div class='col-sm-3'>
              <div class='input-group'>
                <span class='input-group-addon'><?php echo $lang->sqlbuilder->limit;?></span>
                <div><?php echo html::input('limit', '', "class='form-control'");?></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <hr />

      <div id='sqlBox'>
        <div class='form-group'>
          <?php echo html::textarea('sql', '', "class='form-control' rows='5'");?>
        </div>
      </div>
      <div class='text-center'><?php echo html::commonButton($lang->sqlbuilder->use, "onclick='useSQL()'", "btn btn-primary");?></div>
    </form>
  </div>
</div>
<?php include $app->getModuleRoot() . 'common/view/footer.html.php';?>
