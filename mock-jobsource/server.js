const express = require('express');
const app = express();
app.use(express.json());

// ---- داده‌های Mock مطابق با contracts/jobsource.yaml ----

const positions = [
  {
    external_id: 'EXT-1001',
    title: 'Senior Backend Developer (PHP/Laravel)',
    department: 'Engineering',
    level: 'senior',
    employment_type: 'full_time',
    min_experience_years: 5,
    description: 'طراحی و توسعه سرویس‌های بک‌اند با Laravel و بهینه‌سازی کارایی.',
    required_skills: [
      { name: 'PHP', weight: 9, min_years: 5 },
      { name: 'Laravel', weight: 9, min_years: 4 },
      { name: 'PostgreSQL', weight: 7, min_years: 3 },
      { name: 'Docker', weight: 6, min_years: 2 },
      { name: 'Redis', weight: 6, min_years: 2 },
    ],
    preferred_skills: ['Kubernetes', 'Microservices', 'RabbitMQ', 'System Design'],
  },
  {
    external_id: 'EXT-1002',
    title: 'Frontend Developer (Nuxt/Vue)',
    department: 'Engineering',
    level: 'mid',
    employment_type: 'full_time',
    min_experience_years: 3,
    description: 'توسعه رابط کاربری فارسی و راست‌چین با Nuxt 3 و Tailwind.',
    required_skills: [
      { name: 'Vue', weight: 8, min_years: 3 },
      { name: 'Nuxt', weight: 7, min_years: 2 },
      { name: 'Tailwind', weight: 6, min_years: 2 },
      { name: 'TypeScript', weight: 7, min_years: 2 },
    ],
    preferred_skills: ['Pinia', 'i18n', 'RTL Layouts', 'Accessibility'],
  },
  {
    external_id: 'EXT-1003',
    title: 'Data Engineer',
    department: 'Data',
    level: 'mid',
    employment_type: 'full_time',
    min_experience_years: 3,
    required_skills: [
      { name: 'Python', weight: 8, min_years: 3 },
      { name: 'PostgreSQL', weight: 7, min_years: 3 },
      { name: 'Airflow', weight: 6, min_years: 2 },
    ],
    preferred_skills: ['Spark', 'dbt', 'Snowflake'],
  },
  {
    external_id: 'EXT-1004',
    title: 'Junior Backend Developer',
    department: 'Engineering',
    level: 'junior',
    employment_type: 'full_time',
    min_experience_years: 0,
    required_skills: [
      { name: 'PHP', weight: 7, min_years: 0 },
      { name: 'Laravel', weight: 6, min_years: 0 },
    ],
    preferred_skills: ['Git', 'Docker', 'REST API'],
  },
  {
    external_id: 'EXT-1005',
    title: 'DevOps Engineer',
    department: 'Platform',
    level: 'senior',
    employment_type: 'full_time',
    min_experience_years: 5,
    required_skills: [
      { name: 'Docker', weight: 8, min_years: 3 },
      { name: 'Kubernetes', weight: 9, min_years: 3 },
      { name: 'Terraform', weight: 7, min_years: 2 },
    ],
    preferred_skills: ['AWS', 'GitOps', 'Prometheus', 'GitLab CI'],
  },
];

const candidates = [
  { external_id: 'EXT-C-501', name: 'علی محمدی',  email: 'ali.m@example.com',  phone: '+98 912 111 1111' },
  { external_id: 'EXT-C-502', name: 'مریم حسینی', email: 'maryam@example.com', phone: '+98 912 222 2222' },
  { external_id: 'EXT-C-503', name: 'رضا کریمی',  email: 'reza@example.com',   phone: '+98 912 333 3333' },
  { external_id: 'EXT-C-504', name: 'نگار احمدی', email: 'negar@example.com',  phone: '+98 912 444 4444' },
  { external_id: 'EXT-C-505', name: 'حسین رضایی', email: 'hossein@example.com', phone: '+98 912 555 5555' },
];

function buildResume(name, headline, skills) {
  const skillLines = skills.map(([s, y]) => `- ${s} (${y} سال تجربه)`).join('\n');
  return `نام: ${name}
عنوان: ${headline}
خلاصه: توسعه‌دهنده با تجربه در پروژه‌های سازمانی.

مهارت‌ها:
${skillLines}

سوابق کاری:
- شرکت فناوری پارس (1400 - تاکنون): ${headline}
- استارتاپ نوآوران (1398 - 1400): توسعه‌دهنده

تحصیلات:
- کارشناسی مهندسی کامپیوتر، دانشگاه تهران، 1397`;
}

const resumes = {
  'EXT-C-501': buildResume('علی محمدی',  'Senior Backend Developer', [['PHP', 7], ['Laravel', 6], ['PostgreSQL', 5], ['Docker', 4], ['Redis', 4], ['Kubernetes', 2]]),
  'EXT-C-502': buildResume('مریم حسینی', 'Frontend Developer',       [['Vue', 4], ['Nuxt', 3], ['Tailwind', 3], ['TypeScript', 3], ['React', 2]]),
  'EXT-C-503': buildResume('رضا کریمی',  'Data Engineer',            [['Python', 5], ['PostgreSQL', 4], ['Airflow', 3], ['Spark', 2]]),
  'EXT-C-504': buildResume('نگار احمدی', 'Junior Developer',         [['PHP', 1], ['Laravel', 1], ['Git', 1]]),
  'EXT-C-505': buildResume('حسین رضایی', 'DevOps Engineer',          [['Docker', 6], ['Kubernetes', 5], ['Terraform', 4], ['AWS', 3], ['GitLab CI', 3]]),
};

// ---- Routes ----
app.get('/health', (req, res) => res.json({ status: 'ok', service: 'mock-jobsource' }));

app.get('/api/v1/positions', (req, res) => {
  let result = positions;
  if (req.query.status) result = result.filter(p => p.status === req.query.status);
  if (req.query.department) result = result.filter(p => p.department === req.query.department);
  res.json(result);
});

app.get('/api/v1/positions/:id', (req, res) => {
  const p = positions.find(p => p.external_id === req.params.id);
  if (!p) return res.status(404).json({ error: 'not_found' });
  res.json(p);
});

app.get('/api/v1/candidates', (req, res) => res.json(candidates));

app.get('/api/v1/candidates/:id/resume', (req, res) => {
  const text = resumes[req.params.id];
  if (!text) return res.status(404).json({ error: 'not_found' });
  res.json({ candidate_external_id: req.params.id, raw_text: text });
});

const port = process.env.PORT || 4000;
app.listen(port, () => {
  console.log(`[mock-jobsource] listening on ${port}`);
});
