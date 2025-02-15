<?php
public function canParallel($projectID)
{
    return $this->dao->select('id')->from(TABLE_PROJECT)
        ->where('project')->eq($projectID)
        ->andWhere('status')->ne('wait')
        ->fetch('id');
}

/**
 * Check ipd stage date.
 *
 * @param string $stage
 * @access public
 * @return void
 */
public function checkIpdStageDate($stage = '')
{
    $stages = $this->loadModel('execution')->getList($stage->project);

    if($stages) $stages = array_reverse($stages, true);

    $preDate   = $nextDate = '';
    $isCurrent = false;
    foreach($stages as $value)
    {
        if($isCurrent)
        {
            $nextDate = $value->begin;
            break;
        }

        if($stage->attribute == $value->attribute)
        {
            $isCurrent = true;
            continue;
        }

        $preDate = $value->end;
    }

    if($preDate and $preDate > $_POST['begin']) dao::$errors['begin'] = $this->lang->programplan->error->outOfDate . ': ' . $preDate;
    if($nextDate and $nextDate < $_POST['end']) dao::$errors['end']   = $this->lang->programplan->error->lessOfDate . ': ' . $nextDate;
}
