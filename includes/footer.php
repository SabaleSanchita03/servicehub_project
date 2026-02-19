<footer class="main-footer">
    <div class="container">
        <div class="row g-4 mb-5">
            <div class="col-lg-4 col-md-6">
                <div class="footer-brand mb-4">
                    <div class="logo-sq mb-3"><i class="fas fa-layer-group"></i></div>
                    <h3 class="fw-800 m-0 text-dark">Service<span>Hub</span></h3>
                </div>
                <p class="text-muted pe-lg-5">
                    Empowering local merchants to digitize their services and connect with customers seamlessly. The all-in-one platform for your business growth.
                </p>
                <div class="social-links d-flex gap-3">
                    <a href="#"><i class="bi bi-facebook"></i></a>
                    <a href="#"><i class="bi bi-instagram"></i></a>
                    <a href="#"><i class="bi bi-twitter-x"></i></a>
                    <a href="#"><i class="bi bi-linkedin"></i></a>
                </div>
            </div>

            <div class="col-lg-2 col-md-6">
                <h6 class="footer-heading">Platform</h6>
                <ul class="footer-list">
                    <li><a href="index.php">Dashboard</a></li>
                    <li><a href="services.php">My Services</a></li>
                    <li><a href="orders.php">Booking History</a></li>
                    <li><a href="profile.php">Account Settings</a></li>
                </ul>
            </div>

            <div class="col-lg-2 col-md-6">
                <h6 class="footer-heading">Support</h6>
                <ul class="footer-list">
                    <li><a href="#">Help Center</a></li>
                    <li><a href="#">Terms of Service</a></li>
                    <li><a href="#">Privacy Policy</a></li>
                    <li><a href="#">Merchant FAQs</a></li>
                </ul>
            </div>

            <div class="col-lg-4 col-md-6">
                <h6 class="footer-heading">Stay Updated</h6>
                <p class="small text-muted mb-4">Subscribe to get the latest business tips and platform updates.</p>
                <form class="newsletter-form">
                    <div class="input-group">
                        <input type="email" class="form-control" placeholder="Email address">
                        <button class="btn btn-primary" type="button">Join</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="footer-bottom border-top pt-4">
            <div class="row">
                <div class="col-md-6 text-center text-md-start">
                    <p class="small text-muted m-0">&copy; <?= date('Y') ?> ServiceHub Inc. Built for experts like you.</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <div class="d-flex align-items-center justify-content-center justify-content-md-end">
                        <span class="system-status-dot"></span>
                        <span class="small fw-bold text-muted">All Systems Operational</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>

<style>
    .main-footer {
        background: #fff;
        padding: 80px 0 30px 0;
        border-top: 1px solid #eef2f6;
        margin-top: 100px;
    }

    .footer-heading {
        font-weight: 800;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #1e293b;
        margin-bottom: 25px;
    }

    .footer-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .footer-list li {
        margin-bottom: 12px;
    }

    .footer-list a {
        color: #64748b;
        text-decoration: none;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .footer-list a:hover {
        color: #6366f1;
        padding-left: 5px;
    }

    .logo-sq {
        width: 35px; height: 35px;
        background: #6366f1;
        border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        color: white;
    }

    .social-links a {
        width: 36px; height: 36px;
        background: #f8fafc;
        color: #64748b;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        text-decoration: none;
        transition: 0.3s;
    }

    .social-links a:hover {
        background: #6366f1;
        color: white;
        transform: translateY(-3px);
    }

    .newsletter-form .form-control {
        border-radius: 12px 0 0 12px;
        border: 1px solid #e2e8f0;
        padding: 10px 15px;
    }

    .newsletter-form .btn {
        border-radius: 0 12px 12px 0;
        padding: 10px 20px;
        font-weight: 700;
    }

    .system-status-dot {
        width: 8px; height: 8px;
        background: #10b981;
        border-radius: 50%;
        margin-right: 8px;
        box-shadow: 0 0 10px rgba(16, 185, 129, 0.4);
    }

    .fw-800 { font-weight: 800; }
</style>