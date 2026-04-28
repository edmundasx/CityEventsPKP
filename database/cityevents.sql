DROP DATABASE IF EXISTS cityevents;
CREATE DATABASE cityevents CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cityevents;

-- Naudotojai
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    -- Slaptažodžiai turi būti maišomi (password_hash) programinėje įrangoje
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'organizer', 'admin') NOT NULL DEFAULT 'user',
    phone VARCHAR(50) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Renginiai
CREATE TABLE events (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organizer_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    category VARCHAR(255) NOT NULL,
    location VARCHAR(255) NOT NULL,
    lat DECIMAL(10,6) NULL,
    lng DECIMAL(10,6) NULL,
    event_date DATETIME NOT NULL,
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    status ENUM('pending', 'approved', 'rejected', 'update_pending') NOT NULL DEFAULT 'pending',
    rejection_reason TEXT NULL,
    cover_image TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_events_organizer FOREIGN KEY (organizer_id) REFERENCES users(id) ON DELETE CASCADE
);
CREATE INDEX idx_events_organizer ON events (organizer_id);
CREATE INDEX idx_events_status ON events (status);
CREATE INDEX idx_events_event_date ON events (event_date);

-- Mėgstamiausi renginiai
CREATE TABLE favorites (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    event_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    tag VARCHAR(50) NOT NULL DEFAULT 'favorite',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_favorites_event FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    CONSTRAINT fk_favorites_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
CREATE INDEX idx_favorites_user ON favorites (user_id);
CREATE INDEX idx_favorites_event ON favorites (event_id);

-- Pranešimų nustatymai
CREATE TABLE notification_settings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    event_id BIGINT UNSIGNED NOT NULL,
    time_offset VARCHAR(10) NOT NULL, -- pvz. '30m', '1h', '1d'
    channels JSON NOT NULL, -- pvz. '["sms", "email"]'
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notification_settings_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_notification_settings_event FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    UNIQUE KEY uq_user_event (user_id, event_id)
);

-- Naudotojų pranešimai
CREATE TABLE notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    message TEXT NOT NULL,
    type ENUM('user', 'organizer', 'admin') NOT NULL DEFAULT 'user',
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
CREATE INDEX idx_notifications_user ON notifications (user_id);

-- Organizatoriaus veiklos žurnalas
CREATE TABLE organizer_activity (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organizer_id BIGINT UNSIGNED NOT NULL,
    event_id BIGINT UNSIGNED NULL,
    event_title VARCHAR(200) NULL,
    type ENUM('like', 'approved', 'declined', 'submitted') NOT NULL,
    actor_name VARCHAR(150) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_organizer_activity_organizer FOREIGN KEY (organizer_id) REFERENCES users(id) ON DELETE CASCADE
);
CREATE INDEX idx_organizer_activity_organizer ON organizer_activity (organizer_id);

-- Užblokuoti naudotojai
CREATE TABLE blocked_users (
    user_id BIGINT UNSIGNED PRIMARY KEY,
    blocked_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_blocked_users_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

INSERT INTO users (id, name, email, password, role) VALUES 
(1, 'Raganiukės teatras', 'info@raganiuke.lt', 'hashed_password_123', 'organizer'),
(2, 'Muzikos Magija', 'klubas@muzikosmagija.lt', 'hashed_password_456', 'organizer'),
(3, 'Kultūros gidas', 'renginiai@vilnius.lt', 'hashed_password_789', 'organizer');

INSERT INTO events (
    id, 
    organizer_id, 
    title, 
    description, 
    category, 
    location, 
    lat, 
    lng, 
    event_date, 
    price, 
    status, 
    cover_image
) VALUES 
(472, 1, 'Matilda', 'Spektaklis vaikams pagal R. Dahl knygą.', 'Spektakliai ir pasirodymai, Šeimoms', 'Raganiukės teatras, Vilnius', 54.7078, 25.2631, '2026-04-25 12:00:00', 0.00, 'approved', 'https://www.vilnius-events.lt/wp-content/uploads/2025/11/matilda-vertikaliai.jpg'),
(473, 3, 'Šeimų šeštadienis. Pasakos iš puodo', 'Edukacinė veikla šeimoms Muziejuje.', 'Spektakliai ir pasirodymai, Šeimoms, Ekskursijos ir edukacijos', 'Lietuvos teatro, muzikos ir kino muziejus', 54.6802, 25.2818, '2026-04-25 12:00:00', 0.00, 'approved', 'https://www.vilnius-events.lt/wp-content/uploads/2026/01/Pasakos-is-puodo_events.png'),
(474, 2, 'Violončelė ir Vargonai | J.S.Bach', 'Klasikinės muzikos vakaras Organum salėje.', 'Festivaliai ir koncertai', 'Savanorių pr. 1, Vilnius', 54.6780, 25.2750, '2026-04-25 18:00:00', 15.00, 'approved', 'https://www.vilnius-events.lt/wp-content/uploads/2025/11/251220-Gleb-Dainius-Bach-Ekranas-6.jpg'),
(475, 3, 'Stebinantys Puškarnios kiemeliai', 'Ekskursija po senamiesčio kiemus.', 'Po atviru dangumi, Ekskursijos ir edukacijos', 'Katedros aikštė, Vilnius', 54.6853, 25.2875, '2026-04-28 17:50:00', 0.00, 'approved', 'https://www.vilnius-events.lt/wp-content/uploads/2023/01/alyvos-2-scaled-1.jpg'),
(476, 2, 'Plini (AU), Sungazer (US)', 'Progresyvaus roko ir džiazo koncertas.', 'Naktinė veikla, Festivaliai ir koncertai', 'Švitrigailos g. 29, Vilnius', 54.6732, 25.2699, '2026-04-29 19:00:00', 30.00, 'approved', 'https://www.vilnius-events.lt/wp-content/uploads/2025/10/552909350_1085728067108453_8176340787696394877_n.jpg'),
(477, 2, 'Arfos virtuozas XAVIER DE MAISTRE', 'LVSO simfoninio orkestro sezono koncertas.', 'Naktinė veikla, Festivaliai ir koncertai', 'Vilniaus kongresų rūmai', 54.6908, 25.2783, '2026-04-30 19:00:00', 25.00, 'approved', 'https://www.vilnius-events.lt/wp-content/uploads/2026/01/597181806_1390250912803840_4430548901341587637_n-1.jpg'),
(478, 3, 'Tarptautinė darbo diena', 'Visuotiniai gegužės 1-osios renginiai.', 'Kiti', 'Įvairios vietos, Vilnius', 54.6872, 25.2797, '2026-05-01 00:00:00', 0.00, 'approved', 'https://www.vilnius-events.lt/wp-content/uploads/2025/03/vilnius-events.lt-vizualu-formatas-2.png'),
(479, 2, 'VELNIO NUOTAKA | ARENA ŠOU', 'Miuziklas Twinsbet arenoje.', 'Naktinė veikla, Festivaliai ir koncertai', 'Ozo g. 14, Vilnius', 54.7153, 25.2796, '2026-05-01 19:00:00', 40.00, 'approved', 'https://www.vilnius-events.lt/wp-content/uploads/2026/02/620160764_1348728597286415_1591544705342768877_n.jpg'),
(481, 1, 'Močiutė plėšikė', 'Mamos dienos proga skirtas spektaklis.', 'Spektakliai ir pasirodymai, Šeimoms', 'Raganiukės teatras, Vilnius', 54.7078, 25.2631, '2026-05-03 12:00:00', 10.00, 'approved', 'https://www.vilnius-events.lt/wp-content/uploads/2025/11/Mociute-plesike-apk.jpg');
