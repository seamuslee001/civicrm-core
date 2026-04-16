<?php
/*
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC. All rights reserved.                        |
 |                                                                    |
 | This work is published under the GNU AGPLv3 license with some      |
 | permitted exceptions and without any warranty. For full license    |
 | and copyright information, see https://civicrm.org/licensing       |
 +--------------------------------------------------------------------+
 */

use Civi\Api4\CustomField;

/**
 * Upgrade logic for the 6.15.x series.
 *
 * Each minor version in the series is handled by either a `6.15.x.mysql.tpl` file,
 * or a function in this class named `upgrade_6_15_x`.
 * If only a .tpl file exists for a version, it will be run automatically.
 * If the function exists, it must explicitly add the 'runSql' task if there is a corresponding .mysql.tpl.
 *
 * This class may also implement `setPreUpgradeMessage()` and `setPostUpgradeMessage()` functions.
 */
class CRM_Upgrade_Incremental_php_SixFifteen extends CRM_Upgrade_Incremental_Base {

  /**
   * Upgrade step; adds tasks including 'runSql'.
   *
   * @param string $rev
   *   The version number matching this function name
   */
  public function upgrade_6_15_alpha1($rev): void {
    $this->addTask(ts('Upgrade DB to %1: SQL', [1 => $rev]), 'runSql', $rev);
    $this->addTask('dev/core#6390 Remove indexes from serialied custom fields', 'removeSearchIndexForSerializedCustomFields');
  }

  public static function removeSearchIndexForSerializedCustomFields($ctx): bool {
    $customFields = CustomField::get(FALSE)
      ->addWhere('serialize', '!=', 0)
      ->addWhere('is_searchable', '=', TRUE)
      ->execute();
    foreach ($customFields as $customField) {
      $params = [
        'id' => $customField['id'],
        'is_searchable' => 0,
        'serialize' => $customField['serialize'],
      ];
      CRM_Core_BAO_CustomFIeld::create($params);
    }
    return TRUE;
  }

}
