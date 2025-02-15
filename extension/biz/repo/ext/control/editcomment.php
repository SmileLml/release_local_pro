<?php
helper::importControl('repo');
class myRepo extends repo
{
    /**
     * Edit comment.
     *
     * @param  int    $commentID
     * @access public
     * @return void
     */
    public function editComment($commentID)
    {
        if(!empty($_POST))
        {
            $comment = $this->loadModel('file')->pasteImage($this->post->commentText);
            $result  = $this->repo->updateComment($commentID, $comment);
            echo $comment;
        }
    }
}
