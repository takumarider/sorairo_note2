--
-- PostgreSQL database dump
--

\restrict tpEsjAvTGOfxJi90x0VbX7JOemyhh0sK4La5MpgwsaR1q7KeuwhcJu1UxNauOLC

-- Dumped from database version 16.15 (Debian 16.15-1.pgdg13+2)
-- Dumped by pg_dump version 16.15 (Debian 16.15-1.pgdg13+2)

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: business_hours; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.business_hours (
    id bigint NOT NULL,
    day_of_week smallint,
    specific_date date,
    open_time time(0) without time zone NOT NULL,
    close_time time(0) without time zone NOT NULL,
    is_closed boolean DEFAULT false NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: COLUMN business_hours.day_of_week; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.business_hours.day_of_week IS '曜日 (0=日, 1=月, ..., 6=土)';


--
-- Name: COLUMN business_hours.specific_date; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.business_hours.specific_date IS '特定日指定（曜日を上書き）';


--
-- Name: COLUMN business_hours.open_time; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.business_hours.open_time IS '営業開始時間';


--
-- Name: COLUMN business_hours.close_time; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.business_hours.close_time IS '営業終了時間';


--
-- Name: COLUMN business_hours.is_closed; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.business_hours.is_closed IS '休業フラグ';


--
-- Name: business_hours_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.business_hours_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: business_hours_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.business_hours_id_seq OWNED BY public.business_hours.id;


--
-- Name: cache; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cache (
    key character varying(255) NOT NULL,
    value text NOT NULL,
    expiration integer NOT NULL
);


--
-- Name: cache_locks; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cache_locks (
    key character varying(255) NOT NULL,
    owner character varying(255) NOT NULL,
    expiration integer NOT NULL
);


--
-- Name: failed_jobs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.failed_jobs (
    id bigint NOT NULL,
    uuid character varying(255) NOT NULL,
    connection text NOT NULL,
    queue text NOT NULL,
    payload text NOT NULL,
    exception text NOT NULL,
    failed_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


--
-- Name: failed_jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.failed_jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: failed_jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.failed_jobs_id_seq OWNED BY public.failed_jobs.id;


--
-- Name: job_batches; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.job_batches (
    id character varying(255) NOT NULL,
    name character varying(255) NOT NULL,
    total_jobs integer NOT NULL,
    pending_jobs integer NOT NULL,
    failed_jobs integer NOT NULL,
    failed_job_ids text NOT NULL,
    options text,
    cancelled_at integer,
    created_at integer NOT NULL,
    finished_at integer
);


--
-- Name: jobs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.jobs (
    id bigint NOT NULL,
    queue character varying(255) NOT NULL,
    payload text NOT NULL,
    attempts smallint NOT NULL,
    reserved_at integer,
    available_at integer NOT NULL,
    created_at integer NOT NULL
);


--
-- Name: jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.jobs_id_seq OWNED BY public.jobs.id;


--
-- Name: menu_options; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.menu_options (
    id bigint NOT NULL,
    menu_id bigint NOT NULL,
    name character varying(255) NOT NULL,
    price integer NOT NULL,
    duration integer NOT NULL,
    is_active boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    image_path character varying(255)
);


--
-- Name: COLUMN menu_options.name; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.menu_options.name IS 'オプション名';


--
-- Name: COLUMN menu_options.price; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.menu_options.price IS '追加料金（円）';


--
-- Name: COLUMN menu_options.duration; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.menu_options.duration IS '追加所要時間（分）';


--
-- Name: COLUMN menu_options.is_active; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.menu_options.is_active IS '有効フラグ';


--
-- Name: COLUMN menu_options.image_path; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.menu_options.image_path IS 'オプション画像パス';


--
-- Name: menu_options_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.menu_options_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: menu_options_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.menu_options_id_seq OWNED BY public.menu_options.id;


--
-- Name: menus; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.menus (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    description text,
    price integer NOT NULL,
    duration integer NOT NULL,
    image_path character varying(255),
    is_active boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    is_event boolean DEFAULT false NOT NULL,
    price_max integer,
    sort_order integer
);


--
-- Name: COLUMN menus.is_event; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.menus.is_event IS 'イベントメニューかどうか';


--
-- Name: menus_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.menus_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: menus_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.menus_id_seq OWNED BY public.menus.id;


--
-- Name: migrations; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
);


--
-- Name: migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;


--
-- Name: notes; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.notes (
    id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    title character varying(255) DEFAULT ''::character varying NOT NULL,
    content text,
    image_path character varying(255),
    is_published boolean DEFAULT true NOT NULL,
    published_at timestamp(0) without time zone
);


--
-- Name: COLUMN notes.title; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.notes.title IS 'タイトル';


--
-- Name: COLUMN notes.content; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.notes.content IS '本文';


--
-- Name: COLUMN notes.image_path; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.notes.image_path IS '画像パス';


--
-- Name: COLUMN notes.is_published; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.notes.is_published IS '公開フラグ';


--
-- Name: COLUMN notes.published_at; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.notes.published_at IS '公開日時';


--
-- Name: notes_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.notes_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: notes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.notes_id_seq OWNED BY public.notes.id;


--
-- Name: password_reset_tokens; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.password_reset_tokens (
    email character varying(255) NOT NULL,
    token character varying(255) NOT NULL,
    created_at timestamp(0) without time zone
);


--
-- Name: reservation_comments; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.reservation_comments (
    id bigint NOT NULL,
    reservation_id bigint NOT NULL,
    sender_type character varying(255) NOT NULL,
    sender_id bigint,
    body text NOT NULL,
    read_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    hidden_by_user_at timestamp(0) without time zone,
    deleted_by_admin_at timestamp(0) without time zone,
    CONSTRAINT reservation_comments_sender_type_check CHECK (((sender_type)::text = ANY ((ARRAY['user'::character varying, 'admin'::character varying])::text[])))
);


--
-- Name: reservation_comments_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.reservation_comments_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: reservation_comments_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.reservation_comments_id_seq OWNED BY public.reservation_comments.id;


--
-- Name: reservation_options; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.reservation_options (
    id bigint NOT NULL,
    reservation_id bigint NOT NULL,
    menu_option_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: reservation_options_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.reservation_options_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: reservation_options_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.reservation_options_id_seq OWNED BY public.reservation_options.id;


--
-- Name: reservation_publication_months; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.reservation_publication_months (
    id bigint NOT NULL,
    year_month character varying(7) NOT NULL,
    is_published boolean DEFAULT false NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: COLUMN reservation_publication_months.year_month; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.reservation_publication_months.year_month IS '公開設定対象月 (YYYY-MM)';


--
-- Name: COLUMN reservation_publication_months.is_published; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.reservation_publication_months.is_published IS 'ユーザー向け公開フラグ';


--
-- Name: reservation_publication_months_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.reservation_publication_months_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: reservation_publication_months_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.reservation_publication_months_id_seq OWNED BY public.reservation_publication_months.id;


--
-- Name: reservations; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.reservations (
    id bigint NOT NULL,
    user_id bigint NOT NULL,
    menu_id bigint NOT NULL,
    slot_id bigint,
    status character varying(255) DEFAULT 'confirmed'::character varying NOT NULL,
    canceled_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    date date,
    start_time time(0) without time zone,
    end_time time(0) without time zone,
    total_price integer,
    total_duration integer,
    new_dismissed_at timestamp(0) without time zone,
    CONSTRAINT reservations_status_check CHECK (((status)::text = ANY ((ARRAY['confirmed'::character varying, 'canceled'::character varying, 'completed'::character varying])::text[])))
);


--
-- Name: COLUMN reservations.status; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.reservations.status IS '予約ステータス';


--
-- Name: COLUMN reservations.canceled_at; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.reservations.canceled_at IS 'キャンセル日時';


--
-- Name: COLUMN reservations.date; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.reservations.date IS '予約日';


--
-- Name: COLUMN reservations.start_time; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.reservations.start_time IS '開始時刻';


--
-- Name: COLUMN reservations.end_time; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.reservations.end_time IS '終了時刻';


--
-- Name: COLUMN reservations.total_price; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.reservations.total_price IS '予約時点の合計料金（円）';


--
-- Name: COLUMN reservations.total_duration; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.reservations.total_duration IS '予約時点の合計所要時間（分）';


--
-- Name: reservations_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.reservations_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: reservations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.reservations_id_seq OWNED BY public.reservations.id;


--
-- Name: sessions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.sessions (
    id character varying(255) NOT NULL,
    user_id bigint,
    ip_address character varying(45),
    user_agent text,
    payload text NOT NULL,
    last_activity integer NOT NULL
);


--
-- Name: slots; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.slots (
    id bigint NOT NULL,
    menu_id bigint NOT NULL,
    date date NOT NULL,
    start_time time(0) without time zone NOT NULL,
    end_time time(0) without time zone NOT NULL,
    is_reserved boolean DEFAULT false NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    capacity integer
);


--
-- Name: COLUMN slots.date; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.slots.date IS '日付';


--
-- Name: COLUMN slots.start_time; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.slots.start_time IS '開始時間';


--
-- Name: COLUMN slots.end_time; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.slots.end_time IS '終了時間';


--
-- Name: COLUMN slots.is_reserved; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.slots.is_reserved IS '予約済フラグ';


--
-- Name: COLUMN slots.capacity; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.slots.capacity IS 'イベント定員';


--
-- Name: slots_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.slots_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: slots_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.slots_id_seq OWNED BY public.slots.id;


--
-- Name: system_settings; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.system_settings (
    id bigint NOT NULL,
    admin_notification_email character varying(255),
    notification_from_email character varying(255),
    notification_from_name character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    welcome_badge character varying(255),
    welcome_title character varying(255),
    welcome_subtitle character varying(255),
    welcome_lead text,
    welcome_main_image_path character varying(255),
    welcome_body_blocks json,
    welcome_shop_title character varying(255),
    welcome_shop_description text,
    welcome_business_hours character varying(255),
    welcome_regular_holiday character varying(255),
    welcome_business_note text,
    welcome_instagram_url character varying(255),
    user_cancel_deadline_hours smallint DEFAULT '24'::smallint NOT NULL,
    mail_user_confirmed_subject character varying(255),
    mail_user_confirmed_body text,
    mail_user_canceled_subject character varying(255),
    mail_user_canceled_body text,
    mail_admin_confirmed_subject character varying(255),
    mail_admin_confirmed_body text,
    mail_admin_canceled_subject character varying(255),
    mail_admin_canceled_body text,
    welcome_contact_number character varying(255),
    mail_event_user_confirmed_subject character varying(255),
    mail_event_user_confirmed_body text,
    mail_event_user_canceled_subject character varying(255),
    mail_event_user_canceled_body text,
    mail_event_admin_confirmed_subject character varying(255),
    mail_event_admin_confirmed_body text,
    mail_event_admin_canceled_subject character varying(255),
    mail_event_admin_canceled_body text,
    welcome_theme_background character varying(255),
    welcome_theme_accent character varying(255),
    welcome_hero_title_size character varying(255),
    welcome_hero_title_color character varying(255),
    welcome_hero_subtitle_size character varying(255),
    welcome_hero_subtitle_color character varying(255),
    welcome_hero_lead_size character varying(255),
    welcome_hero_lead_color character varying(255),
    welcome_hero_text_align character varying(255),
    welcome_hero_lead_paragraph_mode character varying(255),
    welcome_shop_title_size character varying(255),
    welcome_shop_title_color character varying(255),
    welcome_shop_body_size character varying(255),
    welcome_shop_body_color character varying(255),
    welcome_shop_paragraph_mode character varying(255),
    welcome_card_padding character varying(255),
    welcome_card_radius character varying(255),
    welcome_card_shadow character varying(255),
    welcome_font_style character varying(255)
);


--
-- Name: system_settings_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.system_settings_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: system_settings_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.system_settings_id_seq OWNED BY public.system_settings.id;


--
-- Name: time_blocks; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.time_blocks (
    id bigint NOT NULL,
    start_at timestamp(0) without time zone NOT NULL,
    end_at timestamp(0) without time zone NOT NULL,
    reason character varying(255),
    is_active boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: time_blocks_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.time_blocks_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: time_blocks_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.time_blocks_id_seq OWNED BY public.time_blocks.id;


--
-- Name: users; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.users (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    email character varying(255) NOT NULL,
    email_verified_at timestamp(0) without time zone,
    password character varying(255) NOT NULL,
    remember_token character varying(100),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    is_admin boolean DEFAULT false NOT NULL
);


--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- Name: welcome_page_sections; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.welcome_page_sections (
    id bigint NOT NULL,
    page_id bigint DEFAULT '1'::bigint NOT NULL,
    type character varying(255) NOT NULL,
    sort_order integer DEFAULT 0 NOT NULL,
    is_visible boolean DEFAULT true NOT NULL,
    settings json,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: welcome_page_sections_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.welcome_page_sections_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: welcome_page_sections_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.welcome_page_sections_id_seq OWNED BY public.welcome_page_sections.id;


--
-- Name: business_hours id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.business_hours ALTER COLUMN id SET DEFAULT nextval('public.business_hours_id_seq'::regclass);


--
-- Name: failed_jobs id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.failed_jobs ALTER COLUMN id SET DEFAULT nextval('public.failed_jobs_id_seq'::regclass);


--
-- Name: jobs id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.jobs ALTER COLUMN id SET DEFAULT nextval('public.jobs_id_seq'::regclass);


--
-- Name: menu_options id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.menu_options ALTER COLUMN id SET DEFAULT nextval('public.menu_options_id_seq'::regclass);


--
-- Name: menus id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.menus ALTER COLUMN id SET DEFAULT nextval('public.menus_id_seq'::regclass);


--
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- Name: notes id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.notes ALTER COLUMN id SET DEFAULT nextval('public.notes_id_seq'::regclass);


--
-- Name: reservation_comments id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.reservation_comments ALTER COLUMN id SET DEFAULT nextval('public.reservation_comments_id_seq'::regclass);


--
-- Name: reservation_options id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.reservation_options ALTER COLUMN id SET DEFAULT nextval('public.reservation_options_id_seq'::regclass);


--
-- Name: reservation_publication_months id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.reservation_publication_months ALTER COLUMN id SET DEFAULT nextval('public.reservation_publication_months_id_seq'::regclass);


--
-- Name: reservations id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.reservations ALTER COLUMN id SET DEFAULT nextval('public.reservations_id_seq'::regclass);


--
-- Name: slots id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.slots ALTER COLUMN id SET DEFAULT nextval('public.slots_id_seq'::regclass);


--
-- Name: system_settings id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.system_settings ALTER COLUMN id SET DEFAULT nextval('public.system_settings_id_seq'::regclass);


--
-- Name: time_blocks id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.time_blocks ALTER COLUMN id SET DEFAULT nextval('public.time_blocks_id_seq'::regclass);


--
-- Name: users id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- Name: welcome_page_sections id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.welcome_page_sections ALTER COLUMN id SET DEFAULT nextval('public.welcome_page_sections_id_seq'::regclass);


--
-- Data for Name: business_hours; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.business_hours (id, day_of_week, specific_date, open_time, close_time, is_closed, created_at, updated_at) FROM stdin;
8	0	\N	10:00:00	19:00:00	f	2026-11-10 12:49:58	2026-11-10 12:49:58
9	1	\N	10:00:00	19:00:00	f	2026-11-10 12:49:58	2026-11-10 12:49:58
10	2	\N	10:00:00	19:00:00	f	2026-11-10 12:49:58	2026-11-10 12:49:58
11	3	\N	10:00:00	19:00:00	f	2026-11-10 12:49:58	2026-11-10 12:49:58
12	4	\N	10:00:00	19:00:00	f	2026-11-10 12:49:58	2026-11-10 12:49:58
13	5	\N	10:00:00	19:00:00	f	2026-11-10 12:49:58	2026-11-10 12:49:58
14	6	\N	10:00:00	19:00:00	f	2026-11-10 12:49:58	2026-11-10 12:49:58
\.


--
-- Data for Name: cache; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.cache (key, value, expiration) FROM stdin;
\.


--
-- Data for Name: cache_locks; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.cache_locks (key, owner, expiration) FROM stdin;
\.


--
-- Data for Name: failed_jobs; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.failed_jobs (id, uuid, connection, queue, payload, exception, failed_at) FROM stdin;
\.


--
-- Data for Name: job_batches; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.job_batches (id, name, total_jobs, pending_jobs, failed_jobs, failed_job_ids, options, cancelled_at, created_at, finished_at) FROM stdin;
\.


--
-- Data for Name: jobs; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.jobs (id, queue, payload, attempts, reserved_at, available_at, created_at) FROM stdin;
\.


--
-- Data for Name: menu_options; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.menu_options (id, menu_id, name, price, duration, is_active, created_at, updated_at, image_path) FROM stdin;
1	1	サンプルオプション	1000	30	t	2026-11-10 12:49:58	2026-11-10 12:49:58	\N
\.


--
-- Data for Name: menus; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.menus (id, name, description, price, duration, image_path, is_active, created_at, updated_at, is_event, price_max, sort_order) FROM stdin;
1	サンプルカット	動作確認用の架空の施術メニューです。	4000	60	\N	t	2026-11-10 12:49:58	2026-11-10 12:49:58	f	\N	1
2	サンプルトリートメント	動作確認用の架空の施術メニューです。	5000	90	\N	t	2026-11-10 12:49:58	2026-11-10 12:49:58	f	\N	2
3	サンプルイベント	動作確認用の架空のイベントです。	2500	60	\N	t	2026-11-10 12:49:58	2026-11-10 12:49:58	t	\N	1
\.


--
-- Data for Name: migrations; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.migrations (id, migration, batch) FROM stdin;
1	0001_01_01_000000_create_users_table	1
2	0001_01_01_000001_create_cache_table	1
3	0001_01_01_000002_create_jobs_table	1
4	2026_01_26_213457_create_notes_table	1
5	2026_01_28_003550_add_is_admin_to_users_table	1
6	2026_01_31_151237_create_menus_table	1
7	2026_02_01_000000_create_slots_table	1
8	2026_02_02_000000_create_reservations_table	1
9	2026_02_02_120000_update_notes_table_add_fields	1
10	2026_02_05_000000_create_system_settings_table	1
11	2026_03_29_100000_create_business_hours_table	1
12	2026_03_29_100100_create_menu_options_table	1
13	2026_03_29_100200_create_reservation_options_table	1
14	2026_03_29_100300_add_date_time_to_reservations	1
15	2026_03_30_120000_add_image_path_to_menu_options_table	1
16	2026_04_02_000001_add_welcome_fields_to_system_settings_table	1
17	2026_04_11_120000_add_user_cancel_deadline_hours_to_system_settings_table	1
18	2026_04_11_120100_add_is_next_month_public_to_system_settings_table	1
19	2026_04_11_120200_create_time_blocks_table	1
20	2026_04_14_000100_create_reservation_publication_months_table	1
21	2026_04_14_000200_drop_is_next_month_public_from_system_settings_table	1
22	2026_04_20_000300_normalize_system_settings_singleton	1
23	2026_04_21_000400_add_mail_templates_to_system_settings_table	1
24	2026_04_21_000500_add_welcome_contact_number_to_system_settings_table	1
25	2026_04_23_120000_add_event_fields_to_menus_and_slots	1
26	2026_04_23_120100_add_event_mail_templates_to_system_settings_table	1
27	2026_04_30_100000_add_price_max_to_menus_table	1
28	2026_05_15_000100_add_sort_order_to_menus_table	1
29	2026_05_18_120000_add_welcome_style_fields_to_system_settings_table	1
30	2026_05_18_210000_add_welcome_layout_fields_to_system_settings_table	1
31	2026_05_19_090000_create_welcome_page_sections_table	1
32	2026_05_20_100000_add_total_price_and_total_duration_to_reservations_table	1
33	2026_10_01_000000_create_reservation_comments_table	1
34	2026_10_09_000000_add_hidden_by_user_at_to_reservation_comments_table	1
35	2026_10_09_100000_add_deleted_by_admin_at_to_reservation_comments_table	1
36	2026_10_28_000000_add_new_dismissed_at_to_reservations_table	1
\.


--
-- Data for Name: notes; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.notes (id, created_at, updated_at, title, content, image_path, is_published, published_at) FROM stdin;
\.


--
-- Data for Name: password_reset_tokens; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.password_reset_tokens (email, token, created_at) FROM stdin;
\.


--
-- Data for Name: reservation_comments; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.reservation_comments (id, reservation_id, sender_type, sender_id, body, read_at, created_at, updated_at, hidden_by_user_at, deleted_by_admin_at) FROM stdin;
1	1	user	2	これは動作確認用の架空のメッセージです。	\N	2026-11-10 12:49:58	2026-11-10 12:49:58	\N	\N
\.


--
-- Data for Name: reservation_options; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.reservation_options (id, reservation_id, menu_option_id, created_at, updated_at) FROM stdin;
1	1	1	2026-11-10 12:49:58	2026-11-10 12:49:58
\.


--
-- Data for Name: reservation_publication_months; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.reservation_publication_months (id, year_month, is_published, created_at, updated_at) FROM stdin;
1	2026-11	t	2026-11-10 12:49:58	2026-11-10 12:49:58
2	2026-12	t	2026-11-10 12:49:58	2026-11-10 12:49:58
\.


--
-- Data for Name: reservations; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.reservations (id, user_id, menu_id, slot_id, status, canceled_at, created_at, updated_at, date, start_time, end_time, total_price, total_duration, new_dismissed_at) FROM stdin;
1	2	1	\N	confirmed	\N	2026-11-10 12:49:58	2026-11-10 12:49:58	2026-11-11	11:00:00	12:30:00	5000	90	\N
\.


--
-- Data for Name: sessions; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.sessions (id, user_id, ip_address, user_agent, payload, last_activity) FROM stdin;
\.


--
-- Data for Name: slots; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.slots (id, menu_id, date, start_time, end_time, is_reserved, created_at, updated_at, capacity) FROM stdin;
1	3	2026-11-17	14:00:00	15:00:00	f	2026-11-10 12:49:58	2026-11-10 12:49:58	4
\.


--
-- Data for Name: system_settings; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.system_settings (id, admin_notification_email, notification_from_email, notification_from_name, created_at, updated_at, welcome_badge, welcome_title, welcome_subtitle, welcome_lead, welcome_main_image_path, welcome_body_blocks, welcome_shop_title, welcome_shop_description, welcome_business_hours, welcome_regular_holiday, welcome_business_note, welcome_instagram_url, user_cancel_deadline_hours, mail_user_confirmed_subject, mail_user_confirmed_body, mail_user_canceled_subject, mail_user_canceled_body, mail_admin_confirmed_subject, mail_admin_confirmed_body, mail_admin_canceled_subject, mail_admin_canceled_body, welcome_contact_number, mail_event_user_confirmed_subject, mail_event_user_confirmed_body, mail_event_user_canceled_subject, mail_event_user_canceled_body, mail_event_admin_confirmed_subject, mail_event_admin_confirmed_body, mail_event_admin_canceled_subject, mail_event_admin_canceled_body, welcome_theme_background, welcome_theme_accent, welcome_hero_title_size, welcome_hero_title_color, welcome_hero_subtitle_size, welcome_hero_subtitle_color, welcome_hero_lead_size, welcome_hero_lead_color, welcome_hero_text_align, welcome_hero_lead_paragraph_mode, welcome_shop_title_size, welcome_shop_title_color, welcome_shop_body_size, welcome_shop_body_color, welcome_shop_paragraph_mode, welcome_card_padding, welcome_card_radius, welcome_card_shadow, welcome_font_style) FROM stdin;
1	\N	\N	\N	2026-11-10 21:49:16	2026-11-10 21:49:16	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	24	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
\.


--
-- Data for Name: time_blocks; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.time_blocks (id, start_at, end_at, reason, is_active, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.users (id, name, email, email_verified_at, password, remember_token, created_at, updated_at, is_admin) FROM stdin;
1	サンプル管理者	admin@example.test	2026-11-10 12:49:58	$2y$12$H58Ej78L3fQ43GvDfYVdDe0LtfcWAK7FqKfGoVO4ZgpsZh.MKthk6	\N	2026-11-10 12:49:58	2026-11-10 12:49:58	t
2	サンプル利用者	customer@example.test	2026-11-10 12:49:58	$2y$12$H58Ej78L3fQ43GvDfYVdDe0LtfcWAK7FqKfGoVO4ZgpsZh.MKthk6	\N	2026-11-10 12:49:58	2026-11-10 12:49:58	f
\.


--
-- Data for Name: welcome_page_sections; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.welcome_page_sections (id, page_id, type, sort_order, is_visible, settings, created_at, updated_at) FROM stdin;
\.


--
-- Name: business_hours_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.business_hours_id_seq', 14, true);


--
-- Name: failed_jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.failed_jobs_id_seq', 1, false);


--
-- Name: jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.jobs_id_seq', 1, false);


--
-- Name: menu_options_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.menu_options_id_seq', 1, true);


--
-- Name: menus_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.menus_id_seq', 3, true);


--
-- Name: migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.migrations_id_seq', 36, true);


--
-- Name: notes_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.notes_id_seq', 1, false);


--
-- Name: reservation_comments_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.reservation_comments_id_seq', 1, true);


--
-- Name: reservation_options_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.reservation_options_id_seq', 1, true);


--
-- Name: reservation_publication_months_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.reservation_publication_months_id_seq', 2, true);


--
-- Name: reservations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.reservations_id_seq', 1, true);


--
-- Name: slots_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.slots_id_seq', 1, true);


--
-- Name: system_settings_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.system_settings_id_seq', 1, false);


--
-- Name: time_blocks_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.time_blocks_id_seq', 1, false);


--
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.users_id_seq', 2, true);


--
-- Name: welcome_page_sections_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.welcome_page_sections_id_seq', 1, false);


--
-- Name: business_hours business_hours_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.business_hours
    ADD CONSTRAINT business_hours_pkey PRIMARY KEY (id);


--
-- Name: cache_locks cache_locks_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cache_locks
    ADD CONSTRAINT cache_locks_pkey PRIMARY KEY (key);


--
-- Name: cache cache_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cache
    ADD CONSTRAINT cache_pkey PRIMARY KEY (key);


--
-- Name: failed_jobs failed_jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_uuid_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_uuid_unique UNIQUE (uuid);


--
-- Name: job_batches job_batches_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.job_batches
    ADD CONSTRAINT job_batches_pkey PRIMARY KEY (id);


--
-- Name: jobs jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.jobs
    ADD CONSTRAINT jobs_pkey PRIMARY KEY (id);


--
-- Name: menu_options menu_options_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.menu_options
    ADD CONSTRAINT menu_options_pkey PRIMARY KEY (id);


--
-- Name: menus menus_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.menus
    ADD CONSTRAINT menus_pkey PRIMARY KEY (id);


--
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- Name: notes notes_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.notes
    ADD CONSTRAINT notes_pkey PRIMARY KEY (id);


--
-- Name: password_reset_tokens password_reset_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.password_reset_tokens
    ADD CONSTRAINT password_reset_tokens_pkey PRIMARY KEY (email);


--
-- Name: reservation_comments reservation_comments_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.reservation_comments
    ADD CONSTRAINT reservation_comments_pkey PRIMARY KEY (id);


--
-- Name: reservation_options reservation_options_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.reservation_options
    ADD CONSTRAINT reservation_options_pkey PRIMARY KEY (id);


--
-- Name: reservation_options reservation_options_reservation_id_menu_option_id_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.reservation_options
    ADD CONSTRAINT reservation_options_reservation_id_menu_option_id_unique UNIQUE (reservation_id, menu_option_id);


--
-- Name: reservation_publication_months reservation_publication_months_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.reservation_publication_months
    ADD CONSTRAINT reservation_publication_months_pkey PRIMARY KEY (id);


--
-- Name: reservation_publication_months reservation_publication_months_year_month_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.reservation_publication_months
    ADD CONSTRAINT reservation_publication_months_year_month_unique UNIQUE (year_month);


--
-- Name: reservations reservations_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.reservations
    ADD CONSTRAINT reservations_pkey PRIMARY KEY (id);


--
-- Name: sessions sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (id);


--
-- Name: slots slots_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.slots
    ADD CONSTRAINT slots_pkey PRIMARY KEY (id);


--
-- Name: system_settings system_settings_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.system_settings
    ADD CONSTRAINT system_settings_pkey PRIMARY KEY (id);


--
-- Name: time_blocks time_blocks_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.time_blocks
    ADD CONSTRAINT time_blocks_pkey PRIMARY KEY (id);


--
-- Name: business_hours unique_business_hour; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.business_hours
    ADD CONSTRAINT unique_business_hour UNIQUE (day_of_week, specific_date);


--
-- Name: slots unique_slot; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.slots
    ADD CONSTRAINT unique_slot UNIQUE (menu_id, date, start_time);


--
-- Name: users users_email_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_unique UNIQUE (email);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: welcome_page_sections welcome_page_sections_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.welcome_page_sections
    ADD CONSTRAINT welcome_page_sections_pkey PRIMARY KEY (id);


--
-- Name: business_hours_day_of_week_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX business_hours_day_of_week_index ON public.business_hours USING btree (day_of_week);


--
-- Name: business_hours_specific_date_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX business_hours_specific_date_index ON public.business_hours USING btree (specific_date);


--
-- Name: cache_expiration_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX cache_expiration_index ON public.cache USING btree (expiration);


--
-- Name: cache_locks_expiration_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX cache_locks_expiration_index ON public.cache_locks USING btree (expiration);


--
-- Name: jobs_queue_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX jobs_queue_index ON public.jobs USING btree (queue);


--
-- Name: menu_options_menu_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX menu_options_menu_id_index ON public.menu_options USING btree (menu_id);


--
-- Name: menus_is_active_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX menus_is_active_index ON public.menus USING btree (is_active);


--
-- Name: menus_is_event_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX menus_is_event_index ON public.menus USING btree (is_event);


--
-- Name: menus_is_event_sort_order_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX menus_is_event_sort_order_index ON public.menus USING btree (is_event, sort_order);


--
-- Name: reservation_comments_reservation_id_created_at_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX reservation_comments_reservation_id_created_at_index ON public.reservation_comments USING btree (reservation_id, created_at);


--
-- Name: reservation_comments_sender_type_read_at_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX reservation_comments_sender_type_read_at_index ON public.reservation_comments USING btree (sender_type, read_at);


--
-- Name: reservations_date_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX reservations_date_index ON public.reservations USING btree (date);


--
-- Name: reservations_slot_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX reservations_slot_id_index ON public.reservations USING btree (slot_id);


--
-- Name: reservations_user_id_status_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX reservations_user_id_status_index ON public.reservations USING btree (user_id, status);


--
-- Name: sessions_last_activity_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX sessions_last_activity_index ON public.sessions USING btree (last_activity);


--
-- Name: sessions_user_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX sessions_user_id_index ON public.sessions USING btree (user_id);


--
-- Name: slots_date_is_reserved_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX slots_date_is_reserved_index ON public.slots USING btree (date, is_reserved);


--
-- Name: time_blocks_is_active_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX time_blocks_is_active_index ON public.time_blocks USING btree (is_active);


--
-- Name: time_blocks_start_at_end_at_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX time_blocks_start_at_end_at_index ON public.time_blocks USING btree (start_at, end_at);


--
-- Name: welcome_page_sections_page_id_is_visible_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX welcome_page_sections_page_id_is_visible_index ON public.welcome_page_sections USING btree (page_id, is_visible);


--
-- Name: welcome_page_sections_page_id_sort_order_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX welcome_page_sections_page_id_sort_order_index ON public.welcome_page_sections USING btree (page_id, sort_order);


--
-- Name: menu_options menu_options_menu_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.menu_options
    ADD CONSTRAINT menu_options_menu_id_foreign FOREIGN KEY (menu_id) REFERENCES public.menus(id) ON DELETE CASCADE;


--
-- Name: reservation_comments reservation_comments_reservation_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.reservation_comments
    ADD CONSTRAINT reservation_comments_reservation_id_foreign FOREIGN KEY (reservation_id) REFERENCES public.reservations(id) ON DELETE CASCADE;


--
-- Name: reservation_comments reservation_comments_sender_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.reservation_comments
    ADD CONSTRAINT reservation_comments_sender_id_foreign FOREIGN KEY (sender_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: reservation_options reservation_options_menu_option_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.reservation_options
    ADD CONSTRAINT reservation_options_menu_option_id_foreign FOREIGN KEY (menu_option_id) REFERENCES public.menu_options(id) ON DELETE CASCADE;


--
-- Name: reservation_options reservation_options_reservation_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.reservation_options
    ADD CONSTRAINT reservation_options_reservation_id_foreign FOREIGN KEY (reservation_id) REFERENCES public.reservations(id) ON DELETE CASCADE;


--
-- Name: reservations reservations_menu_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.reservations
    ADD CONSTRAINT reservations_menu_id_foreign FOREIGN KEY (menu_id) REFERENCES public.menus(id) ON DELETE CASCADE;


--
-- Name: reservations reservations_slot_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.reservations
    ADD CONSTRAINT reservations_slot_id_foreign FOREIGN KEY (slot_id) REFERENCES public.slots(id) ON DELETE CASCADE;


--
-- Name: reservations reservations_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.reservations
    ADD CONSTRAINT reservations_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: slots slots_menu_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.slots
    ADD CONSTRAINT slots_menu_id_foreign FOREIGN KEY (menu_id) REFERENCES public.menus(id) ON DELETE CASCADE;


--
-- PostgreSQL database dump complete
--

\unrestrict tpEsjAvTGOfxJi90x0VbX7JOemyhh0sK4La5MpgwsaR1q7KeuwhcJu1UxNauOLC

