-- ================================================================
-- schema.sql
-- Netflix-style PHP Project — Database Schema + Sample Data
-- ================================================================
-- Run this in phpMyAdmin SQL tab OR via: mysql -u root < schema.sql
-- ================================================================

-- 1. Create & select the database
CREATE DATABASE IF NOT EXISTS netflix_php
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE netflix_php;

-- 2. Drop old table if re-running
DROP TABLE IF EXISTS videos;

-- 3. Create the videos table
CREATE TABLE videos (
    id           INT          UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title        VARCHAR(255) NOT NULL,
    description  TEXT         NOT NULL,
    thumbnail_url VARCHAR(500) NOT NULL,
    video_url    VARCHAR(500) NOT NULL,
    duration     VARCHAR(20)  NOT NULL,
    genre        VARCHAR(100) NOT NULL,
    upload_date  DATE         NOT NULL,
    created_at   TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Insert sample videos
INSERT INTO videos (title, description, thumbnail_url, video_url, duration, genre, upload_date) VALUES
(
    'Big Buck Bunny',
    'A large and lovable rabbit deals with three tiny bullies, eventually fighting back in an incredible way. A short open-source animated film produced by the Blender Institute.',
    'https://peach.blender.org/wp-content/uploads/title_anouncement.jpg',
    'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4',
    '9m 56s',
    'Animation',
    '2008-04-10'
),

(
    'Elephant Dream',
    'The first Blender open movie. Emo and Proog explore a fantastic mechanical world. A short animated film about two characters who seem to perceive their world very differently.',
    'https://orange.blender.org/wp-content/uploads/sites/5/2013/06/edDVD.jpg',
    'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ElephantsDream.mp4',
    '10m 54s',
    'Animation',
    '2006-03-24'
),

(
    'Subaru Outback Ad',
    'A scenic journey through stunning mountain landscapes. Experience the power and elegance of adventure vehicles navigating breathtaking terrain in this cinematic short.',
    'https://images.unsplash.com/photo-1493238792000-8113da705763?w=640&q=80',
    'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/SubaruOutbackOnStreetAndDirt.mp4',
    '0m 24s',
    'Documentary',
    '2020-06-15'
),

(
    'Volkswagen GTI Review',
    'A high-octane test drive through city streets and open highways. Watch this iconic hot hatch push its limits in a thrilling performance showcase.',
    'https://images.unsplash.com/photo-1503376780353-7e6692767b70?w=640&q=80',
    'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/VolkswagenGTIReview.mp4',
    '0m 26s',
    'Action',
    '2021-03-08'
),

(
    'We Are Going On Bullrun',
    'An epic road rally adventure across multiple countries. Follow the crew as they compete in one of the most exciting motorsport events in the world.',
    'https://images.unsplash.com/photo-1544636331-e26879cd4d9b?w=640&q=80',
    'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/WeAreGoingOnBullrun.mp4',
    '0m 16s',
    'Action',
    '2019-11-20'
),

(
    'What Car Can You Get',
    'An insightful look into the world of automotive choices. Expert reviewers compare top models across budget ranges to help you find your perfect vehicle.',
    'https://images.unsplash.com/photo-1485291571150-772bcfc10da5?w=640&q=80',
    'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/WhatCarCanYouGetForAGrand.mp4',
    '0m 13s',
    'Documentary',
    '2022-01-10'
),

(
    'For Bigger Blazes',
    'An explosive action sequence featuring state-of-the-art firefighting technology. Watch brave crews tackle some of the most intense controlled burns ever attempted.',
    'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=640&q=80',
    'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerBlazes.mp4',
    '0m 15s',
    'Action',
    '2020-08-22'
),

(
    'For Bigger Escapes',
    'A pulse-pounding adventure film following a team of elite operatives as they race against time. Packed with stunning visuals and breathtaking stunt sequences.',
    'https://images.unsplash.com/photo-1518770660439-4636190af475?w=640&q=80',
    'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerEscapes.mp4',
    '0m 15s',
    'Thriller',
    '2021-07-14'
),

(
    'For Bigger Fun',
    'A lighthearted and entertaining journey showcasing how technology transforms everyday experiences into extraordinary adventures for people of all ages.',
    'https://images.unsplash.com/photo-1522869635100-9f4c5e86aa37?w=640&q=80',
    'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerFun.mp4',
    '0m 60s',
    'Comedy',
    '2022-05-01'
),

(
    'For Bigger Joyrides',
    'Take the wheel and experience freedom on the open road. This cinematic short captures the thrill of speed, wind, and the endless horizon ahead.',
    'https://images.unsplash.com/photo-1449965408869-eaa3f722e40d?w=640&q=80',
    'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerJoyrides.mp4',
    '0m 15s',
    'Action',
    '2019-09-30'
),

(
    'For Bigger Meltdowns',
    'A deep dive into the world of extreme sports and daring challenges. Follow athletes who push human limits and redefine what is physically possible.',
    'https://images.unsplash.com/photo-1551698618-1dfe5d97d256?w=640&q=80',
    'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerMeltdowns.mp4',
    '0m 15s',
    'Thriller',
    '2023-02-18'
),

(
    'Tears of Steel',
    'A sci-fi short film produced with Blender. In a post-apocalyptic Amsterdam, humans and robots attempt to co-exist. Featuring stunning VFX and a gripping storyline.',
    'https://mango.blender.org/wp-content/uploads/sites/4/2013/05/06_01_bookending_cam_AB-1-1024x436.jpg',
    'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/TearsOfSteel.mp4',
    '12m 14s',
    'Sci-Fi',
    '2012-09-26'
);

