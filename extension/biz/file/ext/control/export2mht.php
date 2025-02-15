<?php
helper::importControl('file');
class myfile extends file
{
    public function export2mht()
    {
        $this->view->fields = $this->post->fields;
        $this->view->rows   = $this->post->rows;
        $this->host         = common::getSysURL();

        switch($this->post->kind)
        {
        case 'task':
            foreach($this->view->rows as $row)
            {
                $row->name = html::a($this->host . $this->createLink('task', 'view', "taskID=$row->id"), $row->name, '_blank');
            }
            break;
        case 'story':
            foreach($this->view->rows as $row)
            {
                $row->title= html::a($this->host . $this->createLink('story', 'view', "storyID=$row->id"), $row->title, '_blank');
            }
            break;
        case 'bug':
            foreach($this->view->rows as $row)
            {
                $row->title= html::a($this->host . $this->createLink('bug', 'view', "bugID=$row->id"), $row->title, '_blank');
            }
            break;
        case 'testcase':
            foreach($this->view->rows as $row)
            {
                $row->title= html::a($this->host . $this->createLink('testcase', 'view', "caseID=$row->id"), $row->title, '_blank');
            }
            break;
        }
        $this->view->fileName = $this->post->fileName;
        $output = $this->parse('file', 'export2Html');
        $output = $this->file->getMhtDocument($output, $this->host);

        $this->sendDownHeader($this->post->fileName, 'mht', $output);
    }
}
