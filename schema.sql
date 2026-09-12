-- ================================================================
-- schema.sql  (v2 — YouTube Trailers Edition)
-- Netflix-style PHP Project — Database Schema + Sample Data
-- ================================================================
-- IMPORTANT: Re-run this in phpMyAdmin to refresh the database.
-- All video_url values are now YouTube embed URLs.
-- Thumbnails auto-generated from YouTube's thumbnail CDN.
-- ================================================================

CREATE DATABASE IF NOT EXISTS netflix_php
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE netflix_php;

DROP TABLE IF EXISTS videos;

-- ── videos table ─────────────────────────────────────────────────────────────
-- video_url now stores a YouTube video ID (11 chars), e.g. "oOC-4JxqyzM"
-- The PHP code builds the embed URL: youtube.com/embed/{video_url}
-- thumbnail_url auto-built from: img.youtube.com/vi/{video_url}/maxresdefault.jpg
-- ─────────────────────────────────────────────────────────────────────────────
CREATE TABLE videos (
    id            INT          UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title         VARCHAR(255) NOT NULL,
    description   TEXT         NOT NULL,
    thumbnail_url VARCHAR(500) NOT NULL  COMMENT 'YouTube maxresdefault thumb URL',
    video_url     VARCHAR(500) NOT NULL  COMMENT 'YouTube video ID (11 chars)',
    duration      VARCHAR(20)  NOT NULL  COMMENT 'Human-readable e.g. 2m 30s',
    genre         VARCHAR(100) NOT NULL,
    upload_date   DATE         NOT NULL,
    created_at    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ── Insert real YouTube trailers & famous scenes ──────────────────────────────
-- All are official/public YouTube videos from verified channels.
-- video_url = the 11-character YouTube video ID
-- ─────────────────────────────────────────────────────────────────────────────
INSERT INTO videos (title, description, thumbnail_url, video_url, duration, genre, upload_date) VALUES

-- ── DRAMA ─────────────────────────────────────────────────────────────────────
(
    'Breaking Bad — Official Trailer',
    'A high school chemistry teacher turned methamphetamine manufacturer partners with a former student to secure his family''s financial future. One of the greatest TV dramas ever made. Stars Bryan Cranston and Aaron Paul.',
    'https://img.youtube.com/vi/oOC-4JxqyzM/maxresdefault.jpg',
    'oOC-4JxqyzM',
    '2m 28s',
    'Drama',
    '2008-01-20'
),

(
    'Peaky Blinders — Season 1 Trailer',
    'Set in the lawless backstreets of 1920s Birmingham, the story follows the Shelby crime family and their ambitious and ruthless boss Tommy Shelby. A gritty, stylish British gangster epic.',
    'https://img.youtube.com/vi/oVzVdvGIC7U/maxresdefault.jpg',
    'oVzVdvGIC7U',
    '1m 47s',
    'Drama',
    '2013-09-12'
),

(
    'Ozark — Official Trailer',
    'A financial advisor drags his family from Chicago to the Missouri Ozarks, where he must launder $500 million in five years to appease a drug lord. Jason Bateman delivers a career-best performance.',
    'https://img.youtube.com/vi/5hAXVqrljbs/maxresdefault.jpg',
    '5hAXVqrljbs',
    '2m 30s',
    'Drama',
    '2017-07-21'
),

-- ── THRILLER ──────────────────────────────────────────────────────────────────
(
    'Squid Game — Official Trailer',
    'Hundreds of cash-strapped players accept a mysterious invitation to compete in children''s games. A hidden danger lurks behind the seemingly innocent games. The global Netflix phenomenon that took the world by storm.',
    'https://img.youtube.com/vi/oqxAJKy0ii4/maxresdefault.jpg',
    'oqxAJKy0ii4',
    '2m 11s',
    'Thriller',
    '2021-09-17'
),

(
    'Money Heist — Official Trailer',
    'A criminal mastermind who goes by "The Professor" recruits eight thieves who have nothing to lose. They take hostages, shut themselves in the Royal Mint of Spain, and carry out the biggest heist in history.',
    'https://img.youtube.com/vi/_lBGFyBiXBY/maxresdefault.jpg',
    '_lBGFyBiXBY',
    '1m 55s',
    'Thriller',
    '2017-05-02'
),

(
    'Dark — Official Trailer',
    'A missing child sets four interconnected families on a frantic hunt for answers as they unearth a sinister time travel conspiracy. Germany''s mind-bending answer to Stranger Things, spanning three seasons of pure brilliance.',
    'https://img.youtube.com/vi/ESEUtust5Gs/maxresdefault.jpg',
    'ESEUtust5Gs',
    '1m 43s',
    'Thriller',
    '2017-12-01'
),

-- ── SCI-FI ────────────────────────────────────────────────────────────────────
(
    'Stranger Things — Season 4 Trailer',
    'When a young boy disappears, his mother, a police chief, and his friends must confront terrifying supernatural forces in order to get him back. Season 4 takes the gang to new and horrifying dimensions.',
    'https://img.youtube.com/vi/b9EkMc79ZSU/maxresdefault.jpg',
    'b9EkMc79ZSU',
    '3m 22s',
    'Sci-Fi',
    '2022-05-27'
),

(
    'The Witcher — Official Trailer',
    'Geralt of Rivia, a mutated monster-hunter for hire, journeys toward his destiny in a turbulent world where people often prove more wicked than beasts. Based on the beloved fantasy book series.',
    'https://img.youtube.com/vi/ndl7O5oBM8I/maxresdefault.jpg',
    'ndl7O5oBM8I',
    '2m 31s',
    'Sci-Fi',
    '2019-12-20'
),

-- ── ACTION ────────────────────────────────────────────────────────────────────
(
    'Game of Thrones — Season 1 Trailer',
    'Nine noble families wage war against each other in order to gain control over the mythical land of Westeros. The most ambitious fantasy epic ever put to screen — dragons, politics, betrayal, and ice zombies.',
    'https://img.youtube.com/vi/KPLWWIOCOOQ/maxresdefault.jpg',
    'KPLWWIOCOOQ',
    '1m 37s',
    'Action',
    '2011-04-17'
),

(
    'Narcos — Official Trailer',
    'A chronicled look at the criminal exploits of Colombian drug lord Pablo Escobar, as well as the many other drug kingpins who plagued the country through the years. Based on real events.',
    'https://img.youtube.com/vi/l0qS1NXID-Q/maxresdefault.jpg',
    'l0qS1NXID-Q',
    '1m 49s',
    'Action',
    '2015-08-28'
),

-- ── COMEDY ────────────────────────────────────────────────────────────────────
(
    'Wednesday — Official Trailer',
    'Smart, sarcastic and a little dead inside, Wednesday Addams investigates a murder spree while making new friends and enemies at Nevermore Academy. Jenna Ortega steals every single scene.',
    'https://img.youtube.com/vi/Di310WS8zLk/maxresdefault.jpg',
    'Di310WS8zLk',
    '2m 47s',
    'Comedy',
    '2022-11-23'
),

-- ── DOCUMENTARY ───────────────────────────────────────────────────────────────
(
    'The Crown — Season 1 Trailer',
    'Follows the political rivalries and romance of Queen Elizabeth II''s reign and the events that shaped the second half of the twentieth century. A lavish, meticulously crafted royal drama.',
    'https://img.youtube.com/vi/JWtnJjn6ng0/maxresdefault.jpg',
    'JWtnJjn6ng0',
    '1m 50s',
    'Documentary',
    '2016-11-04'
);

-- ================================================================
-- Done! 12 rows inserted with real YouTube trailer IDs.
-- Re-run player.php to see the embedded YouTube player.
-- ================================================================
