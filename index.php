<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JobPortal - Find Your Dream Job</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary: #2563eb;
            --secondary: #7c3aed;
            --dark: #0f172a;
            --light: #f8fafc;
        }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: var(--dark); overflow-x: hidden; }
        
        /* Navbar */
        .navbar { background: white; box-shadow: 0 2px 15px rgba(0,0,0,0.05); padding: 15px 0; }
        .nav-link { font-weight: 500; color: #64748b; margin: 0 10px; }
        .nav-link:hover { color: var(--primary); }
        .btn-login { border: 1px solid #e2e8f0; color: var(--dark); border-radius: 8px; padding: 8px 20px; }
        .btn-signup { background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white; border: none; border-radius: 8px; padding: 8px 20px; font-weight: 600; }

        /* Hero Section */
        .hero-section {
            padding: 80px 0;
            background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
            position: relative;
        }
        .hero-title { font-size: 3.5rem; font-weight: 800; line-height: 1.2; margin-bottom: 20px; }
        .hero-title span {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .hero-subtitle { font-size: 1.2rem; color: #64748b; margin-bottom: 40px; }

        /* Search Bar Box */
        .search-box {
            background: white;
            padding: 10px;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(37, 99, 235, 0.1);
            display: flex;
            gap: 10px;
            border: 1px solid #e2e8f0;
        }
        .search-input { border: none; box-shadow: none; padding: 15px; background: #f8fafc; border-radius: 8px; }
        .search-input:focus { background: white; box-shadow: 0 0 0 2px var(--primary); }

        /* Featured Jobs */
        .featured-section { padding: 60px 0; background: #fff; }
        .job-card {
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 25px;
            transition: all 0.3s ease;
            height: 100%;
            background: white;
        }
        .job-card:hover { transform: translateY(-5px); box-shadow: 0 10px 30px rgba(0,0,0,0.08); border-color: var(--primary); }
        .company-logo { width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 1.5rem; }
        .badge-type { background: #f1f5f9; color: #475569; font-size: 0.75rem; padding: 4px 10px; border-radius: 20px; }
        .salary { color: var(--primary); font-weight: 600; }

        /* Stats */
        .stats-section { background: #f8fafc; padding: 50px 0; }
        .stat-item h3 { font-size: 2.5rem; font-weight: 800; color: var(--primary); }

        /* Footer */
        footer { background: #0f172a; color: #94a3b8; padding: 40px 0; }
        footer a { color: #cbd5e1; text-decoration: none; }
        footer a:hover { color: white; }
    </style>
</head>
<body>

<!-- 🧭 Navbar -->
<nav class="navbar navbar-expand-lg fixed-top">
    <div class="container">
        <a class="navbar-brand fw-bold fs-4" href="#">
            <i class="bi bi-briefcase-fill text-primary"></i> Job<span class="text-secondary">Portal</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item"><a class="nav-link" href="#">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="student/view_jobs.php">Find Jobs</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Companies</a></li>
            </ul>
            <div class="d-flex gap-2">
                <a href="login.php" class="btn btn-login">Log In</a>
                <a href="register.php" class="btn btn-signup">Sign Up</a>
            </div>
        </div>
    </div>
</nav>

<!-- 🚀 Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-5 mb-lg-0">
                <h1 class="hero-title">Find the <span>Right Job</span> <br> That Fits You</h1>
                <p class="hero-subtitle">Explore over 100+ job opportunities from top companies. The fastest way to land your dream role.</p>
                
                <!-- Search Bar -->
                <form action="student/view_jobs.php" method="GET" class="search-box mb-4">
                    <input type="text" name="search" class="form-control search-input" placeholder="Job title or keyword...">
                    <input type="text" name="city" class="form-control search-input" placeholder="Location (e.g. Delhi)">
                    <button type="submit" class="btn btn-signup px-4">Search</button>
                </form>

                <!-- Popular Tags -->
                <div class="d-flex align-items-center gap-2 text-muted small">
                    <span>Trending:</span>
                    <a href="student/view_jobs.php?field=IT" class="badge bg-light text-dark p-2">IT & Software</a>
                    <a href="student/view_jobs.php?field=Marketing" class="badge bg-light text-dark p-2">Marketing</a>
                    <a href="student/view_jobs.php?field=Design" class="badge bg-light text-dark p-2">Design</a>
                </div>
            </div>
            
            <!-- Hero Image / Illustration -->
            <div class="col-lg-6 text-center">
                <img src="https://cdni.iconscout.com/illustration/premium/thumb/job-search-2996644-2500076.png" alt="Job Search" class="img-fluid" style="max-height: 400px; filter: drop-shadow(0 10px 20px rgba(0,0,0,0.1));">
            </div>
        </div>
    </div>
</section>

<!-- 📊 Stats Section -->
<section class="stats-section">
    <div class="container">
        <div class="row text-center g-4">
            <div class="col-md-4">
                <div class="stat-item">
                    <h3>50+</h3>
                    <p class="text-muted fw-bold">Trusted Companies</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-item">
                    <h3>200+</h3>
                    <p class="text-muted fw-bold">Jobs Posted</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-item">
                    <h3>1000+</h3>
                    <p class="text-muted fw-bold">Candidates Registered</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 🔥 Featured Jobs -->
<section class="featured-section">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Featured Job Openings</h2>
            <p class="text-muted">Hand-picked jobs from top companies just for you</p>
        </div>

        <div class="row g-4">
            <!-- Job Card 1 -->
            <div class="col-md-4">
                <div class="job-card">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="company-logo bg-primary text-white"><i class="bi bi-windows"></i></div>
                            <div>
                                <h6 class="fw-bold mb-0">UI/UX Designer</h6>
                                <small class="text-muted">Microsoft</small>
                            </div>
                        </div>
                        <span class="badge-type">Full Time</span>
                    </div>
                    <div class="d-flex gap-2 mb-3">
                        <small class="text-muted"><i class="bi bi-geo-alt"></i> Bangalore</small>
                        <small class="text-muted"><i class="bi bi-clock"></i> 2 days ago</small>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                        <span class="salary">₹80k - ₹1.2L</span>
                        <a href="register.php" class="btn btn-sm btn-outline-primary">Apply Now</a>
                    </div>
                </div>
            </div>

            <!-- Job Card 2 -->
            <div class="col-md-4">
                <div class="job-card">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="company-logo bg-success text-white"><i class="bi bi-code-slash"></i></div>
                            <div>
                                <h6 class="fw-bold mb-0">React Developer</h6>
                                <small class="text-muted">TechFlow</small>
                            </div>
                        </div>
                        <span class="badge-type">Remote</span>
                    </div>
                    <div class="d-flex gap-2 mb-3">
                        <small class="text-muted"><i class="bi bi-geo-alt"></i> Remote</small>
                        <small class="text-muted"><i class="bi bi-clock"></i> 5 days ago</small>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                        <span class="salary">₹60k - ₹90k</span>
                        <a href="register.php" class="btn btn-sm btn-outline-primary">Apply Now</a>
                    </div>
                </div>
            </div>

            <!-- Job Card 3 -->
            <div class="col-md-4">
                <div class="job-card">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="company-logo bg-warning text-white"><i class="bi bi-megaphone"></i></div>
                            <div>
                                <h6 class="fw-bold mb-0">Marketing Lead</h6>
                                <small class="text-muted">GrowthBox</small>
                            </div>
                        </div>
                        <span class="badge-type">Hybrid</span>
                    </div>
                    <div class="d-flex gap-2 mb-3">
                        <small class="text-muted"><i class="bi bi-geo-alt"></i> Mumbai</small>
                        <small class="text-muted"><i class="bi bi-clock"></i> 1 week ago</small>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                        <span class="salary">₹50k - ₹75k</span>
                        <a href="register.php" class="btn btn-sm btn-outline-primary">Apply Now</a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-5">
            <a href="student/view_jobs.php" class="btn btn-signup px-4 py-2 fs-5">View All Jobs <i class="bi bi-arrow-right"></i></a>
        </div>
    </div>
</section>

<!-- 📜 Footer -->
<footer>
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-4">
                <h5 class="text-white mb-3">JobPortal</h5>
                <p class="small">Connecting talent with opportunity. The best place to find your next career move.</p>
            </div>
            <div class="col-md-4 mb-4">
                <h5 class="text-white mb-3">Quick Links</h5>
                <ul class="list-unstyled">
                    <li><a href="register.php">For Students</a></li>
                    <li><a href="register.php">For Companies</a></li>
                    <li><a href="#">About Us</a></li>
                    <li><a href="#">Contact</a></li>
                </ul>
            </div>
            <div class="col-md-4 mb-4">
                <h5 class="text-white mb-3">Contact Us</h5>
                <p class="small"><i class="bi bi-envelope me-2"></i> support@jobportal.com</p>
                <p class="small"><i class="bi bi-telephone me-2"></i> +91 98765 43210</p>
            </div>
        </div>
        <hr class="border-secondary">
        <div class="text-center small">
            &copy; 2026 JobPortal. All rights reserved.
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>