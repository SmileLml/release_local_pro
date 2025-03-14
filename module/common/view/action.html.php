<?php if($extView = $this->getExtViewFile(__FILE__)){include $extView; return helper::cd();}?>
<?php if(!empty($blockHistory)):?>
<div class="panel block-histories histories no-margin" data-textDiff="<?php echo $lang->action->textDiff;?>" data-original="<?php echo $lang->action->original;?>">
<?php else:?>
<div class="detail histories" id='actionbox' data-textDiff="<?php echo $lang->action->textDiff;?>" data-original="<?php echo $lang->action->original;?>">
<?php endif;?>
<style>
.histories-list > li {word-break: break-word; word-wrap: break-word;}
.history-changes del {padding-left: 3px;}
</style>
  <script>
  $(function()
  {
      var diffButton = '<button type="button" class="btn btn-mini btn-icon btn-strip"><i class="icon icon-code icon-sm"></i></button>';
      var newBoxID = '';
      var oldBoxID = '';
      $('blockquote.textdiff').each(function()
      {
          newBoxID = $(this).parent().attr('id');
          if(newBoxID != oldBoxID)
          {
              oldBoxID = newBoxID;
              if($(this).html() != $(this).next().html()) $(this).closest('.history-changes').before(diffButton);
          }
      });
  });
  </script>
  <?php if(!empty($blockHistory)):?>
  <div class="panel-heading"><div class="panel-title">
  <?php else:?>
  <div class="detail-title">
  <?php endif;?>
    <?php echo $lang->history?> &nbsp;
    <button type="button" class="btn btn-mini btn-icon btn-reverse" title='<?php echo $lang->reverse;?>'>
      <?php $reverseBtnClass = isset($_COOKIE['historyOrder']) && $this->cookie->historyOrder == 'desc' ? 'icon-arrow-down' : 'icon-arrow-up';?>
      <i class="icon <?php echo $reverseBtnClass;?> icon-sm"></i>
    </button>
    <button type="button" class="btn btn-mini btn-icon btn-expand-all" title='<?php echo $lang->switchDisplay;?>'>
      <i class="icon icon-plus icon-sm"></i>
    </button>
    <?php
      if(isset($actionFormLink))
      {
        if(commonModel::hasPriv('action', 'comment'))
        {
          if(isset($config->action->extView[$this->app->rawModule . '-' . $this->app->rawMethod]) && (empty($config->action->extView[$this->app->rawModule . '-' . $this->app->rawMethod]) || (isset($config->action->extView[$this->app->rawModule . '-' . $this->app->rawMethod][$this->app->tab]) && $config->action->extView[$this->app->rawModule . '-' . $this->app->rawMethod][$this->app->tab] == $storyType)))
          {
            echo html::commonButton('<i class="icon icon-chat-line"></i> ' . $lang->action->create, '', 'btn btn-link pull-right btn-comment');
            ?>
            <div class="modal fade modal-comment">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><i class="icon icon-close"></i></button>
                    <h4 class="modal-title"><?php echo $lang->action->create; ?></h4>
                  </div>
                  <div class="modal-body">
                    <form class="load-indicator not-watch" action="<?php echo $actionFormLink; ?>" target='hiddenwin' method='post' enctype='multipart/form-data'>
                      <div class="form-group">
                        <textarea id='comment' name='comment' class="form-control" rows="8" autofocus="autofocus"></textarea>
                      </div>
                      <div class="form-group">
                        <?php echo $this->fetch('file', 'buildform'); ?>
                      </div>
                      <div class="form-group form-actions text-center">
                        <button type="submit" class="btn btn-primary btn-wide"><?php echo $lang->save; ?></button>
                        <button type="button" class="btn btn-wide" data-dismiss="modal"><?php echo $lang->close; ?></button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>
            <script>
            $(function()
            {
                \$body = $('body', window.parent.document);
                if(\$body.hasClass('hide-modal-close')) \$body.removeClass('hide-modal-close');
            });
            </script>
            <?php
          }
          else
          {
            echo common::printCommentIcon($actionFormLink);
          }
        }
      }
    ?>
  </div>
  <?php if(!empty($blockHistory)):?>
  </div>
  <?php endif;?>
  <?php if(!empty($blockHistory)):?>
  <div class="panel-body">
  <?php else:?>
  <div class="detail-content">
  <?php endif;?>
    <ol class='histories-list'>
      <?php $i = 1; ?>
      <?php foreach($actions as $action):?>
      <?php $canEditComment = ((!isset($canBeChanged) or !empty($canBeChanged)) and end($actions) == $action and trim($action->comment) != '' and strpos(',view,objectlibs,viewcard,', ",$this->methodName,") !== false and $action->actor == $this->app->user->account and common::hasPriv('action', 'editComment'));?>
      <li value='<?php echo $i ++;?>'>
        <?php
        $action->actor = zget($users, $action->actor);
        if($action->action == 'assigned' or $action->action == 'toaudit') $action->extra = zget($users, $action->extra);
        if(strpos($action->actor, ':') !== false) $action->actor = substr($action->actor, strpos($action->actor, ':') + 1);
        ?>
        <?php $this->action->printAction($action);?>
        <?php if(!empty($action->history)):?>
        <button type='button' class='btn btn-mini switch-btn btn-icon btn-expand' title='<?php echo $lang->switchDisplay;?>'><i class='change-show icon icon-plus icon-sm'></i></button>
        <div class='history-changes' id='changeBox<?php echo $i;?>'>
          <?php echo $this->action->printChanges($action->objectType, $action->history);?>
        </div>
        <?php endif;?>
        <?php if(strlen(trim(($action->comment))) != 0):?>
        <?php if($canEditComment):?>
        <?php echo html::commonButton('<i class="icon icon-pencil"></i>', "title='{$lang->action->editComment}'", 'btn btn-link btn-icon btn-sm btn-edit-comment');?>
        <style>.comment .comment-content{width: 98%}</style>
        <?php endif;?>
        <div class='article-content comment'>
          <div class='comment-content'>
            <?php
            if(strpos($action->comment, '<pre class="prettyprint lang-html">') !== false)
            {
                $before   = explode('<pre class="prettyprint lang-html">', $action->comment);
                $after    = explode('</pre>', $before[1]);
                $htmlCode = $after[0];
                $text     = $before[0] . htmlspecialchars($htmlCode) . $after[1];
                echo $text;
            }
            else
            {
                echo strip_tags($action->comment) == $action->comment ? nl2br($action->comment) : $action->comment;
            }
            ?>
          </div>
        </div>
        <?php if($canEditComment):?>
        <form method='post' class='comment-edit-form' action='<?php echo $this->createLink('action', 'editComment', "actionID=$action->id")?>'>
          <div class="form-group">
          <?php echo html::textarea('lastComment', htmlSpecialString($action->comment), "rows='8' autofocus='autofocus'");?>
          </div>
          <div class="form-group form-actions">
          <?php echo html::submitButton($lang->save);?>
          <?php echo html::commonButton($lang->close, '', 'btn btn-wide btn-hide-form');?>
          </div>
        </form>
        <?php endif;?>
        <?php endif;?>
      </li>
      <?php endforeach;?>
    </ol>
  </div>
</div>
<script>
$(document).on('historiesReverse', '.histories-list', function(event, isAsc)
{
    $.cookie('historyOrder', isAsc ? 'asc' : 'desc', {expires:config.cookieLife, path:config.webRoot});
});
</script>
