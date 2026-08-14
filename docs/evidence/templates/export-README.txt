THIQA — CUSTOMER DATA EXPORT PACKAGE
====================================

This package is a complete copy of your clinical record as held in your Thiqa
instance. It is yours. You do not need us, or any Thiqa software, to read it.

WHAT IS IN HERE
---------------
database/openemr-full.sql
    A complete SQL dump of your database: all 283 tables, schema and data.
    Restore it into any MySQL or MariaDB server:
        mysql -u <user> -p <newdatabase> < openemr-full.sql
    This is the authoritative copy. Everything else in this package is derived
    from it and is provided for convenience.

reports/*.csv
    Report extracts in comma-separated format. They open directly in Excel,
    LibreOffice, Numbers or Google Sheets. Column headings are the same as the
    headings shown on screen in the application.

documents/
    Every file uploaded to a patient record, in the folder layout the
    application stores them in (one numbered folder per patient).

    IMPORTANT: the files themselves are named with internal identifiers and
    carry no file extension. DOCUMENT-MANIFEST.csv maps every one of them to
    its patient, its original filename and its file type. Read the manifest
    first.

WHAT IS NOT IN HERE
-------------------
- The Thiqa application itself. The software is open source (GPL-3.0-or-later)
  and is obtainable independently of us.
- Any configuration specific to our hosting.
- A migration into another vendor's system. That is a separate service and is
  not part of this export.

VERIFYING THIS PACKAGE
----------------------
CHECKSUMS.sha256 lists a SHA-256 for every file. Verify with:
    sha256sum -c CHECKSUMS.sha256

QUESTIONS
---------
Leaving should be a procedure, not a negotiation. If anything in this package
is unreadable or looks incomplete, say so and we will fix it — that obligation
does not end when the contract does.
