<?php
class calendarBlock extends blockModel
{
    public function getCalendarParams()
    {
        $params = new stdclass();
        return json_encode($params);
    }

    public function printCalendarBlock($module, $param)
    {
        $date      = date('Y-m-d');
        $startDate = date('w') == 1 ? $date : date('Y-m-d', strtotime("last Monday"));
        $endDate   = date('Y-m-d', strtotime("next Sunday $startDate"));
        $todos     = $this->loadModel('todo')->getWeekTodos($startDate, $endDate);

        /* Process todos. */
        $newTodos = array();
        foreach($todos as $todo)
        {
            if(!isset($newTodos[$todo->date])) $newTodos[$todo->date] = array();
            $time = date('H', strtotime("{$todo->date} {$todo->begin}")) > 12 ? 'PM' : 'AM';
            if(!isset($newTodos[$todo->date][$time])) $newTodos[$todo->date][$time] = array();
            $newTodos[$todo->date][$time][] = $todo;
        }

        $data['todos']     = $newTodos;
        $data['date']      = $date;
        $data['startDate'] = $startDate;
        $data['endDate']   = $endDate;
        return $data;
    }
}
