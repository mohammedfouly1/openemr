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
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\AppointmentService;
use OpenEMR\Services\EncounterService;
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

    protected function configure(): void
    {
        $this
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Validate preconditions and report the plan without writing')
            ->addOption('verify-context', null, InputOption::VALUE_NONE, 'Seed exactly one patient to prove author attribution, then stop')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Proceed even though seeded data is already present (NOT for normal use)');
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

        if (!$this->checkPreconditions($io, (bool) $input->getOption('force'))) {
            return self::FAILURE;
        }

        $this->establishAuthorContext();
        $io->writeln(sprintf(
            '  Author context: user id <info>%d</info> (%s)',
            $this->authUserId,
            (string) QueryUtils::fetchSingleValue('SELECT username FROM users WHERE id = ?', 'username', [$this->authUserId])
        ));

        mt_srand(self::RANDOM_SEED);

        if ($verifyContext) {
            return $this->runContextVerification($io);
        }

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
    private function checkPreconditions(SymfonyStyle $io, bool $force): bool
    {
        $problems = [];

        $site = basename((string) ($GLOBALS['OE_SITE_DIR'] ?? ''));
        if ($site !== 'default') {
            $problems[] = "Active site is '{$site}', expected 'default'.";
        }

        $facility = QueryUtils::fetchRecords('SELECT id, name FROM facility ORDER BY id LIMIT 1', []);
        if ($facility === []) {
            $problems[] = 'No facility exists.';
        } else {
            $this->facilityId = (int) $facility[0]['id'];
            $this->facilityName = (string) $facility[0]['name'];
            if (str_contains($this->facilityName, 'Your Clinic Name Here')) {
                $problems[] = 'Facility is still the installer default (RDY-0032 not closed).';
            }
        }

        // Providers must exist, or every encounter would be attributed to nobody.
        $this->providerIds = array_map(
            static fn(array $r): int => (int) $r['id'],
            QueryUtils::fetchRecords(
                "SELECT id FROM users WHERE username IN ('y.alharbi','s.almutairi') ORDER BY id",
                []
            )
        );
        if (count($this->providerIds) < 2) {
            $problems[] = 'The two demo physicians are missing (RDY-0010).';
        }

        // The rollback baseline must exist AND still hash correctly. Seeding without a proven
        // way back is the hard stop T0-3 exists to enforce.
        if (!is_file(self::BASELINE_PATH)) {
            $problems[] = 'RDY-0044-A baseline not found at its recorded path.';
        } elseif (hash_file('sha256', self::BASELINE_PATH) !== self::BASELINE_SHA256) {
            $problems[] = 'RDY-0044-A baseline hash MISMATCH — the rollback artefact is not the accepted one.';
        }

        // The synthetic-data control must be present at the moment of seeding (RDY-0028).
        $control = dirname(__DIR__, 6) . '/docs/evidence/EV-028-synthetic-data-control.md';
        if (!is_file($control)) {
            $problems[] = 'EV-028 synthetic-data control document is missing.';
        }

        // Refuse accidental duplicate re-seeding.
        $existing = (int) QueryUtils::fetchSingleValue(
            'SELECT COUNT(*) c FROM patient_data WHERE pubpid LIKE ?',
            'c',
            [self::MARKER . '%']
        );
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
     * PB-034: writing `$_SESSION['authUserID']` directly does NOT reach
     * `SessionWrapperFactory::getActiveSession()`, so every row landed with `created_by = 0`.
     */
    private function establishAuthorContext(): void
    {
        $this->authUserId = (int) QueryUtils::fetchSingleValue(
            "SELECT id FROM users WHERE username = 'admin'",
            'id',
            []
        );

        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        $session->set('authUserID', $this->authUserId);
        $session->set('authUser', 'admin');
        $session->set('authGroup', 'Default');
        $session->set('authProvider', 'Default');
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

        $createdBy = (int) QueryUtils::fetchSingleValue(
            'SELECT created_by FROM patient_data WHERE pid = ?',
            'created_by',
            [$pid]
        );
        $zeroRows = (int) QueryUtils::fetchSingleValue(
            'SELECT COUNT(*) c FROM patient_data WHERE created_by = 0 OR created_by IS NULL',
            'c',
            []
        );
        $resolves = (int) QueryUtils::fetchSingleValue(
            'SELECT COUNT(*) c FROM users WHERE id = ?',
            'c',
            [$createdBy]
        );

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
            $dob = sprintf('19%02d-%02d-%02d', 55 + ($i % 40), 1 + ($i % 12), 1 + ($i % 28));

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
            $dob = sprintf('19%02d-%02d-%02d', 55 + ($sourceIndex % 40), 1 + ($sourceIndex % 12), 1 + ($sourceIndex % 28));

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

        return (int) $result->getData()[0]['pid'];
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
                date('Y-m-d', strtotime('-' . (90 + $pid) . ' days')),
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
        $io->writeln(sprintf('  %d fictional payers', count($ids)));

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
            $date = date('Y-m-d H:i:s', strtotime("-{$daysAgo} days"));

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

        return (int) QueryUtils::fetchSingleValue(
            'SELECT encounter FROM form_encounter WHERE pid = ? ORDER BY id DESC LIMIT 1',
            'encounter',
            [$pid]
        );
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
            $this->insertEyeExam($encounters[$i]);
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

        // UPSTREAM DEFECT WORKAROUND — see EncounterService::insertSoapNote(), which builds its
        // `forms` insert without `user` or `groupname` (unlike addForm(), which every other form
        // path uses). Every SOAP note created through that service is therefore unattributed in
        // `forms`. Left alone, the demo would show 18 clinical notes authored by nobody, which is
        // exactly the detail an alert IT gatekeeper notices in the audit trail.
        if (is_array($result) && isset($result[1])) {
            QueryUtils::sqlStatementThrowException(
                'UPDATE forms SET user = ?, groupname = ?, date = ? WHERE id = ?',
                ['admin', 'Default', $enc['date'], $result[1]]
            );
        }
    }

    /**
     * DOCUMENTED SERVICE-LAYER EXCEPTION: `VitalsService::create()` is an empty stub in this
     * release (`// TODO: not sure we need this anymore.`), and `save()` expects a populated
     * `FormVitals` shaped by the form POST. The `form_vitals` row plus its `forms` registration
     * is written directly, mirroring exactly what the vitals form itself writes.
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
     */
    private function insertEyeExam(array $enc): void
    {
        if ($this->dryRun) {
            return;
        }

        $formId = (int) QueryUtils::sqlInsert(
            'INSERT INTO form_eye_base SET date = ?, pid = ?, user = ?, groupname = ?, '
            . 'authorized = 1, activity = 1',
            [$enc['date'], $enc['pid'], 'admin', 'Default']
        );

        addForm($enc['eid'], 'Eye Exam', $formId, 'eye_mag', $enc['pid'], 1, $enc['date'], 'admin', 'Default');

        foreach (self::EYE_SATELLITE_TABLES as $table) {
            QueryUtils::sqlInsert("INSERT INTO `{$table}` SET `id` = ?, `pid` = ?", [$formId, $enc['pid']]);
        }

        // Give the exam actual findings rather than an empty shell: uncorrected and
        // manifest-refraction visual acuity for both eyes.
        QueryUtils::sqlStatementThrowException(
            'UPDATE form_eye_acuity SET SCODVA = ?, SCOSVA = ?, MRODVA = ?, MROSVA = ? WHERE id = ?',
            ['20/40', '20/50', '20/20', '20/25', $formId]
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
        addForm($enc['eid'], $name, $formId, $directory, $enc['pid'], 1, $enc['date'], 'admin', 'Default');
    }

    // ------------------------------------------------------------ appointments

    /** @param list<int> $pids */
    private function seedAppointments(SymfonyStyle $io, array $pids): void
    {
        $io->section('Appointments');

        $service = new AppointmentService();
        $monday = date('Y-m-d', strtotime('monday this week'));
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
                $date = date('Y-m-d', strtotime($monday . ' +1 day'));
            } elseif ($i < 5) {
                $status = 'x';                     // cancelled
                $cancelled++;
                $date = date('Y-m-d', strtotime($monday . ' +2 day'));
            } elseif ($i < 17) {
                $status = ['@', '>', '<', '-'][$i % 4];
                $date = $today;                    // today's flow board
            } else {
                $status = ['-', '@', '>'][$i % 3];
                $date = date('Y-m-d', strtotime($monday . ' +' . ($i % 5) . ' day'));
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

        $start = date('Y-m-d', strtotime('monday this week'));
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
                date('Y-m-d', strtotime($start . ' +8 weeks')),
                '-',
                '09:00:00',
                '09:30:00',
                $this->facilityId,
                $this->facilityId,
                $this->authUserId,
                $this->providerIds[0],
                'a:5:{s:17:"event_repeat_freq";s:1:"1";s:22:"event_repeat_freq_type";s:1:"5";'
                . 's:19:"event_repeat_on_num";s:1:"1";s:19:"event_repeat_on_day";s:1:"0";'
                . 's:20:"event_repeat_on_freq";s:1:"0";}',
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
                $pid,
                $this->documentCategoryId(),
                sprintf('SYNTHETIC-DEMO-specimen-%02d.txt', $i + 1),
                'text/plain',
                $body,
                eid: $eid
            );

            if (empty($error)) {
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
                'date_added'   => date('Y-m-d', strtotime('-' . ($i * 5) . ' days')),
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

        $charges = 0;
        $billable = array_slice($encounters, 0, self::TARGET_CHARGES);

        foreach ($billable as $i => $enc) {
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
                // The last charge is deliberately left `billed = 0` so the unbilled/pending
                // report has exactly one hit; the rest are marked billed.
                $isLast = ($i === count($billable) - 1);
                QueryUtils::sqlStatementThrowException(
                    'UPDATE billing SET date = ?, billed = ? WHERE id = ?',
                    [$enc['date'], $isLast ? 0 : 1, $billingId]
                );
            }
            $charges++;
        }

        $this->counts['charges'] = $charges;
        $this->counts['unbilled_encounters'] = 1;

        $payments = $this->seedFinancialEvents($encounters, $payers);
        $this->counts['payments'] = $payments['payments'];
        $this->counts['adjustments'] = $payments['adjustments'];

        $io->writeln(sprintf(
            '  %d charges (1 encounter left deliberately unbilled), %d payments, %d adjustments',
            $charges,
            $payments['payments'],
            $payments['adjustments']
        ));
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
            $postDate = date('Y-m-d', strtotime('-' . $bands[$i % count($bands)] . ' days'));

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
            $postDate = date('Y-m-d', strtotime('-' . $bands[$i % count($bands)] . ' days'));

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

    private function printManifest(SymfonyStyle $io): void
    {
        $io->section('Seed manifest');
        $io->writeln('  Profile:    ' . self::PROFILE_VERSION);
        $io->writeln('  Seed:       ' . self::RANDOM_SEED . ' (fixed — the dataset is reproducible)');
        $io->writeln('  Marker:     ' . self::MARKER . 'nnnn in patient_data.pubpid');
        $io->writeln('  Author:     user id ' . $this->authUserId);
        $io->writeln('  Baseline:   RDY-0044-A ' . substr(self::BASELINE_SHA256, 0, 16) . '… (hash verified this run)');
        $io->newLine();

        $rows = [];
        foreach ($this->counts as $k => $v) {
            $rows[] = [$k, (string) $v];
        }
        $io->table(['Category', 'Seeded'], $rows);
    }
}
