# Architectural Decisions & Assumptions

This document lists the engineering assumptions, design decisions, and system constraints applied during the development of the Lumen AI Engineering Command Center backend.

---

## 1. Core Architectural Decisions

### Unified Business Service Layer
In alignment with the non-negotiable guidelines, **all business logic resides strictly inside the services namespace** (`app/Services`). Livewire components and JSON REST API controllers are thin adapters that delegate execution directly to the service classes. This ensures that diagnostics calculations, scoring, and knowledge gap detection behave identically whether called from the UI console or the REST API.

### Database Portability & Compatibility
- **Supabase Postgres**: In production, Supabase Postgres is targeted. Schema migrations enforce foreign keys, unique indices, and Postgres-native `jsonb` fields. In `config/database.php`, `sslmode=require` is supported.
- **SQLite Support**: During local environment bootstrapping or continuous integration testing, SQLite is supported as a fallback driver. Column declarations like `$table->jsonb(...)` compile safely as `text` fields on SQLite, permitting database-agnostic testing using local files (`database/database.sqlite`).

### OpenRouter & Mock Fallbacks
- The `OpenRouterProvider` calls the public OpenRouter API. If the API key is missing or the endpoint throws network errors (timeouts, DNS resolution, rate limits), it **gracefully falls back to the `MockProvider`**.
- This fallback ensures that console queries and replay screens never hard-crash during live audits.

---

## 2. Diagnostics Heuristics & Audit Loops

### Groundedness & Relevance Average
- **Keyword overlap Jaccard logic**: The retrieval relevance and groundedness scores are calculated by tokenizing query/response text (lowercasing, cleaning punctuation, removing standard stopwords) and checking the percentage of intersection with reference context terms.
- **Diagnostics recalculation on review**: When a human reviewer evaluates a response (e.g. submitting a review rating or flagging an outcome as `incorrect`), the system re-runs the diagnostics engine on that message. If marked as `incorrect`, the classifier changes the diagnosis from `healthy` (or other states) to `prompt_instruction_issue` to highlight the feedback loop.

### Comparative Replay Integrity
- Since a `Diagnostics` row requires a foreign key relation to a persisted `Message` model (with unique constraints), running a "Replay with fixed prompt" creates a new comparison conversation in the database. This maintains full referential integrity and allows users to review the comparative analytics at any point in the future.

---

## 3. Explicit Non-Goals (Not Implemented)
Per system requirements, the following features are noted as future enhancements and are not built:
- **Authentication & User Management**: The system runs under a single implicit administrator.
- **Embeddings & Vector Databases**: Text retrieval is based on keyword overlap (TF-IDF equivalent Jaccard distance) rather than semantic embeddings.
- **SSO & Multi-Tenant Support**: Only a single tenant workspace is supported.
- **Real-Time Alerts / Notifications**: No email, Slack hooks, or browser notifications are triggered on quality drops.
- **Billing & Subscriptions**: No billing limits or subscription tiers are configured.
