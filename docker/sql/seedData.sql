-- ===========================
-- USERS
-- ===========================
INSERT INTO users (first_name, last_name, email, password_hash, role, created_at)
VALUES
    ('Admin', 'System', 'admin@example.com', crypt('admin123', gen_salt('bf')), 'admin', NOW());

-- STUDENTS
INSERT INTO users (first_name, last_name, email, password_hash, role, created_at)
SELECT
    fn.first_name,
    ln.last_name,
    lower(fn.first_name || '.' || ln.last_name || s.i || '@example.com'),
    crypt('password123', gen_salt('bf')),
    'student',
    NOW()
FROM generate_series(1,80) s(i)
         JOIN (
    SELECT unnest(ARRAY[
        'Martin','Peter','Ján','Michal','Tomáš','Lukáš','Adam','Filip','Samuel','Jakub',
        'Marek','Andrej','Roman','Daniel','Dominik','Matúš','Viktor','Oliver','Patrik','Juraj'
        ]) AS first_name
) fn ON true
         JOIN (
    SELECT unnest(ARRAY[
        'Novák','Kováč','Horváth','Varga','Tóth','Nagy','Baláž','Molnár','Hudec','Šimko',
        'Bartoš','Král','Sedlák','Urban','Mihálik','Švec','Kollár','Polák','Dudáš','Hruška'
        ]) AS last_name
) ln ON random() < 0.05
LIMIT 80;

-- TEACHERS
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
    ('Katarína','Švecová','katarina.svecova@example.com',crypt('password123',gen_salt('bf')),'teacher',NOW()),
    ('Daniel','Polák','daniel.polak@example.com',crypt('password123',gen_salt('bf')),'teacher',NOW()),
    ('Monika','Vargová','monika.vargova@example.com',crypt('password123',gen_salt('bf')),'teacher',NOW()),
    ('Filip','Nagy','filip.nagy@example.com',crypt('password123',gen_salt('bf')),'teacher',NOW()),
    ('Alena','Hudecová','alena.hudecova@example.com',crypt('password123',gen_salt('bf')),'teacher',NOW()),
    ('Pavol','Mihálik','pavol.mihalik@example.com',crypt('password123',gen_salt('bf')),'teacher',NOW());
-- ===========================
-- STUDENTS
-- ===========================
INSERT INTO students (user_id, student_number, year)
SELECT
    u.id,
    'S' || LPAD(u.id::text, 6, '0'),
    (random()*2 + 1)::int
FROM users u
WHERE u.role = 'student';
-- ===========================
-- TEACHERS
-- ===========================
INSERT INTO teachers (user_id, department)
SELECT
    u.id,
    CASE
        WHEN random() < 0.25 THEN 'Informatika'
        WHEN random() < 0.50 THEN 'Matematika'
        WHEN random() < 0.75 THEN 'Ekonomika'
        ELSE 'Fyzika'
        END
FROM users u
WHERE u.role = 'teacher';
-- ===========================
-- COURSES
-- ===========================
INSERT INTO courses (teacher_id, name, description, credits)
SELECT
    t.id,
    c.name,
    c.description,
    c.credits
FROM teachers t
         JOIN (
    VALUES
        ('Programovanie v Pythone','Základy programovania v jazyku Python',6),
        ('Databázové systémy','Relačný model, SQL, normalizácia',6),
        ('Algoritmy a dátové štruktúry','Základné algoritmy a ich zložitosť',6),
        ('Lineárna algebra','Matice, vektory a lineárne transformácie',5),
        ('Diskrétna matematika','Logika, grafy, kombinatorika',5),
        ('Operačné systémy','Procesy, pamäť, súborové systémy',6),
        ('Počítačové siete','TCP/IP, sieťové protokoly',5),
        ('Softvérové inžinierstvo','Vývoj softvéru, UML, testovanie',6),
        ('Ekonomika podniku','Základy mikro a makroekonómie',4),
        ('Štatistika a pravdepodobnosť','Popisná a inferenčná štatistika',5),
        ('Webové technológie','HTML, CSS, JavaScript, backend',6),
        ('Základy umelej inteligencie','Vyhľadávanie, heuristiky, ML úvod',6)
) AS c(name, description, credits)
              ON random() < 0.35;
-- ===========================
-- ENROLLMENTS
-- ===========================
INSERT INTO enrollments (student_id, course_id, grade, status)
SELECT
    s.id,
    c.id,
    CASE
        WHEN st = 'approved' AND random() < 0.7 THEN
            (ARRAY['A','B','C','D','E'])[ceil(random()*5)]
        ELSE NULL
        END AS grade,
    st
FROM students s
         JOIN LATERAL (
    SELECT id FROM courses ORDER BY random() LIMIT (random()*3 + 1)::int
    ) c ON true
         CROSS JOIN LATERAL (
    SELECT CASE
               WHEN random() < 0.75 THEN 'approved'
               ELSE 'not_approved'
               END AS st
    ) status;
