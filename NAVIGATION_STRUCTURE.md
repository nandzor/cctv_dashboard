# 🧭 CCTV Dashboard - Navigation Structure

**Technology Stack:**

- **Frontend**: Laravel Blade Templates
- **Interactivity**: Alpine.js
- **Styling**: Tailwind CSS
- **Icons**: Font Awesome / Heroicons
- **Charts**: Chart.js / ApexCharts

## 📱 Main Navigation Layout

```
┌─────────────────────────────────────────────────────────────────────────┐
│  🏠 Dashboard  │  🏢 Groups     │  🏢 Branches  │  📹 Devices  │  🧑 Persons │
│                │  (Admin only)  │               │              │   (Re-ID)  │
├─────────────────────────────────────────────────────────────────────────┤
│  📺 Live CCTV  │  🎛️ Layout     │  🚨 Events    │  📈 Reports  │  🔑 API     │
│                │  (Admin only)  │               │              │            │
├─────────────────────────────────────────────────────────────────────────┤
│  ⚙️ Settings                                                             │
└─────────────────────────────────────────────────────────────────────────┘
```

**Blade Template Structure:**

```
resources/views/
├── layouts/
│   ├── app.blade.php (Main layout with navigation)
│   └── guest.blade.php (Login/Register layout)
├── components/
│   ├── navigation.blade.php (Main nav component)
│   ├── sidebar.blade.php (Sidebar component)
│   ├── modal.blade.php (Reusable modal)
│   └── button.blade.php (Button component)
├── dashboard/
│   └── index.blade.php
├── groups/
│   ├── index.blade.php
│   ├── show.blade.php
│   └── form.blade.php
├── branches/
│   ├── index.blade.php
│   ├── show.blade.php
│   └── form.blade.php
└── ... (other modules)
```

## 🏠 Dashboard Menu

### **Primary Dashboard**

- **Statistics Cards**

  - Total Branches (with status indicators)
  - Total Devices (active/inactive)
  - Today's Detections (with trend arrow)
  - Active Events (with alert count)

- **Quick Actions**

  - 🎯 View Live Streams
  - 📊 Generate Quick Report
  - 🚨 Check Recent Events
  - ⚙️ System Settings

- **Live Charts**

  - 📈 Detection Trends (24h/7d/30d)
  - 🏢 Branch Performance Comparison
  - 📹 Device Activity Heatmap
  - 🎯 Event Type Distribution

- **Recent Activity Feed**
  - Latest detections
  - System notifications
  - WhatsApp delivery status
  - Stream status updates

### **Dashboard Sub-menus**

```
Dashboard
├── Overview (Default)
├── Branch Performance
├── Device Statistics
├── Person Tracking (Re-ID)
├── Event Monitoring
└── System Health
```

## 🏢 Company Group Management Menu (Admin Only)

### **Group Registry**

- **Search & Filter**

  - Search by group name/province
  - Filter by status (active/inactive)
  - Filter by province code
  - Sort by performance

- **Group Cards**
  - Group name & province
  - Province code
  - Status indicator
  - Associated branches count
  - Quick actions (view, edit, settings)

### **Group Details**

- **Basic Information**

  - Group name & province code
  - Province name
  - Address & location
  - Contact information

- **Performance Metrics**

  - Total branches
  - Active branches
  - Total devices
  - Detection statistics

- **Associated Data**
  - Branches list
  - Branch performance
  - Group statistics
  - Regional analytics

### **Group Settings**

- **General Settings**

  - Basic information
  - Contact details
  - Address management
  - Status control

- **Branch Management**

  - Add branches to group
  - Remove branches from group
  - Branch assignment
  - Group hierarchy

### **Group Sub-menus**

```
Company Group Management
├── All Groups
├── Add New Group
├── Group Performance
├── Group Settings
└── Group Reports
```

## 🏢 Branch Management Menu

### **Branch List View**

- **Search & Filter**

  - Search by name/city
  - Filter by status (active/inactive)
  - Filter by group/province
  - Sort by performance

- **Branch Cards**
  - Branch name & city
  - Status indicator
  - Device count
  - Today's detections
  - Quick actions (view, edit, settings)

### **Branch Details**

- **Basic Information**

  - Branch name & code
  - Address & coordinates
  - Contact information
  - Group/Province

- **Performance Metrics**

  - Total devices
  - Active devices
  - Detection counts
  - Event statistics

- **Associated Data**
  - Devices list
  - Stream configurations
  - Event settings
  - API credentials

### **Branch Settings**

- **General Settings**

  - Basic information
  - Contact details
  - GPS coordinates
  - Status management

- **Event Configuration**

  - Notification settings
  - WhatsApp configuration
  - Message templates
  - Image capture settings

- **API Access**
  - Branch-specific credentials
  - Permission settings
  - Rate limits
  - Usage monitoring

### **Branch Sub-menus**

```
Branch Management
├── All Branches
├── Add New Branch
├── Branch Performance
├── Branch Settings
└── Branch Reports
```

## 📹 Device Management Menu

### **Device Registry**

- **Device Grid View**

  - Device ID & name
  - Device type (camera/sensor/thermo)
  - Branch location (each device belongs to one branch)
  - Status (active/inactive)
  - Stream status (online/offline/error)
  - Performance metrics

- **Filters & Search**
  - Device type filter
  - Branch filter
  - Status filter
  - Search by device ID/name

### **Device Details**

- **Technical Information**

  - Device ID (unique identifier)
  - Device name
  - Device type (camera/sensor/thermo)
  - Branch assignment
  - Stream configuration
  - Detection parameters
  - Quality settings

- **Performance Data**

  - Person detection history (Re-ID based)
  - Event logs
  - Stream health status
  - Usage statistics
  - Detection accuracy metrics

- **Configuration**
  - Device settings
  - Event triggers (per device per branch)
  - Stream parameters
  - WhatsApp notification settings

### **Device Sub-menus**

```
Device Management
├── All Devices
├── Device Types
│   ├── Cameras
│   ├── Sensors
│   └── Thermal
├── By Branch
├── Device Performance
├── Device Configuration
└── Device Reports
```

## 🧑 Person Tracking (Re-ID) Menu

### **Person Registry**

- **Person List View**

  - Re-ID identifier (e.g., "person_001_abc123")
  - Person name (if identified/registered)
  - Appearance features (clothing colors, height)
  - Total detection count
  - Branches detected
  - First/last detected timestamps
  - Status (active/inactive)

- **Filters & Search**
  - Search by Re-ID
  - Filter by branch
  - Filter by date range
  - Sort by detection count

### **Person Details**

- **Identification Information**

  - Re-ID (unique identifier)
  - Person name (optional)
  - Appearance features (JSON):
    - Clothing colors
    - Height/build
    - Other distinguishing features

- **Detection Analytics**

  - Total detection count
  - Unique branches count (1 count per branch)
  - Detection timeline
  - Device detection breakdown
  - Movement patterns across branches

- **Detection History**
  - All detection records (re_id_branch_detection)
  - Multiple detections per day
  - Detection timestamps
  - Confidence scores
  - Bounding box data

### **Person Sub-menus**

```
Person Tracking (Re-ID)
├── All Persons
├── Recently Detected
├── Cross-Branch Tracking
├── Detection Analytics
├── Appearance Analysis
└── Person Reports
```

## 📺 Live CCTV View Menu

### **Dynamic Grid Layouts**

- **4-Window Grid (2x2)**

  - Standard quad view
  - Position 1-4 selectable
  - Branch/Device selection per position
  - Stream quality controls

- **6-Window Grid (2x3)**

  - Extended monitoring view
  - Position 1-6 selectable
  - Enhanced surveillance coverage
  - Multi-branch comparison

- **8-Window Grid (2x4)**
  - Maximum surveillance view
  - Position 1-8 selectable
  - Complete monitoring coverage
  - Advanced analytics view

### **Stream Controls**

- **Grid Layout**

  - Dynamic layout switching
  - Position-specific configuration
  - Real-time layout updates
  - Admin-configured layouts

- **Stream Quality**

  - Resolution selector (640x480, 1280x720, 1920x1080)
  - FPS adjustment (15, 25, 30)
  - Bitrate control
  - Quality settings (low/medium/high)

- **Recording & Capture**
  - Start/stop recording
  - Screenshot capture
  - Recording history
  - Export options

### **CCTV Sub-menus**

```
Live CCTV View
├── 4-Window Grid
├── 6-Window Grid
├── 8-Window Grid
├── Single Stream
├── Stream Management
├── Recording Center
└── Stream Settings
```

## 🎛️ CCTV Layout Management Menu (Admin Only)

### **Layout Configuration**

- **Layout Types**

  - 4-Window Layout (2x2 grid)
  - 6-Window Layout (2x3 grid)
  - 8-Window Layout (2x4 grid)
  - Custom layout creation

- **Layout Management**
  - Create new layouts
  - Edit existing layouts
  - Set default layout
  - Delete unused layouts
  - Layout validation

### **Position Settings**

- **Position Configuration**

  - Branch assignment per position
  - Device assignment per position
  - Position naming
  - Enable/disable positions
  - Quality settings per position

- **Advanced Features**
  - Auto-switch configuration
  - Switch intervals (10-300 seconds)
  - Resolution settings
  - Stream quality control
  - Position-specific settings

### **Layout Sub-menus**

```
CCTV Layout Management
├── All Layouts
├── Create Layout
├── Layout Types
│   ├── 4-Window Layouts
│   ├── 6-Window Layouts
│   └── 8-Window Layouts
├── Position Settings
├── Layout Testing
└── Layout Reports
```

## 🚨 Event Management Menu

### **Event Logs**

- **Real-time Feed**

  - Latest events first
  - Event type indicators
  - Branch/device info
  - Timestamp & status

- **Filters**

  - Event type (detection/alert/motion/manual)
  - Branch filter
  - Device filter
  - Date range
  - Status filter

- **Event Details**
  - Full event information
  - Captured images
  - Detection data
  - Notification status

### **Event Settings**

- **Per-Branch Configuration**

  - Enable/disable events
  - Image capture settings
  - Message settings
  - Notification preferences

- **WhatsApp Integration**

  - Phone number management
  - Message templates
  - Delivery tracking
  - Provider configuration

- **Notification Rules**
  - Event triggers
  - Frequency limits
  - Escalation rules
  - Quiet hours

### **Notification Center**

- **Notification Settings**

  - WhatsApp ON/OFF per device
  - Phone numbers management (JSON array)
  - Message templates
  - Template variables:
    - {branch_name}
    - {device_name}
    - {detected_count}
    - {re_id}
    - {timestamp}

- **Notification Status**
  - Notifications sent (boolean flag)
  - Simple fire & forget (no delivery tracking)
  - Laravel log for success/errors
  - Template preview function

### **Event Sub-menus**

```
Event Management
├── Event Logs
├── Event Settings
├── Notification Center
├── WhatsApp Management
└── Event Reports
```

## 📈 Reports & Analytics Menu

### **Real-time Person Counting (Re-ID)**

- **Live Dashboard**

  - Current person detection counts
  - Unique persons detected (Re-ID based)
  - Branch-wise breakdown (1 count per branch)
  - Device performance
  - Detection timeline
  - Trend indicators

- **Person Analytics**

  - Re-ID tracking statistics
  - Cross-branch movement
  - Appearance feature analysis
  - Detection frequency patterns

- **Historical Data**
  - Hourly detection trends
  - Daily person summaries
  - Weekly patterns
  - Monthly analytics
  - Unique person counts over time

### **Standard Reports**

- **Daily Reports**

  - Branch activity
  - Device utilization
  - Event summaries
  - Performance metrics

- **Periodic Reports**
  - Weekly summaries
  - Monthly analytics
  - Quarterly reviews
  - Annual reports

### **Custom Reports**

- **Report Builder**

  - Date range selection
  - Branch filtering
  - Device filtering
  - Metric selection

- **Export Options**
  - PDF format
  - Excel format
  - CSV format
  - JSON format

### **Reports Sub-menus**

```
Reports & Analytics
├── Real-time Person Counting (Re-ID)
├── Person Analytics
├── Daily Reports
├── Weekly Reports
├── Monthly Reports
├── Branch Performance
├── Device Performance
├── Custom Reports
└── Report Scheduler
```

## 🔑 API Management Menu

### **Credential Management**

- **API Keys List**

  - Credential name
  - Scope (branch/device)
  - Status (active/inactive)
  - Usage statistics
  - Expiration date

- **Create New Credential**
  - Credential name
  - Scope selection
  - Permission settings
  - Rate limits
  - Expiration date

### **API Documentation**

- **Endpoint Reference**

  - Authentication guide
  - Request/response formats
  - Error codes
  - Rate limiting info

- **Code Examples**
  - JavaScript examples
  - PHP examples
  - Python examples
  - cURL examples

### **Usage Analytics**

- **Request Logs**

  - Recent requests
  - Response times
  - Error rates
  - Usage patterns

- **Performance Metrics**
  - Request volume
  - Response times
  - Error tracking
  - Rate limit hits

### **API Sub-menus**

```
API Management
├── Credentials
├── Documentation
├── Usage Analytics
├── Rate Limiting
└── Security Settings
```

## ⚙️ Settings Menu

### **System Configuration**

- **General Settings**

  - Application settings
  - Timezone configuration
  - Language settings
  - Theme preferences

- **Database Settings**
  - Connection settings
  - Backup configuration
  - Cache settings
  - Performance tuning

### **Integration Settings**

- **WhatsApp Providers**

  - Provider selection
  - API configuration
  - Test connectivity
  - Delivery tracking

- **Email Configuration**
  - SMTP settings
  - Email templates
  - Notification settings
  - Test delivery

### **User Management**

- **User Roles**

  - Role definitions
  - Permission matrix
  - Access control
  - Activity logging

- **Security Settings**
  - Password policies
  - Session management
  - Two-factor authentication
  - Audit logs

### **Settings Sub-menus**

```
Settings
├── System Configuration
├── Integration Settings
├── User Management
├── Security Settings
├── Backup & Restore
└── System Logs
```

## 📱 Mobile Navigation

### **Mobile Menu Structure**

```
┌─────────────────────────────────────────────────────────────────┐
│  🏠 Dashboard  │  🏢 Groups     │  📹 Devices  │  🧑 Persons  │   │
│                │  (Admin only)  │              │   (Re-ID)    │   │
├─────────────────────────────────────────────────────────────────┤
│  📺 Live CCTV  │  🎛️ Layout     │  🚨 Events  │  📈 Reports  │   │
│                │  (Admin only)  │              │              │   │
├─────────────────────────────────────────────────────────────────┤
│  🔑 API        │  ⚙️ Settings   │  👤 Profile  │              │   │
└─────────────────────────────────────────────────────────────────┘
```

### **Mobile-Specific Features**

- **Swipe Navigation**

  - Swipe between main sections
  - Gesture-based controls
  - Touch-friendly interfaces

- **Responsive Layouts**

  - Stacked cards on mobile
  - Collapsible sections
  - Touch-optimized buttons

- **Mobile CCTV View**
  - Single stream focus
  - Swipe to switch streams
  - Pinch to zoom
  - Landscape mode support

## 🎯 Quick Actions & Shortcuts

### **Global Shortcuts**

- `Ctrl + D` - Dashboard
- `Ctrl + G` - Company Groups (Admin only)
- `Ctrl + B` - Branches
- `Ctrl + M` - Devices (Monitoring)
- `Ctrl + P` - Persons (Re-ID Tracking)
- `Ctrl + V` - Live CCTV
- `Ctrl + L` - Layout Management (Admin only)
- `Ctrl + E` - Events
- `Ctrl + R` - Reports
- `Ctrl + K` - API Management
- `Ctrl + ,` - Settings

### **Context-Specific Actions**

- **Group List**: Quick edit, view details, toggle status, view branches
- **Branch List**: Quick edit, view details, toggle status, view devices
- **Device List**: Quick configure, view stream, check status, view detections
- **Person List (Re-ID)**: View details, track movement, view timeline
- **CCTV View**: Switch streams, capture screenshot, toggle fullscreen, switch layouts
- **Layout Management**: Create layout, edit positions, set default, test layout
- **Event Logs**: Filter, export, view details, view associated person
- **Reports**: Generate, export, schedule, filter by Re-ID

### **Breadcrumb Navigation**

```
Dashboard > Company Groups > Jakarta Group > Branch Management > Jakarta Central Branch > Device Settings
```

## 🔔 Notification System

### **Notification Types**

- **System Alerts**

  - Stream offline
  - Device errors
  - API rate limits
  - System maintenance

- **Event Notifications**
  - New detections
  - Alert triggers
  - WhatsApp delivery status
  - Report generation complete

### **Notification Display**

- **Top Bar Notifications**

  - Bell icon with count
  - Dropdown notification list
  - Mark as read/unread
  - Clear all notifications

- **Toast Notifications**
  - Real-time updates
  - Auto-dismiss after 5s
  - Action buttons
  - Error/warning styling

---

_This navigation structure provides a comprehensive overview of the CCTV Dashboard application's user interface and user experience design._
