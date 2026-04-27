# Development Environment

## Operating System

Development is being performed on:

- Windows 11
- WSL2 (Ubuntu Linux distribution)

---

## Runtime Environment

### Backend
- PHP 8.2+
- Laravel (latest compatible version)
- Laravel Sanctum for authentication
- SQLite as primary database (local development)

---

### CLI Tool
- PHP CLI application
- Uses Guzzle for HTTP requests
- Globally installed command:

```
insighta
```

---

### Web Portal
- React (TypeScript)
- Consumes REST API backend
- Uses HTTP-only cookies for authentication
- CSRF protection enabled

---

## Database

- SQLite (development)
- File-based database stored in project directory
- No external DB server required

---

## Required CLI Behavior (Development Context)

When running CLI commands:
- Must work inside WSL Ubuntu terminal
- Must be globally accessible from any directory
- Credentials stored at:

```
~/.insighta/credentials.json

```



---

## API Communication

All clients (CLI + Web) communicate with:
- Laravel backend REST API
- Authentication handled via Sanctum + OAuth flow

---

## Important Notes

- Use Linux-compatible paths inside WSL
- Ensure file permissions allow CLI credential storage
- Use environment variables for all secrets
- Do not hardcode API URLs