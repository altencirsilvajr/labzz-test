import http from "k6/http";
import { check, sleep } from "k6";

export const options = {
  vus: 50,
  duration: "2m",
  thresholds: {
    http_req_duration: ["p(95)<300"],
    http_req_failed: ["rate<0.02"]
  }
};

const BASE = __ENV.BASE_URL || "http://localhost:8080";
const TOKEN = __ENV.BEARER_TOKEN || "";

export default function () {
  const headers = {
    Authorization: `Bearer ${TOKEN}`,
    "Content-Type": "application/json"
  };

  const health = http.get(`${BASE}/health`);
  check(health, { "health 200": (r) => r.status === 200 });

  const users = http.get(`${BASE}/v1/users`, { headers });
  check(users, { "users 200": (r) => r.status === 200 || r.status === 401 });

  sleep(1);
}
