# 📊 CCTV Dashboard - Complete Database Plan (English)

## 🏗️ Database Architecture Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                    CCTV Dashboard Database                      │
├─────────────────────────────────────────────────────────────────┤
│  Company Groups (Province) → Company Branches (City)           │
│                              ↓                                 │
│  Device Master (re_id) → Device Branch Detection              │
│                              ↓                                 │
│  Branch Event Settings → Event Logs                           │
│                              ↓                                 │
│  API Credentials → CCTV Streams            │
└─────────────────────────────────────────────────────────────────┘
```

## 📋 Complete Database Structure (14 Tables)

### **1. Core Hierarchy Tables (5 tables)**

#### **1.1 company_groups** (Province Level)

```sql
CREATE TABLE company_groups (
    id BIGSERIAL PRIMARY KEY,
    province_code VARCHAR(10) UNIQUE NOT NULL,
    province_name VARCHAR(100) NOT NULL,
    group_name VARCHAR(150) NOT NULL,
    address TEXT,
    phone VARCHAR(20),
    email VARCHAR(100),
    status VARCHAR(20) DEFAULT 'active' CHECK (status IN ('active', 'inactive')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- PostgreSQL indexes
CREATE INDEX idx_company_groups_province_code ON company_groups(province_code);
CREATE INDEX idx_company_groups_status ON company_groups(status);

-- PostgreSQL trigger for updated_at
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ language 'plpgsql';

CREATE TRIGGER update_company_groups_updated_at BEFORE UPDATE ON company_groups
FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
```

**Purpose:** Master data for company groups organized by province
**Key Fields:**

- `province_code`: Unique code (e.g., "JKT", "BDG", "SBY")
- `province_name`: Full province name
- `group_name`: Company group name
- `status`: Active/inactive status

#### **1.2 company_branches** (City Level)

```sql
CREATE TABLE company_branches (
    id BIGSERIAL PRIMARY KEY,
    group_id BIGINT NOT NULL,
    branch_code VARCHAR(10) UNIQUE NOT NULL,
    branch_name VARCHAR(150) NOT NULL,
    city_name VARCHAR(100) NOT NULL,
    address TEXT,
    phone VARCHAR(20),
    email VARCHAR(100),
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    status VARCHAR(20) DEFAULT 'active' CHECK (status IN ('active', 'inactive')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_company_branches_group FOREIGN KEY (group_id)
        REFERENCES company_groups(id) ON DELETE CASCADE
);

-- PostgreSQL indexes
CREATE INDEX idx_company_branches_group_id ON company_branches(group_id);
CREATE INDEX idx_company_branches_branch_code ON company_branches(branch_code);
CREATE INDEX idx_company_branches_status ON company_branches(status);

-- PostgreSQL trigger for updated_at
CREATE TRIGGER update_company_branches_updated_at BEFORE UPDATE ON company_branches
FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
```

**Purpose:** Individual branch locations with GPS coordinates
**Key Fields:**

- `group_id`: Foreign key to company_groups
- `branch_code`: Unique code (e.g., "JKT001", "BDG001")
- `branch_name`: Full branch name
- `city_name`: City location
- `latitude`, `longitude`: GPS coordinates

#### **1.3 device_master** (Device Registry)

```sql
CREATE TABLE device_master (
    id BIGSERIAL PRIMARY KEY,
    device_id VARCHAR(50) UNIQUE NOT NULL,
    device_name VARCHAR(150),
    device_type VARCHAR(20) DEFAULT 'camera' CHECK (device_type IN ('camera', 'sensor', 'thermo', 'other')),
    branch_id BIGINT NOT NULL,
    status VARCHAR(20) DEFAULT 'active' CHECK (status IN ('active', 'inactive')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_device_master_branch FOREIGN KEY (branch_id)
        REFERENCES company_branches(id) ON DELETE CASCADE
);

-- PostgreSQL indexes
CREATE INDEX idx_device_master_device_id ON device_master(device_id);
CREATE INDEX idx_device_master_device_type ON device_master(device_type);
CREATE INDEX idx_device_master_branch_id ON device_master(branch_id);
CREATE INDEX idx_device_master_status ON device_master(status);

-- PostgreSQL trigger for updated_at
CREATE TRIGGER update_device_master_updated_at BEFORE UPDATE ON device_master
FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
```

**Purpose:** Master registry of all devices
**Key Fields:**

- `device_id`: Unique device identifier
- `device_name`: Device/sensor name
- `device_type`: Camera, sensor, thermo, other
- `branch_id`: Which branch this device belongs to

#### **1.4 re_id_master** (Person Re-Identification Registry)

```sql
CREATE TABLE re_id_master (
    id BIGSERIAL PRIMARY KEY,
    re_id VARCHAR(100) UNIQUE NOT NULL,  -- ✅ Re-identification ID (e.g., "person_001_abc123", "RE_20240116_001")
    person_name VARCHAR(150),  -- Optional: if person is identified/registered
    appearance_features JSONB,  -- ✅ PostgreSQL JSONB for better performance
    first_detected_at TIMESTAMP,  -- When this person was first detected
    last_detected_at TIMESTAMP,  -- When this person was last detected
    total_detection_count INTEGER DEFAULT 0,  -- Total number of detections across all branches
    total_actual_count INTEGER DEFAULT 0,  -- Total count from all branches
    status VARCHAR(20) DEFAULT 'active' CHECK (status IN ('active', 'inactive')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- PostgreSQL indexes
CREATE INDEX idx_re_id_master_re_id ON re_id_master(re_id);
CREATE INDEX idx_re_id_master_first_detected ON re_id_master(first_detected_at);
CREATE INDEX idx_re_id_master_last_detected ON re_id_master(last_detected_at);
CREATE INDEX idx_re_id_master_status ON re_id_master(status);

-- ✅ PostgreSQL GIN index for JSONB queries
CREATE INDEX idx_re_id_master_appearance_features ON re_id_master USING GIN (appearance_features);

-- PostgreSQL trigger for updated_at
CREATE TRIGGER update_re_id_master_updated_at BEFORE UPDATE ON re_id_master
FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
```

**Purpose:** Master registry of all persons with Re-Identification ID
**Key Fields:**

- `re_id`: **Re-identification ID** (unique string identifier for person tracking)
- `person_name`: Person name (if identified/registered)
- `appearance_features`: JSON with appearance data (colors, features, etc.)
- `total_detection_count`: Total number of detections
- `total_actual_count`: Total count from all branches

#### **1.5 re_id_branch_detection** (Person Detection Logs)

```sql
CREATE TABLE re_id_branch_detection (
    id BIGSERIAL PRIMARY KEY,
    re_id VARCHAR(100) NOT NULL,  -- ✅ Re-identification ID reference
    branch_id BIGINT NOT NULL,
    device_id VARCHAR(50) NOT NULL,  -- Which device detected this person
    detected_count INTEGER NOT NULL DEFAULT 1,  -- Usually 1 per detection
    detection_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,  -- ✅ When detection occurred
    detection_data JSONB,  -- ✅ PostgreSQL JSONB for better performance
    status VARCHAR(20) DEFAULT 'active' CHECK (status IN ('active', 'inactive')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_re_id_branch_detection_branch FOREIGN KEY (branch_id)
        REFERENCES company_branches(id) ON DELETE CASCADE,
    CONSTRAINT fk_re_id_branch_detection_re_id FOREIGN KEY (re_id)
        REFERENCES re_id_master(re_id) ON DELETE CASCADE,
    CONSTRAINT fk_re_id_branch_detection_device FOREIGN KEY (device_id)
        REFERENCES device_master(device_id) ON DELETE CASCADE
);

-- PostgreSQL indexes
CREATE INDEX idx_re_id_branch_detection_re_id ON re_id_branch_detection(re_id);
CREATE INDEX idx_re_id_branch_detection_branch_id ON re_id_branch_detection(branch_id);
CREATE INDEX idx_re_id_branch_detection_device_id ON re_id_branch_detection(device_id);
CREATE INDEX idx_re_id_branch_detection_timestamp ON re_id_branch_detection(detection_timestamp);

-- ✅ PostgreSQL composite indexes
CREATE INDEX idx_re_id_branch_detection_branch_date ON re_id_branch_detection(branch_id, detection_timestamp);
CREATE INDEX idx_re_id_branch_detection_reid_date ON re_id_branch_detection(re_id, detection_timestamp);

-- ✅ PostgreSQL GIN index for JSONB queries
CREATE INDEX idx_re_id_branch_detection_data ON re_id_branch_detection USING GIN (detection_data);

-- PostgreSQL trigger for updated_at
CREATE TRIGGER update_re_id_branch_detection_updated_at BEFORE UPDATE ON re_id_branch_detection
FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
```

**Purpose:** Detection logs per Re-ID (person) per branch per device - **Multiple records allowed per day**
**Key Fields:**

- `re_id`: **Re-identification ID reference** (person tracking ID)
- `branch_id`: Branch where detection occurred
- `device_id`: Device that detected the person
- `detected_count`: Usually 1 per detection
- `detection_timestamp`: **When detection occurred**
- `detection_data`: JSON with additional detection info (confidence, bounding box, etc.)

---

### **2. Event Management Tables (2 tables)**

#### **2.1 branch_event_settings** (Event Configuration)

```sql
CREATE TABLE branch_event_settings (
    id BIGSERIAL PRIMARY KEY,
    branch_id BIGINT NOT NULL,
    device_id VARCHAR(50) NOT NULL,  -- Device configuration
    is_active BOOLEAN DEFAULT true,
    send_image BOOLEAN DEFAULT true,
    send_message BOOLEAN DEFAULT true,
    send_notification BOOLEAN DEFAULT true,
    whatsapp_enabled BOOLEAN DEFAULT false,  -- ✅ Simple ON/OFF
    whatsapp_numbers JSONB,  -- ✅ PostgreSQL JSONB array: ["+628123456789"]
    message_template TEXT,
    notification_template TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_branch_event_settings_branch FOREIGN KEY (branch_id)
        REFERENCES company_branches(id) ON DELETE CASCADE,
    CONSTRAINT fk_branch_event_settings_device FOREIGN KEY (device_id)
        REFERENCES device_master(device_id) ON DELETE CASCADE,
    CONSTRAINT unique_branch_device UNIQUE (branch_id, device_id)
);

-- PostgreSQL indexes
CREATE INDEX idx_branch_event_settings_branch_id ON branch_event_settings(branch_id);
CREATE INDEX idx_branch_event_settings_device_id ON branch_event_settings(device_id);
CREATE INDEX idx_branch_event_settings_is_active ON branch_event_settings(is_active);

-- ✅ PostgreSQL GIN index for JSONB array
CREATE INDEX idx_branch_event_settings_whatsapp_numbers ON branch_event_settings USING GIN (whatsapp_numbers);

-- PostgreSQL trigger for updated_at
CREATE TRIGGER update_branch_event_settings_updated_at BEFORE UPDATE ON branch_event_settings
FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
```

**Purpose:** Event configuration per branch per device
**Key Fields:**

- `device_id`: **Device reference** (which device to monitor)
- `branch_id`: Branch configuration
- `whatsapp_enabled`: **Simple ON/OFF** (true/false)
- `whatsapp_numbers`: JSON array of phone numbers
- `message_template`: Custom message template

#### **2.2 event_logs** (Event Activity Log)

```sql
CREATE TABLE event_logs (
    id BIGSERIAL PRIMARY KEY,
    branch_id BIGINT NOT NULL,
    device_id VARCHAR(50) NOT NULL,  -- Device that triggered event
    re_id VARCHAR(100),  -- ✅ Re-identification ID (person detected, nullable)
    event_type VARCHAR(20) DEFAULT 'detection' CHECK (event_type IN ('detection', 'alert', 'motion', 'manual')),
    detected_count INTEGER DEFAULT 0,
    image_path VARCHAR(255),
    image_sent BOOLEAN DEFAULT false,
    message_sent BOOLEAN DEFAULT false,
    notification_sent BOOLEAN DEFAULT false,  -- ✅ Simple boolean
    event_data JSONB,  -- ✅ PostgreSQL JSONB
    event_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_event_logs_branch FOREIGN KEY (branch_id)
        REFERENCES company_branches(id) ON DELETE CASCADE,
    CONSTRAINT fk_event_logs_device FOREIGN KEY (device_id)
        REFERENCES device_master(device_id) ON DELETE CASCADE,
    CONSTRAINT fk_event_logs_re_id FOREIGN KEY (re_id)
        REFERENCES re_id_master(re_id) ON DELETE SET NULL
);

-- PostgreSQL indexes
CREATE INDEX idx_event_logs_branch_id ON event_logs(branch_id);
CREATE INDEX idx_event_logs_device_id ON event_logs(device_id);
CREATE INDEX idx_event_logs_re_id ON event_logs(re_id);
CREATE INDEX idx_event_logs_event_type ON event_logs(event_type);
CREATE INDEX idx_event_logs_event_timestamp ON event_logs(event_timestamp);

-- ✅ PostgreSQL composite index
CREATE INDEX idx_event_logs_branch_event_timestamp ON event_logs(branch_id, event_type, event_timestamp);

-- ✅ PostgreSQL GIN index for JSONB
CREATE INDEX idx_event_logs_event_data ON event_logs USING GIN (event_data);

-- ✅ PostgreSQL partial index (only active events)
CREATE INDEX idx_event_logs_notification_sent ON event_logs(notification_sent) WHERE notification_sent = true;

-- PostgreSQL trigger for updated_at
CREATE TRIGGER update_event_logs_updated_at BEFORE UPDATE ON event_logs
FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
```

**Purpose:** Complete event activity log
**Key Fields:**

- `device_id`: **Device reference** (which device triggered event)
- `re_id`: **Re-identification ID** (person detected, nullable)
- `branch_id`: Branch where event occurred
- `event_type`: Detection, alert, motion, manual
- `notification_sent`: **Simple boolean** (true/false)

---

---

### **3. API Security Tables (3 tables)**

#### **3.1 api_credentials** (API Key Management)

```sql
CREATE TABLE api_credentials (
    id BIGSERIAL PRIMARY KEY,
    credential_name VARCHAR(150) NOT NULL,
    api_key VARCHAR(255) UNIQUE NOT NULL,
    api_secret VARCHAR(255),
    branch_id BIGINT,  -- NULL = global access
    device_id VARCHAR(50),  -- NULL = access to all devices
    re_id VARCHAR(100),  -- NULL = access to all persons
    is_active BOOLEAN DEFAULT true,
    permissions JSONB,  -- ✅ PostgreSQL JSONB: {"read": true, "write": true, "delete": false}
    rate_limit INTEGER DEFAULT 1000,
    last_used_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    created_by BIGINT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_api_credentials_branch FOREIGN KEY (branch_id)
        REFERENCES company_branches(id) ON DELETE CASCADE,
    CONSTRAINT fk_api_credentials_device FOREIGN KEY (device_id)
        REFERENCES device_master(device_id) ON DELETE CASCADE,
    CONSTRAINT fk_api_credentials_re_id FOREIGN KEY (re_id)
        REFERENCES re_id_master(re_id) ON DELETE CASCADE,
    CONSTRAINT fk_api_credentials_created_by FOREIGN KEY (created_by)
        REFERENCES users(id) ON DELETE SET NULL
);

-- PostgreSQL indexes
CREATE INDEX idx_api_credentials_api_key ON api_credentials(api_key);
CREATE INDEX idx_api_credentials_branch_id ON api_credentials(branch_id);
CREATE INDEX idx_api_credentials_device_id ON api_credentials(device_id);
CREATE INDEX idx_api_credentials_re_id ON api_credentials(re_id);
CREATE INDEX idx_api_credentials_is_active ON api_credentials(is_active);
CREATE INDEX idx_api_credentials_expires_at ON api_credentials(expires_at);

-- ✅ PostgreSQL GIN index for JSONB permissions
CREATE INDEX idx_api_credentials_permissions ON api_credentials USING GIN (permissions);

-- PostgreSQL trigger for updated_at
CREATE TRIGGER update_api_credentials_updated_at BEFORE UPDATE ON api_credentials
FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
```

**Purpose:** API credential management with device and Re-ID scoping
**Key Fields:**

- `device_id`: **Device scope** (NULL = all devices)
- `re_id`: **Re-identification ID scope** (NULL = all persons)
- `branch_id`: Branch scope (NULL = all branches)
- `permissions`: JSON permission object
- `rate_limit`: Requests per hour

#### **3.2 api_request_logs** (API Usage Tracking)

```sql
CREATE TABLE api_request_logs (
    id BIGSERIAL PRIMARY KEY,
    api_credential_id BIGINT NOT NULL,
    endpoint VARCHAR(255) NOT NULL,
    method VARCHAR(10) NOT NULL CHECK (method IN ('GET', 'POST', 'PUT', 'DELETE', 'PATCH')),
    request_payload JSONB,  -- ✅ PostgreSQL JSONB
    response_status INTEGER,
    response_time_ms INTEGER,
    ip_address INET,  -- ✅ PostgreSQL INET type for IP addresses
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_api_request_logs_credential FOREIGN KEY (api_credential_id)
        REFERENCES api_credentials(id) ON DELETE CASCADE
);

-- PostgreSQL indexes
CREATE INDEX idx_api_request_logs_credential_id ON api_request_logs(api_credential_id);
CREATE INDEX idx_api_request_logs_endpoint ON api_request_logs(endpoint);
CREATE INDEX idx_api_request_logs_created_at ON api_request_logs(created_at);
CREATE INDEX idx_api_request_logs_method ON api_request_logs(method);

-- ✅ PostgreSQL composite index
CREATE INDEX idx_api_request_logs_credential_created ON api_request_logs(api_credential_id, created_at);

-- ✅ PostgreSQL GIN index for JSONB
CREATE INDEX idx_api_request_logs_payload ON api_request_logs USING GIN (request_payload);
```

**Purpose:** API request tracking and analytics
**Key Fields:**

- `endpoint`: API endpoint accessed
- `response_status`: HTTP status code
- `response_time_ms`: Response time

#### **3.3 users** (User Management - Existing Laravel)

```sql
-- Existing Laravel users table
-- Used for created_by foreign key in api_credentials
```

---

### **4. CCTV Streaming (1 table)**

#### **4.1 cctv_streams** (Stream Management)

```sql
CREATE TABLE cctv_streams (
    id BIGSERIAL PRIMARY KEY,
    branch_id BIGINT NOT NULL,
    device_id VARCHAR(50) NOT NULL,  -- ✅ Device reference
    stream_name VARCHAR(150) NOT NULL,
    stream_url VARCHAR(500) NOT NULL,
    stream_type VARCHAR(20) DEFAULT 'rtsp' CHECK (stream_type IN ('rtsp', 'rtmp', 'hls', 'http', 'websocket')),
    stream_username VARCHAR(100),
    stream_password VARCHAR(255),  -- Encrypted
    stream_port INTEGER,
    is_active BOOLEAN DEFAULT true,
    position INTEGER DEFAULT 1 CHECK (position BETWEEN 1 AND 4),  -- Position in 4-window grid (1-4)
    resolution VARCHAR(20),  -- "1920x1080"
    fps INTEGER DEFAULT 30,
    bitrate INTEGER,  -- in kbps
    last_checked_at TIMESTAMP NULL,
    status VARCHAR(20) DEFAULT 'offline' CHECK (status IN ('online', 'offline', 'error')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_cctv_streams_branch FOREIGN KEY (branch_id)
        REFERENCES company_branches(id) ON DELETE CASCADE,
    CONSTRAINT fk_cctv_streams_device FOREIGN KEY (device_id)
        REFERENCES device_master(device_id) ON DELETE CASCADE
);

-- PostgreSQL indexes
CREATE INDEX idx_cctv_streams_branch_id ON cctv_streams(branch_id);
CREATE INDEX idx_cctv_streams_device_id ON cctv_streams(device_id);
CREATE INDEX idx_cctv_streams_is_active ON cctv_streams(is_active);
CREATE INDEX idx_cctv_streams_position ON cctv_streams(position);
CREATE INDEX idx_cctv_streams_status ON cctv_streams(status);

-- ✅ PostgreSQL composite index for grid queries
CREATE INDEX idx_cctv_streams_branch_position ON cctv_streams(branch_id, position);

-- PostgreSQL trigger for updated_at
CREATE TRIGGER update_cctv_streams_updated_at BEFORE UPDATE ON cctv_streams
FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
```

**Purpose:** CCTV stream configuration with device reference
**Key Fields:**

- `device_id`: **Device reference** (which device stream)
- `branch_id`: Branch location
- `position`: 4-window grid position (1-4)
- `stream_url`: Full stream URL
- `status`: Online/offline/error

---

### **5. Reporting (1 table)**

#### **5.1 counting_reports** (Report Cache)

```sql
CREATE TABLE counting_reports (
    id BIGSERIAL PRIMARY KEY,
    report_type VARCHAR(20) NOT NULL CHECK (report_type IN ('daily', 'weekly', 'monthly', 'yearly')),
    report_date DATE NOT NULL,
    branch_id BIGINT,  -- NULL = global report
    total_devices INTEGER DEFAULT 0,
    total_detections INTEGER DEFAULT 0,
    total_events INTEGER DEFAULT 0,
    unique_device_count INTEGER DEFAULT 0,
    unique_person_count INTEGER DEFAULT 0,  -- ✅ Unique Re-ID count
    report_data JSONB,  -- ✅ PostgreSQL JSONB for detailed statistics
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_counting_reports_branch FOREIGN KEY (branch_id)
        REFERENCES company_branches(id) ON DELETE CASCADE,
    CONSTRAINT unique_report UNIQUE (report_type, report_date, branch_id)
);

-- PostgreSQL indexes
CREATE INDEX idx_counting_reports_report_type ON counting_reports(report_type);
CREATE INDEX idx_counting_reports_report_date ON counting_reports(report_date);
CREATE INDEX idx_counting_reports_branch_id ON counting_reports(branch_id);

-- ✅ PostgreSQL composite index
CREATE INDEX idx_counting_reports_type_date ON counting_reports(report_type, report_date);

-- ✅ PostgreSQL GIN index for JSONB
CREATE INDEX idx_counting_reports_data ON counting_reports USING GIN (report_data);
```

**Purpose:** Pre-computed report cache
**Key Fields:**

- `report_type`: Daily, weekly, monthly, yearly
- `branch_id`: Branch-specific or global (NULL)
- `report_data`: JSON with detailed breakdown

### **6. CCTV Layout Management (2 tables)**

#### **6.1 cctv_layout_settings** (Layout Configuration)

```sql
CREATE TABLE cctv_layout_settings (
    id BIGSERIAL PRIMARY KEY,
    layout_name VARCHAR(150) NOT NULL,
    layout_type VARCHAR(20) NOT NULL CHECK (layout_type IN ('4-window', '6-window', '8-window')),
    total_positions INTEGER NOT NULL CHECK (total_positions IN (4, 6, 8)),
    is_default BOOLEAN DEFAULT false,
    is_active BOOLEAN DEFAULT true,
    description TEXT,
    created_by BIGINT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_cctv_layout_settings_created_by FOREIGN KEY (created_by)
        REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT unique_default_layout UNIQUE (is_default) DEFERRABLE INITIALLY DEFERRED
);

-- PostgreSQL indexes
CREATE INDEX idx_cctv_layout_settings_layout_type ON cctv_layout_settings(layout_type);
CREATE INDEX idx_cctv_layout_settings_is_default ON cctv_layout_settings(is_default);
CREATE INDEX idx_cctv_layout_settings_is_active ON cctv_layout_settings(is_active);
CREATE INDEX idx_cctv_layout_settings_created_by ON cctv_layout_settings(created_by);

-- PostgreSQL trigger for updated_at
CREATE TRIGGER update_cctv_layout_settings_updated_at BEFORE UPDATE ON cctv_layout_settings
FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
```

**Purpose:** Master layout configuration for CCTV grid views
**Key Fields:**

- `layout_type`: 4-window, 6-window, 8-window
- `total_positions`: Number of positions (4, 6, or 8)
- `is_default`: Only one layout can be default
- `created_by`: Admin user who created the layout

#### **6.2 cctv_position_settings** (Position Configuration)

```sql
CREATE TABLE cctv_position_settings (
    id BIGSERIAL PRIMARY KEY,
    layout_id BIGINT NOT NULL,
    position_number INTEGER NOT NULL CHECK (position_number BETWEEN 1 AND 8),
    branch_id BIGINT NOT NULL,
    device_id VARCHAR(50) NOT NULL,
    position_name VARCHAR(150) NOT NULL,
    is_enabled BOOLEAN DEFAULT true,
    auto_switch BOOLEAN DEFAULT false,
    switch_interval INTEGER DEFAULT 30 CHECK (switch_interval BETWEEN 10 AND 300), -- seconds
    resolution VARCHAR(20) DEFAULT '1920x1080',
    quality VARCHAR(20) DEFAULT 'high' CHECK (quality IN ('low', 'medium', 'high')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_cctv_position_settings_layout FOREIGN KEY (layout_id)
        REFERENCES cctv_layout_settings(id) ON DELETE CASCADE,
    CONSTRAINT fk_cctv_position_settings_branch FOREIGN KEY (branch_id)
        REFERENCES company_branches(id) ON DELETE CASCADE,
    CONSTRAINT fk_cctv_position_settings_device FOREIGN KEY (device_id)
        REFERENCES device_master(device_id) ON DELETE CASCADE,
    CONSTRAINT unique_layout_position UNIQUE (layout_id, position_number)
);

-- PostgreSQL indexes
CREATE INDEX idx_cctv_position_settings_layout_id ON cctv_position_settings(layout_id);
CREATE INDEX idx_cctv_position_settings_position_number ON cctv_position_settings(position_number);
CREATE INDEX idx_cctv_position_settings_branch_id ON cctv_position_settings(branch_id);
CREATE INDEX idx_cctv_position_settings_device_id ON cctv_position_settings(device_id);
CREATE INDEX idx_cctv_position_settings_is_enabled ON cctv_position_settings(is_enabled);

-- ✅ PostgreSQL composite index for layout queries
CREATE INDEX idx_cctv_position_settings_layout_position ON cctv_position_settings(layout_id, position_number);

-- PostgreSQL trigger for updated_at
CREATE TRIGGER update_cctv_position_settings_updated_at BEFORE UPDATE ON cctv_position_settings
FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
```

**Purpose:** Individual position configuration within each layout
**Key Fields:**

- `layout_id`: Reference to layout configuration
- `position_number`: Position in grid (1-8)
- `branch_id`: Which branch this position monitors
- `device_id`: Which device this position shows
- `auto_switch`: Enable automatic device switching
- `switch_interval`: How often to switch devices (seconds)
- `quality`: Stream quality setting

---

## 🎛️ CCTV Layout Management System

### **Layout Types Supported:**

| Layout Type  | Positions | Grid Layout | Description        |
| ------------ | --------- | ----------- | ------------------ |
| **4-window** | 4         | 2x2         | Standard quad view |
| **6-window** | 6         | 2x3         | Extended view      |
| **8-window** | 8         | 2x4         | Maximum view       |

### **Position Configuration Example:**

```sql
-- Sample data for 4-window layout
INSERT INTO cctv_layout_settings VALUES
(1, 'Default 4-Window Layout', '4-window', 4, true, true, 'Standard quad view layout', 1, NOW(), NOW());

-- Position configurations for 4-window layout
INSERT INTO cctv_position_settings VALUES
-- Position 1: Jakarta Central - Main Entrance
(1, 1, 1, 1, 'CAMERA_001', 'Main Entrance', true, false, 30, '1920x1080', 'high', NOW(), NOW()),

-- Position 2: Jakarta Central - Parking Area
(2, 1, 2, 1, 'CAMERA_002', 'Parking Area', true, false, 30, '1920x1080', 'high', NOW(), NOW()),

-- Position 3: Jakarta South - Lobby
(3, 1, 3, 2, 'CAMERA_003', 'Lobby View', true, true, 60, '1280x720', 'medium', NOW(), NOW()),

-- Position 4: Bandung - Entry Sensor
(4, 1, 4, 3, 'SENSOR_001', 'Entry Sensor', true, false, 30, '640x480', 'low', NOW(), NOW());
```

### **API Endpoints for Layout Management:**

#### **GET /api/admin/cctv/layouts**

Get all available layouts

**Response:**

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "layout_name": "Default 4-Window Layout",
      "layout_type": "4-window",
      "total_positions": 4,
      "is_default": true,
      "is_active": true,
      "positions": [
        {
          "position_number": 1,
          "branch_name": "Jakarta Central Branch",
          "device_name": "Main Entrance Camera",
          "device_id": "CAMERA_001",
          "position_name": "Main Entrance",
          "is_enabled": true,
          "auto_switch": false,
          "quality": "high"
        }
      ]
    }
  ]
}
```

#### **POST /api/admin/cctv/layouts**

Create new layout configuration

**Payload:**

```json
{
  "layout_name": "Custom 6-Window Layout",
  "layout_type": "6-window",
  "description": "Extended view for monitoring",
  "positions": [
    {
      "position_number": 1,
      "branch_id": 1,
      "device_id": "CAMERA_001",
      "position_name": "Main Entrance",
      "is_enabled": true,
      "auto_switch": false,
      "quality": "high"
    },
    {
      "position_number": 2,
      "branch_id": 1,
      "device_id": "CAMERA_002",
      "position_name": "Parking Area",
      "is_enabled": true,
      "auto_switch": true,
      "switch_interval": 30,
      "quality": "high"
    }
  ]
}
```

#### **PUT /api/admin/cctv/layouts/{layout_id}/positions/{position_number}**

Update specific position configuration

**Payload:**

```json
{
  "branch_id": 2,
  "device_id": "CAMERA_003",
  "position_name": "Updated Lobby View",
  "is_enabled": true,
  "auto_switch": true,
  "switch_interval": 45,
  "quality": "medium"
}
```

#### **POST /api/admin/cctv/layouts/{layout_id}/set-default**

Set layout as default

**Response:**

```json
{
  "success": true,
  "message": "Layout set as default successfully",
  "data": {
    "layout_id": 1,
    "layout_name": "Default 4-Window Layout",
    "is_default": true
  }
}
```

### **Frontend Integration Example:**

```javascript
// Get current layout configuration
async function loadCCTVLayout() {
  const response = await fetch("/api/admin/cctv/layouts/default");
  const layout = await response.json();

  // Render grid based on layout type
  renderCCTVGrid(layout.data);
}

function renderCCTVGrid(layout) {
  const container = document.getElementById("cctv-grid");

  // Clear existing grid
  container.innerHTML = "";

  // Set grid CSS class based on layout type
  container.className = `cctv-grid ${layout.layout_type}`;

  // Render each position
  layout.positions.forEach((position) => {
    const positionElement = createPositionElement(position);
    container.appendChild(positionElement);
  });
}

function createPositionElement(position) {
  const div = document.createElement("div");
  div.className = "cctv-position";
  div.innerHTML = `
    <div class="position-header">
      <h4>Position ${position.position_number}</h4>
      <span class="status ${position.is_enabled ? "online" : "offline"}">
        ${position.is_enabled ? "●" : "○"}
      </span>
    </div>
    <div class="position-config">
      <select class="branch-select" data-position="${position.position_number}">
        <option value="${position.branch_id}">${position.branch_name}</option>
      </select>
      <select class="device-select" data-position="${position.position_number}">
        <option value="${position.device_id}">${position.device_name}</option>
      </select>
    </div>
    <div class="position-stream">
      <video autoplay muted>
        <source src="/api/stream/${position.device_id}" type="video/mp4">
      </video>
    </div>
  `;

  return div;
}
```

### **CSS Grid Layouts:**

```css
/* 4-Window Grid (2x2) */
.cctv-grid.4-window {
  display: grid;
  grid-template-columns: 1fr 1fr;
  grid-template-rows: 1fr 1fr;
  gap: 10px;
  height: 100vh;
}

/* 6-Window Grid (2x3) */
.cctv-grid.6-window {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  grid-template-rows: 1fr 1fr;
  gap: 10px;
  height: 100vh;
}

/* 8-Window Grid (2x4) */
.cctv-grid.8-window {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr 1fr;
  grid-template-rows: 1fr 1fr;
  gap: 10px;
  height: 100vh;
}

.cctv-position {
  border: 2px solid #333;
  border-radius: 8px;
  background: #1a1a1a;
  color: white;
  padding: 10px;
  display: flex;
  flex-direction: column;
}

.position-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}

.status.online {
  color: #4ade80;
}
.status.offline {
  color: #ef4444;
}

.position-config {
  display: flex;
  gap: 10px;
  margin-bottom: 10px;
}

.position-config select {
  background: #333;
  color: white;
  border: 1px solid #555;
  padding: 5px;
  border-radius: 4px;
}

.position-stream {
  flex: 1;
  background: #000;
  border-radius: 4px;
  overflow: hidden;
}

.position-stream video {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
```

---

## 🔄 Enhanced Counting Logic with RE_ID

### **Scenario Example:**

- **Re-ID**: "person_001_abc123" (Re-identification ID for person tracking)
- **Total actual count**: 7
- **Branch A detects**: 2 times → Branch count = **1**
- **Branch B detects**: 5 times → Branch count = **1**
- **Each branch that can detect this Re-ID = 1 count** (not based on actual count)

### **Rule 1: Branch Detection Logic**

- Each branch that can detect a Re-ID (person) is counted as **1 count** for that branch
- Regardless of actual detected count (2 or 5 times)
- Focus on **"can detect"** not **"how many times detected"**
- Multiple detection records allowed per day with different timestamps

### **Rule 2: API Counting Logic**

```php
// Enhanced API counting logic with Re-ID (Person Re-identification)
function countReIdByBranch($re_id, $branch_id, $device_id, $detected_count = 1, $detection_data = null) {
    // 1. Ensure Re-ID (person) exists in master table
    $reIdMaster = ReIdMaster::firstOrCreate(
        ['re_id' => $re_id],
        [
            'first_detected_at' => now(),
            'last_detected_at' => now(),
            'total_actual_count' => 0,
            'total_detection_count' => 0
        ]
    );

    // 2. Update master tracking data
    $reIdMaster->increment('total_actual_count', $detected_count);
    $reIdMaster->increment('total_detection_count', 1);
    $reIdMaster->update(['last_detected_at' => now()]);

    // 3. Create new detection record (multiple records allowed per day)
    $detection = ReIdBranchDetection::create([
        're_id' => $re_id,
        'branch_id' => $branch_id,
        'device_id' => $device_id,
        'detected_count' => $detected_count,
        'detection_timestamp' => now(),
        'detection_data' => $detection_data  // JSON with confidence, bounding box, etc.
    ]);

    // 4. Return counting summary for this branch
    return [
        're_id' => $re_id,
        'branch_id' => $branch_id,
        'device_id' => $device_id,
        'branch_count' => 1, // Always 1 for each branch that can detect
        'detected_count' => $detected_count, // Usually 1 per detection
        'total_actual_count' => $reIdMaster->total_actual_count,
        'total_detection_count' => $reIdMaster->total_detection_count,
        'can_detect_branch_count' => ReIdBranchDetection::where('re_id', $re_id)
                                                       ->whereDate('detection_timestamp', now()->toDateString())
                                                       ->distinct('branch_id')
                                                       ->count()
    ];
}
```

---

## 📡 Complete API Endpoints

### **1. Device Counting APIs**

#### **POST /api/detection/log**

Log person detection from device

**Payload:**

```json
{
  "re_id": "person_001_abc123",
  "branch_id": 1,
  "device_id": "CAMERA_001",
  "detected_count": 1,
  "detection_data": {
    "confidence": 0.95,
    "bounding_box": { "x": 120, "y": 150, "width": 80, "height": 200 }
  }
}
```

**Response:**

```json
{
  "success": true,
  "data": {
    "re_id": "person_001_abc123",
    "branch_id": 1,
    "device_id": "CAMERA_001",
    "branch_count": 1,
    "detected_count": 1,
    "total_actual_count": 15,
    "total_detection_count": 15,
    "can_detect_branch_count": 2,
    "detection_timestamp": "2024-01-16 14:30:15",
    "branch_info": {
      "branch_name": "Jakarta Central Branch",
      "city_name": "Central Jakarta"
    }
  }
}
```

#### **GET /api/person/{re_id}**

Get info for specific person (RE_ID) across all branches

**Response:**

```json
{
  "success": true,
  "data": {
    "re_id": "person_001_abc123",
    "person_name": "John Doe",
    "total_detection_count": 15,
    "total_actual_count": 15,
    "first_detected_at": "2024-01-16 08:30:00",
    "last_detected_at": "2024-01-16 16:45:00",
    "detected_branches": [
      {
        "branch_id": 1,
        "branch_name": "Jakarta Central",
        "detection_count": 10
      },
      { "branch_id": 2, "branch_name": "Jakarta South", "detection_count": 5 }
    ]
  }
}
```

#### **GET /api/branch/{branch_id}/detections**

Get all person detections for specific branch

#### **GET /api/detection/summary**

Get global detection summary

### **2. Event Management APIs**

#### **POST /api/event/settings**

Configure event settings for branch/device

**Payload:**

```json
{
  "branch_id": 1,
  "device_id": "CAMERA_001",
  "is_active": true,
  "send_image": true,
  "send_message": true,
  "send_notification": true,
  "whatsapp_enabled": true,
  "whatsapp_numbers": ["+628123456789", "+628987654321"],
  "message_template": "Alert from {branch_name}: Person detected at {device_name}"
}
```

#### **POST /api/event/log**

Log an event occurrence

**Payload:**

```json
{
  "branch_id": 1,
  "device_id": "CAMERA_001",
  "re_id": "person_001_abc123",
  "event_type": "detection",
  "detected_count": 1,
  "image_path": "/storage/events/2024/01/16/event_001.jpg",
  "event_data": {
    "confidence": 0.95,
    "person_detected": true,
    "bounding_box": { "x": 120, "y": 150 }
  }
}
```

### **3. CCTV Stream Management APIs**

#### **POST /api/stream/create**

Create/register CCTV stream

**Payload:**

```json
{
  "branch_id": 1,
  "device_id": "CAMERA_001",
  "stream_name": "Main Entrance Camera",
  "stream_url": "rtsp://192.168.1.100:554/stream1",
  "stream_type": "rtsp",
  "stream_username": "admin",
  "stream_password": "password123",
  "position": 1,
  "resolution": "1920x1080",
  "fps": 30
}
```

#### **GET /api/stream/branch/{branch_id}**

Get all streams for a branch

**Response:**

```json
{
  "success": true,
  "data": {
    "branch_id": 1,
    "branch_name": "Jakarta Central Branch",
    "streams": [
      {
        "id": 1,
        "device_id": "CAMERA_001",
        "stream_name": "Main Entrance",
        "stream_url": "rtsp://192.168.1.100:554/stream1",
        "position": 1,
        "status": "online",
        "resolution": "1920x1080",
        "fps": 30
      }
    ]
  }
}
```

### **4. API Credential Management**

#### **POST /api/credentials/create**

Create new API credential

**Payload:**

```json
{
  "credential_name": "Branch Jakarta API Key",
  "branch_id": 1,
  "device_id": null,
  "re_id": null,
  "permissions": {
    "read": true,
    "write": true,
    "delete": false
  },
  "rate_limit": 1000,
  "expires_at": "2025-12-31T23:59:59Z"
}
```

**Response:**

```json
{
  "success": true,
  "data": {
    "id": 2,
    "credential_name": "Branch Jakarta API Key",
    "api_key": "cctv_live_jkt001branch",
    "api_secret": "secret_jkt001secret",
    "branch_id": 1,
    "device_id": null,
    "re_id": null,
    "permissions": { "read": true, "write": true, "delete": false },
    "rate_limit": 1000,
    "expires_at": "2025-12-31 23:59:59"
  }
}
```

---

## 📊 Sample Data Structure

### **Company Groups**

```sql
INSERT INTO company_groups VALUES
(1, 'JKT', 'DKI Jakarta', 'Jakarta Group', 'Jl. Sudirman No.1, Jakarta', '021-12345678', 'jakarta@group.com', 'active', NOW(), NOW()),
(2, 'BDG', 'West Java', 'Bandung Group', 'Jl. Asia Afrika No.1, Bandung', '022-87654321', 'bandung@group.com', 'active', NOW(), NOW()),
(3, 'SBY', 'East Java', 'Surabaya Group', 'Jl. Tunjungan No.1, Surabaya', '031-11223344', 'surabaya@group.com', 'active', NOW(), NOW());
```

### **Company Branches**

```sql
INSERT INTO company_branches VALUES
(1, 1, 'JKT001', 'Jakarta Central Branch', 'Central Jakarta', 'Jl. Thamrin No.1', '021-11111111', 'jakarta.central@branch.com', -6.200000, 106.816666, 'active', NOW(), NOW()),
(2, 1, 'JKT002', 'Jakarta South Branch', 'South Jakarta', 'Jl. Sudirman No.100', '021-22222222', 'jakarta.south@branch.com', -6.261493, 106.810600, 'active', NOW(), NOW()),
(3, 2, 'BDG001', 'Bandung City Branch', 'Bandung', 'Jl. Asia Afrika No.50', '022-33333333', 'bandung.city@branch.com', -6.917464, 107.619125, 'active', NOW(), NOW()),
(4, 3, 'SBY001', 'Surabaya Central Branch', 'Surabaya', 'Jl. Tunjungan No.25', '031-44444444', 'surabaya.central@branch.com', -7.250445, 112.768845, 'active', NOW(), NOW());
```

### **Device Master**

```sql
INSERT INTO device_master VALUES
(1, 'CAMERA_001', 'Jakarta Central - Main Entrance Camera', 'camera', 1, 'active', NOW(), NOW()),
(2, 'CAMERA_002', 'Jakarta Central - Parking Area Camera', 'camera', 1, 'active', NOW(), NOW()),
(3, 'CAMERA_003', 'Jakarta South - Lobby Camera', 'camera', 2, 'active', NOW(), NOW()),
(4, 'SENSOR_001', 'Bandung - Entry Sensor', 'sensor', 3, 'active', NOW(), NOW()),
(5, 'THERMO_001', 'Surabaya - Thermal Camera', 'thermo', 4, 'active', NOW(), NOW());
```

### **Re-ID Master** (Person Registry)

```sql
INSERT INTO re_id_master VALUES
(1, 'person_001_abc123', 'John Doe', '{"clothing_colors": ["blue", "white"], "height": "medium"}', '2024-01-16 08:30:00', '2024-01-16 16:45:00', 15, 15, 'active', NOW(), NOW()),
(2, 'person_002_def456', 'Jane Smith', '{"clothing_colors": ["red", "black"], "height": "tall"}', '2024-01-16 09:00:00', '2024-01-16 15:30:00', 8, 8, 'active', NOW(), NOW()),
(3, 'person_003_ghi789', NULL, '{"clothing_colors": ["green", "white"], "height": "short"}', '2024-01-16 10:15:00', '2024-01-16 14:20:00', 12, 12, 'active', NOW(), NOW());
```

### **Re-ID Branch Detection** (Person Detection Logs)

```sql
INSERT INTO re_id_branch_detection VALUES
-- person_001_abc123 detected at Branch 1 (Jakarta Central)
(1, 'person_001_abc123', 1, 'CAMERA_001', 1, '2024-01-16 08:30:15', '{"confidence": 0.95, "bounding_box": {"x": 120, "y": 150, "width": 80, "height": 200}}', 'active', NOW(), NOW()),
(2, 'person_001_abc123', 1, 'CAMERA_001', 1, '2024-01-16 14:22:30', '{"confidence": 0.92, "bounding_box": {"x": 130, "y": 160, "width": 75, "height": 195}}', 'active', NOW(), NOW()),
(3, 'person_001_abc123', 1, 'CAMERA_002', 1, '2024-01-16 16:45:10', '{"confidence": 0.89, "bounding_box": {"x": 140, "y": 140, "width": 82, "height": 205}}', 'active', NOW(), NOW()),

-- person_001_abc123 detected at Branch 2 (Jakarta South)
(4, 'person_001_abc123', 2, 'CAMERA_003', 1, '2024-01-16 10:15:20', '{"confidence": 0.93, "bounding_box": {"x": 115, "y": 155, "width": 78, "height": 198}}', 'active', NOW(), NOW()),
(5, 'person_001_abc123', 2, 'CAMERA_003', 1, '2024-01-16 15:30:45', '{"confidence": 0.91, "bounding_box": {"x": 125, "y": 148, "width": 80, "height": 202}}', 'active', NOW(), NOW()),

-- person_002_def456 detected at Branch 1 only
(6, 'person_002_def456', 1, 'CAMERA_001', 1, '2024-01-16 09:00:10', '{"confidence": 0.94, "bounding_box": {"x": 200, "y": 180, "width": 70, "height": 210}}', 'active', NOW(), NOW()),
(7, 'person_002_def456', 1, 'CAMERA_002', 1, '2024-01-16 13:45:20', '{"confidence": 0.88, "bounding_box": {"x": 210, "y": 175, "width": 72, "height": 208}}', 'active', NOW(), NOW()),

-- person_003_ghi789 detected at Branch 3 (Bandung)
(8, 'person_003_ghi789', 3, 'SENSOR_001', 1, '2024-01-16 10:15:30', '{"confidence": 0.87, "bounding_box": {"x": 90, "y": 120, "width": 65, "height": 180}}', 'active', NOW(), NOW()),
(9, 'person_003_ghi789', 3, 'SENSOR_001', 1, '2024-01-16 14:20:45', '{"confidence": 0.90, "bounding_box": {"x": 95, "y": 125, "width": 68, "height": 185}}', 'active', NOW(), NOW());
```

### **Branch Event Settings**

```sql
INSERT INTO branch_event_settings VALUES
(1, 1, 'CAMERA_001', true, true, true, true, true, '["+628123456789", "+628987654321"]', 'Alert from {branch_name}: Person detected at {device_name}', 'Person detected at {device_name}', NOW(), NOW()),
(2, 1, 'CAMERA_002', true, true, false, true, false, NULL, 'Motion detected at {device_name}', NULL, NOW(), NOW()),
(3, 2, 'CAMERA_003', true, false, true, true, true, '["+628111222333"]', 'Camera alert: Person detected', 'Alert notification', NOW(), NOW()),
(4, 3, 'SENSOR_001', true, true, true, false, false, NULL, 'Sensor triggered at {device_name}', 'Sensor notification', NOW(), NOW());
```

### **Event Logs**

```sql
INSERT INTO event_logs VALUES
(1, 1, 'CAMERA_001', 'person_001_abc123', 'detection', 1, '/storage/events/2024/01/16/event_001.jpg', true, true, true, '{"confidence": 0.95, "person_detected": true, "bounding_box": {"x": 120, "y": 150}}', '2024-01-16 14:30:00', NOW()),
(2, 1, 'CAMERA_002', NULL, 'motion', 0, '/storage/events/2024/01/16/event_002.jpg', true, true, false, '{"confidence": 0.82, "motion_area": "left_side"}', '2024-01-16 15:15:00', NOW()),
(3, 2, 'CAMERA_003', 'person_001_abc123', 'detection', 1, '/storage/events/2024/01/16/event_003.jpg', false, true, true, '{"confidence": 0.93, "person_detected": true}', '2024-01-16 16:20:00', NOW()),
(4, 1, 'CAMERA_001', 'person_002_def456', 'alert', 1, '/storage/events/2024/01/16/event_004.jpg', true, true, true, '{"confidence": 0.94, "alert_type": "unauthorized_person"}', '2024-01-16 17:30:00', NOW());
```

### **API Credentials**

```sql
INSERT INTO api_credentials VALUES
(1, 'Global Admin API Key', 'cctv_live_abc123xyz789def456', 'secret_ghi789jkl012mno345', NULL, NULL, NULL, true, '{"read": true, "write": true, "delete": true}', 5000, '2024-01-16 15:30:00', NULL, 1, NOW(), NOW()),
(2, 'Branch Jakarta API Key', 'cctv_live_jkt001branch', 'secret_jkt001secret', 1, NULL, NULL, true, '{"read": true, "write": true, "delete": false}', 1000, '2024-01-16 14:20:00', '2025-12-31 23:59:59', 1, NOW(), NOW()),
(3, 'Device Specific Key', 'cctv_live_dev001camera', 'secret_dev001secret', NULL, 'CAMERA_001', NULL, true, '{"read": true, "write": false, "delete": false}', 500, '2024-01-16 13:10:00', '2024-12-31 23:59:59', 1, NOW(), NOW()),
(4, 'Person Tracking Key', 'cctv_live_person001', 'secret_person001', NULL, NULL, 'person_001_abc123', true, '{"read": true, "write": false, "delete": false}', 300, '2024-01-16 12:00:00', '2024-12-31 23:59:59', 1, NOW(), NOW());
```

### **CCTV Streams**

```sql
INSERT INTO cctv_streams VALUES
(1, 1, 'CAMERA_001', 'Jakarta Central - Main Entrance', 'rtsp://192.168.1.100:554/stream1', 'rtsp', 'admin', 'encrypted_password_123', 554, true, 1, '1920x1080', 30, 4096, '2024-01-16 16:00:00', 'online', NOW(), NOW()),
(2, 1, 'CAMERA_002', 'Jakarta Central - Parking Area', 'rtsp://192.168.1.101:554/stream1', 'rtsp', 'admin', 'encrypted_password_456', 554, true, 2, '1280x720', 25, 2048, '2024-01-16 16:00:05', 'online', NOW(), NOW()),
(3, 2, 'CAMERA_003', 'Jakarta South - Lobby', 'rtsp://192.168.2.100:554/stream1', 'rtsp', 'admin', 'encrypted_password_789', 554, true, 1, '1920x1080', 30, 4096, '2024-01-16 16:00:10', 'online', NOW(), NOW()),
(4, 3, 'SENSOR_001', 'Bandung - Entry Sensor (No Stream)', NULL, NULL, NULL, NULL, NULL, false, 3, NULL, NULL, NULL, '2024-01-16 15:55:00', 'offline', NOW(), NOW()),
(5, 4, 'THERMO_001', 'Surabaya - Thermal Camera', 'rtsp://192.168.4.100:554/stream1', 'rtsp', 'admin', 'encrypted_password_321', 554, true, 4, '640x480', 15, 1024, '2024-01-16 16:00:15', 'online', NOW(), NOW());
```

### **Counting Reports**

```sql
INSERT INTO counting_reports VALUES
(1, 'daily', '2024-01-16', NULL, 5, 35, 10, 3, 3, '{"top_persons":[{"re_id":"person_001_abc123","count":15}],"top_branches":[{"branch_id":1,"detections":20}],"peak_hour":"14:00"}', '2024-01-16 23:59:00', NOW()),
(2, 'daily', '2024-01-16', 1, 2, 20, 7, 2, 2, '{"persons":[{"re_id":"person_001_abc123","count":15},{"re_id":"person_002_def456","count":5}],"peak_hour":"14:00"}', '2024-01-16 23:59:05', NOW()),
(3, 'daily', '2024-01-16', 2, 1, 5, 2, 1, 1, '{"persons":[{"re_id":"person_001_abc123","count":5}],"peak_hour":"15:00"}', '2024-01-16 23:59:10', NOW()),
(4, 'daily', '2024-01-16', 3, 1, 10, 1, 1, 1, '{"persons":[{"re_id":"person_003_ghi789","count":10}],"peak_hour":"12:00"}', '2024-01-16 23:59:15', NOW());
```

### **CCTV Layout Settings**

```sql
INSERT INTO cctv_layout_settings VALUES
(1, 'Default 4-Window Layout', '4-window', 4, true, true, 'Standard quad view layout for main monitoring', 1, NOW(), NOW()),
(2, 'Extended 6-Window Layout', '6-window', 6, false, true, 'Extended view for comprehensive monitoring', 1, NOW(), NOW()),
(3, 'Maximum 8-Window Layout', '8-window', 8, false, true, 'Maximum view for complete surveillance coverage', 1, NOW(), NOW());
```

### **CCTV Position Settings**

```sql
INSERT INTO cctv_position_settings VALUES
-- Default 4-Window Layout Positions
(1, 1, 1, 1, 'CAMERA_001', 'Main Entrance', true, false, 30, '1920x1080', 'high', NOW(), NOW()),
(2, 1, 2, 1, 'CAMERA_002', 'Parking Area', true, false, 30, '1920x1080', 'high', NOW(), NOW()),
(3, 1, 3, 2, 'CAMERA_003', 'Lobby View', true, true, 60, '1280x720', 'medium', NOW(), NOW()),
(4, 1, 4, 3, 'SENSOR_001', 'Entry Sensor', true, false, 30, '640x480', 'low', NOW(), NOW()),

-- Extended 6-Window Layout Positions
(5, 2, 1, 1, 'CAMERA_001', 'Main Entrance', true, false, 30, '1920x1080', 'high', NOW(), NOW()),
(6, 2, 2, 1, 'CAMERA_002', 'Parking Area', true, false, 30, '1920x1080', 'high', NOW(), NOW()),
(7, 2, 3, 2, 'CAMERA_003', 'Lobby View', true, true, 60, '1280x720', 'medium', NOW(), NOW()),
(8, 2, 4, 3, 'SENSOR_001', 'Entry Sensor', true, false, 30, '640x480', 'low', NOW(), NOW()),
(9, 2, 5, 4, 'THERMO_001', 'Thermal Camera', true, false, 30, '640x480', 'medium', NOW(), NOW()),
(10, 2, 6, 1, 'CAMERA_001', 'Main Entrance (Alt)', true, true, 45, '1920x1080', 'high', NOW(), NOW()),

-- Maximum 8-Window Layout Positions
(11, 3, 1, 1, 'CAMERA_001', 'Main Entrance', true, false, 30, '1920x1080', 'high', NOW(), NOW()),
(12, 3, 2, 1, 'CAMERA_002', 'Parking Area', true, false, 30, '1920x1080', 'high', NOW(), NOW()),
(13, 3, 3, 2, 'CAMERA_003', 'Lobby View', true, true, 60, '1280x720', 'medium', NOW(), NOW()),
(14, 3, 4, 3, 'SENSOR_001', 'Entry Sensor', true, false, 30, '640x480', 'low', NOW(), NOW()),
(15, 3, 5, 4, 'THERMO_001', 'Thermal Camera', true, false, 30, '640x480', 'medium', NOW(), NOW()),
(16, 3, 6, 1, 'CAMERA_001', 'Main Entrance (Alt)', true, true, 45, '1920x1080', 'high', NOW(), NOW()),
(17, 3, 7, 2, 'CAMERA_003', 'Lobby View (Alt)', true, true, 90, '1280x720', 'medium', NOW(), NOW()),
(18, 3, 8, 3, 'SENSOR_001', 'Entry Sensor (Alt)', true, false, 30, '640x480', 'low', NOW(), NOW());
```

---

## 🔍 Advanced Query Examples

### **Get RE_ID Summary**

```sql
SELECT
    rim.re_id,
    rim.person_name,
    rim.total_actual_count,
    COUNT(DISTINCT rbd.branch_id) as can_detect_branch_count,
    SUM(rbd.detected_count) as total_detected_count
FROM re_id_master rim
LEFT JOIN re_id_branch_detection rbd ON rim.re_id = rbd.re_id
    AND DATE(rbd.detection_timestamp) = CURDATE()
WHERE rim.status = 'active'
GROUP BY rim.re_id, rim.person_name, rim.total_actual_count;
```

### **Get Branch Performance**

```sql
SELECT
    cb.branch_name,
    cb.city_name,
    COUNT(DISTINCT rbd.re_id) as unique_re_id_count,
    SUM(rbd.detected_count) as total_detected_count
FROM company_branches cb
LEFT JOIN re_id_branch_detection rbd ON cb.id = rbd.branch_id
    AND DATE(rbd.detection_timestamp) = CURDATE()
WHERE cb.status = 'active'
GROUP BY cb.id, cb.branch_name, cb.city_name;
```

### **Get Complete Event Report**

```sql
SELECT
    el.id as event_id,
    el.event_type,
    el.detected_count,
    cb.branch_name,
    dm.device_name,
    dm.device_id,
    rim.re_id,
    rim.person_name,
    el.event_timestamp,
    el.image_path,
    el.image_sent,
    el.message_sent,
    el.notification_sent,
    bes.whatsapp_enabled,
    bes.whatsapp_numbers
FROM event_logs el
JOIN company_branches cb ON el.branch_id = cb.id
JOIN device_master dm ON el.device_id = dm.device_id
LEFT JOIN re_id_master rim ON el.re_id = rim.re_id
LEFT JOIN branch_event_settings bes ON el.branch_id = bes.branch_id AND el.device_id = bes.device_id
WHERE el.event_timestamp >= CURDATE()
ORDER BY el.event_timestamp DESC
LIMIT 50;
```

### **Get Branch Performance with Events**

```sql
SELECT
    cb.branch_name,
    cb.city_name,
    COUNT(DISTINCT rbd.device_id) as unique_devices,
    COUNT(DISTINCT rbd.re_id) as unique_persons,
    SUM(rbd.detected_count) as total_detections,
    COUNT(DISTINCT el.id) as total_events,
    SUM(CASE WHEN el.notification_sent = true THEN 1 ELSE 0 END) as notifications_sent
FROM company_branches cb
LEFT JOIN re_id_branch_detection rbd ON cb.id = rbd.branch_id
    AND DATE(rbd.detection_timestamp) = CURDATE()
LEFT JOIN event_logs el ON cb.id = el.branch_id
    AND DATE(el.event_timestamp) = CURDATE()
WHERE cb.status = 'active'
GROUP BY cb.id, cb.branch_name, cb.city_name
ORDER BY total_detections DESC;
```

### **Get API Usage Statistics**

```sql
SELECT
    ac.credential_name,
    ac.api_key,
    cb.branch_name,
    dm.device_id,
    rim.re_id,
    COUNT(arl.id) as total_requests,
    AVG(arl.response_time_ms) as avg_response_time,
    SUM(CASE WHEN arl.response_status = 200 THEN 1 ELSE 0 END) as successful_requests,
    SUM(CASE WHEN arl.response_status >= 400 THEN 1 ELSE 0 END) as failed_requests,
    ac.rate_limit,
    ac.last_used_at
FROM api_credentials ac
LEFT JOIN company_branches cb ON ac.branch_id = cb.id
LEFT JOIN device_master dm ON ac.device_id = dm.device_id
LEFT JOIN re_id_master rim ON ac.re_id = rim.re_id
LEFT JOIN api_request_logs arl ON ac.id = arl.api_credential_id
    AND arl.created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
WHERE ac.is_active = true
GROUP BY ac.id, ac.credential_name, ac.api_key, cb.branch_name, dm.device_id, rim.re_id, ac.rate_limit, ac.last_used_at
ORDER BY total_requests DESC;
```

### **Get CCTV Stream Status Dashboard**

```sql
SELECT
    cb.branch_name,
    cs.stream_name,
    cs.device_id,
    cs.stream_type,
    cs.position,
    cs.status,
    cs.resolution,
    cs.fps,
    cs.last_checked_at,
    TIMESTAMPDIFF(MINUTE, cs.last_checked_at, NOW()) as minutes_since_check,
    dm.device_type
FROM cctv_streams cs
JOIN company_branches cb ON cs.branch_id = cb.id
JOIN device_master dm ON cs.device_id = dm.device_id
WHERE cs.is_active = true
ORDER BY cb.branch_name, cs.position;
```

### **Get Hourly Detection Trend**

```sql
SELECT
    DATE(event_timestamp) as date,
    HOUR(event_timestamp) as hour,
    COUNT(*) as event_count,
    SUM(detected_count) as total_detections,
    COUNT(DISTINCT re_id) as unique_devices,
    COUNT(DISTINCT branch_id) as active_branches
FROM event_logs
WHERE event_timestamp >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
GROUP BY DATE(event_timestamp), HOUR(event_timestamp)
ORDER BY date DESC, hour DESC;
```

---

## 🎯 Key Features Summary

### **✅ RE_ID Integration**

- **device_master**: Primary RE_ID registry
- **device_branch_detection**: RE_ID detection logs
- **branch_event_settings**: RE_ID event configuration
- **event_logs**: RE_ID event tracking
- **api_credentials**: RE_ID scoping
- **cctv_streams**: RE_ID stream management

### **✅ WhatsApp Simple ON/OFF**

- **branch_event_settings**: `whatsapp_enabled` boolean
- **whatsapp_settings**: Provider configuration
- **event_logs**: `notification_sent` boolean
- **No delivery tracking**: Fire and forget approach

### **✅ Enhanced Counting Logic**

- Each branch that can read RE_ID = 1 count
- Actual detected count tracked separately
- Total actual count aggregated in master table

### **✅ Complete API Coverage**

- Device counting with RE_ID
- Event management with RE_ID
- CCTV stream management with RE_ID
- API credential scoping with RE_ID

### **✅ Performance Optimizations**

- Indexed queries for all foreign keys
- Report caching for fast retrieval
- Partitioning strategy for large tables
- Archiving strategy for old data

---

## 📊 Database Summary

| Category     | Tables | Key Features                                    |
| ------------ | ------ | ----------------------------------------------- |
| **Core**     | 5      | Groups → Branches → Devices + Re-ID → Detection |
| **Events**   | 2      | Event settings + Event logs (with RE_ID)        |
| **Security** | 3      | API credentials + logs + users                  |
| **CCTV**     | 1      | Stream management (with RE_ID)                  |
| **Reports**  | 1      | Pre-computed report cache                       |
| **Layout**   | 2      | CCTV layout management (4/6/8 windows)          |
| **TOTAL**    | **16** | **Complete RE_ID + Layout Management**          |

---

## 🔧 Performance Optimization

### **1. Query Optimization**

#### **Composite Indexes (PostgreSQL Best Practice)**

```sql
-- ✅ Already created in table definitions above, but here are additional examples:

-- ✅ BEST PRACTICE: Covering index for frequently accessed columns
CREATE INDEX idx_re_id_branch_detection_covering
ON re_id_branch_detection(re_id, branch_id, detection_timestamp, detected_count);

-- ✅ BEST PRACTICE: Partial index for active records only (PostgreSQL specific)
CREATE INDEX idx_device_master_active_devices
ON device_master(status, branch_id)
WHERE status = 'active';

-- ✅ BEST PRACTICE: Partial index for today's detections
CREATE INDEX idx_re_id_branch_detection_today
ON re_id_branch_detection(branch_id, re_id, detected_count)
WHERE detection_timestamp >= CURRENT_DATE;

-- ✅ PostgreSQL B-tree index for sorting
CREATE INDEX idx_event_logs_timestamp_desc
ON event_logs(event_timestamp DESC);

-- ✅ PostgreSQL multi-column index for complex queries
CREATE INDEX idx_event_logs_complex
ON event_logs(branch_id, device_id, event_type, event_timestamp)
WHERE notification_sent = true;
```

**PostgreSQL Index Types:**

- **B-tree** (default): For equality and range queries
- **GIN**: For JSONB, arrays, full-text search
- **GiST**: For geometric data, range types
- **BRIN**: For very large tables with natural ordering
- **Hash**: For equality comparisons only

#### **Query Optimization Examples**

**1. Use Eager Loading to Avoid N+1:**

```php
// BAD - N+1 query problem
$detections = ReIdBranchDetection::all();
foreach ($detections as $detection) {
    echo $detection->branch->branch_name;  // N queries
}

// GOOD - Eager loading
$detections = ReIdBranchDetection::with(['branch', 'device', 'reId'])
                                 ->get();
```

**2. Use Select to Reduce Data Transfer:**

```php
// BAD - Fetch all columns
$users = User::all();

// GOOD - Only fetch needed columns
$users = User::select('id', 'name', 'email')->get();
```

**3. Use Chunking for Large Datasets:**

```php
// Process large datasets in chunks
ReIdBranchDetection::whereDate('detection_timestamp', now()->subDays(30))
    ->chunk(1000, function ($detections) {
        foreach ($detections as $detection) {
            // Process each detection
        }
    });
```

**4. Use Query Builder for Complex Queries:**

```php
// More efficient than Eloquent for aggregations
$summary = DB::table('re_id_branch_detection')
    ->select(
        'branch_id',
        DB::raw('COUNT(DISTINCT re_id) as unique_persons'),
        DB::raw('COUNT(*) as total_detections')
    )
    ->whereDate('detection_timestamp', now()->toDateString())
    ->groupBy('branch_id')
    ->get();
```

**5. Use Database Transactions (BEST PRACTICE):**

```php
// ✅ BEST PRACTICE: Wrap multiple operations in transaction
use Illuminate\Support\Facades\DB;

DB::transaction(function () use ($reId, $branchId, $deviceId, $detectedCount) {
    // 1. Update or create re_id_master
    $reIdMaster = ReIdMaster::firstOrCreate(
        ['re_id' => $reId],
        ['first_detected_at' => now()]
    );

    // 2. Update counts
    $reIdMaster->increment('total_detection_count', 1);
    $reIdMaster->increment('total_actual_count', $detectedCount);
    $reIdMaster->update(['last_detected_at' => now()]);

    // 3. Create detection record
    ReIdBranchDetection::create([
        're_id' => $reId,
        'branch_id' => $branchId,
        'device_id' => $deviceId,
        'detected_count' => $detectedCount,
        'detection_timestamp' => now(),
    ]);

    // 4. Log event if configured
    if ($eventSettings->is_active) {
        EventLog::create([...]);
    }
}, 5); // ✅ Retry transaction 5 times on deadlock
```

**6. Use Prepared Statements (Automatic in Laravel):**

```php
// ✅ BEST PRACTICE: Laravel automatically uses prepared statements
// This is safe from SQL injection
$detections = DB::table('re_id_branch_detection')
    ->where('re_id', $reId)  // Automatically parameterized
    ->where('branch_id', $branchId)
    ->get();

// ❌ BAD: Raw queries without bindings
DB::select("SELECT * FROM re_id_branch_detection WHERE re_id = '$reId'");

// ✅ GOOD: Raw queries with bindings
DB::select("SELECT * FROM re_id_branch_detection WHERE re_id = ?", [$reId]);
```

### **2. Connection Pooling**

#### **Database Connection Configuration (PostgreSQL)**

```php
// config/database.php
'pgsql' => [
    'driver' => 'pgsql',
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', '5432'),  // ✅ PostgreSQL default port
    'database' => env('DB_DATABASE', 'cctv_dashboard'),
    'username' => env('DB_USERNAME', 'postgres'),
    'password' => env('DB_PASSWORD', ''),
    'charset' => 'utf8',
    'prefix' => '',
    'prefix_indexes' => true,
    'search_path' => 'public',  // ✅ PostgreSQL schema
    'sslmode' => env('DB_SSLMODE', 'prefer'),  // ✅ SSL connection

    // ✅ BEST PRACTICE: PDO options for performance
    'options' => [
        PDO::ATTR_EMULATE_PREPARES => false,  // Use native prepared statements
        PDO::ATTR_STRINGIFY_FETCHES => false,  // Return actual data types
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,  // Throw exceptions on errors
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,  // Fetch as array
    ],

    // ✅ BEST PRACTICE: Connection settings
    'sticky' => true,  // Use write connection for reads after writes
    'read' => [
        'host' => [
            env('DB_READ_HOST_1', '127.0.0.1'),
            env('DB_READ_HOST_2', '127.0.0.1'),
        ],
    ],
    'write' => [
        'host' => [
            env('DB_WRITE_HOST', '127.0.0.1'),
        ],
    ],

    // ✅ PostgreSQL specific settings
    'application_name' => env('APP_NAME', 'CCTV_Dashboard'),
    'synchronous_commit' => 'on',  // Ensure data durability
],
```

**PostgreSQL Advantages:**

- ✅ **JSONB**: Binary JSON with indexing support (faster than JSON)
- ✅ **GIN Indexes**: Generalized Inverted Index for JSONB queries
- ✅ **INET Type**: Native IP address type
- ✅ **Partial Indexes**: Index only specific rows (WHERE clause)
- ✅ **Advanced Data Types**: Arrays, hstore, full-text search
- ✅ **Better Concurrency**: MVCC (Multi-Version Concurrency Control)
- ✅ **Table Partitioning**: Native support for large tables

**Note:** For high-performance applications, consider:

- **PgBouncer**: Connection pooling for PostgreSQL
- **Laravel Octane**: With Swoole or RoadRunner for persistent connections
- **Read Replicas**: For read-heavy workloads

#### **Queue Configuration for Background Jobs**

```php
// config/queue.php
'connections' => [
    'database' => [
        'driver' => 'database',
        'table' => 'jobs',
        'queue' => 'default',
        'retry_after' => 90,
        'after_commit' => false,  // ✅ Process immediately
    ],
],

// ✅ BEST PRACTICE: Use job classes for background processing
// app/Jobs/ProcessDetectionJob.php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessDetectionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;  // ✅ Retry 3 times if failed
    public $timeout = 60;  // ✅ Timeout after 60 seconds

    public function __construct(
        public string $reId,
        public int $branchId,
        public string $deviceId,
        public int $detectedCount,
        public ?array $detectionData = null
    ) {}

    public function handle()
    {
        // Process detection in background
        ReIdBranchDetection::create([
            're_id' => $this->reId,
            'branch_id' => $this->branchId,
            'device_id' => $this->deviceId,
            'detected_count' => $this->detectedCount,
            'detection_timestamp' => now(),
            'detection_data' => $this->detectionData,
        ]);

        // Job completed successfully
    }

    public function failed(\Throwable $exception)
    {
        // ✅ BEST PRACTICE: Handle failed jobs
        Log::error('Detection processing failed', [
            're_id' => $this->reId,
            'error' => $exception->getMessage()
        ]);
    }
}

// Dispatch job
ProcessDetectionJob::dispatch($reId, $branchId, $deviceId, $detectedCount, $detectionData);
```

---

## 📦 Migration Order & Seeding Guide

### **Migration Order (Execute in this sequence)**

```bash
# 1. Core tables (no dependencies)
php artisan migrate --path=/database/migrations/2024_01_01_000001_create_company_groups_table.php
php artisan migrate --path=/database/migrations/2024_01_01_000002_create_company_branches_table.php

# 2. Device and Person tables
php artisan migrate --path=/database/migrations/2024_01_01_000003_create_device_master_table.php
php artisan migrate --path=/database/migrations/2024_01_01_000004_create_re_id_master_table.php

# 3. Detection table (depends on branches, devices, re_id)
php artisan migrate --path=/database/migrations/2024_01_01_000005_create_re_id_branch_detection_table.php

# 4. Event management tables
php artisan migrate --path=/database/migrations/2024_01_01_000006_create_branch_event_settings_table.php
php artisan migrate --path=/database/migrations/2024_01_01_000007_create_event_logs_table.php

# 5. API security tables (depends on users - Laravel default)
php artisan migrate --path=/database/migrations/2024_01_01_000008_create_api_credentials_table.php
php artisan migrate --path=/database/migrations/2024_01_01_000009_create_api_request_logs_table.php

# 6. CCTV and reporting tables
php artisan migrate --path=/database/migrations/2024_01_01_000010_create_cctv_streams_table.php
php artisan migrate --path=/database/migrations/2024_01_01_000011_create_counting_reports_table.php

# 7. CCTV Layout Management tables
php artisan migrate --path=/database/migrations/2024_01_01_000012_create_cctv_layout_settings_table.php
php artisan migrate --path=/database/migrations/2024_01_01_000013_create_cctv_position_settings_table.php
```

### **Seeding Order (Execute in this sequence)**

```bash
# 1. Seed users first (admin, operators)
php artisan db:seed --class=UserSeeder

# 2. Seed company structure
php artisan db:seed --class=CompanyGroupSeeder
php artisan db:seed --class=CompanyBranchSeeder

# 3. Seed devices
php artisan db:seed --class=DeviceMasterSeeder

# 4. Seed Re-ID master (persons)
php artisan db:seed --class=ReIdMasterSeeder

# 5. Seed configurations
php artisan db:seed --class=BranchEventSettingsSeeder

# 6. Seed sample detections and events
php artisan db:seed --class=ReIdBranchDetectionSeeder
php artisan db:seed --class=EventLogSeeder

# 7. Seed API credentials
php artisan db:seed --class=ApiCredentialSeeder

# 8. Seed CCTV streams
php artisan db:seed --class=CctvStreamSeeder

# 9. Seed CCTV Layout Management
php artisan db:seed --class=CctvLayoutSettingsSeeder
php artisan db:seed --class=CctvPositionSettingsSeeder

# Or run all at once
php artisan db:seed
```

### **Complete Setup Commands**

```bash
# Fresh installation
php artisan migrate:fresh --seed

# Rollback and re-migrate
php artisan migrate:rollback
php artisan migrate
php artisan db:seed

# Reset everything
php artisan migrate:fresh
php artisan db:seed
php artisan cache:clear
php artisan config:clear
```

---

## 🔗 Foreign Key Cascade Behavior

### **ON DELETE CASCADE vs ON DELETE SET NULL**

#### **Use ON DELETE CASCADE when:**

Child records should be **automatically deleted** when parent is deleted.

**Examples:**

```sql
-- Branch deleted → All its detections should be deleted
FOREIGN KEY (branch_id) REFERENCES company_branches(id) ON DELETE CASCADE

-- Device deleted → All its streams should be deleted
FOREIGN KEY (device_id) REFERENCES device_master(device_id) ON DELETE CASCADE

-- Event deleted → All its related data should be deleted
FOREIGN KEY (event_log_id) REFERENCES event_logs(id) ON DELETE CASCADE
```

#### **Use ON DELETE SET NULL when:**

Child records should be **preserved** but reference should be removed.

**Examples:**

```sql
-- Person (re_id) deleted → Event logs should remain but without person reference
FOREIGN KEY (re_id) REFERENCES re_id_master(re_id) ON DELETE SET NULL

-- User deleted → API credentials should remain but without creator reference
FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
```

### **Complete Foreign Key Reference Table**

| Child Table                | Parent Table     | Column            | Cascade Behavior | Reason                          |
| -------------------------- | ---------------- | ----------------- | ---------------- | ------------------------------- |
| **company_branches**       | company_groups   | group_id          | CASCADE          | Branch belongs to group         |
| **device_master**          | company_branches | branch_id         | CASCADE          | Device belongs to branch        |
| **re_id_branch_detection** | company_branches | branch_id         | CASCADE          | Detection belongs to branch     |
| **re_id_branch_detection** | re_id_master     | re_id             | CASCADE          | Detection needs re_id           |
| **re_id_branch_detection** | device_master    | device_id         | CASCADE          | Detection needs device          |
| **branch_event_settings**  | company_branches | branch_id         | CASCADE          | Settings belong to branch       |
| **branch_event_settings**  | device_master    | device_id         | CASCADE          | Settings belong to device       |
| **event_logs**             | company_branches | branch_id         | CASCADE          | Event belongs to branch         |
| **event_logs**             | device_master    | device_id         | CASCADE          | Event needs device              |
| **event_logs**             | re_id_master     | re_id             | **SET NULL**     | Keep event if person deleted    |
| **api_credentials**        | company_branches | branch_id         | CASCADE          | Credential scoped to branch     |
| **api_credentials**        | device_master    | device_id         | CASCADE          | Credential scoped to device     |
| **api_credentials**        | re_id_master     | re_id             | CASCADE          | Credential scoped to person     |
| **api_credentials**        | users            | created_by        | **SET NULL**     | Keep credential if user deleted |
| **api_request_logs**       | api_credentials  | api_credential_id | CASCADE          | Log belongs to credential       |
| **cctv_streams**           | company_branches | branch_id         | CASCADE          | Stream belongs to branch        |
| **cctv_streams**           | device_master    | device_id         | CASCADE          | Stream belongs to device        |
| **counting_reports**       | company_branches | branch_id         | CASCADE          | Report belongs to branch        |

### **Migration Example**

```php
Schema::create('event_logs', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('branch_id');
    $table->string('device_id', 50);
    $table->string('re_id', 100)->nullable();

    // CASCADE - Delete event if branch/device deleted
    $table->foreign('branch_id')
          ->references('id')->on('company_branches')
          ->onDelete('cascade');

    $table->foreign('device_id')
          ->references('device_id')->on('device_master')
          ->onDelete('cascade');

    // SET NULL - Keep event if person deleted
    $table->foreign('re_id')
          ->references('re_id')->on('re_id_master')
          ->onDelete('set null');

    $table->timestamps();
});
```

---

## 🔐 Authentication & Authorization

### **User Roles**

```sql
-- Add role column to users table
ALTER TABLE users ADD COLUMN role ENUM('admin', 'operator', 'viewer') DEFAULT 'viewer';
```

#### **Role Definitions:**

| Role         | Description          | Access Level                                           |
| ------------ | -------------------- | ------------------------------------------------------ |
| **admin**    | System Administrator | Full access to all features, user management, settings |
| **operator** | Branch Operator      | Manage devices, view live streams, acknowledge events  |
| **viewer**   | Read-only User       | View dashboards, reports, and live streams only        |

#### **Role Permissions:**

**Admin:**

- ✅ Full CRUD on all modules
- ✅ User management
- ✅ System settings
- ✅ API credential management
- ✅ Branch/device configuration
- ✅ View all reports and analytics

**Operator:**

- ✅ View assigned branches
- ✅ Manage devices in assigned branches
- ✅ View live CCTV streams
- ✅ Acknowledge events/alerts
- ✅ View branch reports
- ❌ User management
- ❌ System settings
- ❌ API credentials

**Viewer:**

- ✅ View dashboards
- ✅ View reports
- ✅ View live streams (read-only)
- ❌ Any modifications
- ❌ Settings
- ❌ User management

### **User Seeder Example**

```php
// database/seeders/UserSeeder.php
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Create Admin
        User::create([
            'name' => 'System Admin',
            'email' => 'admin@cctv.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Create Operators
        User::create([
            'name' => 'Jakarta Operator',
            'email' => 'operator.jakarta@cctv.com',
            'password' => Hash::make('password'),
            'role' => 'operator',
        ]);

        User::create([
            'name' => 'Bandung Operator',
            'email' => 'operator.bandung@cctv.com',
            'password' => Hash::make('password'),
            'role' => 'operator',
        ]);

        // Create Viewers
        User::create([
            'name' => 'Dashboard Viewer',
            'email' => 'viewer@cctv.com',
            'password' => Hash::make('password'),
            'role' => 'viewer',
        ]);
    }
}
```

### **Role Middleware**

```php
// app/Http/Middleware/CheckRole.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!$request->user() || !in_array($request->user()->role, $roles)) {
            abort(403, 'Unauthorized access');
        }

        return $next($request);
    }
}

// Register in app/Http/Kernel.php
protected $routeMiddleware = [
    'role' => \App\Http\Middleware\CheckRole::class,
];

// Usage in routes
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/users', [UserController::class, 'index']);
});

Route::middleware(['auth', 'role:admin,operator'])->group(function () {
    Route::get('/devices', [DeviceController::class, 'index']);
});
```

### **API Authentication Methods**

#### **1. Laravel Sanctum (Recommended for SPA/Mobile)**

**Installation:**

```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

**Configuration:**

```php
// config/sanctum.php
'expiration' => 60 * 24 * 7, // 7 days

// app/Http/Kernel.php
'api' => [
    \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
    'throttle:api',
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
],
```

**Usage:**

```php
// Login and generate token
Route::post('/login', function (Request $request) {
    $user = User::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    $token = $user->createToken('api-token', ['role:' . $user->role])->plainTextToken;

    return response()->json([
        'token' => $token,
        'user' => $user
    ]);
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/detection/log', [DetectionController::class, 'log']);
});
```

**Client Usage:**

```javascript
// Store token
localStorage.setItem("api_token", response.token);

// Use in requests
fetch("/api/detection/log", {
  method: "POST",
  headers: {
    Authorization: `Bearer ${localStorage.getItem("api_token")}`,
    "Content-Type": "application/json",
  },
  body: JSON.stringify(data),
});
```

#### **2. API Key Authentication (For External Systems)**

**Middleware:**

```php
// app/Http/Middleware/ApiKeyAuth.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ApiCredential;

class ApiKeyAuth
{
    public function handle(Request $request, Closure $next)
    {
        $apiKey = $request->header('X-API-Key');
        $apiSecret = $request->header('X-API-Secret');

        if (!$apiKey || !$apiSecret) {
            return response()->json(['message' => 'API credentials required'], 401);
        }

        $credential = ApiCredential::where('api_key', $apiKey)
                                   ->where('api_secret', $apiSecret)
                                   ->where('is_active', true)
                                   ->first();

        if (!$credential) {
            return response()->json(['message' => 'Invalid API credentials'], 401);
        }

        // Check expiration
        if ($credential->expires_at && $credential->expires_at < now()) {
            return response()->json(['message' => 'API credentials expired'], 401);
        }

        // Update last used
        $credential->update(['last_used_at' => now()]);

        // Add credential to request
        $request->merge(['api_credential' => $credential]);

        return $next($request);
    }
}
```

**Usage:**

```php
// routes/api.php
Route::middleware('api.key')->group(function () {
    Route::post('/detection/log', [DetectionController::class, 'log']);
    Route::get('/person/{re_id}', [PersonController::class, 'show']);
});

// Client usage
curl -X POST https://api.cctv.com/api/detection/log \
  -H "X-API-Key: cctv_live_abc123xyz789def456" \
  -H "X-API-Secret: secret_ghi789jkl012mno345" \
  -H "Content-Type: application/json" \
  -d '{"re_id": "person_001_abc123", "branch_id": 1, "device_id": "CAMERA_001"}'
```

#### **3. Rate Limiting (BEST PRACTICE)**

```php
// app/Providers/RouteServiceProvider.php
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

protected function configureRateLimiting()
{
    // ✅ BEST PRACTICE: Different rate limits for different user types
    RateLimiter::for('api', function (Request $request) {
        $apiCredential = $request->get('api_credential');

        if ($apiCredential) {
            return Limit::perHour($apiCredential->rate_limit)
                        ->by($apiCredential->api_key)
                        ->response(function () use ($apiCredential) {
                            return response()->json([
                                'message' => 'Rate limit exceeded',
                                'limit' => $apiCredential->rate_limit,
                                'retry_after' => 3600
                            ], 429);
                        });
        }

        return Limit::perMinute(60)->by($request->ip());
    });

    // ✅ BEST PRACTICE: Separate rate limit for authenticated users
    RateLimiter::for('api-auth', function (Request $request) {
        return $request->user()
            ? Limit::perMinute(100)->by($request->user()->id)
            : Limit::perMinute(10)->by($request->ip());
    });
}
```

#### **4. Input Validation (BEST PRACTICE)**

```php
// ✅ BEST PRACTICE: Use Form Requests for validation
// app/Http/Requests/StoreDetectionRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDetectionRequest extends FormRequest
{
    public function authorize()
    {
        // Check if user has permission or valid API credential
        return $this->user() || $this->has('api_credential');
    }

    public function rules()
    {
        return [
            're_id' => 'required|string|max:100',
            'branch_id' => 'required|integer|exists:company_branches,id',
            'device_id' => 'required|string|max:50|exists:device_master,device_id',
            'detected_count' => 'required|integer|min:0|max:1000',
            'detection_data' => 'nullable|array',
            'detection_data.confidence' => 'nullable|numeric|min:0|max:1',
            'detection_data.bounding_box' => 'nullable|array',
            'detection_data.bounding_box.x' => 'required_with:detection_data.bounding_box|integer|min:0',
            'detection_data.bounding_box.y' => 'required_with:detection_data.bounding_box|integer|min:0',
        ];
    }

    public function messages()
    {
        return [
            're_id.required' => 'Re-identification ID is required',
            'branch_id.exists' => 'Invalid branch ID',
            'device_id.exists' => 'Invalid device ID',
        ];
    }
}

// Usage in controller
public function store(StoreDetectionRequest $request)
{
    // Data is already validated
    $validated = $request->validated();

    // ✅ BEST PRACTICE: Use try-catch for error handling
    try {
        // Process detection
        ProcessDetectionJob::dispatch(
            $validated['re_id'],
            $validated['branch_id'],
            $validated['device_id'],
            $validated['detected_count'],
            $validated['detection_data'] ?? null
        );

        // ✅ BEST PRACTICE: Consistent API response format
        return response()->json([
            'success' => true,
            'message' => 'Detection logged successfully',
            'data' => [
                're_id' => $validated['re_id'],
                'branch_id' => $validated['branch_id'],
                'device_id' => $validated['device_id'],
            ]
        ], 201);  // ✅ BEST PRACTICE: Use proper HTTP status code

    } catch (\Exception $e) {
        // ✅ BEST PRACTICE: Log errors and return proper error response
        Log::error('Detection logging failed', [
            're_id' => $validated['re_id'],
            'error' => $e->getMessage()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Failed to log detection',
            'error' => $e->getMessage()
        ], 500);
    }
}
```

#### **5. Security Headers (BEST PRACTICE)**

```php
// ✅ BEST PRACTICE: Add security headers middleware
// app/Http/Middleware/SecurityHeaders.php
namespace App\Http\Middleware;

use Closure;

class SecurityHeaders
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        // Prevent clickjacking
        $response->headers->set('X-Frame-Options', 'DENY');

        // Prevent MIME type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Enable XSS protection
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Content Security Policy
        $response->headers->set('Content-Security-Policy', "default-src 'self'");

        // Strict Transport Security (HSTS)
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');

        return $response;
    }
}
```

---

## ✅ Best Practices Checklist

### **Database Design (PostgreSQL)**

- ✅ **Normalized Structure**: 3NF normalization to reduce redundancy
- ✅ **Proper Data Types**: VARCHAR for strings, BIGINT for IDs, BIGSERIAL for auto-increment
- ✅ **PostgreSQL Data Types**: JSONB for JSON, INET for IP, CHECK constraints for enums
- ✅ **Foreign Keys**: All relationships enforced with named constraints
- ✅ **Indexes**: All foreign keys and frequently queried columns indexed
- ✅ **Composite Indexes**: Multi-column indexes for common query patterns
- ✅ **GIN Indexes**: For JSONB and array columns (faster searches)
- ✅ **Partial Indexes**: For filtered queries (PostgreSQL specific)
- ✅ **UTF8**: Full Unicode support including emojis
- ✅ **MVCC**: Multi-Version Concurrency Control (PostgreSQL default)
- ✅ **Timestamp Tracking**: created_at and updated_at on all tables
- ✅ **Triggers**: Auto-update updated_at with triggers
- ✅ **Constraints**: CHECK constraints for data validation

### **Performance**

- ✅ **Database Caching**: Use PostgreSQL materialized views for complex queries
- ✅ **Eager Loading**: Prevent N+1 query problems
- ✅ **Query Builder**: Use for aggregations and complex queries
- ✅ **Chunking**: Process large datasets in batches
- ✅ **Database Transactions**: Wrap multiple operations for data consistency
- ✅ **Background Jobs**: Process heavy operations asynchronously
- ✅ **Read/Write Splitting**: Separate read and write database connections
- ✅ **Connection Pooling**: Reuse database connections with PgBouncer

### **Security**

- ✅ **API Key + Secret**: Dual credential authentication
- ✅ **Laravel Sanctum**: Token-based authentication for SPA/Mobile
- ✅ **Rate Limiting**: Prevent API abuse
- ✅ **Input Validation**: Form Requests with comprehensive rules
- ✅ **Prepared Statements**: Automatic SQL injection prevention
- ✅ **Encrypted Storage**: Sensitive data (passwords, API keys) encrypted
- ✅ **HTTPS Only**: Force HTTPS for all API endpoints
- ✅ **Security Headers**: XSS, clickjacking, MIME-sniffing protection
- ✅ **CORS Configuration**: Proper cross-origin resource sharing
- ✅ **Audit Trail**: Track all API requests and user actions

### **Code Quality**

- ✅ **Form Requests**: Separate validation logic
- ✅ **Service Classes**: Business logic in service layer
- ✅ **Job Classes**: Background processing
- ✅ **Resource Classes**: Consistent API responses
- ✅ **Middleware**: Reusable request filtering
- ✅ **Error Handling**: Proper exception handling
- ✅ **Logging**: Comprehensive error and activity logging
- ✅ **Type Hinting**: PHP 8+ type declarations

### **API Design**

- ✅ **RESTful Convention**: Standard HTTP methods and status codes
- ✅ **Consistent Responses**: Uniform JSON structure
- ✅ **Pagination**: For list endpoints
- ✅ **Filtering**: Query parameters for filtering
- ✅ **Versioning**: API version in URL (/api/v1/)
- ✅ **Documentation**: Clear API documentation
- ✅ **Error Messages**: Descriptive error responses

### **Scalability**

- ✅ **Horizontal Scaling**: Database read replicas
- ✅ **Queue Workers**: Multiple workers for background jobs
- ✅ **CDN**: Static assets delivery
- ✅ **Load Balancer**: Distribute traffic across servers
- ✅ **Microservices Ready**: Modular design for service separation
- ✅ **Database Partitioning**: PostgreSQL table partitioning for large tables

### **Monitoring & Maintenance**

- ✅ **Query Logging**: Log slow queries
- ✅ **Performance Monitoring**: Track response times
- ✅ **Error Tracking**: Centralized error logging
- ✅ **Database Metrics**: Track connections, queries per second
- ✅ **API Analytics**: Track endpoint usage
- ✅ **Health Checks**: Automated system health monitoring

---

## 🚨 Important Notes

### **Environment Variables**

```env
# Database PostgreSQL
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=cctv_dashboard
DB_USERNAME=postgres
DB_PASSWORD=
DB_SSLMODE=prefer

# Database Read/Write Split
DB_READ_HOST_1=127.0.0.1
DB_READ_HOST_2=127.0.0.1
DB_WRITE_HOST=127.0.0.1

# Queue
QUEUE_CONNECTION=database

# Application
APP_TIMEZONE=Asia/Jakarta
APP_URL=https://cctv.yourdomain.com
APP_DEBUG=false  # ✅ MUST be false in production

# Session
SESSION_DRIVER=database
SESSION_LIFETIME=120

# Sanctum
SANCTUM_STATEFUL_DOMAINS=cctv.yourdomain.com
```

### **Required Packages**

```bash
# Core packages
composer require laravel/sanctum  # API authentication

# PostgreSQL specific
# Laravel comes with PDO PostgreSQL support by default
# Ensure php-pgsql extension is installed: apt-get install php-pgsql

# Recommended packages
composer require spatie/laravel-query-builder  # Advanced query filtering
composer require spatie/laravel-activitylog    # Audit trail
composer require barryvdh/laravel-debugbar     # Development only
composer require doctrine/dbal                 # Schema manipulation for PostgreSQL
```

### **Production Deployment Checklist**

- [ ] Set `APP_DEBUG=false`
- [ ] Set `APP_ENV=production`
- [ ] Use strong `APP_KEY`
- [ ] Enable HTTPS/SSL
- [ ] Configure CORS properly
- [ ] Set up database backups
- [ ] Configure log rotation
- [ ] Set up monitoring (New Relic, DataDog)
- [ ] Enable query caching
- [ ] Optimize images and assets
- [ ] Set up CDN
- [ ] Configure firewall rules
- [ ] Set up automated backups
- [ ] Test disaster recovery plan

---

## 🐘 PostgreSQL Specific Features & Optimizations

### **1. JSONB Queries (PostgreSQL Advantage)**

```sql
-- ✅ Query inside JSONB fields
SELECT * FROM re_id_master
WHERE appearance_features->>'height' = 'medium';

-- ✅ JSONB containment
SELECT * FROM re_id_master
WHERE appearance_features @> '{"clothing_colors": ["blue"]}';

-- ✅ JSONB array operations
SELECT * FROM branch_event_settings
WHERE whatsapp_numbers ? '+628123456789';

-- ✅ Update JSONB fields
UPDATE re_id_master
SET appearance_features = appearance_features || '{"verified": true}'::jsonb
WHERE re_id = 'person_001_abc123';
```

### **2. Advanced PostgreSQL Features**

#### **Full-Text Search**

```sql
-- Add tsvector column for full-text search
ALTER TABLE re_id_master ADD COLUMN search_vector tsvector;

-- Create GIN index for full-text search
CREATE INDEX idx_re_id_master_search ON re_id_master USING GIN (search_vector);

-- Update search vector
UPDATE re_id_master
SET search_vector = to_tsvector('english', coalesce(person_name, '') || ' ' || coalesce(re_id, ''));

-- Full-text search query
SELECT * FROM re_id_master
WHERE search_vector @@ to_tsquery('english', 'john');
```

#### **Table Partitioning**

```sql
-- ✅ Partition re_id_branch_detection by month
CREATE TABLE re_id_branch_detection_2024_01 PARTITION OF re_id_branch_detection
FOR VALUES FROM ('2024-01-01') TO ('2024-02-01');

CREATE TABLE re_id_branch_detection_2024_02 PARTITION OF re_id_branch_detection
FOR VALUES FROM ('2024-02-01') TO ('2024-03-01');

-- Auto-create partitions with pg_partman extension
CREATE EXTENSION pg_partman;
```

#### **Materialized Views for Reports**

```sql
-- ✅ Create materialized view for daily summaries
CREATE MATERIALIZED VIEW daily_branch_summary AS
SELECT
    cb.branch_name,
    DATE(rbd.detection_timestamp) as date,
    COUNT(DISTINCT rbd.re_id) as unique_persons,
    COUNT(*) as total_detections
FROM company_branches cb
LEFT JOIN re_id_branch_detection rbd ON cb.id = rbd.branch_id
GROUP BY cb.id, cb.branch_name, DATE(rbd.detection_timestamp);

-- Create index on materialized view
CREATE INDEX idx_daily_branch_summary_date ON daily_branch_summary(date);

-- Refresh materialized view (run daily via cron)
REFRESH MATERIALIZED VIEW CONCURRENTLY daily_branch_summary;
```

### **3. PostgreSQL Performance Tuning**

#### **postgresql.conf Optimization**

```ini
# Memory Settings
shared_buffers = 256MB  # 25% of RAM
effective_cache_size = 1GB  # 50-75% of RAM
work_mem = 16MB  # Per operation
maintenance_work_mem = 128MB  # For VACUUM, CREATE INDEX

# Checkpoint Settings
checkpoint_completion_target = 0.9
wal_buffers = 16MB
min_wal_size = 1GB
max_wal_size = 4GB

# Query Planner
random_page_cost = 1.1  # For SSD (default 4.0 for HDD)
effective_io_concurrency = 200  # For SSD

# Connection Settings
max_connections = 100
shared_preload_libraries = 'pg_stat_statements'  # Query analytics

# Logging
log_min_duration_statement = 1000  # Log queries > 1 second
log_line_prefix = '%t [%p]: [%l-1] user=%u,db=%d,app=%a,client=%h '
```

#### **Maintenance Commands**

```sql
-- ✅ VACUUM to reclaim storage
VACUUM ANALYZE re_id_branch_detection;

-- ✅ REINDEX for index optimization
REINDEX TABLE re_id_branch_detection;

-- ✅ Analyze for query planner statistics
ANALYZE re_id_branch_detection;

-- ✅ Check table bloat
SELECT schemaname, tablename,
       pg_size_pretty(pg_total_relation_size(schemaname||'.'||tablename)) AS size
FROM pg_tables
WHERE schemaname = 'public'
ORDER BY pg_total_relation_size(schemaname||'.'||tablename) DESC;
```

### **4. Migration Example (PostgreSQL)**

```php
// database/migrations/2024_01_01_000001_create_company_groups_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateCompanyGroupsTable extends Migration
{
    public function up()
    {
        Schema::create('company_groups', function (Blueprint $table) {
            $table->id();  // BIGSERIAL PRIMARY KEY
            $table->string('province_code', 10)->unique();
            $table->string('province_name', 100);
            $table->string('group_name', 150);
            $table->text('address')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();  // created_at and updated_at

            // Add CHECK constraint
            $table->check("status IN ('active', 'inactive')");
        });

        // Create trigger function for updated_at
        DB::unprepared('
            CREATE OR REPLACE FUNCTION update_updated_at_column()
            RETURNS TRIGGER AS $$
            BEGIN
                NEW.updated_at = CURRENT_TIMESTAMP;
                RETURN NEW;
            END;
            $$ language \'plpgsql\';
        ');

        // Create trigger
        DB::unprepared('
            CREATE TRIGGER update_company_groups_updated_at
            BEFORE UPDATE ON company_groups
            FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
        ');

        // Create indexes
        Schema::table('company_groups', function (Blueprint $table) {
            $table->index('province_code');
            $table->index('status');
        });
    }

    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS update_company_groups_updated_at ON company_groups');
        Schema::dropIfExists('company_groups');
    }
}
```

---

_This comprehensive database plan follows industry best practices for PostgreSQL performance, security, scalability, and maintainability. All recommendations are based on Laravel framework standards and proven production patterns for PostgreSQL databases._
