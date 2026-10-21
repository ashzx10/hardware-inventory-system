CREATE DATABASE primebuild;
USE primebuild;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(80) UNIQUE, email VARCHAR(160), password_hash VARCHAR(64),
  full_name VARCHAR(120), role VARCHAR(20),
  is_archived TINYINT(1) DEFAULT 0, archived_by VARCHAR(120), archived_at DATETIME, archive_reason VARCHAR(255)
);

-- Each category has a prefix used to generate Stock Keeping Units (CEM-0007)
CREATE TABLE categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) UNIQUE, prefix VARCHAR(5), is_archived TINYINT(1) DEFAULT 0
);

CREATE TABLE suppliers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(160) UNIQUE, contact VARCHAR(120), phone VARCHAR(60), status VARCHAR(20),
  is_archived TINYINT(1) DEFAULT 0, archived_by VARCHAR(120), archived_at DATETIME, archive_reason VARCHAR(255)
);

CREATE TABLE products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sku VARCHAR(60) UNIQUE, name VARCHAR(160), category VARCHAR(120),
  supplier_name VARCHAR(160), price DECIMAL(10,2), stock INT, reorder_level INT,
  is_archived TINYINT(1) DEFAULT 0, archived_by VARCHAR(120), archived_at DATETIME, archive_reason VARCHAR(255),
  FOREIGN KEY (supplier_name) REFERENCES suppliers(name)
);

CREATE TABLE purchase_orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  po_number VARCHAR(40) UNIQUE, supplier_name VARCHAR(160), product_sku VARCHAR(60),
  quantity INT, ordered_at DATETIME, expected_at DATETIME, amount DECIMAL(10,2), status VARCHAR(30),
  is_archived TINYINT(1) DEFAULT 0, archived_by VARCHAR(120), archived_at DATETIME, archive_reason VARCHAR(255),
  FOREIGN KEY (supplier_name) REFERENCES suppliers(name),
  FOREIGN KEY (product_sku) REFERENCES products(sku)
);

-- Payment methods: Cash, Bank Transfer, E-Wallet (no Credit)
CREATE TABLE sales (
  id INT AUTO_INCREMENT PRIMARY KEY,
  receipt_no VARCHAR(40) UNIQUE, sold_at DATETIME, customer VARCHAR(160) DEFAULT '',
  payment_method VARCHAR(60), status VARCHAR(30), total DECIMAL(10,2),
  amount_received DECIMAL(10,2), change_amount DECIMAL(10,2), payment_reference VARCHAR(80), cashier VARCHAR(120),
  is_archived TINYINT(1) DEFAULT 0, archived_by VARCHAR(120), archived_at DATETIME, archive_reason VARCHAR(255)
);

CREATE TABLE sale_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sale_receipt VARCHAR(40), product_sku VARCHAR(60), product_name VARCHAR(160),
  quantity INT, unit_price DECIMAL(10,2),
  FOREIGN KEY (sale_receipt) REFERENCES sales(receipt_no),
  FOREIGN KEY (product_sku) REFERENCES products(sku)
);

-- reference is always filled by the system (receipt number, purchase order number or movement number)
CREATE TABLE stock_movements (
  id INT AUTO_INCREMENT PRIMARY KEY,
  movement_no VARCHAR(20) UNIQUE, moved_at DATETIME, product_sku VARCHAR(60), product_name VARCHAR(160),
  type VARCHAR(10), quantity INT, reference VARCHAR(160), note VARCHAR(255), recorded_by VARCHAR(120),
  FOREIGN KEY (product_sku) REFERENCES products(sku)
);

INSERT INTO users (username, email, password_hash, full_name, role) VALUES
('admin', 'admin@primebuild.local', SHA2('admin123', 256), 'Marco Dela Rosa', 'admin'),
('staff', 'staff@primebuild.local', SHA2('staff123', 256), 'Angelica Reyes', 'staff'),
('maria.santos', 'maria.santos@primebuild.local', SHA2('staff123', 256), 'Maria Santos', 'staff'),
('juan.reyes', 'juan.reyes@primebuild.local', SHA2('staff123', 256), 'Juan Reyes', 'staff'),
('anna.mendoza', 'anna.mendoza@primebuild.local', SHA2('admin123', 256), 'Anna Mendoza', 'admin');

INSERT INTO categories (name, prefix) VALUES
('Cement & Aggregates','CEM'),('Steel & Rebar','STL'),('Lumber & Plywood','LMB'),('Plumbing','PLB'),
('Electrical','ELC'),('Paint & Finishes','PNT'),('Fasteners & Hardware','FST');

INSERT INTO suppliers (name, contact, phone, status) VALUES
('Pacific Cement Corp.','Ramon Villanueva','0917 555 0142','Active'),
('Luzon Steel Works','Angela Tan','0928 555 0187','Active'),
('Calabarzon Lumber Co.','Joel Mendoza','0905 555 0119','On hold'),
('Metro Plumbing Supply','Roberto Flores','0917 555 0201','Active');

INSERT INTO products (sku, name, category, supplier_name, price, stock, reorder_level) VALUES
('CEM-0001','Portland Cement 40kg','Cement & Aggregates','Pacific Cement Corp.',265.00,12,20),
('STL-0001','Deformed Bar 10mm x 6m','Steel & Rebar','Luzon Steel Works',198.00,340,50),
('LMB-0001','Marine Plywood 1/2"','Lumber & Plywood','Calabarzon Lumber Co.',1150.00,0,15),
('PLB-0001','PVC Pipe 2" x 3m','Plumbing','Metro Plumbing Supply',142.00,8,20),
('FST-0001','Common Wire Nails 3"','Fasteners & Hardware','Luzon Steel Works',78.00,96,20),
('PNT-0001','Latex Paint, Flat White 4L','Paint & Finishes','Calabarzon Lumber Co.',640.00,58,15);

INSERT INTO purchase_orders (po_number, supplier_name, product_sku, quantity, ordered_at, expected_at, amount, status) VALUES
('PO-2038','Calabarzon Lumber Co.','PNT-0001',48,'2026-10-04','2026-10-08',30720.00,'Received'),
('PO-2039','Calabarzon Lumber Co.','LMB-0001',40,'2026-09-28','2026-10-14',46000.00,'In transit'),
('PO-2040','Luzon Steel Works','STL-0001',200,'2026-10-03','2026-10-09',39600.00,'Received'),
('PO-2041','Pacific Cement Corp.','CEM-0001',200,'2026-10-06','2026-10-12',53000.00,'In transit'),
('PO-2042','Metro Plumbing Supply','PLB-0001',100,'2026-10-12','2026-10-19',14200.00,'In transit');

INSERT INTO sales (receipt_no, sold_at, customer, payment_method, status, total, amount_received, change_amount, payment_reference, cashier) VALUES
('#S-10479','2026-10-09 09:30:00','','E-Wallet','Paid',390.00,390.00,0,'GC-55210','Angelica Reyes'),
('#S-10480','2026-10-09 13:25:00','Santos Hardware','Bank Transfer','Paid',21930.00,21930.00,0,'BDO-883201','Angelica Reyes'),
('#S-10481','2026-10-10 08:10:00','','Cash','Paid',1420.00,1500.00,80.00,'','Angelica Reyes'),
('#S-10482','2026-10-10 08:40:00','Dela Cruz Builders','Bank Transfer','Paid',15870.00,15870.00,0,'BPI-120944','Marco Dela Rosa'),
('#S-10483','2026-10-14 10:26:00','','Cash','Paid',142.00,200.00,58.00,'','Marco Dela Rosa');

INSERT INTO sale_items (sale_receipt, product_sku, product_name, quantity, unit_price) VALUES
('#S-10479','FST-0001','Common Wire Nails 3"',5,78.00),
('#S-10480','PLB-0001','PVC Pipe 2" x 3m',15,142.00),
('#S-10480','STL-0001','Deformed Bar 10mm x 6m',100,198.00),
('#S-10481','FST-0001','Common Wire Nails 3"',10,78.00),
('#S-10481','PNT-0001','Latex Paint, Flat White 4L',1,640.00),
('#S-10482','CEM-0001','Portland Cement 40kg',30,265.00),
('#S-10482','STL-0001','Deformed Bar 10mm x 6m',40,198.00),
('#S-10483','PLB-0001','PVC Pipe 2" x 3m',1,142.00);

INSERT INTO stock_movements (movement_no, moved_at, product_sku, product_name, type, quantity, reference, note, recorded_by) VALUES
('MOV-00001','2026-10-08 10:05:00','PNT-0001','Latex Paint, Flat White 4L','in',48,'PO-2038','','Marco Dela Rosa'),
('MOV-00002','2026-10-09 09:30:00','FST-0001','Common Wire Nails 3"','out',-5,'#S-10479','','Angelica Reyes'),
('MOV-00003','2026-10-09 13:25:00','PLB-0001','PVC Pipe 2" x 3m','out',-15,'#S-10480','','Angelica Reyes'),
('MOV-00004','2026-10-09 13:25:00','STL-0001','Deformed Bar 10mm x 6m','out',-100,'#S-10480','','Angelica Reyes'),
('MOV-00005','2026-10-09 16:02:00','FST-0001','Common Wire Nails 3"','adj',-2,'MOV-00005','Count correction','Marco Dela Rosa'),
('MOV-00006','2026-10-10 08:10:00','FST-0001','Common Wire Nails 3"','out',-10,'#S-10481','','Angelica Reyes'),
('MOV-00007','2026-10-10 08:10:00','PNT-0001','Latex Paint, Flat White 4L','out',-1,'#S-10481','','Angelica Reyes'),
('MOV-00008','2026-10-10 08:40:00','CEM-0001','Portland Cement 40kg','out',-30,'#S-10482','','Marco Dela Rosa'),
('MOV-00009','2026-10-10 08:40:00','STL-0001','Deformed Bar 10mm x 6m','out',-40,'#S-10482','','Marco Dela Rosa'),
('MOV-00010','2026-10-10 09:14:00','STL-0001','Deformed Bar 10mm x 6m','in',200,'PO-2040','','Marco Dela Rosa'),
('MOV-00011','2026-10-14 10:26:00','PLB-0001','PVC Pipe 2" x 3m','out',-1,'#S-10483','','Marco Dela Rosa');