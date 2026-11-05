import http from 'k6/http';
import { check, sleep } from 'k6';

/**
 * 🏸 SMASHHUB — k6 Concurrency Benchmark Script
 * 
 * Mô tả: Kiểm thử tải và tranh chấp đặt sân cùng một thời điểm với k6.
 * Chạy lệnh: k6 run tests/concurrent_join.js
 */

export const options = {
  scenarios: {
    concurrent_booking: {
      executor: 'per-vu-iterations',
      vus: 10,
      iterations: 1,
      maxDuration: '30s',
    },
  },
  thresholds: {
    http_req_failed: ['rate<0.9'], // Cho phép các request sau bị chặn 409/Overlapping
    http_req_duration: ['p(95)<2000'], // 95% request phản hồi dưới 2s
  },
};

const BASE_URL = __ENV.BASE_URL || 'http://localhost/badminton';

export default function () {
  const url = `${BASE_URL}/index.php?action=book_pitch`;
  
  const payload = {
    pitch_id: '1',
    booking_date: '2026-12-01',
    start_time: '19:00:00',
    end_time: '20:00:00',
    payment_method: 'cash',
    is_ajax: '1',
  };

  const params = {
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
      'X-Requested-With': 'XMLHttpRequest',
    },
  };

  // Convert payload to form-urlencoded
  const body = Object.keys(payload)
    .map(key => encodeURIComponent(key) + '=' + encodeURIComponent(payload[key]))
    .join('&');

  const res = http.post(url, body, params);

  check(res, {
    'status is 200 or 400 or 409': (r) => [200, 400, 409].includes(r.status),
    'response has json format': (r) => {
      try {
        JSON.parse(r.body);
        return true;
      } catch (e) {
        return false;
      }
    },
  });

  sleep(0.1);
}

