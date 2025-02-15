<?php
class attend extends control
{
    /**
     * Export detail attends.
     * 
     * @param  string $date 
     * @access public
     * @return void
     */
    public function exportDetail($date = '')
    {
        if($date == '' or strlen($date) != 6) $date = date('Ym');
        $currentYear  = substr($date, 0, 4);
        $currentMonth = substr($date, 4, 2);
        $deptID       = isset($_SESSION['attendDeptID'])  ? $_SESSION['attendDeptID']  : 0;
        $account      = isset($_SESSION['attendAccount']) ? $_SESSION['attendAccount'] : '';

        if($_POST)
        {
            /* Get fields. */
            $fields = explode(',', $this->config->attend->list->exportFields);
            foreach($fields as $key => $field)
            {
                $field = trim($field);
                $fields[$field] = isset($this->lang->attend->$field) ? $this->lang->attend->$field : '';
                unset($fields[$key]);
            }
            $fields['dept']     = $this->lang->user->dept;
            $fields['realname'] = $this->lang->user->realname;

            $attends = $this->attend->getDetailAttends($date, $account, $deptID);
            
            if(!empty($attends))
            {
                foreach($attends as $attend) 
                {
                    $attend->status = $attend->desc ? $attend->desc : '';
                }
            }

            $this->post->set('fields', $fields);
            $this->post->set('rows', $attends);
            $this->fetch('file', 'export2' . $this->post->fileType, $_POST);
        }

        $fileName = ''; 
        if($deptID)
        {
            $dept = $this->loadModel('dept')->getById($deptID, $type = 'dept');
            if($dept) $fileName .= $dept->name . ' - ';
        }
        if($account) 
        {
            $user = $this->loadModel('user')->getByAccount($account);
            if($user) $fileName .= $user->realname . ' - ';
        }
        $fileName .= $currentYear . $this->lang->year . $currentMonth . $this->lang->month . $this->lang->attend->detail;

        $this->view->fileName = $fileName;
        $this->display('attend', 'export');
    }
}