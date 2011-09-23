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
    const afterBlockMove = 'kitpages.cms.zone.after.block_move';

    const onZonePublish = 'kitpages.cms.zone.on.publish';
    const afterZonePublish = 'kitpages.cms.zone.after.publish';

    const onZoneUnpublish = 'kitpages.cms.zone.on.unpublish';
    const afterZoneUnpublish = 'kitpages.cms.zone.after.unpublish';

    const onZoneDelete = 'kitpages.cms.zone.on.delete';
    const afterZoneDelete = 'kitpages.cms.zone.after.delete';


    ////
    // page events
    ////
    const afterPageModify = 'kitpages.cms.page.after.modify';

    const onPagePublish = 'kitpages.cms.page.on.publish';
    const afterPagePublish = 'kitpages.cms.page.after.publish';

    const onMultiplePagePublish = 'kitpages.cms.page.on.multiple_publish';
    const afterMultiplePagePublish = 'kitpages.cms.page.after.multiple_publish';

    const onPageUnpublish = 'kitpages.cms.page.on.unpublish';
    const afterPageUnpublish = 'kitpages.cms.page.after.unpublish';

    const onPageDelete = 'kitpages.cms.page.on.delete';
    const afterPageDelete = 'kitpages.cms.page.after.delete';

    const onPagePendingDelete = 'kitpages.cms.page.on.pending_delete';
    const afterPagePendingDelete = 'kitpages.cms.page.after.pending_delete';

    const onPageUnpendingDelete = 'kitpages.cms.page.on.unpending_delete';
    const afterPageUnpendingDelete = 'kitpages.cms.page.after.unpending_delete';

    ////
    // nav events
    ////
    const onNavMove = 'kitpages.cms.nav.on.move';
    const afterNavMove = 'kitpages.cms.nav.after.move';

    const onNavPublish = 'kitpages.cms.nav.on.publish';
    const afterNavPublish = 'kitpages.cms.nav.after.publish';

    const onNavUnpublish = 'kitpages.cms.nav.on.unpublish';
    const afterNavUnpublish = 'kitpages.cms.nav.after.unpublish';

}