# Lumen API Documentation

Lumen surfaces a thin, robust JSON REST API layer over its core services. All responses are encapsulated inside standard envelopes (`{ "data": ... }` or `{ "data": ..., "meta": ... }`).

## Error Envelope Shape

Standard HTTP error responses are wrapped in an error container:
```json
{
  "error": {
    "message": "Resource not found.",
    "code": "NOT_FOUND"
  }
}
```
HTTP status codes are returned correctly:
- `422 Unprocessable Entity`: Validation failures.
- `404 Not Found`: Missing resource models.
- `500 Internal Server Error`: Critical unhandled exceptions.

---

## Endpoints

### 1. Knowledge Base CRUD

#### List Chunks
- **Method & Path**: `GET /api/knowledge`
- **Response**: List of all knowledge base chunks.
- **cURL Example**:
  ```bash
  curl -X GET http://127.0.0.1:8000/api/knowledge \
    -H "Accept: application/json"
  ```

#### Create Chunk
- **Method & Path**: `POST /api/knowledge`
- **Headers**: `Content-Type: application/json`
- **Request Body**:
  ```json
  {
    "title": "Domestic Refund Policy",
    "content": "Our domestic refund policy allows customers to request a full refund within 30 days of purchase.",
    "tags": ["refund", "domestic", "policy"]
  }
  ```
- **cURL Example**:
  ```bash
  curl -X POST http://127.0.0.1:8000/api/knowledge \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"title": "International Shipping Timelines", "content": "International orders take 7-14 business days.", "tags": ["shipping", "international"]}'
  ```

#### Update Chunk
- **Method & Path**: `PUT /api/knowledge/{id}`
- **Headers**: `Content-Type: application/json`
- **Request Body**: Same schema as Create.
- **cURL Example**:
  ```bash
  curl -X PUT http://127.0.0.1:8000/api/knowledge/1 \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"title": "Updated Title", "content": "Updated content goes here..."}'
  ```

#### Delete Chunk
- **Method & Path**: `DELETE /api/knowledge/{id}`
- **Response**: `{ "data": { "success": true } }`
- **cURL Example**:
  ```bash
  curl -X DELETE http://127.0.0.1:8000/api/knowledge/1 \
    -H "Accept: application/json"
  ```

---

### 2. Test Console Query

#### Execute Query & Diagnose
- **Method & Path**: `POST /api/console/query`
- **Headers**: `Content-Type: application/json`
- **Request Body**:
  ```json
  {
    "prompt_version_id": 2,
    "query": "What are the shipping timelines?",
    "provider": "mock"
  }
  ```
- **Response**: Returns the generated assistant message and diagnostic report.
- **cURL Example**:
  ```bash
  curl -X POST http://127.0.0.1:8000/api/console/query \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"prompt_version_id": 2, "query": "How do I reset my password?", "provider": "mock"}'
  ```

---

### 3. Replay & Prompt Comparative Analysis

#### Show Replay Timeline
- **Method & Path**: `GET /api/messages/{id}/replay`
- **Response**: Returns message content, retrieved context chunks, system prompt, diagnostics, and human reviews.
- **cURL Example**:
  ```bash
  curl -X GET http://127.0.0.1:8000/api/messages/12/replay \
    -H "Accept: application/json"
  ```

#### Comparative Replay with Fixed Prompt
- **Method & Path**: `POST /api/messages/{id}/replay`
- **Headers**: `Content-Type: application/json`
- **Request Body**:
  ```json
  {
    "prompt_version_id": 2
  }
  ```
- **Response**: Re-runs the user's original query against the fixed system prompt. Returns the new response timeline.
- **cURL Example**:
  ```bash
  curl -X POST http://127.0.0.1:8000/api/messages/12/replay \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"prompt_version_id": 2}'
  ```

---

### 4. Human Audit & Evaluation

#### Submit Evaluation Signal
- **Method & Path**: `POST /api/messages/{id}/evaluation`
- **Headers**: `Content-Type: application/json`
- **Request Body**:
  ```json
  {
    "reviewer_name": "Lead Auditor",
    "rating": 1,
    "flag": "hallucination",
    "notes": "The AI completely fabricated a response without context."
  }
  ```
- **Response**: Returns the saved evaluation details.
- **cURL Example**:
  ```bash
  curl -X POST http://127.0.0.1:8000/api/messages/12/evaluation \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"reviewer_name": "Lead Auditor", "rating": 1, "flag": "hallucination", "notes": "We do not offer international refunds."}'
  ```

---

### 5. Health, Gaps, and Doctor Analytics

#### Retrieve Health Score
- **Method & Path**: `GET /api/health-score`
- **Parameters**: `lookback_days` (optional, default = 7)
- **Response**: Overall composite health score and subscore breakdowns.
- **cURL Example**:
  ```bash
  curl -X GET "http://127.0.0.1:8000/api/health-score?lookback_days=7" \
    -H "Accept: application/json"
  ```

#### Retrieve Doctor Cases
- **Method & Path**: `GET /api/doctor/cases`
- **Response**: Paginated list of recent failed/anomaly diagnostics.
- **cURL Example**:
  ```bash
  curl -X GET http://127.0.0.1:8000/api/doctor/cases \
    -H "Accept: application/json"
  ```

#### Retrieve Knowledge Gaps
- **Method & Path**: `GET /api/knowledge-gaps`
- **Response**: Ranked list of unmatched query terms.
- **cURL Example**:
  ```bash
  curl -X GET http://127.0.0.1:8000/api/knowledge-gaps \
    -H "Accept: application/json"
  ```
