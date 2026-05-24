<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RIVIO - Second Brain</title>
    <style>
        /* === RESET & FOUNDATION === */
        * {
            margin: 0; padding: 0; box-sizing: border-box;
            font-family: 'Inter', 'Segoe UI', Roboto, sans-serif;
        }

        /* Menambahkan smooth scroll agar perpindahan halus */
        html {
            scroll-behavior: smooth;
        }

        :root {
            --bg-color: #040816;
            --nav-bg: #060d21;
            --accent-blue: #3b82f6;
            --text-gray: #94a3b8;
        }

        body {
            background-color: var(--bg-color);
            color: #ffffff;
            line-height: 1.5;
        }

        .bg-dots {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background-image: radial-gradient(rgba(255, 255, 255, 0.05) 1px, transparent 1px);
            background-size: 40px 40px;
            z-index: -1;
        }

        /* === NAVIGATION === */
        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem 8%;
            background: var(--nav-bg);
            border-bottom: 1px solid rgba(255,255,255,0.05);
            position: sticky; 
            top: 0; 
            z-index: 1000; /* Ditingkatkan agar selalu di atas */
        }

        .logo-group {
            display: flex; align-items: center; gap: 10px;
        }

        .nav-links a {
            color: #cbd5e1; text-decoration: none; margin: 0 15px;
            font-size: 0.9rem; transition: 0.3s;
            cursor: pointer;
        }
        .nav-links a:hover { color: var(--accent-blue); }

        .btn-get-started {
            background: var(--accent-blue);
            color: white; padding: 0.6rem 1.5rem;
            border-radius: 6px; text-decoration: none;
            font-weight: 600; font-size: 0.9rem;
        }

        /* === HERO === */
        .hero {
            display: flex; align-items: center;
            padding: 100px 10%; gap: 40px;
            min-height: 85vh;
        }

        .hero-content { flex: 1; }
        .hero-title-img { 
            width: 320px; margin-bottom: 1.5rem; 
            filter: drop-shadow(0 0 15px rgba(59, 130, 246, 0.3));
        }

        .hero-desc {
            color: var(--text-gray); font-size: 0.85rem;
            letter-spacing: 1.5px; text-transform: uppercase;
            max-width: 480px; margin-bottom: 2.5rem; line-height: 1.8;
        }

        .btn-white {
            background: #ffffff; color: #000;
            padding: 1rem 2.5rem; border-radius: 4px;
            text-decoration: none; font-weight: 700;
            font-size: 0.85rem; letter-spacing: 1px;
        }

        .hero-visual { flex: 1; display: flex; justify-content: center; }
        .hero-visual img { width: 100%; max-width: 500px; }

        /* === PROBLEM === */
        .section-label {
            font-size: 1.2rem; letter-spacing: 5px; font-weight: 800;
            margin-bottom: 2rem; display: block;
        }

        .problem { padding: 120px 10% 80px; } /* Tambah padding top agar tidak tertutup nav saat scroll */
        .problem-box {
            display: flex; align-items: center; gap: 60px;
            background: rgba(255,255,255,0.02);
            padding: 50px; border-radius: 24px;
            border-left: 3px solid rgba(59, 130, 246, 0.5);
        }
        .problem-img { flex: 0 0 350px; }
        .problem-img img { width: 100%; border-radius: 12px; }
        .problem-text { color: var(--text-gray); font-size: 0.95rem; line-height: 2; }

        /* === FEATURES === */
        .features { padding: 120px 10% 80px; }
        .feature-card {
            background: #ffffff; color: #1e293b;
            display: flex; align-items: center;
            padding: 60px; border-radius: 20px;
            margin-bottom: 80px; position: relative; gap: 40px;
        }
        .feature-card.reverse { flex-direction: row-reverse; }

        .feature-info { flex: 1; }
        .feature-info h3 { font-size: 1.8rem; letter-spacing: 3px; margin-bottom: 1rem; }
        .feature-info p { font-size: 0.95rem; line-height: 1.8; color: #475569; }

        .feature-img { flex: 1; text-align: center; }
        .feature-img img { max-width: 100%; height: 280px; object-fit: contain; }

        .dot {
            width: 45px; height: 45px; border-radius: 50%;
            position: absolute; z-index: 5;
        }
        .dot-blue-light { background: #00d2ff; top: -20px; left: -20px; }
        .dot-blue-dark { background: #2563eb; bottom: -20px; right: -20px; }

        /* === FOOTER === */
        .footer-curve {
            background: #ffffff; color: #1e293b;
            border-radius: 50px 50px 0 0;
            padding: 60px 10% 40px; margin-top: 100px;
        }
        .footer-main {
            display: flex; justify-content: space-between; align-items: center;
            border-bottom: 1px solid #e2e8f0; padding-bottom: 30px;
        }
        .footer-brand h2 { color: var(--accent-blue); margin-bottom: 5px; }
        .footer-brand p { font-size: 0.85rem; color: #64748b; }
        .footer-nav a {
            text-decoration: none; color: #334155; margin-left: 25px;
            font-size: 0.9rem; font-weight: 600;
        }
        .footer-copy {
            text-align: center; padding-top: 30px; font-size: 0.8rem; color: #94a3b8;
        }

        @media (max-width: 900px) {
            .hero, .problem-box, .feature-card { flex-direction: column; text-align: center; padding: 30px; }
            .problem-img, .feature-img { width: 100%; }
            .hero-title-img { width: 250px; margin: 0 auto 20px; }
        }
    </style>
</head>
<body>

    <div class="bg-dots"></div>

    <nav>
        <div class="logo-group">
            <img src="asset/Logo.png" height="35" alt="">
            <img src="asset/RIVIO.png" height="18" alt="">
        </div>
        <div class="nav-links">
            <!-- Menambahkan tanda # untuk internal link -->
            <a href="#features">Feature</a>
            <a href="#problem">Problem</a>
            <a href="#about">About us</a>
        </div>
        <a href="register.php" class="btn-get-started">Get Started →</a>
    </nav>

    <main>
        <section class="hero">
            <div class="hero-content">
                <img src="asset/RIVIO.png" class="hero-title-img" alt="RIVIO">
                <p class="hero-desc">
                    Simplify your life with Rivio. Manage your schedule, complete tasks, 
                    and track your finances with one integrated second brain.
                </p>
                <a href="register.php" class="btn-white">GET STARTED</a>
            </div>
            <div class="hero-visual">
                <img src="asset/foto_1.png" alt="">
            </div>
        </section>

        <!-- Menambahkan id="problem" -->
        <section id="problem" class="problem">
            <span class="section-label">PROBLEM</span>
            <div class="problem-box">
                <div class="problem-img">
                    <img src="asset/foto_2.png" alt="">
                </div>
                <p class="problem-text">
                    OTAK MANUSIA DIRANCANG UNTUK MENCIPTAKAN IDE, BUKAN MENYIMPANNYA. 
                    DI ERA DIGITAL, KITA KEWALAHAN MENERIMA INFORMASI, NAMUN SERING KALI LUPA 
                    SAAT MEMBUTUHKANNYA. AKIBATNYA, BANYAK POTENSI KREATIF TERBUANG.
                </p>
            </div>
        </section>

        <!-- Menambahkan id="features" -->
        <section id="features" class="features">
            <span class="section-label" style="text-align: center;">FEATURE</span>

            <div class="feature-card">
                <div class="dot dot-blue-light"></div>
                <div class="feature-info">
                    <h3>TASK</h3>
                    <p>Ubah rencana menjadi hasil nyata. Dengan Task, Anda tidak hanya mencatat daftar tugas, tapi menyetel prioritas. Memastikan tidak ada tenggat waktu yang terlewat.</p>
                </div>
                <div class="feature-img">
                    <img src="asset/foto_3.png" alt="">
                </div>
                <div class="dot dot-blue-dark"></div>
            </div>

            <div class="feature-card reverse">
                <div class="dot dot-blue-light"></div>
                <div class="feature-info">
                    <h3>NOTES</h3>
                    <p>Jangan biarkan ide hebat menguap. Fitur Notes dirancang untuk menangkap inspirasi secepat kilat. Semuanya tersimpan rapi dan mudah dicari.</p>
                </div>
                <div class="feature-img">
                    <img src="asset/foto_4.png" alt="">
                </div>
                <div class="dot dot-blue-dark"></div>
            </div>

            <div class="feature-card">
                <div class="dot dot-blue-light"></div>
                <div class="feature-info">
                    <h3>FINANCE</h3>
                    <p>Keputusan finansial yang lebih baik dimulai dari pencatatan yang disiplin. Lacak arus kas, atur anggaran, dan lihat profil keuangan secara transparan.</p>
                </div>
                <div class="feature-img">
                    <img src="asset/foto_5.png" alt="">
                </div>
                <div class="dot dot-blue-dark" style="background: #1e88e5;"></div>
            </div>
        </section>
    </main>

    <footer id="about" class="footer-curve">
        <div class="footer-main">
            <div class="footer-brand">
                <img src="asset/Logo.png" alt="Logo" style="height: 24px; width: auto;">
                <img src="asset/RIVIO.png" alt="RIVIO" style="height: 14px; width: auto;">
                <p>Created by Achmad Fadhil • 2026</p>
            </div>
            <div class="footer-nav">
                <a href="mailto:achmadfadhilithisom@gmail.com">Email</a>
                <a href="https://instagram.com/fdhil273">Instagram</a>
                <a href="https://wa.me/6281363901809">WhatsApp</a>
            </div>
        </div>
        <div class="footer-copy">
            &copy; 2026 RIVIO Second Brain. All rights reserved.
        </div>
    </footer>

</body>
</html>