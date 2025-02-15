<?php
/**
 * The control file of conference module of XXB.
 *
 * @copyright   Copyright 2009-2023 禅道软件（青岛）有限公司(ZenTao Software (Qingdao) Co., Ltd., www.zentao.net)
 * @license     ZOSL (https://zpl.pub/page/zoslv1.html)
 * @author      Wenrui LI <liwenrui@easycorp.ltd>
 * @package     conference
 * @version     $Id$
 * @link        https://xuanim.com
 */
?>
<?php
class conference extends control
{
    /**
     * View and set owt configuration.
     *
     * @param  string $type type could be 'server', 'video', 'edit'
     * @access public
     * @return void
     */
    public function admin($type = 'server')
    {
        if(!empty($_POST))
        {
            if($type == 'video')
            {
                $result = $this->conference->setVideoConfiguration($_POST);
                //update all room video config now
                $data = $this->conference->batchUpdateRoom();
            }
            else
            {
                $result = $this->conference->setConfiguration($_POST);
            }
            $this->send($result);
        }

        $conferenceConfig = $this->conference->getConfiguration();

        $this->view->title              = $this->lang->conference->common;
        $this->view->type               = $type;
        $this->view->backendType        = isset($conferenceConfig->backendtype) ? $conferenceConfig->backendtype : 'owt';
        $this->view->enabled            = isset($conferenceConfig->enabled) && $conferenceConfig->enabled == 'true';
        $this->view->serviceId          = isset($conferenceConfig->serviceid) ? $conferenceConfig->serviceid : '';
        $this->view->serviceKey         = isset($conferenceConfig->servicekey) ? $conferenceConfig->servicekey : '';
        $this->view->serverAddr         = isset($conferenceConfig->serveraddr) ? $conferenceConfig->serveraddr : '';
        $this->view->apiPort            = isset($conferenceConfig->apiport) ? $conferenceConfig->apiport : '3004';
        $this->view->mgmtPort           = isset($conferenceConfig->mgmtport) ? $conferenceConfig->mgmtport : '3300';
        $this->view->rtcPort            = isset($conferenceConfig->rtcport) ? $conferenceConfig->rtcport : '1989';
        $this->view->https              = !isset($conferenceConfig->https) || $conferenceConfig->https == 'true';
        $this->view->resolutionWidth    = isset($conferenceConfig->resolutionwidth) ? $conferenceConfig->resolutionwidth : '';
        $this->view->resolutionHeight   = isset($conferenceConfig->resolutionheight) ? $conferenceConfig->resolutionheight : '';
        $this->view->detachedConference = isset($conferenceConfig->detachedConference) && $conferenceConfig->detachedConference == 'true';

        $this->display();
    }
}
