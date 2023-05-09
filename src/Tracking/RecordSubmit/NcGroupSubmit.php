<?php

declare(strict_types = 1);

namespace Drupal\poc_nextcloud\Tracking\RecordSubmit;

use Drupal\poc_nextcloud\Endpoint\NxGroupEndpoint;
use Drupal\poc_nextcloud\Tracking\Tracker;

/**
 * Writes pending groups to Nextcloud.
 */
class NcGroupSubmit implements TrackingRecordSubmitInterface {

  /**
   * Constructor.
   *
   * @param \Drupal\poc_nextcloud\Endpoint\NxGroupEndpoint $groupEndpoint
   *   Group endpoint.
   */
  public function __construct(
    private NxGroupEndpoint $groupEndpoint,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function submitTrackingRecord(array &$record, int $op): void {
    [
      'nc_group_id' => $group_id,
      'nc_display_name' => $display_name,
    ] = $record;

    switch ($op) {
      case Tracker::OP_UPDATE:
        $this->groupEndpoint->setDisplayName($group_id, $display_name);
        break;

      case Tracker::OP_INSERT:
        $this->groupEndpoint->insert($group_id, $display_name);
        break;

      case Tracker::OP_DELETE:
        $this->groupEndpoint->delete($group_id);
        break;

      default:
        throw new \RuntimeException('Unexpected operation.');
    }
  }

}
