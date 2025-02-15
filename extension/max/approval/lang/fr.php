<?php
$lang->approval->common   = 'Apporval';
$lang->approval->node     = 'Approval node';
$lang->approval->reviewer = 'Reviewer';
$lang->approval->cc       = 'CC';
$lang->approval->ccer     = 'CCer';
$lang->approval->progress = 'Progress';
$lang->approval->noResult = 'No result';

$lang->approval->start    = 'Submit Review';
$lang->approval->end      = 'End Review';
$lang->approval->cancel   = 'Cancel Review';

$lang->approval->otherReviewer = 'Other Reviewer : ';
$lang->approval->otherCcer     = 'Other Ccer : ';

$lang->approval->reviewDesc = new stdclass();
$lang->approval->reviewDesc->start = 'Start approval by <strong>$actor</strong>. <span class="text-muted">$date</span>' . "\n";
$lang->approval->reviewDesc->pass  = 'Approved by <strong>$actor</strong> . Result is <span class="result pass">Pass</span> <span class="text-muted">$date</span>' . "\n";
$lang->approval->reviewDesc->fail  = 'Approved by <strong>$actor</strong>. Result is <span class="result fail">Fail</span> <span class="text-muted">$date</span>' . "\n";
$lang->approval->reviewDesc->doing = 'Approving by <strong>$actor</strong> . <span class="result reviewing">In approval</span> <span class="text-muted">$date</span>' . "\n";
$lang->approval->reviewDesc->wait  = '<strong>$actor</strong> <span class="text-muted">Pending approval</span>' . "\n";
$lang->approval->reviewDesc->cc    = 'CC to <strong>$actor</strong>' . "\n";

$lang->approval->reviewDesc->autoReject      = 'Automatic rejection is set' . "\n";
$lang->approval->reviewDesc->autoPass        = 'Automatic consent is set' . "\n";
$lang->approval->reviewDesc->autoRejected    = 'Automatically rejected' . "\n";
$lang->approval->reviewDesc->autoPassed      = 'Automatically agreed' . "\n";
$lang->approval->reviewDesc->pass4NoReviewer = 'No reviewer is selected, it will pass automatically according to the configuration' . "\n";

$lang->approval->notice = new stdclass();
$lang->approval->notice->orSign       = '(One person pass that will do)';
$lang->approval->notice->times        = 'Submission %s';
$lang->approval->notice->approvalTime = 'Reviewed %s';
$lang->approval->notice->day          = 'Day';
$lang->approval->notice->hour         = 'Hour';

$lang->approval->currentResult = 'Result';
$lang->approval->currentNode   = 'Current node';

$lang->approval->statusList = array();
$lang->approval->statusList['wait']  = 'Wait';
$lang->approval->statusList['doing'] = 'Doing';
$lang->approval->statusList['done']  = 'Done';

$lang->approval->resultList = array();
$lang->approval->resultList['pass'] = 'Pass';
$lang->approval->resultList['fail'] = 'Fail';

$lang->approval->mailResultList = array();
$lang->approval->mailResultList['pass']   = 'Pass';
$lang->approval->mailResultList['fail']   = 'Reject';
$lang->approval->mailResultList['ignore'] = 'Cancel';

$lang->approval->nodeList = array();
$lang->approval->nodeList['cc']     = 'CC';
$lang->approval->nodeList['review'] = 'Review';
$lang->approval->nodeList['doing']  = 'Reviewing';

$lang->approval->mailContent = array();
$lang->approval->mailContent['mail'] = '
<p>Dear %s</p>
<p>[<a href="%s">%s</a>] You are currently required for approval. Please go to the Zen Path for approval.</p>
';
$lang->approval->mailContent['mailto'] = '
<p>Dear %s</p>
<p>[<a href="%s">%s</a>] Under approval.</p>
';
$lang->approval->mailContent['result'] = '
<p>Dear %s</p>
<p>[<a href="%s">%s</a>] The final approval result is <span class="%s"><strong>%s</strong></span>.</p>
';
