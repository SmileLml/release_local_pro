<?php
class feedbackTodo extends todoModel
{
    public function create($date, $account)
    {
        $todoID = parent::create($date, $account);
        if(empty($todoID)) return false;

        if($this->post->feedback) $this->dao->update(TABLE_TODO)->set('feedback')->eq($this->post->feedback)->where('id')->eq($todoID)->exec();

        /* If todo is from feedback, record action for feedback. */
        $todo = $this->getByID($todoID);
        if($todo->type == 'feedback' && $todo->idvalue)
        {
            $feedback = new stdclass();
            $feedback->status        = 'commenting';
            $feedback->result        = $todoID;
            $feedback->processedBy   = $this->app->user->account;
            $feedback->processedDate = helper::now();
            $feedback->solution      = 'totodo';

            $this->dao->update(TABLE_FEEDBACK)->data($feedback)->where('id')->eq($todo->idvalue)->exec();

            $this->loadModel('action')->create('feedback', $todo->idvalue, 'totodo', '', $todoID);

            $this->loadModel('feedback')->updateStatus('todo', $todo->idvalue, $todo->status);
        }

        return $todoID;
    }
}
