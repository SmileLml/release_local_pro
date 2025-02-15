<?php
/**
 * The model file of conference module of XXB.
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
class conferenceModel extends model
{
    /**
     * Make a header with signature for the use of owt api requests.
     *
     * @return array
     */
    function makeHeader()
    {
        $this->loadModel('setting');

        /* $cnonce should be an int between 0 and 99999. */
        $cnonce = rand(0, 99999);

        /* $timestamp should include milliseconds. */
        $timestamp = round(microtime(true) * 1000);

        $auth = array();
        $auth['Mauth realm']            = 'http://webrtc.intel.com';
        $auth['mauth_signature_method'] = 'HMAC_SHA256';
        $auth['mauth_serviceid']        = $this->setting->getItem("owner=system&module=owt&section=common&key=serviceid");
        $auth['mauth_cnonce']           = $cnonce;
        $auth['mauth_timestamp']        = $timestamp;

        /* Generate the signature and convert to base64. */
        $rawSignature = hash_hmac('sha256', $timestamp . ',' . $cnonce, $this->setting->getItem("owner=system&module=owt&section=common&key=servicekey"));
        $auth['mauth_signature'] = base64_encode($rawSignature);

        /* Implode the keys and values of the $auth array into a string connected by comma and linebreak along with contentType json. */
        return array('Authorization: ' . urldecode(http_build_query($auth, '', ',')), 'Content-Type: application/json');
    }

    /**
     * Make request to owt server with given path, method and params.
     *
     * @param  string       $path
     * @param  string       $method
     * @param  string|array $params
     * @return string
     */
    function makeRequest($path, $method = 'GET', $params = '')
    {
        /* Convert params to JSON string if it is object or array. */
        if(is_array($params)) $params = json_encode($params);

        $config = $this->getConfiguration();
        $protocol = $config->https != 'false' ? 'https' : 'http';
        $server   = $config->serveraddr;
        $mgmtPort = $config->backendtype != 'srs' ? $config->mgmtport : $config->apiport;

        /* Prepare curl handler and set its params. */
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "$protocol://$server:$mgmtPort$path");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->makeHeader());
        /* Allow the specification of other methods like DELETE and PUT. */
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        /* Allow "unsafe" connections. */
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        /* Prevent curl from long waiting. */
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

        /* Execute request and return result. */
        $result = curl_exec($ch);
        return $result;
    }

    /**
     * Create a room with given name in owt server.
     *
     * @param  string $name
     * @access public
     * @return array
     */
    public function createRoom($name)
    {
        $presenter = (object) array
        (
            'role' => 'presenter',
            'publish' => (object) array
            (
                'video' => true,
                'audio' => true,
            ),
            'subscribe' => (object) array
            (
                'video' => true,
                'audio' => true,
            ),
        );
        $member = (object) array
        (
            'role' => 'member',
            'publish' => (object) array
            (
                'video' => true,
                'audio' => true,
            ),
            'subscribe' => (object) array
            (
                'video' => true,
                'audio' => true,
            ),
        );

        $options = new stdclass();
        $options->name = $name;
        $options->roles = array($presenter, $member);
        $roomConfig = array('name' => $name, 'options' => $options);
        $data = $this->makeRequest('/v1/rooms', 'POST', $roomConfig);
        $room = json_decode($data);
        if(isset($room->error)) return false;
        // if xxb video config, then update room
        $owt = $this->queryVideoConfig();
        if(!empty($owt) && !empty($room)) $this->updateRoomByVideoConfig($owt, $room);
        return $room;
    }

    /**
     * Batch update room by video config.
     *
     * @access public
     * @return void
     */
    public function batchUpdateRoom()
    {
        $data = $this->listRooms();
        $roomListInfo = json_decode($data);
        $owt = $this->queryVideoConfig();
        foreach($roomListInfo as $roomInfo) $this->updateRoomByVideoConfig($owt, $roomInfo);
    }

    /**
     * Quey room video config.
     *
     * @access public
     * @return object
     */
    public function queryVideoConfig()
    {
        $owt = new stdclass();
        $configItems = $this->loadModel('setting')->getItems("owner=system&module=owt&section=common&key=resolutionwidth,resolutionheight");
        foreach($configItems as $configItem) $owt->{$configItem->key} = $configItem->value;
        return $owt;
    }

    /**
     * Update room  by video config.
     *
     * @access public
     * @return array
     */
    public function updateRoomByVideoConfig($owt, $room)
    {
        if(!empty($owt->resolutionwidth) && !empty($owt->resolutionheight))
        {
            $room->views[0]->video->parameters->resolution->width  = $owt->resolutionwidth;
            $room->views[0]->video->parameters->resolution->height = $owt->resolutionheight;
        }
        return $this->updateRoom($room->id, json_encode($room));
    }

    /**
     * Get a room by given id from owt server.
     *
     * @param  string $roomId
     * @access public
     * @return array
     */
    public function getRoom($roomId)
    {
        $data = $this->makeRequest("/v1/rooms/$roomId");
        $room = json_decode($data);
        if(isset($room->error)) return false;
        return $room;
    }

    /**
     * Create room list from owt server.
     *
     * @access public
     * @return array
     */
    public function listRooms()
    {
        return $this->makeRequest('/v1/rooms');
    }

    /**
     * Delete a room by given id in owt server.
     *
     * @param  string $roomId
     * @access public
     * @return array
     */
    public function deleteRoom($roomId)
    {
        return $this->makeRequest("/v1/rooms/$roomId", 'DELETE');
    }

    /**
     * Update a room with given id and room data in owt server.
     *
     * @param  string       $roomId
     * @param  string|array $roomData
     * @access public
     * @return array
     */
    public function updateRoom($roomId, $roomData)
    {
        return $this->makeRequest("/v1/rooms/$roomId", 'PUT', $roomData);
    }

    /**
     * Get current owt configuration.
     *
     * @param  string $type to get config for xxc, type should be 'client'.
     * @access public
     * @return object
     */
    public function getConfiguration($type = '')
    {
        $conferenceConfig = new stdclass();
        $configKeys = $this->config->conference->configItems;
        $configKeys = strtolower(join(',', array_unique(array_merge($configKeys->owt, $configKeys->srs, $configKeys->common))));
        $configItems = $this->loadModel('setting')->getItems("owner=system&module=owt&section=common&key=backendtype,enabled,https,$configKeys");
        foreach($configItems as $configItem) $conferenceConfig->{$configItem->key} = $configItem->value;

        /* Detached conference requires license. */
        $conferenceConfig->detachedConference = isset($conferenceConfig->detachedConference) && $conferenceConfig->detachedConference === 'true' && extCommonModel::ilMethod('conference', 'detachedConference');

        if($type == 'client')
        {
            if(!isset($conferenceConfig->enabled) || $conferenceConfig->enabled == 'false' || empty($conferenceConfig->serveraddr) || ($conferenceConfig->backendtype == 'owt' && (empty($conferenceConfig->serviceid) || empty($conferenceConfig->servicekey)))) return false;

            $conf = new stdclass();
            $conf->backend            = isset($conferenceConfig->backendtype) ? $conferenceConfig->backendtype : 'owt';
            $conf->host               = $conferenceConfig->serveraddr;
            $conf->https              = !(isset($conferenceConfig->https) && $conferenceConfig->https === 'false');
            $conf->api                = ($conf->https ? 'https' : 'http') . "://{$conferenceConfig->serveraddr}:{$conferenceConfig->apiport}";
            $conf->detachedConference = $conferenceConfig->detachedConference;

            if($conf->backend == 'srs') $conf->rtc = ($conf->https ? 'wss' : 'ws') . "://{$conferenceConfig->serveraddr}:{$conferenceConfig->rtcport}";

            return $conf;
        }

        return $conferenceConfig;
    }

    /**
     * Check and set incoming owt configuration.
     *
     * @param  array $config
     * @access public
     * @return void
     */
    public function setConfiguration()
    {
        $post = fixer::input('post')
            ->setIF($this->post->backendType != '', 'backendType', trim($this->post->backendType))
            ->setIF($this->post->serviceId   != '', 'serviceId',   trim($this->post->serviceId))
            ->setIF($this->post->serviceKey  != '', 'serviceKey',  trim($this->post->serviceKey))
            ->setIF($this->post->serverAddr  != '', 'serverAddr',  trim($this->post->serverAddr))
            ->setIF($this->post->apiPort     != '', 'apiPort',     trim($this->post->apiPort))
            ->setIF($this->post->mgmtPort    != '', 'mgmtPort',    trim($this->post->mgmtPort))
            ->setIF($this->post->rtcPort     != '', 'rtcPort',     trim($this->post->rtcPort))
            ->get();

        $backendType = $post->backendType;

        $errors = array();
        foreach(array_merge($this->config->conference->configItems->$backendType) as $item)
        {
            if(empty($post->{$item})) $errors[$item][]  = $this->lang->conference->inputError->{$item};
        }
        $openConferences = $this->loadModel('im')->conferenceGetOpenConferences();
        if(!empty($openConferences)) $errors['detachedConference'][] = $this->lang->conference->settingsError->hasOpenConferences;
        if(!empty($errors)) return array('result' => 'fail', 'message' => $errors);

        $this->loadModel('setting');
        $this->setting->setItem('system.owt.common.enabled',     !isset($post->enabled) || empty($post->enabled) ? 'false' : 'true');
        $this->setting->setItem('system.owt.common.backendtype', $backendType);
        $this->setting->setItem('system.owt.common.https',       !isset($post->https) || empty($post->https) ? 'false' : 'true');
        $this->setting->setItem('system.owt.common.serveraddr',  $post->serverAddr);
        $this->setting->setItem('system.owt.common.apiport',     $post->apiPort);
        if($backendType == 'owt')
        {
            $this->setting->setItem('system.owt.common.serviceid',  $post->serviceId);
            $this->setting->setItem('system.owt.common.servicekey', $post->serviceKey);
            $this->setting->setItem('system.owt.common.mgmtport',   $post->mgmtPort);
        }
        if($backendType == 'srs')
        {
            $this->setting->setItem('system.owt.common.rtcport', $post->rtcPort);
            if(extCommonModel::ilMethod('conference', 'detachedConference'))
            {
                $this->setting->setItem('system.owt.common.detachedConference', !isset($post->detachedConference) || empty($post->detachedConference) ? 'false' : 'true');
                if (isset($post->detachedConference) && $post->detachedConference == 'true')
                {
                    $this->im->message->createDetachedConferenceEnableNotify(); // TODO: optimize notification creation.
                }
            }
        }

        if(dao::isError()) return array('result' => 'fail', 'message' => dao::getError());

        return array('result' => 'success', 'message' => $this->lang->saveSuccess, 'locate' => inlink('admin'));
    }

    /**
     * Check and set incoming owt video configuration.
     *
     * @param  array $config
     * @access public
     * @return void
     */
    public function setVideoConfiguration()
    {
        $post = fixer::input('post')
            ->setIF($this->post->resolutionWidth  != '', 'resolutionWidth',  trim($this->post->resolutionWidth))
            ->setIF($this->post->resolutionHeight != '', 'resolutionHeight', trim($this->post->resolutionHeight))
            ->get();

        $errors = array();
        foreach(array('resolutionWidth', 'resolutionHeight') as $item)
        {
            if(empty($post->{$item})) $errors[$item][]  = $this->lang->conference->inputError->{$item};
        }
        if(!empty($errors)) return array('result' => 'fail', 'message' => $errors);

        $this->loadModel('setting');
        $this->setting->setItem('system.owt.common.resolutionwidth', $post->resolutionWidth);
        $this->setting->setItem('system.owt.common.resolutionheight', $post->resolutionHeight);

        if(dao::isError()) return array('result' => 'fail', 'message' => dao::getError());

        return array('result' => 'success', 'message' => $this->lang->saveSuccess, 'locate' => inlink('admin'));
    }

    /**
     * Check if owt functionality is enabled.
     *
     * @access public
     * @return boolean
     */
    public function isEnabled()
    {
        return filter_var($this->loadModel('setting')->getItem("owner=system&module=owt&section=common&key=enabled"), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Remove user from SRS server with clients delete api.
     *
     * @param  int     $userID
     * @access public
     * @return bool
     */
    public function removeUserFromSRS($userID)
    {
        $config = $this->getConfiguration();
        if($config->backendtype != 'srs') return false;

        $clientsResponse = $this->makeRequest('/api/v1/clients/', 'GET');
        $clientsData = json_decode($clientsResponse);

        if(isset($clientsData->clients) && count($clientsData->clients) > 0)
        {
            foreach($clientsData->clients as $client)
            {
                if(isset($client->tcUrl))
                {
                    $clientUser = substr($client->tcUrl, strrpos($client->tcUrl, '/') + 1);
                    if($clientUser == "user-$userID")
                    {
                        $deleteResponse = $this->makeRequest("/api/v1/clients/$client->id", 'DELETE');
                        $deleteResponseData = json_decode($deleteResponse);
                        if($deleteResponseData->code == 0) return true;
                    }
                }
            }
        }

        return false;
    }
}
