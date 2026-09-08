<?php
declare(strict_types=1);

/**
 * Team Members Master Data Store
 * All 4 core profiles with dynamic bilingual fields, skills, and portfolio data.
 */
return [
    'mehrab-mahmoudi' => [
        'num' => '01',
        'slug' => 'mehrab-mahmoudi',
        'avatar_type' => 'ai_security',
        'name_fa' => 'محمد محراب محمودی',
        'name_en' => 'Mohammad Mehrab Mahmoudi',
        'role_fa' => 'مدیر ارشد تیم، معمار امنیت و سیستم‌های هوش مصنوعی',
        'role_en' => 'Team Lead & Systems Architect / Security & AI',
        'short_desc_fa' => 'راهبری معماری سیستم‌های توزیع‌شده، انتخاب زیرساخت‌های اجرایی، ارزیابی امنیت شبکه و نظارت بر توسعه سیستم‌های هوش مصنوعی و اتوماسیون سازمانی.',
        'short_desc_en' => 'Leading distributed systems architecture, tech-stack orchestration, deployment infrastructure security, and the development of intelligent AI automation workflows.',
        'bio_fa' => 'محمد محراب با تمرکز بر پیوند میان زیرساخت‌های امن، اتوماسیون داده‌محور و مدل‌های هوش مصنوعی، وظیفه انتخاب استک‌های تکنولوژی، نظارت بر گیت‌هاب و مدیریت چرخه‌های استقرار پروژه‌های فیزیوالکتریک را بر عهده دارد.',
        'bio_en' => 'Focused on the intersection of secure infrastructure, data-driven automation, and artificial intelligence, Mehrab oversees tech stacks, GitHub governance, and end-to-end project deployment pipelines.',
        'tags' => ['AI Systems', 'DevSecOps', 'Cloud Architecture', 'Python', 'Automation'],
        'skills' => [
            ['name' => 'AI & Neural Networks', 'level' => 92],
            ['name' => 'Systems Security & DevSecOps', 'level' => 95],
            ['name' => 'Python & Automation Agents', 'level' => 90],
            ['name' => 'Git Architecture & CI/CD', 'level' => 96],
            ['name' => 'Cloud & Linux Environments', 'level' => 88],
        ],
        'expertise_fa' => ['معماری نرم‌افزار سازمانی', 'مدیریت و استقرار گیت‌هاب', 'عوامل اتوماسیون هوش مصنوعی', 'امنیت سایبری و تست نفوذ وب'],
        'expertise_en' => ['Enterprise Software Architecture', 'GitHub & DevOps Governance', 'AI Automation Agents', 'Cybersecurity & Web Hardening'],
        'projects' => [
            ['title_fa' => 'سامانه اتوماسیون و مانیتورینگ امنیتی', 'title_en' => 'Security Hardening & Automation Core', 'desc_fa' => 'پیاده‌سازی پلتفرم تست آسیب‌پذیری و بازرسی خودکار کدهای زیرساختی.', 'desc_en' => 'Automated vulnerability scanning and deployment pipeline orchestrator.', 'tech' => 'Python, Docker, OWASP'],
            ['title_fa' => 'موتور مدیریت ایجنت‌های هوشمند', 'title_en' => 'Enterprise AI Agent Workflow', 'desc_fa' => 'طراحی سیستم تعامل هوشمند با داده‌های سازمانی.', 'desc_en' => 'Multi-agent orchestration framework for processing company documents.', 'tech' => 'PyTorch, FastApi, Redis']
        ],
        'experience' => [
            ['role_fa' => 'مدیر تیم و معمار سیستم', 'role_en' => 'Team Lead & Systems Architect', 'org' => 'PhysioElectric', 'period' => '2024 - Present', 'desc_fa' => 'هدایت پروژه‌ها و مهندسی زیرساخت سیستم‌ها.', 'desc_en' => 'Directing system architecture and technology roadmaps.'],
        ],
        'education' => [
            ['degree_fa' => 'کارشناسی مهندسی', 'degree_en' => 'B.Sc. in Engineering', 'school' => 'Iran University of Science & Technology', 'period' => '2023 - 2027']
        ],
        'resume_url' => '/uploads/resumes/mehrab-mahmoudi-resume.pdf',
        'socials' => [
            'github' => 'https://github.com',
            'linkedin' => 'https://linkedin.com',
            'telegram' => 'https://t.me',
            'email' => 'mailto:mehrab@physioelectric.com'
        ]
    ],

    'mohammadreza-afraz' => [
        'num' => '02',
        'slug' => 'mohammadreza-afraz',
        'avatar_type' => 'vision_multiphysics',
        'name_fa' => 'محمدرضا افراز',
        'name_en' => 'Mohammad Reza Afraz',
        'role_fa' => 'مدیر فرانت‌اند و شبیه‌سازی / بینایی ماشین و پردازش تصویر',
        'role_en' => 'Lead Front-End & Simulation / Computer Vision',
        'short_desc_fa' => 'توسعه فرانت‌اند تعاملی و اتصال APIها، پردازش تصویر بلادرنگ با OpenCV، الگوریتم‌های هوش مصنوعی و شبیه‌سازی‌های پیشرفته در متلب و کامسول.',
        'short_desc_en' => 'Front-end system orchestration, real-time computer vision with OpenCV, machine learning models, and high-precision multiphysics modeling in MATLAB & COMSOL.',
        'bio_fa' => 'محمدرضا پیونددهنده دقت محاسبات ریاضی و مدل‌سازی فیزیکی به دنیای کدنویسی نرم‌افزار است. تخصص او در شبیه‌سازی‌های المان محدود (FEM)، مدل‌سازی سیستم‌های کنترل، بینایی ماشین سه‌بعدی و پیاده‌سازی رابط‌های کاربری مدرن با بالاترین کارایی است.',
        'bio_en' => 'Mohammad Reza bridges mathematical rigor and physical simulations with high-performance software engineering. His focus spans finite element analysis (COMSOL), control systems, 3D computer vision, and reactive UI architecture.',
        'tags' => ['Computer Vision', 'COMSOL Multiphysics', 'MATLAB', 'OpenCV', 'Front-End UI'],
        'skills' => [
            ['name' => 'Computer Vision & OpenCV', 'level' => 96],
            ['name' => 'COMSOL Multiphysics & FEA', 'level' => 94],
            ['name' => 'MATLAB & Scientific Computing', 'level' => 95],
            ['name' => 'Modern Front-End (JS / CSS / UI)', 'level' => 92],
            ['name' => 'C++ Algorithms & Optimization', 'level' => 88],
        ],
        'expertise_fa' => ['ردیابی سه‌بعدی و بینایی ماشین', 'شبیه‌سازی الکترومغناطیسی و حرارتی', 'طراحی رابط‌های کاربری تعاملی وب', 'محاسبات عددی و بهینه‌سازی الگوریتم‌ها'],
        'expertise_en' => ['3D Vision Tracking & Detection', 'Electromagnetic & Thermal FEA', 'Interactive Web Architecture', 'Numerical Modeling & MATLAB Optimization'],
        'projects' => [
            ['title_fa' => 'سیستم ردیابی بلادرنگ سه‌بعدی اهداف', 'title_en' => 'Real-time 3D Object Tracking Engine', 'desc_fa' => 'توسعه الگوریتم OpenCV برای تخمین موقعیت فضایی و ارسال با پروتکل UDP.', 'desc_en' => 'High-frequency 3D coordinates detection via OpenCV and UDP telemetry.', 'tech' => 'Python, OpenCV, UDP'],
            ['title_fa' => 'شبیه‌سازی مغناطیسی معلق‌سازی در کامسول', 'title_en' => 'COMSOL Magnetic Levitation Model', 'desc_fa' => 'مدل‌سازی دینامیکی دوقطبی‌های مغناطیسی با Moving Mesh.', 'desc_en' => 'Dynamic FEM multiphysics model coupling magnetic fields with kinematics.', 'tech' => 'COMSOL, ODEs, PDEs']
        ],
        'experience' => [
            ['role_fa' => 'مدیر فرانت‌اند و شبیه‌سازی', 'role_en' => 'Lead Front-End & Simulation', 'org' => 'PhysioElectric', 'period' => '2024 - Present', 'desc_fa' => 'طراحی سیستم فرانت‌اند و مدیریت محاسبات شبیه‌سازی.', 'desc_en' => 'Directing front-end architecture and scientific simulation workflows.'],
            ['role_fa' => 'مدرس مبانی برنامه‌نویسی C++', 'role_en' => 'C++ Programming Fundamentals Instructor', 'org' => 'University Academic Forum', 'period' => '2025 - 2026', 'desc_fa' => 'آموزش ساختارهای داده پیشرفته، حافظه پویا و اشاره‌گرها.', 'desc_en' => 'Teaching algorithmic design, memory management, and pointers.']
        ],
        'education' => [
            ['degree_fa' => 'کارشناسی مهندسی برق', 'degree_en' => 'B.Sc. in Electrical Engineering', 'school' => 'دانشگاه علم و صنعت ایران (IUST)', 'period' => '2023 - 2027']
        ],
        'resume_url' => '/uploads/resumes/mohammadreza-afraz-resume.pdf',
        'socials' => [
            'github' => 'https://github.com',
            'linkedin' => 'https://linkedin.com',
            'telegram' => 'https://t.me',
            'email' => 'mailto:afraz@physioelectric.com'
        ]
    ],

    'parsa-ahadi' => [
        'num' => '03',
        'slug' => 'parsa-ahadi',
        'avatar_type' => 'backend_iot',
        'name_fa' => 'پارسا احدی',
        'name_en' => 'Parsa Ahadi',
        'role_fa' => 'مهندس ارشد بک‌اند، اینترنت اشیا (IoT) و امنیت API',
        'role_en' => 'Back-End & IoT Systems Engineer',
        'short_desc_fa' => 'طراحی سیستم‌های سرور مقیاس‌پذیر، معماری میکروسرویس، پیاده‌سازی پروتکل‌های مخابراتی سخت‌افزار (IoT) و استانداردهای سخت‌گیرانه امنیت داده.',
        'short_desc_en' => 'Engineering scalable server architectures, microservice backends, embedded hardware network protocols, and robust API cryptographic defenses.',
        'bio_fa' => 'پارسا متخصص برقراری ارتباط بدون وقفه میان سخت‌افزارهای امبدد و سرورهای ابری است. او پایداری، سرعت و مقاومت معماری بک‌اند در برابر حملات سایبری را تضمین می‌کند.',
        'bio_en' => 'Parsa specializes in connecting embedded microcontrollers to cloud backends, designing fault-tolerant REST/WebSocket APIs, and enforcing zero-trust API protection.',
        'tags' => ['Embedded IoT', 'Backend APIs', 'System Security', 'STM32/ESP32', 'Network Protocols'],
        'skills' => [
            ['name' => 'Backend Architecture (PHP/Node)', 'level' => 93],
            ['name' => 'IoT & Microcontrollers (ESP32/STM32)', 'level' => 90],
            ['name' => 'API Security & Cryptography', 'level' => 91],
            ['name' => 'Real-Time Telemetry & WebSockets', 'level' => 89],
            ['name' => 'Docker & Server Virtualization', 'level' => 87],
        ],
        'expertise_fa' => ['پیاده‌سازی شبکه‌های سنسوری IoT', 'طراحی APIهای بلادرنگ و با تاخیر کم', 'امنیت احراز هویت و توکن‌های نشست', 'مدیریت ترافیک سرور و Rate Limiting'],
        'expertise_en' => ['IoT Sensor Network Design', 'Low-Latency Real-Time APIs', 'Session Token Cryptography', 'High-Concurrence Rate Limiting'],
        'projects' => [
            ['title_fa' => 'هاب کنترل مرکزی داده‌های اینترنت اشیا', 'title_en' => 'Industrial IoT Gateway Hub', 'desc_fa' => 'گردآوری داده‌های سنسوری با رمزنگاری سخت‌افزاری و پردازش در لبه.', 'desc_en' => 'Edge telemetry aggregation with hardware TLS encryption.', 'tech' => 'C++, MQTT, WebSockets'],
            ['title_fa' => 'موتور توزیع‌شده احراز هویت API', 'title_en' => 'Zero-Trust API Authentication Engine', 'desc_fa' => 'سامانه محافظت از دسترسی با مکانیزم‌های ضد بروت‌فورس و هشینگ پیشرفته.', 'desc_en' => 'High-throughput token verification gate with adaptive IP throttling.', 'tech' => 'PHP 8, Argon2id, Redis']
        ],
        'experience' => [
            ['role_fa' => 'مهندس بک‌اند و اینترنت اشیا', 'role_en' => 'Back-End & IoT Systems Lead', 'org' => 'PhysioElectric', 'period' => '2024 - Present', 'desc_fa' => 'توسعه APIها و پل‌های ارتباط سخت‌افزار با شبکه.', 'desc_en' => 'Building backend infrastructure and embedded hardware integrations.']
        ],
        'education' => [
            ['degree_fa' => 'کارشناسی مهندسی', 'degree_en' => 'B.Sc. in Engineering', 'school' => 'Iran University of Science & Technology', 'period' => '2023 - 2027']
        ],
        'resume_url' => '/uploads/resumes/parsa-ahadi-resume.pdf',
        'socials' => [
            'github' => 'https://github.com',
            'linkedin' => 'https://linkedin.com',
            'telegram' => 'https://t.me',
            'email' => 'mailto:parsa@physioelectric.com'
        ]
    ],

    'amirreza-hashemi' => [
        'num' => '04',
        'slug' => 'amirreza-hashemi',
        'avatar_type' => 'data_architecture',
        'name_fa' => 'سید امیررضا هاشمی',
        'name_en' => 'Seyed Amirreza Hashemi',
        'role_fa' => 'معمار پایگاه داده و تحلیل‌گر داده‌های ساختاریافته',
        'role_en' => 'Database Architect & Data Strategist',
        'short_desc_fa' => 'مدل‌سازی اسکیماهای رابطه‌ای و غیررابطه‌ای، بهینه‌سازی کوئری‌های پیچیده، پایداری تراکنش‌ها و معماری خطوط پردازش داده در مقیاس بالا.',
        'short_desc_en' => 'Architecting relational and distributed database schemata, query execution optimization, ACID transaction guarantees, and high-volume data pipeline engineering.',
        'bio_fa' => 'امیررضا قلب تپنده ذخیره‌سازی و بازیابی اطلاعات در فیزیوالکتریک است. رویکرد او حذف گلوگاه‌های حافظه، تضمین بی‌نقص بودن داده‌ها در تراکنش‌های هم‌زمان و استخراج بینش‌های آماری از جریان‌های حجیم داده است.',
        'bio_en' => 'Amirreza is responsible for data integrity, low-latency index tuning, database partitioning, and optimizing mission-critical SQL/NoSQL storage engines under high concurrency.',
        'tags' => ['Database Architecture', 'MySQL / PostgreSQL', 'Query Tuning', 'Data Pipeline', 'ACID'],
        'skills' => [
            ['name' => 'Database Schemata & Relational Design', 'level' => 95],
            ['name' => 'Query Optimization & Indexing', 'level' => 93],
            ['name' => 'Data Integrity & Transactions (ACID)', 'level' => 94],
            ['name' => 'High-Volume Data Pipelines', 'level' => 88],
            ['name' => 'Data Normalization & Sharding', 'level' => 89],
        ],
        'expertise_fa' => ['طراحی اسکیماهای دیتابیس بدون افزونگی', 'تیونینگ سرعت کوئری‌های چندجدولی سنگین', 'استراتژی‌های پشتیبان‌گیری و Failover', 'مدل‌سازی آماری داده‌های پروژه'],
        'expertise_en' => ['Zero-Redundancy Schema Normalization', 'Heavy Join Query Profiling & Optimization', 'Replication & Failover Strategies', 'Statistical Modeling of System Metrics'],
        'projects' => [
            ['title_fa' => 'معماری دیتابیس چندمستأجره با امنیت داده بالا', 'title_en' => 'High-Concurrency Database Partition', 'desc_fa' => 'طراحی ساختار دیتابیس جداشده با شاخص‌گذاری پیشرفته B-Tree.', 'desc_en' => 'Partitioned table architecture with sub-millisecond query latency.', 'tech' => 'MySQL 8, InnoDB Engine, Redis'],
            ['title_fa' => 'موتور گزارش‌گیری بلادرنگ شاخص‌های آماری', 'title_en' => 'Real-Time Analytics Pipeline', 'desc_fa' => 'تحلیل و نمایش آمار تراکنش‌های پروژه‌ها با کوئری‌های تحلیلی بهینه.', 'desc_en' => 'Real-time aggregation pipeline converting raw event logs into metrics.', 'tech' => 'PostgreSQL, SQL Views, Event Queues']
        ],
        'experience' => [
            ['role_fa' => 'معمار پایگاه داده', 'role_en' => 'Database Architect', 'org' => 'PhysioElectric', 'period' => '2024 - Present', 'desc_fa' => 'طراحی و مدیریت جامع دیتابیس‌های سامانه‌های آنلاین.', 'desc_en' => 'Designing and auditing all persistent database engines and models.']
        ],
        'education' => [
            ['degree_fa' => 'کارشناسی مهندسی', 'degree_en' => 'B.Sc. in Engineering', 'school' => 'Iran University of Science & Technology', 'period' => '2023 - 2027']
        ],
        'resume_url' => '/uploads/resumes/amirreza-hashemi-resume.pdf',
        'socials' => [
            'github' => 'https://github.com',
            'linkedin' => 'https://linkedin.com',
            'telegram' => 'https://t.me',
            'email' => 'mailto:amirreza@physioelectric.com'
        ]
    ]
];