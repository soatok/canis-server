CREATE TABLE IF NOT EXISTS canis_vendors (
    vendorid BIGSERIAL PRIMARY KEY,
    name TEXT UNIQUE,
    display_name TEXT,
    biography TEXT,
    created TIMESTAMP DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS canis_vendor_accounts (
    vendorid BIGINT REFERENCES canis_vendors (vendorid),
    accountid BIGINT REFERENCES canis_accounts (accountid)
);

CREATE TABLE IF NOT EXISTS canis_vendor_public_keys (
    publickeyid BIGSERIAL PRIMARY KEY,
    vendorid BIGINT REFERENCES canis_vendors (vendorid),
    publickey TEXT,
    revoked BOOLEAN DEFAULT FALSE,
    chronicle_create TEXT, -- summaryhash from Chronicle instance
    chronicle_revoke TEXT NULL, -- summaryhash from Chronicle instance
    created TIMESTAMP DEFAULT NOW()
);
