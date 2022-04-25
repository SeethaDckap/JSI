<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\AccessRight\Acl\AclResource;

class TreeBuilder
{
    /**
     * Transform resource list into sorted resource tree that includes only active resources
     *
     * @param array $resourceList
     * @return array
     */

    protected $_allresource = [];
    public function build(array $resourceList)
    {
        $result = [];
        foreach ($resourceList as $resource) {
            if ($resource['disabled']) {
                continue;
            }
            unset($resource['disabled']);
            $this->_allresource[] = $resource['id'];
            $resource['children'] = $this->build($resource['children']);
            $result[] = $resource;
        }
        usort($result, [$this, '_sortTree']);
        return $result;
    }
    public function getAllResource(array $resourceList)
    {
        if(empty($this->_allresource)){
            $this->build($resourceList);
        }
        return $this->_allresource;
    }


    /**
     * Sort ACL resource nodes
     *
     * @param array $nodeA
     * @param array $nodeB
     * @return int
     */
    protected function _sortTree(array $nodeA, array $nodeB)
    {
        return $nodeA['sortOrder'] < $nodeB['sortOrder'] ? -1 : ($nodeA['sortOrder'] > $nodeB['sortOrder'] ? 1 : 0);
    }
}
