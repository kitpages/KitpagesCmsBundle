<?php
namespace Kitpages\CmsBundle;

final class KitpagesCmsEvents
{
    ////
    // block events
    ////
    const afterBlockModify = 'kitpages.cms.block.after.modify';
    
    const onBlockPublish = 'kitpages.cms.block.on.publish';
    const afterBlockPublish = 'kitpages.cms.block.on.publish';
    
    const onBlockDelete = 'kitpages.cms.block.on.delete';
    const afterBlockDelete = 'kitpages.cms.block.after.delete';
    
    ////
    // zone events
    ////
    const onBlockMove = 'kitpages.cms.zone.on.block_move'; 
    const afterBlockMove = 'kitpages.cms.zone.on.block_move'; 
    
    const onZonePublish = 'kitpages.cms.zone.on.publish';
    const afterZonePublish = 'kitpages.cms.zone.after.publish';
}