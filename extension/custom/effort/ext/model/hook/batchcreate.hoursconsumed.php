<?php
$today              = helper::today();
$hoursConsumedToday = $this->getAccountStatistics();

foreach($this->post->objectType as $i => $objectType)
{
    if(empty($this->post->work[$i])) continue;
    if(in_array($objectType, ['task', 'bug']) && $this->post->dates[$i] == $today && $this->post->consumed[$i] > 0)
    {
        $hoursConsumedToday += $this->post->consumed[$i];
        if($hoursConsumedToday > $this->config->limitWorkHour) die(js::alert($objectType == 'task' ? $this->lang->effort->hoursConsumedTodayOverflowForTask : $this->lang->effort->hoursConsumedTodayOverflowForBug));
    }
}
