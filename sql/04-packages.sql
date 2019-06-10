CREATE TABLE IF NOT EXISTS canis_packages (
    packageid BIGSERIAL PRIMARY KEY,
    vendorid BIGINT REFERENCES canis_vendors (vendorid),
    name TEXT,
    platform TEXT, -- Game Maker 2, RPG Maker XP, RPG Maker MV, etc.
    type TEXT, -- Script, Plugin, Library, etc. Can be platform-specific.
    deprecated BOOLEAN DEFAULT FALSE,
    recommend BIGINT REFERENCES canis_packages (packageid), -- If deprecated, recommend new
    created TIMESTAMP DEFAULT NOW(),
    modified TIMESTAMP
);
CREATE UNIQUE INDEX ON canis_packages (vendorid, name);

/* Maintains a revision history of the package metadata: */
CREATE TABLE IF NOT EXISTS canis_package_meta (
    metaid BIGSERIAL PRIMARY KEY,
    packageid BIGINT REFERENCES canis_packages (packageid),
    summary TEXT,
    tags JSONB,
    created TIMESTAMP DEFAULT NOW()
);

/* Release metadata */
CREATE TABLE IF NOT EXISTS canis_package_releases (
    releaseid BIGSERIAL PRIMARY KEY,
    packageid BIGINT REFERENCES canis_packages (packageid),
    tagname TEXT, -- v1.0.1, etc.
    stable BOOLEAN DEFAULT FALSE, -- Safe to auto-update?
    publickeyid BIGINT REFERENCES canis_vendor_public_keys (publickeyid),
    fileid BIGINT REFERENCES canis_files (fileid),
    signature TEXT, -- Digital signature
    chronicle_data TEXT, -- BLAKE2b hash of .zip bundle, git/svn version info, etc.
    chronicle_create TEXT, -- summaryhash from Chronicle instance
    chronicle_revoke TEXT NULL, -- summaryhash from Chronicle instance
    revoked BOOLEAN DEFAULT FALSE,
    revoke_reason TEXT,
    created TIMESTAMP DEFAULT NOW()
);
