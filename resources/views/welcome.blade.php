<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'TicketPro') }} - Professional Ticket Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); min-height: 100vh; }
        .hero { min-height: 100vh; display: flex; align-items: center; }
        .hero-content { text-align: center; color: white; }
        .hero-title { font-size: 3.5rem; font-weight: 700; margin-bottom: 1.5rem; }
        .hero-title span { background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .hero-subtitle { font-size: 1.25rem; color: rgba(255,255,255,0.7); margin-bottom: 2rem; }
        .btn-hero { padding: 1rem 2.5rem; font-size: 1.1rem; border-radius: 50px; font-weight: 600; }
        .btn-primary { background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); border: none; }
        .btn-primary:hover { background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%); transform: translateY(-2px); }
        .btn-outline-light:hover { background: white; color: #1e293b; }
        .feature-card { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 16px; padding: 2rem; text-align: center; color: white; transition: all 0.3s; }
        .feature-card:hover { background: rgba(255,255,255,0.1); transform: translateY(-5px); }
        .feature-icon { width: 60px; height: 60px; background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; }
        .feature-icon i { font-size: 1.5rem; color: white; }
    </style>
</head>
<body>
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title">Welcome to <span>TicketPro</span></h1>
                <p class="hero-subtitle">Professional IT Ticket Management System with Role-Based Access Control and Real-Time Notifications</p>
                <div class="d-flex gap-3 justify-content-center mb-5">
                    <a href="{{ route('login') }}" class="btn btn-primary btn-hero"><i class="fas fa-sign-in-alt me-2"></i>Login</a>
                    <a href="{{ route('register') }}" class="btn btn-outline-light btn-hero"><i class="fas fa-user-plus me-2"></i>Register</a>
                </div>
                
                <div class="row g-4 mt-5">
                    <div class="col-md-4">
                        <div class="feature-card">
                            <div class="feature-icon"><i class="fas fa-users-cog"></i></div>
                            <h5>Role-Based Access</h5>
                            <p class="text-white-50">Admin, Manager, and Developer roles with specific permissions using Spatie Laravel Permission.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="feature-card">
                            <div class="feature-icon"><i class="fas fa-bell"></i></div>
                            <h5>Real-Time Notifications</h5>
                            <p class="text-white-50">Instant notifications via Pusher WebSockets when tickets are assigned or updated.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="feature-card">
                            <div class="feature-icon"><i class="fas fa-tasks"></i></div>
                            <h5>IT Workflow</h5>
                            <p class="text-white-50">Ticket statuses: Assigned, In Process, Completed with flexible status transitions.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</body>
</html>
