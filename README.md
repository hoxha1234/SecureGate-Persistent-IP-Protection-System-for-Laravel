# SecureGate: Persistent IP-Based Security & Access Control

**SecureGate** is a high-level security implementation for Laravel designed to prevent **Brute-Force attacks** and **Credential Stuffing**. Unlike standard rate-limiting that relies on volatile cache, this system uses **Stateful Persistence** to ensure security remains active even after server reboots.



## 🛡️ Engineering Overview

The architecture follows a **Defense-in-Depth** strategy, structured through four main engineering layers:

### 1. Stateful Rate Limiting
The system tracks failed login attempts directly in the database (`security_blacklist` table). This provides persistent monitoring of malicious actors across different sessions and server states.

### 2. Adaptive Blocking Logic
The security engine implements an **Escalation Policy**:
* **Threshold:** 5 failed attempts.
* **Action:** Automatic state change to `is_blocked = true`.
* **Duration:** 1-hour cooling period via `blocked_until` timestamp.

### 3. Middleware Interceptor (SecureGate)
Acting as an **Application-Level Firewall (WAF)**, the middleware intercepts requests before they reach the application logic. If an IP is flagged, the system returns a custom `403 Forbidden` view, saving server resources.

### 4. Legacy Hash Migration (Bcrypt Re-hashing)
An integrated **Silent Migration** tool. Upon a successful login, the system detects if the user is using a legacy plain-text password and automatically upgrades it to a secure `Bcrypt` hash without interrupting the user experience.

---

## 🛠️ Components

| Component | Responsibility |
| :--- | :--- |
| **SecureGate Middleware** | Intercepts blocked IPs and renders the lockout view. |
| **SecurityController** | Provides API/Internal methods for manual IP unlocking and log management. |
| **LoginController** | Handles the authentication logic and increments threat counters. |
| **Security Monitor (Blade)** | A real-time administrative dashboard to track and block suspicious IPs. |

---

## 🚀 Installation & Setup

### 1. Database Schema
Ensure your `security_blacklist` table is configured:
```sql
CREATE TABLE security_blacklist (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    ip_address VARCHAR(45) UNIQUE,
    failed_attempts INT DEFAULT 0,
    is_blocked BOOLEAN DEFAULT FALSE,
    blocked_until TIMESTAMP NULL,
    last_attack_detected TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
