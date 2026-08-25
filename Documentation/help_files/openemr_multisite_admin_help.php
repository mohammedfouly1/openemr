<?php

/**
 * Multi Site Administration Help.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author Ranganath Pathak <pathak@scrs1.org>
 * @copyright Copyright (c) 2019 Ranganath Pathak <pathak@scrs1.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// Finding B2-follow-up. `admin.php:215` loads this page into its help modal, and `admin.php`
// answers an unauthenticated request — so every product name printed below was an anonymous
// brand leak, on the same surface and for the same reason as the two literals `admin.php`
// itself carried. `Documentation/` is scanned by no branding guard, which is why it survived.
//
// This page loads no bootstrap at all: no `vendor/autoload.php`, no `globals.php`, no database.
// `ProductIdentity` is the seam built for exactly that (ADR-BRAND-005) — it depends on nothing
// but PHP builtins and one generated artefact — so it is required directly, as `admin.php`
// does. Escaping is hand-rolled for the same reason: `text()` is a composer `autoload.files`
// entry this page never reaches.
// Separately, and found while converting: this page called `xla()` in two <script> blocks and
// loaded nothing that defines it, so **every** direct load ended in
// `Call to undefined function xla()` and a 500 — verified in the PHP error log against the
// unmodified file. Since the iframe above is the only way this page is ever opened, it has been
// broken for as long as those calls have been there. The four affected strings are now plain
// literals, matching the bare `echo ("...")` every other string on this page already uses; the
// page has no bootstrap to translate through, so making it consistent is the honest repair.
require_once(__DIR__ . '/../../src/Common/Branding/ProductIdentity.php');

$productNameEsc = htmlspecialchars(OpenEMR\Common\Branding\ProductIdentity::name(), ENT_QUOTES, 'UTF-8');

?>
<!DOCTYPE html>
<html>
    <head>
        <link rel=stylesheet href="../../public/themes/style_light.css">
        <link rel="stylesheet" href="../../public/assets/jquery-ui/jquery-ui.css">
        <script src="../../public/assets/jquery/dist/jquery.min.js"></script>
        <script src="../../public/assets/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
        <link rel="stylesheet" href="../../public/assets/@fortawesome/fontawesome-free/css/all.min.css">
        <link rel="shortcut icon" href="../../public/images/favicon.ico" />
        <script src="../../public/assets/jquery-ui/jquery-ui.js"></script>
    <title><?php echo ("Multi Site Administration Help");?></title>
    <style>
        @media only screen and (max-width: 768px) {
           [class*="col-"] {
           width: 100%;
           text-align:left!Important;
            }
        }
    </style>
    </head>
   <body>
        <div class="container oe-help-container">
            <div>
                <h2 class="text-center"><a name='entire_doc'><?php echo $productNameEsc; ?><?php echo (" Multi Site Administration");?></a></h2>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <p><?php echo ("This is the central location to manage multisite installations");?>.</p>

                    <p><?php echo ("It serves three functions");?>:</p>
                        <ul>
                            <li><?php echo ("Tabulates all sites that have been installed using the multisite module with ability to login to each site"); ?></li>
                            <li><?php echo ("Keeps track of the version and status of the site's database, access control list tables and patch status"); ?></li>
                            <li><?php echo ("Lets the user add a new site using the multisite module"); ?></li>
                        </ul>

                    <p><?php echo ("Initially it will have only the 'default' site installed");?>.</p>

                    <p><?php echo ("The displayed table will have six columns - 'Site ID', 'DB Name', 'Site Name', 'Version', 'Is Current', and 'Log In'");?>.</p>

                    <p><?php echo ("<strong>Site ID</strong>  - unique ID of the site, should be one word, preferably lower case");?>.</p>

                    <p><?php echo ("This will be used to identify which site to login to");?>.</p>

                    <p><?php echo ("The site-specific non-database patient data will be stored in a sub-directory bearing the site ID in the 'sites' directory");?>.</p>

                    <p><?php echo ("<strong>DB Name</strong> - the name of the database containing site-specific data");?>.</p>

                    <p><?php echo ("<strong>Site Name</strong> - by default it will be ");?><?php echo $productNameEsc; ?><?php echo (", once the site is setup this can be changed for that instance by going to Administration > Appearance > Application Title");?>.</p>

                    <p><?php echo ("<strong>Version</strong> - the version of the current installation");?>.</p>

                    <p><?php echo ("As the script files are common to all sites, it would be imperative that all sites have the same version number");?>.</p>

                    <p><?php echo ("<strong>Is Current</strong> - Whether on not the the site's installed database, access control list version and patch status is current i.e. the ");?><?php echo $productNameEsc; ?><?php echo (" scripts will work with the installed database, the latest access control lists are available and that the required patches have been applied and is up to date ");?>.</p>

                    <p><?php echo ("<strong>Log In</strong> - That will let you login to the particular site");?>.</p>

                    <p><?php echo ("Clicking the 'Add a New Site' button will take you to 'Optional Site ID Selection' page that will begin the process of adding a new site using the setup script");?>.</p>

                    <p><?php echo ("More information on how to use the multisite module is available by clicking the help icon on the 'Optional Site ID Selection' page");?>.</p>

                </div>
            </div>
        </div><!--end of container div-->
        <script>
           $('#show_hide').click(function() {
                var elementTitle = $('#show_hide').prop('title');
                var hideTitle = 'Click to Hide';
                var showTitle = 'Click to Show';
                $('.hideaway').toggle('1000');
                $(this).toggleClass('fa-eye-slash fa-eye');
                if (elementTitle == hideTitle) {
                    elementTitle = showTitle;
                } else if (elementTitle == showTitle) {
                    elementTitle = hideTitle;
                }
                $('#show_hide').prop('title', elementTitle);
            });
        </script>

        <script>
        // better script for tackling nested divs
           $('.show_hide').click(function() {
                var elementTitle = $(this).prop('title');
                var hideTitle = 'Click to Hide';
                var showTitle = 'Click to Show';
                //$('.hideaway').toggle('1000');
                $(this).parent().parent().closest('div').children('.hideaway').toggle('1000');
                if (elementTitle == hideTitle) {
                    elementTitle = showTitle;
                    $(this).toggleClass('fa-eye-slash fa-eye');
                } else if (elementTitle == showTitle) {
                    elementTitle = hideTitle;
                    $(this).toggleClass('fa-eye fa-eye-slash');
                }
                $(this).prop('title', elementTitle);
            });
        </script>
    </body>
</html>
