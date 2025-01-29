USE notification_system;

CREATE DATABASE IF NOT EXISTS notification_system;
USE notification_system;

CREATE TABLE IF NOT EXISTS users
(
    id         INT AUTO_INCREMENT PRIMARY KEY,
    email      VARCHAR(255) NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS templates
(
    id         INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100)                  NOT NULL,
    type       ENUM ('email','sms','in-app') NOT NULL,
    subject    VARCHAR(255),
    body       TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS notifications
(
    id            INT AUTO_INCREMENT PRIMARY KEY,
    user_id       INT NOT NULL,
    template_id   INT NOT NULL,
    status        ENUM ('queued','sent','failed','opened') DEFAULT 'queued',
    scheduled_for DATETIME,
    sent_at       DATETIME,
    created_at    DATETIME                                 DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users (id),
    FOREIGN KEY (template_id) REFERENCES templates (id)
);

CREATE TABLE IF NOT EXISTS analytics
(
    id              INT AUTO_INCREMENT PRIMARY KEY,
    notification_id INT         NOT NULL,
    event_type      VARCHAR(50) NOT NULL,
    event_time      DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES notifications (id)
);

CREATE TABLE IF NOT EXISTS user_preferences
(
    id                INT AUTO_INCREMENT PRIMARY KEY,
    user_id           INT                           NOT NULL,
    notification_type ENUM ('email','sms','in-app') NOT NULL,
    subscribed        TINYINT(1) DEFAULT 1,
    FOREIGN KEY (user_id) REFERENCES users (id)
);
