-- Create Database
CREATE DATABASE IF NOT EXISTS uytsa_db;
USE uytsa_db;

-- Users Table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('member', 'Patron', 'Chairperson', 'Vice_Chairperson', 'Secretary_General', 'Treasurer', 'Organizing_Secretary', 'Publicity_Officer', 'NextGen_Docket') DEFAULT 'member',
    phone VARCHAR(20),
    institution VARCHAR(100),
    course VARCHAR(100),
    year_of_study VARCHAR(20),
    graduation_year INT,
    profile_image VARCHAR(255),
    bio TEXT,
    status ENUM('active', 'inactive', 'alumni') DEFAULT 'active',
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    reset_token VARCHAR(255),
    reset_expiry TIMESTAMP NULL
);

-- Announcements Table
CREATE TABLE announcements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    category ENUM('general', 'academic', 'event', 'financial', 'opportunity') DEFAULT 'general',
    author_id INT,
    is_important BOOLEAN DEFAULT FALSE,
    attachment VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Opportunities Table
CREATE TABLE opportunities (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    type ENUM('internship', 'scholarship', 'job', 'volunteer', 'training', 'competition') NOT NULL,
    organization VARCHAR(255),
    deadline DATE,
    requirements TEXT,
    link VARCHAR(255),
    posted_by INT,
    contact_email VARCHAR(100),
    is_verified BOOLEAN DEFAULT FALSE,
    views INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (posted_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Gallery Table
CREATE TABLE gallery (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    image_path VARCHAR(255) NOT NULL,
    category ENUM('event', 'expedition', 'volunteering', 'meeting', 'other') DEFAULT 'event',
    uploaded_by INT,
    event_date DATE,
    location VARCHAR(100),
    likes INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Finances Table
CREATE TABLE finances (
    id INT PRIMARY KEY AUTO_INCREMENT,
    transaction_type ENUM('contribution', 'donation', 'expense', 'project_fund', 'event_fund') NOT NULL,
    member_id INT,
    amount DECIMAL(10,2) NOT NULL,
    purpose VARCHAR(255) NOT NULL,
    description TEXT,
    receipt_number VARCHAR(50) UNIQUE,
    payment_method ENUM('cash', 'mobile_money', 'bank_transfer', 'other') DEFAULT 'cash',
    status ENUM('pending', 'completed', 'verified', 'cancelled') DEFAULT 'pending',
    recorded_by INT,
    transaction_date DATE,
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (member_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Events Table
CREATE TABLE events (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    event_type ENUM('meeting', 'workshop', 'seminar', 'social', 'volunteer', 'other') DEFAULT 'meeting',
    start_date DATETIME NOT NULL,
    end_date DATETIME,
    location VARCHAR(255),
    organizer_id INT,
    max_participants INT,
    registration_deadline DATE,
    status ENUM('upcoming', 'ongoing', 'completed', 'cancelled') DEFAULT 'upcoming',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (organizer_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Event Registrations
CREATE TABLE event_registrations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    event_id INT,
    user_id INT,
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('registered', 'attended', 'cancelled') DEFAULT 'registered',
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_registration (event_id, user_id)
);

-- Study Materials Table
CREATE TABLE study_materials (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    file_path VARCHAR(255),
    category VARCHAR(100),
    uploaded_by INT,
    downloads INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Notifications Table
CREATE TABLE notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'success', 'warning', 'error') DEFAULT 'info',
    is_read BOOLEAN DEFAULT FALSE,
    link VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert Default Admin User (password: admin123)
INSERT INTO users (username, email, password, full_name, role, phone, institution) 
VALUES ('admin', 'admin@uytsa.com', 'admin123', 'System Administrator', 'Chairperson', '0712345678', 'UYTSA Headquarters');

-- Insert sample executive members
INSERT INTO users (username, email, password, full_name, role, phone) VALUES
('treasurer', 'treasurer@uytsa.com', 'treasurer123', 'John Treasurer', 'Treasurer', '0723456789'),
('secretary', 'secretary@uytsa.com', 'secretary123', 'Mary Secretary', 'Secretary_General', '0734567890');

-- Insert sample regular members
INSERT INTO users (username, email, password, full_name, role, phone, institution, course, year_of_study, status) VALUES
('john_doe', 'john@example.com', 'member123', 'John Doe', 'member', '0745678901', 'University of Nairobi', 'Computer Science', '3rd Year', 'active'),
('jane_smith', 'jane@example.com', 'member123', 'Jane Smith', 'member', '0756789012', 'Kenyatta University', 'Business Administration', '4th Year', 'active'),
('peter_w', 'peter@example.com', 'member123', 'Peter Wilson', 'member', '0767890123', 'Moi University', 'Engineering', '2nd Year', 'active'),
('sarah_m', 'sarah@example.com', 'member123', 'Sarah Mwangi', 'member', '0778901234', 'JKUAT', 'Medicine', '5th Year+', 'active'),
('alumni1', 'alumni1@example.com', 'member123', 'David Alumni', 'member', '0789012345', 'Strathmore University', 'Law', 'Alumni', 'alumni');

-- Insert sample announcements
INSERT INTO announcements (title, content, category, author_id, is_important) VALUES
('Welcome to UYTSA Platform', 'Welcome all members to our new community platform! This system will help us stay connected and share opportunities.', 'general', 1, 1),
('Monthly Meeting - January 2024', 'Our monthly general meeting will be held on 15th January 2024 at Ulumbi Community Hall. All members are expected.', 'event', 1, 1),
('Scholarship Opportunities Available', 'Several scholarship opportunities are now open for application. Check the opportunities section for details.', 'academic', 1, 0),
('Membership Contributions', 'Annual membership contributions of Ksh 1000 are due by end of January. Please make payments to the treasurer.', 'financial', 2, 1),
('Community Service Day', 'Join us for community service at Ulumbi Primary School on 20th January 2024.', 'event', 3, 0);

-- Insert sample opportunities
INSERT INTO opportunities (title, description, type, organization, deadline, posted_by, is_verified) VALUES
('Internship at Tech Company', '3-month internship program for IT students at leading tech company.', 'internship', 'Safaricom PLC', '2024-02-15', 4, 1),
('Scholarship for Tertiary Students', 'Full scholarship for students from Ulumbi community.', 'scholarship', 'Equity Bank Foundation', '2024-03-31', 1, 1),
('Part-time Marketing Job', 'Part-time marketing assistant needed for local business.', 'job', 'Ulumbi Enterprises', '2024-01-31', 5, 1),
('Volunteer Teachers Needed', 'Volunteer to teach at Ulumbi Primary School computer classes.', 'volunteer', 'Ulumbi Primary School', '2024-02-28', 1, 1),
('Web Development Training', 'Free web development training for youth.', 'training', 'UYTSA Training Center', '2024-01-20', 2, 1);

-- Insert sample gallery images
INSERT INTO gallery (title, description, image_path, category, uploaded_by, event_date, location) VALUES
('Annual General Meeting 2023', 'Our successful AGM held last year', 'agm2023.jpg', 'meeting', 1, '2023-12-15', 'Ulumbi Community Hall'),
('Community Clean-up', 'Volunteers cleaning the community', 'cleanup.jpg', 'volunteering', 4, '2023-11-10', 'Ulumbi Market'),
-- Expedition entry removed
('Sports Day', 'Annual sports competition', 'sports_day.jpg', 'event', 2, '2023-09-20', 'Ulumbi Grounds'),
('Mentorship Session', 'Career guidance for students', 'mentorship.jpg', 'event', 3, '2023-08-15', 'Ulumbi Secondary School');

-- Insert sample financial transactions
INSERT INTO finances (transaction_type, member_id, amount, purpose, payment_method, status, recorded_by, transaction_date) VALUES
('contribution', 4, 1000.00, 'Annual Membership', 'mobile_money', 'completed', 2, '2024-01-05'),
('contribution', 5, 1000.00, 'Annual Membership', 'cash', 'completed', 2, '2024-01-06'),
('contribution', 6, 1000.00, 'Annual Membership', 'mobile_money', 'pending', 2, '2024-01-07'),
('expense', NULL, 5000.00, 'Meeting Hall Rental', 'cash', 'completed', 2, '2024-01-08'),
('donation', NULL, 20000.00, 'Donation from Well-wisher', 'bank_transfer', 'verified', 2, '2024-01-10');

-- Insert sample events
INSERT INTO events (title, description, event_type, start_date, end_date, location, organizer_id, max_participants, status) VALUES
('Career Guidance Workshop', 'Workshop on career choices and opportunities', 'workshop', '2024-02-10 09:00:00', '2024-02-10 13:00:00', 'Ulumbi Community Hall', 3, 50, 'upcoming'),
('Monthly General Meeting', 'Regular monthly meeting', 'meeting', '2024-01-15 14:00:00', '2024-01-15 16:00:00', 'UYTSA Office', 1, 100, 'upcoming'),
('Community Health Day', 'Free medical checkups for community', 'volunteer', '2024-02-20 08:00:00', '2024-02-20 17:00:00', 'Ulumbi Dispensary', 2, 200, 'upcoming'),
('Study Group Session', 'Group study for exam preparation', 'seminar', '2024-01-25 10:00:00', '2024-01-25 16:00:00', 'Ulumbi Library', 4, 30, 'upcoming');

-- Insert sample study materials
INSERT INTO study_materials (title, description, category, uploaded_by) VALUES
('Mathematics Revision Notes', 'Comprehensive notes for KCSE mathematics', 'Mathematics', 1),
('Computer Programming Guide', 'Introduction to programming with Python', 'Computer Science', 4),
('Business Plan Template', 'Template for creating business plans', 'Business', 5),
('Research Methods Handbook', 'Guide to academic research methods', 'Academic Writing', 3);

-- Insert sample notifications
INSERT INTO notifications (user_id, title, message, type) VALUES
(4, 'Welcome to UYTSA', 'Welcome to the Ulumbi Youth & Tertiary Students Association platform!', 'info'),
(5, 'Payment Received', 'Your membership contribution has been received. Thank you!', 'success'),
(6, 'Payment Pending', 'Your membership contribution is pending verification.', 'warning');