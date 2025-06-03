# ThinkHuge Hiring Test – Custom PHP MVC System

> This project is intentionally over-engineered for its scope.  
> I built it this way to demonstrate my skills and understanding of modern PHP development using a clean MVC architecture.  
> I know the goal of the task is to evaluate PHP knowledge—not to create a production-ready system—but I wanted to show how I’d structure a real-world app.

---

## Overview

This is a simple accounting app for managing clients and tracking income/expenses.  
It includes an admin authentication system, CRUD for clients and transactions, a reporting section, and a documented API.

---

## Key Features

- Custom lightweight MVC (no frameworks)
- Basic routing system with support for middlewares
- Middleware implementation:
  - Auth (protects internal routes)
  - ApiAuth (protects API)
  - CSRF protection
  - Rate limiting (for API)
- Full validation system (required, min, max, regex, email, etc.)
- Flash messaging and redirect utilities
- Authentication with session tracking
- Soft delete support
- Paginated reports with date range filtering
- Secure API with key-based access
- API documentation included

---

## Database Structure

- The schema uses proper foreign keys and indexes for performance and data integrity
- All relationships use ON DELETE CASCADE
- Tables:
  - `users`: unique email + unique API key
  - `clients`: belongs to `users`
  - `transactions`: belongs to `clients` and `users`
  - `sessions`: tracks user login sessions across devices

---

## Installation & Setup

1. Clone the repo
2. Open `config/config.php` and enter your database credentials
3. Open the app in your browser

The system will automatically install the database and populate it with test data.

You can use the following test account:

```
Email: ismail@gmail.com  
Password: 123456Aa.
```

You can also create a new account and start fresh with your own clients and transactions.

---

## API Access

There’s a full API built into the system.  
You can access the API documentation and key from the “API” page once logged in.  
All endpoints are secured and rate-limited.

---

## Live Demo

A hosted version is available here for testing:

https://postiw.site/public/

---

## Code Organization

```
/app
  /Controllers
  /Models
  /Middlewares
/core
/views
/public
/config
```

This structure separates concerns clearly between core logic, HTTP handling, and rendering.

---

## Final Notes

While this may seem like overkill for a CRUD test, I wanted to demonstrate how I approach architecture, separation of concerns, validation, and security when building systems in PHP.

Feel free to explore the source and test all parts of the application.
