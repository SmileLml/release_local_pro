<?php
helper::importControl('review');
class myReview extends review
{
    public function create($projectID = 0, $object = '', $productID = 0, $reviewRange = 'all', $checkedItem = '')
    {
        $reviewedPoints = '';
        $project        = $this->loadModel('project')->getByID($projectID);

        if($project->model == 'ipd') $reviewedPoints = $this->review->getReviewPointByProject($projectID);

        if($_POST and $project->model == 'ipd')
        {
            if($_POST['point'] != 'other') $_POST['object'] = $_POST['point'];
            if($_POST['begin'] > $_POST['deadline']) return $this->send(array('result' => 'fail', 'message' => $this->lang->review->errorLetter));
        }

        $this->view->project = $project;
        $this->view->reviewedPoints = $reviewedPoints;
        return parent::create($projectID, $object, $productID, $reviewRange, $checkedItem);
    }
}
