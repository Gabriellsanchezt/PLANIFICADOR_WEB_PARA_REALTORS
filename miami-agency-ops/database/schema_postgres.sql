-- =====================================================================
-- ESQUEMA DE BASE DE DATOS RELACIONAL NORMALIZADO EN 3NF (PostgreSQL)
-- AGENCIA AUDIOVISUAL Y OPERACIONES CON REALTORS EN MIAMI
-- =====================================================================

-- DOMINIO DE AUTENTICACIÓN Y CONTROL DE ACCESO (RBAC)

CREATE TABLE IF NOT EXISTS roles (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    description VARCHAR(255) NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS specialties (
    id SERIAL PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    description VARCHAR(255) NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE TYPE user_status_enum AS ENUM ('ACTIVO', 'INACTIVO', 'SUSPENDIDO');

CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    email VARCHAR(191) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role_id INT NOT NULL REFERENCES roles(id) ON DELETE RESTRICT,
    status user_status_enum DEFAULT 'ACTIVO',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS user_specialties (
    user_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    specialty_id INT NOT NULL REFERENCES specialties(id) ON DELETE CASCADE,
    assigned_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, specialty_id)
);

-- DOMINIO OPERATIVO DE LA AGENCIA

CREATE TABLE IF NOT EXISTS employees (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL UNIQUE REFERENCES users(id) ON DELETE CASCADE,
    full_name VARCHAR(150) NOT NULL,
    phone VARCHAR(30) NULL,
    hire_date DATE NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE TYPE realtor_status_enum AS ENUM ('ACTIVO', 'INACTIVO', 'PROSPECTO');

CREATE TABLE IF NOT EXISTS realtors (
    id SERIAL PRIMARY KEY,
    company_name VARCHAR(150) NOT NULL,
    contact_person VARCHAR(150) NOT NULL,
    phone VARCHAR(30) NOT NULL,
    email VARCHAR(191) NOT NULL UNIQUE,
    miami_zone VARCHAR(100) NOT NULL,
    social_handles JSONB NULL,
    status realtor_status_enum DEFAULT 'ACTIVO',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS contract_templates (
    id SERIAL PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    description TEXT NULL,
    template_body_html TEXT NOT NULL,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE TYPE contract_status_enum AS ENUM ('BORRADOR', 'EN_REVISION', 'APROBADO', 'RECHAZADO');

CREATE TABLE IF NOT EXISTS contracts (
    id SERIAL PRIMARY KEY,
    realtor_id INT NOT NULL REFERENCES realtors(id) ON DELETE RESTRICT,
    template_id INT NOT NULL REFERENCES contract_templates(id) ON DELETE RESTRICT,
    generated_by_employee_id INT NOT NULL REFERENCES employees(id) ON DELETE RESTRICT,
    contract_code VARCHAR(50) NOT NULL UNIQUE,
    compiled_body_html TEXT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    monthly_fee NUMERIC(10,2) NOT NULL,
    status contract_status_enum DEFAULT 'BORRADOR',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE TYPE review_status_enum AS ENUM ('APROBADO', 'RECHAZADO');

CREATE TABLE IF NOT EXISTS contract_reviews (
    id SERIAL PRIMARY KEY,
    contract_id INT NOT NULL REFERENCES contracts(id) ON DELETE CASCADE,
    reviewer_user_id INT NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
    status_assigned review_status_enum NOT NULL,
    feedback_notes TEXT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE TYPE activity_type_enum AS ENUM ('EDICION_VIDEO', 'CARRUSEL_FOTO', 'RODAJE_CAMARA', 'COMMUNITY_MANAGEMENT', 'OTRO');
CREATE TYPE priority_enum AS ENUM ('BAJA', 'MEDIA', 'ALTA', 'URGENTE');
CREATE TYPE activity_status_enum AS ENUM ('NUEVA', 'EN_PROCESO', 'ENTREGADA_REVISION', 'APROBADA', 'CORRECCION');

CREATE TABLE IF NOT EXISTS activities (
    id SERIAL PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT NULL,
    realtor_id INT NOT NULL REFERENCES realtors(id) ON DELETE CASCADE,
    employee_id INT NOT NULL REFERENCES employees(id) ON DELETE RESTRICT,
    contract_id INT NULL REFERENCES contracts(id) ON DELETE SET NULL,
    activity_type activity_type_enum NOT NULL,
    priority priority_enum DEFAULT 'MEDIA',
    scheduled_date DATE NOT NULL,
    current_status activity_status_enum DEFAULT 'NUEVA',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE TYPE deliverable_type_enum AS ENUM ('FRAME_IO', 'GOOGLE_DRIVE', 'DROPBOX', 'OTRO');

CREATE TABLE IF NOT EXISTS activity_deliverables (
    id SERIAL PRIMARY KEY,
    activity_id INT NOT NULL REFERENCES activities(id) ON DELETE CASCADE,
    deliverable_url VARCHAR(500) NOT NULL,
    deliverable_type deliverable_type_enum NOT NULL,
    notes TEXT NULL,
    submitted_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE TYPE deliverable_review_enum AS ENUM ('APROBADO', 'REQUIERE_CAMBIOS');

CREATE TABLE IF NOT EXISTS activity_reviews (
    id SERIAL PRIMARY KEY,
    deliverable_id INT NOT NULL REFERENCES activity_deliverables(id) ON DELETE CASCADE,
    reviewer_user_id INT NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
    review_status deliverable_review_enum NOT NULL,
    review_notes TEXT NOT NULL,
    reviewed_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_pg_users_email ON users(email);
CREATE INDEX idx_pg_contracts_status ON contracts(status);
CREATE INDEX idx_pg_contracts_realtor ON contracts(realtor_id);
CREATE INDEX idx_pg_activities_employee_status ON activities(employee_id, current_status);
