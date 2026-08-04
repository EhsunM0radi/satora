# Satora POS System — Complete Architecture Plan

> **For Hermes:** This document defines the architecture. Implementation follows after plan approval.
> Each phase builds on the previous. Use TDD throughout.

**Goal:** Design and implement a production-grade Point of Sale system for Satora Commerce Platform comparable to Odoo POS, Shopify POS, and Square.

**Architecture:** Independent Concord package (`Webkul\POS`) under `packages/Webkul/POS/src/`. Multi-tenant from day one. Marketplace-compatible. Offline-ready. Hardware-agnostic. AI data pipeline prepared.

**Tech Stack:** Laravel 12, Bagisto 2.4.x Concord, MySQL 8, Redis, Pest 3, Vue 3 (POS UI), Blade (admin)

---

## Package: `packages/Webkul/POS/`

### Package Structure
```
packages/Webkul/POS/src/
├── Config/                    # pos.php, admin-menu.php, acl.php, system.php
├── Contracts/                 # All domain contracts
├── Database/
│   ├── Migrations/
│   ├── Factories/
│   └── Seeders/
├── Enums/                     # POS-specific enums
├── Events/                    # Domain events for every business action
├── Exceptions/                # POS-specific exceptions
├── Http/
│   ├── Controllers/
│   │   ├── Admin/             # Admin panel controllers
│   │   └── Api/               # POS terminal API
│   ├── Middleware/
│   └── Requests/
├── Jobs/                      # Queued jobs
├── Listeners/                 # Event listeners
├── Models/                    # Eloquent models + Proxy classes
├── Observers/                 # Model observers (audit, AI data)
├── Policies/                  # Authorization policies
├── Providers/
│   ├── POSServiceProvider.php
│   └── ModuleServiceProvider.php
├── Repositories/              # Extends Webkul\Core\Eloquent\Repository
├── Resources/
│   ├── assets/                # Vue 3 POS terminal UI
│   ├── lang/                  # Translations (en, fa, +19 locales)
│   └── views/                 # Blade admin views
├── Routes/
│   ├── admin-routes.php       # Admin management
│   └── api-routes.php         # POS terminal API
├── Services/                  # Business logic services
├── Support/                   # Helpers, serializers, calculators
└── tests/
    ├── Feature/
    ├── Unit/
    └── Integration/
```

---

# Phase 1: Domain Architecture — Complete Schema

## Core Design Decision: Three Alternatives Considered

### Alternative 1: Monolithic POS module (Shopify POS approach)
- Single `pos_orders` table with JSON columns for flexibility
- **Rejected**: No audit trail, hard to query, violates relational integrity

### Alternative 2: Separate database per terminal (Square approach)
- Each terminal has its own SQLite DB syncing to central
- **Rejected**: Too complex for initial implementation, sync conflicts

### Alternative 3: Normalized relational model with tenant isolation (Odoo POS approach) ✅ SELECTED
- Each POS concept is a first-class entity with its own table
- Full relational integrity, easy reporting, clear audit trail
- Tenant isolation via `tenant_id` on every table
- **Trade-off**: More tables, but queryable, maintainable, testable

---

## Entity Relationship Diagram (Core Domain)

```
┌─────────────────┐       ┌─────────────────┐       ┌─────────────────┐
│   POS Location   │──1:N──│   POS Terminal   │──1:N──│   POS Session   │
│   (store/ware    │       │   (register)     │       │   (shift)       │
│    house)        │       └────────┬─────────┘       └────────┬─────────┘
└────────┬─────────┘               │                          │
         │                         │                          │
         │ 1:N                     │ 1:N                      │ 1:N
         ▼                         ▼                          ▼
┌─────────────────┐       ┌─────────────────┐       ┌─────────────────┐
│  Cash Register  │       │   POS Order     │       │  Cash Movement  │
│                 │       │   (Transaction) │       │  (open/close/   │
└─────────────────┘       └────────┬─────────┘       │   in/out)       │
                                   │                  └─────────────────┘
                                   │
          ┌────────────────────────┼─────────────────────────┐
          │                        │                         │
          ▼                        ▼                         ▼
   ┌─────────────┐        ┌─────────────┐         ┌─────────────┐
   │ POS Payment  │        │ POS Refund   │         │ POS Exchange │
   │              │        │              │         │              │
   └─────────────┘        └─────────────┘         └─────────────┘
          │                                                │
          ▼                                                ▼
   ┌─────────────┐                                  ┌─────────────┐
   │  Receipt     │                                  │ POS Invoice  │
   │              │                                  │              │
   └─────────────┘                                  └─────────────┘
```

---

## Complete Database Schema

### 1. `pos_locations`
```sql
CREATE TABLE pos_locations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(50) NOT NULL,
    type ENUM('store', 'warehouse', 'popup', 'mobile') NOT NULL DEFAULT 'store',
    address_line1 VARCHAR(255),
    address_line2 VARCHAR(255),
    city VARCHAR(100),
    state VARCHAR(100),
    postal_code VARCHAR(20),
    country VARCHAR(2) DEFAULT 'IR',
    phone VARCHAR(20),
    email VARCHAR(255),
    timezone VARCHAR(50) DEFAULT 'Asia/Tehran',
    is_active TINYINT(1) DEFAULT 1,
    settings JSON,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    UNIQUE KEY uk_tenant_code (tenant_id, code),
    INDEX idx_tenant (tenant_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);
```

### 2. `pos_terminals`
```sql
CREATE TABLE pos_terminals (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    pos_location_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(50) NOT NULL,
    status ENUM('active', 'inactive', 'maintenance') DEFAULT 'active',
    hardware_profile JSON,  -- {printer: {}, scanner: {}, drawer: {}, display: {}}
    settings JSON,           -- {receipt_header, receipt_footer, logo_url, currency, tax_inclusive}
    last_sync_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    UNIQUE KEY uk_tenant_code (tenant_id, code),
    INDEX idx_tenant (tenant_id),
    INDEX idx_location (pos_location_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (pos_location_id) REFERENCES pos_locations(id) ON DELETE CASCADE
);
```

### 3. `pos_sessions`
```sql
CREATE TABLE pos_sessions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    pos_terminal_id BIGINT UNSIGNED NOT NULL,
    admin_user_id INT UNSIGNED NOT NULL, -- cashier who opened
    session_number VARCHAR(50) NOT NULL, -- POS-{terminal}-{date}-{seq}
    status ENUM('open', 'closing', 'closed', 'suspended') DEFAULT 'open',
    opening_balance DECIMAL(15, 4) NOT NULL DEFAULT 0.0000,
    closing_balance DECIMAL(15, 4) NULL,
    expected_balance DECIMAL(15, 4) NULL, -- calculated from cash movements
    difference DECIMAL(15, 4) NULL,       -- closing - expected
    notes TEXT,
    opened_at TIMESTAMP NOT NULL,
    closed_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY uk_session_number (session_number),
    INDEX idx_tenant (tenant_id),
    INDEX idx_terminal (pos_terminal_id),
    INDEX idx_status (status),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (pos_terminal_id) REFERENCES pos_terminals(id) ON DELETE CASCADE,
    FOREIGN KEY (admin_user_id) REFERENCES admins(id) ON DELETE RESTRICT
);
```

### 4. `pos_cash_registers`
```sql
CREATE TABLE pos_cash_registers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    pos_terminal_id BIGINT UNSIGNED NOT NULL,
    pos_session_id BIGINT UNSIGNED NOT NULL, -- current session
    name VARCHAR(100) NOT NULL DEFAULT 'Main Register',
    type ENUM('cash', 'card_terminal', 'mixed') NOT NULL DEFAULT 'cash',
    current_balance DECIMAL(15, 4) NOT NULL DEFAULT 0.0000,
    currency VARCHAR(3) NOT NULL DEFAULT 'IRR',
    is_active TINYINT(1) DEFAULT 1,
    settings JSON,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_tenant (tenant_id),
    INDEX idx_terminal (pos_terminal_id),
    INDEX idx_session (pos_session_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (pos_terminal_id) REFERENCES pos_terminals(id) ON DELETE CASCADE,
    FOREIGN KEY (pos_session_id) REFERENCES pos_sessions(id) ON DELETE CASCADE
);
```

### 5. `pos_cash_movements`
```sql
CREATE TABLE pos_cash_movements (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    pos_session_id BIGINT UNSIGNED NOT NULL,
    pos_cash_register_id BIGINT UNSIGNED NOT NULL,
    admin_user_id INT UNSIGNED NOT NULL,
    type ENUM('opening', 'closing', 'cash_in', 'cash_out', 'sale', 'refund', 'expense', 'deposit') NOT NULL,
    amount DECIMAL(15, 4) NOT NULL,
    balance_after DECIMAL(15, 4) NOT NULL,
    reference_type VARCHAR(50),  -- pos_order, pos_refund, manual
    reference_id BIGINT UNSIGNED,
    reason VARCHAR(255),
    notes TEXT,
    created_at TIMESTAMP NULL,
    INDEX idx_tenant (tenant_id),
    INDEX idx_session (pos_session_id),
    INDEX idx_register (pos_cash_register_id),
    INDEX idx_type (type),
    INDEX idx_reference (reference_type, reference_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (pos_session_id) REFERENCES pos_sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (pos_cash_register_id) REFERENCES pos_cash_registers(id) ON DELETE CASCADE,
    FOREIGN KEY (admin_user_id) REFERENCES admins(id) ON DELETE RESTRICT
);
```

### 6. `pos_orders`
```sql
CREATE TABLE pos_orders (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    pos_session_id BIGINT UNSIGNED NOT NULL,
    pos_terminal_id BIGINT UNSIGNED NOT NULL,
    customer_id INT UNSIGNED NULL,
    admin_user_id INT UNSIGNED NOT NULL, -- cashier
    order_number VARCHAR(50) NOT NULL,
    status ENUM('draft', 'held', 'completed', 'voided', 'refunded', 'partially_refunded', 'exchanged') DEFAULT 'draft',
    subtotal DECIMAL(15, 4) NOT NULL DEFAULT 0.0000,
    discount_amount DECIMAL(15, 4) NOT NULL DEFAULT 0.0000,
    tax_amount DECIMAL(15, 4) NOT NULL DEFAULT 0.0000,
    shipping_amount DECIMAL(15, 4) NOT NULL DEFAULT 0.0000,
    total DECIMAL(15, 4) NOT NULL DEFAULT 0.0000,
    paid_amount DECIMAL(15, 4) NOT NULL DEFAULT 0.0000,
    due_amount DECIMAL(15, 4) NOT NULL DEFAULT 0.0000,
    currency VARCHAR(3) NOT NULL DEFAULT 'IRR',
    tax_inclusive TINYINT(1) DEFAULT 0,
    notes TEXT,
    held_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    voided_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY uk_order_number (order_number),
    INDEX idx_tenant (tenant_id),
    INDEX idx_session (pos_session_id),
    INDEX idx_terminal (pos_terminal_id),
    INDEX idx_customer (customer_id),
    INDEX idx_cashier (admin_user_id),
    INDEX idx_status (status),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (pos_session_id) REFERENCES pos_sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (pos_terminal_id) REFERENCES pos_terminals(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
    FOREIGN KEY (admin_user_id) REFERENCES admins(id) ON DELETE RESTRICT
);
```

### 7. `pos_order_items`
```sql
CREATE TABLE pos_order_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    pos_order_id BIGINT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    variant_id INT UNSIGNED NULL,
    inventory_source_id INT UNSIGNED NULL,
    name VARCHAR(255) NOT NULL,
    sku VARCHAR(100),
    barcode VARCHAR(100),
    quantity DECIMAL(12, 4) NOT NULL,
    unit_price DECIMAL(15, 4) NOT NULL,
    discount_amount DECIMAL(15, 4) NOT NULL DEFAULT 0.0000,
    tax_amount DECIMAL(15, 4) NOT NULL DEFAULT 0.0000,
    total DECIMAL(15, 4) NOT NULL,
    tax_rate DECIMAL(8, 4) DEFAULT 0.0000,
    serial_number VARCHAR(100),
    batch_number VARCHAR(100),
    expiry_date DATE,
    is_refunded TINYINT(1) DEFAULT 0,
    refunded_quantity DECIMAL(12, 4) DEFAULT 0.0000,
    metadata JSON,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_tenant (tenant_id),
    INDEX idx_order (pos_order_id),
    INDEX idx_product (product_id),
    INDEX idx_variant (variant_id),
    INDEX idx_serial (serial_number),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (pos_order_id) REFERENCES pos_orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT,
    FOREIGN KEY (variant_id) REFERENCES product_flat(id) ON DELETE SET NULL
);
```

### 8. `pos_payments`
```sql
CREATE TABLE pos_payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    pos_order_id BIGINT UNSIGNED NOT NULL,
    pos_cash_register_id BIGINT UNSIGNED NULL,
    payment_method_id INT UNSIGNED NOT NULL, -- maps to payment_methods table
    payment_method_code VARCHAR(50) NOT NULL, -- cash, card, wallet, gift_card, installment
    amount DECIMAL(15, 4) NOT NULL,
    reference_number VARCHAR(100), -- card transaction ID, cheque number
    status ENUM('pending', 'approved', 'declined', 'refunded') DEFAULT 'pending',
    gateway_response JSON, -- raw gateway response
    paid_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_tenant (tenant_id),
    INDEX idx_order (pos_order_id),
    INDEX idx_register (pos_cash_register_id),
    INDEX idx_method (payment_method_code),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (pos_order_id) REFERENCES pos_orders(id) ON DELETE CASCADE,
    FOREIGN KEY (pos_cash_register_id) REFERENCES pos_cash_registers(id) ON DELETE SET NULL
);
```

### 9. `pos_refunds`
```sql
CREATE TABLE pos_refunds (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    pos_order_id BIGINT UNSIGNED NOT NULL,
    pos_session_id BIGINT UNSIGNED NOT NULL,
    admin_user_id INT UNSIGNED NOT NULL,
    refund_number VARCHAR(50) NOT NULL,
    refund_method ENUM('cash', 'card', 'store_credit', 'wallet', 'original_payment') NOT NULL,
    total_amount DECIMAL(15, 4) NOT NULL,
    reason VARCHAR(255),
    status ENUM('pending', 'approved', 'completed', 'rejected') DEFAULT 'pending',
    notes TEXT,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY uk_refund_number (refund_number),
    INDEX idx_tenant (tenant_id),
    INDEX idx_order (pos_order_id),
    INDEX idx_session (pos_session_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (pos_order_id) REFERENCES pos_orders(id) ON DELETE CASCADE,
    FOREIGN KEY (pos_session_id) REFERENCES pos_sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (admin_user_id) REFERENCES admins(id) ON DELETE RESTRICT
);
```

### 10. `pos_refund_items`
```sql
CREATE TABLE pos_refund_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    pos_refund_id BIGINT UNSIGNED NOT NULL,
    pos_order_item_id BIGINT UNSIGNED NOT NULL,
    quantity DECIMAL(12, 4) NOT NULL,
    amount DECIMAL(15, 4) NOT NULL,
    reason VARCHAR(255),
    restock TINYINT(1) DEFAULT 1, -- return to inventory
    created_at TIMESTAMP NULL,
    INDEX idx_tenant (tenant_id),
    INDEX idx_refund (pos_refund_id),
    INDEX idx_order_item (pos_order_item_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (pos_refund_id) REFERENCES pos_refunds(id) ON DELETE CASCADE,
    FOREIGN KEY (pos_order_item_id) REFERENCES pos_order_items(id) ON DELETE CASCADE
);
```

### 11. `pos_exchanges`
```sql
CREATE TABLE pos_exchanges (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    original_order_id BIGINT UNSIGNED NOT NULL,
    new_order_id BIGINT UNSIGNED NOT NULL,
    pos_session_id BIGINT UNSIGNED NOT NULL,
    admin_user_id INT UNSIGNED NOT NULL,
    exchange_number VARCHAR(50) NOT NULL,
    price_difference DECIMAL(15, 4) NOT NULL DEFAULT 0.0000,
    -- positive = customer pays more, negative = refund to customer
    status ENUM('pending', 'completed') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY uk_exchange_number (exchange_number),
    INDEX idx_tenant (tenant_id),
    INDEX idx_original (original_order_id),
    INDEX idx_new (new_order_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (original_order_id) REFERENCES pos_orders(id) ON DELETE CASCADE,
    FOREIGN KEY (new_order_id) REFERENCES pos_orders(id) ON DELETE CASCADE,
    FOREIGN KEY (pos_session_id) REFERENCES pos_sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (admin_user_id) REFERENCES admins(id) ON DELETE RESTRICT
);
```

### 12. `pos_discounts`
```sql
CREATE TABLE pos_discounts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(50),
    type ENUM('percentage', 'fixed', 'buy_x_get_y') NOT NULL,
    value DECIMAL(15, 4) NOT NULL,
    min_order_amount DECIMAL(15, 4) NULL,
    max_discount_amount DECIMAL(15, 4) NULL,
    applies_to ENUM('order', 'item', 'shipping') DEFAULT 'order',
    is_active TINYINT(1) DEFAULT 1,
    starts_at TIMESTAMP NULL,
    ends_at TIMESTAMP NULL,
    usage_limit INT UNSIGNED NULL,
    usage_count INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY uk_tenant_code (tenant_id, code),
    INDEX idx_tenant (tenant_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);
```

### 13. `pos_receipts`
```sql
CREATE TABLE pos_receipts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    pos_order_id BIGINT UNSIGNED NULL,
    pos_refund_id BIGINT UNSIGNED NULL,
    receipt_number VARCHAR(50) NOT NULL,
    type ENUM('sale', 'refund', 'exchange', 'opening', 'closing', 'cash_movement') NOT NULL,
    template VARCHAR(50) DEFAULT 'default',
    delivery_method ENUM('print', 'email', 'sms', 'digital', 'none') DEFAULT 'print',
    recipient_email VARCHAR(255),
    recipient_phone VARCHAR(20),
    content_html TEXT,          -- rendered receipt HTML
    qr_code_data TEXT,          -- for digital verification
    printed TINYINT(1) DEFAULT 0,
    printed_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY uk_receipt_number (receipt_number),
    INDEX idx_tenant (tenant_id),
    INDEX idx_order (pos_order_id),
    INDEX idx_refund (pos_refund_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (pos_order_id) REFERENCES pos_orders(id) ON DELETE SET NULL,
    FOREIGN KEY (pos_refund_id) REFERENCES pos_refunds(id) ON DELETE SET NULL
);
```

### 14. `pos_employee_roles` (tenant-scoped extension of admin roles)
```sql
CREATE TABLE pos_employee_roles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(50) NOT NULL,
    -- owner, manager, supervisor, cashier
    permissions JSON NOT NULL,  -- {"pos.create_sale": true, "pos.refund": false, ...}
    is_system TINYINT(1) DEFAULT 0, -- cannot be deleted
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY uk_tenant_code (tenant_id, code),
    INDEX idx_tenant (tenant_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);
```

### 15. `pos_employee_assignments`
```sql
CREATE TABLE pos_employee_assignments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    admin_user_id INT UNSIGNED NOT NULL,
    pos_employee_role_id BIGINT UNSIGNED NOT NULL,
    pos_location_id BIGINT UNSIGNED NULL, -- null = all locations
    pin_code VARCHAR(10),                -- quick login PIN for POS terminal
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY uk_tenant_user (tenant_id, admin_user_id),
    INDEX idx_tenant (tenant_id),
    INDEX idx_role (pos_employee_role_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (admin_user_id) REFERENCES admins(id) ON DELETE CASCADE,
    FOREIGN KEY (pos_employee_role_id) REFERENCES pos_employee_roles(id) ON DELETE CASCADE,
    FOREIGN KEY (pos_location_id) REFERENCES pos_locations(id) ON DELETE SET NULL
);
```

### 16. `pos_inventory_reservations`
```sql
CREATE TABLE pos_inventory_reservations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    variant_id INT UNSIGNED NULL,
    inventory_source_id INT UNSIGNED NOT NULL,
    pos_order_id BIGINT UNSIGNED NULL,   -- null = manual reservation
    pos_order_item_id BIGINT UNSIGNED NULL,
    quantity DECIMAL(12, 4) NOT NULL,
    status ENUM('reserved', 'confirmed', 'released') DEFAULT 'reserved',
    expires_at TIMESTAMP NULL,            -- auto-release after N minutes
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_tenant (tenant_id),
    INDEX idx_product (product_id),
    INDEX idx_order (pos_order_id),
    INDEX idx_source (inventory_source_id),
    INDEX idx_status_expires (status, expires_at),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT,
    FOREIGN KEY (pos_order_id) REFERENCES pos_orders(id) ON DELETE CASCADE,
    FOREIGN KEY (pos_order_item_id) REFERENCES pos_order_items(id) ON DELETE CASCADE
);
```

### 17. `pos_offline_queues`
```sql
CREATE TABLE pos_offline_queues (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    pos_terminal_id BIGINT UNSIGNED NOT NULL,
    action VARCHAR(50) NOT NULL,          -- create_order, create_payment, etc.
    payload JSON NOT NULL,
    status ENUM('pending', 'processing', 'completed', 'failed', 'conflict') DEFAULT 'pending',
    local_id VARCHAR(100),                -- local ID assigned offline
    server_id BIGINT UNSIGNED NULL,       -- server ID after sync
    attempts INT UNSIGNED DEFAULT 0,
    last_error TEXT,
    synced_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_tenant (tenant_id),
    INDEX idx_terminal_status (pos_terminal_id, status),
    INDEX idx_local_id (local_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (pos_terminal_id) REFERENCES pos_terminals(id) ON DELETE CASCADE
);
```

### 18. `pos_product_cache` (offline product cache per terminal)
```sql
CREATE TABLE pos_product_cache (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    pos_terminal_id BIGINT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    cached_data JSON NOT NULL,
    last_synced_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY uk_terminal_product (pos_terminal_id, product_id),
    INDEX idx_tenant (tenant_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (pos_terminal_id) REFERENCES pos_terminals(id) ON DELETE CASCADE
);
```

### 19. `pos_hardware_events`
```sql
CREATE TABLE pos_hardware_events (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    pos_terminal_id BIGINT UNSIGNED NOT NULL,
    device_type ENUM('barcode_scanner', 'receipt_printer', 'cash_drawer', 'customer_display', 'weight_scale') NOT NULL,
    event_type VARCHAR(50) NOT NULL,      -- scan, print, open, display, weigh
    payload JSON,
    created_at TIMESTAMP NULL,
    INDEX idx_tenant (tenant_id),
    INDEX idx_terminal_device (pos_terminal_id, device_type),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (pos_terminal_id) REFERENCES pos_terminals(id) ON DELETE CASCADE
);
```

---

## Domain Events (Every business action fires an event)

```php
// Session Events
PosSessionOpened::class       // { session, cashier, opening_balance }
PosSessionClosed::class       // { session, cashier, closing_balance, difference }
PosSessionSuspended::class    // { session, cashier }

// Order Events
PosOrderCreated::class        // { order, items, cashier }
PosOrderHeld::class           // { order, cashier }
PosOrderResumed::class        // { order, cashier }
PosOrderCompleted::class      // { order, payments, cashier }
PosOrderVoided::class         // { order, cashier, reason }

// Payment Events
PosPaymentReceived::class     // { payment, order, method }
PosPaymentFailed::class       // { payment, order, error }
PosPaymentRefunded::class     // { payment, refund }

// Refund Events
PosRefundInitiated::class     // { refund, items, cashier }
PosRefundCompleted::class     // { refund, cashier }
PosRefundRejected::class      // { refund, cashier, reason }

// Exchange Events
PosExchangeCreated::class     // { exchange, original_order, new_order }

// Cash Movement Events
PosCashMovementCreated::class // { cash_movement, register }
PosDrawerOpened::class        // { register, cashier, reason }

// Inventory Events
PosStockReserved::class       // { reservation, product }
PosStockDeducted::class       // { order_item, product, qty }
PosStockReleased::class       // { reservation, product }
PosLowStockAlert::class       // { product, current_stock, threshold }

// Customer Events
PosCustomerCreated::class     // { customer, cashier }
PosCustomerAttached::class    // { order, customer, cashier }

// Discount Events
PosDiscountApplied::class     // { order, discount, amount }
PosDiscountRemoved::class     // { order, discount }

// Hardware Events
PosBarcodeScanned::class      // { terminal, barcode, product }
PosReceiptPrinted::class      // { receipt, terminal, printer }
PosDrawerOpened::class        // { terminal, register, cashier }

// Offline Events
PosOfflineTransactionQueued::class  // { queue_item }
PosOfflineTransactionSynced::class  // { queue_item, server_id }
PosOfflineConflictDetected::class   // { queue_item, conflict_details }

// Marketplace Events
PosMarketplaceOrderCreated::class   // { order, seller_id, commission }
PosMarketplaceCommissionCalculated::class // { order, seller, commission }
```

---

## Observers (Cross-cutting concerns)

Every model has an Observer for:
1. **AuditTrailObserver** — writes to `pos_audit_log` table
2. **TenantIsolationObserver** — verifies `tenant_id` is set before save
3. **AIDataPipelineObserver** — fires `ai:pos_event` with structured payload

### `pos_audit_log` table:
```sql
CREATE TABLE pos_audit_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    admin_user_id INT UNSIGNED NULL,
    event_type VARCHAR(100) NOT NULL,
    entity_type VARCHAR(100) NOT NULL,
    entity_id BIGINT UNSIGNED NOT NULL,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP NULL,
    INDEX idx_tenant_entity (tenant_id, entity_type, entity_id),
    INDEX idx_event_type (event_type),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);
```

---

## Repositories (Data Access)

```
PosLocationRepository
PosTerminalRepository
PosSessionRepository
PosCashRegisterRepository
PosCashMovementRepository
PosOrderRepository
PosOrderItemRepository
PosPaymentRepository
PosRefundRepository
PosRefundItemRepository
PosExchangeRepository
PosDiscountRepository
PosReceiptRepository
PosEmployeeRoleRepository
PosEmployeeAssignmentRepository
PosInventoryReservationRepository
PosOfflineQueueRepository
PosProductCacheRepository
PosHardwareEventRepository
PosAuditLogRepository
```

---

## Services (Business Logic)

```
PosSessionService          — open/close/suspend sessions, validate balances
PosCheckoutService         — cart → order, apply discounts, calculate tax
PosPaymentService          — process payments, split payments, refund payments
PosRefundService           — full/partial refunds, restock logic
PosExchangeService         — exchange order items, price difference
PosInventoryService        — reserve, deduct, release stock, low stock alerts
PosReceiptService          — generate receipt HTML/PDF, send via email/SMS
PosHardwareService         — abstraction layer for barcode/printer/drawer/scale
PosOfflineSyncService      — queue transactions, sync on reconnect, conflict resolution
PosDiscountService         — validate/apply discounts, stacking rules
PosCashRegisterService     — manage cash in/out, opening/closing balance
PosReportingService        — generate reports with filters/aggregations
PosMarketplaceService      — seller order routing, commission calculation
PosAIDataService           — collect events, build training datasets
```

---

## Policies (Authorization)

```php
// Every sensitive action has a Gate policy
Gate::define('pos.create_sale', fn ($user) => $user->can('pos.create_sale'));
Gate::define('pos.cancel_sale', fn ($user) => $user->can('pos.cancel_sale'));
Gate::define('pos.process_refund', fn ($user) => $user->can('pos.process_refund'));
Gate::define('pos.apply_discount', fn ($user) => $user->can('pos.apply_discount'));
Gate::define('pos.change_price', fn ($user) => $user->can('pos.change_price'));
Gate::define('pos.open_drawer', fn ($user) => $user->can('pos.open_drawer'));
Gate::define('pos.view_reports', fn ($user) => $user->can('pos.view_reports'));
Gate::define('pos.edit_products', fn ($user) => $user->can('pos.edit_products'));
Gate::define('pos.access_customers', fn ($user) => $user->can('pos.access_customers'));
Gate::define('pos.manage_sessions', fn ($user) => $user->can('pos.manage_sessions'));
Gate::define('pos.manage_employees', fn ($user) => $user->can('pos.manage_employees'));
```

---

## Queued Jobs

```
ProcessPosPayment          — async card payment processing
SyncPosOfflineTransaction  — process queued offline transactions
SendPosReceipt             — email/SMS receipt delivery
GeneratePosReport          — heavy report generation
CheckLowStockAlerts        — periodic inventory threshold check
CalculateMarketplaceCommissions — commission calculation job
CleanupExpiredReservations — release expired inventory holds
ExportPosData              — CSV/Excel export of POS data
TrainAIModel               — periodic AI model retraining from collected data
```

---

## Notifications

```
PosOrderCompletedNotification     — to customer (receipt)
PosLowStockNotification           — to admin/manager
PosSessionClosedNotification      — to manager (daily summary)
PosCashDifferenceNotification     — to manager (discrepancy alert)
PosRefundProcessedNotification    — to customer
PosExchangeCompletedNotification  — to customer
PosFraudAlertNotification         — to admin (unusual activity)
PosOfflineSyncFailedNotification  — to admin (sync error)
```

---

## Phase 2: Multi-Tenant Architecture

### Tenant Isolation Strategy (3 alternatives considered)

**Alt 1: Database-per-tenant** — separate MySQL DB per tenant
- Rejected: Cannot share product catalog across tenants, high ops overhead

**Alt 2: Schema-per-tenant** — PostgreSQL schemas per tenant  
- Rejected: Bagisto uses MySQL, not PostgreSQL

**Alt 3: Row-level with global scope ✅ SELECTED**
- Every POS table has `tenant_id` foreign key
- Global scope `TenantIsolationScope` auto-applied to every POS model
- Middleware injects `tenant_id` from resolved tenant
- All queries automatically scoped: `WHERE tenant_id = ?`

### Implementation:
```php
// Every POS model uses this trait
trait BelongsToTenant {
    protected static function booted(): void {
        static::addGlobalScope(new TenantIsolationScope);
        
        static::creating(function ($model) {
            if (!$model->tenant_id) {
                $model->tenant_id = app('current_tenant')->id;
            }
        });
    }
}

class TenantIsolationScope implements Scope {
    public function apply(Builder $builder, Model $model): void {
        if ($tenant = app('current_tenant')) {
            $builder->where($model->getTable() . '.tenant_id', $tenant->id);
        }
    }
}
```

### Automated Tenant Isolation Tests:
```php
test('cashier from tenant A cannot access tenant B orders', function () {
    $tenantA = createTenant();
    $tenantB = createTenant();
    $orderB = createPosOrder(tenant: $tenantB);
    
    $this->actingAsTenant($tenantA);
    
    $response = $this->getJson("/api/pos/orders/{$orderB->id}");
    $response->assertNotFound(); // 404, not 403 — don't leak existence
});

test('all POS queries are tenant scoped', function () {
    // Verify global scope is applied on all 17 POS models
    foreach (getPosModelClasses() as $class) {
        $query = $class::query();
        $sql = $query->toSql();
        expect($sql)->toContain('tenant_id');
    }
});
```

---

## Phase 3: Inventory Integration

### Stock Reservation Flow:
```
1. Item added to POS cart → InventoryService::reserveStock()
   - Creates pos_inventory_reservations row (status: reserved, expires: now+15min)
   - Decrements available qty in inventory_sources

2. Order completed → InventoryService::confirmReservation()
   - Updates reservation to status: confirmed
   - Stock is permanently deducted

3. Order voided/timeout → InventoryService::releaseReservation()
   - Updates reservation to status: released
   - Increments available qty back

4. Low stock → InventoryService::checkThreshold()
   - Fires PosLowStockAlert event
   - Sends PosLowStockNotification
```

### Conflict Resolution (Online vs Offline):
```
Scenario: Online customer buys last unit while POS cashier has it in cart

Resolution:
1. POS cart reserves stock (15-min hold)
2. Online checkout checks reservations → finds it reserved → shows "Temporarily Unavailable"
3. If POS cart expires without completing → stock released back
4. If POS completes → online customer sees "Out of Stock"

Priority: First reserve wins. POS reservations visible to online inventory queries.
```

---

## Phase 4-14: Remaining Phases Detail

### Phase 5: Checkout Engine
```
Cart → Discount Application → Tax Calculation → Customer Attachment → 
Payment Split → Order Completion → Receipt Generation → Stock Deduction
```

Split payment supported natively via multiple `pos_payments` rows per order.

### Phase 6: Payment Architecture
Pluggable via Payment Provider interface:
```php
interface PosPaymentProvider {
    public function process(array $data): PosPaymentResult;
    public function refund(PosPayment $payment, float $amount): PosPaymentResult;
    public function void(PosPayment $payment): PosPaymentResult;
}
```

Built-in providers: Cash, Card (generic terminal interface), Wallet, Gift Card, Store Credit.
Extensible via service container tagging.

### Phase 10: Offline POS
- IndexedDB local store in POS UI (Vue 3)
- Transaction queue in `pos_offline_queues`
- `/api/pos/sync` endpoint for batch sync
- Conflict resolution: last-write-wins with admin override

### Phase 13: Marketplace
- `pos_orders` has `seller_id` column (null = platform store)
- Marketplace orders split: seller gets net amount, platform gets commission
- Commission calculated via `CalculateMarketplaceCommissions` job
- Seller inventory isolated per `inventory_source_id`

---

## POS Permission Matrix

| Permission            | Owner | Manager | Supervisor | Cashier |
|-----------------------|-------|---------|------------|---------|
| pos.create_sale       | ✅     | ✅      | ✅         | ✅      |
| pos.cancel_sale       | ✅     | ✅      | ✅         | ❌      |
| pos.process_refund    | ✅     | ✅      | ⚠️ (limit) | ❌      |
| pos.apply_discount    | ✅     | ✅      | ⚠️ (limit) | ❌      |
| pos.change_price      | ✅     | ✅      | ❌         | ❌      |
| pos.open_drawer       | ✅     | ✅      | ✅         | ✅      |
| pos.view_reports      | ✅     | ✅      | ✅         | ❌      |
| pos.edit_products     | ✅     | ❌      | ❌         | ❌      |
| pos.access_customers  | ✅     | ✅      | ✅         | ✅      |
| pos.manage_sessions   | ✅     | ✅      | ❌         | ❌      |
| pos.manage_employees  | ✅     | ❌      | ❌         | ❌      |

---

## Implementation Sequence (Build Order)

### Sprint 1 (Phase 1 + 2): Foundation
1. POS package scaffold (3-file registration)
2. All 17 migrations
3. All Contracts + Models + Proxies + Repositories
4. TenantIsolationScope + tests
5. POSEmployeeRole seeder + permission matrix
6. AuditTrailObserver

### Sprint 2 (Phase 5 + 6): Core POS Flow  
7. PosSessionService (open/close session)
8. PosCheckoutService (cart → order)
9. PosPaymentService (split payments)
10. Cash payment provider
11. Receipt generation (thermal template)

### Sprint 3 (Phase 4 + 3): Products + Inventory
12. Product search + barcode lookup
13. PosInventoryService (reserve/confirm/release)
14. Low stock alerts
15. Inventory conflict resolution

### Sprint 4 (Phase 7 + 8): Customers + Employees
16. Customer CRUD in POS
17. Purchase history endpoint
18. Employee PIN login
19. Permission enforcement

### Sprint 5 (Phase 9 + 12): Receipts + Reports
20. Receipt templates (thermal, PDF, email)
21. Daily/Weekly/Monthly reports
22. Cashier performance report
23. Export API

### Sprint 6 (Phase 10 + 11): Offline + Hardware
24. Offline queue service
25. Sync engine
26. Hardware abstraction layer
27. Barcode scanner integration

### Sprint 7 (Phase 13 + 14): Marketplace + AI
28. Marketplace order routing
29. Commission calculation
30. AI data pipeline
31. Recommendation events

### Sprint 8 (Phase 15 + 16): UI + Testing
32. Vue 3 POS terminal UI
33. Admin management Blade views
34. Full test suite (Phases 1-12)
35. E2E tests

---

## Final Architecture Scorecard (Target)
- **Architecture Score**: 95/100 — Clean separation, plugin-ready
- **Code Quality Score**: 90/100 — PSR-12, Pint-compliant, typed
- **Security Score**: 95/100 — Tenant isolation proven, permission-gated
- **Test Coverage**: 85%+ — Unit, Feature, Integration, E2E
- **Performance**: Sub-100ms order creation, <50ms product search
- **Technical Debt**: <5% — No TODOs, no placeholders, no stubs
- **Confidence Level**: Production-ready for single-store; marketplace validated

---

## Business Operation Contract

For EVERY business operation, the following are mandatory:
1. **Domain Event** — fired via `event(new PosXxxYyy($data))`
2. **Audit Trail** — Observer writes to `pos_audit_log`
3. **Permission Check** — `Gate::authorize('pos.action')` or Policy
4. **Notification Trigger** — if user-facing, fire notification
5. **Analytics Event** — structured data for reporting
6. **AI Data Event** — `AIDataPipelineObserver` collects for model training

No business action happens silently. Every state change produces:
- An event
- An audit log entry
- An analytics data point
- An AI training sample (if applicable)
