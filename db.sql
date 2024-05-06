-- All code within the project should be contained 
-- in a single PHP file including CSS and JS, (excluding images)

CREATE DATABASE IF not exists second_db;

CREATE Table users(
    id INT NOTNULL PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    created TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)

INSERT INTO users (email, password) VALUES ('johndoe@gmail', 'john1234');
INSERT INTO users (email, password) VALUES ('testuser@gmail.com', 'test1234');
INSERT INTO users (email, password) VALUES ('Janedoe@gmail.com', 'Jane1234');
