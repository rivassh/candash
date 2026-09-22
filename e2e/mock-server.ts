import http from 'http';
import { randomUUID } from 'crypto';

const mockData = {
  candidates: [],
  jobPositions: [],
  matches: [],
};

const seedData = () => {
  if (mockData.candidates.length === 0) {
    mockData.candidates.push({
      id: randomUUID(),
      name: 'Ahmed',
      email: 'ahmed@example.com',
      phone: '+913-987654321',
      linkedinUrl: 'https://linkedin.com/in/ahmed',
      status: 'active',
      summary: 'Senior Developer',
      skills: [
        { id: randomUUID(), name: 'React', category: 'frontend', yearsExperience: 5 },
        { id: randomUUID(), name: 'Node.js', category: 'backend', yearsExperience: 3 },
      ],
      experiences: [
        {
          id: randomUUID(),
          company: 'TechCorp',
          jobTitle: 'Senior Engineer',
          startDate: '2020-01-15',
          endDate: null,
          isCurrent: true,
          confidence: 0.9,
          source: 'resume',
        },
      ],
      educations: [
        {
          id: randomUUID(),
          degree: 'Bachelor of Science',
          fieldOfStudy: 'Computer Science',
          institution: 'University of Tehran',
          graduationYear: 2018,
          confidence: 0.95,
          source: 'edu',
        },
      ],
      latestResume: {
        id: randomUUID(),
        status: 'published',
        confidence: 1.0,
        createdAt: '2024-01-15',
      },
    });
  }

  if (mockData.jobPositions.length === 0) {
    mockData.jobPositions.push({
      id: randomUUID(),
      title: 'Backend Engineer',
      department: 'engineering',
      level: 'mid',
      minExperienceYears: 2,
      description: 'Build scalable backend services',
    });
    mockData.jobPositions.push({
      id: randomUUID(),
      title: 'Frontend Developer',
      department: 'frontend',
      level: 'junior',
      minExperienceYears: 1,
      description: 'Create responsive UI components',
    });
  }

  if (mockData.matches.length === 0) {
    mockData.matches.push({
      id: '123',
      candidateId: mockData.candidates[0]?.id ?? 'candidate-1',
      jobPositionId: mockData.jobPositions[0]?.id ?? 'job-position-1',
      totalScore: 85,
      status: 'shortlisted',
    });
    mockData.matches.push({
      id: randomUUID(),
      candidateId: mockData.candidates[0]?.id ?? 'candidate-1',
      jobPositionId: mockData.jobPositions[0]?.id ?? 'job-position-1',
      totalScore: 85,
      status: 'shortlisted',
    });
  }
};

const parseBody = (req: http.IncomingMessage): Promise<any> =>
  new Promise((resolve, reject) => {
    let body = '';
    req.on('data', chunk => body += chunk);
    req.on('end', () => {
      try {
        resolve(body ? JSON.parse(body) : {});
      } catch (error) {
        reject(error);
      }
    });
    req.on('error', reject);
  });

const sendJson = (res: http.ServerResponse, statusCode: number, data: any) => {
  res.statusCode = statusCode;
  res.setHeader('Content-Type', 'application/json');
  res.end(JSON.stringify(data));
};

const server = http.createServer(async (req, res) => {
  const url = new URL(req.url ?? '/', 'http://localhost:8086');
  const pathname = url.pathname;
  const method = req.method;

  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
  res.setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');

  if (method === 'OPTIONS') {
    res.statusCode = 200;
    res.end();
    return;
  }

  try {
    // Health check
    if (pathname === '/api/health' && method === 'GET') {
      sendJson(res, 200, { status: 'ok', app: 'Mock Candash API' });
      return;
    }

    // Authentication
    if (pathname === '/api/auth/login' && method === 'POST') {
      const body = await parseBody(req);
      if (!body.email || !body.password) {
        sendJson(res, 422, { error: 'Validation failed' });
        return;
      }
      if (body.password !== 'admin123') {
        sendJson(res, 401, { error: 'Invalid credentials' });
        return;
      }
      sendJson(res, 200, { token: randomUUID(), user: { name: 'Test User', email: body.email } });
      return;
    }

    if (pathname === '/api/auth/logout' && method === 'POST') {
      sendJson(res, 200, { message: 'Logged out successfully' });
      return;
    }

    if (pathname === '/api/auth/me' && method === 'GET') {
      sendJson(res, 200, { name: 'Test User', email: 'test@example.com' });
      return;
    }

    if (pathname === '/api/search/candidates' && method === 'GET') {
      const q = url.searchParams.get('q') ?? '';
      const status = url.searchParams.get('status');
      const skill_ids = url.searchParams.get('skill_ids');
      const min_experience = url.searchParams.get('min_experience');
      const page = parseInt(url.searchParams.get('page') ?? '1', 10);
      const perPage = 20;
      
      let results = mockData.candidates;
      if (q) {
        results = results.filter(c => c.name.toLowerCase().includes(q.toLowerCase()));
      }
      if (status) {
        results = results.filter(c => c.status === status);
      }
      
      const total = results.length;
      const offset = (page - 1) * perPage;
      const paginated = results.slice(offset, offset + perPage);
      
      sendJson(res, 200, {
        data: paginated.map(c => ({
          id: c.id,
          name: c.name,
          status: c.status,
          score: 0.9,
          skills: c.skills?.map(s => s.name) ?? [],
        })),
        meta: {
          total,
          page,
          per_page: perPage,
          last_page: Math.ceil(total / perPage),
        },
      });
      return;
    }

    if (pathname === '/api/search/jobs' && method === 'GET') {
      const q = url.searchParams.get('q') ?? '';
      const level = url.searchParams.get('level');
      const employment_type = url.searchParams.get('employment_type');
      const department = url.searchParams.get('department');
      const page = parseInt(url.searchParams.get('page') ?? '1', 10);
      const perPage = 20;
      
      let results = mockData.jobPositions;
      if (q) {
        results = results.filter(j => j.title.toLowerCase().includes(q.toLowerCase()));
      }
      if (level) {
        results = results.filter(j => j.level === level);
      }
      if (employment_type) {
        results = results.filter(j => j.employment_type === employment_type);
      }
      if (department) {
        results = results.filter(j => j.department === department);
      }
      
      const total = results.length;
      const offset = (page - 1) * perPage;
      const paginated = results.slice(offset, offset + perPage);
      
      sendJson(res, 200, {
        data: paginated.map(j => ({
          id: j.id,
          title: j.title,
          department: j.department,
          level: j.level,
          employment_type: j.employment_type,
          required_skills: j.required_skills ?? [],
          preferred_skills: j.preferred_skills ?? [],
          score: 0.9,
          status: 'open',
        })),
        meta: {
          total,
          page,
          per_page: perPage,
          last_page: Math.ceil(total / perPage),
        },
      });
      return;
    }

    if (pathname === '/api/search/health' && method === 'GET') {
      sendJson(res, 200, { status: 'ok', meilisearch: 'available' });
      return;
    }

    if (pathname === '/api/search/reindex' && method === 'POST') {
      sendJson(res, 200, { message: 'Reindexed 5 candidates', type: 'candidates' });
      return;
    }

    if (pathname === '/api/search/match' && method === 'GET') {
      const jobPositionId = url.searchParams.get('job_position_id');
      if (!jobPositionId || !mockData.jobPositions.find(j => j.id === jobPositionId)) {
        sendJson(res, 404, { message: 'Job position not found.' });
        return;
      }
      const page = parseInt(url.searchParams.get('page') ?? '1', 10);
      const perPage = 20;
      
      const results = mockData.candidates.map(c => ({
        id: c.id,
        name: c.name,
        status: c.status,
        score: 0.9,
        match_score: 85,
        match_breakdown: { skills: 50, experience: 35 },
        skills: c.skills?.map(s => s.name) ?? [],
      }));
      
      sendJson(res, 200, {
        data: results,
        meta: {
          total: results.length,
          page,
          per_page: perPage,
          last_page: 1,
        },
        job_position: mockData.jobPositions.find(j => j.id === jobPositionId),
      });
      return;
    }

    // Dashboard
    if (pathname === '/api/dashboard/summary' && method === 'GET') {
      const totalCandidates = mockData.candidates.length;
      const totalJobs = mockData.jobPositions.length;
      const totalMatches = mockData.matches.length;
      sendJson(res, 200, {
        candidates: { total: totalCandidates, new: totalCandidates, in_review: 0 },
        positions: { total: totalJobs, open: totalJobs },
        matches: { total: totalMatches, shortlisted: totalMatches },
        top_matches: mockData.matches.slice(0, 3),
      });
      return;
    }

    // Job Positions
    if (pathname === '/api/JobPositions' && method === 'GET') {
      sendJson(res, 200, { data: mockData.jobPositions });
      return;
    }

    if (pathname === '/api/JobPositions' && method === 'POST') {
      const body = await parseBody(req);
      if (!body.title) {
        sendJson(res, 422, { error: 'Validation failed' });
        return;
      }
      const newJob = { id: randomUUID(), ...body };
      mockData.jobPositions.push(newJob);
      sendJson(res, 201, newJob);
      return;
    }

    const jobMatch = pathname.match(/^\/api\/JobPositions\/(.+)$/);
    if (jobMatch && method === 'GET') {
      const job = mockData.jobPositions.find(j => j.id === jobMatch[1]);
      if (!job) {
        sendJson(res, 404, { error: 'Job not found' });
        return;
      }
      sendJson(res, 200, job);
      return;
    }

    // Candidates
    if (pathname === '/api/candidates' && method === 'GET') {
      const search = url.searchParams.get('search');
      const results = search
        ? mockData.candidates.filter(c => c.name.toLowerCase().includes(search.toLowerCase()))
        : mockData.candidates;
      sendJson(res, 200, { data: results });
      return;
    }

    if (pathname === '/api/candidates' && method === 'POST') {
      const body = await parseBody(req);
      if (!body.name) {
        sendJson(res, 422, { error: 'Validation failed' });
        return;
      }
      const newCandidate = { id: randomUUID(), ...body };
      mockData.candidates.push(newCandidate);
      sendJson(res, 201, newCandidate);
      return;
    }

    const candidateMatch = pathname.match(/^\/api\/candidates\/(.+?)(?:\/profile)?$/);
    if (candidateMatch && method === 'GET') {
      const candidate = mockData.candidates.find(c => c.id === candidateMatch[1]);
      if (!candidate) {
        sendJson(res, 404, { error: 'Candidate not found' });
        return;
      }
      sendJson(res, 200, candidate);
      return;
    }

    if (candidateMatch && method === 'PUT') {
      const body = await parseBody(req);
      const index = mockData.candidates.findIndex(c => c.id === candidateMatch[1]);
      if (index === -1) {
        sendJson(res, 404, { error: 'Candidate not found' });
        return;
      }
      const updated = { ...mockData.candidates[index], ...body };
      mockData.candidates[index] = updated;
      sendJson(res, 200, updated);
      return;
    }

    if (candidateMatch && method === 'DELETE') {
      const index = mockData.candidates.findIndex(c => c.id === candidateMatch[1]);
      if (index === -1) {
        sendJson(res, 404, { error: 'Candidate not found' });
        return;
      }
      mockData.candidates.splice(index, 1);
      res.statusCode = 204;
      res.end();
      return;
    }

    // Matching
    if (pathname === '/api/match/run' && method === 'POST') {
      const body = await parseBody(req);
      if (!body.candidate_ids && !body.position_ids) {
        sendJson(res, 422, { message: 'At least one candidate_ids or position_ids is required.' });
        return;
      }
      const results = mockData.candidates.map(c => ({
        id: randomUUID(),
        candidateId: c.id,
        jobPositionId: body.job_position_id ?? mockData.jobPositions[0]?.id,
        totalScore: 85,
        status: 'shortlisted',
      }));
      sendJson(res, 200, { data: results, position: { id: body.job_position_id ?? mockData.jobPositions[0]?.id, title: 'Test Position' } });
      return;
    }

    if (pathname === '/api/match/results' && method === 'GET') {
      const search = new URL(req.url ?? '', `http://${req.headers.host}`).searchParams.get('search');
      let matches = mockData.matches;
      if (search) {
        matches = matches.filter(m =>
          (m.candidateId ?? '').toLowerCase().includes(search.toLowerCase()) ||
          (m.status ?? '').toLowerCase().includes(search.toLowerCase())
        );
      }
      sendJson(res, 200, { data: matches, meta: { current_page: 1, last_page: 1, per_page: 20, total: matches.length } });
      return;
    }

    if (pathname === '/api/match/results' && method === 'POST') {
      const body = await parseBody(req);
      const newMatch = {
        id: body.id ?? randomUUID(),
        candidateId: body.candidateId ?? 'candidate-1',
        jobPositionId: body.jobPosition_id ?? mockData.jobPositions[0]?.id,
        totalScore: body.totalScore ?? 85,
        status: body.status ?? 'pending',
      };
      mockData.matches.push(newMatch);
      sendJson(res, 201, newMatch);
      return;
    }

    const matchResultMatch = pathname.match(/^\/api\/match\/results\/(.+?)(?:\/status)?$/)
    if (matchResultMatch && method === 'GET') {
      const result = mockData.matches.find(m => m.id === matchResultMatch[1]);
      if (!result) {
        sendJson(res, 404, { error: 'Match result not found' });
        return;
      }
      sendJson(res, 200, result);
      return;
    }

    if (matchResultMatch && method === 'PATCH') {
      const body = await parseBody(req);
      const match = mockData.matches.find(m => m.id === matchResultMatch[1]);
      if (!match) {
        sendJson(res, 404, { error: 'Match result not found' });
        return;
      }
      match.status = body.status;
      sendJson(res, 200, match);
      return;
    }

    if (matchResultMatch && method === 'DELETE') {
      const index = mockData.matches.findIndex(m => m.id === matchResultMatch[1]);
      if (index === -1) {
        sendJson(res, 404, { error: 'Match result not found' });
        return;
      }
      mockData.matches.splice(index, 1);
      res.statusCode = 204;
      res.end();
      return;
    }

    sendJson(res, 404, { error: 'Not found' });
  } catch (error) {
    sendJson(res, 500, { error: 'Mock server error' });
  }
});

const PORT = parseInt(process.env.PORT ?? '8086', 10);
seedData();
server.listen(PORT, () => {
  console.log(`Mock API server running on http://localhost:${PORT}`);
});