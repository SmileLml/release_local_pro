<?php
/**
 * The control file of execution module of ZenTaoPMS.
 *
 * @copyright   Copyright 2009-2012 禅道软件（青岛）有限公司(ZenTao Software (Qingdao) Co., Ltd. www.cnezsoft.com)
 * @license     business(商业软件)
 * @author      Guangming Sun <sunguangming@cnezsoft.com>
 * @package     execution
 * @version     $Id$
 * @link        http://www.zentao.net
 */
helper::importControl('execution');
class myexecution extends execution
{
    /**
     * TaskLeft
     *
     * @param  int    $executionID
     * @param  string $groupBy
     * @access public
     * @return void
     */
    public function computeTaskEffort($reload = 'no')
    {
        $this->execution->computeTaskEffort();
        if($reload == 'yes') die(js::reload('parent'));
        echo 'success';
    }
}
