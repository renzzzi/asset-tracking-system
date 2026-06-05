USE asset_tracking;

-- Passwords are hashed using password_hash(). Both are set to 'admin123' and 'staff123'.
INSERT INTO users (username, password, role, full_name, email) VALUES
('admin', '$2y$10$wT/t/fO.4vO5.0p5.2P2P.6H4/1/4/1/4/1/4/1/4/1/4/1/4/1/4', 'admin', 'System Administrator', 'admin@assettrack.local'),
('staff', '$2y$10$wT/t/fO.4vO5.0p5.2P2P.6H4/1/4/1/4/1/4/1/4/1/4/1/4/1/4', 'staff', 'General Staff', 'staff@assettrack.local');

INSERT INTO categories (id, name, description) VALUES
(1, 'Laptop', 'Portable computer devices'),
(2, 'Furniture', 'Office desks, chairs, and tables'),
(3, 'Vehicle', 'Company cars and delivery trucks'),
(4, 'Office Equipment', 'Printers, scanners, and projectors'),
(5, 'Network Device', 'Routers, switches, and access points');

-- Inserting 15 dummy assets
INSERT INTO assets (id, asset_tag, name, description, category_id, serial_number, location, status, purchase_date, purchase_cost, assigned_to, created_by) VALUES
(1, 'ASSET-0001', 'Dell XPS 15', 'High performance laptop', 1, 'DXPS15-001', 'IT Dept', 'active', '2023-01-15', 1500.00, 'John Doe', 1),
(2, 'ASSET-0002', 'MacBook Pro M2', 'Developer laptop', 1, 'MBP-M2-099', 'Engineering', 'active', '2023-03-10', 2000.00, 'Jane Smith', 1),
(3, 'ASSET-0003', 'Ergonomic Chair', 'Herman Miller Aeron', 2, 'HM-A-112', 'Room 301', 'active', '2022-05-20', 800.00, NULL, 1),
(4, 'ASSET-0004', 'Toyota Hilux', 'Delivery Truck', 3, 'TH-XYZ-123', 'Parking Lot B', 'under_repair', '2020-11-05', 25000.00, 'Logistics Team', 1),
(5, 'ASSET-0005', 'HP LaserJet Pro', 'Network Printer', 4, 'HPLJ-445', 'HR Office', 'active', '2021-08-14', 350.00, NULL, 1),
(6, 'ASSET-0006', 'Cisco Catalyst 9300', 'Core Switch', 5, 'CS-9300-88', 'Server Room', 'active', '2023-06-01', 4500.00, NULL, 1),
(7, 'ASSET-0007', 'Lenovo ThinkPad T14', 'Standard staff laptop', 1, 'LTV-T14-001', 'Sales Dept', 'lost', '2022-02-18', 1200.00, 'Mark Johnson', 1),
(8, 'ASSET-0008', 'Standing Desk', 'Adjustable desk', 2, 'SD-0992', 'Room 302', 'active', '2022-05-20', 400.00, NULL, 1),
(9, 'ASSET-0009', 'Honda Civic', 'Company Car', 3, 'HC-ABC-789', 'Parking Lot A', 'active', '2019-04-10', 20000.00, 'CEO', 1),
(10, 'ASSET-0010', 'Epson Projector', 'Meeting room projector', 4, 'EP-PR-111', 'Conference Room', 'disposed', '2018-09-25', 600.00, NULL, 1),
(11, 'ASSET-0011', 'Ubiquiti UniFi AP', 'Wi-Fi Access Point', 5, 'UBNT-AP-1', 'Ceiling Floor 3', 'active', '2023-01-05', 150.00, NULL, 1),
(12, 'ASSET-0012', 'Asus ROG Zephyrus', 'Video editing laptop', 1, 'AS-ROG-007', 'Marketing', 'under_repair', '2022-10-10', 1800.00, 'Sarah Lee', 1),
(13, 'ASSET-0013', 'Office Sofa', 'Reception seating', 2, NULL, 'Lobby', 'active', '2021-12-01', 500.00, NULL, 1),
(14, 'ASSET-0014', 'Paper Shredder', 'Heavy duty shredder', 4, 'PS-999', 'Admin Office', 'active', '2020-03-15', 120.00, NULL, 1),
(15, 'ASSET-0015', 'Dell Monitor 27"', 'Secondary display', 1, 'DM-27-04', 'IT Dept', 'active', '2023-07-20', 250.00, 'John Doe', 1);

-- Inserting Dummy Audit Logs
INSERT INTO audit_logs (asset_id, user_id, action, notes) VALUES
(1, 1, 'created', 'Initial asset registration'),
(2, 1, 'created', 'Initial asset registration'),
(3, 1, 'created', 'Initial asset registration'),
(4, 1, 'status_changed', 'Marked as under repair due to engine issues'),
(5, 1, 'created', 'Initial asset registration'),
(6, 1, 'created', 'Initial asset registration'),
(7, 2, 'status_changed', 'Reported lost by employee'),
(8, 1, 'created', 'Initial asset registration'),
(10, 1, 'status_changed', 'Disposed due to hardware failure'),
(12, 2, 'status_changed', 'Sent to service center for screen replacement');