<?php

/**
 * view_form.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2024 Sophisticated Acquisitions <sophisticated.acquisitions@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");

use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Forms\FormLocator;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\EncounterService;

$clean_id = sanitizeNumber($_GET["id"]);

$pageName = 'view.php';
$isLBF = false;
/**
 * @global $incdir
 */
if (!str_starts_with((string) $_GET["formname"], 'LBF')) {
    if ((!empty($_GET['pid'])) && ($_GET['pid'] > 0)) {
        $pid = $_GET['pid'];
        $encounter = $_GET['encounter'];
    }

    // ensure the path variable has no illegal characters
    check_file_dir_name($_GET["formname"]);

    // ensure authorized to see the form
    if (!AclMain::aclCheckForm($_GET["formname"])) {
        $formLabel = xl_form_title(getRegistryEntryByDirectory($_GET["formname"], 'name')['name'] ?? '');
        $formLabel = $formLabel !== '' ? (string) $formLabel : (string) $_GET["formname"];
        AccessDeniedHelper::denyWithTemplate("ACL check failed for form: " . $formLabel, $formLabel);
    }

    // Form-type ACL above only confirms the role may see this *kind* of form. It does not check
    // the specific encounter's sensitivity level, so a high-sensitivity note was previously
    // reachable by any role authorized for the form type in general. Apply the same
    // 'sensitivities' ACL object check already used to gate the Visit History listing
    // (interface/patient_file/history/encounters.php) so this entry point enforces it too.
    if (!empty($pid) && !empty($encounter)) {
        $sensitivity = (new EncounterService())->getSensitivity((int) $pid, (int) $encounter);
        if (!empty($sensitivity) && !AclMain::aclCheckCore('sensitivities', (string) $sensitivity)) {
            AccessDeniedHelper::denyWithTemplate("Sensitivity ACL check failed for encounter", xl("Not Authorized"));
        }
    }
}

$formLocator = new FormLocator();
$file = $formLocator->findFile($_GET['formname'], $pageName, 'load_form.php');
require_once($file);

$id = $clean_id;
if (OEGlobalsBag::getInstance()->getBoolean('text_templates_enabled')) { ?>
    <script src="<?php echo OEGlobalsBag::getInstance()->getWebRoot() ?>/library/js/CustomTemplateLoader.js"></script>
<?php } ?>
