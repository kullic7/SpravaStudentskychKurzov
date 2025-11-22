CREATE TABLE users (
                       id              SERIAL PRIMARY KEY,
                       first_name      VARCHAR(255) NOT NULL,
                       last_name       VARCHAR(255) NOT NULL,
                       email           VARCHAR(255) NOT NULL UNIQUE,
                       password_hash   VARCHAR(255) NOT NULL,
                       role            VARCHAR(50) NOT NULL,
                       created_at      TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE students (
                          id              SERIAL PRIMARY KEY,
                          user_id         INT UNIQUE REFERENCES users(id) ON DELETE CASCADE,
                          student_number  VARCHAR(50) NOT NULL UNIQUE,
                          year            INT NOT NULL
);

CREATE TABLE teachers (
                          id          SERIAL PRIMARY KEY,
                          user_id     INT UNIQUE REFERENCES users(id) ON DELETE CASCADE,
                          department  VARCHAR(255) NOT NULL
);

CREATE TABLE courses (
                         id          SERIAL PRIMARY KEY,
                         teacher_id  INT REFERENCES teachers(id) ON DELETE SET NULL,
                         name        VARCHAR(255) NOT NULL,
                         description TEXT,
                         credits     INT NOT NULL
);

CREATE TABLE enrollments (
                             id          SERIAL PRIMARY KEY,
                             student_id  INT NOT NULL REFERENCES students(id) ON DELETE CASCADE,
                             course_id   INT NOT NULL REFERENCES courses(id) ON DELETE CASCADE,
                             grade       VARCHAR(10),
                             status      VARCHAR(50) NOT NULL
);