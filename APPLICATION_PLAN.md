# 📱 CCTV Dashboard - Application Plan & Workflow

## 🏗️ Application Architecture Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                    CCTV Dashboard Application                    │
├─────────────────────────────────────────────────────────────────┤
│  Dashboard  │  Branches  │  Devices  │  Persons  │  Events  │   │
│             │            │           │  (Re-ID)  │          │   │
│  Live CCTV  │  Settings  │  Counting │  API      │  Reports │   │
│  View       │            │  Analytics│  Creds    │          │   │
└─────────────────────────────────────────────────────────────────┘
```

**Core Modules:**

- **Dashboard**: Overview statistics and live monitoring
- **Company Groups**: Province-level company group management (Admin only)
- **Branches**: Company branch management (city level)
- **Devices**: Camera/sensor/thermo device management (per branch)
- **Persons (Re-ID)**: Person re-identification tracking
- **Events**: Event logging and notification management
- **Live CCTV**: 4/6/8-window grid stream viewer
- **Counting**: Person detection analytics (Re-ID based)
- **API**: API credential and usage management
- **Reports**: Report generation and analytics
- **Settings**: System configuration

**Technology Stack:**

- **Backend**: Laravel 10+ (PHP 8.2+)
- **Frontend**: Laravel Blade Templates
- **JavaScript**: Alpine.js for interactivity
- **CSS**: Tailwind CSS
- **Database**: PostgreSQL 15+
- **Queue**: Database Queue
- **Real-time**: Laravel Echo + Pusher/WebSockets

## 📋 Main Menu Structure

### **1. Dashboard** 📊

- **Overview Statistics**

  - Total branches
  - Total devices
  - Today's detections
  - Active events
  - System health status

- **Live Charts**

  - Detection trends (hourly/daily)
  - Branch performance comparison
  - Device activity heatmap
  - Event type distribution

- **Quick Actions**
  - View live CCTV streams
  - Generate quick reports
  - System alerts & notifications

### **2. Company Group Management** 🏢

- **Group Registry**

  - All company groups (province level)
  - Province code & name
  - Group name & description
  - Contact information
  - Status monitoring (active/inactive)
  - Associated branches count

- **Group Details**

  - Province information
  - Group contact details
  - Address & location
  - Associated branches list
  - Performance metrics
  - Group statistics

- **Group Settings**
  - Basic information
  - Contact details
  - Address management
  - Status control
  - Branch assignment

### **3. Branch Management** 🏢

- **Branch List**

  - View all branches
  - Search & filter by group/province
  - Branch status indicators
  - Quick actions (edit, view details)

- **Branch Details**

  - Basic information
  - Contact details
  - GPS coordinates
  - Associated devices
  - Event settings
  - Parent group information

- **Branch Settings**
  - General information
  - WhatsApp configuration
  - Notification preferences
  - API access settings

### **4. Device Management** 📹

- **Device Registry**

  - All devices across branches
  - Device type filtering (camera, sensor, thermo)
  - Status monitoring (active/inactive)
  - Performance metrics
  - Device belongs to specific branch

- **Device Details**

  - Device ID & name
  - Device type (camera/sensor/thermo)
  - Branch location
  - Technical specifications
  - Current status
  - Detection history
  - Stream configuration

- **Device Configuration**
  - Device settings
  - Detection parameters
  - Stream quality settings
  - Event triggers (per device per branch)

### **4.5 Person Tracking (Re-ID)** 🧑

- **Person Registry**

  - All detected persons (Re-ID)
  - Re-identification ID list
  - Person name (if identified)
  - Appearance features (JSON)
  - Detection statistics
  - First/last detected timestamps

- **Person Details**

  - Re-ID identifier
  - Person name (optional)
  - Appearance features (clothing colors, height, etc.)
  - Total detection count
  - Branches that detected this person
  - Detection timeline
  - Associated events

- **Person Tracking**
  - Track person across branches
  - View detection history
  - Appearance feature analysis
  - Cross-branch movement patterns

### **5. Live CCTV View** 📺

- **Dynamic Grid Layouts**

  - **4-Window Grid** (2x2): Standard quad view
  - **6-Window Grid** (2x3): Extended monitoring
  - **8-Window Grid** (2x4): Maximum surveillance
  - Admin-configurable layouts
  - Position-specific branch/device assignment

- **Layout Management (Admin Only)**

  - Create custom layouts
  - Set default layout
  - Configure position settings
  - Branch/device per position
  - Auto-switch functionality
  - Quality settings per position

- **Stream Management**

  - Position-based stream assignment
  - Stream health monitoring
  - Recording controls
  - Screenshot capture
  - Resolution & quality controls

- **Multi-Branch View**
  - Switch between branches per position
  - Compare multiple locations
  - Dynamic layout switching
  - Real-time stream status

### **6. CCTV Layout Management** 🎛️

- **Layout Configuration (Admin Only)**

  - Create/Edit/Delete layouts
  - Layout types: 4-window, 6-window, 8-window
  - Set default layout
  - Layout descriptions and metadata
  - User access control

- **Position Settings**

  - Branch assignment per position
  - Device assignment per position
  - Position naming
  - Enable/disable positions
  - Auto-switch configuration
  - Quality settings (low/medium/high)
  - Resolution settings

- **Layout Management**

  - Switch between layouts
  - Position-specific configurations
  - Real-time layout updates
  - Layout performance monitoring
  - Backup/restore layouts

- **Admin Controls**
  - Layout creation wizard
  - Position configuration interface
  - Layout testing and validation
  - User permission management

### **7. Event Management** 🚨

- **Event Logs**

  - Real-time event feed
  - Event type filtering
  - Branch/device filtering
  - Export capabilities

- **Event Settings**

  - Per-branch configuration
  - Notification rules
  - Image capture settings
  - WhatsApp integration

- **Notification Center**
  - WhatsApp delivery status
  - Failed notifications
  - Notification templates
  - Delivery reports

### **8. Counting & Analytics** 📈

- **Real-time Person Counting (Re-ID)**

  - Live person detection counts
  - Unique persons detected (by Re-ID)
  - Branch-wise breakdown
  - Device performance
  - Detection timeline
  - Cross-branch tracking

- **Detection Analytics**

  - Person tracking (Re-ID based)
  - Detection frequency per person
  - Branch counting logic (1 count per branch)
  - Actual detection count tracking
  - Appearance feature analysis
  - Movement patterns

- **Counting Reports**

  - Daily detection reports
  - Weekly person summaries
  - Monthly analytics
  - Unique person counts
  - Branch performance comparison
  - Custom date ranges

- **Branch Performance**
  - Individual branch stats
  - Unique persons detected per branch
  - Detection counts per branch
  - Device performance metrics
  - Comparative analysis
  - Performance rankings

### **9. API Management** 🔑

- **Credential Management**

  - Create API keys
  - Manage permissions
  - Monitor usage
  - Revoke access

- **API Documentation**

  - Endpoint documentation
  - Authentication guide
  - Rate limiting info
  - Sample requests

- **Usage Analytics**
  - Request logs
  - Performance metrics
  - Error tracking
  - Usage patterns

### **10. Reports & Exports** 📄

- **Standard Reports**

  - Daily activity reports
  - Branch performance
  - Device utilization
  - Event summaries

- **Custom Reports**

  - Date range selection
  - Branch filtering
  - Device filtering
  - Export formats (PDF, Excel, CSV)

- **Scheduled Reports**
  - Automated report generation
  - Email delivery
  - Report templates
  - Subscription management

### **11. Settings** ⚙️

- **System Configuration**

  - General settings
  - Database configuration
  - Cache settings
  - Backup settings

- **Integration Settings**

  - WhatsApp providers
  - Email configuration
  - SMS providers
  - Third-party APIs

- **User Management**
  - User roles & permissions
  - Access control
  - Activity logs
  - Security settings

## 🔄 Module Workflows

### **1. Dashboard Workflow**

```
User Login → Dashboard Load
    │
    ├── Load Statistics (Async)
    │   ├── Total branches
    │   ├── Total devices
    │   ├── Today's detections
    │   └── Active events
    │
    ├── Load Charts (Async)
    │   ├── Detection trends
    │   ├── Branch comparison
    │   ├── Device heatmap
    │   └── Event distribution
    │
    ├── Load Live Feeds (WebSocket)
    │   ├── Real-time notifications
    │   ├── System alerts
    │   └── Status updates
    │
    └── Display Dashboard
        ├── Statistics cards
        ├── Interactive charts
        ├── Live feeds
        └── Quick actions
```

### **2. Company Group Management Workflow**

```
Group Management Request → Group Processing
    │
    ├── Authentication & Authorization
    │   ├── Verify admin role
    │   ├── Check group permissions
    │   ├── Validate user access
    │   └── Log admin action
    │
    ├── Group CRUD Operations
    │   ├── Create Group:
    │   │   ├── Validate province_code (unique)
    │   │   ├── Validate province_name
    │   │   ├── Validate group_name
    │   │   ├── Set status (active/inactive)
    │   │   └── Save to company_groups
    │   │
    │   ├── Update Group:
    │   │   ├── Validate group exists
    │   │   ├── Update group information
    │   │   ├── Update contact details
    │   │   └── Update status
    │   │
    │   └── Delete Group:
    │       ├── Check for associated branches
    │       ├── Cascade delete branches (if confirmed)
    │       ├── Delete group record
    │       └── Log deletion
    │
    ├── Branch Association
    │   ├── View associated branches
    │   ├── Add branches to group
    │   ├── Remove branches from group
    │   └── Update branch-group relationships
    │
    ├── Group Validation
    │   ├── Verify province code uniqueness
    │   ├── Check branch associations
    │   ├── Validate contact information
    │   └── Test group functionality
    │
    └── Group Activation
        ├── Activate/deactivate group
        ├── Update associated branches status
        ├── Notify connected clients
        └── Log group changes
```

### **3. Person Detection Workflow (Re-ID)**

```
Device Detection → Person Re-Identification → Event Processing
    │
    ├── Validate Detection
    │   ├── Check device status (device_master)
    │   ├── Verify branch settings (company_branches)
    │   ├── Extract Re-ID from detection (re_id)
    │   └── Validate detection data (confidence, bounding box)
    │
    ├── Process Re-ID (Person Tracking)
    │   ├── Check if re_id exists in re_id_master
    │   ├── Create new person if not exists
    │   ├── Update appearance_features (JSON)
    │   ├── Update first_detected_at (if new)
    │   ├── Update last_detected_at (always)
    │   └── Increment total_detection_count
    │
    ├── Log Detection
    │   ├── Save to re_id_branch_detection:
    │   │   ├── re_id (person identifier)
    │   │   ├── branch_id (where detected)
    │   │   ├── device_id (which device)
    │   │   ├── detected_count (usually 1)
    │   │   ├── detection_timestamp (when)
    │   │   └── detection_data (JSON: confidence, bounding box)
    │   └── Multiple records allowed per day
    │
    ├── Check Event Settings
    │   ├── Get branch_event_settings (by branch + device)
    │   ├── Check is_active (enabled/disabled)
    │   ├── Check send_image (true/false)
    │   ├── Check send_message (true/false)
    │   ├── Check send_notification (true/false)
    │   └── Check whatsapp_enabled (true/false)
    │
    ├── Create Event Log
    │   ├── Save to event_logs:
    │   │   ├── branch_id
    │   │   ├── device_id
    │   │   ├── re_id (person detected, nullable)
    │   │   ├── event_type (detection/alert/motion)
    │   │   ├── detected_count
    │   │   ├── image_path (if captured)
    │   │   └── event_data (JSON)
    │   └── Set notification flags (image_sent, message_sent, notification_sent)
    │
    ├── Process Event (If Enabled)
    │   ├── Capture Image (if send_image = true)
    │   ├── Send Message (if send_message = true)
    │   ├── Send Notification (if send_notification = true)
    │   └── Send WhatsApp (if whatsapp_enabled = true)
    │       ├── Get whatsapp_numbers from settings
    │       ├── Format message with template
    │       ├── Call WhatsApp provider API
    │       └── Update notification_sent = true
    │
    └── Update Counters & Real-time Updates
        ├── Update re_id_master statistics
        ├── Invalidate related caches
        ├── Trigger WebSocket updates
        └── Log completion
```

### **4. WhatsApp Notification Workflow**

```
Event Triggered → WhatsApp Processing
    │
    ├── Check Event Settings
    │   ├── Get branch_event_settings
    │   ├── Check whatsapp_enabled (boolean ON/OFF)
    │   └── Get whatsapp_numbers (JSON array)
    │
    ├── Prepare Notification
    │   ├── Get event details
    │   ├── Load message template
    │   ├── Replace template variables:
    │   │   ├── {branch_name}
    │   │   ├── {device_name}
    │   │   ├── {detected_count}
    │   │   └── {timestamp}
    │   └── Prepare phone numbers from JSON array
    │
    ├── Send WhatsApp Messages (Fire & Forget)
    │   ├── For each phone number:
    │   │   ├── Call WhatsApp provider API
    │   │   ├── Send message with optional image
    │   │   └── No delivery tracking (simple send)
    │   └── Queue for background processing
    │
    └── Completion
        ├── Update event_log.notification_sent = true
        ├── Log to Laravel logs (success/error)
        └── Continue processing (no waiting)
```

### **5. API Request Workflow**

```
API Request → Processing & Response
    │
    ├── Authentication
    │   ├── Validate API key
    │   ├── Check expiration
    │   ├── Verify permissions
    │   └── Check rate limits
    │
    ├── Authorization
    │   ├── Check scope (branch/device)
    │   ├── Validate permissions
    │   ├── Check resource access
    │   └── Log request
    │
    ├── Process Request
    │   ├── Validate payload
    │   ├── Execute business logic
    │   ├── Update database
    │   └── Prepare response
    │
    ├── Log Response
    │   ├── Record response time
    │   ├── Log status code
    │   ├── Store request/response
    │   └── Update usage stats
    │
    └── Return Response
        ├── JSON response
        ├── Status code
        ├── Error messages (if any)
        └── Rate limit headers
```

### **6. CCTV Layout Management Workflow (Admin Only)**

```
Admin Layout Request → Layout Configuration
    │
    ├── Authentication & Authorization
    │   ├── Verify admin role
    │   ├── Check layout permissions
    │   ├── Validate user access
    │   └── Log admin action
    │
    ├── Layout Creation/Update
    │   ├── Validate layout type (4/6/8-window)
    │   ├── Check position count
    │   ├── Validate branch/device assignments
    │   └── Save to cctv_layout_settings
    │
    ├── Position Configuration
    │   ├── Configure each position (1-8)
    │   ├── Assign branch per position
    │   ├── Assign device per position
    │   ├── Set position name & quality
    │   ├── Configure auto-switch settings
    │   └── Save to cctv_position_settings
    │
    ├── Layout Validation
    │   ├── Verify all positions configured
    │   ├── Check device availability
    │   ├── Validate stream URLs
    │   └── Test layout functionality
    │
    ├── Set Default Layout (Optional)
    │   ├── Unset previous default
    │   ├── Set new default layout
    │   ├── Update user preferences
    │   └── Broadcast layout change
    │
    └── Layout Activation
        ├── Activate layout
        ├── Update frontend configuration
        ├── Notify connected clients
        └── Log layout change
```

### **7. CCTV Stream Management Workflow**

```
Stream Request → Stream Delivery
    │
    ├── Stream Validation
    │   ├── Check stream exists
    │   ├── Verify stream status
    │   ├── Validate user access
    │   └── Check branch permissions
    │
    ├── Stream Preparation
    │   ├── Get stream configuration
    │   ├── Decrypt credentials
    │   ├── Build stream URL
    │   └── Set quality parameters
    │
    ├── Stream Delivery
    │   ├── Establish connection
    │   ├── Stream to client
    │   ├── Monitor stream health
    │   └── Handle disconnections
    │
    ├── Health Monitoring
    │   ├── Ping stream endpoint
    │   ├── Check stream quality
    │   ├── Update status
    │   └── Log performance
    │
    └── Cleanup
        ├── Close connections
        ├── Update last_checked_at
        ├── Log session data
        └── Release resources
```

### **8. Report Generation Workflow**

```
Report Request → Report Delivery
    │
    ├── Validate Request
    │   ├── Check user permissions
    │   ├── Validate date ranges
    │   ├── Verify branch access
    │   └── Check report type
    │
    ├── Check Cache
    │   ├── Look for existing report
    │   ├── Check cache validity
    │   ├── Return cached if valid
    │   └── Continue if expired
    │
    ├── Generate Report
    │   ├── Query raw data
    │   ├── Calculate statistics
    │   ├── Build charts/graphs
    │   └── Format data
    │
    ├── Cache Report
    │   ├── Save to counting_reports
    │   ├── Set expiration
    │   ├── Update cache metadata
    │   └── Log generation time
    │
    └── Deliver Report
        ├── Return JSON/PDF
        ├── Set download headers
        ├── Log delivery
        └── Update analytics
```

## 🎯 User Roles & Access Control

### **1. Admin** (System Administrator)

**Full Access:**

- ✅ Full CRUD on all modules
- ✅ User management
- ✅ System settings
- ✅ API credential management
- ✅ Company Group Management (create/edit/delete groups)
- ✅ Branch/device configuration
- ✅ View all reports and analytics
- ✅ Re-ID (person) management
- ✅ Event configuration (all branches)
- ✅ CCTV Layout Management (create/edit/delete layouts)

**Access Scope:**

- All company groups
- All branches
- All devices
- All persons (Re-ID)
- System configuration

### **2. Operator** (Branch Operator)

**Limited Access:**

- ✅ View assigned branches
- ✅ Manage devices in assigned branches
- ✅ View live CCTV streams
- ✅ Acknowledge events/alerts
- ✅ View branch reports
- ✅ View person detections (Re-ID)
- ✅ Use configured CCTV layouts (view only)
- ❌ User management
- ❌ System settings
- ❌ API credentials
- ❌ Company Group Management
- ❌ CCTV Layout Management

**Access Scope:**

- Assigned branches only
- Devices in assigned branches
- Events in assigned branches

### **3. Viewer** (Read-only User)

**Read-only Access:**

- ✅ View dashboards
- ✅ View reports
- ✅ View live streams (read-only)
- ✅ View person tracking (Re-ID)
- ✅ Use configured CCTV layouts (view only)
- ❌ Any modifications
- ❌ Settings
- ❌ User management
- ❌ Event configuration
- ❌ Company Group Management
- ❌ CCTV Layout Management

**Access Scope:**

- Dashboard view only
- Report viewing
- Stream viewing (no control)

## 📱 Mobile Responsiveness

### **Dashboard Mobile View**

- Stacked statistics cards
- Swipeable charts
- Collapsible sections
- Touch-friendly controls

### **CCTV Mobile View**

- Single stream view
- Swipe to switch streams
- Pinch to zoom
- Landscape mode support

### **Navigation Mobile**

- Hamburger menu
- Bottom navigation
- Quick actions
- Search functionality

## 🔔 Real-time Features

### **WebSocket Events**

- Live detection updates
- Stream status changes
- Notification deliveries
- System alerts

### **Push Notifications**

- Browser notifications
- Mobile app notifications
- Email notifications
- SMS alerts (future)

### **Live Updates**

- Dashboard statistics
- Chart data
- Event feeds
- Stream health

## 📊 Performance Considerations

### **Database Optimization**

- Indexed queries (PostgreSQL GIN, B-tree, partial indexes)
- Materialized views for complex queries
- Table partitioning for large tables
- Read replicas for read-heavy workloads
- Connection pooling with PgBouncer

### **Frontend Optimization**

- **Blade Components**: Reusable UI components
- **Alpine.js**: Lightweight interactivity (no heavy JS framework)
- **Lazy Loading**: Images and content
- **Livewire (Optional)**: For reactive components
- **Vite**: Asset bundling and HMR
- **CDN**: Static assets delivery
- **Turbo/Inertia (Optional)**: SPA-like experience

---

_This application plan provides a comprehensive overview of the CCTV Dashboard system with detailed workflows and user experience considerations._
