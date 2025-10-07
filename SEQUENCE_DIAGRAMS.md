# 📊 CCTV Dashboard - Sequence Diagrams

**Technology Stack:**

- **Backend**: Laravel 10+ (PHP 8.2+)
- **Frontend**: Laravel Blade Templates
- **Interactivity**: Alpine.js
- **Real-time**: Laravel Echo + Pusher/WebSockets
- **API**: Laravel API Resources
- **Queue**: Laravel Queue (Database)

## 🔄 Key Workflow Sequence Diagrams

### **1. Company Group Management Workflow (Admin Only)**

```
┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐
│   Admin     │  │   Blade     │  │  Laravel    │  │  Database   │  │ WebSocket   │
│   User      │  │ Template    │  │ Controller  │  │             │  │   Server    │
└─────────────┘  └─────────────┘  └─────────────┘  └─────────────┘  └─────────────┘
       │                │                │                │                │
       │ 1. Group Management Request     │                │                │
       │ (Create/Update/Delete Group)    │                │                │
       ├───────────────►│                │                │                │
       │                │                │                │                │
       │                │ 2. POST/PUT/DELETE /groups     │                │
       │                │ Form Data: {province_code,     │                │
       │                │  province_name, group_name,    │                │
       │                │  address, status}              │                │
       │                ├───────────────────────────────►│                │
       │                │                │                │                │
       │                │                │ 3. Validate Admin Access        │
       │                │                │ - Check admin role              │
       │                │                │ - Verify permissions            │
       │                │                │ - Log admin action              │
       │                │                ├───────────────►│                │
       │                │                │                │                │
       │                │                │ 4. Group CRUD Operations        │
       │                │                │ - Validate province_code unique │
       │                │                │ - Check for associated branches  │
       │                │                │ - Save to company_groups         │
       │                │                │ - Update timestamps             │
       │                │                ├───────────────►│                │
       │                │                │                │                │
       │                │                │ 5. Branch Association           │
       │                │                │ - View associated branches       │
       │                │                │ - Add/remove branches           │
       │                │                │ - Update branch-group relations  │
       │                │                ├───────────────►│                │
       │                │                │                │                │
       │                │                │ 6. Group Validation             │
       │                │                │ - Verify province code unique   │
       │                │                │ - Check branch associations     │
       │                │                │ - Validate contact information   │
       │                │                ├───────────────►│                │
       │                │                │                │                │
       │                │                │ 7. Group Response               │
       │                │                │ - Group ID returned             │
       │                │                │ - Associated branches count     │
       │                │                │ - Status confirmation           │
       │                │                │◄───────────────┤                │
       │                │                │                │                │
       │                │ 8. Return to Blade View         │                │
       │                │ - Redirect to groups.index      │                │
       │                │ - With success message          │                │
       │                │ - Flash session data            │                │
       │                │◄───────────────┤                │                │
       │                │                │                │                │
       │                │ 9. Broadcast Group Change       │                │
       │                │ - WebSocket: group_updated      │                │
       │                │ - Data: {group_id, status}      │                │
       │                ├───────────────────────────────►│                │
       │                │                │                │                │
       │                │                │                │ 10. Notify All Clients│
       │                │                │                │ - Group change event │
       │                │                │                │ - Update UI components│
       │                │                │                ├─────────────────►│
       │                │                │                │                │
       │                │ 11. Render Blade View           │                │                │
       │                │ - Load groups.index.blade.php   │                │                │
       │                │ - Display success toast         │                │                │
       │                │ - Alpine.js updates list        │                │                │
       │                │◄─────────────────────────────────────────────────┤                │
```

### **2. Person Detection & Re-Identification Processing**

```
┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐
│   Device    │  │   System    │  │  Database   │  │  WhatsApp   │  │   Client    │
│  (Camera)   │  │   API       │  │             │  │  Provider   │  │ Dashboard   │
└─────────────┘  └─────────────┘  └─────────────┘  └─────────────┘  └─────────────┘
       │                │                │                │                │
       │ 1. Person Detection                            │                │
       │ (re_id, confidence, bbox)      │                │                │
       ├───────────────►│                │                │                │
       │                │                │                │                │
       │                │ 2. POST /api/detection/log     │                │
       │                │ {re_id, branch_id, device_id,  │                │
       │                │  detected_count, detection_data}                │
       │                ├───────────────────────────────►│                │
       │                │                │                │                │
       │                │                │ 3. Process Re-ID (Person)       │
       │                │                │ - Check re_id_master            │
       │                │                │ - Create if not exists          │
       │                │                │ - Update appearance_features    │
       │                │                │ - Update timestamps             │
       │                │                ├───────────────►│                │
       │                │                │                │                │
       │                │                │ 4. Log Detection                │
       │                │                │ - re_id_branch_detection        │
       │                │                │ - Multiple records per day OK   │
       │                │                │ - Save detection_timestamp      │
       │                │                │ - Save detection_data (JSON)    │
       │                │                ├───────────────►│                │
       │                │                │                │                │
       │                │                │ 5. Check Event Settings         │
       │                │                │ - branch_event_settings         │
       │                │                │ - WHERE branch_id + device_id   │
       │                │                │ - Get whatsapp_enabled (bool)   │
       │                │                ├───────────────►│                │
       │                │                │                │                │
       │                │                │ 6. Create Event Log             │
       │                │                │ - event_logs table              │
       │                │                │ - branch_id, device_id, re_id   │
       │                │                │ - Set notification flags        │
       │                │                ├───────────────►│                │
       │                │                │                │                │
       │                │                │ 7. Send WhatsApp (if enabled)   │
       │                │                │ - Get whatsapp_numbers (JSON)   │
       │                │                │ - Format message template       │
       │                │                │ - Call provider API             │
       │                │                ├─────────────────────────────────►│
       │                │                │                │                │
       │                │                │ 8. Provider Response            │
       │                │                │ - Fire & forget (no tracking)   │
       │                │                │◄─────────────────────────────────┤
       │                │                │                │                │
       │                │                │ 9. Update Flags                 │
       │                │                │ - notification_sent = true      │
       │                │                │ - image_sent = true/false       │
       │                │                ├───────────────►│                │
       │                │                │                │                │
       │                │ 10. Response   │                │                │
       │                │ {success: true, data: {         │                │
       │                │  re_id, branch_count: 1,        │                │
       │                │  total_detection_count}}        │                │
       │                │◄───────────────┤                │                │
       │                │                │                │                │
       │                │ 11. WebSocket Update            │                │
       │                │ - Real-time dashboard update    │                │
       │                │ - Person tracking update        │                │
       │                ├─────────────────────────────────────────────────►│
```

### **3. CCTV Stream Request & Display**

```
┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐
│   User      │  │   Blade     │  │  Laravel    │  │  Database   │  │  Stream     │
│ Browser     │  │ Template    │  │ Controller  │  │             │  │  Server     │
└─────────────┘  └─────────────┘  └─────────────┘  └─────────────┘  └─────────────┘
       │                │                │                │                │
       │ 1. Select Stream                │                │                │
       │ (Position 1, Branch A)          │                │                │
       ├───────────────►│                │                │                │
       │                │                │                │                │
       │                │ 2. GET /api/stream/branch/1    │                │
       │                │ ?position=1                    │                │
       │                ├───────────────────────────────►│                │
       │                │                │                │                │
       │                │                │ 3. Query Streams                │
       │                │                │ - cctv_streams                  │
       │                │                │ - WHERE branch_id=1, position=1 │
       │                │                ├───────────────►│                │
       │                │                │                │                │
       │                │                │ 4. Stream Config                │
       │                │                │ {stream_url, credentials,       │
       │                │                │  resolution, fps}               │
       │                │                │◄───────────────┤                │
       │                │                │                │                │
       │                │                │ 5. Validate & Decrypt           │
       │                │                │ - Check user permissions        │
       │                │                │ - Decrypt stream credentials    │
       │                │                │ - Build authenticated URL       │
       │                │                ├───────────────►│                │
       │                │                │                │                │
       │                │ 6. Stream Response              │                │
       │                │ {stream_url, status, quality}   │                │
       │                │◄───────────────┤                │                │
       │                │                │                │                │
       │                │ 7. Initialize Video Player      │                │
       │                │ - Alpine.js initializes player  │                │
       │                │ - Load stream URL               │                │
       │                │ - Set video element source      │                │
       │                ├─────────────────────────────────┤                │
       │                │                │                │                │
       │                │ 8. Connect to Stream            │                │
       │                │ - WebRTC/RTSP/HLS connection    │                │
       │                ├─────────────────────────────────────────────────►│
       │                │                │                │                │
       │                │ 9. Stream Data                  │                │
       │                │ - Video frames                  │                │
       │                │ - Audio (if available)          │                │
       │                │◄─────────────────────────────────────────────────┤
       │                │                │                │                │
       │                │ 10. Display Stream              │                │
       │                │ - Render video in grid          │                │
       │                │ - Show stream info              │                │
       │                ├─────────────────────────────────┤                │
       │                │                │                │                │
       │                │                │ 11. Health Check (Periodic)     │
       │                │                │ - Ping stream endpoint          │
       │                │                │ - Update stream status          │
       │                │                ├─────────────────────────────────►│
       │                │                │                │                │
       │                │ 12. Status Updates              │                │
       │                │ - Stream quality indicators     │                │
       │                │ - Connection status             │                │
       │                │◄───────────────┤                │                │
```

### **4. API Credential Creation & Usage**

```
┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐
│   Admin     │  │   Blade     │  │  Laravel    │  │  Database   │  │  External   │
│   User      │  │ Template    │  │ Controller  │  │             │  │   Client    │
└─────────────┘  └─────────────┘  └─────────────┘  └─────────────┘  └─────────────┘
       │                │                │                │                │
       │ 1. Create API Key               │                │                │
       │ (Branch: Jakarta, Permissions)  │                │                │
       ├───────────────►│                │                │                │
       │                │                │                │                │
       │                │ 2. POST /api/credentials        │                │
       │                │ Form: {name, branch_id,        │                │
       │                │  permissions, rate_limit,      │                │
       │                │  expires_at}                   │                │
       │                ├───────────────────────────────►│                │
       │                │                │                │                │
       │                │                │ 3. Validate Input               │
       │                │                │ - Check admin permissions       │
       │                │                │ - Validate branch access        │
       │                │                │ - Verify permission format      │
       │                │                ├───────────────►│                │
       │                │                │                │                │
       │                │                │ 4. Generate Credentials         │
       │                │                │ - Generate unique API key       │
       │                │                │ - Generate API secret           │
       │                │                │ - Encrypt secret                │
       │                │                ├───────────────►│                │
       │                │                │                │                │
       │                │                │ 5. Save to Database             │
       │                │                │ - api_credentials table         │
       │                │                │ - Set created_by                │
       │                │                ├───────────────►│                │
       │                │                │                │                │
       │                │                │ 6. Success Response             │
       │                │                │ {api_key, api_secret, id}       │
       │                │                │◄───────────────┤                │
       │                │                │                │                │
       │                │ 7. Render Blade View            │                │
       │                │ - Display API key (one-time)    │                │
       │                │ - Display secret (one-time)     │                │
       │                │ - Alpine.js copy to clipboard   │                │
       │                │◄───────────────┤                │                │
       │                │                │                │                │
       │                │                │                │                │
       │                │                │ 8. External API Request         │
       │                │                │ GET /api/counting/summary       │
       │                │                │ Headers: X-API-Key: xxx         │
       │                │                ├─────────────────────────────────►│
       │                │                │                │                │
       │                │                │ 9. Authenticate Request         │
       │                │                │ - Validate API key              │
       │                │                │ - Check permissions             │
       │                │                │ - Verify rate limits            │
       │                │                ├───────────────►│                │
       │                │                │                │                │
       │                │                │ 10. Log Request                 │
       │                │                │ - api_request_logs              │
       │                │                │ - Update last_used_at           │
       │                │                ├───────────────►│                │
       │                │                │                │                │
       │                │                │ 11. Process Request             │
       │                │                │ - Query counting data           │
       │                │                │ - Generate response             │
       │                │                ├───────────────►│                │
       │                │                │                │                │
       │                │                │ 12. API Response                │
       │                │                │ {success: true, data: {...}}    │
       │                │                │◄─────────────────────────────────┤
       │                │                │                │                │
       │                │                │ 13. Update Usage Stats          │
       │                │                │ - Increment request count       │
       │                │                │ - Update response time          │
       │                │                ├───────────────►│                │
```

### **5. Report Generation Workflow**

```
┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐
│   User      │  │   Blade     │  │  Laravel    │  │  Database   │
│             │  │ Template    │  │ Controller  │  │ (PostgreSQL)│
└─────────────┘  └─────────────┘  └─────────────┘  └─────────────┘
       │                │                │
       │ 1. Request Report               │                │
       │ (Daily, Branch Jakarta)         │                │
       ├───────────────►│                │                │
       │                │                │                │
       │                │ 2. GET /reports/generate        │                │
       │                │ Query: {type: "daily",         │                │
       │                │  date: "2024-01-16",           │                │
       │                │  branch_id: 1, format: "pdf"}  │                │
       │                ├───────────────────────────────►│                │
       │                │                │                │
       │                │                │ 3. Check Cached Report          │
       │                │                │ - counting_reports table        │
       │                │                │ - WHERE report_type, date,      │
       │                │                │   branch_id                     │
       │                │                ├───────────────►│                │
       │                │                │                │
       │                │                │ 4. Report Found/Not Found       │
       │                │                │ - Existing: Return cached       │
       │                │                │ - Not exists: Generate new      │
       │                │                │◄───────────────┤                │
       │                │                │                │
       │                │                │ 5. Query Raw Data (if needed)   │
       │                │                │ - re_id_branch_detection        │
       │                │                │ - event_logs                    │
       │                │                │ - device_master                 │
       │                │                │ - company_branches              │
       │                │                ├───────────────►│                │
       │                │                │                │
       │                │                │ 6. Calculate Statistics         │
       │                │                │ - Total detections              │
       │                │                │ - Total events                  │
       │                │                │ - Unique persons (Re-ID)        │
       │                │                │ - Performance metrics           │
       │                │                │◄───────────────┤                │
       │                │                │                │
       │                │                │ 7. Save Report                  │
       │                │                │ - Save to counting_reports      │
       │                │                │ - Set generated_at              │
       │                │                ├───────────────►│                │
       │                │                │                │
       │                │                │ 8. Generate Response            │
       │                │                │ - Format data                   │
       │                │                │ - Prepare charts data           │
       │                │                │◄───────────────┤                │
       │                │                │                │
       │                │ 9. Return Blade View/PDF        │                │
       │                │ - Render reports.show.blade     │                │
       │                │ - Or download PDF file          │                │
       │                │◄───────────────┤                │                │
       │                │                │                │
       │                │ 10. Display Report              │                │
       │                │ - Chart.js renders charts       │                │
       │                │ - Alpine.js table interactions  │                │
       │                │ - Export buttons                │                │
       │                │◄───────────────┤                │
```

### **6. CCTV Layout Management Workflow (Admin Only)**

```
┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐
│   Admin     │  │   Blade     │  │  Laravel    │  │  Database   │  │ WebSocket   │
│   User      │  │ Template    │  │ Controller  │  │             │  │   Server    │
└─────────────┘  └─────────────┘  └─────────────┘  └─────────────┘  └─────────────┘
       │                │                │                │                │
       │ 1. Create Layout Request        │                │                │
       │ (4-window, positions config)    │                │                │
       ├───────────────►│                │                │                │
       │                │                │                │                │
       │                │ 2. POST /layouts                │                │
       │                │ Form: {layout_name, layout_type,│                │
       │                │  positions: [{branch_id,       │                │
       │                │   device_id, position_name}]}  │                │
       │                ├───────────────────────────────►│                │
       │                │                │                │                │
       │                │                │ 3. Validate Admin Access        │
       │                │                │ - Check admin role              │
       │                │                │ - Verify permissions            │
       │                │                │ - Log admin action              │
       │                │                ├───────────────►│                │
       │                │                │                │                │
       │                │                │ 4. Create Layout                │
       │                │                │ - Save to cctv_layout_settings  │
       │                │                │ - Validate layout type         │
       │                │                │ - Set created_by               │
       │                │                ├───────────────►│                │
       │                │                │                │                │
       │                │                │ 5. Configure Positions         │
       │                │                │ - Save to cctv_position_settings│
       │                │                │ - Validate branch/device        │
       │                │                │ - Set position settings        │
       │                │                ├───────────────►│                │
       │                │                │                │                │
       │                │                │ 6. Layout Created               │
       │                │                │ - Layout ID returned            │
       │                │                │ - Position count confirmed      │
       │                │                │◄───────────────┤                │
       │                │                │                │                │
       │                │ 7. Redirect to Blade View       │                │
       │                │ - Redirect to layouts.index     │                │
       │                │ - With success flash message    │                │
       │                │◄───────────────┤                │                │
       │                │                │                │                │
       │                │ 8. Set Default Layout (Optional)│                │
       │                │ POST /layouts/{id}/set-default  │                │
       │                ├───────────────────────────────►│                │
       │                │                │                │                │
       │                │                │ 9. Update Default              │
       │                │                │ - Unset previous default        │
       │                │                │ - Set new default layout        │
       │                │                │ - Update user preferences       │
       │                │                ├───────────────►│                │
       │                │                │                │                │
       │                │                │ 10. Broadcast Layout Change     │
       │                │                │ - WebSocket: layout_updated      │
       │                │                │ - Data: {layout_id, is_default} │
       │                │                ├───────────────────────────────►│
       │                │                │                │                │
       │                │                │                │ 11. Notify All Clients│
       │                │                │                │ - Layout change event │
       │                │                │                │ - Update UI components│
       │                │                │                ├─────────────────►│
       │                │                │                │                │
       │                │ 12. Render Blade View           │                │                │
       │                │ - Load layouts.index.blade      │                │                │
       │                │ - Display success toast         │                │                │
       │                │ - Alpine.js updates UI          │                │                │
       │                │◄─────────────────────────────────────────────────┤                │
```

### **7. Real-time Dashboard Updates**

```
┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐
│   Device    │  │   API       │  │  Database   │  │ WebSocket   │  │   Client    │
│  (Camera)   │  │   Server    │  │             │  │   Server    │  │ Dashboard   │
└─────────────┘  └─────────────┘  └─────────────┘  └─────────────┘  └─────────────┘
       │                │                │                │                │
       │ 1. Detection Event              │                │                │
       │ (New detection: count=5)        │                │                │
       ├───────────────►│                │                │                │
       │                │                │                │                │
       │                │ 2. Process & Save              │                │
       │                │ - Update detection logs        │                │
       │                │ - Update device totals         │                │
       │                │ - Create event log             │                │
       │                ├───────────────►│                │                │
       │                │                │                │                │
       │                │ 3. Database Updated            │                │
       │                │ - All tables updated           │                │
       │                │ - Statistics recalculated      │                │
       │                │◄───────────────┤                │                │
       │                │                │                │                │
       │                │ 4. Broadcast Update            │                │
       │                │ - WebSocket channel: dashboard │                │
       │                │ - Event: detection_update      │                │
       │                │ - Data: {device_id, count,     │                │
       │                │   branch_id, timestamp}        │                │
       │                ├───────────────────────────────►│                │
       │                │                │                │                │
       │                │                │                │ 5. Broadcast to Clients│
       │                │                │                │ - All connected clients│
       │                │                │                │ - Dashboard subscribers│
       │                │                │                ├─────────────────►│
       │                │                │                │                │
       │                │                │                │                │ 6. Update UI
       │                │                │                │                │ - Update statistics│
       │                │                │                │                │ - Refresh charts  │
       │                │                │                │                │ - Show notification│
       │                │                │                │                │◄─────────────────┤
       │                │                │                │                │
       │                │                │                │                │
       │                │ 7. WhatsApp Notification       │                │                │
       │                │ - Send to configured numbers   │                │                │
       │                │ - Track delivery status        │                │                │
       │                ├─────────────────────────────────────────────────┤                │
       │                │                │                │                │
       │                │ 8. Notification Status Update  │                │                │
       │                │ - WhatsApp delivered/read      │                │                │
       │                │ - Update notification status   │                │                │
       │                ├─────────────────────────────────────────────────┤                │
       │                │                │                │                │
       │                │ 9. Broadcast Notification Update                │                │
       │                │ - WebSocket: notification_update                │                │
       │                │ - Data: {event_id, status}     │                │                │
       │                ├───────────────────────────────►│                │                │
       │                │                │                │                │
       │                │                │                │ 10. Update Notification UI│
       │                │                │                │ - Show delivery status │
       │                │                │                │ - Update notification list│
       │                │                │                ├─────────────────►│
```

## 📊 Sequence Diagram Summary

### **Key Interactions:**

1. **Company Group Management Flow (Admin Only)**

   - Admin → Frontend → API → Database → WebSocket → All Clients
   - Group creation, update, deletion
   - Branch association management
   - Real-time group updates and notifications

2. **Person Detection Flow (Re-ID Based)**

   - Device → API → Re-ID Master → Detection Log → Event Log → WhatsApp → Client
   - Real-time person re-identification and tracking
   - Multiple detection records per person per day
   - Fire & forget WhatsApp notifications

3. **CCTV Stream Flow**

   - Client → API → Database → Stream Server → Display
   - Device-based stream management
   - Authentication, validation, and health monitoring

4. **API Management Flow**

   - Admin → Frontend → API → Database → External Client
   - Complete credential lifecycle management
   - Device and Re-ID scoping support

5. **Report Generation Flow**

   - User → Blade → Controller → Database (counting_reports) → Response
   - Database-stored reports (counting_reports table)
   - Materialized views for complex queries
   - Person tracking analytics

6. **CCTV Layout Management Flow (Admin Only)**

   - Admin → Frontend → API → Database → WebSocket → All Clients
   - Layout creation and position configuration
   - Real-time layout updates and notifications
   - Admin-controlled layout switching

7. **Real-time Updates Flow**
   - Device → API → Database → WebSocket → All Clients
   - Live dashboard updates and notifications
   - Person tracking updates
   - Detection count updates
   - Layout change notifications
   - Group change notifications

### **Performance Optimizations:**

- **Database Caching**: PostgreSQL materialized views for complex queries
- **Async Processing**: Background jobs with retry mechanism (Database Queue)
- **WebSocket**: Real-time updates without polling (Laravel Echo)
- **Database Transactions**: ACID compliance with deadlock retry
- **Read/Write Splitting**: Separate read and write connections
- **Composite Indexes**: Optimized multi-column indexes (GIN, B-tree, partial)
- **Eager Loading**: Prevent N+1 query problems
- **Rate Limiting**: Per-credential and per-IP throttling
- **Connection Pooling**: PgBouncer for PostgreSQL

### **Error Handling:**

- **Validation**: Form Requests with comprehensive rules
- **Fallbacks**: Materialized view refresh, retry mechanisms (3 attempts)
- **Monitoring**: Health checks, status tracking, performance metrics
- **Logging**: Comprehensive audit trails (Laravel logs + database logs)
- **Transactions**: Automatic rollback on errors
- **Failed Jobs**: Queue failed job handling with notifications

### **Frontend Architecture (Laravel Blade + Alpine.js):**

#### **Blade Components:**

```php
// resources/views/components/button.blade.php
@props(['variant' => 'primary', 'type' => 'button'])

<button type="{{ $type }}"
    {{ $attributes->merge(['class' => "btn btn-{$variant}"]) }}>
    {{ $slot }}
</button>
```

#### **Alpine.js Integration:**

```html
<!-- resources/views/groups/index.blade.php -->
<div
  x-data="{ 
    showModal: false, 
    selectedGroup: null,
    deleteGroup(id) {
        this.selectedGroup = id;
        this.showModal = true;
    }
}"
>
  <!-- Group list with Alpine.js interactivity -->
  <button @click="deleteGroup({{ $group->id }})">Delete</button>

  <!-- Modal component -->
  <x-modal x-show="showModal" @close="showModal = false">
    <!-- Modal content -->
  </x-modal>
</div>
```

#### **Real-time Updates with Laravel Echo:**

```javascript
// resources/js/app.js
import Echo from "laravel-echo";
import Pusher from "pusher-js";

window.Pusher = Pusher;
window.Echo = new Echo({
  broadcaster: "pusher",
  key: import.meta.env.VITE_PUSHER_APP_KEY,
  cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
});

// Listen for real-time updates
Echo.channel("dashboard").listen("GroupUpdated", (e) => {
  // Alpine.js updates UI
  Alpine.store("groups").refresh();
});
```

#### **Chart.js Integration:**

```html
<!-- resources/views/dashboard/index.blade.php -->
<canvas id="detectionChart" x-data="chartComponent()"></canvas>

<script>
  function chartComponent() {
      return {
          chart: null,
          init() {
              const ctx = document.getElementById('detectionChart');
              this.chart = new Chart(ctx, {
                  type: 'line',
                  data: @json($chartData)
              });
          }
      }
  }
</script>
```

---

_These sequence diagrams provide a detailed view of how each major workflow operates within the CCTV Dashboard system using Laravel Blade Templates and Alpine.js, ensuring proper understanding of data flow and system interactions._
