<?php
public function mergeFiles($docID)
{
    return $this->loadExtension('zentaobiz')->mergeFiles($docID);
}

public function diff($text1, $text2)
{
    return $this->loadExtension('zentaobiz')->diff($text1, $text2);
}

public function isImage($text)
{
    return $this->loadExtension('zentaobiz')->isImage($text);
}

public function diffImage($image1, $image2)
{
    return $this->loadExtension('zentaobiz')->diffImage($image1, $image2);
}

public function checkPrivLib($object, $extra = '')
{
    return $this->loadExtension('zentaobiz')->checkPrivLib($object, $extra);
}

public function checkPrivDoc($object)
{
    return $this->loadExtension('zentaobiz')->checkPrivDoc($object);
}

public function createLib()
{
    return $this->loadExtension('zentaobiz')->createLib();
}

public function updateLib($libID)
{
    return $this->loadExtension('zentaobiz')->updateLib($libID);
}

public function create()
{
    return $this->loadExtension('zentaobiz')->create();
}

public function update($docID)
{
    return $this->loadExtension('zentaobiz')->update($docID);
}

public function getAdminCatalog($bookID, $nodeID, $serials)
{
    return $this->loadExtension('zentaobiz')->getAdminCatalog($bookID, $nodeID, $serials);
}

public function computeSN($bookID, $from = 'doc')
{
    return $this->loadExtension('zentaobiz')->computeSN($bookID, $from);
}

public function getChildren($bookID, $nodeID = 0)
{
    return $this->loadExtension('zentaobiz')->getChildren($bookID, $nodeID);
}

public function manageCatalog($bookID, $nodeID)
{
    return $this->loadExtension('zentaobiz')->manageCatalog($bookID, $nodeID);
}

public function getBookStructure($bookID)
{
    return $this->loadExtension('zentaobiz')->getBookStructure($bookID);
}

public function getFrontCatalog($bookID, $serials, $articleID = 0)
{
    return $this->loadExtension('zentaobiz')->getFrontCatalog($bookID, $serials, $articleID);
}

public function sortBookOrder()
{
    return $this->loadExtension('zentaobiz')->sortBookOrder();
}

public function getBookOptionMenu($bookID, $removeRoot = false)
{
    return $this->loadExtension('zentaobiz')->getBookOptionMenu($bookID, $removeRoot);
}

public function fixPath($bookID)
{
    return $this->loadExtension('zentaobiz')->fixPath($bookID);
}

public function getChildModules($parentID)
{
    return $this->loadExtension('zentaobiz')->getChildModules($parentID);
}

public function setDocPOST($docID, $version = 0)
{
    return $this->loadExtension('zentaobiz')->setDocPOST($docID, $version);
}
