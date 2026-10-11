import http from 'k6/http';
import { check, sleep } from 'k6';

export const options = {
  scenarios: {
    smoke: {
      executor: 'ramping-vus',
      stages: [
        { duration: '20s', target: 10 },
        { duration: '40s', target: 25 },
        { duration: '20s', target: 0 },
      ],
    },
  },
  thresholds: {
    http_req_failed: ['rate<0.01'],
    http_req_duration: ['p(95)<800'],
    checks: ['rate>0.99'],
  },
};

const base = (__ENV.BASE_URL || 'http://127.0.0.1:8000').replace(/\/$/, '');

export default function () {
  const ready = http.get(`${base}/health/ready`);
  check(ready, { 'ready is 200': (r) => r.status === 200 });
  const home = http.get(`${base}/`);
  check(home, { 'home is 200': (r) => r.status === 200 });
  const login = http.get(`${base}/login`);
  check(login, { 'login is 200': (r) => r.status === 200 });
  sleep(1);
}