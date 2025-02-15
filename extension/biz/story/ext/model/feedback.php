<?php
public function create($executionID = 0, $bugID = 0, $from = '', $extra = '')
{
    return $this->loadExtension('feedback')->create($executionID, $bugID, $from, $extra);
}

public function getById($storyID, $version = 0, $setImgSize = false)
{
    return $this->loadExtension('feedback')->getById($storyID, $version, $setImgSize);
}
