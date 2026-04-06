-- This file is for reference only. Use Laravel migration for actual creation.

-- ============================================
-- DISTRICTS
-- ============================================

CREATE TABLE districts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ============================================
-- SCHOOLS
-- ============================================

-- Schools table (district_id FK + loose district VARCHAR)
CREATE TABLE schools (
    id INT AUTO_INCREMENT PRIMARY KEY,

    name VARCHAR(200) NOT NULL,
    district_id INT,
    address TEXT,

    -- New Profile Fields
    governance_level VARCHAR(100),         -- e.g. "Elementary", "Secondary"
    ro VARCHAR(100),                       -- Regional Office
    sdo VARCHAR(100),                      -- Schools Division Office
    location VARCHAR(255),                 -- General location/landmark
    coordinates_lat VARCHAR(50),           -- Latitude as string for flexibility
    coordinates_long VARCHAR(50),          -- Longitude as string for flexibility
    travel_time_min INT,                   -- Travel time in minutes
    access_paths VARCHAR(200),             -- Comma-separated or JSON
    contact_mobile1 VARCHAR(50),
    contact_mobile2 VARCHAR(50),
    contact_landline VARCHAR(50),
    head_name VARCHAR(150),                -- School Head Name
    head_position VARCHAR(100),            -- School Head Position
    head_email VARCHAR(150),               -- School Head Email
    admin_name VARCHAR(150),               -- Admin/Inventory Clerk Name
    admin_mobile VARCHAR(50),              -- Admin/Inventory Clerk Contact
    nearby_institutions VARCHAR(255),      -- Comma-separated or JSON
    notes TEXT,                            -- General Notes

    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (district_id) REFERENCES districts(id)
);

-- ============================================
-- POSITIONS
-- ============================================

CREATE TABLE positions (
    id INT AUTO_INCREMENT PRIMARY KEY,

    name VARCHAR(200),
    type ENUM('TEACHING','NONTEACHING','RELATED_TEACHING'),
    description TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- INSERT DATA LATER

-- ============================================
-- PERSONNEL (Only Operational Attributes)
-- ============================================

CREATE TABLE personnel (
    id INT AUTO_INCREMENT PRIMARY KEY,
    position_id INT,
    assigned_school_id INT,
    deployed_school_id INT,

    profile_photo VARCHAR(255),
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    emp_id VARCHAR(50) UNIQUE,          -- COMMENT: Check agency_employee_number comment in pds_personal
    item_number VARCHAR(50) UNIQUE,     -- COMMENT: Check agency_employee_number comment in pds_personal
    current_step INT,                   -- Used for STEP Monitoring
    last_step_increment_date DATE,      -- Used for STEP Monitoring

    FOREIGN KEY (position_id) REFERENCES positions(id),
    FOREIGN KEY (assigned_school_id) REFERENCES schools(id),
    FOREIGN KEY (deployed_school_id) REFERENCES schools(id)
);

-- ============================================
-- PDS (Contains the Actual Personnel Details)
-- Based on Actual PDS Format
-- ============================================

-- SUBMISSION

CREATE TABLE pds_submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    personnel_id INT,
    version_number INT,

    submitted_at DATETIME NULL,
    submitted_by INT NULL,

    status ENUM('SUBMITTED','APPROVED','REJECTED') NULL,
    reviewed_at DATETIME NULL,
    reviewed_by INT NULL,
    review_remarks TEXT NULL,

    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (personnel_id) REFERENCES personnel(id)
);

-- MAIN (PERSONAL & FAMILY & QUESTIONS)
CREATE TABLE pds_main (
    id INT AUTO_INCREMENT PRIMARY KEY,
    personnel_id INT,
    submission_id INT,

    -- PERSONAL INFORMATION [Section I] (Page 1)

    last_name VARCHAR(50),
    first_name VARCHAR(50),
    middle_name VARCHAR(50),
    extension_name VARCHAR(20),
    birth_date DATE,
    birth_place VARCHAR(200),
    birth_sex ENUM('MALE','FEMALE'),
    civil_status ENUM('SINGLE','MARRIED','WIDOWED','SEPARATED'),
    height DECIMAL(5,2),
    weight DECIMAL(5,2),
    blood_type ENUM('A+','A-','B+','B-','AB+','AB-','O+','O-'),

    umid_id_number VARCHAR(50),
    pagibig_number VARCHAR(50),
    philhealth_number VARCHAR(50),
    philsys_number VARCHAR(50) UNIQUE,
    tin_number VARCHAR(50) UNIQUE,
    agency_employee_number VARCHAR(50) UNIQUE, -- COMMENT: Is this employee/item number? If so, remove one.

    citizenship_type ENUM('FILIPINO','DUAL') NOT NULL DEFAULT 'FILIPINO',
    citizenship_mode ENUM('BIRTH','NATURALIZATION') NULL,
    dual_citizenship_country VARCHAR(100) NULL,
    dual_citizenship_details TEXT,

    res_house_lot VARCHAR(100) NULL,
    res_street VARCHAR(150) NULL,
    res_subdivision VARCHAR(150) NULL,
    res_barangay VARCHAR(100) NULL,
    res_city VARCHAR(100) NULL,
    res_province VARCHAR(100) NULL,
    res_zipcode VARCHAR(10) NULL,

    perm_house_lot VARCHAR(100) NULL,
    perm_street VARCHAR(150) NULL,
    perm_subdivision VARCHAR(150) NULL,
    perm_barangay VARCHAR(100) NULL,
    perm_city VARCHAR(100) NULL,
    perm_province VARCHAR(100) NULL,
    perm_zipcode VARCHAR(10) NULL,

    telephone VARCHAR(50),
    mobile VARCHAR(50),
    email_address VARCHAR(200) NULL,

    -- FAMILY BACKGROUND [Section II] (Page 1)

    spouse_last_name VARCHAR(50),
    spouse_first_name VARCHAR(50),
    spouse_middle_name VARCHAR(50),
    spouse_extension_name VARCHAR(20),
    spouse_occupation VARCHAR(50),
    spouse_employer VARCHAR(200),
    employer_address TEXT,
    spouse_telephone VARCHAR(50),

    father_last_name VARCHAR(50),
    father_first_name VARCHAR(50),
    father_middle_name VARCHAR(50),
    father_extension_name VARCHAR(20),

    mother_last_name VARCHAR(50),
    mother_first_name VARCHAR(50),
    mother_middle_name VARCHAR(50),

    -- QUESTIONS (Page 4)

    related_third_degree TINYINT(1),
    related_fourth_degree TINYINT(1),
    related_fourth_degree_details TEXT NULL,

    admin_offense TINYINT(1),
    admin_offense_details TEXT NULL,
    criminal_case TINYINT(1),
    criminal_case_details TEXT NULL,

    convicted TINYINT(1),
    convicted_details TEXT NULL,

    separated_service TINYINT(1),
    separated_service_details TEXT NULL,

    election_candidate TINYINT(1),
    election_candidate_details TEXT NULL,
    election_resigned TINYINT(1),
    election_resigned_details TEXT NULL,

    immigrant TINYINT(1),
    immigrant_details TEXT NULL,

    indigenous TINYINT(1),
    indigenous_details TEXT NULL,
    pwd TINYINT(1),
    pwd_details TEXT NULL,
    solo_parent TINYINT(1),
    solo_parent_details TEXT NULL,

    -- GOVERNMENT ID (Page 4)

    issued_id VARCHAR(50),
    id_number VARCHAR(50),
    issue_date DATE,
    issue_place VARCHAR(200),

    FOREIGN KEY (personnel_id) REFERENCES personnel(id),
    FOREIGN KEY (submission_id) REFERENCES pds_submissions(id)
);

-- CHILDREN (Under Family Background) [Section II] (Page 1) [1:M]

CREATE TABLE pds_children (
    id INT AUTO_INCREMENT PRIMARY KEY,
    personnel_id INT,
    submission_id INT,

    child_name VARCHAR(200),
    birth_date DATE,

    FOREIGN KEY (personnel_id) REFERENCES personnel(id),
    FOREIGN KEY (submission_id) REFERENCES pds_submissions(id)
);

-- EDUCATION [Section III] (Page 1) [1:M]

CREATE TABLE pds_education (
    id INT AUTO_INCREMENT PRIMARY KEY,
    personnel_id INT,
    submission_id INT,

    level ENUM('ELEMENTARY','SECONDARY','VOCATIONAL_TRADE_COURSE','COLLEGE','GRADUATE_STUDIES'),
    school_name VARCHAR(200),
    degree VARCHAR(200),
    from_year INT,
    to_year INT,
    honors VARCHAR(200),

    FOREIGN KEY (personnel_id) REFERENCES personnel(id),
    FOREIGN KEY (submission_id) REFERENCES pds_submissions(id)
);

-- ELIGIBILITY [Section IV] (PAGE 2) [1:M]

CREATE TABLE pds_eligibility (
    id INT AUTO_INCREMENT PRIMARY KEY,
    personnel_id INT,
    submission_id INT,

    eligibility VARCHAR(200),
    rating VARCHAR(50),
    exam_date DATE,
    exam_place VARCHAR(200),
    license_number VARCHAR(50),
    license_valid_until DATETIME,

    FOREIGN KEY (personnel_id) REFERENCES personnel(id),
    FOREIGN KEY (submission_id) REFERENCES pds_submissions(id) 
);

-- WORK EXPERIENCE [Section V] (Page 2) [1:M]

CREATE TABLE pds_work_experience (
    id INT AUTO_INCREMENT PRIMARY KEY,
    personnel_id INT,
    submission_id INT,

    start_date DATE,
    end_date DATE,
    position VARCHAR(200),
    company VARCHAR(200),
    appointment_status VARCHAR(50),

    FOREIGN KEY (personnel_id) REFERENCES personnel(id),
    FOREIGN KEY (submission_id) REFERENCES pds_submissions(id) 
);

-- TRAININGS [Section VII] (Page 3) [1:M]

CREATE TABLE pds_training (
    id INT AUTO_INCREMENT PRIMARY KEY,
    personnel_id INT,
    submission_id INT,

    title VARCHAR(200),
    start_date DATE,
    end_date DATE,
    hours INT,
    type ENUM('MANAGERIAL','SUPERVISORY','TECHNICAL'), -- COMMENT: etc in PDS. Might have to change to VARCHAR if choices are not set
    sponsor VARCHAR(200), 

    FOREIGN KEY (personnel_id) REFERENCES personnel(id),
    FOREIGN KEY (submission_id) REFERENCES pds_submissions(id) 
);

-- REFERENCES (Page 4) [1:M]

CREATE TABLE pds_references (
    id INT AUTO_INCREMENT PRIMARY KEY,
    personnel_id INT,
    submission_id INT,

    name VARCHAR(200),
    address TEXT,
    contact VARCHAR(50),

    FOREIGN KEY (personnel_id) REFERENCES personnel(id),
    FOREIGN KEY (submission_id) REFERENCES pds_submissions(id) 
);



-- ============================================
-- PERSONNEL HISTORY
-- ============================================

CREATE TABLE personnel_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    personnel_id INT,
    assigned_school_id INT,
    deployed_school_id INT,
    position_id INT,
    start_date DATE,
    end_date DATE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (personnel_id) REFERENCES personnel(id),
    FOREIGN KEY (assigned_school_id) REFERENCES schools(id),
    FOREIGN KEY (deployed_school_id) REFERENCES schools(id),
    FOREIGN KEY (position_id) REFERENCES positions(id)
);

-- ============================================
-- USERS
-- ============================================

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) UNIQUE,
    email VARCHAR(150) UNIQUE,
    password VARCHAR(255),
    office VARCHAR(100),                    -- Hard-coded Select
    -- NO role column, Roles come from SPATIE
    school_id INT NULL,
    personnel_id INT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (school_id) REFERENCES schools(id),
    FOREIGN KEY (personnel_id) REFERENCES personnel(id)
);

--
-- Add reviewer/submitter FKs after users table exists
--
ALTER TABLE pds_submissions
    ADD CONSTRAINT fk_pds_submissions_submitted_by FOREIGN KEY (submitted_by) REFERENCES users(id),
    ADD CONSTRAINT fk_pds_submissions_reviewed_by FOREIGN KEY (reviewed_by) REFERENCES users(id);

-- ============================================
-- AUDIT TRAIL
-- ============================================

CREATE TABLE activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(200),
    target_user_id INT,
    details TEXT,
    ip_address VARCHAR(100),
    user_agent TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (target_user_id) REFERENCES users(id)
);

-- ============================================
-- SPECIAL ORDERS
-- ============================================

CREATE TABLE special_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,

    so_number VARCHAR(100) NOT NULL,
    series_year YEAR NOT NULL,
    title VARCHAR(200),
    description TEXT,
    type VARCHAR(100),              -- Hard-coded Select
    -- attachment
    issued_date DATE,
    
    created_by INT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE personnel_special_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,

    personnel_id INT NOT NULL,
    special_order_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_pso_personnel FOREIGN KEY (personnel_id) REFERENCES personnel(id) ON DELETE CASCADE,
    CONSTRAINT fk_pso_special_order FOREIGN KEY (special_order_id) REFERENCES special_orders(id) ON DELETE CASCADE
);

-- ============================================
-- EQUIPMENT
-- ============================================

CREATE TABLE equipment (
    id INT AUTO_INCREMENT PRIMARY KEY,
    is_deleted TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    school_id INT NULL,

    -- CORE IDENTIFICATION

    property_number VARCHAR(100) UNIQUE,
    old_property_number VARCHAR(100),
    serial_number VARCHAR(100),

    -- EQUIPMENT DETAILS

    device_type VARCHAR(100),                   -- Hard-coded Select
    unit_of_measure VARCHAR(50),                -- Hard-coded Select
    brand VARCHAR(100),                         -- Hard-coded Select
    model VARCHAR(100),
    item_description VARCHAR(255),
    specifications TEXT,

    -- DCP INFORMATION

    is_dcp TINYINT(1) DEFAULT 0,
    dcp_package VARCHAR(100) NULL,              -- Hard-coded Select
    dcp_year YEAR NULL,
    
    -- FINANCIAL INFORMATION

    acquisition_cost DECIMAL(12,2),             
    category ENUM('LOW_VALUE','HIGH_VALUE'),    -- Auto calculated
    classification VARCHAR(100),                -- Hard-coded Select
    useful_life_years INT,
    gl_sl_code VARCHAR(50),
    uacs VARCHAR(50),

    -- ACQUISITION DETAILS

    acquisition_mode VARCHAR(100),              -- Hard-coded Select
    acquisition_source VARCHAR(100),            -- Hard-coded Select
    source_of_funds VARCHAR(100),               -- Hard-coded Select
    allotment_class VARCHAR(50),                -- Hard-coded Select
    acquisition_date DATE NULL,
    pmp_reference VARCHAR(100),

    -- MOVEMENT TRACKING (DIFFERENT TABLE)
    -- CURRENT ACCOUNTABILTIY

    accountable_personnel_id INT,
    accountable_date DATE,
    custodian_personnel_id INT,
    custodian_date DATE,

    -- SUPPLIER & WARRANTY

    supplier VARCHAR(200),
    supplier_contact VARCHAR(200),
    under_warranty TINYINT(1) DEFAULT 0,

    -- STATUS & CONDITION

    equipment_location VARCHAR(200),
    functional TINYINT(1) DEFAULT 1,
    condition_classification VARCHAR(100),
    disposition_status VARCHAR(100),
    remarks TEXT,

    CONSTRAINT fk_equipment_school FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE SET NULL,
    CONSTRAINT fk_equipment_accountable FOREIGN KEY (accountable_personnel_id) REFERENCES personnel(id) ON DELETE SET NULL,
    CONSTRAINT fk_equipment_custodian FOREIGN KEY (custodian_personnel_id) REFERENCES personnel(id) ON DELETE SET NULL
);

CREATE TABLE equipment_movements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    equipment_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    -- Automatically populated when accountability is changed
    from_personnel_id INT,
    to_personnel_id INT,
    movement_date DATE,

    -- Manually inputted by the user
    document_type ENUM('PAR','ICS','RRSP','RS','WMF','OR','SI','DR','IAR'),
    document_number VARCHAR(100),
    remarks TEXT,

    CONSTRAINT fk_move_equipment FOREIGN KEY (equipment_id) REFERENCES equipment(id) ON DELETE CASCADE,
    CONSTRAINT fk_move_from FOREIGN KEY (from_personnel_id) REFERENCES personnel(id) ON DELETE SET NULL,
    CONSTRAINT fk_move_to FOREIGN KEY (to_personnel_id) REFERENCES personnel(id) ON DELETE SET NULL
);

-- ============================================
-- INTERNET
-- ============================================

CREATE TABLE internet_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    school_id INT NOT NULL,
    is_deleted TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- CONNECTION INFORMATION

    isp_name VARCHAR(200),
    account_reference VARCHAR(100),
    connection_type ENUM('FIBER','DSL','CABLE','FIXED_WIRELESS','SATELLITE','MOBILE_DATA','LEASED_LINE'),
    subscription_type ENUM('POSTPAID','PREPAID'),
    subscription_purpose VARCHAR(255),
    acquisition_mode VARCHAR(100),
    donor VARCHAR(200),
    source_of_funds VARCHAR(200),
    plan_bandwidth_mbps INT,
    guaranteed_bandwidth_mbps INT,
    monthly_cost DECIMAL(12,2),
    status ENUM('ACTIVE','INACTIVE','PENDING_INSTALLATION'),
    coverage_area ENUM('ADMIN_ONLY','FACULTY_ROOM','COMPUTER_LAB','CLASSROOMS','WHOLE_SCHOOL'),
    package_inclusions TEXT,

    -- TECHNICAL & CONTRACT DETAILS

    installation_date DATE NULL,
    contract_end_date DATE NULL,
    ip_configuration ENUM('STATIC','DYNAMIC'),
    public_ip VARCHAR(50) NULL,
    remarks TEXT,

    -- ACCESS POINTS & COVERAGE DETAILS

    access_point_count INT,
    access_point_locations TEXT,
    admin_rooms_covered INT,
    admin_active_counter INT,
    admin_connectivity_rating INT,          -- 1 to 5
    classrooms_covered INT,
    classroom_active_counter INT,
    classroom_connectivity_rating INT,      -- 1 to 5

    -- PERFORMANCE & QUALITY

    active_isp_counter INT,
    signal_quality VARCHAR(100),
    isp_rating INT,                         -- 1 to 5

    CONSTRAINT fk_internet_school FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE
);

CREATE TABLE internet_speedtests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    internet_profile_id INT NOT NULL,

    test_date DATE,
    test_time TIME,
    download_mbps DECIMAL(8,2),
    upload_mbps DECIMAL(8,2),
    ping_ms INT,
    remarks TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_speedtest_profile FOREIGN KEY (internet_profile_id) REFERENCES internet_profiles(id) ON DELETE CASCADE
);

-- ============================================
-- LEAVE & CREDITS
-- ============================================

-- ZERO

-- NO TABLES NEEDED FOR MONITORING & REPORT