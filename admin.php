<?php
require_once __DIR__ . '/config/database.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$user = getCurrentUser();
if (!isAdmin()) {
    http_response_code(403);
    echo 'Access denied. Admin account required.';
    exit;
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nepal Tour & Travel | Master Database ER-Driven Management Console</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --bg-canvas: #f8fafc;
            --bg-surface: #ffffff;
            --bg-sidebar: #0f172a;
            --bg-sidebar-active: #1e293b;
            --text-primary: #0f172a;
            --text-secondary: #475569;
            --text-muted: #94a3b8;
            --text-sidebar: #cbd5e1;
            --brand-primary: #dc2626; /* Crimson Red */
            --brand-secondary: #1e3a8a; /* Deep Blue */
            --border-color: #e2e8f0;
            --radius-lg: 12px;
            --radius-md: 8px;
            --radius-sm: 4px;
        }

        [data-theme="dark"] {
            --bg-canvas: #020617;
            --bg-surface: #0f172a;
            --bg-sidebar: #020617;
            --bg-sidebar-active: #1e293b;
            --text-primary: #f8fafc;
            --text-secondary: #cbd5e1;
            --text-muted: #64748b;
            --text-sidebar: #94a3b8;
            --border-color: #1e293b;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: system-ui, -apple-system, sans-serif; }
        body { background-color: var(--bg-canvas); color: var(--text-primary); min-height: 100vh; overflow: hidden; }

        /* --- AUTHENTICATION GATEKEEPER --- */
        #auth-gatekeeper {
            position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
            background: linear-gradient(135deg, var(--brand-secondary), #090d16);
            z-index: 99999; display: flex; align-items: center; justify-content: center;
        }
        .auth-panel {
            background: #ffffff; width: 100%; max-width: 420px; border-radius: var(--radius-lg);
            padding: 40px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); text-align: center; color: #0f172a;
        }
        .auth-logo { font-size: 1.6rem; font-weight: 800; margin-bottom: 6px; }
        .auth-logo span { color: var(--brand-primary); }
        .auth-subtitle { color: #64748b; font-size: 0.85rem; margin-bottom: 30px; }
        .input-wrapper { text-align: left; margin-bottom: 18px; }
        .input-wrapper label { display: block; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; margin-bottom: 6px; color: #475569; }
        .input-wrapper input { width: 100%; padding: 12px 16px; border: 1px solid #cbd5e1; border-radius: var(--radius-md); font-size: 0.95rem; outline: none; }
        .input-wrapper input:focus { border-color: var(--brand-primary); }

        /* --- APP RUNTIME STRUCTURAL FRAME --- */
        #app-runtime-frame { display: flex; height: 100vh; width: 100vw; }
        
        /* SIDEBAR INTERACTIVE SCHEMATICS */
        #sidebar-navigation { width: 300px; background-color: var(--bg-sidebar); color: #ffffff; display: flex; flex-direction: column; height: 100%; flex-shrink: 0; border-right: 1px solid var(--border-color); }
        .sidebar-title { padding: 25px 20px; font-weight: 800; font-size: 1.15rem; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .sidebar-title span { color: var(--brand-primary); }
        .sidebar-menu-scroller { flex-grow: 1; overflow-y: auto; padding: 12px; }
        .schema-section-label { font-size: 0.68rem; font-weight: 700; text-transform: uppercase; color: #64748b; padding: 15px 10px 6px 10px; letter-spacing: 1.2px; }
        
        .node-wrapper { margin-bottom: 3px; }
        .node-header { display: flex; align-items: center; justify-content: space-between; padding: 11px 14px; font-size: 0.88rem; color: var(--text-sidebar); cursor: pointer; border-radius: var(--radius-md); transition: background 0.2s; }
        .node-header:hover { background-color: var(--bg-sidebar-active); color: #ffffff; }
        .node-wrapper.open .node-header { background-color: var(--bg-sidebar-active); color: #ffffff; }
        .node-chevron { font-size: 0.7rem; transition: transform 0.2s; }
        .node-wrapper.open .node-chevron { transform: rotate(90deg); }
        
        .nested-sub-menu { max-height: 0; overflow: hidden; background-color: rgba(0,0,0,0.15); padding-left: 8px; transition: max-height 0.2s ease-out; }
        .node-wrapper.open .nested-sub-menu { max-height: 400px; }
        .nested-item-link { display: block; padding: 9px 14px; font-size: 0.82rem; color: var(--text-sidebar); text-decoration: none; cursor: pointer; border-radius: var(--radius-sm); }
        .nested-item-link:hover, .nested-item-link.active-node { color: #ffffff; background-color: rgba(255,255,255,0.05); }
        .nested-item-link.active-node { color: var(--brand-primary); font-weight: 700; border-left: 2px solid var(--brand-primary); }

        /* CONTENT DISPLAY VIEWPORT */
        #workspace-viewport-canvas { flex-grow: 1; display: flex; flex-direction: column; height: 100%; overflow: hidden; }
        #global-header-bar { height: 70px; background-color: var(--bg-surface); border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; padding: 0 30px; flex-shrink: 0; }
        .viewport-body { padding: 30px; overflow-y: auto; flex-grow: 1; }
        .view-segment-panel { display: none; animation: panelFade 0.15s ease-out; }
        .view-segment-panel.active-panel { display: block; }
        @keyframes panelFade { from { opacity: 0; transform: translateY(2px); } to { opacity: 1; transform: translateY(0); } }

        .heading-block { margin-bottom: 25px; }
        .heading-block h1 { font-size: 1.6rem; font-weight: 800; letter-spacing: -0.5px; }
        .heading-block p { color: var(--text-muted); font-size: 0.9rem; margin-top: 2px; }

        /* GRID MATRIX AND CARD STYLING */
        .kpi-matrix-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 25px; }
        .kpi-relational-card { background-color: var(--bg-surface); border: 1px solid var(--border-color); padding: 18px; border-radius: var(--radius-lg); }
        .kpi-relational-card .entity-tag { font-size: 0.72rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; }
        .kpi-relational-card .metric-total { font-size: 1.6rem; font-weight: 800; margin-top: 4px; }
        
        .er-content-container { background-color: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 24px; margin-bottom: 24px; }
        .er-container-title { font-size: 1rem; font-weight: 700; margin-bottom: 18px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color); padding-bottom: 10px; }

        /* DATA INTERACTIVE REGISTRY MATRIX TABLES */
        .table-scroll-wrapper { overflow-x: auto; border: 1px solid var(--border-color); border-radius: var(--radius-md); background: var(--bg-surface); }
        .er-schema-table { width: 100%; border-collapse: collapse; font-size: 0.88rem; text-align: left; }
        .er-schema-table th { background-color: var(--bg-canvas); color: var(--text-secondary); font-weight: 600; padding: 12px 14px; border-bottom: 1px solid var(--border-color); font-size: 0.72rem; text-transform: uppercase; }
        .er-schema-table td { padding: 12px 14px; border-bottom: 1px solid var(--border-color); color: var(--text-primary); vertical-align: middle; }
        .er-schema-table tr:hover td { background-color: rgba(220, 38, 38, 0.01); }

        /* FORM COMPONENT PARAMETERS */
        .form-grid-layout { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 16px; }
        .input-field-element { display: flex; flex-direction: column; gap: 6px; }
        .input-field-element label { font-size: 0.8rem; font-weight: 700; color: var(--text-secondary); text-transform: uppercase; }
        .input-field-element input, .input-field-element select, .input-field-element textarea { padding: 10px; border: 1px solid var(--border-color); border-radius: var(--radius-md); background-color: var(--bg-canvas); color: var(--text-primary); outline: none; font-size: 0.9rem; }

        /* ACTION BUTTON PLATFORMS */
        .btn-action { padding: 9px 16px; font-size: 0.82rem; font-weight: 600; border-radius: var(--radius-md); border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; transition: background 0.2s; }
        .btn-primary-action { background-color: var(--brand-primary); color: #ffffff; }
        .btn-primary-action:hover { opacity: 0.95; }
        .btn-secondary-action { background-color: var(--brand-secondary); color: #ffffff; }
        .btn-danger-action { background-color: #ef4444; color: #ffffff; }
        .btn-danger-action:hover { background-color: #b91c1c; }
        
        /* STATUS BADGE MODIFIERS */
        .status-badge { padding: 4px 10px; border-radius: 50px; font-size: 0.75rem; font-weight: 600; display: inline-block; }
        .badge-success { background-color: rgba(16,185,129,0.15); color: #10b981; }
        .badge-pending { background-color: rgba(245,158,11,0.15); color: #f59e0b; }
        .badge-danger { background-color: rgba(239,68,68,0.15); color: #ef4444; }
        .badge-blue { background-color: rgba(30,58,138,0.15); color: #2563eb; }

        /* IMAGE MANAGEMENT CARDS GRID */
        .image-gallery-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 20px; }
        .gallery-card { border: 1px solid var(--border-color); border-radius: var(--radius-lg); overflow: hidden; background-color: var(--bg-surface); }
        .gallery-img-frame { height: 160px; background-size: cover; background-position: center; background-color: #e2e8f0; display: flex; align-items: center; justify-content: center; font-size: 2rem;}
        .gallery-meta { padding: 14px; }
        .gallery-meta h4 { font-size: 0.95rem; font-weight: 700; margin-bottom: 4px; }
        .gallery-meta p { font-size: 0.8rem; color: var(--text-muted); }

        .journal-row { padding: 10px 0; border-bottom: 1px solid var(--border-color); font-size: 0.82rem; }
        .journal-timestamp { font-weight: 700; color: var(--brand-secondary); margin-right: 6px; }
    </style>
</head>
<body>

    <!-- CORE SYSTEM RUNTIME VIEWPORT COMPONENT -->
    <div id="app-runtime-frame">
        
        <!-- MAIN NAVIGATION DRAWER -->
        <aside id="sidebar-navigation">
            <div class="sidebar-title">Nepal Core <span>Master</span></div>
            <div class="sidebar-menu-scroller">
                
                <div class="schema-section-label">System Diagnostics</div>
                <div class="node-wrapper open">
                    <div class="node-header" onclick="toggleSidebarNode(this)"><span>📊 Data Telemetry</span><span class="node-chevron">▶</span></div>
                    <div class="nested-sub-menu">
                        <div class="nested-item-link active-node" onclick="routeViewportChannel('er-dash', this)">Live Metric Matrix</div>
                    </div>
                </div>

                <div class="schema-section-label">Inventory & Logistics</div>
                <div class="node-wrapper">
                    <div class="node-header" onclick="toggleSidebarNode(this)"><span>🗺️ Package Management</span><span class="node-chevron">▶</span></div>
                    <div class="nested-sub-menu">
                        <div class="nested-item-link" onclick="routeViewportChannel('pkg-mgt', this)">Tour Catalog Registry</div>
                    </div>
                </div>

                <div class="schema-section-label">Original Diagram Entities</div>
                <div class="node-wrapper">
                    <div class="node-header" onclick="toggleSidebarNode(this)"><span>👥 Users & Bookings</span><span class="node-chevron">▶</span></div>
                    <div class="nested-sub-menu">
                        <div class="nested-item-link" onclick="routeViewportChannel('cust-mgt', this)">Customer Management</div>
                        <div class="nested-item-link" onclick="routeViewportChannel('bkg-mgt', this)">Booking Operations</div>
                        <div class="nested-item-link" onclick="routeViewportChannel('pay-mgt', this)">Payment Registry Logs</div>
                        <div class="nested-item-link" onclick="routeViewportChannel('cancel-mgt', this)">Cancellation Handlers</div>
                    </div>
                </div>

                <div class="schema-section-label">Advanced Added Sectors</div>
                
                <div class="node-wrapper">
                    <div class="node-header" onclick="toggleSidebarNode(this)"><span>🏨 Hotel Management</span><span class="node-chevron">▶</span></div>
                    <div class="nested-sub-menu">
                        <div class="nested-item-link" onclick="routeViewportChannel('hotel-add', this)">Register New Hotel</div>
                        <div class="nested-item-link" onclick="routeViewportChannel('hotel-list', this)">Rooms & Occupancy Logs</div>
                    </div>
                </div>

                <div class="node-wrapper">
                    <div class="node-header" onclick="toggleSidebarNode(this)"><span>✈️ Flight Infrastructure</span><span class="node-chevron">▶</span></div>
                    <div class="nested-sub-menu">
                        <div class="nested-item-link" onclick="routeViewportChannel('flight-add', this)">Inject Scheduled Flight</div>
                        <div class="nested-item-link" onclick="routeViewportChannel('flight-list', this)">Aviation Manifest Matrix</div>
                    </div>
                </div>

                <div class="node-wrapper">
                    <div class="node-header" onclick="toggleSidebarNode(this)"><span>🚌 Ground Fleet Logistics</span><span class="node-chevron">▶</span></div>
                    <div class="nested-sub-menu">
                        <div class="nested-item-link" onclick="routeViewportChannel('bus-add', this)">Deploy Ground Vehicle</div>
                        <div class="nested-item-link" onclick="routeViewportChannel('bus-list', this)">Transit Manifest Matrix</div>
                    </div>
                </div>

                <div class="schema-section-label">Communication & Social Assets</div>
                <div class="node-wrapper">
                    <div class="node-header" onclick="toggleSidebarNode(this)"><span>💬 Direct User Messages</span><span class="node-chevron">▶</span></div>
                    <div class="nested-sub-menu">
                        <div class="nested-item-link" onclick="routeViewportChannel('msg-mgt', this)">User Messaging Desk</div>
                    </div>
                </div>

                <div class="node-wrapper">
                    <div class="node-header" onclick="toggleSidebarNode(this)"><span>📸 Media & Feedback Review</span><span class="node-chevron">▶</span></div>
                    <div class="nested-sub-menu">
                        <div class="nested-item-link" onclick="routeViewportChannel('img-mgt', this)">Image Storage Assets</div>
                        <div class="nested-item-link" onclick="routeViewportChannel('rating-mgt', this)">Ratings & Experience Reviews</div>
                    </div>
                </div>

            </div>
        </aside>

        <!-- CONTAINER WORKSPACE PANELS -->
        <main id="workspace-viewport-canvas">
            <header id="global-header-bar">
                <div style="font-size: 0.88rem; font-weight: 500; color: var(--text-secondary);"><code id="active_admin_display_tag">Admin: <?= htmlspecialchars($user['name'] ?? $user['email']) ?></code></div>
                <div style="display: flex; align-items: center; gap: 14px;">
                    <button class="btn-action" onclick="toggleColorMode()" style="background: none; border: 1px solid var(--border-color);">🌓 Toggle UI Theme</button>
                    <a class="btn-action btn-secondary-action" href="index.php" style="text-decoration:none;">Back to Site</a>
                    <a class="btn-action btn-danger-action" href="logout.php" style="text-decoration:none;">Logout</a>
                </div>
            </header>

            <div class="viewport-body">
                
                <!-- DIAGNOSTIC OVERVIEW PANEL -->
                <div id="view-er-dash" class="view-segment-panel active-panel">
                    <div class="heading-block">
                        <h1>Master Enterprise Diagnostic Hub</h1>
                        <p>Complete control matrix mapping core relational databases, added infrastructure lines, and active consumer transaction items.</p>
                    </div>
                    <div class="kpi-matrix-row">
                        <div class="kpi-relational-card"><div class="entity-tag">Active Live Packages</div><div class="metric-total" id="kpi-pkg-count">0</div></div>
                        <div class="kpi-relational-card"><div class="entity-tag">Hotels Configured</div><div class="metric-total" id="kpi-hotel-count">0</div></div>
                        <div class="kpi-relational-card"><div class="entity-tag">Flight Sectors Tracked</div><div class="metric-total" id="kpi-flight-count">0</div></div>
                        <div class="kpi-relational-card"><div class="entity-tag">Ground Fleet Assets</div><div class="metric-total" id="kpi-bus-count">0</div></div>
                    </div>

                    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
                        <div class="er-content-container">
                            <div class="er-container-title">Cross-Sector Resource Allocation Balance Volatility</div>
                            <div style="position: relative; height: 260px;"><canvas id="masterAllocationChartEngine"></canvas></div>
                        </div>
                        <div class="er-content-container">
                            <div class="er-container-title">Live Admin Control Action Monitor</div>
                            <div id="adminMasterSystemLoggerStream" style="max-height: 240px; overflow-y:auto; font-family: monospace;"></div>
                        </div>
                    </div>
                </div>

                <!-- PACKAGE MANAGEMENT INTENT HUB -->
                <div id="view-pkg-mgt" class="view-segment-panel">
                    <div class="heading-block"><h1>Tour Package Catalog Registry</h1><p>Provision and configure dynamic multi-day travel products (1-Day, 2-Day, 5-Day itineraries).</p></div>
                    <div class="er-content-container">
                        <form onsubmit="insertPackagePipeline(event)">
                            <div class="form-grid-layout">
                                <div class="input-field-element"><label>Package Name</label><input type="text" id="pkg_name" placeholder="e.g., Pokhara Paragliding Adventure" required></div>
                                <div class="input-field-element"><label>Duration Frame</label><select id="pkg_duration"><option value="1 Day">1 Day Quick Excursion</option><option value="2 Days">2 Days Weekend Getaway</option><option value="5 Days">5 Days Comprehensive Journey</option></select></div>
                                <div class="input-field-element"><label>Base Value Cost (NPR)</label><input type="number" id="pkg_cost" placeholder="25000" required></div>
                                <div class="input-field-element"><label>Primary Destination Hub</label><input type="text" id="pkg_destination" placeholder="e.g., Pokhara / Kathmandu" required></div>
                            </div>
                            <div class="input-field-element" style="margin-top: 12px; margin-bottom: 16px;"><label>Itinerary Abstract Timeline Summary</label><textarea id="pkg_itinerary" rows="2" placeholder="Day 1: Arrival & Briefing. Day 2: Trek Termination..." required></textarea></div>
                            <button type="submit" class="btn-action btn-primary-action">Commit New Package Configuration Row</button>
                        </form>
                    </div>
                    <div class="er-content-container">
                        <div class="table-scroll-wrapper"><table class="er-schema-table" id="table-package-node"><thead><tr><th>Package_ID (PK)</th><th>Name</th><th>Duration</th><th>Base Cost</th><th>Destination</th><th>Itinerary Plan</th><th>Actions</th></tr></thead><tbody></tbody></table></div>
                    </div>
                </div>

                <!-- CUSTOMER MANAGEMENT PANEL -->
                <div id="view-cust-mgt" class="view-segment-panel">
                    <div class="heading-block"><h1>Customer Entity Node Configuration</h1><p>Add, trace, or remove registered user variables.</p></div>
                    <div class="er-content-container">
                        <form onsubmit="insertCustomerPipeline(event)">
                            <div class="form-grid-layout">
                                <div class="input-field-element"><label>Full Name</label><input type="text" id="cust_name" required></div>
                                <div class="input-field-element"><label>Email Address</label><input type="email" id="cust_email" required></div>
                                <div class="input-field-element"><label>Contact Phone Line</label><input type="text" id="cust_phone" required></div>
                                <div class="input-field-element"><label>Residential Address</label><input type="text" id="cust_address" required></div>
                            </div>
                            <button type="submit" class="btn-action btn-primary-action">Commit New Customer Entity Row</button>
                        </form>
                    </div>
                    <div class="er-content-container">
                        <div class="table-scroll-wrapper"><table class="er-schema-table" id="table-customer-node"><thead><tr><th>Customer_ID (PK)</th><th>Name</th><th>Email</th><th>Phone</th><th>Address</th><th>Operations Actions</th></tr></thead><tbody></tbody></table></div>
                    </div>
                </div>

                <!-- BOOKING MANAGEMENT PANEL -->
                <div id="view-bkg-mgt" class="view-segment-panel">
                    <div class="heading-block"><h1>Booking Transaction Node Records</h1><p>Admin master override tools for managing active trip package requests.</p></div>
                    <div class="er-content-container">
                        <form onsubmit="insertBookingPipeline(event)">
                            <div class="form-grid-layout">
                                <div class="input-field-element"><label>Select Target Customer Reference</label><select id="bkg_cust_fk"></select></div>
                                <div class="input-field-element"><label>Select Core Tour Package (FK Link)</label><select id="bkg_pkg_fk"></select></div>
                                <div class="input-field-element"><label>Scheduling Date</label><input type="date" id="bkg_date" required></div>
                                <div class="input-field-element"><label>Operational Processing State Status</label><select id="bkg_status"><option value="Pending Allocation Review">Pending Allocation Review</option><option value="Confirmed & Secured">Confirmed & Secured</option></select></div>
                            </div>
                            <button type="submit" class="btn-action btn-primary-action">Commit Booking Row Entry</button>
                        </form>
                    </div>
                    <div class="er-content-container">
                        <div class="table-scroll-wrapper"><table class="er-schema-table" id="table-booking-node"><thead><tr><th>Booking_ID (PK)</th><th>Customer ID</th><th>Relational Package Row</th><th>Date</th><th>Tracking Status State</th><th>Operational Actions</th></tr></thead><tbody></tbody></table></div>
                    </div>
                </div>

                <!-- PAYMENT LEDGER PANEL -->
                <div id="view-pay-mgt" class="view-segment-panel">
                    <div class="heading-block"><h1>Payment Registry Balancing Logs</h1><p>Verify ledger funds and switch clearance items from pending loops to successful processing states.</p></div>
                    <div class="er-content-container">
                        <form onsubmit="insertPaymentPipeline(event)">
                            <div class="form-grid-layout">
                                <div class="input-field-element"><label>Target Booking Reference Code</label><select id="pay_bkg_fk"></select></div>
                                <div class="input-field-element"><label>Amount (NPR)</label><input type="number" id="pay_amount" required></div>
                                <div class="input-field-element"><label>Clearing Method</label><select id="pay_method"><option value="eSewa Wallet Connection">eSewa Wallet Connection</option><option value="Khalti Digital Portal">Khalti Digital Portal</option><option value="Bank SWIFT Wire Transfer">Bank SWIFT Wire Transfer</option></select></div>
                                <div class="input-field-element"><label>Current Ledger Status</label><select id="pay_status"><option value="Pending System Clearance">Pending System Clearance</option><option value="Successful Settlement Verified">Successful Settlement Verified</option></select></div>
                            </div>
                            <button type="submit" class="btn-action btn-primary-action">Record Incoming Payment Entry</button>
                        </form>
                    </div>
                    <div class="er-content-container">
                        <div class="table-scroll-wrapper"><table class="er-schema-table" id="table-payment-node"><thead><tr><th>Payment_ID (PK)</th><th>Booking Reference</th><th>Valuation Amount</th><th>Method</th><th>Clearance State</th><th>Operational Actions</th></tr></thead><tbody></tbody></table></div>
                    </div>
                </div>

                <!-- CANCELLATION HANDLERS PANEL -->
                <div id="view-cancel-mgt" class="view-segment-panel">
                    <div class="heading-block"><h1>Cancellation Handling Systems</h1><p>Tracks trip rollbacks and transaction voids.</p></div>
                    <div class="er-content-container">
                        <div class="table-scroll-wrapper"><table class="er-schema-table" id="table-cancellation-node"><thead><tr><th>Cancel_ID (PK)</th><th>Booking Reference ID</th><th>Log Execution Date</th><th>Stated Reason Message</th><th>Operational Actions</th></tr></thead><tbody></tbody></table></div>
                    </div>
                </div>

                <!-- HOTEL SECTOR: INSERT ENTRY FORM -->
                <div id="view-hotel-add" class="view-segment-panel">
                    <div class="heading-block"><h1>Register New Hospitality Property Entity</h1><p>Insert hotel properties into your travel catalog maps.</p></div>
                    <div class="er-content-container">
                        <form onsubmit="insertHotelPipeline(event)">
                            <div class="form-grid-layout">
                                <div class="input-field-element"><label>Property Resort Name</label><input type="text" id="hotel_name" placeholder="e.g., Pokhara Shangri-La Oasis Resort" required></div>
                                <div class="input-field-element"><label>Geographic Region Location</label><input type="text" id="hotel_location" placeholder="e.g., Lakeside Sector-6, Pokhara" required></div>
                                <div class="input-field-element"><label>Base Tier Pricing (Per Night NPR)</label><input type="number" id="hotel_price" placeholder="14500" required></div>
                                <div class="input-field-element"><label>Room Class Allocations Status</label><select id="hotel_status"><option value="Available Active Supply">Available Active Supply</option><option value="Pending Safety Audits Room Allocation">Pending Safety Audits Room Allocation</option><option value="Fully Booked">Fully Booked</option></select></div>
                            </div>
                            <button type="submit" class="btn-action btn-primary-action">Commit Hotel Structure to Database</button>
                        </form>
                    </div>
                </div>

                <!-- HOTEL SECTOR: DATA MATRIX GRID -->
                <div id="view-hotel-list" class="view-segment-panel">
                    <div class="heading-block"><h1>Hotels & Rooms Allocation Matrix Table</h1><p>Comprehensive overview monitoring hospitality structures.</p></div>
                    <div class="er-content-container">
                        <div class="table-scroll-wrapper"><table class="er-schema-table" id="table-hotel-node"><thead><tr><th>Hotel_ID (PK)</th><th>Resort Brand Name</th><th>Geographic Location Zone</th><th>Base Pricing Model</th><th>Capacity Allocation State</th><th>Operational Database Actions</th></tr></thead><tbody></tbody></table></div>
                    </div>
                </div>

                <!-- FLIGHT SECTOR: INSERT ROUTE FORM -->
                <div id="view-flight-add" class="view-segment-panel">
                    <div class="heading-block"><h1>Aviation Route Configuration Engine</h1><p>Provision new flights for transit networks.</p></div>
                    <div class="er-content-container">
                        <form onsubmit="insertFlightPipeline(event)">
                            <div class="form-grid-layout">
                                <div class="input-field-element"><label>Airlines Carrier Corporate Handle</label><input type="text" id="flight_carrier" placeholder="e.g., Buddha Air / Yeti Airlines" required></div>
                                <div class="input-field-element"><label>Flight Identifier Code Number</label><input type="text" id="flight_code" placeholder="e.g., YT-421" required></div>
                                <div class="input-field-element"><label>Sector Route Corridor Path</label><input type="text" id="flight_sector" placeholder="e.g., KTM Airport -> PKR Runway" required></div>
                                <div class="input-field-element"><label>Airspace Manifest Dispatch Status</label><select id="flight_status"><option value="Active Runway Scheduled">Active Runway Scheduled</option><option value="Pending Airspace Weather Review">Pending Airspace Weather Review</option></select></div>
                            </div>
                            <button type="submit" class="btn-action btn-primary-action">Inject Aviation Route Row</button>
                        </form>
                    </div>
                </div>

                <!-- FLIGHT SECTOR: DATA MATRIX -->
                <div id="view-flight-list" class="view-segment-panel">
                    <div class="heading-block"><h1>Aviation Network Core Fleet Manifest Table</h1><p>Maintains scheduled flight details.</p></div>
                    <div class="er-content-container">
                        <div class="table-scroll-wrapper"><table class="er-schema-table" id="table-flight-node"><thead><tr><th>Flight_ID (PK)</th><th>Airlines Carrier Company</th><th>Flight Code No</th><th>Sector Route Path</th><th>Logistics Status State</th><th>Operational Database Actions</th></tr></thead><tbody></tbody></table></div>
                    </div>
                </div>

                <!-- BUS SECTOR: INSERT VEHICLE FORM -->
                <div id="view-bus-add" class="view-segment-panel">
                    <div class="heading-block"><h1>Deploy Tourist Ground Vehicle</h1><p>Register transport units for tourist routes.</p></div>
                    <div class="er-content-container">
                        <form onsubmit="insertBusPipeline(event)">
                            <div class="form-grid-layout">
                                <div class="input-field-element"><label>Bus Fleet Unit License Plate ID</label><input type="text" id="bus_plate" placeholder="e.g., BA-PA-8812 Deluxe" required></div>
                                <div class="input-field-element"><label>Assigned Lead Transit Operator Driver</label><input type="text" id="bus_driver" placeholder="e.g., Hari Narayan Shrestha" required></div>
                                <div class="input-field-element"><label>Highway Travel Corridor Sector Path</label><input type="text" id="bus_corridor" placeholder="e.g., Kathmandu Ring Road Hub -> Sauraha Chitwan Park Gate" required></div>
                                <div class="input-field-element"><label>Vehicle Operations Transit Status</label><select id="bus_status"><option value="Active Route Operational">Active Route Operational</option><option value="Pending Mechanical Safety Check">Pending Mechanical Safety Check</option></select></div>
                            </div>
                            <button type="submit" class="btn-action btn-primary-action">Deploy Vehicle Unit to Network Grid</button>
                        </form>
                    </div>
                </div>

                <!-- BUS SECTOR: DATA MATRIX -->
                <div id="view-bus-list" class="view-segment-panel">
                    <div class="heading-block"><h1>Ground Fleet Manifest Table</h1><p>Maintains scheduled transits across overland routes.</p></div>
                    <div class="er-content-container">
                        <div class="table-scroll-wrapper"><table class="er-schema-table" id="table-bus-node"><thead><tr><th>Bus_ID (PK)</th><th>License Plate</th><th>Lead Driver Name</th><th>Highway Travel Corridor Sector Path</th><th>Transit Status</th><th>Operational Database Actions</th></tr></thead><tbody></tbody></table></div>
                    </div>
                </div>

                <!-- USER MESSAGING DESK -->
                <div id="view-msg-mgt" class="view-segment-panel">
                    <div class="heading-block"><h1>User Messaging Support Core</h1><p>Direct communication triage desk for responding to user requests.</p></div>
                    <div class="er-content-container">
                        <form onsubmit="insertMessagePipeline(event)">
                            <div class="form-grid-layout">
                                <div class="input-field-element"><label>Sender Identity</label><input type="text" id="msg_sender" placeholder="Guest / Registered User Name" required></div>
                                <div class="input-field-element"><label>Context Message String Content</label><textarea id="msg_content" rows="2" placeholder="Write query details here..." required></textarea></div>
                            </div>
                            <button type="submit" class="btn-action btn-primary-action">Dispatch Thread Logs</button>
                        </form>
                    </div>
                    <div class="er-content-container">
                        <div class="table-scroll-wrapper"><table class="er-schema-table" id="table-msg-node"><thead><tr><th>Msg_ID (PK)</th><th>Sender</th><th>Message Thread Extract</th><th>Timestamp</th><th>Operational Actions</th></tr></thead><tbody></tbody></table></div>
                    </div>
                </div>

                <!-- IMAGE STORAGE ASSETS -->
                <div id="view-img-mgt" class="view-segment-panel">
                    <div class="heading-block"><h1>Image Object Cloud Storage Buckets</h1><p>Relational links indexing geographic visual resources for digital catalogs.</p></div>
                    <div class="er-content-container">
                        <form onsubmit="insertImagePipeline(event)">
                            <div class="form-grid-layout">
                                <div class="input-field-element"><label>Resource Title Label Tag</label><input type="text" id="img_title" placeholder="e.g., Mount Everest Basecamp Panoramic" required></div>
                                <div class="input-field-element"><label>Mock Asset Vector Icon (Emoji)</label><input type="text" id="img_emoji" placeholder="🏔️, 🛕, 🪵, waves" value="🏔️" required></div>
                                <div class="input-field-element"><label>Resolution Category Grid</label><select id="img_resolution"><option value="Ultra-HD 4K Asset">Ultra-HD 4K Asset</option><option value="Standard Web compressed Dynamic Linear">Standard Web compressed Dynamic Linear</option></select></div>
                            </div>
                            <button type="submit" class="btn-action btn-primary-action">Index Static Asset Link</button>
                        </form>
                    </div>
                    <div class="image-gallery-grid" id="image-gallery-render-row"></div>
                </div>

                <!-- RATINGS AND REVIEWS -->
                <div id="view-rating-mgt" class="view-segment-panel">
                    <div class="heading-block"><h1>Consumer Experience Feedback Index</h1><p>Relational star reviews linking customer satisfaction indexes back onto booked itineraries.</p></div>
                    <div class="er-content-container">
                        <div class="table-scroll-wrapper"><table class="er-schema-table" id="table-rating-node"><thead><tr><th>Review_ID (PK)</th><th>Itinerary Reference</th><th>Rating Tier Metric</th><th>Stated Analysis Message</th><th>Actions</th></tr></thead><tbody></tbody></table></div>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <!-- CORE DESK REACTION INTERACTIVITY APPLICATION LOGIC SCRIPT ENGINE -->
    <script>
        // --- REACTIVE STATE MANAGEMENT AND DATABASE STORE INITIALIZATION ---
        let DB = JSON.parse(localStorage.getItem('nepal_travel_db')) || {
            packages: [
                {id: "PKG-5001", name: "Kathmandu Valley Cultural Day Out", duration: "1 Day", cost: 6500, destination: "Kathmandu", itinerary: "Day 1: Pashupatinath, Swayambhunath, and Patan Durbar Square evening exploration route."},
                {id: "PKG-5002", name: "Nagarkot Sunrise Escape", duration: "2 Days", cost: 14000, destination: "Nagarkot", itinerary: "Day 1: Drive to Nagarkot & Sunset view. Day 2: 5:00 AM Sunrise observation over the Langtang Range, return via Bhaktapur."},
                {id: "PKG-5005", name: "Pokhara Elite Leisure Odyssey", duration: "5 Days", cost: 48000, destination: "Pokhara", itinerary: "Day 1: Luxury coach transit. Day 2: Sarangkot Sunrise & Cave tours. Day 3: Phewa Lake boating & Peace Pagoda. Day 4: Pumdikot Shiva Statue excursion. Day 5: Return corridor flyback."}
            ],
            customers: [
                {id: "CUST-1001", name: "Anish Sharma", email: "anish@domain.np", phone: "+977-9851012345", address: "Balaju-16, Kathmandu"},
                {id: "CUST-1002", name: "Sophia Taylor", email: "sophia.t@global.com", phone: "+1-555-019921", address: "California, USA"}
            ],
            bookings: [
                {id: "BKG-9001", customer_id: "CUST-1001", package_id: "PKG-5005", date: "2026-03-15", status: "Confirmed & Secured"},
                {id: "BKG-9002", customer_id: "CUST-1002", package_id: "PKG-5002", date: "2026-04-02", status: "Pending Allocation Review"}
            ],
            payments: [
                {id: "PAY-4041", booking_id: "BKG-9001", amount: 48000, method: "eSewa Wallet Connection", status: "Successful Settlement Verified"}
            ],
            cancellations: [
                {id: "CNL-2011", booking_id: "BKG-9002", date: "2026-02-10", reason: "Flight re-route changes by customer international line rescheduling."}
            ],
            hotels: [
                {id: "HTL-301", name: "Pokhara Shangri-La Oasis Resort", location: "Lakeside Sector-6, Pokhara", price: 14500, status: "Available Active Supply"},
                {id: "HTL-302", name: "Himalayan Horizon View Lodge", location: "Dhulikhel Outpost", price: 9200, status: "Available Active Supply"}
            ],
            flights: [
                {id: "FLT-701", carrier: "Buddha Air", code: "U4-501", sector: "KTM Airport -> PKR Runway", status: "Active Runway Scheduled"},
                {id: "FLT-702", carrier: "Yeti Airlines", code: "YT-422", sector: "KTM Airport -> BWP Terai Hub", status: "Pending Airspace Weather Review"}
            ],
            buses: [
                {id: "BUS-801", plate: "BA-PA-8812 Deluxe", driver: "Hari Narayan Shrestha", corridor: "Kathmandu Ring Road Hub -> Sauraha Chitwan Park Gate", status: "Active Route Operational"}
            ],
            messages: [
                {id: "MSG-001", sender: "Ramesh Thapa", content: "Is the road corridor to Muktinath open for bus operations near Mustang during late March?", timestamp: "2026-06-27 14:21:05"}
            ],
            images: [
                {id: "IMG-501", title: "Phewa Lake Reflection", emoji: "🌊", resolution: "Ultra-HD 4K Asset"},
                {id: "IMG-502", title: "Boudhanath Stupa Serenity", emoji: "🛕", resolution: "Standard Web compressed Dynamic Linear"}
            ],
            ratings: [
                {id: "REV-601", itinerary_id: "BKG-9001", tier: "⭐⭐⭐⭐⭐", message: "Absolutely spectacular management orchestration. Guide was superb and hotels were clean."}
            ]
        };

        let allocationChartInstance = null;

        function saveState() {
            localStorage.setItem('nepal_travel_db', JSON.stringify(DB));
            refreshTelemetryVisualizations();
        }

        function pushSystemLog(message) {
            const stream = document.getElementById('adminMasterSystemLoggerStream');
            const row = document.createElement('div');
            row.className = 'journal-row';
            const time = new Date().toLocaleTimeString();
            row.innerHTML = `<span class="journal-timestamp">[${time}]</span> ${message}`;
            stream.prepend(row);
        }

        document.addEventListener('DOMContentLoaded', () => {
            pushSystemLog("Admin session authorized. Master sector mapping online.");
            initializeApplicationConsoleRuntime();
        });

        function toggleColorMode() {
            const body = document.documentElement;
            const current = body.getAttribute('data-theme');
            body.setAttribute('data-theme', current === 'dark' ? 'light' : 'dark');
            pushSystemLog(`UI theme toggled to: ${current === 'dark' ? 'light' : 'dark'}`);
            refreshTelemetryVisualizations();
        }

        // --- SIDEBAR ACCORDION AND NAVIGATION ROUTER ---
        function toggleSidebarNode(headerElement) {
            const wrapper = headerElement.parentElement;
            wrapper.classList.toggle('open');
        }

        function routeViewportChannel(targetPanelId, linkElement) {
            document.querySelectorAll('.view-segment-panel').forEach(panel => panel.classList.remove('active-panel'));
            document.querySelectorAll('.nested-item-link').forEach(link => link.classList.remove('active-node'));

            document.getElementById(`view-${targetPanelId}`).classList.add('active-panel');
            if(linkElement) linkElement.classList.add('active-node');
            
            pushSystemLog(`Routed workspace focus to area segment canvas channel: [${targetPanelId.toUpperCase()}]`);
        }

        // --- SYSTEM ENGINE INITIALIZER & REFRESH MANDATES ---
        function initializeApplicationConsoleRuntime() {
            renderPackageTable();
            renderCustomerTable();
            renderBookingTable();
            renderPaymentTable();
            renderCancellationTable();
            renderHotelTable();
            renderFlightTable();
            renderBusTable();
            renderMessageTable();
            renderImageGallery();
            renderRatingTable();
            repopulateCascadingSelectDropdowns();
            refreshTelemetryVisualizations();
        }

        function repopulateCascadingSelectDropdowns() {
            const bkgCustSelect = document.getElementById('bkg_cust_fk');
            bkgCustSelect.innerHTML = DB.customers.map(c => `<option value="${c.id}">${c.name} (${c.id})</option>`).join('');

            const bkgPkgSelect = document.getElementById('bkg_pkg_fk');
            bkgPkgSelect.innerHTML = DB.packages.map(p => `<option value="${p.id}">${p.name} [${p.duration}]</option>`).join('');

            const paySelect = document.getElementById('pay_bkg_fk');
            paySelect.innerHTML = DB.bookings.map(b => {
                const pkg = DB.packages.find(p => p.id === b.package_id) || { name: 'Unknown Package' };
                return `<option value="${b.id}">${b.id} - ${pkg.name}</option>`;
            }).join('');
        }

        function refreshTelemetryVisualizations() {
            document.getElementById('kpi-pkg-count').innerText = DB.packages.length;
            document.getElementById('kpi-hotel-count').innerText = DB.hotels.length;
            document.getElementById('kpi-flight-count').innerText = DB.flights.length;
            document.getElementById('kpi-bus-count').innerText = DB.buses.length;

            const ctx = document.getElementById('masterAllocationChartEngine').getContext('2d');
            const counts = [
                DB.packages.length, DB.customers.length, DB.bookings.length, 
                DB.payments.length, DB.hotels.length, DB.flights.length, DB.buses.length
            ];

            if(allocationChartInstance) {
                allocationChartInstance.destroy();
            }

            const isDark = document.documentElement.getAttribute('data-theme') === 'dark';

            allocationChartInstance = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Packages', 'Customers', 'Bookings', 'Payments', 'Hotels', 'Flights', 'Buses'],
                    datasets: [{
                        label: 'Operational Table Metrics Configuration Count Rows',
                        data: counts,
                        backgroundColor: isDark ? '#dc2626' : '#1e3a8a',
                        borderColor: isDark ? '#b91c1c' : '#0f172a',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { labels: { color: isDark ? '#cbd5e1' : '#0f172a' } }
                    },
                    scales: {
                        x: { grid: { display: false }, ticks: { color: isDark ? '#94a3b8' : '#475569' } },
                        y: { grid: { color: isDark ? '#1e293b' : '#e2e8f0' }, ticks: { color: isDark ? '#94a3b8' : '#475569', beginAtZero: true } }
                    }
                }
            });
        }

        // --- PIPELINE COMPONENT INSERTION METHODS ---
        function insertPackagePipeline(e) {
            e.preventDefault();
            const newPkg = {
                id: `PKG-${Math.floor(5000 + Math.random() * 999)}`,
                name: document.getElementById('pkg_name').value,
                duration: document.getElementById('pkg_duration').value,
                cost: Number(document.getElementById('pkg_cost').value),
                destination: document.getElementById('pkg_destination').value,
                itinerary: document.getElementById('pkg_itinerary').value
            };
            DB.packages.push(newPkg);
            saveState();
            renderPackageTable();
            repopulateCascadingSelectDropdowns();
            pushSystemLog(`Committed Tour Package: ${newPkg.id} | ${newPkg.name} (${newPkg.duration})`);
            e.target.reset();
        }

        function insertCustomerPipeline(e) {
            e.preventDefault();
            const newCust = {
                id: `CUST-${Math.floor(1000 + Math.random() * 9000)}`,
                name: document.getElementById('cust_name').value,
                email: document.getElementById('cust_email').value,
                phone: document.getElementById('cust_phone').value,
                address: document.getElementById('cust_address').value
            };
            DB.customers.push(newCust);
            saveState();
            renderCustomerTable();
            repopulateCascadingSelectDropdowns();
            pushSystemLog(`Committed Customer Row: ${newCust.id} | ${newCust.name}`);
            e.target.reset();
        }

        function insertBookingPipeline(e) {
            e.preventDefault();
            const newBkg = {
                id: `BKG-${Math.floor(1000 + Math.random() * 9000)}`,
                customer_id: document.getElementById('bkg_cust_fk').value,
                package_id: document.getElementById('bkg_pkg_fk').value,
                date: document.getElementById('bkg_date').value,
                status: document.getElementById('bkg_status').value
            };
            DB.bookings.push(newBkg);
            saveState();
            renderBookingTable();
            repopulateCascadingSelectDropdowns();
            pushSystemLog(`Committed Booking Row Trace: ${newBkg.id} mapped to Package ${newBkg.package_id}`);
            e.target.reset();
        }

        function insertPaymentPipeline(e) {
            e.preventDefault();
            const newPay = {
                id: `PAY-${Math.floor(1000 + Math.random() * 9000)}`,
                booking_id: document.getElementById('pay_bkg_fk').value,
                amount: Number(document.getElementById('pay_amount').value),
                method: document.getElementById('pay_method').value,
                status: document.getElementById('pay_status').value
            };
            DB.payments.push(newPay);
            saveState();
            renderPaymentTable();
            pushSystemLog(`Committed Payment Ledger balancing node item: ${newPay.id} for value NPR ${newPay.amount}`);
            e.target.reset();
        }

        function insertHotelPipeline(e) {
            e.preventDefault();
            const newHotel = {
                id: `HTL-${Math.floor(100 + Math.random() * 900)}`,
                name: document.getElementById('hotel_name').value,
                location: document.getElementById('hotel_location').value,
                price: Number(document.getElementById('hotel_price').value),
                status: document.getElementById('hotel_status').value
            };
            DB.hotels.push(newHotel);
            saveState();
            renderHotelTable();
            pushSystemLog(`Committed Hospitality Resource Node item: ${newHotel.id} [${newHotel.name}]`);
            e.target.reset();
        }

        function insertFlightPipeline(e) {
            e.preventDefault();
            const newFlight = {
                id: `FLT-${Math.floor(100 + Math.random() * 900)}`,
                carrier: document.getElementById('flight_carrier').value,
                code: document.getElementById('flight_code').value,
                sector: document.getElementById('flight_sector').value,
                status: document.getElementById('flight_status').value
            };
            DB.flights.push(newFlight);
            saveState();
            renderFlightTable();
            pushSystemLog(`Committed Aviation Fleet Record Segment: ${newFlight.code} via ${newFlight.carrier}`);
            e.target.reset();
        }

        function insertBusPipeline(e) {
            e.preventDefault();
            const newBus = {
                id: `BUS-${Math.floor(100 + Math.random() * 900)}`,
                plate: document.getElementById('bus_plate').value,
                driver: document.getElementById('bus_driver').value,
                corridor: document.getElementById('bus_corridor').value,
                status: document.getElementById('bus_status').value
            };
            DB.buses.push(newBus);
            saveState();
            renderBusTable();
            pushSystemLog(`Committed Ground Transit vehicle entity alignment: ${newBus.plate}`);
            e.target.reset();
        }

        function insertMessagePipeline(e) {
            e.preventDefault();
            const newMsg = {
                id: `MSG-${Math.floor(100 + Math.random() * 900)}`,
                sender: document.getElementById('msg_sender').value,
                content: document.getElementById('msg_content').value,
                timestamp: new Date().toISOString().replace('T',' ').substring(0,19)
            };
            DB.messages.push(newMsg);
            saveState();
            renderMessageTable();
            pushSystemLog(`Injected Direct Communications Support thread row log: ${newMsg.id}`);
            e.target.reset();
        }

        function insertImagePipeline(e) {
            e.preventDefault();
            const newImg = {
                id: `IMG-${Math.floor(100 + Math.random() * 900)}`,
                title: document.getElementById('img_title').value,
                emoji: document.getElementById('img_emoji').value,
                resolution: document.getElementById('img_resolution').value
            };
            DB.images.push(newImg);
            saveState();
            renderImageGallery();
            pushSystemLog(`Indexed Static Asset Object allocation link: ${newImg.id}`);
            e.target.reset();
        }

        // --- DELETION DATA HANDLERS ---
        function deleteSchemaRow(sector, id) {
            if(confirm(`Execute relational deletion cascades override for entity signature block ID: [${id}]?`)) {
                DB[sector] = DB[sector].filter(item => item.id !== id);
                saveState();
                initializeApplicationConsoleRuntime();
                pushSystemLog(`Purged entity element node allocation item explicitly from sector [${sector.toUpperCase()}]: key code ${id}`);
            }
        }

        function triggerCancellationCascade(bkgId) {
            const reason = prompt("Enter official structural cause statement log entry for cancellation void processes:");
            if(!reason) return;

            const targetBkg = DB.bookings.find(b => b.id === bkgId);
            if(targetBkg) {
                targetBkg.status = "Voided & Cancelled";
                
                const newCancel = {
                    id: `CNL-${Math.floor(1000 + Math.random() * 9000)}`,
                    booking_id: bkgId,
                    date: new Date().toISOString().substring(0,10),
                    reason: reason
                };
                DB.cancellations.push(newCancel);
                saveState();
                initializeApplicationConsoleRuntime();
                pushSystemLog(`Relational cascade sequence tripped: ${bkgId} flagged VOID. Cancellation log item generated.`);
            }
        }

        // --- VIEWPORT TABLE RENDERING LAYOUTS ---
        function renderPackageTable() {
            const tbody = document.querySelector('#table-package-node tbody');
            tbody.innerHTML = DB.packages.map(p => `
                <tr>
                    <td><strong>${p.id}</strong></td>
                    <td><span class="status-badge badge-blue">${p.name}</span></td>
                    <td><strong>${p.duration}</strong></td>
                    <td>NPR ${p.cost.toLocaleString()}</td>
                    <td>${p.destination}</td>
                    <td><p style="max-width:280px; font-size:0.8rem; color:var(--text-secondary);">${p.itinerary}</p></td>
                    <td><button class="btn-action btn-danger-action" onclick="deleteSchemaRow('packages', '${p.id}')">Delete</button></td>
                </tr>
            `).join('');
        }

        function renderCustomerTable() {
            const tbody = document.querySelector('#table-customer-node tbody');
            tbody.innerHTML = DB.customers.map(c => `
                <tr>
                    <td><strong>${c.id}</strong></td>
                    <td>${c.name}</td>
                    <td><code>${c.email}</code></td>
                    <td>${c.phone}</td>
                    <td><small>${c.address}</small></td>
                    <td><button class="btn-action btn-danger-action" onclick="deleteSchemaRow('customers', '${c.id}')">Drop Row</button></td>
                </tr>
            `).join('');
        }

        function renderBookingTable() {
            const tbody = document.querySelector('#table-booking-node tbody');
            tbody.innerHTML = DB.bookings.map(b => {
                const pkg = DB.packages.find(p => p.id === b.package_id) || { name: 'Deleted Package' };
                return `
                    <tr>
                        <td><strong>${b.id}</strong></td>
                        <td><code>${b.customer_id}</code></td>
                        <td><strong>${pkg.name}</strong></td>
                        <td>${b.date}</td>
                        <td><span class="status-badge ${b.status.includes('Confirmed') ? 'badge-success' : b.status.includes('Voided') ? 'badge-danger' : 'badge-pending'}">${b.status}</span></td>
                        <td>
                            ${b.status.includes('Confirmed') || b.status.includes('Review') ? `<button class="btn-action btn-danger-action" onclick="triggerCancellationCascade('${b.id}')">Void & Rollback</button>` : `<small style="color:var(--text-muted)">No actions available</small>`}
                        </td>
                    </tr>
                `;
            }).join('');
        }

        function renderPaymentTable() {
            const tbody = document.querySelector('#table-payment-node tbody');
            tbody.innerHTML = DB.payments.map(p => `
                <tr>
                    <td><strong>${p.id}</strong></td>
                    <td><code>${p.booking_id}</code></td>
                    <td><strong>NPR ${p.amount.toLocaleString()}</strong></td>
                    <td><small>${p.method}</small></td>
                    <td><span class="status-badge ${p.status.includes('Settlement') ? 'badge-success' : 'badge-pending'}">${p.status}</span></td>
                    <td><button class="btn-action btn-danger-action" onclick="deleteSchemaRow('payments', '${p.id}')">Purge Entry</button></td>
                </tr>
            `).join('');
        }

        function renderCancellationTable() {
            const tbody = document.querySelector('#table-cancellation-node tbody');
            tbody.innerHTML = DB.cancellations.map(c => `
                <tr>
                    <td><strong>${c.id}</strong></td>
                    <td><code>${c.booking_id}</code></td>
                    <td>${c.date}</td>
                    <td><p style="max-width:300px; font-size:0.8rem; color:var(--text-secondary);">${c.reason}</p></td>
                    <td><button class="btn-action btn-danger-action" onclick="deleteSchemaRow('cancellations', '${c.id}')">Drop Record</button></td>
                </tr>
            `).join('');
        }

        function renderHotelTable() {
            const tbody = document.querySelector('#table-hotel-node tbody');
            tbody.innerHTML = DB.hotels.map(h => `
                <tr>
                    <td><strong>${h.id}</strong></td>
                    <td>${h.name}</td>
                    <td><small>${h.location}</small></td>
                    <td>NPR ${h.price}/night</td>
                    <td><span class="status-badge ${h.status.includes('Supply') ? 'badge-success' : 'badge-pending'}">${h.status}</span></td>
                    <td><button class="btn-action btn-danger-action" onclick="deleteSchemaRow('hotels', '${h.id}')">De-register</button></td>
                </tr>
            `).join('');
        }

        function renderFlightTable() {
            const tbody = document.querySelector('#table-flight-node tbody');
            tbody.innerHTML = DB.flights.map(f => `
                <tr>
                    <td><strong>${f.id}</strong></td>
                    <td>${f.carrier}</td>
                    <td><code>${f.code}</code></td>
                    <td><small>${f.sector}</small></td>
                    <td><span class="status-badge ${f.status.includes('Scheduled') ? 'badge-blue' : 'badge-pending'}">${f.status}</span></td>
                    <td><button class="btn-action btn-danger-action" onclick="deleteSchemaRow('flights', '${f.id}')">Cancel Flight Route</button></td>
                </tr>
            `).join('');
        }

        function renderBusTable() {
            const tbody = document.querySelector('#table-bus-node tbody');
            tbody.innerHTML = DB.buses.map(b => `
                <tr>
                    <td><strong>${b.id}</strong></td>
                    <td><code>${b.plate}</code></td>
                    <td>${b.driver}</td>
                    <td><small>${b.corridor}</small></td>
                    <td><span class="status-badge badge-success">${b.status}</span></td>
                    <td><button class="btn-action btn-danger-action" onclick="deleteSchemaRow('buses', '${b.id}')">Recall Fleet Unit</button></td>
                </tr>
            `).join('');
        }

        function renderMessageTable() {
            const tbody = document.querySelector('#table-msg-node tbody');
            tbody.innerHTML = DB.messages.map(m => `
                <tr>
                    <td><strong>${m.id}</strong></td>
                    <td><strong>${m.sender}</strong></td>
                    <td><p style="max-width:340px; font-size:0.8rem;">"${m.content}"</p></td>
                    <td><small>${m.timestamp}</small></td>
                    <td><button class="btn-action btn-secondary-action" onclick="alert('Support console integration pending API connection.')">Reply</button></td>
                </tr>
            `).join('');
        }

        // (Remaining render methods match previous structural states perfectly)
        function renderImageGallery() {
            const container = document.getElementById('image-gallery-render-row');
            container.innerHTML = DB.images.map(img => `
                <div class="gallery-card">
                    <div class="gallery-img-frame">${img.emoji}</div>
                    <div class="gallery-meta">
                        <h4>${img.title}</h4>
                        <p>${img.resolution}</p>
                        <button class="btn-action btn-danger-action" style="margin-top:10px; width:100%; justify-content:center; font-size:0.75rem;" onclick="deleteSchemaRow('images', '${img.id}')">Unlink Asset</button>
                    </div>
                </div>
            `).join('');
        }

        function renderRatingTable() {
            const tbody = document.querySelector('#table-rating-node tbody');
            tbody.innerHTML = DB.ratings.map(r => `
                <tr>
                    <td><strong>${r.id}</strong></td>
                    <td><code>${r.itinerary_id}</code></td>
                    <td style="letter-spacing:2px; color:#f59e0b;">${r.tier}</td>
                    <td><p style="max-width:350px; font-size:0.8rem; color:var(--text-secondary); italic">${r.message}</p></td>
                    <td><button class="btn-action btn-danger-action" onclick="deleteSchemaRow('ratings', '${r.id}')">Drop Review</button></td>
                </tr>
            `).join('');
        }
    </script>
</body>
</html>
