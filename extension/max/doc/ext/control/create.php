<?php
class myDoc extends doc
{
    /**
     * Create a doc.
     *
     * @param  int    $objectType
     * @param  int    $objectID
     * @param  int    $libID
     * @param  int    $moduleID
     * @param  string $docType
     * @param  string $from
     * @access public
     * @return void
     */
    public function create($objectType, $objectID, $libID, $moduleID = 0, $docType = '', $from = 'doc')
    {
        $this->app->loadLang('baseline');
        parent::create($objectType, $objectID, $libID, $moduleID, $docType, $from);
    }
}
