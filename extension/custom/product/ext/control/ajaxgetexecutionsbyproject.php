<?php

class myproduct extends product
{
    public function ajaxGetExecutionsByProject($productID, $projectID = 0, $branch = 0, $number = 0)
    {
        $noMultipleExecutionID = $projectID ? $this->loadModel('execution')->getNoMultipleID($projectID) : '';
        $executions            = $this->product->getExecutionPairsByProduct($productID, $branch, 'id_desc', $projectID, 'multiple,stagefilter' . (isset($this->config->CRProject) && empty($this->config->CRProject) ? 'projectclosefilter' : ''));

        $disabled = $noMultipleExecutionID ? "disabled='disabled'" : '';
        $html = html::select("executions[{$number}]", array('' => '') + $executions, 0, "class='form-control' onchange='loadExecutionBuilds($productID, this.value, $number)' $disabled");

        if($noMultipleExecutionID) $html .= html::hidden("executions[{$number}]", $noMultipleExecutionID, "id=executions{$number}");

        return print($html);
    }
}