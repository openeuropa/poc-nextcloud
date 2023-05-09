<?php

declare(strict_types = 1);

namespace Drupal\poc_nextcloud\Tracking\RecordSubmit;

use Drupal\poc_nextcloud\Endpoint\NxGroupFolderEndpoint;
use Drupal\poc_nextcloud\NextcloudConstants;
use Drupal\poc_nextcloud\Tracking\Tracker;

/**
 * Submit handler to set access for Nc groups to Nc group folders.
 */
class NcGroupFolderGroupSubmit implements TrackingRecordSubmitInterface {

  /**
   * Constructor.
   *
   * @param \Drupal\poc_nextcloud\Endpoint\NxGroupFolderEndpoint $groupFolderEndpoint
   *   Group folder endpoint.
   */
  public function __construct(
    private NxGroupFolderEndpoint $groupFolderEndpoint,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function submitTrackingRecord(array &$record, int $op): void {
    [
      // The group folder id has to come from a table join.
      'nc_group_id' => $group_id,
      'nc_permissions' => $permissions,
    ] = $record;
    $group_folder_id = (int) $record['nc_group_folder_id'] ?: NULL;

    if ($group_folder_id === NULL) {
      throw new \Exception('Missing group folder id in tracking record.');
    }

    switch ($op) {
      case Tracker::OP_INSERT:
      case Tracker::OP_UPDATE:
        if ($op === Tracker::OP_INSERT) {
          $this->groupFolderEndpoint->addGroup($group_folder_id, $group_id);
        }
        $this->groupFolderEndpoint->setGroupPermissions($group_folder_id, $group_id, $permissions & NextcloudConstants::PERMISSION_ALL);
        $this->groupFolderEndpoint->setManageAclGroup($group_folder_id, $group_id, (bool) ($permissions & NextcloudConstants::PERMISSION_ADVANCED));
        break;

      case Tracker::OP_DELETE:
        // Remove the group from the group folder.
        $this->groupFolderEndpoint->removeGroup($group_folder_id, $group_id);
        break;

      default:
        throw new \RuntimeException('Unexpected operation.');
    }
  }

}
