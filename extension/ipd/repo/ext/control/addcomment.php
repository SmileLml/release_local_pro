<?php
helper::importControl('repo');;
class myRepo extends repo
{
    /**
     * Add comment.
     *
     * @access public
     * @return void
     */
    public function addComment()
    {
        if(!empty($_POST))
        {
            $now  = helper::now();
            $bug  = $this->loadModel('bug')->getByID($this->post->objectID);
            $data = fixer::input('post')
               ->add('objectType', 'bug')
               ->add('product', ',' . $bug->product . ',')
               ->add('project', $bug->project)
               ->add('actor', $this->app->user->account)
               ->add('action', 'commented')
               ->add('date', $now)
               ->get();

            $this->dao->insert(TABLE_ACTION)->data($data)->exec();
            $actionID = $this->dao->lastInsertID();

            $response = array('bugID' => $bug->id, 'id' => $actionID, 'realname' => $this->app->user->realname, 'date' => substr($now, 5, 11), 'edit' => true, 'comment' => $data->comment, 'user' => $this->app->user);
            echo json_encode($response);
        }
    }
}
