CREATE DATABASE IF NOT EXISTS skillswap
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE skillswap;

CREATE TABLE `USER` (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    F_name VARCHAR(100) NOT NULL,
    m_name VARCHAR(100) NULL,
    L_name VARCHAR(100) NOT NULL,
    phone VARCHAR(30) NOT NULL,
    location VARCHAR(150) NULL,
    bio TEXT NULL,
    join_date DATE NOT NULL,
    role VARCHAR(50) NOT NULL,
    profile_pic VARCHAR(255) NULL,
    password VARCHAR(255) NOT NULL,
    status VARCHAR(50) NOT NULL,
    S_time TIME NULL,
    E_time TIME NULL,
    time_zone VARCHAR(100) NULL
) ENGINE=InnoDB;

CREATE TABLE `SKILL` (
    skill_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    description TEXT NULL,
    level VARCHAR(50) NULL,
    status VARCHAR(50) NOT NULL,
    category_name VARCHAR(100) NULL,

    CONSTRAINT fk_skill_user
        FOREIGN KEY (user_id)
        REFERENCES `USER`(user_id)
) ENGINE=InnoDB;

CREATE TABLE `SESSION_BOOKING` (
    booking_id INT AUTO_INCREMENT PRIMARY KEY,
    bookedByUser INT NOT NULL,
    skill_id INT NOT NULL,
    meeting_link VARCHAR(255) NULL,
    payment_status VARCHAR(50) NULL,
    session_status VARCHAR(50) NOT NULL,
    session_slot DATETIME NOT NULL,

    CONSTRAINT fk_booking_user
        FOREIGN KEY (bookedByUser)
        REFERENCES `USER`(user_id),

    CONSTRAINT fk_booking_skill
        FOREIGN KEY (skill_id)
        REFERENCES `SKILL`(skill_id)
) ENGINE=InnoDB;

CREATE TABLE `REVIEW` (
    booking_id INT PRIMARY KEY,
    user_id INT NOT NULL,
    rating INT NOT NULL,
    comment TEXT NULL,
    review_date DATETIME NOT NULL,

    CONSTRAINT fk_review_booking
        FOREIGN KEY (booking_id)
        REFERENCES `SESSION_BOOKING`(booking_id),

    CONSTRAINT fk_review_user
        FOREIGN KEY (user_id)
        REFERENCES `USER`(user_id)
) ENGINE=InnoDB;

CREATE TABLE `BADGE` (
    badge_id INT AUTO_INCREMENT PRIMARY KEY,
    badge_name VARCHAR(120) NOT NULL,
    description TEXT NULL,
    badge_icon VARCHAR(255) NULL,
    criteria TEXT NULL
) ENGINE=InnoDB;

CREATE TABLE `USER_BADGE` (
    user_id INT NOT NULL,
    badge_id INT NOT NULL,
    earned_date DATETIME NOT NULL,

    PRIMARY KEY (user_id, badge_id),

    CONSTRAINT fk_user_badge_user
        FOREIGN KEY (user_id)
        REFERENCES `USER`(user_id),

    CONSTRAINT fk_user_badge_badge
        FOREIGN KEY (badge_id)
        REFERENCES `BADGE`(badge_id)
) ENGINE=InnoDB;
