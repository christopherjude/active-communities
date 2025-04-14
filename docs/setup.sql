-- Create and use the database
CREATE DATABASE IF NOT EXISTS activecommunities;
USE activecommunities;

-- Users table (base table for all roles)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL
);

-- Registered Users table
CREATE TABLE registered_users (
    user_id INT PRIMARY KEY,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Instructors table
CREATE TABLE instructors (
    user_id INT PRIMARY KEY,
    profile_bio TEXT,
    experience TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Leaders table
CREATE TABLE leaders (
    user_id INT PRIMARY KEY,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Training sessions
CREATE TABLE training_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    date DATE NOT NULL,
    time TIME NOT NULL,
    route VARCHAR(255),
    grade VARCHAR(50),
    created_by INT,
    FOREIGN KEY (created_by) REFERENCES leaders(user_id) ON DELETE SET NULL
);

-- Bookings (many-to-many: registered user <-> session)
CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_id INT NOT NULL,
    status VARCHAR(50) DEFAULT 'pending',
    FOREIGN KEY (user_id) REFERENCES registered_users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (session_id) REFERENCES training_sessions(id) ON DELETE CASCADE
);

-- Reviews (many-to-many: registered user <-> instructor)
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reviewer_id INT NOT NULL,
    instructor_id INT NOT NULL,
    rating INT CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    FOREIGN KEY (reviewer_id) REFERENCES registered_users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (instructor_id) REFERENCES instructors(user_id) ON DELETE CASCADE
);

-- Instructor Applications
CREATE TABLE instructor_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    status VARCHAR(50) DEFAULT 'pending',
    notes TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Contact Forms
CREATE TABLE contact_forms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    status VARCHAR(50) DEFAULT 'unread',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
