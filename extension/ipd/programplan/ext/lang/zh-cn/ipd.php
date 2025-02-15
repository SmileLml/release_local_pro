<?php
$lang->programplan->stageCustom->point = '显示评审点';

$lang->programplan->parallel = '阶段是否允许并行';

$lang->programplan->parallelTip = '不支持并行是指上一阶段完成之后下一阶段才能开始，例如：概念阶段完成之后，计划阶段才能开始，包括阶段下的任务。
并行是指阶段与阶段之间没有依赖关系，阶段及任务的开始状态不受其他阶段状态的影响。';

$lang->programplan->parallelList[0] = '否';
$lang->programplan->parallelList[1] = '是';

$lang->programplan->error->outOfDate  = '计划开始时间应当大于上一阶段的计划结束时间';
$lang->programplan->error->lessOfDate = '计划结束时间应当小于下一阶段的计划开始时间';
