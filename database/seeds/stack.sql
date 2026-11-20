-- Stack tecnológico inicial (el que mostraba la página antes del panel).
-- Uso: composer db:seed   (solo corre si stack_categories está vacía)
-- in_marquee = 1: la herramienta también aparece en la cinta animada.

INSERT INTO stack_categories (name, icon, sort_order) VALUES ('Desarrollo', 'code', 10);
SET @cat = LAST_INSERT_ID();
INSERT INTO stack_tools (category_id, name, in_marquee, sort_order) VALUES
    (@cat, 'React',     1, 10),
    (@cat, 'Vite',      0, 20),
    (@cat, 'Node.js',   1, 30),
    (@cat, 'Express',   0, 40),
    (@cat, 'PHP · MVC', 1, 50),
    (@cat, 'Composer',  0, 60),
    (@cat, 'Java',      0, 70);

INSERT INTO stack_categories (name, icon, sort_order) VALUES ('Infraestructura & soporte', 'server', 20);
SET @cat = LAST_INSERT_ID();
INSERT INTO stack_tools (category_id, name, in_marquee, sort_order) VALUES
    (@cat, 'Linux',   1, 10),
    (@cat, 'Docker',  1, 20),
    (@cat, 'Apache',  0, 30),
    (@cat, 'Railway', 0, 40),
    (@cat, 'n8n',     1, 50),
    (@cat, 'Git',     0, 60);

INSERT INTO stack_categories (name, icon, sort_order) VALUES ('Datos', 'database', 30);
SET @cat = LAST_INSERT_ID();
INSERT INTO stack_tools (category_id, name, in_marquee, sort_order) VALUES
    (@cat, 'MySQL',       1, 10),
    (@cat, 'Prisma ORM',  0, 20),
    (@cat, 'SQLite',      0, 30),
    (@cat, 'Python',      1, 40),
    (@cat, 'Modelado ER', 0, 50);

INSERT INTO stack_categories (name, icon, sort_order) VALUES ('Seguridad', 'shield', 40);
SET @cat = LAST_INSERT_ID();
INSERT INTO stack_tools (category_id, name, in_marquee, sort_order) VALUES
    (@cat, 'JWT + bcrypt',           0, 10),
    (@cat, 'OWASP',                  0, 20),
    (@cat, 'Hashing de contraseñas', 0, 30),
    (@cat, 'Privacidad · LFPDPPP',   0, 40);
