CREATE EXTENSION IF NOT EXISTS vector;
CREATE EXTENSION IF NOT EXISTS pg_trgm;

-- ایندکس‌های جستجوی سریع‌تر
CREATE INDEX IF NOT EXISTS candidates_name_trgm_idx ON candidates USING gin (name gin_trgm_ops);
CREATE INDEX IF NOT EXISTS job_positions_title_trgm_idx ON job_positions USING gin (title gin_trgm_ops);