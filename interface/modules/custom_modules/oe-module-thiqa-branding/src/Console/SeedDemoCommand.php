<?php

/**
 * Seeds the Marketing MVP demo dataset (RDY-0020…RDY-0027).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ThiqaBranding\Console;

use OpenEMR\Billing\BillingUtilities;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Session\SessionUtil;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\AppointmentService;
use OpenEMR\Services\EncounterService;
use OpenEMR\Services\FormService;
use OpenEMR\Services\InsuranceCompanyService;
use OpenEMR\Services\PatientService;
use OpenEMR\Services\PrescriptionService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Marketing MVP Seed v1 — the locked demo dataset.
 *
 * **Every value is synthetic and governed by `docs/evidence/EV-028-synthetic-data-control.md`.**
 * The safe-value conventions are implemented *here*, in the generator, rather than applied by
 * hand afterwards, because conventions applied by hand are applied inconsistently and the one
 * row that gets missed is the one that ends up in the screenshot.
 *
 * **Determinism.** A fixed random seed and fixed name tables mean two runs against the same
 * pre-seed baseline produce the same dataset. That is what makes RDY-0044-B's "a second reset
 * produces identical counts" test meaningful rather than coincidental.
 *
 * **Fail-closed.** Preconditions refuse to run rather than producing a half-seeded database:
 * wrong site, missing demo users, an installer-default facility, an unverified rollback
 * baseline, or data already present all abort before the first write.
 *
 * **Author attribution.** The session author is set through the session *wrapper* the services
 * actually read. Writing the raw `$_SESSION` superglobal does not reach it, which is how every
 * pilot row landed with `created_by = 0` (PB-034).
 *
 * **Exit codes**: `0` seeded (or dry-run completed); `1` a precondition failed or a write failed.
 */
#[AsCommand(
    name: 'thiqa-branding:seed-demo',
    description: 'Seed the locked Marketing MVP demo dataset (RDY-0020…0027).',
)]
final class SeedDemoCommand extends Command
{
    /** Dataset profile identity. Recorded in the manifest and in every seeded marker. */
    private const PROFILE_VERSION = 'marketing-mvp-seed-v1';

    /** Fixed seed: the dataset must be reproducible, not merely plausible. */
    private const RANDOM_SEED = 20260813;

    /** Visible provenance marker prefix (EV-028 §2). */
    private const MARKER = 'SYN-';

    /** Locked targets (Owner decision, 2026-08-13). */
    private const TARGET_PATIENTS = 30;
    private const TARGET_ENCOUNTERS = 72;
    private const TARGET_APPOINTMENTS = 36;
    private const TARGET_DOCUMENTS = 10;
    private const TARGET_PRESCRIPTIONS = 12;
    private const TARGET_CHARGES = 36;
    private const TARGET_PAYERS = 2;
    private const TARGET_SOAP = 18;
    private const TARGET_VITALS = 12;
    private const TARGET_EYE_EXAMS = 8;
    private const TARGET_ALLERGY_PATIENTS = 5;
    private const TARGET_PROBLEM_PATIENTS = 6;
    private const TARGET_PAYMENTS = 12;
    private const TARGET_ADJUSTMENTS = 4;
    private const DUPLICATE_PAIRS = 2;

    /**
     * The billing-expected demo cohort: 37 encounters for which a charge is legitimately
     * expected. 36 carry charges; the one at PLANTED_MISSING_CHARGE_INDEX has none.
     * The other 35 encounters in the dataset are clinical / no-charge visits, not defects.
     */
    private const COHORT_SIZE = 37;

    /**
     * Deliberately mid-cohort rather than first or last, so finding it requires the
     * reconciliation report rather than looking at either end of the list.
     */
    private const PLANTED_MISSING_CHARGE_INDEX = 18;

    /** The pre-seed rollback baseline this seed depends on (RDY-0044-A / PB-031). */
    private const BASELINE_SHA256 = '18564f74b01dc505a3bc70e5674837ae89b9f61061b728772235ad5933661e71';
    private const BASELINE_PATH = 'C:/openemr-stack/backups/protected/rdy0044a/'
        . 'thiqa-rdy0044a-preseed-20260813-185745.sql';

    /** @var list<string> */
    private const GIVEN_F = ['Hessa', 'Noura', 'Amal', 'Rana', 'Dalal', 'Reem', 'Huda', 'Lama',
        'Wafa', 'Asma', 'Bushra', 'Ghada', 'Salma', 'Jawaher'];
    /** @var list<string> */
    private const GIVEN_M = ['Faisal', 'Turki', 'Bandar', 'Majed', 'Nawaf', 'Ziad', 'Rakan',
        'Talal', 'Mishal', 'Saud', 'Fahad', 'Anas', 'Waleed', 'Hatim'];
    /** @var list<string> */
    private const FAMILY = ['Alharthi', 'Alqarni', 'Albishi', 'Alshamrani', 'Aldawsari',
        'Alghamdi', 'Alzahrani', 'Alsubaie', 'Alanazi', 'Alrashidi', 'Almalki', 'Alotaibi',
        'Alharbi', 'Alqahtani', 'Alamri'];

    /**
     * The eleven satellite tables the Eye Exam form creates alongside `form_eye_base`.
     * Taken from `interface/forms/eye_mag/save.php:342-347`.
     *
     * @var list<string>
     */
    private const EYE_SATELLITE_TABLES = ['form_eye_hpi', 'form_eye_ros', 'form_eye_vitals',
        'form_eye_acuity', 'form_eye_refraction', 'form_eye_biometrics', 'form_eye_external',
        'form_eye_antseg', 'form_eye_postseg', 'form_eye_neuro', 'form_eye_locking'];

    /**
     * Ophthalmology examination profiles, one per seeded exam, **each matched to that patient's
     * problem list** (see seedAllergiesAndProblems: problems are assigned to patients 1..6 in
     * this order, and exams are seeded onto the first eight encounters, i.e. patients 1..8).
     *
     * Eight identical exams would be filler: a glaucoma patient and an asthma patient cannot
     * plausibly have the same eyes, and a diabetic-retinopathy patient with a normal macula is
     * a contradiction a clinician spots instantly. Each profile therefore carries findings that
     * follow from the diagnosis, and acuity consistent with those findings.
     *
     * @var array<int, array<string, string>>
     */
    private const EYE_PROFILES = [
        // 1 — Type 2 diabetes, no retinopathy yet. Screening exam, near-normal.
        ['dx' => 'Type 2 diabetes mellitus — annual screening',
            'scod' => '20/25', 'scos' => '20/25', 'mrod' => '20/20', 'mros' => '20/20',
            'cupod' => '0.3', 'cupos' => '0.3',
            'macod' => 'Flat, no oedema, no exudate', 'macos' => 'Flat, no oedema, no exudate',
            'vesod' => 'Normal calibre', 'vesos' => 'Normal calibre',
            'lensod' => 'Clear', 'lensos' => 'Clear',
            'cc' => 'Diabetic eye screening, no visual complaint',
            'iopod' => '14', 'iopos' => '15'],
        // 2 — Hypertension. Arteriolar narrowing and AV nicking, vision preserved.
        ['dx' => 'Essential hypertension — hypertensive retinopathy grade 1',
            'scod' => '20/30', 'scos' => '20/25', 'mrod' => '20/20', 'mros' => '20/20',
            'cupod' => '0.35', 'cupos' => '0.3',
            'macod' => 'Flat, no oedema', 'macos' => 'Flat, no oedema',
            'vesod' => 'Arteriolar narrowing, AV nicking', 'vesos' => 'Arteriolar narrowing',
            'lensod' => 'Trace nuclear sclerosis', 'lensos' => 'Trace nuclear sclerosis',
            'cc' => 'Routine review, hypertension',
            'iopod' => '16', 'iopos' => '15'],
        // 3 — Primary open-angle glaucoma. Enlarged cup, preserved central acuity.
        ['dx' => 'Primary open-angle glaucoma — stable on treatment',
            'scod' => '20/30', 'scos' => '20/40', 'mrod' => '20/25', 'mros' => '20/25',
            'cupod' => '0.7', 'cupos' => '0.75',
            'macod' => 'Flat', 'macos' => 'Flat',
            'vesod' => 'Normal', 'vesos' => 'Normal',
            'lensod' => 'Clear', 'lensos' => 'Trace nuclear sclerosis',
            'cc' => 'Glaucoma follow-up, pressure check',
            'iopod' => '17', 'iopos' => '18',
            'ioptargetod' => '16', 'ioptargetos' => '16'],
        // 4 — Hyperlipidaemia. Corneal arcus, otherwise unremarkable.
        ['dx' => 'Hyperlipidaemia — corneal arcus, no ocular sequelae',
            'scod' => '20/25', 'scos' => '20/30', 'mrod' => '20/20', 'mros' => '20/20',
            'cupod' => '0.3', 'cupos' => '0.3',
            'macod' => 'Flat', 'macos' => 'Flat',
            'vesod' => 'Normal', 'vesos' => 'Normal',
            'lensod' => 'Clear', 'lensos' => 'Clear',
            'cc' => 'Routine examination',
            'iopod' => '13', 'iopos' => '14'],
        // 5 — Asthma is not an ocular condition. Age-related lens change only.
        ['dx' => 'Early nuclear sclerotic cataract — asthma is incidental',
            'scod' => '20/40', 'scos' => '20/40', 'mrod' => '20/25', 'mros' => '20/25',
            'cupod' => '0.3', 'cupos' => '0.35',
            'macod' => 'Flat', 'macos' => 'Flat',
            'vesod' => 'Normal', 'vesos' => 'Normal',
            'lensod' => 'Nuclear sclerosis 1+', 'lensos' => 'Nuclear sclerosis 1+',
            'cc' => 'Gradual blurring of distance vision',
            'iopod' => '15', 'iopos' => '14'],
        // 6 — Diabetic retinopathy WITH macular oedema: reduced acuity, raised CMT.
        ['dx' => 'Moderate non-proliferative diabetic retinopathy with macular oedema',
            'scod' => '20/80', 'scos' => '20/60', 'mrod' => '20/60', 'mros' => '20/50',
            'cupod' => '0.35', 'cupos' => '0.3',
            'macod' => 'Centre-involving oedema, hard exudates', 'macos' => 'Microaneurysms, no oedema',
            'vesod' => 'Dot-blot haemorrhages, venous beading', 'vesos' => 'Scattered microaneurysms',
            'lensod' => 'Nuclear sclerosis 1+', 'lensos' => 'Nuclear sclerosis 1+',
            'cc' => 'Blurred central vision, right worse than left',
            'cmtod' => '412', 'cmtos' => '268',
            'iopod' => '16', 'iopos' => '16'],
        // 7 — Visually significant cataract: poor unaided, limited refractive improvement.
        ['dx' => 'Visually significant nuclear sclerotic cataract, bilateral',
            'scod' => '20/100', 'scos' => '20/80', 'mrod' => '20/60', 'mros' => '20/50',
            'cupod' => '0.3', 'cupos' => '0.3',
            'macod' => 'No view of detail, no gross abnormality', 'macos' => 'Flat',
            'vesod' => 'Normal', 'vesos' => 'Normal',
            'lensod' => 'Nuclear sclerosis 3+', 'lensos' => 'Nuclear sclerosis 2+',
            'cc' => 'Glare at night, difficulty reading',
            'iopod' => '14', 'iopos' => '15'],
        // 8 — Dry eye: good acuity, abnormal tear film. Schirmer/TBUT carry the diagnosis.
        ['dx' => 'Dry eye disease — reduced tear film stability',
            'scod' => '20/25', 'scos' => '20/25', 'mrod' => '20/20', 'mros' => '20/20',
            'cupod' => '0.3', 'cupos' => '0.3',
            'macod' => 'Flat', 'macos' => 'Flat',
            'vesod' => 'Normal', 'vesos' => 'Normal',
            'lensod' => 'Clear', 'lensos' => 'Clear',
            'cc' => 'Gritty, burning sensation worse in air conditioning',
            'schirmerod' => '4', 'schirmeros' => '5', 'tbutod' => '5', 'tbutos' => '6',
            'iopod' => '13', 'iopos' => '13'],
    ];

    /** @var array<int, array{code: string, text: string, fee: float}> */
    private const SERVICES = [
        ['code' => '99213', 'text' => 'Office visit, established patient', 'fee' => 250.00],
        ['code' => '99214', 'text' => 'Office visit, extended',            'fee' => 400.00],
        ['code' => '92014', 'text' => 'Eye exam, established patient',     'fee' => 350.00],
        ['code' => '92083', 'text' => 'Visual field examination',          'fee' => 300.00],
    ];

    private int $authUserId = 0;
    private int $facilityId = 0;
    private string $facilityName = '';
    /** @var list<int> */
    private array $providerIds = [];
    /** @var array<string, int> */
    private array $counts = [];
    private bool $dryRun = false;
    private string $plantedMissingCharge = '(not seeded)';
    private string $cohortFrom = '';
    private string $cohortTo = '';

    protected function configure(): void
    {
        $this->getDefinition()->addOption(BaselineOption::define(self::BASELINE_PATH));

        $this
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Validate preconditions and report the plan without writing')
            ->addOption('verify-context', null, InputOption::VALUE_NONE, 'Seed exactly one patient to prove author attribution, then stop')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Proceed even though seeded data is already present (NOT for normal use)')
            ->addOption(
                'apply-postseed-fixes',
                null,
                InputOption::VALUE_NONE,
                'Apply the three Owner-authorised post-seed changes (RDY-0023/0044/PB-057) to the '
                . 'already-accepted dataset, then stop. Does not re-seed'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $this->dryRun = (bool) $input->getOption('dry-run');
        $verifyContext = (bool) $input->getOption('verify-context');

        $io->title(sprintf(
            'Thiqa demo seed — profile %s%s',
            self::PROFILE_VERSION,
            $this->dryRun ? ' (DRY RUN — nothing will be written)' : ''
        ));

        if ((bool) $input->getOption('apply-postseed-fixes')) {
            // Deliberately does NOT go through checkPreconditions() — that method's baseline-hash
            // check and "already seeded" refusal are both about starting a FRESH seed from the
            // RDY-0044-A pre-seed snapshot, which this path does not do. It patches the
            // already-accepted, already-seeded dataset in place, so it needs only the facility
            // lookup checkPreconditions() also does, not the rest of that method's checks.
            $facility = QueryUtils::fetchRecords('SELECT id, name FROM facility ORDER BY id LIMIT 1', []);
            if ($facility === []) {
                $io->error('No facility exists.');

                return self::FAILURE;
            }
            $this->facilityId = self::asInt($facility[0]['id'] ?? null);
            $this->facilityName = self::asString($facility[0]['name'] ?? null);

            $this->establishAuthorContext();

            return $this->applyPostSeedFixes($io);
        }

        if (!$this->checkPreconditions($io, (bool) $input->getOption('force'), BaselineOption::resolve($input))) {
            return self::FAILURE;
        }

        $this->establishAuthorContext();
        $io->writeln(sprintf(
            '  Author context: user id <info>%d</info> (%s)',
            $this->authUserId,
            self::asString(
                QueryUtils::fetchSingleValue('SELECT username FROM users WHERE id = ?', 'username', [$this->authUserId])
            )
        ));

        mt_srand(self::RANDOM_SEED);

        if ($verifyContext) {
            return $this->runContextVerification($io);
        }

        $this->completeFacilityAndProviderIdentity($io);

        // Dependency order. Each stage records its own count for the manifest.
        $patients = $this->seedPatients($io);
        $this->seedAllergiesAndProblems($io, $patients);
        $payers = $this->seedPayers($io);
        $this->seedFeeSchedule($io);
        $encounters = $this->seedEncounters($io, $patients);
        $this->seedClinicalContent($io, $encounters);
        $this->seedAppointments($io, $patients);
        $this->seedDocuments($io, $patients, $encounters);
        $this->seedPrescriptions($io, $patients);
        $this->seedBilling($io, $encounters, $payers);

        $this->printManifest($io);

        if ($this->dryRun) {
            $io->warning('DRY RUN — no data was written.');
        } else {
            $io->success('Marketing MVP Seed v1 complete. Run the EV-028 §5 scans before accepting this dataset.');
        }

        return self::SUCCESS;
    }

    /**
     * Fail-closed preconditions. Each refuses the run rather than producing a partial dataset.
     */
    private function checkPreconditions(SymfonyStyle $io, bool $force, BaselineOption $baseline): bool
    {
        $problems = [];

        $site = basename(OEGlobalsBag::getInstance()->getString('OE_SITE_DIR'));
        if ($site !== 'default') {
            $problems[] = "Active site is '{$site}', expected 'default'.";
        }

        $facility = QueryUtils::fetchRecords('SELECT id, name FROM facility ORDER BY id LIMIT 1', []);
        if ($facility === []) {
            $problems[] = 'No facility exists.';
        } else {
            $this->facilityId = self::asInt($facility[0]['id'] ?? null);
            $this->facilityName = self::asString($facility[0]['name'] ?? null);
            if (str_contains($this->facilityName, 'Your Clinic Name Here')) {
                $problems[] = 'Facility is still the installer default (RDY-0032 not closed).';
            }
        }

        // Providers must exist, or every encounter would be attributed to nobody.
        $this->providerIds = array_map(
            static fn(array $r): int => self::asInt($r['id'] ?? null),
            QueryUtils::fetchRecords(
                "SELECT id FROM users WHERE username IN ('y.alharbi','s.almutairi') ORDER BY id",
                []
            )
        );
        if (count($this->providerIds) < 2) {
            $problems[] = 'The two demo physicians are missing (RDY-0010).';
        }

        // The rollback baseline must exist AND still hash correctly. Seeding without a proven
        // way back is the hard stop T0-3 exists to enforce. Its *location* is configurable
        // (--baseline-path, defaulting to BASELINE_PATH) because the recorded default names a
        // developer workstation and would refuse on every other host, the Ubuntu demo target
        // included; the integrity check itself is unchanged and cannot be skipped (PR-17).
        $baselineProblem = $baseline->verify(self::BASELINE_SHA256);
        if ($baselineProblem !== null) {
            $problems[] = $baselineProblem;
        }

        // The synthetic-data control must be present at the moment of seeding (RDY-0028).
        $control = dirname(__DIR__, 6) . '/docs/evidence/EV-028-synthetic-data-control.md';
        if (!is_file($control)) {
            $problems[] = 'EV-028 synthetic-data control document is missing.';
        }

        // Refuse accidental duplicate re-seeding.
        $existing = self::asInt(QueryUtils::fetchSingleValue(
            'SELECT COUNT(*) c FROM patient_data WHERE pubpid LIKE ?',
            'c',
            [self::MARKER . '%']
        ));
        if ($existing > 0 && !$force) {
            $problems[] = sprintf(
                '%d seeded patient(s) already present. Roll back to the RDY-0044-A baseline first, '
                . 'or pass --force if you genuinely intend to add to an existing dataset.',
                $existing
            );
        }

        if ($problems !== []) {
            $io->error('Preconditions FAILED — nothing was written:');
            foreach ($problems as $p) {
                $io->writeln('  ✗ ' . $p);
            }

            return false;
        }

        $io->writeln(sprintf(
            '  Preconditions <info>PASS</info> — site %s, facility "%s" (id %d), %d providers, baseline hash verified.',
            $site,
            $this->facilityName,
            $this->facilityId,
            count($this->providerIds)
        ));

        return true;
    }

    /**
     * Set the author through the session wrapper the services actually read.
     *
     * PB-034: writing `$_SESSION['authUserID']` directly does NOT reach the session wrapper
     * the services read, so every row landed with `created_by = 0`. `SessionUtil::setSession()`
     * writes through that wrapper and reopens a read-and-close session lock first.
     */
    private function establishAuthorContext(): void
    {
        $this->authUserId = self::asInt(QueryUtils::fetchSingleValue(
            "SELECT id FROM users WHERE username = 'admin'",
            'id',
            []
        ));

        SessionUtil::setSession([
            'authUserID' => $this->authUserId,
            'authUser' => 'admin',
            'authGroup' => 'Default',
            'authProvider' => 'Default',
        ]);
    }

    /**
     * §3 acceptance step 3: one patient, then stop, so `created_by` can be proven before volume.
     */
    private function runContextVerification(SymfonyStyle $io): int
    {
        $io->section('Author-context verification — one patient only');

        $pid = $this->insertPatient(0, 'Verify', 'Context', 'Female', '1990-01-01');
        if ($pid === null) {
            $io->error('Verification patient could not be created.');

            return self::FAILURE;
        }

        $createdBy = self::asInt(QueryUtils::fetchSingleValue(
            'SELECT created_by FROM patient_data WHERE pid = ?',
            'created_by',
            [$pid]
        ));
        $zeroRows = self::asInt(QueryUtils::fetchSingleValue(
            'SELECT COUNT(*) c FROM patient_data WHERE created_by = 0 OR created_by IS NULL',
            'c',
            []
        ));
        $resolves = self::asInt(QueryUtils::fetchSingleValue(
            'SELECT COUNT(*) c FROM users WHERE id = ?',
            'c',
            [$createdBy]
        ));

        $io->table(
            ['Check', 'Result'],
            [
                ['Patient created', 'pid ' . $pid],
                ['created_by', (string) $createdBy],
                ['created_by resolves to a real user', $resolves === 1 ? 'YES' : 'NO'],
                ['Rows with created_by = 0', (string) $zeroRows],
            ]
        );

        if ($createdBy <= 0 || $resolves !== 1 || $zeroRows !== 0) {
            $io->error('AUTHOR CONTEXT STILL BROKEN — do not proceed to the full seed.');

            return self::FAILURE;
        }

        $io->success('Author context correct. Roll back to the baseline, then run the full seed.');

        return self::SUCCESS;
    }

    // ---------------------------------------------------------------- patients

    /**
     * Completes the two identity gaps that only surface on **printed** output (PB-056).
     *
     * 1. `users.facility` — OpenEMR carries both a `facility_id` foreign key and a denormalised
     *    `facility` **name string**. The demo users had the FK set and the string NULL, and the
     *    prescription letterhead is built by `C_Prescription::multiprintplain_header()` with
     *    `JOIN facility AS f ON f.name = users.facility` — a join on the *string*. With it NULL the
     *    join returned no rows, so every printed prescription carried **no clinic name, address or
     *    phone**. Nothing is visibly wrong on screen; it only appears on paper.
     *
     * 2. The facility record itself held **only a name** — street, city, state, postal code and
     *    country were empty and the phone was the installer placeholder `000-000-0000`. Fixing the
     *    join alone would therefore have produced a letterhead with a clinic name and a blank
     *    address, which is not obviously better.
     *
     * Both are idempotent and only fill what is empty, so re-running never overwrites a value an
     * operator has set deliberately.
     */
    private function completeFacilityAndProviderIdentity(SymfonyStyle $io): void
    {
        $io->section('Facility and provider identity (printed-output fields)');

        if ($this->dryRun) {
            $io->writeln('  would complete the facility address/phone and set users.facility');

            return;
        }

        QueryUtils::sqlStatementThrowException(
            "UPDATE facility SET
                street       = IF(street IS NULL OR street = '', ?, street),
                city         = IF(city IS NULL OR city = '', ?, city),
                state        = IF(state IS NULL OR state = '', ?, state),
                postal_code  = IF(postal_code IS NULL OR postal_code = '', ?, postal_code),
                country_code = IF(country_code IS NULL OR country_code = '', ?, country_code),
                phone        = IF(phone IS NULL OR phone = '' OR phone = '000-000-0000', ?, phone)
             WHERE id = ?",
            [
                '3100 Fictional Boulevard',
                'Riyadh',
                'Riyadh Region',
                '00000',
                'SA',
                // EV-028 3.2 — structurally undialable, same rule as the patient numbers.
                '+966 11 000 000',
                $this->facilityId,
            ]
        );

        // The name string the print header joins on.
        $updated = QueryUtils::sqlStatementThrowException(
            "UPDATE users SET facility = ? WHERE facility_id = ? AND (facility IS NULL OR facility = '')",
            [$this->facilityName, $this->facilityId]
        );

        $withString = self::asInt(QueryUtils::fetchSingleValue(
            'SELECT COUNT(*) c FROM users WHERE facility = ?',
            'c',
            [$this->facilityName]
        ));

        $io->writeln(sprintf(
            '  facility address and phone completed; %d users now carry the facility name string',
            $withString
        ));
        unset($updated);
    }

    /** @return list<int> */
    private function seedPatients(SymfonyStyle $io): array
    {
        $io->section('Patients');
        $unique = self::TARGET_PATIENTS - self::DUPLICATE_PAIRS; // 28 distinct people
        $pids = [];

        for ($i = 0; $i < $unique; $i++) {
            $female = ($i % 2) === 0;
            $given = $female
                ? self::GIVEN_F[$i % count(self::GIVEN_F)]
                : self::GIVEN_M[$i % count(self::GIVEN_M)];
            $family = self::FAMILY[$i % count(self::FAMILY)];
            $dob = $this->birthDate($i);

            $pid = $this->insertPatient($i, $given, $family, $female ? 'Female' : 'Male', $dob);
            if ($pid !== null) {
                $pids[] = $pid;
            }
        }

        // Two planted duplicate pairs: same person re-registered, which is exactly how
        // duplicates arise in reality. Same name and DOB, different pubpid and identifier.
        for ($d = 0; $d < self::DUPLICATE_PAIRS; $d++) {
            $sourceIndex = $d * 7; // deterministic choice of who gets duplicated
            $female = ($sourceIndex % 2) === 0;
            $given = $female
                ? self::GIVEN_F[$sourceIndex % count(self::GIVEN_F)]
                : self::GIVEN_M[$sourceIndex % count(self::GIVEN_F)];
            $family = self::FAMILY[$sourceIndex % count(self::FAMILY)];
            $dob = $this->birthDate($sourceIndex);

            $pid = $this->insertPatient($unique + $d, $given, $family, $female ? 'Female' : 'Male', $dob);
            if ($pid !== null) {
                $pids[] = $pid;
            }
        }

        $this->counts['patients'] = count($pids);
        $io->writeln(sprintf(
            '  %d patients (%d distinct + %d planted duplicates)',
            count($pids),
            $unique,
            self::DUPLICATE_PAIRS
        ));

        return $pids;
    }

    /**
     * Deterministic but non-monotonic date of birth.
     *
     * The previous formula (`1955 + index % 40`) walked the years in order, so the first eight
     * patients — the ones that receive the ophthalmology examinations — came out as a strict
     * 71, 70, 69, 68 … run. That reads as generated at a glance. Multiplying by a coprime stride
     * scatters the years across 1948-1992 while staying fully reproducible, and still leaves the
     * eye-exam patients in a plausible ophthalmology age range.
     */
    private function birthDate(int $index): string
    {
        $year = 1948 + (($index * 17) % 45);
        $month = 1 + (($index * 7) % 12);
        $day = 1 + (($index * 11) % 28);

        return sprintf('%04d-%02d-%02d', $year, $month, $day);
    }

    private function insertPatient(int $index, string $given, string $family, string $sex, string $dob): ?int
    {
        if ($this->dryRun) {
            return $index + 1;
        }

        $service = new PatientService();
        $result = $service->insert([
            'fname'        => $given,
            'lname'        => $family,
            'sex'          => $sex,
            'DOB'          => $dob,
            // EV-028 §3.1 — leading 9 is not a real Saudi ID class.
            'ss'           => sprintf('999%07d', $index + 1),
            // EV-028 §3.2 — 10 digits, structurally undialable on the Saudi mobile plan.
            'phone_cell'   => sprintf('+966 5 000 %03d', $index + 1),
            'street'       => sprintf('%d Fictional Street', 1000 + $index),
            'city'         => ['Riyadh', 'Jeddah', 'Dammam', 'Khobar'][$index % 4],
            'state'        => 'Riyadh Region',
            'postal_code'  => '00000',
            'country_code' => 'SA',
            'occupation'   => ['Teacher', 'Engineer', 'Accountant', 'Retired'][$index % 4],
            // EV-028 §2 / §5.4 — visible provenance marker.
            'pubpid'       => sprintf('%s%04d', self::MARKER, $index + 1),
            'status'       => 'single',
        ]);

        if (!$result->isValid() || $result->hasInternalErrors()) {
            return null;
        }

        $data = $result->getData();
        if (!is_array($data) || !isset($data[0]) || !is_array($data[0])) {
            return null;
        }

        return self::asInt($data[0]['pid'] ?? null);
    }

    // ------------------------------------------------- allergies and problems

    /** @param list<int> $pids */
    private function seedAllergiesAndProblems(SymfonyStyle $io, array $pids): void
    {
        $io->section('Allergies and chronic problems');

        $allergies = ['Penicillin', 'Sulfa drugs', 'Latex', 'Peanuts', 'Iodine contrast'];
        $problems = ['Type 2 diabetes mellitus', 'Essential hypertension', 'Primary open-angle glaucoma',
            'Hyperlipidaemia', 'Asthma', 'Diabetic retinopathy'];

        for ($i = 0; $i < self::TARGET_ALLERGY_PATIENTS; $i++) {
            $this->insertListEntry($pids[$i] ?? 0, 'allergy', $allergies[$i]);
        }

        // THE CONSTRUCTED ALLERGY-ALERT CASE (D-7 step 11, RDY-0024).
        //
        // `allergy_conflict()` (library/clinical_rules.php:354) matches with a literal SQL IN:
        // `prescriptions.drug` must be **byte-identical** to a `lists.title` allergy. None of the
        // five clinical allergies above matches any seeded drug, so the alert could never fire and
        // D-7 step 11's own failure condition was guaranteed.
        //
        // This adds a SECOND allergy to a patient who already holds the matching prescription, so
        // `COUNT(DISTINCT pid)` stays at TARGET_ALLERGY_PATIENTS and no locked target moves. The
        // title is the full product string because that is what an exact match requires — and it is
        // clinically ordinary to record an allergy against the product that caused the reaction.
        $alertPid = $pids[1] ?? 0;   // the patient carrying 'Timolol 0.5% eye drops'
        $this->insertListEntry($alertPid, 'allergy', 'Timolol 0.5% eye drops');
        for ($i = 0; $i < self::TARGET_PROBLEM_PATIENTS; $i++) {
            $this->insertListEntry($pids[$i] ?? 0, 'medical_problem', $problems[$i]);
        }

        $this->counts['allergy_patients'] = self::TARGET_ALLERGY_PATIENTS;
        $this->counts['problem_patients'] = self::TARGET_PROBLEM_PATIENTS;
        $io->writeln(sprintf(
            '  %d patients with an allergy, %d with a chronic problem',
            self::TARGET_ALLERGY_PATIENTS,
            self::TARGET_PROBLEM_PATIENTS
        ));
    }

    /**
     * DOCUMENTED SERVICE-LAYER EXCEPTION (EV-028 / Owner §2).
     *
     * `AllergyIntoleranceService` and `ConditionService` are FHIR-facing read/write surfaces whose
     * insert paths expect FHIR-shaped payloads with coded concepts. Both ultimately write the same
     * `lists` row this method writes. Constructing a FHIR AllergyIntolerance resource to seed a
     * demo allergy would add a translation layer without changing the stored result, so the `lists`
     * row is written directly. The write is still parameterised and goes through QueryUtils.
     */
    private function insertListEntry(int $pid, string $type, string $title): void
    {
        if ($this->dryRun || $pid === 0) {
            return;
        }

        QueryUtils::sqlInsert(
            'INSERT INTO lists SET uuid = ?, date = NOW(), type = ?, title = ?, begdate = ?, '
            . 'activity = 1, pid = ?, user = ?, groupname = ?, comments = ?',
            [
                (new UuidRegistry(['table_name' => 'lists']))->createUuid(),
                $type,
                $title,
                date('Y-m-d', self::timestamp('-' . (90 + $pid) . ' days')),
                $pid,
                'admin',
                'Default',
                'SYNTHETIC DEMO',
            ]
        );
    }

    // ------------------------------------------------------------------ payers

    /** @return list<int> */
    private function seedPayers(SymfonyStyle $io): array
    {
        $io->section('Payers');
        $ids = [];
        $payers = [
            ['name' => 'Meridian Gulf Health (SYNTHETIC)', 'cms_id' => 'SYN001'],
            ['name' => 'Northwind Care Cooperative (SYNTHETIC)', 'cms_id' => 'SYN002'],
        ];

        foreach ($payers as $p) {
            if ($this->dryRun) {
                $ids[] = 0;
                continue;
            }
            $service = new InsuranceCompanyService();
            $ids[] = (int) $service->insert([
                'name'          => $p['name'],
                'attn'          => null,
                'cms_id'        => $p['cms_id'],
                'ins_type_code' => 2,
                'x12_receiver_id' => null,
                'x12_default_partner_id' => null,
                'alt_cms_id'    => '',
                'cqm_sop'       => null,
            ]);
        }

        $this->counts['payers'] = count($ids);
        // Reporting the locked figure next to the actual is how every other stage would surface a
        // drift between TARGET_* and what was seeded.
        $io->writeln(sprintf('  %d fictional payers (locked target %d)', count($ids), self::TARGET_PAYERS));

        return $ids;
    }

    /**
     * DOCUMENTED SERVICE-LAYER EXCEPTION: there is no fee-schedule service in `src/Services`.
     * `prices` is a four-column reference table (`pr_id`, `pr_selector`, `pr_level`, `pr_price`)
     * keyed to `codes`, written directly by the Fee Sheet UI. Parameterised insert.
     */
    private function seedFeeSchedule(SymfonyStyle $io): void
    {
        $io->section('Fee schedule and price level');
        $rows = 0;

        foreach (self::SERVICES as $svc) {
            if ($this->dryRun) {
                $rows++;
                continue;
            }
            $codeId = QueryUtils::fetchSingleValue(
                'SELECT id FROM codes WHERE code = ? LIMIT 1',
                'id',
                [$svc['code']]
            );
            if ($codeId === null) {
                $codeId = QueryUtils::sqlInsert(
                    'INSERT INTO codes SET code_text = ?, code = ?, code_type = 12, active = 1',
                    [$svc['text'], $svc['code']]
                );
            }
            QueryUtils::sqlInsert(
                'INSERT INTO prices SET pr_id = ?, pr_selector = ?, pr_level = ?, pr_price = ?',
                [$codeId, '', 'standard', $svc['fee']]
            );
            $rows++;
        }

        $this->counts['fee_schedule_rows'] = $rows;
        $io->writeln(sprintf('  %d priced services at price level "standard"', $rows));
    }

    // -------------------------------------------------------------- encounters

    /**
     * @param  list<int> $pids
     * @return list<array{eid: int, pid: int, provider: int, date: string}>
     */
    private function seedEncounters(SymfonyStyle $io, array $pids): array
    {
        $io->section('Encounters');
        $encounters = [];

        for ($i = 0; $i < self::TARGET_ENCOUNTERS; $i++) {
            $pid = $pids[$i % count($pids)];
            $provider = $this->providerIds[$i % count($this->providerIds)];
            // Spread across ~6 months so the ageing and trend reports have shape.
            $daysAgo = (int) round($i * (180 / self::TARGET_ENCOUNTERS));
            $date = date('Y-m-d H:i:s', self::timestamp("-{$daysAgo} days"));

            $eid = $this->insertEncounter($pid, $provider, $date);
            if ($eid !== null) {
                $encounters[] = ['eid' => $eid, 'pid' => $pid, 'provider' => $provider, 'date' => $date];
            }
        }

        $this->counts['encounters'] = count($encounters);
        $io->writeln(sprintf('  %d encounters across ~6 months', count($encounters)));

        return $encounters;
    }

    private function insertEncounter(int $pid, int $provider, string $date): ?int
    {
        if ($this->dryRun) {
            return 1;
        }

        $puuidRaw = QueryUtils::fetchSingleValue('SELECT uuid FROM patient_data WHERE pid = ?', 'uuid', [$pid]);
        if (!is_string($puuidRaw)) {
            return null;
        }

        $service = new EncounterService();
        $result = $service->insertEncounter(UuidRegistry::uuidToString($puuidRaw), [
            'date'             => $date,
            'onset_date'       => $date,
            'reason'           => 'Ophthalmology consultation (SYNTHETIC DEMO)',
            'facility'         => $this->facilityName,
            'facility_id'      => $this->facilityId,
            'billing_facility' => $this->facilityId,
            'provider_id'      => $provider,
            'pc_catid'         => 5,
            'class_code'       => 'AMB',
            'user'             => 'admin',
            'group'            => 'Default',
            'sensitivity'      => 'normal',
            'pos_code'         => 11,
        ]);

        if (!$result->isValid() || $result->hasInternalErrors()) {
            return null;
        }

        return self::asInt(QueryUtils::fetchSingleValue(
            'SELECT encounter FROM form_encounter WHERE pid = ? ORDER BY id DESC LIMIT 1',
            'encounter',
            [$pid]
        ));
    }

    /** @param list<array{eid: int, pid: int, provider: int, date: string}> $encounters */
    private function seedClinicalContent(SymfonyStyle $io, array $encounters): void
    {
        $io->section('Clinical content');

        for ($i = 0; $i < self::TARGET_SOAP && isset($encounters[$i]); $i++) {
            $this->insertSoap($encounters[$i]);
        }
        for ($i = 0; $i < self::TARGET_VITALS && isset($encounters[$i]); $i++) {
            $this->insertVitals($encounters[$i]);
        }
        for ($i = 0; $i < self::TARGET_EYE_EXAMS && isset($encounters[$i]); $i++) {
            $this->insertEyeExam($encounters[$i], self::EYE_PROFILES[$i]);
        }

        $this->counts['soap_notes'] = self::TARGET_SOAP;
        $this->counts['vitals'] = self::TARGET_VITALS;
        $this->counts['eye_exams'] = self::TARGET_EYE_EXAMS;
        $io->writeln(sprintf(
            '  %d SOAP notes, %d vitals, %d ophthalmology examinations',
            self::TARGET_SOAP,
            self::TARGET_VITALS,
            self::TARGET_EYE_EXAMS
        ));
    }

    /** @param array{eid: int, pid: int, provider: int, date: string} $enc */
    private function insertSoap(array $enc): void
    {
        if ($this->dryRun) {
            return;
        }

        $result = (new EncounterService())->insertSoapNote($enc['pid'], $enc['eid'], [
            'subjective' => 'Patient reports intermittent blurred vision when reading. No pain, '
                . 'no discharge, no flashes or floaters. (SYNTHETIC DEMO)',
            'objective'  => 'Anterior segment clear. Lens shows early nuclear sclerosis '
                . 'bilaterally. Fundus examination unremarkable. (SYNTHETIC DEMO)',
            'assessment' => 'Early nuclear sclerotic cataract, bilateral. Presbyopia. (SYNTHETIC DEMO)',
            'plan'       => 'Update refraction. Review in six months, sooner if symptoms '
                . 'progress. (SYNTHETIC DEMO)',
        ]);

        // Attribution is no longer corrected here: the service itself now records `user` and
        // `groupname` (patch record PR-14). Only the date is adjusted, because
        // `insertSoapNote()` hard-codes `date = NOW()` and a note must carry its encounter's date
        // for the six-month clinical history to read correctly.
        if (is_array($result) && isset($result[1])) {
            QueryUtils::sqlStatementThrowException(
                'UPDATE forms SET date = ? WHERE id = ?',
                [$enc['date'], $result[1]]
            );
        }
    }

    /**
     * DOCUMENTED SERVICE-LAYER EXCEPTION: `VitalsService::create()` is an empty stub in this
     * release (`// TODO: not sure we need this anymore.`), and `save()` expects a populated
     * `FormVitals` shaped by the form POST. The `form_vitals` row plus its `forms` registration
     * is written directly, mirroring exactly what the vitals form itself writes.
     *
     * @param array{eid: int, pid: int, provider: int, date: string} $enc
     */
    private function insertVitals(array $enc): void
    {
        if ($this->dryRun) {
            return;
        }

        $formId = QueryUtils::sqlInsert(
            'INSERT INTO form_vitals SET date = ?, pid = ?, user = ?, groupname = ?, activity = 1, '
            . 'bps = ?, bpd = ?, weight = ?, height = ?, temperature = ?, pulse = ?, respiration = ?, '
            . 'BMI = ?, oxygen_saturation = ?',
            [
                $enc['date'], $enc['pid'], 'admin', 'Default',
                (string) (110 + ($enc['pid'] % 25)),
                (string) (70 + ($enc['pid'] % 15)),
                (string) (60 + ($enc['pid'] % 30)),
                (string) (155 + ($enc['pid'] % 25)),
                '36.8',
                (string) (62 + ($enc['pid'] % 20)),
                (string) (14 + ($enc['pid'] % 4)),
                (string) (21 + ($enc['pid'] % 8)),
                (string) (96 + ($enc['pid'] % 4)),
            ]
        );

        $this->registerForm($enc, 'Vitals', 'vitals', (int) $formId);
    }

    /**
     * The Eye Exam (`eye_mag`) is a **multi-table** form: a `form_eye_base` parent plus eleven
     * satellite tables that share the parent's id. There is no `form_eye_mag` table.
     *
     * DOCUMENTED SERVICE-LAYER EXCEPTION: `eye_mag` is a legacy form module with no service class.
     * This mirrors the form's own `save.php` "new" path exactly (`interface/forms/eye_mag/save.php`
     * lines 338-352): insert the base row, register the form via `addForm()`, then create the
     * satellite rows. Creating them is not optional — the form's report and edit views join to
     * them, so a base row alone renders as a broken exam.
     *
     * @param array{eid: int, pid: int, provider: int, date: string} $enc
     * @param array<string, string> $profile one entry from EYE_PROFILES
     */
    private function insertEyeExam(array $enc, array $profile): void
    {
        if ($this->dryRun) {
            return;
        }

        $formId = (int) QueryUtils::sqlInsert(
            'INSERT INTO form_eye_base SET date = ?, pid = ?, user = ?, groupname = ?, '
            . 'authorized = 1, activity = 1',
            [$enc['date'], $enc['pid'], 'admin', 'Default']
        );

        (new FormService())->addForm(
            $enc['eid'],
            'Eye Exam',
            $formId,
            'eye_mag',
            $enc['pid'],
            1,
            $enc['date'],
            'admin',
            'Default'
        );

        foreach (self::EYE_SATELLITE_TABLES as $table) {
            QueryUtils::sqlInsert("INSERT INTO `{$table}` SET `id` = ?, `pid` = ?", [$formId, $enc['pid']]);
        }

        // Visual acuity, uncorrected and with manifest refraction.
        QueryUtils::sqlStatementThrowException(
            'UPDATE form_eye_acuity SET SCODVA = ?, SCOSVA = ?, MRODVA = ?, MROSVA = ? WHERE id = ?',
            [$profile['scod'], $profile['scos'], $profile['mrod'], $profile['mros'], $formId]
        );

        // Posterior segment: cup/disc, macula, vessels — the findings that make the diagnosis
        // legible to a reviewer rather than implied by the problem list alone.
        QueryUtils::sqlStatementThrowException(
            'UPDATE form_eye_postseg SET ODCUP = ?, OSCUP = ?, ODMACULA = ?, OSMACULA = ?, '
            . 'ODVESSELS = ?, OSVESSELS = ?, ODDISC = ?, OSDISC = ?, ODCMT = ?, OSCMT = ?, '
            . 'RETINA_COMMENTS = ? WHERE id = ?',
            [
                $profile['cupod'], $profile['cupos'],
                $profile['macod'], $profile['macos'],
                $profile['vesod'], $profile['vesos'],
                'Pink, distinct margins', 'Pink, distinct margins',
                $profile['cmtod'] ?? '', $profile['cmtos'] ?? '',
                'SYNTHETIC DEMO — ' . $profile['dx'],
                $formId,
            ]
        );

        // Anterior segment: lens grade carries the cataract picture; Schirmer/TBUT carry dry eye.
        QueryUtils::sqlStatementThrowException(
            'UPDATE form_eye_antseg SET ODLENS = ?, OSLENS = ?, ODCORNEA = ?, OSCORNEA = ?, '
            . 'ODCONJ = ?, OSCONJ = ?, ODAC = ?, OSAC = ?, '
            . 'ODSCHIRMER1 = ?, OSSCHIRMER1 = ?, ODTBUT = ?, OSTBUT = ? WHERE id = ?',
            [
                $profile['lensod'], $profile['lensos'],
                // Corneal arcus belongs with the lipid profile, nowhere else.
                str_contains($profile['dx'], 'Hyperlipidaemia') ? 'Arcus senilis' : 'Clear',
                str_contains($profile['dx'], 'Hyperlipidaemia') ? 'Arcus senilis' : 'Clear',
                'White and quiet', 'White and quiet',
                'Deep and quiet', 'Deep and quiet',
                $profile['schirmerod'] ?? '', $profile['schirmeros'] ?? '',
                $profile['tbutod'] ?? '', $profile['tbutos'] ?? '',
                $formId,
            ]
        );

        // Intraocular pressure by applanation, plus a treatment target. IOP belongs in any eye
        // examination, and its absence was conspicuous on the glaucoma case whose own presenting
        // complaint is a pressure check.
        //
        // **Every field the eye_mag form would otherwise default is written here explicitly, in
        // the form's own encoding.** Opening `view.php` in a browser runs a JS lock-acquire path
        // that persists the form's in-memory defaults over any NULL column — so a record left
        // partly NULL is silently rewritten the first time anyone looks at it (PB-043). Seeding
        // the same values the form would write makes the record **idempotent under viewing**:
        // there is nothing left for it to fill in, so the dataset stays byte-stable across demos,
        // clinical review, and the RDY-0044-B reset proof.
        //
        // Semantics, verified in `view.php:1145-1167` and `:1084`: for the visual-field quadrant
        // flags **1 means a defect and 0 means no defect** — 0 and NULL take the same branch and
        // render identically, and with no defects the form ticks `FTCF` (full to confrontation).
        // Amsler behaves the same way (`if (!$AMSLEROD) $AMSLEROD = "0"`). So writing 0 asserts
        // *no recorded defect*, which is exactly what this dataset means. `21` is the form's own
        // default target pressure, retained for the non-glaucoma exams and overridden with the
        // treated target on the glaucoma case.
        QueryUtils::sqlStatementThrowException(
            'UPDATE form_eye_vitals SET ODIOPAP = ?, OSIOPAP = ?, IOPTIME = ?, '
            . 'ODIOPTARGET = ?, OSIOPTARGET = ?, alert = 1, oriented = 1, confused = 1, '
            . 'AMSLEROD = 0, AMSLEROS = 0, '
            . 'ODVF1 = 0, ODVF2 = 0, ODVF3 = 0, ODVF4 = 0, '
            . 'OSVF1 = 0, OSVF2 = 0, OSVF3 = 0, OSVF4 = 0 WHERE id = ?',
            [
                $profile['iopod'], $profile['iopos'],
                date('H:i', self::timestamp($enc['date'])),
                $profile['ioptargetod'] ?? '21', $profile['ioptargetos'] ?? '21',
                $formId,
            ]
        );

        // Presenting complaint, so the record reads as a consultation rather than a data dump.
        QueryUtils::sqlStatementThrowException(
            'UPDATE form_eye_hpi SET CC1 = ?, HPI1 = ? WHERE id = ?',
            [$profile['cc'], 'SYNTHETIC DEMO. ' . $profile['dx'] . '.', $formId]
        );
    }

    /**
     * Registers a form against its encounter through the application's own helper, which is what
     * every form module calls, rather than writing `forms` directly.
     *
     * @param array{eid: int, pid: int, provider: int, date: string} $enc
     */
    private function registerForm(array $enc, string $name, string $directory, int $formId): void
    {
        // The global addForm() helper is deprecated and is a one-line delegation to this exact
        // call, so going straight to the service is the same write.
        (new FormService())->addForm(
            $enc['eid'],
            $name,
            $formId,
            $directory,
            $enc['pid'],
            1,
            $enc['date'],
            'admin',
            'Default'
        );
    }

    // ------------------------------------------------------------ appointments

    /** @param list<int> $pids */
    private function seedAppointments(SymfonyStyle $io, array $pids): void
    {
        $io->section('Appointments');

        $service = new AppointmentService();
        $monday = date('Y-m-d', self::timestamp('monday this week'));
        $today = date('Y-m-d');
        $made = 0;
        $noShows = 0;
        $cancelled = 0;

        for ($i = 0; $i < self::TARGET_APPOINTMENTS; $i++) {
            $pid = $pids[$i % count($pids)];

            // 2 no-shows, 3 cancellations, the rest a normal mix. Today's slots are
            // deliberately populated so the flow board has a list to show.
            if ($i < 2) {
                $status = '?';                     // no show
                $noShows++;
                $date = date('Y-m-d', self::timestamp($monday . ' +1 day'));
            } elseif ($i < 5) {
                $status = 'x';                     // cancelled
                $cancelled++;
                $date = date('Y-m-d', self::timestamp($monday . ' +2 day'));
            } elseif ($i < 17) {
                $status = ['@', '>', '<', '-'][$i % 4];
                $date = $today;                    // today's flow board
            } else {
                $status = ['-', '@', '>'][$i % 3];
                $date = date('Y-m-d', self::timestamp($monday . ' +' . ($i % 5) . ' day'));
            }

            $hour = 8 + ($i % 9);
            $minute = ($i % 2) === 0 ? '00' : '30';

            if (!$this->dryRun) {
                $service->insert($pid, [
                    'pc_catid'           => 5,
                    'pc_title'           => 'Ophthalmology appointment (SYNTHETIC DEMO)',
                    'pc_duration'        => 1800,
                    'pc_hometext'        => 'SYNTHETIC DEMO appointment',
                    'pc_eventDate'       => $date,
                    'pc_apptstatus'      => $status,
                    'pc_startTime'       => sprintf('%s %02d:%s:00', $date, $hour, $minute),
                    'pc_facility'        => $this->facilityId,
                    'pc_billing_location' => $this->facilityId,
                    'pc_aid'             => $this->providerIds[$i % count($this->providerIds)],
                ]);
            }
            $made++;
        }

        // One recurring series, which the calendar treats as a distinct shape.
        $this->seedRecurringSeries($pids[0]);

        $this->counts['appointments'] = $made;
        $this->counts['appt_no_shows'] = $noShows;
        $this->counts['appt_cancelled'] = $cancelled;
        $io->writeln(sprintf(
            '  %d appointments this week (%d no-show, %d cancelled, today populated, 1 recurring series)',
            $made,
            $noShows,
            $cancelled
        ));
    }

    /**
     * DOCUMENTED SERVICE-LAYER EXCEPTION: `AppointmentService::insert()` does not expose the
     * recurrence columns (`pc_recurrtype`, `pc_recurrspec`, `pc_endDate`). A recurring series is
     * an explicit demo requirement, so the recurrence fields are set directly, in the same shape
     * the calendar UI writes them.
     */
    private function seedRecurringSeries(int $pid): void
    {
        if ($this->dryRun) {
            return;
        }

        $start = date('Y-m-d', self::timestamp('monday this week'));
        QueryUtils::sqlInsert(
            'INSERT INTO openemr_postcalendar_events SET uuid = ?, pc_pid = ?, pc_catid = 5, '
            . 'pc_title = ?, pc_time = NOW(), pc_duration = 1800, pc_hometext = ?, pc_eventDate = ?, '
            . 'pc_endDate = ?, pc_apptstatus = ?, pc_startTime = ?, pc_endTime = ?, pc_facility = ?, '
            . 'pc_billing_location = ?, pc_informant = ?, pc_eventstatus = 1, pc_sharing = 1, '
            . 'pc_aid = ?, pc_recurrtype = 1, pc_recurrspec = ?',
            [
                (new UuidRegistry())->createUuid(),
                $pid,
                'Weekly post-operative review (SYNTHETIC DEMO)',
                'Recurring series, SYNTHETIC DEMO',
                $start,
                date('Y-m-d', self::timestamp($start . ' +8 weeks')),
                '-',
                '09:00:00',
                '09:30:00',
                $this->facilityId,
                $this->facilityId,
                $this->authUserId,
                $this->providerIds[0],
                // event_repeat_freq_type MUST be one of the regular types the calendar emits:
                // 0 day, 1 week, 2 month, 3 year, 4 workday (add_edit_event.php:1476).
                // 5 and 6 are reserved for the dynamically-built recurrences and carry extra
                // spec fields; with type 5 and these values __increment() never advances the
                // date, so fetchEvents() loops forever and the Appointments Report dies with
                // "Allowed memory size exhausted". This is weekly (1), which is what a weekly
                // post-operative review actually is.
                //
                // `exdate` is present-but-empty because the expansion reads it unconditionally.
                serialize([
                    'event_repeat_freq' => '1',
                    'event_repeat_freq_type' => '1',
                    'event_repeat_on_num' => '1',
                    'event_repeat_on_day' => '0',
                    'event_repeat_on_freq' => '0',
                    'exdate' => '',
                ]),
            ]
        );
    }

    // ---------------------------------------------------------------- documents

    /**
     * @param list<int> $pids
     * @param list<array{eid: int, pid: int, provider: int, date: string}> $encounters
     */
    private function seedDocuments(SymfonyStyle $io, array $pids, array $encounters): void
    {
        $io->section('Documents');
        $made = 0;

        for ($i = 0; $i < self::TARGET_DOCUMENTS; $i++) {
            $pid = $pids[$i % count($pids)];
            $eid = $encounters[$i % max(1, count($encounters))]['eid'] ?? 0;

            if ($this->dryRun) {
                $made++;
                continue;
            }

            // EV-028 §2: the marking must be on the rendered face of the document, not in
            // metadata, because metadata does not survive a screenshot.
            $body = "SYNTHETIC DEMO / NOT A REAL PATIENT\n"
                . str_repeat('=', 60) . "\n\n"
                . "Clinical correspondence — specimen document\n"
                . "Profile: " . self::PROFILE_VERSION . "\n"
                . "Document " . ($i + 1) . " of " . self::TARGET_DOCUMENTS . "\n\n"
                . "This document contains no real patient information. Every name, identifier,\n"
                . "date and clinical detail in this system is fabricated for demonstration.\n\n"
                . str_repeat('=', 60) . "\n"
                . "SYNTHETIC DEMO / NOT A REAL PATIENT\n";

            $document = new \Document();
            $error = $document->createDocument(
                (string) $pid,
                $this->documentCategoryId(),
                sprintf('SYNTHETIC-DEMO-specimen-%02d.txt', $i + 1),
                'text/plain',
                $body,
                eid: $eid
            );

            if ($error === '') {
                $made++;
            }
        }

        $this->counts['documents'] = $made;
        $io->writeln(sprintf('  %d specimen documents, each marked SYNTHETIC DEMO / NOT A REAL PATIENT', $made));
    }

    private function documentCategoryId(): int
    {
        $id = QueryUtils::fetchSingleValue(
            "SELECT id FROM categories WHERE name = 'Medical Record' LIMIT 1",
            'id',
            []
        );

        return is_numeric($id) ? (int) $id : 1;
    }

    // ------------------------------------------------------------ prescriptions

    /** @param list<int> $pids */
    private function seedPrescriptions(SymfonyStyle $io, array $pids): void
    {
        $io->section('Prescriptions');

        $drugs = [
            ['drug' => 'Latanoprost 0.005% eye drops', 'dose' => '1', 'unit' => 'drop', 'interval' => 'at bedtime'],
            ['drug' => 'Timolol 0.5% eye drops', 'dose' => '1', 'unit' => 'drop', 'interval' => 'twice daily'],
            ['drug' => 'Artificial tears', 'dose' => '1', 'unit' => 'drop', 'interval' => 'four times daily'],
            ['drug' => 'Prednisolone acetate 1%', 'dose' => '1', 'unit' => 'drop', 'interval' => 'four times daily'],
        ];
        $made = 0;

        for ($i = 0; $i < self::TARGET_PRESCRIPTIONS; $i++) {
            if ($this->dryRun) {
                $made++;
                continue;
            }

            $d = $drugs[$i % count($drugs)];
            $result = (new PrescriptionService())->insert([
                'patient_id'   => $pids[$i % count($pids)],
                'provider_id'  => $this->providerIds[$i % count($this->providerIds)],
                'date_added'   => date('Y-m-d', self::timestamp('-' . ($i * 5) . ' days')),
                'drug'         => $d['drug'],
                'quantity'     => '1',
                'refills'      => (string) ($i % 3),
                'per_refill'   => '1',
                'note'         => 'SYNTHETIC DEMO prescription',
                'active'       => 1,
                'dosage'       => $d['dose'],
                'size'         => '2.5',
                'unit'         => 1,
                'route'        => 1,
                'interval'     => 1,
            ]);

            if ($result->isValid() && !$result->hasInternalErrors()) {
                $made++;
            }
        }

        $this->counts['prescriptions'] = $made;
        $io->writeln(sprintf('  %d prescriptions', $made));
    }

    // ---------------------------------------------------------------- billing

    /**
     * @param list<array{eid: int, pid: int, provider: int, date: string}> $encounters
     * @param list<int> $payers
     */
    private function seedBilling(SymfonyStyle $io, array $encounters, array $payers): void
    {
        $io->section('Billing');

        // ---- BILLING-EXPECTED DEMO COHORT (Owner decision, 2026-08-13) -------------------
        //
        // 37 encounters are "billing-expected": a charge is legitimately expected for each.
        // 36 carry charges; **exactly one is planted with no charge record at all** — that is
        // the missing-charge case the Appointments-and-Encounters reconciliation must find.
        //
        // The remaining 35 encounters are clinical / no-charge visits (reviews, post-ops) and
        // are NOT billing defects. That distinction is the whole point: a demo where every
        // uncharged encounter is an error teaches the viewer the wrong thing.
        //
        // The cohort is the **most recent 37 encounters**, i.e. a contiguous date window, because
        // date range is the filter `appt_encounter_report.php` and `encounters_report.php`
        // actually expose. A cohort that no report can isolate would not be demonstrable.
        $cohort = array_slice($encounters, 0, self::COHORT_SIZE);
        $plantedIndex = self::PLANTED_MISSING_CHARGE_INDEX;
        $oldest = end($cohort);
        if ($oldest === false) {
            throw new \RuntimeException('The billing cohort is empty; there is nothing to reconcile against.');
        }
        $this->cohortFrom = $oldest['date'];
        $this->cohortTo = $cohort[0]['date'];

        $charges = 0;

        foreach ($cohort as $i => $enc) {
            if ($i === $plantedIndex) {
                // THE PLANTED CASE. No addBilling() call at all — genuinely no charge record,
                // not a charge flagged unbilled. Recorded in the manifest by synthetic id.
                $this->plantedMissingCharge = sprintf(
                    'encounter %d / patient %s (%s)',
                    $enc['eid'],
                    self::asString(QueryUtils::fetchSingleValue(
                        'SELECT pubpid FROM patient_data WHERE pid = ?',
                        'pubpid',
                        [$enc['pid']]
                    )),
                    substr($enc['date'], 0, 10)
                );
                continue;
            }

            $svc = self::SERVICES[$i % count(self::SERVICES)];

            if (!$this->dryRun) {
                $billingId = BillingUtilities::addBilling(
                    $enc['eid'],
                    'CPT4',
                    $svc['code'],
                    $svc['text'],
                    $enc['pid'],
                    '1',
                    (string) $enc['provider'],
                    '',
                    '1',
                    number_format($svc['fee'], 2, '.', ''),
                    '',
                    '',
                    0,
                    '',
                    'standard'
                );

                // `addBilling()` hard-codes `date = NOW()`, so every charge would land in the
                // same ageing bucket and the A/R ageing report would show a single band. Backdate
                // to the encounter's own date, which is what makes the ageing report meaningful.
                //
                // All 36 are marked billed. `billed = 0` is deliberately NOT used to stand in for
                // the missing-charge case (Owner decision): a charge flagged unbilled and an
                // encounter with no charge at all are different findings, and conflating them
                // would misrepresent what the reconciliation report detects.
                QueryUtils::sqlStatementThrowException(
                    'UPDATE billing SET date = ?, billed = 1 WHERE id = ?',
                    [$enc['date'], $billingId]
                );
            }
            $charges++;
        }

        $this->counts['charges'] = $charges;
        $this->counts['billing_expected_cohort'] = self::COHORT_SIZE;
        $this->counts['planted_missing_charge'] = 1;
        $this->counts['clinical_no_charge_encounters'] = count($encounters) - self::COHORT_SIZE;

        $payments = $this->seedFinancialEvents($encounters, $payers);
        $this->counts['payments'] = $payments['payments'];
        $this->counts['adjustments'] = $payments['adjustments'];

        $io->writeln(sprintf(
            '  cohort %d = %d charged + 1 planted missing-charge; %d clinical/no-charge encounters',
            self::COHORT_SIZE,
            $charges,
            count($encounters) - self::COHORT_SIZE
        ));
        $io->writeln(sprintf('  %d payments, %d adjustments', $payments['payments'], $payments['adjustments']));
    }

    /**
     * DOCUMENTED SERVICE-LAYER EXCEPTION: there is no payment-posting service in `src/Services`.
     * Posting is done by `interface/billing/` screens writing `ar_session` + `ar_activity`
     * directly. This mirrors that shape, parameterised.
     *
     * @param  list<array{eid: int, pid: int, provider: int, date: string}> $encounters
     * @param  list<int> $payers
     * @return array{payments: int, adjustments: int}
     */
    private function seedFinancialEvents(array $encounters, array $payers): array
    {
        if ($this->dryRun) {
            return ['payments' => self::TARGET_PAYMENTS, 'adjustments' => self::TARGET_ADJUSTMENTS];
        }

        $payments = 0;
        $adjustments = 0;

        // Ageing bands: post across 10 / 45 / 75 days so more than one band is populated.
        $bands = [10, 45, 75];

        for ($i = 0; $i < self::TARGET_PAYMENTS; $i++) {
            $enc = $encounters[$i % count($encounters)];
            $postDate = date('Y-m-d', self::timestamp('-' . $bands[$i % count($bands)] . ' days'));

            $sessionId = QueryUtils::sqlInsert(
                'INSERT INTO ar_session SET payer_id = ?, user_id = ?, closed = 0, reference = ?, '
                . 'check_date = ?, deposit_date = ?, pay_total = ?, created_time = NOW(), '
                . 'modified_time = NOW(), payment_type = ?, description = ?, patient_id = ?, '
                . 'payment_method = ?, post_to_date = ?',
                [
                    $payers[$i % count($payers)],
                    $this->authUserId,
                    sprintf('SYN-PAY-%04d', $i + 1),
                    $postDate,
                    $postDate,
                    '150.00',
                    'insurance',
                    'SYNTHETIC DEMO payment',
                    $enc['pid'],
                    'check',
                    $postDate,
                ]
            );

            QueryUtils::sqlInsert(
                'INSERT INTO ar_activity SET pid = ?, encounter = ?, sequence_no = ?, code_type = ?, '
                . 'code = ?, payer_type = 1, post_time = ?, post_user = ?, session_id = ?, memo = ?, '
                . 'pay_amount = ?, adj_amount = ?, modified_time = NOW(), post_date = ?, deleted = NULL',
                [
                    $enc['pid'], $enc['eid'], $i + 1, 'CPT4',
                    self::SERVICES[$i % count(self::SERVICES)]['code'],
                    $postDate, $this->authUserId, $sessionId,
                    'SYNTHETIC DEMO payment', '150.00', '0.00', $postDate,
                ]
            );
            $payments++;
        }

        for ($i = 0; $i < self::TARGET_ADJUSTMENTS; $i++) {
            $enc = $encounters[($i + 20) % count($encounters)];
            $postDate = date('Y-m-d', self::timestamp('-' . $bands[$i % count($bands)] . ' days'));

            $sessionId = QueryUtils::sqlInsert(
                'INSERT INTO ar_session SET payer_id = ?, user_id = ?, closed = 0, reference = ?, '
                . 'check_date = ?, deposit_date = ?, pay_total = ?, created_time = NOW(), '
                . 'modified_time = NOW(), payment_type = ?, description = ?, patient_id = ?, '
                . 'adjustment_code = ?, post_to_date = ?',
                [
                    $payers[$i % count($payers)], $this->authUserId,
                    sprintf('SYN-ADJ-%04d', $i + 1), $postDate, $postDate, '0.00',
                    'insurance', 'SYNTHETIC DEMO adjustment', $enc['pid'],
                    'contractual', $postDate,
                ]
            );

            QueryUtils::sqlInsert(
                'INSERT INTO ar_activity SET pid = ?, encounter = ?, sequence_no = ?, code_type = ?, '
                . 'code = ?, payer_type = 1, post_time = ?, post_user = ?, session_id = ?, memo = ?, '
                . 'pay_amount = ?, adj_amount = ?, modified_time = NOW(), post_date = ?, '
                . 'reason_code = ?, deleted = NULL',
                [
                    $enc['pid'], $enc['eid'], 100 + $i, 'CPT4',
                    self::SERVICES[$i % count(self::SERVICES)]['code'],
                    $postDate, $this->authUserId, $sessionId,
                    'SYNTHETIC DEMO contractual adjustment', '0.00', '45.00', $postDate,
                    'Contractual',
                ]
            );
            $adjustments++;
        }

        return ['payments' => $payments, 'adjustments' => $adjustments];
    }

    // ---------------------------------------------------------------- manifest

    // ----------------------------------------------------- post-seed fixes (RDY-0023/0044/PB-057)

    /**
     * Applies three targeted changes to the already-accepted RDY-0044-B dataset, authorised by the
     * Owner directly in conversation, 2026-08-19:
     *
     * 1. RDY-0023 — converts one existing patient (SYN-0013, chosen for carrying zero SOAP/vitals/
     *    eye-exam content, so nothing already clinically reviewed is touched) to a paediatric DOB.
     *    Converts rather than adds a 31st patient — this project already reverted exactly that
     *    addition once (`EV-044` §10, "pid 31 — removed, not folded in") because `patients=30` is a
     *    locked figure every count-based check (EV-028, cohort/duplicate-detection validation) is
     *    built on.
     * 2. RDY-0044 (PB-077 change 2, part 1) — flags one encounter (SYN-0014's most recent) with
     *    `sensitivity = 'high'` (the only non-'normal' value the `sensitivities` ACO section
     *    defines — `AclMain.php`'s own doc-block, confirmed live against `gacl_aco`).
     * 3. RDY-0044 (PB-077 change 2, part 2) — a "clinician-authored" note on SYN-0015's most recent
     *    encounter, written under that encounter's own already-assigned provider's identity via the
     *    same session-context mechanism `EncounterService::insertSoapNote()` already reads for every
     *    other seeded note (PR-14) — an extension of the existing attribution mechanism to the
     *    encounter's real clinician instead of the seeder's admin stand-in, not a new one.
     *
     * Also applies `completeFacilityAndProviderIdentity()` (PB-057), which has been implemented and
     * proven read-only since 2026-08-14 but never run against the live dataset.
     *
     * Idempotent: safe to re-run: each of the three changes checks its own already-applied state
     * first and skips if so.
     */
    private function applyPostSeedFixes(SymfonyStyle $io): int
    {
        $io->section(
            'Post-seed fixes (Owner-authorised 2026-08-19): paediatric conversion, '
            . 'sensitivity flag, clinician-authored note, facility identity'
        );

        $patientCount = self::asInt(
            QueryUtils::fetchSingleValue('SELECT COUNT(*) c FROM patient_data', 'c', [])
        );
        if ($patientCount !== self::TARGET_PATIENTS) {
            $io->error(sprintf(
                'Expected exactly %d patients (the accepted RDY-0044-B dataset), found %d. '
                . 'Refusing to run against an unexpected state.',
                self::TARGET_PATIENTS,
                $patientCount
            ));

            return self::FAILURE;
        }

        $paediatricPid = QueryUtils::fetchSingleValue(
            "SELECT pid FROM patient_data WHERE pubpid = 'SYN-0013'",
            'pid',
            []
        );
        $sensitiveRow = QueryUtils::fetchRecords(
            'SELECT fe.id, fe.sensitivity FROM form_encounter fe '
            . "JOIN patient_data p ON p.pid = fe.pid WHERE p.pubpid = 'SYN-0014' "
            . 'ORDER BY fe.date DESC LIMIT 1',
            []
        );
        $clinicianRow = QueryUtils::fetchRecords(
            'SELECT fe.encounter, fe.pid, fe.date, fe.provider_id, u.username '
            . 'FROM form_encounter fe JOIN patient_data p ON p.pid = fe.pid '
            . 'JOIN users u ON u.id = fe.provider_id '
            . "WHERE p.pubpid = 'SYN-0015' ORDER BY fe.date DESC LIMIT 1",
            []
        );

        if ($paediatricPid === null || $sensitiveRow === [] || $clinicianRow === []) {
            $io->error(
                'Could not resolve the three target rows (SYN-0013/0014/0015) against the '
                . 'expected dataset shape. Refusing to proceed.'
            );

            return self::FAILURE;
        }

        $paediatricPid = self::asInt($paediatricPid);

        // 1. Paediatric patient — RDY-0023.
        $currentDob = self::asString(QueryUtils::fetchSingleValue(
            'SELECT DOB FROM patient_data WHERE pid = ?',
            'DOB',
            [$paediatricPid]
        ));
        // C_FormVitals.class.php:116 gates on patient_age <= 20. 2010-03-15 is comfortably under
        // that threshold for the foreseeable life of this demo dataset, not just today.
        $paediatricDob = '2010-03-15';
        if ($currentDob === $paediatricDob) {
            $io->writeln('  Paediatric conversion already applied — skipping (idempotent).');
        } else {
            QueryUtils::sqlStatementThrowException(
                'UPDATE patient_data SET DOB = ? WHERE pid = ?',
                [$paediatricDob, $paediatricPid]
            );
            $io->writeln(sprintf(
                '  Paediatric conversion: pid %d (SYN-0013) DOB %s -> %s',
                $paediatricPid,
                $currentDob,
                $paediatricDob
            ));
        }

        // 2. Sensitivity-flagged encounter — RDY-0044 change 2, part 1.
        if ($sensitiveRow[0]['sensitivity'] === 'high') {
            $io->writeln('  Sensitivity flag already applied — skipping (idempotent).');
        } else {
            QueryUtils::sqlStatementThrowException(
                'UPDATE form_encounter SET sensitivity = ? WHERE id = ?',
                ['high', self::asInt($sensitiveRow[0]['id'] ?? null)]
            );
            $io->writeln(sprintf(
                '  Sensitivity flag: form_encounter.id %d (SYN-0014) sensitivity -> high',
                self::asInt($sensitiveRow[0]['id'] ?? null)
            ));
        }

        // 3. Clinician-authored note — RDY-0044 change 2, part 2.
        $enc = $clinicianRow[0];
        $encounterId = self::asInt($enc['encounter'] ?? null);
        $providerId = self::asInt($enc['provider_id'] ?? null);
        $username = self::asString($enc['username'] ?? null);
        $alreadyAuthored = self::asInt(QueryUtils::fetchSingleValue(
            "SELECT COUNT(*) c FROM forms WHERE encounter = ? AND formdir = 'soap' AND user != 'admin'",
            'c',
            [$encounterId]
        ));
        if ($alreadyAuthored > 0) {
            $io->writeln('  Clinician-authored note already present — skipping (idempotent).');
        } else {
            SessionUtil::setSession([
                'authUserID' => $providerId,
                'authUser' => $username,
            ]);

            $this->insertSoap([
                'eid'      => $encounterId,
                'pid'      => self::asInt($enc['pid'] ?? null),
                'provider' => $providerId,
                'date'     => self::asString($enc['date'] ?? null),
            ]);

            // Restore the seeder's own admin context immediately — a one-row exception, not a
            // change to how the rest of a run (or a re-run of this method) attributes anything.
            SessionUtil::setSession([
                'authUserID' => $this->authUserId,
                'authUser' => 'admin',
            ]);

            $io->writeln(sprintf(
                '  Clinician-authored note: encounter %d (SYN-0015), authored by %s',
                $encounterId,
                $username
            ));
        }

        // 4. Facility/letterhead identity — PB-057. Implemented and proven read-only since
        //    2026-08-14; this is its first live run. Idempotent by its own construction (only
        //    fills empty fields).
        $this->completeFacilityAndProviderIdentity($io);

        $io->success('Post-seed fixes applied. Take a fresh baseline before treating this as the accepted dataset.');

        return self::SUCCESS;
    }

    private function printManifest(SymfonyStyle $io): void
    {
        $io->section('Seed manifest');
        $io->writeln('  Profile:    ' . self::PROFILE_VERSION);
        $io->writeln('  Seed:       ' . self::RANDOM_SEED . ' (fixed — the dataset is reproducible)');
        $io->writeln('  Marker:     ' . self::MARKER . 'nnnn in patient_data.pubpid');
        $io->writeln('  Author:     user id ' . $this->authUserId);
        $io->writeln('  Baseline:   RDY-0044-A ' . substr(self::BASELINE_SHA256, 0, 16) . '… (hash verified this run)');
        $io->newLine();
        $io->writeln('  <comment>BILLING-EXPECTED DEMO COHORT</comment>');
        $io->writeln(sprintf('    Size:              %d encounters', self::COHORT_SIZE));
        $io->writeln(sprintf('    Date window:       %s … %s', substr($this->cohortFrom, 0, 10), substr($this->cohortTo, 0, 10)));
        $io->writeln(sprintf('    Charged:           %d', self::TARGET_CHARGES));
        $io->writeln('    PLANTED MISSING CHARGE: ' . $this->plantedMissingCharge);
        $io->writeln('    Outside the cohort: clinical / no-charge visits — NOT billing defects');
        $io->newLine();

        $rows = [];
        foreach ($this->counts as $k => $v) {
            $rows[] = [$k, (string) $v];
        }
        $io->table(['Category', 'Seeded'], $rows);
    }

    /**
     * strtotime() for expressions this command builds itself.
     *
     * Every expression passed here is assembled from literals and integers in this file, so the
     * false return is unreachable; without this guard date() would be handed `false`, which is a
     * TypeError under strict types rather than a usable date.
     */
    private static function timestamp(string $expression): int
    {
        $timestamp = strtotime($expression);
        if ($timestamp === false) {
            throw new \RuntimeException('Unparseable relative date expression: ' . $expression);
        }

        return $timestamp;
    }

    /**
     * Narrows an untyped database value to int.
     *
     * QueryUtils hands rows and single values back untyped, so they cannot be cast directly.
     * Every scalar is converted exactly as the previous `(int)` cast converted it; an array or
     * object - which `(int)` would have turned into a warning and a meaningless `1` - is refused
     * instead, because it means the query no longer returns the shape this command expects.
     */
    private static function asInt(mixed $value): int
    {
        if (is_int($value)) {
            return $value;
        }
        if ($value === null || is_bool($value) || is_float($value) || is_string($value)) {
            return (int) $value;
        }

        throw new \RuntimeException('Expected a scalar database value, got ' . get_debug_type($value) . '.');
    }

    /**
     * Narrows an untyped database value to string. See {@see asInt()} for why the scalar branches
     * are exhaustive and why the remaining branch is an error rather than a cast.
     */
    private static function asString(mixed $value): string
    {
        if (is_string($value)) {
            return $value;
        }
        if ($value === null || is_bool($value) || is_float($value) || is_int($value)) {
            return (string) $value;
        }

        throw new \RuntimeException('Expected a scalar database value, got ' . get_debug_type($value) . '.');
    }
}
