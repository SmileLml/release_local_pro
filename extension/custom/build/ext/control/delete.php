<?php

helper::importControl('build');

class mybuild extends build
{
    /**
     * Delete a build.
     *
     * @param  int    $buildID
     * @param  string $confirm  yes|noe
     * @access public
     * @return void
     */
    public function delete($buildID, $confirm = 'no')
    {
        if($confirm == 'no')
        {
            return print(js::confirm($this->lang->build->confirmDelete, $this->createLink('build', 'delete', "buildID=$buildID&confirm=yes")));
        }
        else
        {
            $build = $this->build->getById($buildID);
            $link = $this->app->tab == 'project' ? $this->createLink('projectbuild', 'browse',  "projectID=$build->project") : $this->createLink('execution', 'build', "executionID=$build->execution");
            if(!empty($build) && !empty($build->project) && !empty($build->product))
            {
                $releaseCount = $this->build->checkReleaseByBuild($build->project, $build->product, $buildID);
                if($releaseCount > 0) return $this->send(array('result' => 'fail', 'message' => $this->lang->build->deleteLinkReleaseError, 'locate'=> $link));
            }
            $this->build->delete(TABLE_BUILD, $buildID);

            $message = $this->executeHooks($buildID);
            if($message) $response['message'] = $message;

            /* if ajax request, send result. */
            if($this->server->ajax)
            {
                if(dao::isError())
                {
                    $response['result']  = 'fail';
                    $response['message'] = dao::getError();
                }
                else
                {
                    $response['result']  = 'success';
                    $response['message'] = '';
                }
                return $this->send($response);
            }

            if(defined('RUN_MODE') && RUN_MODE == 'api') return $this->send(array('status' => 'success'));
            return print(js::locate($link, 'parent'));
        }
    }
}