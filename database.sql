CREATE DATABASE IF NOT EXISTS buildstock;
USE buildstock;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(80), email VARCHAR(160), password_hash VARCHAR(64),
  full_name VARCHAR(120), role VARCHAR(20)
);

CREATE TABLE suppliers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(160) UNIQUE, contact VARCHAR(120), phone VARCHAR(60), status VARCHAR(20)
);

CREATE TABLE products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sku VARCHAR(60) UNIQUE, name VARCHAR(160), category VARCHAR(120),
  supplier_name VARCHAR(160), price DECIMAL(10,2), stock INT, reorder_level INT,
  FOREIGN KEY (supplier_name) REFERENCES suppliers(name)
);

CREATE TABLE purchase_orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  po_number VARCHAR(40) UNIQUE, supplier_name VARCHAR(160), product_sku VARCHAR(60),
  quantity INT, ordered_at DATETIME, expected_at DATETIME, amount DECIMAL(10,2), status VARCHAR(30),
  FOREIGN KEY (supplier_name) REFERENCES suppliers(name),
  FOREIGN KEY (product_sku) REFERENCES products(sku)
);

CREATE TABLE sales (
  id INT AUTO_INCREMENT PRIMARY KEY,
  receipt_no VARCHAR(40) UNIQUE, sold_at DATETIME, customer VARCHAR(160),
  payment_method VARCHAR(60), status VARCHAR(30), total DECIMAL(10,2)
);

-- Junction table between sales and products
CREATE TABLE sale_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sale_receipt VARCHAR(40), product_sku VARCHAR(60), product_name VARCHAR(160),
  quantity INT, unit_price DECIMAL(10,2),
  FOREIGN KEY (sale_receipt) REFERENCES sales(receipt_no),
  FOREIGN KEY (product_sku) REFERENCES products(sku)
);

CREATE TABLE stock_movements (
  id INT AUTO_INCREMENT PRIMARY KEY,
  moved_at DATETIME, product_sku VARCHAR(60), product_name VARCHAR(160), type VARCHAR(10),
  quantity INT, reference VARCHAR(160), recorded_by VARCHAR(120),
  FOREIGN KEY (product_sku) REFERENCES products(sku)
);

-- Exactly five sample records per table.
INSERT INTO users (username, email, password_hash, full_name, role) VALUES
('admin', 'admin@buildstock.local', SHA2('admin123', 256), 'System Administrator', 'admin'),
('staff', 'staff@buildstock.local', SHA2('staff123', 256), 'Staff Member', 'staff'),
('maria.santos', 'maria.santos@buildstock.local', SHA2('staff123', 256), 'Maria Santos', 'staff'),
('juan.reyes', 'juan.reyes@buildstock.local', SHA2('staff123', 256), 'Juan Reyes', 'staff'),
('anna.mendoza', 'anna.mendoza@buildstock.local', SHA2('admin123', 256), 'Anna Mendoza', 'admin');

INSERT INTO suppliers (name, contact, phone, status) VALUES
('Pacific Cement Corp.', 'Ramon Villanueva', '0917 555 0142', 'Active'),
('Luzon Steel Works', 'Angela Tan', '0928 555 0117', 'Active'),
('Metro Plumbing Supply', 'Roberto Flores', '0917 555 0201', 'Active'),
('Prime Electrical Depot', 'Grace Lim', '0917 555 0202', 'Active'),
('Solid Tools Trading', 'Noel Ramos', '0917 555 0203', 'Active');

INSERT INTO products (sku, name, category, supplier_name, price, stock, reorder_level) VALUES
('CEM-040', 'Portland Cement 40kg', 'Cement and Aggregates', 'Pacific Cement Corp.', 265.00, 12, 20),
('STL-D10', 'Deformed Bar 10mm x 6m', 'Steel and Rebar', 'Luzon Steel Works', 198.00, 340, 50),
('PLB-ELB', 'PVC Elbow 2 inch', 'Plumbing', 'Metro Plumbing Supply', 35.00, 75, 20),
('ELC-W12', 'Electrical Wire 12 AWG 100m', 'Electrical', 'Prime Electrical Depot', 2850.00, 18, 10),
('TLS-HMR', 'Claw Hammer 16oz', 'Tools', 'Solid Tools Trading', 320.00, 42, 10);

INSERT INTO purchase_orders (po_number, supplier_name, product_sku, quantity, ordered_at, expected_at, amount, status) VALUES
('PO-2041', 'Pacific Cement Corp.', 'CEM-040', 200, '2026-10-06', '2026-10-12', 53000.00, 'In transit'),
('PO-2042', 'Luzon Steel Works', 'STL-D10', 500, '2026-10-07', '2026-10-14', 99000.00, 'Received'),
('PO-2043', 'Metro Plumbing Supply', 'PLB-ELB', 100, '2026-10-11', '2026-10-16', 3500.00, 'Pending'),
('PO-2044', 'Prime Electrical Depot', 'ELC-W12', 20, '2026-10-11', '2026-10-18', 57000.00, 'In transit'),
('PO-2045', 'Solid Tools Trading', 'TLS-HMR', 50, '2026-10-09', '2026-10-13', 16000.00, 'Received');

INSERT INTO sales (receipt_no, sold_at, customer, payment_method, status, total) VALUES
('#S-10481', '2026-10-10 08:10:00', 'Walk-in customer', 'Cash', 'Paid', 990.00),
('#S-10482', '2026-10-10 08:40:00', 'Dela Cruz Builders', 'Bank transfer', 'Paid', 7950.00),
('#S-10483', '2026-10-11 09:15:00', 'Ramos Construction', 'Cash', 'Paid', 960.00),
('#S-10484', '2026-10-11 10:20:00', 'Walk-in customer', 'GCash', 'Paid', 280.00),
('#S-10485', '2026-10-11 13:05:00', 'Lopez Builders', 'Bank transfer', 'Paid', 5700.00);

INSERT INTO sale_items (sale_receipt, product_sku, product_name, quantity, unit_price) VALUES
('#S-10481', 'STL-D10', 'Deformed Bar 10mm x 6m', 5, 198.00),
('#S-10482', 'CEM-040', 'Portland Cement 40kg', 30, 265.00),
('#S-10483', 'TLS-HMR', 'Claw Hammer 16oz', 3, 320.00),
('#S-10484', 'PLB-ELB', 'PVC Elbow 2 inch', 8, 35.00),
('#S-10485', 'ELC-W12', 'Electrical Wire 12 AWG 100m', 2, 2850.00);

INSERT INTO stock_movements (moved_at, product_sku, product_name, type, quantity, reference, recorded_by) VALUES
('2026-10-10 08:10:00', 'STL-D10', 'Deformed Bar 10mm x 6m', 'out', -5, '#S-10481', 'Staff Member'),
('2026-10-10 08:40:00', 'CEM-040', 'Portland Cement 40kg', 'out', -30, '#S-10482', 'System Administrator'),
('2026-10-11 09:15:00', 'TLS-HMR', 'Claw Hammer 16oz', 'out', -3, '#S-10483', 'Maria Santos'),
('2026-10-11 10:20:00', 'PLB-ELB', 'PVC Elbow 2 inch', 'out', -8, '#S-10484', 'Juan Reyes'),
('2026-10-11 13:05:00', 'ELC-W12', 'Electrical Wire 12 AWG 100m', 'out', -2, '#S-10485', 'Maria Santos');
