-- Toyota Silang CRM Seed Data
-- Default configuration and optional demo data

SET NAMES utf8mb4;

-- Default Admin User (password: admin123)
INSERT INTO users (username, password_hash, full_name, role, active) VALUES
('admin', '$2y$10$kuA7590yhzVlN7u8fZ99t.JLc8P5eMlR9D7SNoOyFyVlvPushajMC', 'CRM Administrator', 'admin', 1);

-- Lead Statuses
INSERT INTO lead_statuses (name, color, sort_order, active) VALUES
('New Lead', '#0d6efd', 1, 1),
('Contacted', '#6f42c1', 2, 1),
('Talking', '#198754', 3, 1),
('Quote Sent', '#fd7e14', 4, 1),
('Follow-up', '#0dcaf0', 5, 1),
('Lost', '#dc3545', 6, 1);

-- Opportunity Stages
INSERT INTO opportunity_stages (name, color, sort_order, active) VALUES
('Met', '#6c757d', 1, 1),
('Test Drive', '#0d6efd', 2, 1),
('Financing Appli', '#198754', 3, 1),
('Approved', '#198754', 4, 1),
('Booked', '#0d6efd', 5, 1),
('Released', '#dc3545', 6, 1),
('Reserved', '#fd7e14', 7, 1),
('With PO', '#6f42c1', 8, 1),
('Downpayment', '#198754', 9, 1);

-- Priorities
INSERT INTO priorities (name, color, level, active) VALUES
('High', '#dc3545', 1, 1),
('Medium', '#fd7e14', 2, 1),
('Low', '#6c757d', 3, 1);

-- Lead Sources
INSERT INTO lead_sources (name, sort_order, active) VALUES
('Facebook', 1, 1),
('Walk-in', 2, 1),
('Referral', 3, 1),
('Phone', 4, 1),
('Event', 5, 1),
('Google Maps', 6, 1),
('LinkedIn', 7, 1),
('Repeat Buyer', 8, 1),
('Saturation', 9, 1);

-- Vehicle Models (Toyota Philippines)
INSERT INTO vehicle_models (name, sort_order, active) VALUES
('Vios', 1, 1),
('Corolla Altis', 2, 1),
('Camry', 3, 1),
('Yaris Ativ', 4, 1),
('Raize', 5, 1),
('Rush', 6, 1),
('Avanza', 7, 1),
('Innova', 8, 1),
('Fortuner', 9, 1),
('HiAce', 10, 1),
('Hilux', 11, 1),
('Land Cruiser', 12, 1),
('GR86', 13, 1),
('Corolla Cross', 14, 1),
('RAV4', 15, 1),
('4Runner', 16, 1),
('Prado', 17, 1);

-- Vehicle Colors
INSERT INTO vehicle_colors (name, sort_order, active) VALUES
('White', 1, 1),
('Black', 2, 1),
('Silver', 3, 1),
('Gray', 4, 1),
('Red', 5, 1),
('Blue', 6, 1),
('Pearl White', 7, 1),
('Metallic Gray', 8, 1),
('Champagne', 9, 1),
('Attitude Black', 10, 1),
('Coral Blue', 11, 1),
('Spicy Orange', 12, 1);

-- Working Days (Mon-Fri)
INSERT INTO working_days (day_of_week, is_working) VALUES
('Monday', 1),
('Tuesday', 1),
('Wednesday', 1),
('Thursday', 1),
('Friday', 1),
('Saturday', 0),
('Sunday', 0);

-- Settings
INSERT INTO settings (setting_key, setting_value) VALUES
('closed_release_stage', 'Released'),
('closing_ratio_method', 'deals / leads_this_month'),
('app_name', 'Toyota Silang CRM'),
('app_timezone', 'Asia/Manila');

-- Philippine Holidays 2026 (sample)
INSERT INTO holidays (holiday_date, name) VALUES
('2026-01-01', 'New Year'),
('2026-02-25', 'EDSA Revolution'),
('2026-04-02', 'Maundy Thursday'),
('2026-04-03', 'Good Friday'),
('2026-04-09', 'Araw ng Kagitingan'),
('2026-05-01', 'Labor Day'),
('2026-06-12', 'Independence Day'),
('2026-08-31', 'National Heroes Day'),
('2026-11-30', 'Bonifacio Day'),
('2026-12-25', 'Christmas Day'),
('2026-12-30', 'Rizal Day');

-- ============================================
-- OPTIONAL DEMO DATA
-- Remove this section if not needed
-- ============================================

-- Demo Sales Target
INSERT INTO sales_targets (year, month, target) VALUES
(2026, 8, 15),
(2026, 9, 15);

-- Demo Lead Generation Targets for Aug 2026
INSERT INTO lead_generation_targets (year, month, source_id, target) VALUES
(2026, 8, 1, 30),
(2026, 8, 2, 10),
(2026, 8, 3, 8),
(2026, 8, 4, 12),
(2026, 8, 5, 5),
(2026, 8, 6, 15),
(2026, 8, 7, 5),
(2026, 8, 8, 5),
(2026, 8, 9, 10);

-- Demo Leads
INSERT INTO leads (lead_id, lead_name, company, phone, email, status_id, opportunity_stage_id, priority_id, source_id, model_id, color_id, initial_contact_date, last_contact_date, next_step, next_step_date, location, notes, archived) VALUES
('C0001', 'Juan Dela Cruz', 'Dela Cruz Trading', '09171234567', 'juan@example.com', 3, 5, 1, 1, 9, 1, '2026-08-01', '2026-08-24', 'Follow-up on financing', '2026-08-28', 'Silang, Cavite', 'Interested in Fortuner. Ready for booking.', 0),
('C0002', 'Maria Santos', 'Santos Corp', '09181234567', 'maria@example.com', 4, 4, 1, 3, 8, 5, '2026-08-05', '2026-08-26', 'Send financing documents', '2026-08-27', 'Dasmarinas, Cavite', 'Needs unit for family use. Budget-conscious.', 0),
('C0003', 'Pedro Reyes', '', '09191234567', 'pedro@example.com', 2, NULL, 2, 4, 14, 3, '2026-08-10', '2026-08-20', 'Call back for test drive', '2026-08-29', 'Imus, Cavite', 'Considering Corolla Cross vs RAV4.', 0),
('C0004', 'Ana Garcia', '', '09201234567', 'ana@example.com', 1, NULL, 2, 2, 1, 1, '2026-08-25', NULL, 'Initial call', '2026-08-26', 'Tanza, Cavite', 'Walk-in inquiry for Vios.', 0),
('C0005', 'Roberto Mendoza', 'Mendoza Auto Parts', '09211234567', 'roberto@example.com', 5, NULL, 3, 7, 11, 4, '2026-07-15', '2026-08-18', 'Follow-up after meeting', '2026-08-20', 'Bacoor, Cavite', 'Was interested in Hilux but went quiet.', 0),
('C0006', 'Carmen Lim', 'Lim Enterprises', '09221234567', 'carmen@example.com', 3, 3, 1, 1, 9, 2, '2026-08-03', '2026-08-25', 'Submit financing application', '2026-08-28', 'General Trias, Cavite', 'Pre-approved by BPI. Very interested.', 0),
('C0007', 'Fernando Cruz', '', '09231234567', 'fernando@example.com', 6, NULL, 3, 5, NULL, NULL, '2026-06-20', '2026-07-01', '', NULL, 'Tagaytay', 'Lost to competitor pricing.', 0),
('C0008', 'Grace Villanueva', 'Villanueva Group', '09241234567', 'grace@example.com', 3, 2, 1, 3, 10, 1, '2026-08-08', '2026-08-22', 'Schedule test drive', '2026-08-26', 'Silang, Cavite', 'Fleet inquiry for 3 HiAce units.', 0),
('C0009', 'Luis Tan', '', '09251234567', 'luis@example.com', 2, NULL, 2, 6, 5, 6, '2026-08-15', '2026-08-21', 'Send brochure and pricing', '2026-08-27', 'Bacoor, Cavite', 'Found via Google Maps. Interested in Raize.', 0),
('C0010', 'Sofia Ramos', '', '09261234567', 'sofia@example.com', 1, NULL, 2, 8, 14, 7, '2026-08-26', NULL, 'Welcome call', '2026-08-27', 'Dasmariñas, Cavite', 'Repeat buyer. Previously bought Vios in 2023.', 0);

-- Demo Activities
INSERT INTO activities (lead_id, activity_type, activity_date, notes, next_step, next_step_date, created_by, created_at) VALUES
(1, 'Call', '2026-08-24 10:30:00', 'Discussed financing options. Client interested in bank financing.', 'Follow-up on financing', '2026-08-28', 1, '2026-08-24 10:35:00'),
(1, 'Quote', '2026-08-20 14:00:00', 'Sent quotation for Fortuner G Automatic.', 'Follow-up', '2026-08-24', 1, '2026-08-20 14:10:00'),
(2, 'Meeting', '2026-08-26 09:00:00', 'Client reviewed financing documents. Looks good.', 'Send financing documents', '2026-08-27', 1, '2026-08-26 11:00:00'),
(2, 'Test Drive', '2026-08-15 11:00:00', 'Test drove Innova. Very satisfied.', 'Follow-up with quote', '2026-08-18', 1, '2026-08-15 12:00:00'),
(3, 'Message', '2026-08-20 16:00:00', 'Sent comparison brochure for Corolla Cross vs RAV4.', 'Call back for test drive', '2026-08-29', 1, '2026-08-20 16:05:00'),
(6, 'Financing', '2026-08-25 13:00:00', 'BPI pre-approval received. Processing documents.', 'Submit financing application', '2026-08-28', 1, '2026-08-25 14:00:00'),
(8, 'Call', '2026-08-22 10:00:00', 'Discussed fleet pricing for 3 HiAce units.', 'Schedule test drive', '2026-08-26', 1, '2026-08-22 10:15:00'),
(8, 'Meeting', '2026-08-18 14:00:00', 'Met with fleet manager. Very interested.', 'Follow-up call', '2026-08-22', 1, '2026-08-18 15:00:00');
