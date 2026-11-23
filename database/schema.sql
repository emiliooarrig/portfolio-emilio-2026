-- Base de datos del portafolio (MySQL 8 / MariaDB 10.5+)
-- Uso: mysql -u root -p < database/schema.sql

CREATE DATABASE IF NOT EXISTS portfolio
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE portfolio;

CREATE TABLE IF NOT EXISTS contact_messages (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name        VARCHAR(80)  NOT NULL,
    email       VARCHAR(160) NOT NULL,
    topic       ENUM('desarrollo', 'soporte', 'datos', 'vacante') NOT NULL,
    message     TEXT         NOT NULL,
    ip_hash     CHAR(64)     NOT NULL COMMENT 'HMAC-SHA256 de la IP con APP_KEY',
    user_agent  VARCHAR(255) NOT NULL DEFAULT '',
    status      ENUM('nuevo', 'leido', 'respondido', 'archivado') NOT NULL DEFAULT 'nuevo',
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_contact_created (created_at),
    KEY idx_contact_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Administradores del panel (/admin). Se crean con: composer admin:create
CREATE TABLE IF NOT EXISTS admins (
    id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name           VARCHAR(80)  NOT NULL,
    email          VARCHAR(160) NOT NULL,
    password_hash  VARCHAR(255) NOT NULL,
    last_login_at  DATETIME     NULL,
    created_at     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_admins_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Certificaciones de la sección pública. Se editan desde /admin/certificaciones.
-- Para cargar las de data/certifications.json: composer certs:import
CREATE TABLE IF NOT EXISTS certifications (
    id            INT UNSIGNED      NOT NULL AUTO_INCREMENT,
    title         VARCHAR(200)      NOT NULL,
    issuer        VARCHAR(160)      NOT NULL DEFAULT '',
    year          SMALLINT UNSIGNED NULL,
    status        VARCHAR(60)       NOT NULL DEFAULT '',
    url           VARCHAR(500)      NOT NULL DEFAULT '',
    icon          VARCHAR(20)       NOT NULL DEFAULT 'award',
    sort_order    INT               NOT NULL DEFAULT 0,
    is_published  TINYINT(1)        NOT NULL DEFAULT 1,
    created_at    DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_cert_order (is_published, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Experiencia laboral de la sección pública. Se edita desde /admin/experiencia.
-- Para cargar la de data/experience.json: composer experience:import
CREATE TABLE IF NOT EXISTS experiences (
    id            INT UNSIGNED      NOT NULL AUTO_INCREMENT,
    company       VARCHAR(120)      NOT NULL,
    role          VARCHAR(160)      NOT NULL DEFAULT '',
    summary       VARCHAR(600)      NOT NULL DEFAULT '',
    start_year    SMALLINT UNSIGNED NULL,
    start_month   TINYINT UNSIGNED  NULL COMMENT '1-12, NULL = solo el año',
    end_year      SMALLINT UNSIGNED NULL,
    end_month     TINYINT UNSIGNED  NULL,
    is_current    TINYINT(1)        NOT NULL DEFAULT 0 COMMENT '1 = "hoy", ignora end_*',
    sort_order    INT               NOT NULL DEFAULT 0,
    is_published  TINYINT(1)        NOT NULL DEFAULT 1,
    created_at    DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_exp_order (is_published, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Stack tecnológico: categorías (1) → herramientas (N). Se edita desde /admin/stack.
-- Para cargar el stack inicial: composer db:seed (database/seeds/stack.sql)
CREATE TABLE IF NOT EXISTS stack_categories (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name          VARCHAR(80)  NOT NULL,
    icon          VARCHAR(20)  NOT NULL DEFAULT 'code',
    sort_order    INT          NOT NULL DEFAULT 0,
    is_published  TINYINT(1)   NOT NULL DEFAULT 1,
    created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_stack_cat_name (name),
    KEY idx_stack_cat_order (is_published, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Al borrar una categoría se borran sus herramientas (ON DELETE CASCADE).
CREATE TABLE IF NOT EXISTS stack_tools (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    category_id   INT UNSIGNED NOT NULL,
    name          VARCHAR(60)  NOT NULL,
    in_marquee    TINYINT(1)   NOT NULL DEFAULT 0 COMMENT '1 = aparece en la cinta animada de la sección',
    sort_order    INT          NOT NULL DEFAULT 0,
    is_published  TINYINT(1)   NOT NULL DEFAULT 1,
    created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_stack_tool (category_id, name),
    KEY idx_stack_tool_order (category_id, sort_order),
    CONSTRAINT fk_stack_tool_category FOREIGN KEY (category_id)
        REFERENCES stack_categories (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Proyectos de la sección pública, con su caso en formato STAR + aprendizaje.
-- Cada uno tiene su página en /proyectos/{slug}. Se editan desde /admin/proyectos.
-- categories y stack son listas separadas por coma. Para cargar data/projects.json: composer projects:import
CREATE TABLE IF NOT EXISTS projects (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    slug          VARCHAR(80)  NOT NULL COMMENT 'Parte de la URL: /proyectos/{slug}',
    title         VARCHAR(120) NOT NULL,
    subtitle      VARCHAR(200) NOT NULL DEFAULT '',
    kicker        VARCHAR(80)  NOT NULL DEFAULT '',
    status        VARCHAR(60)  NOT NULL DEFAULT '',
    is_live       TINYINT(1)   NOT NULL DEFAULT 0 COMMENT '1 = punto verde junto al estado',
    is_featured   TINYINT(1)   NOT NULL DEFAULT 0 COMMENT '1 = tarjeta grande',
    visual        VARCHAR(20)  NOT NULL DEFAULT 'dash',
    categories    VARCHAR(40)  NOT NULL DEFAULT '' COMMENT 'Claves de filtro separadas por coma: dev,ops,data',
    description   VARCHAR(400) NOT NULL DEFAULT '' COMMENT 'Resumen de la tarjeta',
    stack         VARCHAR(500) NOT NULL DEFAULT '' COMMENT 'Tecnologías separadas por coma',
    repo_url      VARCHAR(500) NOT NULL DEFAULT '',
    situation     TEXT         NOT NULL,
    task          TEXT         NOT NULL,
    action        TEXT         NOT NULL,
    result        TEXT         NOT NULL,
    learning      TEXT         NOT NULL,
    sort_order    INT          NOT NULL DEFAULT 0,
    is_published  TINYINT(1)   NOT NULL DEFAULT 1,
    created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_projects_slug (slug),
    KEY idx_projects_order (is_published, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Recomendado: un usuario con permisos mínimos para la app.
-- CREATE USER 'portfolio_app'@'localhost' IDENTIFIED BY 'cambia-esta-contraseña';
-- GRANT SELECT, INSERT, UPDATE, DELETE ON portfolio.* TO 'portfolio_app'@'localhost';
