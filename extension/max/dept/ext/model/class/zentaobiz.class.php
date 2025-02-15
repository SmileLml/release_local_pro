<?php
class zentaobizDept extends deptModel
{
    /**
     * Get department manager.
     *
     * @param  int    $deptID
     * @access public
     * @return string
     */
    public function getManager($deptID)
    {
        if(empty($deptID)) return '';

        $dept = $this->getById($deptID);
        if($dept->manager) return $dept->manager;

        $parentDepts = $this->dao->select('*')->from(TABLE_DEPT)->where('id')->in($dept->path)->andWhere('id')->ne($deptID)->orderBy('grade_desc')->fetchAll('id');
        foreach($parentDepts as $dept)
        {
            if($dept->manager) return $dept->manager;
        }

        return '';
    }
}
