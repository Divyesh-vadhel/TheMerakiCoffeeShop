<style>
    :root {
        /* TAVERN THEME PALETTE */
        --sidebar-bg: #2C1810;      /* Deep Espresso */
        --sidebar-hover: #3E2C26;   /* Lighter Brown */
        --sidebar-active: #D4A373;  /* Warm Latte/Gold */
        --sidebar-text: #F3F0EB;    /* Foam White */
        
        --main-bg: #FAFAF8;         /* Off-White Body */
        --card-bg: #FFFFFF;
        
        --text-primary: #2C1810;
        --text-secondary: #665b56;
        
        --accent: #D4A373;          /* Gold Button */
        --accent-hover: #b08d55;
        
        --border: #E5E0D8;
        --shadow: 0 4px 6px -1px rgba(44, 24, 16, 0.05);
        --shadow-lg: 0 10px 15px -3px rgba(44, 24, 16, 0.1);
        
        --danger: #ef4444;
        --success: #10b981;
    }

    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        font-family: 'Poppins', sans-serif;
        background: var(--main-bg);
        color: var(--text-primary);
        min-height: 100vh;
        display: flex;
    }

    /* TYPOGRAPHY */
    h1, h2, h3, .brand-font { font-family: 'Playfair Display', serif; }

    /* SIDEBAR */
    .sidebar {
        width: 260px;
        background: var(--sidebar-bg);
        min-height: 100vh;
        position: fixed;
        left: 0; top: 0;
        display: flex; flex-direction: column;
        color: var(--sidebar-text);
        z-index: 1000;
        box-shadow: 4px 0 10px rgba(0,0,0,0.1);
    }

    .sidebar-brand {
        padding: 30px 24px;
        border-bottom: 1px solid rgba(255,255,255,0.1);
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--sidebar-text);
        font-family: 'Playfair Display', serif;
    }
    .sidebar-brand span { color: var(--accent); }

    .sidebar-nav { flex: 1; padding: 20px 12px; }
    
    .nav-link {
        display: flex; align-items: center; gap: 12px;
        padding: 14px 16px;
        color: rgba(255,255,255,0.7);
        text-decoration: none;
        border-radius: 8px;
        margin-bottom: 5px;
        transition: 0.3s;
        font-size: 0.95rem;
    }
    
    .nav-link:hover { background: var(--sidebar-hover); color: white; }
    .nav-link.active { background: var(--accent); color: var(--sidebar-bg); font-weight: 600; }

    .sidebar-footer { padding: 24px; border-top: 1px solid rgba(255,255,255,0.1); }
    .btn-logout {
        width: 100%; padding: 12px;
        background: rgba(255,255,255,0.1);
        border: 1px solid rgba(255,255,255,0.2);
        color: white; border-radius: 6px;
        cursor: pointer; transition: 0.3s;
        font-family: 'Poppins', sans-serif;
    }
    .btn-logout:hover { background: #cf4545; border-color: #cf4545; }

    /* MAIN CONTENT */
    .main-content { flex: 1; margin-left: 260px; padding: 40px; }
    .page-header { margin-bottom: 30px; border-bottom: 1px solid var(--border); padding-bottom: 20px; }
    .page-header h1 { font-size: 2.2rem; color: var(--primary); }

    /* CARDS */
    .card {
        background: var(--card-bg);
        border-radius: 12px;
        padding: 24px;
        box-shadow: var(--shadow);
        border: 1px solid var(--border);
        margin-bottom: 24px;
    }

    /* BUTTONS */
    .btn-primary {
        padding: 10px 20px;
        background: var(--text-primary);
        color: #ffffff;
        border: none; border-radius: 6px;
        font-size: 0.9rem; font-weight: 600;
        cursor: pointer; transition: 0.3s;
    }
    .btn-primary:hover { background: var(--accent); color: var(--text-primary); }

    .btn-small { padding: 6px 12px; border-radius: 4px; border:none; cursor: pointer; font-size: 0.8rem; }
    .btn-delete { background: #fee2e2; color: #b91c1c; }
    .btn-delete:hover { background: #b91c1c; color: white; }

    /* FORM ELEMENTS */
    input, select, textarea {
        width: 100%; padding: 12px;
        border: 1px solid var(--border);
        border-radius: 8px;
        background: #fff;
        font-family: 'Poppins', sans-serif;
        margin-bottom: 10px;
    }
    input:focus { outline: none; border-color: var(--accent); }
    label { font-size: 0.9rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 5px; display: block; }

    /* ALERTS & BADGES */
    .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; }
    .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
    .alert-error { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }

    .status-badge { padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; }
    .status-pending { background: #fef3c7; color: #92400e; }
    .status-confirmed { background: #d1fae5; color: #065f46; }
    .status-cancelled { background: #fee2e2; color: #b91c1c; }

    @media (max-width: 768px) {
        .sidebar { transform: translateX(-100%); transition: 0.3s; }
        .main-content { margin-left: 0; padding: 20px; }
    }
</style>