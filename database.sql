-- Create database
CREATE DATABASE IF NOT EXISTS quiz_quest;
USE quiz_quest;

-- Table for quiz questions
CREATE TABLE questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question TEXT NOT NULL,
    type ENUM('mcq', 'open') NOT NULL,
    choices JSON, -- For MCQ questions, store options as JSON array
    answer TEXT NOT NULL,
    difficulty ENUM('Easy', 'Medium', 'Hard') NOT NULL,
    category VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table for quiz rounds
CREATE TABLE quiz_rounds (
    id INT AUTO_INCREMENT PRIMARY KEY,
    round_name VARCHAR(255) NOT NULL,
    theme VARCHAR(100) NOT NULL,
    time_limit INT NOT NULL, -- in seconds
    questions JSON NOT NULL, -- Array of question IDs
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table for player scores
CREATE TABLE scores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    player_name VARCHAR(100) NOT NULL,
    score INT NOT NULL,
    difficulty ENUM('Easy', 'Medium', 'Hard') NOT NULL,
    quiz_date DATE NOT NULL,
    time_taken INT NOT NULL, -- in seconds
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table for leaderboard (can be a view or materialized table)
CREATE VIEW leaderboard AS
SELECT 
    player_name, 
    score, 
    difficulty, 
    quiz_date,
    RANK() OVER (PARTITION BY difficulty ORDER BY score DESC, time_taken ASC) as rank_position
FROM scores
ORDER BY difficulty, rank_position;