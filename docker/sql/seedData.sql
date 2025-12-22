-- ADMIN
INSERT INTO users (first_name, last_name, email, password_hash, role, created_at)
VALUES
    ('Admin','System','admin@example.com',crypt('admin123',gen_salt('bf')),'admin',NOW());

-- STUDENTS (60)
INSERT INTO users (first_name, last_name, email, password_hash, role, created_at)
SELECT
    fn,
    ln,
    lower(fn || '.' || ln || i || '@example.com'),
    crypt('password123', gen_salt('bf')),
    'student',
    NOW()
FROM generate_series(1,60) i
         JOIN (VALUES
                   ('Martin','Novák'),('Peter','Kováč'),('Ján','Horváth'),('Michal','Varga'),
                   ('Tomáš','Tóth'),('Lukáš','Nagy'),('Adam','Baláž'),('Filip','Molnár'),
                   ('Samuel','Hudec'),('Jakub','Šimko'),('Marek','Bartoš'),('Roman','Král')
) n(fn,ln) ON random() < 0.2
LIMIT 60;

-- TEACHERS (10)
INSERT INTO users (first_name, last_name, email, password_hash, role, created_at)
VALUES
    ('Mária','Kováčová','maria.kovacova@example.com',crypt('password123',gen_salt('bf')),'teacher',NOW()),
    ('Ivan','Hronec','ivan.hronec@example.com',crypt('password123',gen_salt('bf')),'teacher',NOW()),
    ('Lucia','Šimková','lucia.simkova@example.com',crypt('password123',gen_salt('bf')),'teacher',NOW()),
    ('Peter','Benko','peter.benko@example.com',crypt('password123',gen_salt('bf')),'teacher',NOW()),
    ('Zuzana','Malá','zuzana.mala@example.com',crypt('password123',gen_salt('bf')),'teacher',NOW()),
    ('Miroslav','Urban','miroslav.urban@example.com',crypt('password123',gen_salt('bf')),'teacher',NOW()),
    ('Jozef','Král','jozef.kral@example.com',crypt('password123',gen_salt('bf')),'teacher',NOW()),
    ('Eva','Tóthová','eva.tothova@example.com',crypt('password123',gen_salt('bf')),'teacher',NOW()),
    ('Roman','Bielik','roman.bielik@example.com',crypt('password123',gen_salt('bf')),'teacher',NOW()),
    ('Katarína','Švecová','katarina.svecova@example.com',crypt('password123',gen_salt('bf')),'teacher',NOW());
INSERT INTO students (user_id, student_number, year)
SELECT
    u.id,
    'S' || LPAD(u.id::text,6,'0'),
    (random()*2 + 1)::int
FROM users u
WHERE u.role = 'student';
INSERT INTO teachers (user_id, department)
SELECT
    u.id,
    CASE
        WHEN random() < 0.3 THEN 'Informatika'
        WHEN random() < 0.6 THEN 'Matematika'
        ELSE 'Ekonomika'
        END
FROM users u
WHERE u.role = 'teacher';
INSERT INTO courses (teacher_id, name, description, credits)
VALUES
    (1,'Programovanie v Pythone','Základy programovania v Pythone',6),
    (2,'Objektovo orientované programovanie','Triedy, dedičnosť, polymorfizmus',6),
    (3,'Databázové systémy','SQL a návrh databáz',6),
    (4,'Algoritmy a dátové štruktúry','Efektívne algoritmy',6),
    (5,'Diskrétna matematika','Logika, grafy, kombinatorika',5),
    (6,'Lineárna algebra','Vektory a matice',5),
    (7,'Operačné systémy','Procesy a pamäť',6),
    (8,'Počítačové siete','TCP/IP a protokoly',5),
    (9,'Softvérové inžinierstvo','Proces vývoja softvéru',6),
    (10,'Webové aplikácie','Frontend a backend webu',6),
    (1,'Základy umelej inteligencie','Úvod do AI',6),
    (2,'Strojové učenie','Základné ML algoritmy',6),
    (3,'Teória databáz','Normalizácia a transakcie',5),
    (4,'Projektový manažment v IT','Riadenie IT projektov',4),
    (5,'Ekonomika pre informatikov','Ekonomické princípy',4);
INSERT INTO enrollments (student_id, course_id, grade, status)
SELECT
    s.id,
    c.id,
    CASE
        WHEN st = 'approved' AND random() < 0.7
            THEN (ARRAY['A','B','C','D','E'])[ceil(random()*5)]
        ELSE NULL
        END,
    st
FROM students s
         JOIN LATERAL (
    SELECT id FROM courses
    ORDER BY random()
    LIMIT (random()*3 + 1)::int
    ) c ON true
         CROSS JOIN LATERAL (
    SELECT CASE
               WHEN random() < 0.75 THEN 'approved'
               ELSE 'not_approved'
               END
    ) status(st);


ALTER TABLE courses ADD CONSTRAINT unique_course_name UNIQUE (name);