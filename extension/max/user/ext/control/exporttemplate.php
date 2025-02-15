<?php
class myUser extends user
{
    public function exportTemplate()
    {
        $this->loadModel('transfer');
        if($_POST)
        {
            $this->user->setListValue();

            foreach($this->config->user->export->templateFields as $field) $fields[$field] = $this->lang->user->$field;

            $this->post->set('fields', $fields);
            $this->post->set('kind', 'user');
            $this->post->set('rows', array());
            $this->post->set('extraNum',   $this->post->num);
            $this->post->set('fileName', 'userTemplate');
            $this->fetch('file', 'export2' . $this->post->fileType, $_POST);
        }

        $this->display();
    }
}
