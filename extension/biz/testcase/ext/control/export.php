<?php
helper::importControl('testcase');
class mytestcase extends testcase
{
    /**
     * Export
     *
     * @param  int    $productID
     * @param  int    $orderBy
     * @param  int    $taskID
     * @param  string $browseType
     * @access public
     * @return void
     */
    public function export($productID, $orderBy, $taskID = 0, $browseType = '')
    {
        if($_POST)
        {
            $this->loadModel('transfer');

            $this->session->set('testcaseTransferParams', array('productID'=> $productID, 'orderBy' => $orderBy));

            $this->config->testcase->datatable->fieldList['branch']['dataSource']['params'] = '$productID&active';
            $this->transfer->export('testcase');

            $this->updateRows($_POST['rows']);

            $this->fetch('file', 'export2' . $_POST['fileType'], $_POST);
        }

        $product     = $this->loadModel('product')->getByID($productID);
        $productName = !empty($product->name) ? $product->name : '';
        $fileName    = $this->lang->testcase->common;
        $browseType  = isset($this->lang->testcase->featureBar['browse'][$browseType]) ? $this->lang->testcase->featureBar['browse'][$browseType] : '';

        if($product->type == 'normal') $this->config->testcase->exportFields = str_replace('branch,', '', $this->config->testcase->exportFields);
        if($product->shadow and $this->app->tab == 'project') $this->config->testcase->exportFields = str_replace('product,', '', $this->config->testcase->exportFields);

        $this->view->fileName        = $productName . $this->lang->dash . $browseType . $fileName;
        $this->view->allExportFields = $this->config->testcase->exportFields;
        $this->view->customExport    = true;

        $this->display();
    }

    /**
     * Update rows for other field .
     *
     * @param  array  $rows
     * @access public
     * @return void
     */
    public function updateRows($rows = array())
    {
        $caseIDList = array();
        foreach($rows as $row) $caseIDList[] = $row->id;

        $caseBugs    = $this->dao->select('count(*) as count, `case`')->from(TABLE_BUG)->where('`case`')->in($caseIDList)->andWhere('deleted')->eq(0)->groupBy('`case`')->fetchPairs('case', 'count');
        $resultCount = $this->dao->select('count(*) as count, `case`')->from(TABLE_TESTRESULT)->where('`case`')->in($caseIDList)->groupBy('`case`')->fetchPairs('case', 'count');

        $steps = $this->dao->select('id,`case`,`version`,`type`,`desc`,`expect`')->from(TABLE_CASESTEP)
            ->where('`case`')->in($caseIDList)
            ->fetchGroup('case', 'id');

        $stmt = $this->dao->select('t1.*')->from(TABLE_TESTRESULT)->alias('t1')
            ->leftJoin(TABLE_TESTRUN)->alias('t2')->on('t1.run=t2.id')
            ->where('t1.`case`')->in($caseIDList)
            ->orderBy('id_desc')
            ->query();

        $results = array();
        while($result = $stmt->fetch())
        {
            if(!isset($results[$result->case])) $results[$result->case] = unserialize($result->stepResults);
        }

        foreach($rows as $row)
        {
            $caseLang   = $this->lang->testcase;
            $caseID = $row->id;
            $result = isset($results[$caseID]) ? $results[$caseID] : array();

            $row->bugs       = isset($caseBugs[$caseID])  ? $caseBugs[$caseID]   : 0;
            $row->results    = isset($resultCount[$caseID]) ? $resultCount[$caseID]    : 0;
            $row->stepNumber = 0;
            $row->stepDesc   = $row->stepExpect = $row->real = '';

            $row->lastRunResult = zget($this->lang->testcase->resultList, $row->lastRunResult);

            if(!empty($result) and !isset($steps[$caseID]))
            {
                $firstStep  = reset($result);
                $row->real = $firstStep['real'];
            }

            if(isset($steps[$caseID]))
            {
                $i = $childId = 0;

                foreach($steps[$caseID] as $step)
                {
                    if($step->version != $row->version) continue;
                    if($step->type != 'group') $row->stepNumber += 1;

                    $stepId = 0;
                    if($step->type == 'group' or $step->type == 'step')
                    {
                        $i++;
                        $childId = 0;
                        $stepId  = $i;
                    }
                    else
                    {
                        $stepId = $i . '.' . $childId;
                    }

                    $sign = (in_array($this->post->fileType, array('html', 'xml'))) ? '<br />' : "\n";
                    $row->stepDesc   .= $stepId . ". " . htmlspecialchars_decode($step->desc) . $sign;
                    $row->stepExpect .= $stepId . ". " . htmlspecialchars_decode($step->expect) . $sign;
                    $row->real       .= $stepId . ". " . (isset($result[$step->id]) ? $result[$step->id]['real'] : '') . $sign;
                    $childId ++;
                }
            }

            $row->stage = explode(',', $row->stage);
            foreach($row->stage as $key => $stage) $row->stage[$key] = isset($caseLang->stageList[$stage]) ? $caseLang->stageList[$stage] : $stage;
            $row->stage = join("\n", $row->stage);
        }

        $this->post->set('rows', $rows);
    }
}
