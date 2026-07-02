# Lumen REST API Reference

**Version:** `v1.0.0` | **Base URL:** `http://127.0.0.1:8000/api` | **Content-Type:** `application/json`

Lumen provides a robust, thin JSON REST API layer over its core observability services. All responses use standardized envelopes (`{ "data": ... }` or `{ "error": ... }`).

---

## Endpoint Summary

| Method | Endpoint | Description |
| :--- | :--- | :--- |
| **GET** | `/api/knowledge` | List all knowledge base chunks. |
| **POST** | `/api/knowledge` | Create a new knowledge chunk. |
| **PUT** | `/api/knowledge/{id}` | Update an existing knowledge chunk. |
| **DELETE**| `/api/knowledge/{id}` | Delete a knowledge chunk. |
| **POST** | `/api/console/query` | Execute a test query against the RAG system and retrieve diagnostics. |
| **GET** | `/api/messages/{id}/replay` | Retrieve the trace timeline and diagnostics for a specific message. |
| **POST** | `/api/messages/{id}/replay` | Run a comparative replay for a historical message trace. |
| **POST** | `/api/messages/{id}/evaluation`| Submit a human review and evaluation signal for a trace. |
| **GET** | `/api/health-score` | Retrieve the platform's aggregate health score and metrics. |
| **GET** | `/api/doctor/cases` | Retrieve paginated diagnostic anomaly cases for review. |
| **GET** | `/api/knowledge-gaps` | Retrieve a ranked list of unmatched query terms (missing context). |

---

## 1. Knowledge Base CRUD

**`GET /api/knowledge`** (List Chunks)
*   **Returns:** Array of vectorized document chunks acting as ground-truth for RAG.

**`POST /api/knowledge`** (Create Chunk)
*   **Body:** `{"title": "Policy", "content": "Our policy is...", "tags": ["policy"]}`
*   **Validation:** `title` and `content` are required strings.
*   **Returns:** `201 Created` with the new chunk resource.

**`PUT /api/knowledge/{id}`** (Update Chunk)
*   **Body:** `{"title": "Updated Title", "content": "Updated content..."}`
*   **Returns:** `200 OK` with the updated chunk resource.

**`DELETE /api/knowledge/{id}`** (Delete Chunk)
*   **Returns:** `200 OK` with `{"data": {"success": true}}`. Removes context from the vector store.

---

## 2. Diagnostics & RAG Execution

**`POST /api/console/query`** (Execute Test Query)
*   **Description:** Simulates an LLM completion, fetches context, runs diagnostics, and returns health metrics.
*   **Body:** `{"prompt_version_id": 2, "query": "How do I reset?", "provider": "mock"}`
*   **Validation:** `prompt_version_id`, `query`, and `provider` are required.
*   **Returns:** AI response text and diagnostic metrics (`groundedness`, `root_cause`).

---

## 3. Replay & Prompt Comparative Analysis

**`GET /api/messages/{id}/replay`** (Show Replay Timeline)
*   **Returns:** Complete trace timeline including context chunks, prompt version, AI output, and diagnostic scoring for a historical message.

**`POST /api/messages/{id}/replay`** (Comparative Replay)
*   **Description:** Re-runs a historical failed query against a different `prompt_version_id` to compare outputs.
*   **Body:** `{"prompt_version_id": 3}`
*   **Returns:** The newly generated response and updated diagnostics without overwriting the original trace.

---

## 4. Human Audit & Evaluation

**`POST /api/messages/{id}/evaluation`** (Submit Evaluation)
*   **Description:** Attaches a human reviewer rating and flag to a trace to assist in dataset alignment.
*   **Body:** `{"reviewer_name": "Auditor", "rating": 1, "flag": "hallucination", "notes": "Fabricated."}`
*   **Validation:** `reviewer_name`, `rating`, and `flag` are required.

---

## 5. Analytics & Aggregation

**`GET /api/health-score`** (Retrieve Health Score)
*   **Query Params:** `lookback_days` (Optional, defaults to 7).
*   **Returns:** Global platform anomaly metrics and composite health score (`score`, `total_queries`, `breakdown`).

**`GET /api/doctor/cases`** (Retrieve Doctor Cases)
*   **Returns:** Paginated trace logs flagged with anomalies (e.g., `knowledge_gap`, `hallucination`) for triage.

**`GET /api/knowledge-gaps`** (Retrieve Knowledge Gaps)
*   **Returns:** Ranked frequency list of search keywords that historically returned 0 matching context chunks.

---

## Error Codes

| Code | Type | Description |
| :--- | :--- | :--- |
| `400` | `BAD_REQUEST` | Malformed request or missing configuration. |
| `404` | `NOT_FOUND` | Requested resource ID was not found. |
| `422` | `VALIDATION_ERROR` | Request payload failed semantic or type validation. |
| `500` | `INTERNAL_SERVER_ERROR`| Critical unhandled system failure. |

*(Note: JWT Authentication, Pagination, RBAC, and Webhooks are slated for future releases.)*
