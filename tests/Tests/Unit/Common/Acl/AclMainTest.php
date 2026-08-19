<?php

/**
 * AclMainTest.php
 * @package openemr
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

/**
 * Handles unit tests of the AclMain class
 *
 * @package OpenEMR\RestControllers\SMART
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2020 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Unit\Common\Acl;

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Services\UserService;
use PHPUnit\Framework\TestCase;

class AclMainTest extends TestCase
{
    /**
     * Unit test to explore the ACLs and verify the checks are working properly.
     */
    public function testAclCheckCore(): void
    {
        // make sure we've cleared all GACL caches here...
        AclMain::clearGaclCache();

        // we assume in our unit tests that our admin user will have access to certain parts of the database
        $adminUsername = getenv("OE_USER", true) ?: "admin";
        $userService = new UserService();

        $admin = $userService->getUserByUsername($adminUsername);
        $this->assertNotEmpty($admin, "Admin user should be in database for unit tests to execute");

        $accessToPatientDemo = AclMain::aclCheckCore('patients', 'demo', $adminUsername);
        $this->assertTrue($accessToPatientDemo, "Admin has access to view patient list");

        $isSuperUser = AclMain::aclCheckCore('admin', 'users', $adminUsername);
        $this->assertTrue($isSuperUser, "Has access to admin section");

        // TODO: we need to write a WHOLE lot more ACL tests here.
    }

    /**
     * Regression test for RDY-0016 A-10 (EV-016-A10-acl-probes.md / EV-016-A10-fix-scope.md §1a):
     * a form directory with no `registry` row must deny access, not fail open. Before the fix,
     * a missing row made aclCheckForm() pass null into aclCheckAcoSpec(), whose
     * empty-spec-means-unrestricted contract then granted access to anyone.
     */
    public function testAclCheckFormDeniesWhenNoRegistryRow(): void
    {
        AclMain::clearGaclCache();

        $result = AclMain::aclCheckForm('rdy0016_a10_regression_test_nonexistent_form_directory');
        $this->assertFalse($result, "A form directory with no registry row must be denied, not fail open");
    }

    /**
     * Negative control for the above: a form directory that *is* registered with a real aco_spec
     * must still resolve through the normal aclCheckAcoSpec() path, unaffected by the fix.
     */
    public function testAclCheckFormStillResolvesRegisteredForm(): void
    {
        AclMain::clearGaclCache();

        $adminUsername = getenv("OE_USER", true) ?: "admin";
        // 'newpatient' carries registry.aco_spec = 'patients|appt' on the audited baseline;
        // an administrator holds that ACO, so this must remain true post-fix.
        $result = AclMain::aclCheckForm('newpatient', $adminUsername);
        $this->assertTrue($result, "A registered form's normal ACL resolution must be unaffected by the no-registry-row fix");
    }
}
