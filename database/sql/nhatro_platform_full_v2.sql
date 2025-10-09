
-- =====================================================================
-- NhaTro Platform - FULL SCHEMA v2 (MySQL 8.x)
-- Includes: Core rental system + Payroll & Commission modules
-- Updates:
--  * ticket_logs: thêm cost_amount/cost_note/charge_to/linked_invoice_id
--  * lease_residents: thêm user_id (FK -> users)
--  * locations: chuẩn hoá theo geo_* (countries/provinces/districts/wards) + FKs code
-- Charset: utf8mb4 / Collation: utf8mb4_unicode_ci
-- Engine: InnoDB
-- Author: ChatGPT
-- Last Updated: 2025-10-07
-- =====================================================================

-- ------------------------
-- Create Database
-- ------------------------
CREATE DATABASE IF NOT EXISTS nhatro_platform
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE nhatro_platform;

-- session settings (safe defaults)
SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;
SET time_zone = '+07:00';

-- =====================================================================
-- 0) GEO MASTER DATA (theo chuẩn phân cấp)
-- =====================================================================
CREATE TABLE IF NOT EXISTS geo_countries (
  code        VARCHAR(10) PRIMARY KEY,      -- ví dụ 'VN'
  name        VARCHAR(150) NOT NULL,
  name_local  VARCHAR(150),
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Quốc gia';

CREATE TABLE IF NOT EXISTS geo_provinces (
  code         VARCHAR(20) PRIMARY KEY,     -- ví dụ 'VN-HN' hoặc mã tổng cục thống kê
  country_code VARCHAR(10) NOT NULL,
  name         VARCHAR(150) NOT NULL,
  name_local   VARCHAR(150),
  kind         ENUM('province','city','municipality') DEFAULT 'province',
  created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_gprov_country FOREIGN KEY (country_code) REFERENCES geo_countries(code) ON DELETE CASCADE,
  INDEX idx_gprov_country (country_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tỉnh/Thành phố';

CREATE TABLE IF NOT EXISTS geo_districts (
  code           VARCHAR(20) PRIMARY KEY,
  province_code  VARCHAR(20) NOT NULL,
  name           VARCHAR(150) NOT NULL,
  name_local     VARCHAR(150),
  kind           ENUM('district','town','urban_district') DEFAULT 'district',
  created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_gdist_province FOREIGN KEY (province_code) REFERENCES geo_provinces(code) ON DELETE CASCADE,
  INDEX idx_gdist_province (province_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Quận/Huyện/Thị xã';

CREATE TABLE IF NOT EXISTS geo_wards (
  code           VARCHAR(20) PRIMARY KEY,
  district_code  VARCHAR(20) NOT NULL,
  name           VARCHAR(150) NOT NULL,
  name_local     VARCHAR(150),
  kind           ENUM('ward','commune','townlet') DEFAULT 'ward',
  created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_gward_district FOREIGN KEY (district_code) REFERENCES geo_districts(code) ON DELETE CASCADE,
  INDEX idx_gward_district (district_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Phường/Xã/Thị trấn';

-- Seed tối thiểu quốc gia VN
INSERT IGNORE INTO geo_countries (code, name, name_local) VALUES ('VN','Vietnam','Việt Nam');

-- =====================================================================
-- 1) CORE / ORG / RBAC
-- =====================================================================
CREATE TABLE IF NOT EXISTS organizations (
  id           BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  code         VARCHAR(50) UNIQUE,
  name         VARCHAR(255) NOT NULL,
  phone        VARCHAR(30),
  email        VARCHAR(255),
  tax_code     VARCHAR(50),
  address      VARCHAR(255),
  status       TINYINT NOT NULL DEFAULT 1, -- 1=active,0=inactive
  created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tổ chức/đơn vị vận hành';

CREATE TABLE IF NOT EXISTS roles (
  id           BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  key_code     VARCHAR(100) NOT NULL UNIQUE, -- admin, manager, agent, landlord, tenant
  name         VARCHAR(150) NOT NULL,
  created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Vai trò';

CREATE TABLE IF NOT EXISTS permissions (
  id           BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  key_code     VARCHAR(150) NOT NULL UNIQUE, -- ví dụ: auth.signup, lease.create, invoice.view
  description  VARCHAR(255),
  created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Quyền';

CREATE TABLE IF NOT EXISTS role_permissions (
  role_id       BIGINT UNSIGNED NOT NULL,
  permission_id BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (role_id, permission_id),
  CONSTRAINT fk_rp_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
  CONSTRAINT fk_rp_perm FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Gán quyền cho vai trò';

CREATE TABLE IF NOT EXISTS users (
  id              BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  organization_id BIGINT UNSIGNED NULL,
  email           VARCHAR(255) UNIQUE,
  phone           VARCHAR(30) UNIQUE,
  password_hash   VARCHAR(255),
  full_name       VARCHAR(255),
  avatar_url      VARCHAR(500),
  status          TINYINT NOT NULL DEFAULT 1, -- 1=active,0=locked
  last_login_at   DATETIME NULL,
  created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_users_org FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Người dùng';

CREATE TABLE IF NOT EXISTS user_roles (
  user_id BIGINT UNSIGNED NOT NULL,
  role_id BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (user_id, role_id),
  CONSTRAINT fk_ur_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_ur_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Vai trò toàn cục của user';

CREATE TABLE IF NOT EXISTS organization_users (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  organization_id BIGINT UNSIGNED NOT NULL,
  user_id BIGINT UNSIGNED NOT NULL,
  role_id BIGINT UNSIGNED NOT NULL,
  status ENUM('active','inactive') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_org_user_role (organization_id, user_id, role_id),
  CONSTRAINT fk_ou_org FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE,
  CONSTRAINT fk_ou_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_ou_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Thành viên theo tổ chức & vai trò';

CREATE TABLE IF NOT EXISTS user_profiles (
  user_id      BIGINT UNSIGNED PRIMARY KEY,
  dob          DATE NULL,
  gender       ENUM('male','female','other') DEFAULT 'other',
  id_number    VARCHAR(50),
  id_issued_at DATE,
  id_images    JSON,
  address      VARCHAR(255),
  note         TEXT,
  CONSTRAINT fk_profiles_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Hồ sơ cơ bản người dùng';

-- =====================================================================
-- 2) LOCATION (sử dụng geo_* codes làm chuẩn, giữ tương thích ngược)
-- =====================================================================
CREATE TABLE IF NOT EXISTS locations (
  id             BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  country_code   VARCHAR(10) DEFAULT 'VN',
  province_code  VARCHAR(20),
  district_code  VARCHAR(20),
  ward_code      VARCHAR(20),
  street         VARCHAR(255),
  -- text fields (tương thích cũ) - có thể NULL nếu dùng code
  country        VARCHAR(100),
  city           VARCHAR(100),     -- tên tỉnh/thành nếu cần
  district       VARCHAR(100),
  ward           VARCHAR(100),
  lat            DECIMAL(10,7),
  lng            DECIMAL(10,7),
  postal_code    VARCHAR(20),
  created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_loc_country  FOREIGN KEY (country_code)  REFERENCES geo_countries(code)  ON DELETE SET NULL,
  CONSTRAINT fk_loc_province FOREIGN KEY (province_code) REFERENCES geo_provinces(code)  ON DELETE SET NULL,
  CONSTRAINT fk_loc_district FOREIGN KEY (district_code) REFERENCES geo_districts(code)  ON DELETE SET NULL,
  CONSTRAINT fk_loc_ward     FOREIGN KEY (ward_code)     REFERENCES geo_wards(code)      ON DELETE SET NULL,
  INDEX idx_loc_codes (country_code, province_code, district_code, ward_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Địa chỉ theo chuẩn geo codes';

-- =====================================================================
-- 3) PROPERTY / UNIT / AMENITY / LISTING
-- =====================================================================
CREATE TABLE IF NOT EXISTS properties (
  id              BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  organization_id BIGINT UNSIGNED,
  owner_id        BIGINT UNSIGNED,
  name            VARCHAR(255) NOT NULL,
  location_id     BIGINT UNSIGNED,
  description     TEXT,
  total_floors    INT,
  status          TINYINT DEFAULT 1,
  created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_properties_org FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE,
  CONSTRAINT fk_properties_owner FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE SET NULL,
  CONSTRAINT fk_properties_location FOREIGN KEY (location_id) REFERENCES locations(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tòa nhà/Tài sản';

CREATE TABLE IF NOT EXISTS units (
  id              BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  property_id     BIGINT UNSIGNED NOT NULL,
  code            VARCHAR(50),
  floor           INT,
  area_m2         DECIMAL(10,2),
  unit_type       ENUM('room','apartment','dorm','shared') DEFAULT 'room',
  base_rent       DECIMAL(12,2) NOT NULL,
  deposit_amount  DECIMAL(12,2) DEFAULT 0,
  max_occupancy   INT DEFAULT 1,
  status          ENUM('available','reserved','occupied','maintenance') DEFAULT 'available',
  note            TEXT,
  created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_property_code (property_id, code),
  INDEX idx_units_status (status),
  CONSTRAINT fk_units_property FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Phòng/căn';

CREATE TABLE IF NOT EXISTS amenities (
  id         BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  key_code   VARCHAR(100) UNIQUE,
  name       VARCHAR(150) NOT NULL,
  category   VARCHAR(100)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tiện ích';

CREATE TABLE IF NOT EXISTS unit_amenities (
  unit_id     BIGINT UNSIGNED NOT NULL,
  amenity_id  BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (unit_id, amenity_id),
  CONSTRAINT fk_ua_unit FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE CASCADE,
  CONSTRAINT fk_ua_amen FOREIGN KEY (amenity_id) REFERENCES amenities(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tiện ích gắn cho phòng';

CREATE TABLE IF NOT EXISTS listings (
  id             BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  unit_id        BIGINT UNSIGNED NOT NULL,
  title          VARCHAR(255) NOT NULL,
  slug           VARCHAR(255) UNIQUE,
  description    TEXT,
  price_display  DECIMAL(12,2),
  publish_status ENUM('draft','published','archived') DEFAULT 'draft',
  published_at   DATETIME,
  created_by     BIGINT UNSIGNED,
  created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_listings_unit FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE CASCADE,
  CONSTRAINT fk_listings_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
  FULLTEXT KEY ft_listings (title, description)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tin đăng';

-- =====================================================================
-- 4) CRM
-- =====================================================================
CREATE TABLE IF NOT EXISTS leads (
  id            BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT COMMENT 'ID lead CRM',
  source        VARCHAR(100) COMMENT 'Nguồn: web/zalo/fb/referral/...',
  name          VARCHAR(150) COMMENT 'Tên khách tiềm năng',
  phone         VARCHAR(30)  COMMENT 'SĐT',
  email         VARCHAR(150) COMMENT 'Email',
  desired_city  VARCHAR(100) COMMENT 'Khu vực mong muốn',
  budget_min    DECIMAL(12,2) COMMENT 'Ngân sách tối thiểu',
  budget_max    DECIMAL(12,2) COMMENT 'Ngân sách tối đa',
  note          TEXT          COMMENT 'Ghi chú',
  status        ENUM('new','contacted','qualified','lost','converted') DEFAULT 'new' COMMENT 'Trạng thái CRM',
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_leads_status_created (status, created_at),
  INDEX idx_leads_phone (phone),
  INDEX idx_leads_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Lead';

CREATE TABLE IF NOT EXISTS viewings (
  id           BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT COMMENT 'ID lịch xem phòng',
  lead_id      BIGINT UNSIGNED COMMENT 'Lead nếu khách chưa có account',
  listing_id   BIGINT UNSIGNED COMMENT 'Tin đăng xem',
  agent_id     BIGINT UNSIGNED COMMENT 'CTV/Nhân viên phụ trách',
  schedule_at  DATETIME NOT NULL COMMENT 'Thời điểm hẹn',
  status       ENUM('requested','confirmed','done','no_show','cancelled') DEFAULT 'requested' COMMENT 'Trạng thái',
  result_note  TEXT COMMENT 'Kết quả buổi xem',
  created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_view_lead FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE SET NULL,
  CONSTRAINT fk_view_listing FOREIGN KEY (listing_id) REFERENCES listings(id) ON DELETE SET NULL,
  CONSTRAINT fk_view_agent FOREIGN KEY (agent_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_viewings_status_time (status, schedule_at),
  INDEX idx_viewings_agent_time (agent_id, schedule_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Lịch xem phòng';

CREATE TABLE IF NOT EXISTS booking_deposits (
  id             BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT COMMENT 'ID đặt cọc',
  unit_id        BIGINT UNSIGNED NOT NULL COMMENT 'Phòng giữ chỗ',
  tenant_user_id BIGINT UNSIGNED COMMENT 'User nếu khách đã có tài khoản',
  lead_id        BIGINT UNSIGNED COMMENT 'Lead nếu khách chưa có tài khoản',
  amount         DECIMAL(12,2) NOT NULL COMMENT 'Số tiền cọc',
  invoice_id     BIGINT UNSIGNED COMMENT 'Hoá đơn cọc liên kết',
  payment_status ENUM('unpaid','paid','refunded','failed') DEFAULT 'unpaid' COMMENT 'Trạng thái thanh toán',
  hold_until     DATETIME COMMENT 'Hết hạn giữ chỗ',
  created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_bd_unit FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE CASCADE,
  CONSTRAINT fk_bd_tenant FOREIGN KEY (tenant_user_id) REFERENCES users(id) ON DELETE SET NULL,
  CONSTRAINT fk_bd_lead FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE SET NULL,
  INDEX idx_deposits_unit (unit_id),
  INDEX idx_deposits_status (payment_status),
  INDEX idx_deposits_hold_until (hold_until)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Đặt cọc giữ chỗ';

-- =====================================================================
-- 5) LEASES / RESIDENTS
-- =====================================================================
CREATE TABLE IF NOT EXISTS leases (
  id               BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  organization_id  BIGINT UNSIGNED,
  unit_id          BIGINT UNSIGNED NOT NULL,
  tenant_id        BIGINT UNSIGNED NOT NULL,
  agent_id         BIGINT UNSIGNED NULL,
  start_date       DATE NOT NULL,
  end_date         DATE NOT NULL,
  rent_amount      DECIMAL(12,2) NOT NULL,
  deposit_amount   DECIMAL(12,2) DEFAULT 0,
  billing_day      TINYINT DEFAULT 1,
  status           ENUM('draft','active','terminated','expired') DEFAULT 'draft',
  contract_no      VARCHAR(100),
  signed_at        DATETIME,
  created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_leases_unit_status (unit_id, status),
  INDEX idx_leases_tenant (tenant_id),
  CONSTRAINT fk_lease_org FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE SET NULL,
  CONSTRAINT fk_lease_unit FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE RESTRICT,
  CONSTRAINT fk_lease_tenant FOREIGN KEY (tenant_id) REFERENCES users(id) ON DELETE RESTRICT,
  CONSTRAINT fk_lease_agent FOREIGN KEY (agent_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Hợp đồng thuê';

CREATE TABLE IF NOT EXISTS lease_residents (
  id        BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  lease_id  BIGINT UNSIGNED NOT NULL,
  user_id   BIGINT UNSIGNED NULL COMMENT 'Nếu cư dân có tài khoản → liên kết để theo dõi hóa đơn/ticket',
  name      VARCHAR(150) NOT NULL,
  phone     VARCHAR(30),
  id_number VARCHAR(50),
  note      TEXT,
  CONSTRAINT fk_resident_lease FOREIGN KEY (lease_id) REFERENCES leases(id) ON DELETE CASCADE,
  CONSTRAINT fk_resident_user  FOREIGN KEY (user_id)  REFERENCES users(id)  ON DELETE SET NULL,
  INDEX idx_lease_residents_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Cư dân kèm theo hợp đồng';

-- =====================================================================
-- 6) SERVICES / METERING
-- =====================================================================
CREATE TABLE IF NOT EXISTS services (
  id           BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  key_code     VARCHAR(100) UNIQUE,
  name         VARCHAR(150) NOT NULL,
  pricing_type ENUM('fixed','per_unit','tiered') DEFAULT 'fixed',
  unit_label   VARCHAR(50),
  description  TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Danh mục dịch vụ';

CREATE TABLE IF NOT EXISTS lease_services (
  id           BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  lease_id     BIGINT UNSIGNED NOT NULL,
  service_id   BIGINT UNSIGNED NOT NULL,
  price        DECIMAL(12,2) NOT NULL,
  meta_json    JSON,
  UNIQUE KEY uq_lease_service (lease_id, service_id),
  CONSTRAINT fk_ls_lease FOREIGN KEY (lease_id) REFERENCES leases(id) ON DELETE CASCADE,
  CONSTRAINT fk_ls_service FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Dịch vụ áp cho hợp đồng';

CREATE TABLE IF NOT EXISTS meters (
  id           BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  property_id  BIGINT UNSIGNED,
  unit_id      BIGINT UNSIGNED,
  service_id   BIGINT UNSIGNED,
  serial_no    VARCHAR(100),
  installed_at DATE,
  status       TINYINT DEFAULT 1,
  UNIQUE KEY uq_meter (unit_id, service_id),
  CONSTRAINT fk_meter_property FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE SET NULL,
  CONSTRAINT fk_meter_unit FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE CASCADE,
  CONSTRAINT fk_meter_service FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Đồng hồ/công tơ';

CREATE TABLE IF NOT EXISTS meter_readings (
  id           BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  meter_id     BIGINT UNSIGNED NOT NULL,
  reading_date DATE NOT NULL,
  value        DECIMAL(12,3) NOT NULL,
  image_url    VARCHAR(500),
  taken_by     BIGINT UNSIGNED,
  note         TEXT,
  created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_meter_date (meter_id, reading_date),
  CONSTRAINT fk_mr_meter FOREIGN KEY (meter_id) REFERENCES meters(id) ON DELETE CASCADE,
  CONSTRAINT fk_mr_user FOREIGN KEY (taken_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Chỉ số công tơ';

-- =====================================================================
-- 7) INVOICING & PAYMENTS
-- =====================================================================
CREATE TABLE IF NOT EXISTS invoices (
  id              BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  organization_id BIGINT UNSIGNED,
  lease_id        BIGINT UNSIGNED,
  invoice_no      VARCHAR(100) UNIQUE,
  issue_date      DATE NOT NULL,
  due_date        DATE NOT NULL,
  status          ENUM('draft','issued','paid','overdue','cancelled') DEFAULT 'draft',
  subtotal        DECIMAL(12,2) DEFAULT 0,
  tax_amount      DECIMAL(12,2) DEFAULT 0,
  discount_amount DECIMAL(12,2) DEFAULT 0,
  total_amount    DECIMAL(12,2) DEFAULT 0,
  currency        VARCHAR(10) DEFAULT 'VND',
  note            TEXT,
  created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_invoices_lease_status (lease_id, status),
  INDEX idx_invoices_due (due_date),
  CONSTRAINT fk_inv_org FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE SET NULL,
  CONSTRAINT fk_inv_lease FOREIGN KEY (lease_id) REFERENCES leases(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Hóa đơn';

CREATE TABLE IF NOT EXISTS invoice_items (
  id           BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  invoice_id   BIGINT UNSIGNED NOT NULL,
  item_type    ENUM('rent','service','meter','deposit','other') DEFAULT 'other',
  description  VARCHAR(255),
  quantity     DECIMAL(12,3) DEFAULT 1,
  unit_price   DECIMAL(12,2) NOT NULL,
  amount       DECIMAL(12,2) NOT NULL,
  meta_json    JSON,
  INDEX idx_invoice_items_invoice (invoice_id),
  CONSTRAINT fk_item_invoice FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Dòng hóa đơn';

CREATE TABLE IF NOT EXISTS payment_methods (
  id         BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  key_code   VARCHAR(50) UNIQUE,
  name       VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Phương thức thanh toán';

CREATE TABLE IF NOT EXISTS payments (
  id              BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  invoice_id      BIGINT UNSIGNED NOT NULL,
  method_id       BIGINT UNSIGNED,
  amount          DECIMAL(12,2) NOT NULL,
  paid_at         DATETIME NOT NULL,
  txn_ref         VARCHAR(150),
  status          ENUM('pending','success','failed','refunded') DEFAULT 'pending',
  payer_user_id   BIGINT UNSIGNED,
  attachment_url  VARCHAR(500),
  note            TEXT,
  created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_payments_invoice (invoice_id),
  INDEX idx_payments_status (status),
  CONSTRAINT fk_pay_invoice FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE,
  CONSTRAINT fk_pay_method FOREIGN KEY (method_id)  REFERENCES payment_methods(id) ON DELETE SET NULL,
  CONSTRAINT fk_pay_user FOREIGN KEY (payer_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Thanh toán';

-- FK invoice cho booking_deposits
ALTER TABLE booking_deposits
  ADD CONSTRAINT fk_bd_invoice FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE SET NULL;

-- =====================================================================
-- 8) TICKETS (bảo trì) + LOGS (có giá tiền)
-- =====================================================================
CREATE TABLE IF NOT EXISTS tickets (
  id              BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  organization_id BIGINT UNSIGNED,
  unit_id         BIGINT UNSIGNED,
  lease_id        BIGINT UNSIGNED,
  created_by      BIGINT UNSIGNED,
  assigned_to     BIGINT UNSIGNED,
  title           VARCHAR(255) NOT NULL,
  description     TEXT,
  priority        ENUM('low','medium','high','urgent') DEFAULT 'medium',
  status          ENUM('open','in_progress','resolved','closed','cancelled') DEFAULT 'open',
  created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_tickets_status_priority (status, priority),
  CONSTRAINT fk_tk_org FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE SET NULL,
  CONSTRAINT fk_tk_unit FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE SET NULL,
  CONSTRAINT fk_tk_lease FOREIGN KEY (lease_id) REFERENCES leases(id) ON DELETE SET NULL,
  CONSTRAINT fk_tk_created FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
  CONSTRAINT fk_tk_assigned FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Ticket bảo trì/sự cố';

CREATE TABLE IF NOT EXISTS ticket_logs (
  id                 BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  ticket_id          BIGINT UNSIGNED NOT NULL,
  actor_id           BIGINT UNSIGNED,
  action             VARCHAR(100),       -- status_changed, comment, assign...
  detail             TEXT,
  cost_amount        DECIMAL(12,2) NOT NULL DEFAULT 0 COMMENT 'Chi phí phát sinh (để có thể trừ vào cọc)',
  cost_note          VARCHAR(255) NULL COMMENT 'Mô tả chi phí',
  charge_to          ENUM('none','tenant_deposit','tenant_invoice','landlord') DEFAULT 'none' COMMENT 'Hướng hạch toán',
  linked_invoice_id  BIGINT UNSIGNED NULL COMMENT 'Hóa đơn liên quan (nếu charge_to=tenant_invoice)',
  created_at         TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_tkl_ticket FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
  CONSTRAINT fk_tkl_actor  FOREIGN KEY (actor_id)  REFERENCES users(id)   ON DELETE SET NULL,
  CONSTRAINT fk_tkl_invoice FOREIGN KEY (linked_invoice_id) REFERENCES invoices(id) ON DELETE SET NULL,
  INDEX idx_tkl_ticket_created (ticket_id, created_at),
  INDEX idx_tkl_charge_to (charge_to)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Nhật ký ticket + chi phí';

-- =====================================================================
-- 9) NOTIFICATIONS
-- =====================================================================
CREATE TABLE IF NOT EXISTS notification_channels (
  id       BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  key_code VARCHAR(50) UNIQUE,
  name     VARCHAR(100) NOT NULL,
  active   TINYINT DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Kênh thông báo';

CREATE TABLE IF NOT EXISTS notifications (
  id          BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  channel_id  BIGINT UNSIGNED,
  to_user_id  BIGINT UNSIGNED,
  subject     VARCHAR(255),
  content     TEXT,
  status      ENUM('queued','sent','failed') DEFAULT 'queued',
  error_msg   VARCHAR(500),
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  sent_at     DATETIME,
  INDEX idx_notifications_status (status, created_at),
  CONSTRAINT fk_ntf_channel FOREIGN KEY (channel_id) REFERENCES notification_channels(id) ON DELETE SET NULL,
  CONSTRAINT fk_ntf_user    FOREIGN KEY (to_user_id)  REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Thông báo';

-- =====================================================================
-- 10) FILES / DOCS / INTEGRATIONS / AUDIT
-- =====================================================================
CREATE TABLE IF NOT EXISTS documents (
  id           BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  owner_type   VARCHAR(50),
  owner_id     BIGINT UNSIGNED,
  file_url     VARCHAR(500) NOT NULL,
  file_name    VARCHAR(255),
  mime_type    VARCHAR(100),
  uploaded_by  BIGINT UNSIGNED,
  created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_documents_owner (owner_type, owner_id),
  CONSTRAINT fk_docs_uploader FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tài liệu/ảnh';

CREATE TABLE IF NOT EXISTS webhooks (
  id              BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  organization_id BIGINT UNSIGNED,
  event_key       VARCHAR(100),
  target_url      VARCHAR(500) NOT NULL,
  secret          VARCHAR(255),
  active          TINYINT DEFAULT 1,
  created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_wh_org FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Webhook cấu hình';

CREATE TABLE IF NOT EXISTS audit_logs (
  id            BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  actor_id      BIGINT UNSIGNED,
  action        VARCHAR(100),
  entity_type   VARCHAR(50),
  entity_id     BIGINT UNSIGNED,
  before_json   JSON,
  after_json    JSON,
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_audit_entity (entity_type, entity_id),
  CONSTRAINT fk_audit_actor FOREIGN KEY (actor_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Audit log';

-- =====================================================================
-- 11) PAYROLL & COMMISSION MODULES
-- =====================================================================
CREATE TABLE IF NOT EXISTS salary_contracts (
  id               BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT COMMENT 'Hợp đồng lương theo org & user',
  organization_id  BIGINT UNSIGNED NOT NULL,
  user_id          BIGINT UNSIGNED NOT NULL,
  base_salary      DECIMAL(12,2) NOT NULL COMMENT 'Lương cơ bản/tháng',
  currency         VARCHAR(10) DEFAULT 'VND',
  pay_cycle        ENUM('monthly') DEFAULT 'monthly',
  pay_day          TINYINT DEFAULT 5 COMMENT 'Ngày trả lương (1..28)',
  allowances_json  JSON NULL COMMENT 'Phụ cấp: {phone:..., travel:..., meal:...}',
  kpi_target_json  JSON NULL COMMENT 'Mục tiêu KPI để thưởng theo hiệu suất',
  effective_from   DATE NOT NULL,
  effective_to     DATE NULL,
  status           ENUM('active','ended','on_hold') DEFAULT 'active',
  created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_sc_org_user (organization_id, user_id, status),
  CONSTRAINT fk_sc_org FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE,
  CONSTRAINT fk_sc_user FOREIGN KEY (user_id)        REFERENCES users(id)          ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Cấu hình lương & phụ cấp';

CREATE TABLE IF NOT EXISTS payroll_cycles (
  id               BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT COMMENT 'Kỳ lương theo tháng',
  organization_id  BIGINT UNSIGNED NOT NULL,
  period_month     CHAR(7) NOT NULL COMMENT 'YYYY-MM',
  status           ENUM('open','locked','paid') DEFAULT 'open',
  locked_at        DATETIME NULL,
  paid_at          DATETIME NULL,
  note             VARCHAR(255),
  created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_org_month (organization_id, period_month),
  CONSTRAINT fk_pc_org FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Kỳ lương';

CREATE TABLE IF NOT EXISTS payroll_items (
  id               BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT COMMENT 'Dòng tính lương',
  payroll_cycle_id BIGINT UNSIGNED NOT NULL,
  user_id          BIGINT UNSIGNED NOT NULL,
  item_type        ENUM('base','allowance','overtime','commission','bonus','deduction','insurance','tax','advance','other') NOT NULL,
  sign             TINYINT NOT NULL DEFAULT 1,
  amount           DECIMAL(12,2) NOT NULL,
  ref_type         VARCHAR(50) NULL,
  ref_id           BIGINT UNSIGNED NULL,
  note             VARCHAR(255),
  created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_pi_cycle_user (payroll_cycle_id, user_id),
  INDEX idx_pi_type (item_type),
  CONSTRAINT fk_pi_cycle FOREIGN KEY (payroll_cycle_id) REFERENCES payroll_cycles(id) ON DELETE CASCADE,
  CONSTRAINT fk_pi_user  FOREIGN KEY (user_id)          REFERENCES users(id)           ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Dòng lương';

CREATE TABLE IF NOT EXISTS payroll_payslips (
  id               BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  payroll_cycle_id BIGINT UNSIGNED NOT NULL,
  user_id          BIGINT UNSIGNED NOT NULL,
  gross_amount     DECIMAL(12,2) NOT NULL DEFAULT 0,
  deduction_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
  net_amount       DECIMAL(12,2) NOT NULL DEFAULT 0,
  status           ENUM('draft','approved','paid') DEFAULT 'draft',
  paid_at          DATETIME NULL,
  payment_method   VARCHAR(50) NULL,
  note             VARCHAR(255),
  created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_slip (payroll_cycle_id, user_id),
  CONSTRAINT fk_ps_cycle FOREIGN KEY (payroll_cycle_id) REFERENCES payroll_cycles(id) ON DELETE CASCADE,
  CONSTRAINT fk_ps_user  FOREIGN KEY (user_id)          REFERENCES users(id)          ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Phiếu lương';

CREATE TABLE IF NOT EXISTS commission_policies (
  id               BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  organization_id  BIGINT UNSIGNED NOT NULL,
  code             VARCHAR(50) UNIQUE,
  title            VARCHAR(150) NOT NULL,
  trigger_event    ENUM('deposit_paid','lease_signed','invoice_paid','viewing_done','listing_published') NOT NULL,
  basis            ENUM('cash','accrual') DEFAULT 'cash',
  calc_type        ENUM('percent','flat','tiered') NOT NULL,
  percent_value    DECIMAL(5,2) NULL,
  flat_amount      DECIMAL(12,2) NULL,
  apply_limit_months TINYINT NULL,
  min_amount       DECIMAL(12,2) NULL,
  cap_amount       DECIMAL(12,2) NULL,
  filters_json     JSON NULL,
  active           TINYINT DEFAULT 1,
  created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_cp_org_active (organization_id, active),
  CONSTRAINT fk_cp_org FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Chính sách hoa hồng';

CREATE TABLE IF NOT EXISTS commission_policy_splits (
  id            BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  policy_id     BIGINT UNSIGNED NOT NULL,
  role_key      VARCHAR(50) NOT NULL,
  percent_share DECIMAL(5,2) NOT NULL,
  UNIQUE KEY uq_policy_role (policy_id, role_key),
  CONSTRAINT fk_cps_policy FOREIGN KEY (policy_id) REFERENCES commission_policies(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Phân chia hoa hồng';

CREATE TABLE IF NOT EXISTS commission_events (
  id               BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  policy_id        BIGINT UNSIGNED NOT NULL,
  organization_id  BIGINT UNSIGNED NOT NULL,
  trigger_event    ENUM('deposit_paid','lease_signed','invoice_paid','viewing_done','listing_published') NOT NULL,
  ref_type         VARCHAR(50) NOT NULL,
  ref_id           BIGINT UNSIGNED NOT NULL,
  lease_id         BIGINT UNSIGNED NULL,
  listing_id       BIGINT UNSIGNED NULL,
  unit_id          BIGINT UNSIGNED NULL,
  agent_id         BIGINT UNSIGNED NULL,
  occurred_at      DATETIME NOT NULL,
  amount_base      DECIMAL(12,2) NOT NULL,
  commission_total DECIMAL(12,2) NOT NULL,
  status           ENUM('pending','approved','paid','reversed','cancelled') DEFAULT 'pending',
  created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_ce_org_time (organization_id, occurred_at),
  INDEX idx_ce_status (status),
  CONSTRAINT fk_ce_policy  FOREIGN KEY (policy_id)       REFERENCES commission_policies(id) ON DELETE CASCADE,
  CONSTRAINT fk_ce_org     FOREIGN KEY (organization_id) REFERENCES organizations(id)      ON DELETE CASCADE,
  CONSTRAINT fk_ce_lease   FOREIGN KEY (lease_id)        REFERENCES leases(id)             ON DELETE SET NULL,
  CONSTRAINT fk_ce_listing FOREIGN KEY (listing_id)      REFERENCES listings(id)           ON DELETE SET NULL,
  CONSTRAINT fk_ce_unit    FOREIGN KEY (unit_id)         REFERENCES units(id)              ON DELETE SET NULL,
  CONSTRAINT fk_ce_agent   FOREIGN KEY (agent_id)        REFERENCES users(id)              ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Sự kiện hoa hồng';

CREATE TABLE IF NOT EXISTS commission_event_splits (
  id               BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  event_id         BIGINT UNSIGNED NOT NULL,
  user_id          BIGINT UNSIGNED NOT NULL,
  role_key         VARCHAR(50) NOT NULL,
  percent_share    DECIMAL(5,2) NOT NULL,
  amount           DECIMAL(12,2) NOT NULL,
  payroll_item_id  BIGINT UNSIGNED NULL,
  status           ENUM('pending','booked','paid','reversed') DEFAULT 'pending',
  created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_event_user (event_id, user_id),
  INDEX idx_ces_status (status),
  CONSTRAINT fk_ces_event  FOREIGN KEY (event_id)        REFERENCES commission_events(id) ON DELETE CASCADE,
  CONSTRAINT fk_ces_user   FOREIGN KEY (user_id)         REFERENCES users(id)             ON DELETE CASCADE,
  CONSTRAINT fk_ces_pitem  FOREIGN KEY (payroll_item_id) REFERENCES payroll_items(id)     ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Hoa hồng chia theo cá nhân';

CREATE TABLE IF NOT EXISTS salary_advances (
  id               BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  organization_id  BIGINT UNSIGNED NOT NULL,
  user_id          BIGINT UNSIGNED NOT NULL,
  amount           DECIMAL(12,2) NOT NULL,
  status           ENUM('requested','approved','deducting','settled','rejected') DEFAULT 'requested',
  requested_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  approved_at      DATETIME NULL,
  schedule_json    JSON NULL,
  note             VARCHAR(255),
  INDEX idx_sa_org_user (organization_id, user_id, status),
  CONSTRAINT fk_sa_org FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE,
  CONSTRAINT fk_sa_user FOREIGN KEY (user_id)        REFERENCES users(id)          ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tạm ứng lương';

-- =====================================================================
-- 12) SEEDING
-- =====================================================================
INSERT IGNORE INTO roles (key_code, name) VALUES
 ('admin','Quản trị hệ thống'),
 ('manager','Quản lý'),
 ('agent','CTV/Nhân viên'),
 ('landlord','Chủ trọ'),
 ('tenant','Người thuê');

INSERT IGNORE INTO permissions (key_code, description) VALUES
 ('auth.signup','Đăng ký'),
 ('auth.signin','Đăng nhập'),
 ('profile.view','Xem hồ sơ'),
 ('profile.update','Cập nhật hồ sơ'),
 ('listing.create','Tạo tin'),
 ('listing.view','Xem tin'),
 ('lease.create','Tạo hợp đồng'),
 ('lease.view','Xem hợp đồng'),
 ('invoice.create','Tạo hóa đơn'),
 ('invoice.view','Xem hóa đơn'),
 ('payment.create','Ghi nhận thanh toán'),
 ('ticket.create','Tạo ticket'),
 ('ticket.view','Xem ticket');

INSERT IGNORE INTO payment_methods (key_code, name) VALUES
 ('cash','Tiền mặt'),
 ('bank_qr','Chuyển khoản/QR'),
 ('momo','MoMo'),
 ('zalopay','ZaloPay'),
 ('vnpay','VNPAY');

INSERT IGNORE INTO services (key_code, name, pricing_type, unit_label) VALUES
 ('electricity','Điện','per_unit','kWh'),
 ('water','Nước','per_unit','m3'),
 ('internet','Internet','fixed','month'),
 ('parking','Giữ xe','fixed','slot');
