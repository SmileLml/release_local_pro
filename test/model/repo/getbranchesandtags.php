#!/usr/bin/env php
<?php
include dirname(dirname(dirname(__FILE__))) . '/lib/init.php';
include dirname(dirname(dirname(__FILE__))) . '/class/repo.class.php';
su('admin');

/**

title=测试repoModel->getBranchesAndTags();
cid=1
pid=1

*/

$repoIDs = array(
    'real'  => 1,
    'dummy' => 0
);

$versions = array(
    'empty'       => '',
    'notExists'   => '0',
    'exists'      => 'master',
    'otherExists' => 'test_case'
);

$repo = new repoTest();

r($repo->getBranchesAndTagsTest($repoIDs['dummy']))                                                  && p() && e('fail');     // 测试代码库不存在情况返回信息 
r($repo->getBranchesAndTagsTest($repoIDs['real'], $versions['empty'],     $versions['empty']))       && p() && e('success');  // 测试代码库正确,源和目标分支为空的返回信息
r($repo->getBranchesAndTagsTest($repoIDs['real'], $versions['empty'],     $versions['notExists']))   && p() && e('success');  // 测试代码库正确,源分支为空,目标分支不存在的返回信息
r($repo->getBranchesAndTagsTest($repoIDs['real'], $versions['empty'],     $versions['exists']))      && p() && e('success');  // 测试代码库正确,源分支为空,目标分支存在的返回信息
r($repo->getBranchesAndTagsTest($repoIDs['real'], $versions['notExists'], $versions['notExists']))   && p() && e('success');  // 测试代码库正确,源分支不存在,目标分支不存在的返回信息
r($repo->getBranchesAndTagsTest($repoIDs['real'], $versions['notExists'], $versions['exists']))      && p() && e('success');  // 测试代码库正确,源分支不存在,目标分支存在的返回信息
r($repo->getBranchesAndTagsTest($repoIDs['real'], $versions['exists'],    $versions['notExists']))   && p() && e('success');  // 测试代码库正确,源分支存在,目标分支不存在的返回信息
r($repo->getBranchesAndTagsTest($repoIDs['real'], $versions['exists'],    $versions['empty']))       && p() && e('success');  // 测试代码库正确,源分支存在,目标分支为空的返回信息
r($repo->getBranchesAndTagsTest($repoIDs['real'], $versions['exists'],    $versions['exists']))      && p() && e('success');  // 测试代码库正确,源分支存在,目标分支相同的返回信息
r($repo->getBranchesAndTagsTest($repoIDs['real'], $versions['exists'],    $versions['otherExists'])) && p() && e('success');  // 测试代码库正确,源分支存在,目标分支不同的返回信息

system("./ztest init");
