#!/usr/bin/env php
<?php
include dirname(dirname(dirname(__FILE__))) . '/lib/init.php';
include dirname(dirname(dirname(__FILE__))) . '/class/repo.class.php';
su('admin');

/**

title=测试repoModel->getLinkedObjects();
cid=1
pid=1

*/

$comment = array(
    'empty' => '',
    'story' => 'Start story #1,2,3',
    'task'  => 'Start task #1,2,3',
    'bug'   => 'Start bug #1,2,3',
    'all'   => 'Start story #1,2. Start task #1,2. Start bug #1,2.',
);

$repo = new repoTest();

r($repo->getLinkedObjectsTest($comment['empty'], 'count'))   && p()    && e('0');  // 测试注释为空时的关联对象
r($repo->getLinkedObjectsTest($comment['story'], 'stories')) && p('1') && e('2');  // 测试注释仅包含需求时的关联对象
r($repo->getLinkedObjectsTest($comment['story'], 'stories')) && p('2') && e('3');  // 测试注释仅包含需求时的关联对象
r($repo->getLinkedObjectsTest($comment['task'],  'tasks'))   && p('1') && e('2');  // 测试注释仅包含任务时的关联对象
r($repo->getLinkedObjectsTest($comment['task'],  'tasks'))   && p('2') && e('3');  // 测试注释仅包含任务时的关联对象
r($repo->getLinkedObjectsTest($comment['bug'],   'bugs'))    && p('1') && e('2');  // 测试注释仅包含bug时的关联对象
r($repo->getLinkedObjectsTest($comment['bug'],   'bugs'))    && p('2') && e('3');  // 测试注释仅包含bug时的关联对象
r($repo->getLinkedObjectsTest($comment['all'],   'count'))   && p()    && e('6');  // 测试注释包含所有类型内容时的关联对象

system("./ztest init");
