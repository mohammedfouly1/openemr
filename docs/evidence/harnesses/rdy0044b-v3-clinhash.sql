-- RDY-0044-B v3 re-baseline — clinical fingerprint (CLINHASH)
-- AGENT-DATA, PB-17x. Re-runnable: mariadb -u root --host=127.0.0.1 --port=3306 openemr < this file
-- Combines per-table MD5(GROUP_CONCAT(...)) over actual clinical VALUES (not row counts) across
-- the tables most exposed by this operation (form_vitals, insurance_companies — the UUID fix) plus
-- the broader clinical surface already tracked by EV-044/EV-021 (SOAP, allergies/problems, rx,
-- encounters, eye-exam vitals/IOP) so a dump/restore round-trip corruption would show up here even
-- if it never touched a UUID column.
SELECT MD5(CONCAT_WS('|',
  (SELECT MD5(IFNULL(GROUP_CONCAT(CONCAT_WS(':', id, pid, date, bps, bpd, weight, height, temperature,
      pulse, respiration, BMI, waist_circ, head_circ, oxygen_saturation, note) ORDER BY id SEPARATOR '~'), ''))
   FROM form_vitals),
  (SELECT MD5(IFNULL(GROUP_CONCAT(CONCAT_WS(':', id, name, cms_id, ins_type_code, inactive, eligibility_id)
      ORDER BY id SEPARATOR '~'), ''))
   FROM insurance_companies),
  (SELECT MD5(IFNULL(GROUP_CONCAT(CONCAT_WS(':', id, pid, date, user, subjective, objective, assessment, plan)
      ORDER BY id SEPARATOR '~'), ''))
   FROM form_soap),
  (SELECT MD5(IFNULL(GROUP_CONCAT(CONCAT_WS(':', id, pid, type, subtype, title, begdate, enddate, diagnosis, comments)
      ORDER BY id SEPARATOR '~'), ''))
   FROM lists),
  (SELECT MD5(IFNULL(GROUP_CONCAT(CONCAT_WS(':', id, patient_id, drug, drug_id, encounter, start_date)
      ORDER BY id SEPARATOR '~'), ''))
   FROM prescriptions),
  (SELECT MD5(IFNULL(GROUP_CONCAT(CONCAT_WS(':', id, pid, date, reason, encounter, sensitivity)
      ORDER BY id SEPARATOR '~'), ''))
   FROM form_encounter),
  (SELECT MD5(IFNULL(GROUP_CONCAT(CONCAT_WS(':', id, pid, alert, oriented, confused, ODIOPAP, OSIOPAP,
      ODIOPTARGET, OSIOPTARGET) ORDER BY id SEPARATOR '~'), ''))
   FROM form_eye_vitals)
)) AS CLINHASH;
