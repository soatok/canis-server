CREATE TABLE IF NOT EXISTS canis_files (
    fileid BIGSERIAL PRIMARY KEY,
    filename TEXT,
    cdn_urls TEXT NULL, -- Amazon S3, etc. URLs
    filesize BIGINT, -- Bytes
    realpath TEXT, -- Local filesystem path (CDN fallback)
    filetype TEXT, -- e.g. "application/zip"
    filehash TEXT UNIQUE, -- BLAKE2b hash (with domain separation)
    uploaded_by BIGINT REFERENCES canis_accounts(accountid),
    created TIMESTAMP DEFAULT NOW()
);
