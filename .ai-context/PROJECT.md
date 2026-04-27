# Insighta Labs+

## Overview

Insighta Labs+ is a backend-driven Profile Intelligence Platform built as part of the HNG Internship Backend Engineering Track.

It extends a previously built Profile Intelligence System (Stage 1 + Stage 2) into a secure, production-grade, multi-interface platform.

The system is designed for multiple user types:
- Analysts (read-only users)
- Engineers (advanced query users)
- Admins (full system control)

---

## Core Objective

Transform a functional data system into a secure, authenticated, role-based platform with multiple access interfaces:

- REST API (primary system)
- CLI tool (developer interface)
- Web portal (non-technical interface)

---

## System Capabilities

### 1. Profile Intelligence System (Core Domain)
- Create profiles using external APIs:
  - Genderize
  - Agify
  - Nationalize
- Store structured demographic data
- Prevent duplicate profile creation (idempotency)

---

### 2. Advanced Query Engine (Stage 2)
- Filtering:
  - gender
  - age_group
  - country_id
  - age ranges
  - probability thresholds
- Sorting:
  - age
  - created_at
  - gender_probability
- Pagination with metadata and navigation links
- Natural language search (rule-based parsing)

---

### 3. Authentication System (Stage 3)
- GitHub OAuth with PKCE
- Sanctum-based token authentication
- Access tokens (3 min expiry)
- Refresh tokens (5 min expiry, rotation required)

---

### 4. Authorization (RBAC)
- Roles:
  - admin (full access)
  - analyst (read-only)
- Middleware-based enforcement across all endpoints

---

### 5. Interfaces
- REST API (primary interface)
- CLI tool (global command: `insighta`)
- Web portal (cookie-based session auth)

---

## API Requirements
- All requests must include:
  - `X-API-Version: 1`
- All endpoints require authentication
- Consistent error format:
```json
{ "status": "error", "message": "..." }