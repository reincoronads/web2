CREATE DATABASE IF NOT EXISTS personal_records;
USE personal_records;

CREATE TABLE IF NOT EXISTS personal_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    last_name VARCHAR(100) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100),
    suffix VARCHAR(20),
    dob DATE NOT NULL,
    sex ENUM('Male', 'Female') NOT NULL,
    civil_status VARCHAR(50) NOT NULL,
    civil_status_other VARCHAR(100),
    tin VARCHAR(50),
    nationality VARCHAR(100) NOT NULL,
    religion VARCHAR(100),
    pob_city_municipality VARCHAR(100) NOT NULL,
    pob_province VARCHAR(100),
    pob_country VARCHAR(100) DEFAULT 'Philippines',
    birth_same_as_home TINYINT(1) DEFAULT 0,
    home_address TEXT,
    mobile VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL,
    telephone VARCHAR(50),
    father_last_name VARCHAR(100),
    father_first_name VARCHAR(100),
    father_middle_name VARCHAR(100),
    father_suffix VARCHAR(20),
    father_dob DATE,
    mother_last_name VARCHAR(100),
    mother_first_name VARCHAR(100),
    mother_middle_name VARCHAR(100),
    mother_suffix VARCHAR(20),
    mother_dob DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS dependents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    personal_data_id INT NOT NULL,
    type ENUM('spouse', 'child', 'beneficiary') NOT NULL,
    last_name VARCHAR(100),
    first_name VARCHAR(100),
    middle_name VARCHAR(100),
    suffix VARCHAR(20),
    relationship VARCHAR(50),
    dob DATE,
    FOREIGN KEY (personal_data_id) REFERENCES personal_data(id) ON DELETE CASCADE
) ENGINE=InnoDB;