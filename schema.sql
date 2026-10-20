-- =====================================================
-- CHARITY HUB database  (import this ONCE in phpMyAdmin)
-- Re-importing wipes and recreates everything.
-- =====================================================
DROP DATABASE IF EXISTS charity_hub;
CREATE DATABASE charity_hub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE charity_hub;

CREATE TABLE users (
  user_id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  phone VARCHAR(20) NOT NULL,
  role ENUM('ADMIN','ORGANIZATION','DONOR','VOLUNTEER','BENEFICIARY') NOT NULL,
  status ENUM('ACTIVE','SUSPENDED') NOT NULL DEFAULT 'ACTIVE',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE organizations (
  org_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  organization_name VARCHAR(200) NOT NULL,
  description TEXT NULL,
  location VARCHAR(150) NULL,
  verification_status ENUM('PENDING','VERIFIED','REJECTED') NOT NULL DEFAULT 'PENDING',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE projects (
  project_id INT AUTO_INCREMENT PRIMARY KEY,
  org_id INT NOT NULL,
  title VARCHAR(200) NOT NULL,
  description TEXT NOT NULL,
  category VARCHAR(60) NOT NULL DEFAULT 'General',
  target_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
  start_date DATE NULL,
  end_date DATE NULL,
  status ENUM('PLANNED','ONGOING','COMPLETED','ON_HOLD') NOT NULL DEFAULT 'ONGOING',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (org_id) REFERENCES organizations(org_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE donations (
  donation_id INT AUTO_INCREMENT PRIMARY KEY,
  donor_id INT NOT NULL,
  org_id INT NOT NULL,
  project_id INT NULL,
  amount DECIMAL(12,2) NOT NULL,
  payment_method ENUM('M-Pesa','Card','Bank Transfer','Cash') NOT NULL DEFAULT 'M-Pesa',
  reference VARCHAR(40) NOT NULL,
  message VARCHAR(255) NULL,
  anonymous TINYINT(1) NOT NULL DEFAULT 0,
  status ENUM('COMPLETED','PENDING') NOT NULL DEFAULT 'COMPLETED',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (donor_id) REFERENCES users(user_id) ON DELETE CASCADE,
  FOREIGN KEY (org_id) REFERENCES organizations(org_id) ON DELETE CASCADE,
  FOREIGN KEY (project_id) REFERENCES projects(project_id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE events (
  event_id INT AUTO_INCREMENT PRIMARY KEY,
  org_id INT NOT NULL,
  title VARCHAR(200) NOT NULL,
  description TEXT NOT NULL,
  location VARCHAR(200) NOT NULL,
  event_date DATE NOT NULL,
  start_time TIME NULL,
  volunteers_needed INT NOT NULL DEFAULT 10,
  status ENUM('OPEN','CLOSED','COMPLETED','CANCELLED') NOT NULL DEFAULT 'OPEN',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (org_id) REFERENCES organizations(org_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE volunteer_signups (
  signup_id INT AUTO_INCREMENT PRIMARY KEY,
  event_id INT NOT NULL,
  volunteer_id INT NOT NULL,
  status ENUM('PENDING','APPROVED','DECLINED') NOT NULL DEFAULT 'PENDING',
  hours DECIMAL(5,1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_signup (event_id, volunteer_id),
  FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE CASCADE,
  FOREIGN KEY (volunteer_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE assistance_requests (
  request_id INT AUTO_INCREMENT PRIMARY KEY,
  beneficiary_id INT NOT NULL,
  org_id INT NOT NULL,
  assistance_type VARCHAR(80) NOT NULL,
  description TEXT NOT NULL,
  status ENUM('SUBMITTED','APPROVED','REJECTED','FULFILLED') NOT NULL DEFAULT 'SUBMITTED',
  org_note VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (beneficiary_id) REFERENCES users(user_id) ON DELETE CASCADE,
  FOREIGN KEY (org_id) REFERENCES organizations(org_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE expenses (
  expense_id INT AUTO_INCREMENT PRIMARY KEY,
  org_id INT NOT NULL,
  project_id INT NULL,
  title VARCHAR(200) NOT NULL,
  category VARCHAR(60) NOT NULL,
  amount DECIMAL(12,2) NOT NULL,
  expense_date DATE NOT NULL,
  notes VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (org_id) REFERENCES organizations(org_id) ON DELETE CASCADE,
  FOREIGN KEY (project_id) REFERENCES projects(project_id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE announcements (
  announcement_id INT AUTO_INCREMENT PRIMARY KEY,
  author_id INT NOT NULL,
  title VARCHAR(200) NOT NULL,
  body TEXT NOT NULL,
  audience ENUM('ALL','DONOR','VOLUNTEER','BENEFICIARY','ORGANIZATION') NOT NULL DEFAULT 'ALL',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (author_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ================= DEMO DATA =================
-- All demo accounts use the password:  Password@123
INSERT INTO users (user_id,name,email,password,phone,role,status) VALUES
 (1,'System Admin','admin@charityhub.org','$2y$10$0/1.KalMAjQPoBRsEPJzv.oWlxKQG8Z5toNxLGEh5kQNCZWSQhXBe','0700000001','ADMIN','ACTIVE'),
 (2,'Hope Foundation','org@charityhub.org','$2y$10$0/1.KalMAjQPoBRsEPJzv.oWlxKQG8Z5toNxLGEh5kQNCZWSQhXBe','0700000002','ORGANIZATION','ACTIVE'),
 (3,'Wanjiru Kamau','donor@charityhub.org','$2y$10$0/1.KalMAjQPoBRsEPJzv.oWlxKQG8Z5toNxLGEh5kQNCZWSQhXBe','0700000003','DONOR','ACTIVE'),
 (4,'Brian Otieno','volunteer@charityhub.org','$2y$10$0/1.KalMAjQPoBRsEPJzv.oWlxKQG8Z5toNxLGEh5kQNCZWSQhXBe','0700000004','VOLUNTEER','ACTIVE'),
 (5,'Mary Achieng','beneficiary@charityhub.org','$2y$10$0/1.KalMAjQPoBRsEPJzv.oWlxKQG8Z5toNxLGEh5kQNCZWSQhXBe','0700000005','BENEFICIARY','ACTIVE'),
 (6,'Green Future Kenya','org2@charityhub.org','$2y$10$0/1.KalMAjQPoBRsEPJzv.oWlxKQG8Z5toNxLGEh5kQNCZWSQhXBe','0700000006','ORGANIZATION','ACTIVE'),
 (7,'Peter Mwangi','peter@charityhub.org','$2y$10$0/1.KalMAjQPoBRsEPJzv.oWlxKQG8Z5toNxLGEh5kQNCZWSQhXBe','0700000007','DONOR','ACTIVE');

INSERT INTO organizations (org_id,user_id,organization_name,description,location,verification_status) VALUES
 (1,2,'Hope Foundation','Education, health and food support for families in Nairobi informal settlements.','Nairobi','VERIFIED'),
 (2,6,'Green Future Kenya','Tree planting and clean-water projects across Kenya.','Nakuru','PENDING');

INSERT INTO projects (org_id,title,description,category,target_amount,start_date,end_date,status) VALUES
 (1,'School Fees for 100 Children','Cover a full year of school fees, uniforms and books for 100 children from low-income families in Mathare and Kibera.','Education',500000,'2026-01-10','2026-12-20','ONGOING'),
 (1,'Community Food Pantry','A weekly food pantry serving 300 households with maize flour, beans, rice and cooking oil.','Food Security',250000,'2026-02-01','2026-12-31','ONGOING'),
 (1,'Mobile Health Clinic','A monthly mobile clinic offering free check-ups, vaccinations and maternal care.','Health',400000,'2026-03-01','2027-03-01','ONGOING'),
 (1,'Clean Water Borehole','Drilling a borehole to give a village access to safe drinking water.','Water',650000,'2026-05-01','2026-11-30','PLANNED');

INSERT INTO donations (donor_id,org_id,project_id,amount,payment_method,reference,message,anonymous,status,created_at) VALUES
 (3,1,1,15000,'M-Pesa','CH-A1B2C3D4','Keep up the good work!',0,'COMPLETED',NOW() - INTERVAL 20 DAY),
 (3,1,2,5000,'M-Pesa','CH-B2C3D4E5',NULL,0,'COMPLETED',NOW() - INTERVAL 12 DAY),
 (7,1,1,25000,'Bank Transfer','CH-C3D4E5F6','For the children',0,'COMPLETED',NOW() - INTERVAL 9 DAY),
 (7,1,3,10000,'Card','CH-D4E5F6A7',NULL,1,'COMPLETED',NOW() - INTERVAL 4 DAY),
 (3,1,4,8000,'M-Pesa','CH-E5F6A7B8','Water is life',0,'COMPLETED',NOW() - INTERVAL 1 DAY);

INSERT INTO events (org_id,title,description,location,event_date,start_time,volunteers_needed,status) VALUES
 (1,'Food Pantry Distribution Day','Help pack and distribute food parcels to registered households.','Mathare, Nairobi',CURDATE() + INTERVAL 10 DAY,'08:00:00',15,'OPEN'),
 (1,'Back-to-School Drive','Sort uniforms and books and help children get fitted.','Kibera, Nairobi',CURDATE() + INTERVAL 21 DAY,'09:30:00',10,'OPEN'),
 (1,'Community Health Camp','Assist nurses with registration, queue management and crowd guidance.','Kawangware, Nairobi',CURDATE() + INTERVAL 35 DAY,'08:30:00',12,'OPEN');

INSERT INTO volunteer_signups (event_id,volunteer_id,status,hours) VALUES (1,4,'APPROVED',0);

INSERT INTO assistance_requests (beneficiary_id,org_id,assistance_type,description,status,org_note) VALUES
 (5,1,'Education Support','I need help with school fees and uniforms for my two children.','APPROVED','Approved. Please visit our office with school fee statements.');

INSERT INTO expenses (org_id,project_id,title,category,amount,expense_date,notes) VALUES
 (1,1,'School fees - Term 1','Education',120000,CURDATE() - INTERVAL 30 DAY,'Paid to 24 schools'),
 (1,2,'Maize flour and beans','Food',45000,CURDATE() - INTERVAL 15 DAY,'Bulk purchase'),
 (1,3,'Medical supplies','Health',32000,CURDATE() - INTERVAL 7 DAY,'Vaccines and consumables');

INSERT INTO announcements (author_id,title,body,audience) VALUES
 (1,'Welcome to Charity Hub','Thank you for joining our community. Together we make giving simple, transparent and impactful.','ALL'),
 (1,'Volunteers needed this month','Several verified organizations are looking for volunteers. Open Events in your menu to sign up.','VOLUNTEER');