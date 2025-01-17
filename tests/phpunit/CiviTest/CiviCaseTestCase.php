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

use Civi\Api4\RelationshipType;

/**
 * Class CiviReportTestCase
 */
class CiviCaseTestCase extends CiviUnitTestCase {

  /**
   * @var string
   * Symbolic-name
   */
  protected $caseType;

  protected $caseTypeId;

  protected $caseStatusGroup;

  protected $optionValues;

  /**
   * Tables to truncate as part of cleanup
   * @var array
   */
  protected $tablesToTruncate;

  public function setUp(): void {
    parent::setUp();

    // CRM-9404 - set-up is a bit cumbersome but had to put something in place to set up activity types & case types
    //. Using XML was causing breakage as id numbers were changing over time
    // & was really hard to troubleshoot as involved truncating option_value table to mitigate this & not leaving DB in a
    // state where tests could run afterwards without re-loading.
    $this->caseStatusGroup = $this->callAPISuccess('option_group', 'get', [
      'name' => 'case_status',
      'format.only_id' => 1,
    ]);
    $optionValues = [
      'Medical evaluation' => 'Medical evaluation',
      'Mental health evaluation' => "Mental health evaluation",
      'Secure temporary housing' => 'Secure temporary housing',
      'Long-term housing plan' => 'Long-term housing plan',
      'ADC referral' => 'ADC referral',
      'Income and benefits stabilization' => 'Income and benefits stabilization',
    ];
    foreach ($optionValues as $name => $label) {
      $activityTypes = CRM_Core_BAO_OptionValue::ensureOptionValueExists([
        'option_group_id' => 'activity_type',
        'name' => $name,
        'label' => $label,
        'component_id' => 'CiviCase',
      ]);
      // store for cleanup
      // @todo is this ever used?
      $this->optionValues[] = $activityTypes['id'];
    }

    // We used to be inconsistent about "HousingSupport" vs "housing_support".
    // Now, the rule is simply: use the "name" from "civicrm_case_type.name".
    $this->caseType = 'housing_support';
    $this->caseTypeId = 1;

    $this->individualCreate(['first_name' => 'site', 'last_name' => 'admin'], 'site_admin');
    $this->loadAllFixtures();

    // enable the default custom templates for the case type xml files
    $this->customDirectories(['template_path' => TRUE]);

    /** @var \CRM_Utils_Hook_UnitTests $hooks  */
    $hooks = \CRM_Utils_Hook::singleton();
    $hooks->setHook('civicrm_caseTypes', [$this, 'hook_caseTypes']);
    \CRM_Case_XMLRepository::singleton(TRUE);
    \CRM_Case_XMLProcessor::flushStaticCaches();

    // create a logged in USER since the code references it for source_contact_id
    $this->createLoggedInUser();
  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  public function tearDown(): void {
    $this->customDirectories(['template_path' => FALSE]);
    $this->tablesToTruncate = [
      'civicrm_activity',
      'civicrm_contact',
      'civicrm_case',
      'civicrm_case_contact',
      'civicrm_case_activity',
      'civicrm_case_type',
      'civicrm_activity_contact',
      'civicrm_managed',
      'civicrm_relationship',
      'civicrm_uf_match',
      'civicrm_activity',
      'civicrm_group_contact',
      'civicrm_file',
      'civicrm_entity_file',
    ];
    $this->quickCleanup($this->tablesToTruncate, TRUE);
    CRM_Case_XMLRepository::singleton(TRUE);
    parent::tearDown();
  }

  public static function setUpBeforeClass(): void {
    parent::setUpBeforeClass();
    CRM_Core_BAO_ConfigSetting::enableComponent('CiviCase');
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public static function tearDownAfterClass(): void {
    CRM_Core_BAO_ConfigSetting::disableComponent('CiviCase');
    RelationshipType::delete(FALSE)
      ->addWhere('name_b_a', 'IN', [
        'Homeless Services Coordinator',
        'Health Services Coordinator',
        'Senior Services Coordinator',
        'Benefits Specialist',
      ])
      ->execute();
    parent::tearDownAfterClass();
  }

  /**
   * Subclasses may override this if they want to be explicit about the case-type definition.
   *
   * @param $caseTypes
   * @see CRM_Utils_Hook::caseTypes
   */
  public function hook_caseTypes(&$caseTypes) {
  }

}
