# SaaS Android Video Control Platform – Functional & Technical Specification

## 1. Overview

This SaaS product enables companies to **centrally control, schedule, monitor, and report video playback** on Android-based devices (Android TV, phones, tablets) via a managed application.

The system is designed for **digital signage, internal communications, advertising, and announcements**, supporting both **uploaded videos** and **YouTube streams**.

The platform consists of:

* **Back Office (Web Admin Panel)** for companies
* **Android Client App** installed on managed devices
* **Backend & APIs** for orchestration, streaming, monitoring, and reporting

---

## 2. Key Objectives

* Centralized control of video content across distributed devices
* Real-time and scheduled video playback
* Device-level targeting (location, type, branch)
* Playback monitoring and proof-of-play reporting
* Scalable multi-tenant SaaS architecture

---

## 3. User Roles & Permissions

### 3.1 Platform Roles

**Super Admin (SaaS Owner)**

* Manage companies (tenants)
* Billing & subscription management
* Global settings
* System-wide analytics

**Company Admin**

* Manage branches & devices
* Upload and manage videos
* Create schedules & campaigns
* View reports
* Manage company users

**Company Operator (Optional)**

* Limited access (e.g., scheduling & monitoring only)

---

## 4. Multi-Tenant Structure

```
SaaS Platform
 └── Company (Tenant)
     └── Branch
         └── Device Group (Optional)
             └── Device (Android TV / Phone / Tab)
```

### Entities

* **Company**: Independent tenant
* **Branch**: Physical or logical location
* **Device**: Registered Android device
* **Device Group**: Logical grouping (e.g., "Lobby TVs")

---

## 5. Back Office (Web Admin Panel)

### 5.1 Authentication & Security

* Email/password login
* Optional SSO (future)
* Role-based access control (RBAC)
* JWT / OAuth 2.0

---

### 5.2 Dashboard

* Online vs Offline devices
* Currently playing content per device
* Upcoming schedules
* Alerts (offline device, failed playback)
* Quick actions (Play now, Stop, Restart)

---

### 5.3 Video Management

#### Supported Content

* Uploaded videos (MP4, WebM – configurable)
* YouTube videos (URL or playlist)

#### Features

* Upload videos
* Automatic transcoding (backend)
* Thumbnail generation
* Duration & metadata extraction
* Categorization & tags
* Versioning (optional)

---

### 5.4 Device Management

* Device registration via **pairing code / QR code**
* Assign device to branch & group
* Device metadata:

  * Device type (TV / Phone / Tablet)
  * OS version
  * Location (manual or GPS-based)
* Device status:

  * Online / Offline
  * Last heartbeat
  * Current playback

---

### 5.5 Playback Control

#### Instant Play

* Play a video immediately on:

  * Specific device
  * Device group
  * Branch
  * Entire company

#### Control Actions

* Play
* Pause
* Stop
* Restart
* Volume control (if supported)

---

### 5.6 Scheduling & Campaigns

#### Scheduling Types

* One-time
* Recurring (daily, weekly, custom)
* Time-window based

#### Campaign Rules

* Target devices by:

  * Branch
  * Device group
  * Device type
  * Location
* Priority handling (override existing content)

#### Playlist Support

* Sequential
* Loop
* Weighted random

---

### 5.7 Reporting & Analytics

#### Playback Reports

* Proof-of-play (video played, duration, timestamp)
* Device-wise reports
* Branch-wise reports
* Campaign-wise reports

#### Metrics

* Total play count
* Total watch time
* Missed schedules
* Offline duration

#### Export

* CSV / PDF
* API access

---

### 5.8 Notifications & Alerts

* Device offline alerts
* Playback failure alerts
* Email / In-app notifications

---

## 6. Android Client App

### 6.1 Supported Devices

* Android TV
* Android Phone
* Android Tablet

---

### 6.2 Device Registration

* App generates a unique Device ID
* User enters pairing code from admin panel
* Device becomes linked to company & branch

---

### 6.3 Playback Engine

* Fullscreen playback
* Auto-start on boot (kiosk mode)
* Local caching of videos
* Fallback content when offline

#### YouTube Playback

* Embedded player
* Controlled via backend commands

---

### 6.4 Communication with Backend

* WebSocket / MQTT for real-time commands
* REST APIs for sync
* Heartbeat every X seconds

---

### 6.5 Offline Behavior

* Cached schedules & videos
* Resume playback when connection restores
* Offline event logging

---

### 6.6 Monitoring & Logging

* Playback started / completed
* Errors (decode failure, network)
* System health (CPU, memory – optional)

---

## 7. Backend System Architecture

### 7.1 Core Services

* **Auth Service** – authentication & RBAC
* **Tenant Service** – company & branch management
* **Content Service** – video storage & metadata
* **Scheduler Service** – time-based orchestration
* **Device Control Service** – real-time commands
* **Reporting Service** – analytics & logs

---

### 7.2 Communication Flow

```
Admin Panel → Backend API → Command Queue → Android App
Android App → Playback Logs → Backend → Reports
```

---

### 7.3 Storage

* Video storage: S3-compatible object storage
* Database:

  * Relational (PostgreSQL/MySQL) for metadata
  * Time-series / NoSQL for logs
* Cache: Redis

---

### 7.4 Scalability & Reliability

* Horizontal scaling
* Multi-region support (future)
* CDN for video delivery
* Retry & fallback mechanisms

---

## 8. Security Considerations

* Tenant data isolation
* Signed playback URLs
* Encrypted device communication
* App tamper protection
* Rate limiting & audit logs

---

## 9. Subscription & Billing (Optional Phase)

* Plans based on:

  * Number of devices
  * Storage
  * Features (scheduling, reports)
* Monthly / yearly billing

---

## 10. Future Enhancements

* Live stream support
* AI-based content recommendations
* Face detection & audience analytics
* Remote screenshots
* iOS support

---

## 11. MVP Scope Recommendation

**Phase 1 (MVP)**

* Company, branch, device management
* Video upload & YouTube playback
* Instant play & basic scheduling
* Android TV focus
* Basic playback reporting

**Phase 2**

* Advanced analytics
* Device groups
* Billing
* Offline-first improvements

---

## 12. Non-Functional Requirements

* 99.9% uptime target
* <2s command propagation
* GDPR-compliant data handling
* Modular, API-first design

---

## 13. Success Criteria

* Devices reliably play assigned content
* Accurate proof-of-play reports
* Easy onboarding for companies
* Scalable to thousands of devices

---

**End of Specification**
