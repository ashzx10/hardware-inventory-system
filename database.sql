CREATE DATABASE IF NOT EXISTS buildstock;
USE buildstock;

-- User accounts for login
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(80),
  email VARCHAR(160),
  password_hash VARCHAR(64),
  full_name VARCHAR(120),
  role VARCHAR(20)
);

-- Supplier information
CREATE TABLE suppliers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(160) UNIQUE,
  contact VARCHAR(120),
  phone VARCHAR(60),
  status VARCHAR(20)
);

-- Products in the hardware store
CREATE TABLE products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sku VARCHAR(60) UNIQUE,
  name VARCHAR(160),
  category VARCHAR(120),
  supplier_name VARCHAR(160),
  price DECIMAL(10,2),
  stock INT,
  reorder_level INT,
  FOREIGN KEY (supplier_name) REFERENCES suppliers(name)
);

-- Purchase orders from suppliers
CREATE TABLE purchase_orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  po_number VARCHAR(40) UNIQUE,
  supplier_name VARCHAR(160),
  product_sku VARCHAR(60),
  quantity INT,
  ordered_at DATETIME,
  expected_at DATETIME,
  amount DECIMAL(10,2),
  status VARCHAR(30),
  FOREIGN KEY (supplier_name) REFERENCES suppliers(name),
  FOREIGN KEY (product_sku) REFERENCES products(sku)
);

-- Sales made to customers
CREATE TABLE sales (
  id INT AUTO_INCREMENT PRIMARY KEY,
  receipt_no VARCHAR(40) UNIQUE,
  sold_at DATETIME,
  customer VARCHAR(160),
  payment_method VARCHAR(60),
  status VARCHAR(30),
  total DECIMAL(10,2)
);

-- Products included in every sale
CREATE TABLE sale_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sale_receipt VARCHAR(40),
  product_sku VARCHAR(60),
  product_name VARCHAR(160),
  quantity INT,
  unit_price DECIMAL(10,2),
  FOREIGN KEY (sale_receipt) REFERENCES sales(receipt_no),
  FOREIGN KEY (product_sku) REFERENCES products(sku)
);

-- Record of stock added, removed, or adjusted
CREATE TABLE stock_movements (
  id INT AUTO_INCREMENT PRIMARY KEY,
  moved_at DATETIME,
  product_sku VARCHAR(60),
  product_name VARCHAR(160),
  type VARCHAR(10),
  quantity INT,
  reference VARCHAR(160),
  recorded_by VARCHAR(120),
  FOREIGN KEY (product_sku) REFERENCES products(sku)
);

INSERT INTO users (username, email, password_hash, full_name, role)
VALUES
('admin', 'admin@buildstock.local', SHA2('admin123', 256), 'System Administrator', 'admin'),
('staff', NULL, SHA2('staff123', 256), 'Staff Member', 'staff');

INSERT INTO suppliers (name, contact, phone, status)
VALUES
('Pacific Cement Corp.', 'Ramon Villanueva', '0917 555 0142', 'Active'),
('Luzon Steel Works', 'Angela Tan', '0928 555 0117', 'Active'),
('Calabarzon Lumber Co.', 'Jose Cruz', '0905 555 0119', 'On hold');

INSERT INTO products (sku, name, category, supplier_name, price, stock, reorder_level)
VALUES
('CEM-040', 'Portland Cement 40kg', 'Cement and Aggregates', 'Pacific Cement Corp.', 265.00, 12, 20),
('STL-D10', 'Deformed Bar 10mm x 6m', 'Steel and Rebar', 'Luzon Steel Works', 198.00, 340, 50),
('LMB-P12', 'Marine Plywood 1/2 inch', 'Lumber and Plywood', 'Calabarzon Lumber Co.', 1150.00, 0, 15),
('PLB-P20', 'PVC Pipe 2 inch x 3m', 'Plumbing', 'Pacific Cement Corp.', 142.00, 9, 20),
('FST-N03', 'Common Wire Nails 3 inch', 'Fasteners and Hardware', 'Luzon Steel Works', 78.00, 96, 20),
('PNT-E04', 'Latex Paint Flat White 4L', 'Paint and Finishes', 'Calabarzon Lumber Co.', 640.00, 58, 15);

INSERT INTO purchase_orders (po_number, supplier_name, product_sku, quantity, ordered_at, expected_at, amount, status)
VALUES
('PO-2041', 'Pacific Cement Corp.', 'CEM-040', 200, '2026-10-06', '2026-10-12', 53000.00, 'In transit'),
('PO-2040', 'Luzon Steel Works', 'STL-D10', 500, '2026-10-03', '2026-10-09', 99000.00, 'Received'),
('PO-2039', 'Calabarzon Lumber Co.', 'LMB-P12', 40, '2026-09-28', '2026-10-14', 46000.00, 'Pending');

INSERT INTO sales (receipt_no, sold_at, customer, payment_method, status, total)
VALUES
('#S-10482', '2026-10-10 08:40:00', 'Dela Cruz Builders', 'Bank transfer', 'Paid', 15870.00),
('#S-10481', '2026-10-10 08:10:00', 'Walk-in customer', 'Cash', 'Paid', 1420.00),
('#S-10480', '2026-10-09 13:25:00', 'Santos Hardware', 'Credit', 'Credit', 21930.00),
('#S-10479', '2026-10-09 09:30:00', 'Walk-in customer', 'GCash', 'Paid', 390.00);

INSERT INTO sale_items (sale_receipt, product_sku, product_name, quantity, unit_price)
VALUES
('#S-10482', 'CEM-040', 'Portland Cement 40kg', 30, 265.00),
('#S-10482', 'STL-D10', 'Deformed Bar 10mm x 6m', 40, 198.00),
('#S-10481', 'FST-N03', 'Common Wire Nails 3 inch', 10, 78.00),
('#S-10481', 'PNT-E04', 'Latex Paint Flat White 4L', 1, 640.00),
('#S-10480', 'PLB-P20', 'PVC Pipe 2 inch x 3m', 15, 142.00),
('#S-10480', 'STL-D10', 'Deformed Bar 10mm x 6m', 100, 198.00),
('#S-10479', 'FST-N03', 'Common Wire Nails 3 inch', 5, 78.00);

INSERT INTO stock_movements (moved_at, product_sku, product_name, type, quantity, reference, recorded_by)
VALUES
('2026-10-10 09:14:00', 'STL-D10', 'Deformed Bar 10mm x 6m', 'in', 200, 'PO-2040', 'System Administrator'),
('2026-10-10 08:40:00', 'CEM-040', 'Portland Cement 40kg', 'out', -30, '#S-10482', 'System Administrator'),
('2026-10-09 16:02:00', 'FST-N03', 'Common Wire Nails 3 inch', 'adj', -2, 'Count correction', 'Staff Member'),
('2026-10-09 13:25:00', 'PLB-P20', 'PVC Pipe 2 inch x 3m', 'out', -15, '#S-10480', 'Staff Member'),
('2026-10-08 10:05:00', 'PNT-E04', 'Latex Paint Flat White 4L', 'in', 48, 'PO-2038', 'System Administrator');

-- Additional sample data: five new records for every table.
-- These rows are appended and do not replace the sample data above.
INSERT INTO users (username, email, password_hash, full_name, role)
VALUES
('maria.santos', 'maria.santos@buildstock.local', SHA2('staff123', 256), 'Maria Santos', 'staff'),
('juan.reyes', 'juan.reyes@buildstock.local', SHA2('staff123', 256), 'Juan Reyes', 'staff'),
('liza.cruz', 'liza.cruz@buildstock.local', SHA2('staff123', 256), 'Liza Cruz', 'staff'),
('carlos.garcia', 'carlos.garcia@buildstock.local', SHA2('staff123', 256), 'Carlos Garcia', 'staff'),
('anna.mendoza', 'anna.mendoza@buildstock.local', SHA2('admin123', 256), 'Anna Mendoza', 'admin');

INSERT INTO suppliers (name, contact, phone, status)
VALUES
('Metro Plumbing Supply', 'Roberto Flores', '0917 555 0201', 'Active'),
('Prime Electrical Depot', 'Grace Lim', '0917 555 0202', 'Active'),
('Solid Tools Trading', 'Noel Ramos', '0917 555 0203', 'Active'),
('Southline Paint Center', 'Karen Diaz', '0917 555 0204', 'Active'),
('Reliable Fasteners Inc.', 'Victor Tan', '0917 555 0205', 'Active');

INSERT INTO products (sku, name, category, supplier_name, price, stock, reorder_level)
VALUES
('PLB-ELB', 'PVC Elbow 2 inch', 'Plumbing', 'Metro Plumbing Supply', 35.00, 75, 20),
('ELC-W12', 'Electrical Wire 12 AWG 100m', 'Electrical', 'Prime Electrical Depot', 2850.00, 18, 10),
('TLS-HMR', 'Claw Hammer 16oz', 'Tools', 'Solid Tools Trading', 320.00, 42, 10),
('PNT-PRM', 'Concrete Primer 4L', 'Paint and Finishes', 'Southline Paint Center', 780.00, 14, 10),
('FST-BLT', 'Hex Bolt 3/8 inch', 'Fasteners and Hardware', 'Reliable Fasteners Inc.', 12.00, 500, 100);

INSERT INTO purchase_orders (po_number, supplier_name, product_sku, quantity, ordered_at, expected_at, amount, status)
VALUES
('PO-2042', 'Metro Plumbing Supply', 'PLB-ELB', 100, '2026-10-11', '2026-10-16', 3500.00, 'Pending'),
('PO-2043', 'Prime Electrical Depot', 'ELC-W12', 20, '2026-10-11', '2026-10-18', 57000.00, 'In transit'),
('PO-2044', 'Solid Tools Trading', 'TLS-HMR', 50, '2026-10-09', '2026-10-13', 16000.00, 'Received'),
('PO-2045', 'Southline Paint Center', 'PNT-PRM', 30, '2026-10-12', '2026-10-19', 23400.00, 'Pending'),
('PO-2046', 'Reliable Fasteners Inc.', 'FST-BLT', 300, '2026-10-10', '2026-10-15', 3600.00, 'In transit');

INSERT INTO sales (receipt_no, sold_at, customer, payment_method, status, total)
VALUES
('#S-10483', '2026-10-11 09:15:00', 'Ramos Construction', 'Cash', 'Paid', 960.00),
('#S-10484', '2026-10-11 10:20:00', 'Walk-in customer', 'GCash', 'Paid', 284.00),
('#S-10485', '2026-10-11 13:05:00', 'Lopez Builders', 'Bank transfer', 'Paid', 5700.00),
('#S-10486', '2026-10-12 08:45:00', 'Santiago Home Repair', 'Cash', 'Paid', 1560.00),
('#S-10487', '2026-10-12 14:30:00', 'Walk-in customer', 'Credit', 'Credit', 1360.00);

INSERT INTO sale_items (sale_receipt, product_sku, product_name, quantity, unit_price)
VALUES
('#S-10483', 'TLS-HMR', 'Claw Hammer 16oz', 3, 320.00),
('#S-10484', 'PLB-ELB', 'PVC Elbow 2 inch', 8, 35.00),
('#S-10485', 'ELC-W12', 'Electrical Wire 12 AWG 100m', 2, 2850.00),
('#S-10486', 'PNT-PRM', 'Concrete Primer 4L', 2, 780.00),
('#S-10487', 'FST-BLT', 'Hex Bolt 3/8 inch', 100, 12.00);

INSERT INTO stock_movements (moved_at, product_sku, product_name, type, quantity, reference, recorded_by)
VALUES
('2026-10-11 09:15:00', 'TLS-HMR', 'Claw Hammer 16oz', 'out', -3, '#S-10483', 'Maria Santos'),
('2026-10-11 10:20:00', 'PLB-ELB', 'PVC Elbow 2 inch', 'out', -8, '#S-10484', 'Juan Reyes'),
('2026-10-11 13:05:00', 'ELC-W12', 'Electrical Wire 12 AWG 100m', 'out', -2, '#S-10485', 'Liza Cruz'),
('2026-10-12 08:45:00', 'PNT-PRM', 'Concrete Primer 4L', 'out', -2, '#S-10486', 'Carlos Garcia'),
('2026-10-12 14:30:00', 'FST-BLT', 'Hex Bolt 3/8 inch', 'out', -100, '#S-10487', 'Anna Mendoza');
