<?php include $app->getModuleRoot() . 'common/view/header.html.php'?>
<style>
#progress > li.reviewer:before{width:0px; height:7px; left:-13px; border:none; border-left:1px solid #eee;}
#progress > li.node:before{width:10px; height:10px; background-color:unset;left:-18px;}
#progress > li.node.pass:before {width:0px; height:0px; background-color:unset;left:-13px;border:none}
#progress > li.node.pass > div:before{position: absolute; content: "\e92f"; color:#00D293; left:-22px; top:5px; font-size:18px; font-family: ZentaoIcon;z-index:50}
#progress > li.node.doing:before {width:18px; height:18px; background-color:#00B3FF;left:-21px;top:5px;border-color:#00B3FF}
#progress > li.node.doing > div:before{position: absolute; content: "..."; color:#FFF; left:-19px; top:0px; font-size:18px; font-family: ZentaoIcon;z-index:50}
#progress > li.node.fail:before {width:18px; height:18px; background-color:#FF4550;left:-21px;top:5px;border-color:#FF4550}
#progress > li.node.fail > div:before{position: absolute; content: "\e936"; color:#FFF; left:-18px; top:3px; font-size:12px; font-family: ZentaoIcon;z-index:50}
#progress > li .timeline-text .result.reviewing {color:#00B3FF; padding-left:3px; font-weight:bolder;}
#progress > li .timeline-text .result.pass      {color:#00D293; padding-left:3px; font-weight:bolder;}
#progress > li .timeline-text .result.fail      {color:#FF4550; padding-left:3px; font-weight:bolder;}
#progress > li .opinion {padding: 8px; background-color:#F5F5F5;}
.approvalNotice {padding-left: 8px;}
</style>
<div id='mainContent' class='main-content'>
  <div class='center-block'>
    <div class='main-header'>
      <span class="text"><?php echo $title;?></span>
    </div>
    <ul id='progress' class='timeline timeline-tag-right no-margin'>
      <?php
      foreach($nodes as $node)
      {
          if(empty($node->id)) continue;
          if($node->type == 'start')
          {
              echo $this->approval->buildReviewDesc($node, array('users' => $users, 'approval' => $approval));
          }
          elseif($node->type == 'branch')
          {
              foreach($node->branches as $branchNodes)
              {
                  foreach($branchNodes->nodes as $branchNode)
                  {
                      echo $this->approval->buildReviewDesc($branchNode, array('users' => $users, 'allReviewers' => $reviewers, 'reviewers' => zget($reviewers, $branchNode->id, array())));
                  }
              }
          }
          elseif(isset($reviewers[$node->id]))
          {
              echo $this->approval->buildReviewDesc($node, array('users' => $users, 'reviewers' => $reviewers[$node->id]));
          }
          elseif(isset($node->agentType) and $node->agentType == 'pass')
          {
              echo $this->approval->buildReviewDesc($node);
          }
      }
      ?>
    <ul>
  </div>
</div>
<?php include $app->getModuleRoot() . 'common/view/footer.html.php';?>
