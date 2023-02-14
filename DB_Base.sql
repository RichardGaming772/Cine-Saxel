--
-- Created from PostgreSQL dump
--

CREATE TABLE film.actors (
    "idActor" integer NOT NULL,
    name character varying(50)
);

CREATE SEQUENCE film."actors_idActor_seq"
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

CREATE TABLE film.directors (
    "idDirector" integer NOT NULL,
    name character varying(50)
);

CREATE SEQUENCE film."directors_idDirector_seq"
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

CREATE TABLE film."filmActors" (
    "idFilm" character varying(20) NOT NULL,
    "idActor" integer NOT NULL
);

CREATE TABLE film."filmDirectors" (
    "idFilm" character varying(20) NOT NULL,
    "idDirector" integer NOT NULL
);

CREATE TABLE film."filmGenre" (
    "idFilm" character varying(20) NOT NULL,
    "idGenre" integer NOT NULL
);

CREATE TABLE film.filmadded (
    "idFilm" character varying(20) NOT NULL,
    username character varying(10),
    date date NOT NULL
);

CREATE TABLE film.films (
    "idFilm" character varying(20) NOT NULL,
    title character varying(100) NOT NULL,
    length character varying(10),
    year integer,
    image character varying(200),
    location character varying(500),
    comment character varying,
    type character varying(50),
    plot character varying,
    "lengthMins" integer,
    localtitle character varying(100)
);

CREATE TABLE film.genre (
    "idGenre" integer NOT NULL,
    "genreName" character varying(20) NOT NULL
);

CREATE SEQUENCE film."genre_idGenre_seq"
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

CREATE TABLE film."user" (
    username character varying(10) NOT NULL
);

CREATE TABLE film."userScore" (
    "idFilm" character varying(20) NOT NULL,
    username character varying(10) NOT NULL,
    score integer,
    CONSTRAINT "scoreRange" CHECK (((score >= 0) AND (score <= 5)))
);

CREATE TABLE film."userSeen" (
    "idFilm" character varying(20) NOT NULL,
    username character varying(10) NOT NULL,
    seen boolean DEFAULT false
);

ALTER TABLE ONLY film.actors ALTER COLUMN "idActor" SET DEFAULT nextval('film."actors_idActor_seq"'::regclass);

ALTER TABLE ONLY film.directors ALTER COLUMN "idDirector" SET DEFAULT nextval('film."directors_idDirector_seq"'::regclass);

ALTER TABLE ONLY film.genre ALTER COLUMN "idGenre" SET DEFAULT nextval('film."genre_idGenre_seq"'::regclass);

ALTER TABLE ONLY film.actors
    ADD CONSTRAINT actors_pkey PRIMARY KEY ("idActor");

ALTER TABLE ONLY film.directors
    ADD CONSTRAINT directors_pkey PRIMARY KEY ("idDirector");

ALTER TABLE ONLY film."filmActors"
    ADD CONSTRAINT "filmActors_pkey" PRIMARY KEY ("idFilm", "idActor");

ALTER TABLE ONLY film."filmDirectors"
    ADD CONSTRAINT "filmDirectors_pkey" PRIMARY KEY ("idFilm", "idDirector");

ALTER TABLE ONLY film."filmGenre"
    ADD CONSTRAINT "filmGenre_pkey" PRIMARY KEY ("idFilm", "idGenre");

ALTER TABLE ONLY film.filmadded
    ADD CONSTRAINT filmadded_pkey PRIMARY KEY ("idFilm");

ALTER TABLE ONLY film.films
    ADD CONSTRAINT films_pkey PRIMARY KEY ("idFilm");

ALTER TABLE ONLY film.genre
    ADD CONSTRAINT genre_pkey PRIMARY KEY ("idGenre");

ALTER TABLE ONLY film."userScore"
    ADD CONSTRAINT "userScore_pkey" PRIMARY KEY ("idFilm", username);

ALTER TABLE ONLY film."userSeen"
    ADD CONSTRAINT "userSeen_pkey" PRIMARY KEY ("idFilm", username);

ALTER TABLE ONLY film."user"
    ADD CONSTRAINT user_pkey PRIMARY KEY (username);

ALTER TABLE ONLY film."filmActors"
    ADD CONSTRAINT "filmActors_idActor_fkey" FOREIGN KEY ("idActor") REFERENCES film.actors("idActor") ON UPDATE CASCADE;

ALTER TABLE ONLY film."filmActors"
    ADD CONSTRAINT "filmActors_idFilm_fkey" FOREIGN KEY ("idFilm") REFERENCES film.films("idFilm") ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE ONLY film."filmDirectors"
    ADD CONSTRAINT "filmDirectors_idDirector_fkey" FOREIGN KEY ("idDirector") REFERENCES film.directors("idDirector") ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE ONLY film."filmDirectors"
    ADD CONSTRAINT "filmDirectors_idFilm_fkey" FOREIGN KEY ("idFilm") REFERENCES film.films("idFilm") ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE ONLY film."filmGenre"
    ADD CONSTRAINT "filmGenre_idFilm_fkey" FOREIGN KEY ("idFilm") REFERENCES film.films("idFilm") ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE ONLY film."filmGenre"
    ADD CONSTRAINT "filmGenre_idGenre_fkey" FOREIGN KEY ("idGenre") REFERENCES film.genre("idGenre") ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE ONLY film.filmadded
    ADD CONSTRAINT "filmadded_idFilm_fkey" FOREIGN KEY ("idFilm") REFERENCES film.films("idFilm") ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE ONLY film."userScore"
    ADD CONSTRAINT "userScore_idFilm_fkey" FOREIGN KEY ("idFilm") REFERENCES film.films("idFilm") ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE ONLY film."userScore"
    ADD CONSTRAINT "userScore_username_fkey" FOREIGN KEY (username) REFERENCES film."user"(username) ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE ONLY film."userSeen"
    ADD CONSTRAINT "userSeen_idFilm_fkey" FOREIGN KEY ("idFilm") REFERENCES film.films("idFilm") ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE ONLY film."userSeen"
    ADD CONSTRAINT "userSeen_username_fkey" FOREIGN KEY (username) REFERENCES film."user"(username) ON UPDATE CASCADE ON DELETE CASCADE;
