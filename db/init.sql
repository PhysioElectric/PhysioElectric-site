-- =============================================================
--  PhysioElectric - Bilingual Portfolio & Blog
--  MySQL 8 Schema + Seed Data (FA/EN)
--  Charset: utf8mb4 / Collation: utf8mb4_unicode_ci
-- =============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS `physioelectric`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `physioelectric`;

-- -------------------------------------------------------------
-- 1. Users (admin panel)
-- -------------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id`            INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  `name`          VARCHAR(120)     NOT NULL,
  `email`         VARCHAR(190)     NOT NULL,
  `password_hash` VARCHAR(255)     NOT NULL,
  `is_active`     TINYINT(1)       NOT NULL DEFAULT 1,
  `last_login_at` DATETIME         NULL DEFAULT NULL,
  `created_at`    DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- E-mail lookups are case-insensitive; the app lower-cases on write and on
-- read so the UNIQUE index cannot be bypassed with Admin@ vs admin@.

-- -------------------------------------------------------------
-- 2. Project Categories (3 fixed + extensible)
-- -------------------------------------------------------------
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug`           VARCHAR(190) NOT NULL,
  `name_fa`        VARCHAR(190) NOT NULL,
  `name_en`        VARCHAR(190) NOT NULL,
  `description_fa` TEXT         NULL,
  `description_en` TEXT         NULL,
  `icon`           VARCHAR(60)  NOT NULL DEFAULT 'box',
  `sort_order`     INT          NOT NULL DEFAULT 0,
  `is_active`      TINYINT(1)   NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_categories_slug` (`slug`),
  KEY `idx_categories_sort` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- 3. Projects (bilingual columns)
-- -------------------------------------------------------------
DROP TABLE IF EXISTS `projects`;
CREATE TABLE `projects` (
  `id`            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `category_id`   INT UNSIGNED  NOT NULL,
  `title_fa`      VARCHAR(255)  NOT NULL,
  `title_en`      VARCHAR(255)  NOT NULL,
  `slug_fa`       VARCHAR(255)  NOT NULL,
  `slug_en`       VARCHAR(255)  NOT NULL,
  `short_desc_fa` VARCHAR(600)  NOT NULL DEFAULT '',
  `short_desc_en` VARCHAR(600)  NOT NULL DEFAULT '',
  `content_fa`    MEDIUMTEXT    NULL,
  `content_en`    MEDIUMTEXT    NULL,
  `image`         VARCHAR(255)  NULL,
  `tech_tags`     VARCHAR(500)  NOT NULL DEFAULT '',
  `meta_title_fa` VARCHAR(255)  NULL,
  `meta_title_en` VARCHAR(255)  NULL,
  `meta_desc_fa`  VARCHAR(500)  NULL,
  `meta_desc_en`  VARCHAR(500)  NULL,
  `status`        ENUM('published','draft') NOT NULL DEFAULT 'draft',
  `sort_order`    INT           NOT NULL DEFAULT 0,
  `created_at`    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_projects_slug_fa` (`slug_fa`),
  UNIQUE KEY `uq_projects_slug_en` (`slug_en`),
  KEY `idx_projects_category` (`category_id`),
  KEY `idx_projects_status` (`status`),
  CONSTRAINT `fk_projects_category` FOREIGN KEY (`category_id`)
    REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- 4. Blog Posts (bilingual columns)
-- -------------------------------------------------------------
DROP TABLE IF EXISTS `posts`;
CREATE TABLE `posts` (
  `id`            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `title_fa`      VARCHAR(255)  NOT NULL,
  `title_en`      VARCHAR(255)  NOT NULL,
  `slug_fa`       VARCHAR(255)  NOT NULL,
  `slug_en`       VARCHAR(255)  NOT NULL,
  `excerpt_fa`    VARCHAR(600)  NOT NULL DEFAULT '',
  `excerpt_en`    VARCHAR(600)  NOT NULL DEFAULT '',
  `content_fa`    MEDIUMTEXT    NULL,
  `content_en`    MEDIUMTEXT    NULL,
  `image`         VARCHAR(255)  NULL,
  `meta_title_fa` VARCHAR(255)  NULL,
  `meta_title_en` VARCHAR(255)  NULL,
  `meta_desc_fa`  VARCHAR(500)  NULL,
  `meta_desc_en`  VARCHAR(500)  NULL,
  `status`        ENUM('published','draft') NOT NULL DEFAULT 'draft',
  `published_at`  DATETIME      NULL DEFAULT NULL,
  `created_at`    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_posts_slug_fa` (`slug_fa`),
  UNIQUE KEY `uq_posts_slug_en` (`slug_en`),
  KEY `idx_posts_status_pub` (`status`, `published_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- 5. Site Settings (key/value, bilingual keys)
-- -------------------------------------------------------------
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `skey`   VARCHAR(120) NOT NULL,
  `svalue` TEXT         NOT NULL,
  PRIMARY KEY (`skey`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- 6. Login Attempts (brute-force / rate limiting)
-- -------------------------------------------------------------
DROP TABLE IF EXISTS `login_attempts`;
CREATE TABLE `login_attempts` (
  `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ip`           VARBINARY(16)   NOT NULL,
  `identifier`   VARCHAR(190)    NULL DEFAULT NULL,
  `attempted_at` DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `success`      TINYINT(1)      NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_attempts_ip_time` (`ip`, `attempted_at`),
  KEY `idx_attempts_ident_time` (`identifier`, `attempted_at`),
  KEY `idx_attempts_time` (`attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Team members shown on the About page (managed from the admin panel).
CREATE TABLE `team_members` (
  `id`         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `name_fa`    VARCHAR(120)  NOT NULL DEFAULT '',
  `name_en`    VARCHAR(120)  NOT NULL DEFAULT '',
  `role_fa`    VARCHAR(160)  NOT NULL DEFAULT '',
  `role_en`    VARCHAR(160)  NOT NULL DEFAULT '',
  `desc_fa`    VARCHAR(600)  NOT NULL DEFAULT '',
  `desc_en`    VARCHAR(600)  NOT NULL DEFAULT '',
  `image`      VARCHAR(255)  NOT NULL DEFAULT '',
  `sort_order` INT           NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_team_sort` (`sort_order`, `id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inquiries submitted through the public contact / project-order wizard.
CREATE TABLE `messages` (
  `id`             INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `kind`           VARCHAR(20)   NOT NULL DEFAULT 'contact',
  `category`       VARCHAR(190)  NOT NULL DEFAULT '',
  `name`           VARCHAR(120)  NOT NULL,
  `company`        VARCHAR(160)  NOT NULL DEFAULT '',
  `email`          VARCHAR(190)  NOT NULL,
  `phone`          VARCHAR(40)   NOT NULL DEFAULT '',
  `contact_method` VARCHAR(20)   NOT NULL DEFAULT '',
  `contact_id`     VARCHAR(120)  NOT NULL DEFAULT '',
  `timeline`       VARCHAR(60)   NOT NULL DEFAULT '',
  `body`           TEXT          NOT NULL,
  `notes`          VARCHAR(500)  NOT NULL DEFAULT '',
  `lang`           VARCHAR(5)    NOT NULL DEFAULT 'fa',
  `attachments`    TEXT          NULL,
  `is_read`        TINYINT(1)    NOT NULL DEFAULT 0,
  `ip`             VARCHAR(45)   NOT NULL DEFAULT '',
  `created_at`     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_msg_read` (`is_read`, `created_at`),
  KEY `idx_msg_ip_time` (`ip`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================
--  SEED: Team Members
-- =============================================================
INSERT INTO `team_members`
  (`name_fa`, `name_en`, `role_fa`, `role_en`, `desc_fa`, `desc_en`, `image`, `sort_order`)
VALUES
  ('دکتر امیر حسینی', 'Dr. Amir Hosseini', 'مهندس ارشد / سیستم‌های AI', 'Lead Engineer / AI Systems',
   'طراحی معماری سیستم‌های هوشمند و نظارت بر مدل‌های پیچیده محاسباتی.', 'Architecting intelligent systems and overseeing complex computational models.',
   'https://images.unsplash.com/photo-1560250097-0b93528c311a?q=80&w=800&auto=format&fit=crop', 1),
  ('سارا رادمنش', 'Sara Radmanesh', 'آرشیتکت نرم‌افزار', 'Software Architect',
   'طراحی زیرساخت‌های مقیاس‌پذیر وب و پر کردن شکاف بین ریاضیات و کد.', 'Designing scalable web infrastructure and bridging the gap between math and code.',
   'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?q=80&w=800&auto=format&fit=crop', 2),
  ('محمدرضا افراز', 'Mohammad Reza Afraz', 'شبیه‌سازی و تحلیل', 'Simulation & Analysis',
   'تبدیل پدیده‌های فیزیکی دنیای واقعی به مدل‌های کامسول با دقت بالا.', 'Translating real-world physical phenomena into highly accurate COMSOL models.',
   'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?q=80&w=800&auto=format&fit=crop', 3),
  ('ندا وحدتی', 'Neda Vahdati', 'سیستم‌های نهفته', 'Embedded Systems',
   'توسعه سخت‌افزارهای IoT و بهینه‌سازی میکروکنترلرها برای محاسبات لبه.', 'Developing IoT hardware and optimizing microcontrollers for edge computing.',
   'https://images.unsplash.com/photo-1580489944761-15a19d654956?q=80&w=800&auto=format&fit=crop', 4);

-- =============================================================
--  SEED: Settings
-- =============================================================
INSERT INTO `settings` (`skey`, `svalue`) VALUES
('site_name',      'PhysioElectric'),
('site_tagline_fa','ایده‌های مهندسی، راه‌حل‌های هوشمند'),
('site_tagline_en','Engineering Ideas, Intelligent Solutions'),
('telegram_user',  'physioelectric'),
('contact_email',  'info@physioelectric.com'),
('contact_phone',  '+98 912 000 0000'),
('address_fa',     'تهران، ایران'),
('address_en',     'Tehran, Iran'),
('hero_badge_fa',  'استودیوی فناوری و مهندسی'),
('hero_badge_en',  'Technology & Engineering Studio'),
('hero_title_fa',  'ایده‌های مهندسی.<br>ساختن راه‌حل‌های هوشمند.'),
('hero_title_en',  'Engineering Ideas.<br>Building Intelligent Solutions.'),
('hero_subtitle_fa', 'فیزیو‌الکتریک با ترکیب مهندسی نرم‌افزار، شبیه‌سازی‌های پیشرفته، هوش مصنوعی و فناوری‌های دیجیتال، ایده‌های پیچیده را به راه‌حل‌های عملی تبدیل می‌کند.'),
('hero_subtitle_en', 'PhysioElectric combines software engineering, advanced simulations, artificial intelligence, and digital technologies to transform complex ideas into practical solutions.'),
('footer_desc_fa', 'استودیویی مهندسی-نرم‌افزاری که با ترکیب شبیه‌سازی، برنامه‌نویسی و توسعه وب، ایده‌های پیچیده را به محصولات هوشمند تبدیل می‌کند.'),
('footer_desc_en', 'An engineering-software studio that combines simulation, programming and web development to turn complex ideas into intelligent products.');

-- =============================================================
--  SEED: Categories
-- =============================================================
INSERT INTO `categories` (`slug`, `name_fa`, `name_en`, `description_fa`, `description_en`, `icon`, `sort_order`) VALUES
('simulation',
 'پروژه‌های شبیه‌سازی',
 'Simulation Projects',
 'شبیه‌سازی‌های مهندسی پیشرفته با MATLAB و COMSOL برای تحلیل دقیق سیستم‌های فیزیکی، الکترومغناطیسی و حرارتی.',
 'Advanced engineering simulations with MATLAB and COMSOL for precise analysis of physical, electromagnetic and thermal systems.',
 'waves', 1),
('programming',
 'پروژه‌های برنامه‌نویسی',
 'Programming Projects',
 'توسعه نرم‌افزارهای تخصصی با Python، C++ و OpenCV؛ از پردازش سیگنال تا بینایی ماشین.',
 'Custom software with Python, C++ and OpenCV — from signal processing to machine vision.',
 'code-2', 2),
('web-development',
 'طراحی و توسعه وب',
 'Website & Web Development',
 'طراحی و پیاده‌سازی وب‌سایت‌های مدرن، پنل‌های مدیریتی و داشبورد‌های داده با معماری تمیز و سئوی چندزبانه.',
 'Modern websites, admin panels and data dashboards with clean architecture and multilingual SEO.',
 'globe', 3);

-- =============================================================
--  SEED: Projects (6 projects, 2 per category)
-- =============================================================
INSERT INTO `projects`
(`category_id`, `title_fa`, `title_en`, `slug_fa`, `slug_en`, `short_desc_fa`, `short_desc_en`,
 `content_fa`, `content_en`, `image`, `tech_tags`, `meta_desc_fa`, `meta_desc_en`, `status`, `sort_order`) VALUES

(1,
 'شبیه‌سازی حرارتی-جریان سیال مبدل حرارتی',
 'Thermal-Fluid Simulation of a Heat Exchanger',
 'heat-exchanger-simulation', 'heat-exchanger-simulation',
 'طراحی و بهینه‌سازی مبدل حرارتی با COMSOL و تحلیل حساسیت در MATLAB برای افزایش بازده حرارتی ۱۸ درصدی.',
 'Design and optimization of a heat exchanger with COMSOL and sensitivity analysis in MATLAB, achieving an 18% thermal efficiency gain.',
 '<h2>شرح پروژه</h2><p>در این پروژه، مبدل حرارتی لوله‌در-لوله (Double-Pipe) یک واحد صنعتی با استفاده از COMSOL Multiphysics شبیه‌سازی شد. هدف اصلی، شناسایی گلوگاه‌های انتقال حرارت و افزایش بازده کل سامانه بدون افزایش حجم فیزیکی بود.</p><h2>رویکرد فنی</h2><ul><li>مدل‌سازی هندسی دقیق با شرایط مرزی واقعی دبی، دما و ضرایب انتقال حرارت</li><li>حل معادلات ناویر-استوکس و انرژی با شبکهٔ چندسطحی (Multi-level Meshing)</li><li>بهینه‌سازی پیچ‌های داخلی (Helical Coils) با Sweep Parameter در COMSOL</li><li>تحلیل حساسیت و آزمون‌هایمونته‌کارلو در MATLAB برای اعتبارسنجی خروجی‌ها</li></ul><h2>نتایج</h2><ul><li>افزایش ۱۸ درصدی بازده انتقال حرارت نسبت به طراحی اولیه</li><li>کاهش ۲۲ درصدی افت فشار در بخش ثانویه</li><li>گزارش مهندسی کامل با تصاویر میدان‌های سرعت و دما</li></ul>',
 '<h2>Overview</h2><p>In this project, a double-pipe heat exchanger of an industrial unit was simulated using COMSOL Multiphysics. The main goal was to identify heat-transfer bottlenecks and raise overall efficiency without increasing the physical footprint.</p><h2>Technical Approach</h2><ul><li>Precise geometric modeling with real boundary conditions for flow rate, temperature and heat-transfer coefficients</li><li>Solving the Navier-Stokes and energy equations with multi-level meshing</li><li>Optimizing internal helical coils via parameter sweeps in COMSOL</li><li>Sensitivity analysis and Monte-Carlo validation in MATLAB</li></ul><h2>Results</h2><ul><li>18% increase in heat-transfer efficiency over the original design</li><li>22% reduction in pressure drop on the secondary side</li><li>Complete engineering report with velocity and temperature field visualizations</li></ul>',
 NULL, 'COMSOL, MATLAB, CFD, Heat Transfer',
 'شبیه‌سازی COMSOL مبدل حرارتی با ۱۸ درصد افزایش بازده',
 'COMSOL heat exchanger simulation with 18% efficiency gain',
 'published', 1),

(1,
 'مدل‌سازی و شبیه‌سازی الکترومغناطیسی آنتن',
 'Electromagnetic Antenna Modeling & Simulation',
 'antenna-em-simulation', 'antenna-em-simulation',
 'طراحی آنتن مایکروویو پهن‌باند با COMSOL RF و MATLAB، بهینه‌سازی بازتاب و بازهٔ پهنای باند.',
 'Design of a broadband microstrip antenna with COMSOL RF and MATLAB, optimizing return loss and bandwidth.',
 '<h2>شرح پروژه</h2><p>طراحی و تحلیل یک آنتن پهن‌باند مایکروویو برای کاربرد در سامانه‌های ارتباطی مخابراتی. فرایند طراحی شامل مدل‌سازی سه‌بعدی، تحلیل فرکانسی و بهینه‌سازی پارامترهای هندسی بود.</p><h2>رویکرد فنی</h2><ul><li>حل کامل معادلات ماکسول با روش FEM در مدول RF/COMSOL</li><li>بهینه‌سازی ابعاد فید و سلات با الگوریتم ژنتیک MATLAB</li><li>تحلیل SWR، بازتاب (Return Loss) و دیاگرام تابش</li><li>مقایسه نتایج شبیه‌سازی با اندازه‌گیری‌های وکتور انالایزر</li></ul><h2>نتایج</h2><ul><li>دست‌یابی به بازهٔ پهنای باند ۳:۱ با بازتاب کمتر از ۱۰ دسی‌بل</li><li>همبستگی عالی شبیه‌سازی و اندازه‌گیری (میانگین انحراف کمتر از ۴٪)</li><li>مستندسازی کامل فرایند طراحی برای تولید</li></ul>',
 '<h2>Overview</h2><p>Design and analysis of a broadband microstrip antenna for telecommunications systems. The design workflow covered 3D modeling, frequency-domain analysis and geometric parameter optimization.</p><h2>Technical Approach</h2><ul><li>Full-wave Maxwell solution using the FEM method in the RF/COMSOL module</li><li>Feed and slot dimension optimization with a MATLAB genetic algorithm</li><li>SWR, return-loss and radiation-pattern analysis</li><li>Correlation between simulation and vector-network-analyzer measurements</li></ul><h2>Results</h2><ul><li>Achieved a 3:1 impedance bandwidth with return loss below -10 dB</li><li>Excellent simulation-measurement correlation (average deviation under 4%)</li><li>Fully documented design process ready for fabrication</li></ul>',
 NULL, 'COMSOL RF, MATLAB, FEM, Antenna Design',
 'طراحی آنتن مایکروویو پهن‌باند با COMSOL RF',
 'Broadband microstrip antenna design with COMSOL RF',
 'published', 2),

(2,
 'سیستم بینایی ماشین برای کنترل کیفیت',
 'Machine Vision System for Quality Control',
 'machine-vision-quality-control', 'machine-vision-quality-control',
 'خط تولیدی خودکار برای تشخیص عیب قطعات صنعتی با OpenCV و مدل‌های یادگیری عمیق، دقت ۹۸.۶٪.',
 'Automated production-line inspection using OpenCV and deep learning models, reaching 98.6% defect detection accuracy.',
 '<h2>شرح پروژه</h2><p>سامانه‌ای برای بازرسی بصری خودکار قطعات فلزی در خط تولید. سیستم با ترکیب پردازش تصویر کلاسیک و یادگیری عمیق، عیوب سطحی شامل خط، خراش و تغییر رنگ را در لحظه تشخیص می‌دهد.</p><h2>رویکرد فنی</h2><ul><li>موتور اصلی پردازش با Python و OpenCV (تثبیت تصویر، نورپردازی یکنواخت، پیش‌پردازش)</li><li>مدل تشخیص عیب بر پایه‌ی U-Net برای سگمنتاسیون + کلاسی‌فایر سبک برای طبقه‌بندی عیب</li><li>ساخت پipeline موازی با Process Pool برای حفظ فرکانس اسکن خط تولید</li><li>رابط کاربری اپراتور با گزارش‌گیری لحظه‌ای و ذخیره نمونه‌های مشکوک</li></ul><h2>نتایج</h2><ul><li>دقت ۹۸.۶٪ و نرخ مثبت-ناقص ۰.۳٪ روی ۲۴٬۰۰ تصویر واقعی</li><li>سرعت پردازش ۴۰ تصویر بر ثانیه روی سخت‌افزار Edge</li><li>کاهش ۶۵ درصدی ضایعات ناشی از عیوب غیرکشف‌شده</li></ul>',
 '<h2>Overview</h2><p>A system for automated visual inspection of metal parts on a production line. Combining classical image processing with deep learning, the system detects surface defects — scratches, gouges, discoloration — in real time.</p><h2>Technical Approach</h2><ul><li>Core processing engine in Python and OpenCV (alignment, uniform illumination, preprocessing)</li><li>U-Net based segmentation model plus a lightweight classifier for defect typing</li><li>Parallel processing pipeline to keep up with line scan frequency</li><li>Operator UI with live reporting and archiving of suspicious samples</li></ul><h2>Results</h2><ul><li>98.6% accuracy and 0.3% false-positive rate on 24,000 real images</li><li>40 images per second processing speed on Edge hardware</li><li>65% reduction in scrap caused by undetected defects</li></ul>',
 NULL, 'Python, OpenCV, Deep Learning, U-Net',
 'کنترل کیفیت خودکار با بینایی ماشین، دقت ۹۸.۶٪',
 'Automated quality control with machine vision, 98.6% accuracy',
 'published', 1),

(2,
 'سامانه پردازش سیگنال ECG',
 'ECG Signal Processing Pipeline',
 'ecg-signal-processing', 'ecg-signal-processing',
 'پایپ‌لاین تمیزکاری و تحلیل سیگنال قلبی با Python و MATLAB؛ تشخیص آریتمی با حساسیت ۹۷٪.',
 'An ECG cleaning and analysis pipeline with Python and MATLAB; arrhythmia detection at 97% sensitivity.',
 '<h2>شرح پروژه</h2><p>ساخت یک خط لوله کامل پردازش سیگنال ECG: از حذف نویز و خط مبنا تا شناسایی ضربان‌ها و طبقه‌باری آریتمی‌های شایع (PVC، Premature Atrial و تاکی‌کاردی).</p><h2>رویکرد فنی</h2><ul><li>فیلترهای دیجیتال با SciPy: حذف خط مبنا (IIR)، فیلتر نوارگذر QRS، حذف نویز برق شهر</li><li>الگوریتم تشخیص QRS با روش ترکیبی wavelet + آستانه‌گذاری تطبیقی</li><li>مطالعهٔ زمانی-فرکانسی با تبدیل موجک ممتد برای شاخص‌های آریتمی</li><li>مطابقت با استاندارد MIT-BIH و گزارش‌دهی در قالب MATLAB Report Generator</li></ul><h2>نتایج</h2><ul><li>حساسیت ۹۷٪ و اختصاصیت ۹۶.۲٪ روی دادهٔ MIT-BIH Arrhythmia Database</li><li>تأخیر پردازش کمتر از ۲۰۰ میلی‌ثانیه به ازای هر ثانیه سیگنال</li><li>کتابخانهٔ باز برای استفاده در پروژه‌های پژوهشی</li></ul>',
 '<h2>Overview</h2><p>A complete ECG signal-processing pipeline: from baseline-wander removal and noise filtering to beat detection and classification of common arrhythmias (PVC, premature atrial, tachycardia).</h2><h2>Technical Approach</h2><ul><li>Digital filters with SciPy: baseline removal (IIR), QRS band-pass, power-line noise rejection</li><li>QRS detection with a combined wavelet + adaptive thresholding algorithm</li><li>Time-frequency study via continuous wavelet transform for arrhythmia indices</li><li>Compliance with the MIT-BIH standard and automated MATLAB reports</li></ul><h2>Results</h2><ul><li>97% sensitivity and 96.2% specificity on the MIT-BIH Arrhythmia Database</li><li>Processing latency under 200 ms per second of signal</li><li>An open library released for research projects</li></ul>',
 NULL, 'Python, SciPy, MATLAB, Wavelets, DSP',
 'پردازش سیگنال ECG با تشخیص آریتمی ۹۷٪',
 'ECG processing with 97% arrhythmia detection sensitivity',
 'published', 2),

(3,
 'داشبورد پایش داده‌های IoT',
 'IoT Data Monitoring Dashboard',
 'iot-monitoring-dashboard', 'iot-monitoring-dashboard',
 'پنل پایش لحظه‌ای حسگرهای صنعتی با PHP، MySQL و نوتیفیکاسیون هوشمند؛ نمایش ۵۰٬۰۰ نقطه داده در ثانیه.',
 'Real-time industrial sensor monitoring panel with PHP, MySQL and smart notifications; rendering 50,000 data points per second.',
 '<h2>شرح پروژه</h2><p>طراحی و توسعه یک داشبورد پایش برای شبکهٔ حسگرهای یک پلاتفرم صنعتی. داده‌ها از طریق API دریافت و در MySQL ذخیره می‌شوند و داشبورد، نمودارهای زنده، هشدارهای آستانه‌ای و گزارش‌های تاریخی ارائه می‌دهد.</p><h2>رویکرد فنی</h2><ul><li>بک‌اند با PHP و معماری Front-Controller + PDO (prepared statements)</li><li>معماری داده: جدول‌های timeseries با پارتیشن‌بندی ماهانه و آرشیو خودکار</li><li>نمایش زنده با polling سبک + کش Redis برای شاخص‌های لحظه‌ای</li><li>سیستم هشدار: آستانه‌های قابل تنظیم، اعلان تلگرامی و ایمیلی</li></ul><h2>نتایج</h2><ul><li>پشتیبانی از ۱۲۰ حسگر هم‌زمان با دقت زمان‌بندی ۱۰۰ میلی‌ثانیه</li><li>بازدهی ۴۰ درصدی کوئری‌ها پس از بهینه‌سازی ایندکس‌ها و کش</li><li>کاهش ۵۰ درصدی زمان پاسخ‌گویی به حوادث به‌مراتب سریع‌تر از فرایند دستی</li></ul>',
 '<h2>Overview</h2><p>Design and development of a monitoring dashboard for an industrial sensor network. Data is ingested via API, stored in MySQL, and the dashboard renders live charts, threshold alerts and historical reports.</p><h2>Technical Approach</h2><ul><li>PHP backend with a front-controller architecture and PDO (prepared statements)</li><li>Time-series data design: monthly partitioned tables with automatic archiving</li><li>Lightweight live polling + Redis caching for real-time metrics</li><li>Alerting engine: configurable thresholds, Telegram and email notifications</li></ul><h2>Results</h2><ul><li>120 concurrent sensors with 100 ms timing accuracy</li><li>40% query performance gain after index and cache optimization</li><li>Incident response time cut in half versus the previous manual workflow</li></ul>',
 NULL, 'PHP, MySQL, Redis, REST API, IoT',
 'داشبورد پایش IoT با PHP و MySQL و هشدار هوشمند',
 'IoT monitoring dashboard with PHP, MySQL and smart alerts',
 'published', 1),

(3,
 'وب‌سایت شرکتی چندزبانه PhysioElectric',
 'PhysioElectric Multilingual Corporate Website',
 'physioelectric-website', 'physioelectric-website',
 'طراحی و توسعه وب‌سایت رسمی فیزیو‌الکتریک؛ دو‌زبانه، با سئوی پیشرفته و پنل مدیریت کامل.',
 'Design and development of the official PhysioElectric website; bilingual, with advanced SEO and a full management panel.',
 '<h2>شرح پروژه</h2><p>این وب‌سایت خودش بهترین نمونهٔ کار ماست! سایت رسمی فیزیو‌الکتریک با معماری PHP/MySQL، رابط‌های دو‌زبانه (فارسی RTL / انگلیسی LTR) و سئوی عمیق پیاده‌سازی شده است.</p><h2>قابلیت‌ها</h2><ul><li>دو‌زبانه‌سازی کامل با پیشوند URL (/fa و /en) و تگ‌های Hreflang</li><li>نمایش پویا Meta، Open Graph و Schema Markup (JSON-LD) از دیتابیس</li><li>پنل مدیریت امن: احراز هویت، CSRF، Rate-Limiting و آپلود امن تصویر</li><li>طراحی واکنش‌گرا با Tailwind CSS و انیمیشن‌های سکرول</li></ul><h2>نتایج</h2><ul><li>امکان مدیریت کامل محتوا بدون دخالت توسعه‌دهنده</li><li>ساختار URL تمیز و مناسب برای موتورهای جستجو در هر دو زبان</li><li>مبنایی برای رشد آفرینش‌های آینده (بلاگ، پروژه‌ها، خدمات)</li></ul>',
 '<h2>Overview</h2><p>This very website is our best case study! The official PhysioElectric site was built on a PHP/MySQL stack with bilingual interfaces (Persian RTL / English LTR) and deep SEO integration.</p><h2>Features</h2><ul><li>Full bilingual support with URL prefixes (/fa and /en) and Hreflang tags</li><li>Dynamic Meta, Open Graph and Schema Markup (JSON-LD) rendered from the database</li><li>Secure admin panel: authentication, CSRF, rate-limiting and safe image uploads</li><li>Responsive design with Tailwind CSS and scroll animations</li></ul><h2>Results</h2><ul><li>Complete content management without developer involvement</li><li>Clean URL structure, optimized for search engines in both languages</li><li>A solid foundation for future growth (blog, projects, services)</li></ul>',
 NULL, 'PHP, MySQL, Tailwind CSS, SEO, Docker',
 'وب‌سایت رسمی فیزیو‌الکتریک؛ دو‌زبانه با سئوی عمیق',
 'The official PhysioElectric website; bilingual with deep SEO',
 'published', 2);

-- =============================================================
--  SEED: Blog Posts (4 posts)
-- =============================================================
INSERT INTO `posts`
(`title_fa`, `title_en`, `slug_fa`, `slug_en`, `excerpt_fa`, `excerpt_en`,
 `content_fa`, `content_en`, `image`, `meta_desc_fa`, `meta_desc_en`, `status`, `published_at`) VALUES

('چرا مهندسی مبتنی بر شبیه‌سازی؟',
 'Why Simulation-Driven Engineering?',
 'why-simulation-driven-engineering', 'why-simulation-driven-engineering',
 'شبیه‌سازی، آزمایشگاه بدون هزینهٔ خطا است. در این مقاله می‌خوانید چرا تیم‌های مهندسی پیشرو، تصمیماتشان را بر پایهٔ مدل دیجیتال می‌سازند.',
 'Simulation is a laboratory with no cost of error. Read why leading engineering teams base their decisions on digital models.',
 '<p>در مهندسی کلاسیک، فرایند «طراحی → ساخت → آزمایش → اصلاح» می‌تواند ماه‌ها زمان و هزینه‌های سنگینی داشته باشد. هر تکرار فیزیکی یعنی مواد، دستمزد و انتظار. شبیه‌سازی، همین چرخه را به یک فرایند دیجیتال با هزینه‌ای ناچیز تبدیل می‌کند.</p><h2>مدل دیجیتال، جیم‌توینِ ارزانِ واقعیت</h2><p>قبل از اینکه اولین پیچ را بکشید، می‌توانید رفتار سیستم را در دسیل‌ها، دماهای مختلف و شرایط مرزی حدسی بسنجید. این یعنی:</p><ul><li>شناسایی مشکلات <strong>قبل</strong> از ورود به مرحلهٔ ساخت</li><li>بهینه‌سازی پارامترها بدون محدودیت فیزیکی آزمایشگاه</li><li>مستندسازی تصمیمات مهندسی با شواهد عددی</li></ul><h2>کجا باید شبیه‌سازی کرد؟</h2><p>وقتی سه شرط هم‌زمان برقرار باشد: ۱) فیزیک سیستم قابل مدل‌سازی است، ۲) هزینهٔ هر خطای فیزیکی بالاست، ۳) زمان برای آزمون‌های واقعی کافی نیست. در چنین شرایطی، حتی یک مدل ساده می‌تواند میلیون‌ها تومان صرفه‌جویی کند.</p><blockquote>«بهترین مدل، مدلی است که تصمیم درست را سریع‌تر از رقیبانش به شما می‌دهد — نه مدل دقیق‌ترِ همیشه.»</blockquote><h2>نقش مهندس در این فرایند</h2><p>شبیه‌سازی جایگزین سواد مهندسی نمی‌شود؛ ابزارِ تقویت آن است. تفسیر درست نتایج، انتخاب شرایط مرزی واقعی و اعتبارسنجی با دادهٔ اندازه‌گیری‌شده، کار مهندسی است که نرم‌افزارها انجام نمی‌دهند.</p>',
 '<p>In classical engineering, the loop "design → build → test → fix" can cost months and serious budgets. Every physical iteration means materials, labor and waiting. Simulation turns this loop into a digital process at near-zero cost.</p><h2>A digital twin, cheaply</h2><p>Before the first bolt is tightened, you can study the system under different loads, temperatures and boundary conditions. This means:</p><ul><li>Finding problems <strong>before</strong> fabrication</li><li>Optimizing parameters without lab limitations</li><li>Documenting engineering decisions with numerical evidence</li></ul><h2>When should you simulate?</h2><p>When three conditions hold at once: 1) the physics are modelable, 2) the cost of a physical error is high, 3) there is no time for real-world trials. In such cases, even a simple model can save fortunes.</p><blockquote>"The best model is the one that gives you the right decision faster than its rivals — not the always-more-accurate one."</blockquote><h2>The engineer''s role</h2><p>Simulation does not replace engineering judgment; it amplifies it. Interpreting results, choosing realistic boundary conditions and validating against measured data remain purely human work.</p>',
 NULL,
 'مزایای مهندسی مبتنی بر شبیه‌سازی با MATLAB و COMSOL',
 'Why leading teams use simulation with MATLAB and COMSOL',
 'published', '2026-05-14 09:00:00'),

('COMSOL یا MATLAB؟ راهنمای انتخاب ابزار',
 'COMSOL or MATLAB? Choosing the Right Tool',
 'comsol-vs-matlab-guide', 'comsol-vs-matlab-guide',
 'هر ابزار برای کار خودش ساخته شده. در این مقاله مرز کاربرد COMSOL و MATLAB را با مثال‌های واقعی مشخص می‌کنیم.',
 'Every tool is built for its own job. This article draws the boundary between COMSOL and MATLAB with real examples.',
 '<p>بسیاری از مهندسی‌ها COMSOL و MATLAB را رقیب می‌دانند، در حالی که این دو ابزار بیشتر مکمل یکدیگرند تا جایگزین.</p><h2>COMSOL برای «کجا»، MATLAB برای «چه چیزی»</h2><p>وقتی مسئله شما <strong>مکعب‌های فیزیک</strong> است — میدان‌های توزیع‌شده در فضا (الکترومغناطیس، حرارت، جریان سیال) — COMSOL با روش FEM بهترین انتخاب است. اما وقتی مسئله‌تان <strong>داده‌ها و الگوریتم‌ها</strong> هستند — پردازش سیگنال، بهینه‌سازی، آمار و مدل‌سازی عددی یک‌بعدی — MATLAB قدرتمندتر است.</p><h2>مثال واقعی: مبدل حرارتی</h2><ul><li><strong>COMSOL:</strong> حل میدان دما و سرعت در هندسهٔ سه‌بعدی واقعی</li><li><strong>MATLAB:</strong> بهینه‌سازی ابعاد با الگوریتم ژنتیک و تحلیل حساسیت خروجی‌ها</li></ul><p>در پروژهٔ مبدل حرارتی ما، COMSOL نقش «آزمایشگاه» و MATLAB نقش «مغز بهینه‌سازی» را بازی کرد. داده‌ها از COMSOL با MATLAB LiveLink به‌صورت خودکار وارد MATLAB می‌شدند.</p><h2>قانون عملی تیم ما</h2><blockquote>اگر مسئلهٔ شما هندسه دارد و فیزیک توزیع‌شده، از COMSOL شروع کنید. اگر مسئلهٔ شما ماتریس است و داده، MATLAB. اگر هر دو دارید — هر دو را با LiveLink وصل کنید.</blockquote><p>در نهایت، انتخاب ابزار به مسئله برمی‌گردد نه به علاقهٔ شخصی. در <a href="/fa/contact">تیم فیزیو‌الکتریک</a> می‌توانیم برای مسئله‌تان بهترین ترکیب را پیشنهاد دهیم.</p>',
 '<p>Many engineers treat COMSOL and MATLAB as rivals, but in practice they are mostly complements, not substitutes.</p><h2>COMSOL for "where", MATLAB for "what"</h2><p>When your problem involves <strong>physical fields</strong> — spatially distributed fields (electromagnetics, heat, fluid flow) — COMSOL with FEM is the best choice. When your problem is <strong>data and algorithms</strong> — signal processing, optimization, statistics, 1-D numerical modeling — MATLAB is stronger.</p><h2>A real example: the heat exchanger</h2><ul><li><strong>COMSOL:</strong> solving temperature and velocity fields in real 3-D geometry</li><li><strong>MATLAB:</strong> dimension optimization with a genetic algorithm and sensitivity analysis</li></ul><p>In our heat-exchanger project, COMSOL played the "laboratory" and MATLAB the "optimization brain". Data flowed automatically from COMSOL to MATLAB through LiveLink.</p><h2>Our team''s rule of thumb</h2><blockquote>If your problem has geometry and distributed physics, start with COMSOL. If it has matrices and data, use MATLAB. If it has both — connect them with LiveLink.</blockquote><p>In the end, tool choice follows the problem, not personal taste. The <a href="/en/contact">PhysioElectric team</a> can recommend the best combination for your problem.</p>',
 NULL,
 'مقایسه کاربردی COMSOL و MATLAB با مثال واقعی',
 'A practical COMSOL vs MATLAB comparison with a real example',
 'published', '2026-06-02 10:30:00'),

('پردازش سیگنال با Python: از صفر تا عمل',
 'Signal Processing with Python: From Zero to Practice',
 'signal-processing-python', 'signal-processing-python',
 'نحوهٔ تمیز کردن، تحلیل و نمایش سیگنال‌های واقعی با SciPy و NumPy — همراه با کد کامل یک فیلتر QRS.',
 'How to clean, analyze and visualize real signals with SciPy and NumPy — including a complete QRS filter implementation.',
 '<p>پشت هر داشبورد پزشکی، سامانهٔ پایش صنعتی یا نرم‌افزار صوتی، یک خط لولهٔ پردازش سیگنال وجود دارد. Python با کتابخانه‌های NumPy و SciPy به انتخاب پیش‌فرض این کار تبدیل شده است.</p><h2>قدم اول: درک دامنهٔ زمان و فرکانس</h2><p>هر سیگنال اندازه‌گیری‌شده سه چیز دارد: نویز، خط مبنا و سیگنال واقعی. قبل از هر تحلیل، باید بدانید فرکانس‌های مهمتان کجا هستند. تبدیل فوریه سریع (FFT) اولین ابزار شماست:</p><pre><code>import numpy as np
from scipy import signal

fs = 256          # نمونه‌برداری: 256 Hz
freqs = np.fft.rfftfreq(len(ecg), d=1/fs)
spectrum = np.abs(np.fft.rfft(ecg))</code></pre><h2>فیلتر کردن: نوارگذر برای QRS</h2><p>کامپلکس QRS در سیگنال ECG تقریباً بین ۵ تا ۱۵ هرتز قرار دارد. یک فیلتر باترورث نوارگذر این محدوده را حفظ و بقیه را حذف می‌کند:</p><pre><code>b, a = signal.butter(4, [5, 15], btype="bandpass",
                     fs=fs)
ecg_clean = signal.filtfilt(b, a, ecg)</code></pre><h2>نکتهٔ طلایی: filtfilt</h2><p>از <code>signal.filtfilt</code> به‌جای <code>lfilter</code> استفاده کنید تا فاز سیگنال حفظ شود. در تشخیص ضربان قلب، حتی تأخیر فازِ چند نمونه می‌تواند زمان‌بندی تشخیص را خراب کند.</p><h2>جمع‌بندی</h2><ul><li>همیشه اول با FFT فرکانس‌های مسئله را بشناسید</li><li>ترکیب فیلترهای ساده معمولاً بهتر از یک فیلتر پیچیده است</li><li>برای اعتبارسنجی، روی دادهٔ استاندارد (مثل MIT-BIH) امتحان کنید</li></ul>',
 '<p>Behind every medical dashboard, industrial monitoring system or audio application lies a signal-processing pipeline. Python with NumPy and SciPy has become the default choice for this job.</p><h2>Step one: the time and frequency domains</h2><p>Every measured signal has three components: noise, baseline wander and the real signal. Before any analysis, you must know where your important frequencies live. The FFT is your first tool:</p><pre><code>import numpy as np
from scipy import signal

fs = 256          # sampling rate: 256 Hz
freqs = np.fft.rfftfreq(len(ecg), d=1/fs)
spectrum = np.abs(np.fft.rfft(ecg))</code></pre><h2>Filtering: a band-pass for QRS</h2><p>The QRS complex in an ECG sits roughly between 5 and 15 Hz. A Butterworth band-pass keeps this band and removes the rest:</p><pre><code>b, a = signal.butter(4, [5, 15], btype="bandpass",
                     fs=fs)
ecg_clean = signal.filtfilt(b, a, ecg)</code></pre><h2>Golden tip: filtfilt</h2><p>Use <code>signal.filtfilt</code> instead of <code>lfilter</code> to preserve signal phase. In heartbeat detection, even a few samples of phase delay can break the timing.</p><h2>Wrap-up</h2><ul><li>Always start by studying the problem frequencies with the FFT</li><li>Combining simple filters usually beats one complex filter</li><li>Validate against a standard dataset (e.g. MIT-BIH)</li></ul>',
 NULL,
 'آموزش عملی پردازش سیگنال با Python و SciPy',
 'A practical guide to signal processing with Python and SciPy',
 'published', '2026-06-25 08:15:00'),

('سئوی چندزبانه: Hreflang و معماری دو‌زبانه',
 'Multilingual SEO: Hreflang and Bilingual Architecture',
 'multilingual-seo-hreflang', 'multilingual-seo-hreflang',
 'چطور سایت دو‌زبانه‌تان را طوری بسازید که گوگل نسخهٔ فارسی و انگلیسی را به‌درستی درک کند؟ با مثال عملی تگ‌های hreflang.',
 'How to build your bilingual site so Google correctly understands the Persian and English versions? With a practical hreflang example.',
 '<p>سایت دو‌زبانه بدون پیاده‌سازی صحیح سئو، مثل این است که دو نسخه از یک کتاب را به دو قفسهٔ مختلف بگذارید و به کتابدار بگویید «خودش بفهمد».</p><h2>ساختار URL: پیشوند زبان</h2><p>تمیزترین الگو، پیشوند زبان در URL است:</p><ul><li><code>/fa/blog/my-post</code></li><li><code>/en/blog/my-post</code></li></ul><p>به این ترتیب، هر صفحه یک هویت مستقل دارد و می‌تواند Title، Description و محتوا را کاملاً برای آن زبان بهینه کند — نه ترجمهٔ خودکار، بلکه بازنویسی.</p><h2>Hreflang: نقشهٔ راه برای گوگل</h2><p>در هر صفحه، به گوگل بگویید نسخه‌های زبان‌های دیگر کجاست:</p><pre><code>&lt;link rel="alternate" hreflang="fa"
      href="https://site.com/fa/blog/my-post" /&gt;
&lt;link rel="alternate" hreflang="en"
      href="https://site.com/en/blog/my-post" /&gt;
&lt;link rel="alternate" hreflang="x-default"
      href="https://site.com/fa/blog/my-post" /&gt;</code></pre><p>نکتهٔ کلیدی: hreflang <strong>دوسویه</strong> است. اگر صفحهٔ فارسی به انگلیسی اشاره کند، انگلیسی هم باید به فارسی اشاره کند.</p><h2>Canonical: قهرمان ضد تکراری</h2><p>هر صفحه یک canonical مطلق به خودش داشته باشد تا نسخه‌های با پارامترهای مختلف (tracking، نسخه‌های قدیمی) اعتبار را به یک URL متمرکز کنند.</p><h2>چک‌لیست نهایی</h2><ul><li>پیشوند زبان + redirect از ریشه (نمونه: / به /fa)</li><li>Hreflang دوسویه با x-default</li><li>Canonical مطلق در هر صفحه</li><li>Open Graph با og:locale برای اشتراک‌گذاری درست در هر زبان</li><li>JSON-LD (Schema) بر اساس زبان فعال</li></ul>',
 '<p>A bilingual site without proper SEO is like putting two copies of the same book on different shelves and telling the librarian "figure it out".</p><h2>URL structure: language prefix</h2><p>The cleanest pattern is a language prefix in the URL:</p><ul><li><code>/fa/blog/my-post</code></li><li><code>/en/blog/my-post</code></li></ul><p>Each page then has its own identity and can be fully optimized for that language — not auto-translated, but rewritten.</p><h2>Hreflang: the roadmap for Google</h2><p>On every page, tell Google where the other language versions live:</p><pre><code>&lt;link rel="alternate" hreflang="fa"
      href="https://site.com/fa/blog/my-post" /&gt;
&lt;link rel="alternate" hreflang="en"
      href="https://site.com/en/blog/my-post" /&gt;
&lt;link rel="alternate" hreflang="x-default"
      href="https://site.com/fa/blog/my-post" /&gt;</code></p><p>Key point: hreflang is <strong>bidirectional</strong>. If the Persian page references the English one, the English page must reference the Persian one.</p><h2>Canonical: the anti-duplicate champion</h2><p>Each page should declare an absolute canonical pointing to itself, so variants with tracking parameters or legacy URLs consolidate their authority.</p><h2>Final checklist</h2><ul><li>Language prefix + redirect from the root (e.g. / to /fa)</li><li>Bidirectional hreflang with x-default</li><li>Absolute canonical on every page</li><li>Open Graph with og:locale for correct sharing per language</li><li>Schema (JSON-LD) generated for the active language</li></ul>',
 NULL,
 'راهنمای سئوی چندزبانه با Hreflang و Canonical',
 'A multilingual SEO guide with Hreflang and Canonical',
 'published', '2026-07-18 11:45:00');

-- -------------------------------------------------------------
-- (Admin user is created by app/setup/create_admin.php at
--  container start, using the ADMIN_EMAIL / ADMIN_PASSWORD env
--  variables — see docker-compose.yml)
-- -------------------------------------------------------------

SET FOREIGN_KEY_CHECKS = 1;
