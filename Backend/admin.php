<?php
require_once __DIR__ . '/database.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../frontend/login.php');
}

$adminUser = getCurrentUser();

$userCount = 0;
$travelPlanCount = 0;
$bookingCount = 0;
$recentPlans = [];
$recentBookings = [];

try {
    $userCount = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE archived_at IS NULL")->fetchColumn();
    $travelPlanCount = (int) $pdo->query("SELECT COUNT(*) FROM travel_plans WHERE archived_at IS NULL")->fetchColumn();
    $bookingCount = (int) $pdo->query("SELECT COUNT(*) FROM service_bookings WHERE archived_at IS NULL")->fetchColumn();

    $recentPlans = $pdo->query("
        SELECT tp.title, tp.destination, tp.status, tp.created_at, u.name AS user_name
        FROM travel_plans tp
        JOIN users u ON u.id = tp.user_id
        WHERE tp.archived_at IS NULL
        ORDER BY tp.created_at DESC
        LIMIT 5
    ")->fetchAll();

    $recentBookings = $pdo->query("
        SELECT service_name, service_category, full_name, status, created_at
        FROM service_bookings
        WHERE archived_at IS NULL
        ORDER BY created_at DESC
        LIMIT 5
    ")->fetchAll();
} catch (Throwable $e) {
    // Keep the dashboard usable even if a query fails.
}
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head> 
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nepal Tour and Travel Dashboard</title>
    <!-- AlpineJS for declarative UI interactions -->
    <script src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <!-- Tailwind CSS for styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        [x-cloak] { display: none !important; }
        .custom-scrollbar::-webkit-scrollbar { width: 5px; height: 5px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #475569; }
    </style>
</head>
<body class="h-full font-sans antialiased text-slate-900" x-data="{ sidebarOpen: true }">
    <div class="bg-blue-600 text-white text-sm px-4 py-2 text-center">
        Live admin session for <?= htmlspecialchars($adminUser['email'] ?? 'admin') ?>.
        Users: <?= (int) $userCount ?> |
        Travel plans: <?= (int) $travelPlanCount ?> |
        Service bookings: <?= (int) $bookingCount ?>
    </div>

    <!-- Global Application State Context Wrapper -->
    <div class="flex h-full overflow-hidden w-full" 
         x-data="{
            // 1. Navigation Routing Controller View Target states
            currentView: 'dashboard', 
            bookingFilter: 'all',
            packageFilter: 'all',
            paymentFilter: 'transactions',
            reportFilter: 'sales',
            settingFilter: 'company',

            // 2. Mock Data Stores
            packages: [
                { id: 'PKG-101', destination: 'Paris, France', category: 'Luxury Travel', duration: '7 Days', price: 1499, status: 'Active' },
                { id: 'PKG-102', destination: 'Kyoto, Japan', category: 'Cultural Exploration', duration: '10 Days', price: 2850, status: 'Active' },
                { id: 'PKG-103', destination: 'Maui, Hawaii', category: 'Beach Resort', duration: '5 Days', price: 1999, status: 'Active' }
            ],
            categories: ['Luxury Travel', 'Cultural Exploration', 'Beach Resort', 'Adventure Trekking', 'Wildlife Safari'],
            destinations: ['Paris, France', 'Kyoto, Japan', 'Maui, Hawaii', 'Cairo, Egypt', 'Reykjavik, Iceland'],
            
            bookings: [
                { id: 'BKG-901', customer: 'John Doe', destination: 'Paris, France', date: '2026-07-20', status: 'New', amount: 1499 },
                { id: 'BKG-902', customer: 'Jane Smith', destination: 'Kyoto, Japan', date: '2026-08-12', status: 'Confirmed', amount: 2850 },
                { id: 'BKG-903', customer: 'Robert Johnson', destination: 'Maui, Hawaii', date: '2026-06-01', status: 'Completed', amount: 1999 },
                { id: 'BKG-904', customer: 'Emily Davis', destination: 'Paris, France', date: '2026-05-14', status: 'Cancelled', amount: 1499 }
            ],
            
            customers: [
                { id: 'CST-001', name: 'John Doe', email: 'john@example.com', phone: '+1 555-0192', totalBookings: 2 },
                { id: 'CST-002', name: 'Jane Smith', email: 'jane@example.com', phone: '+1 555-0143', totalBookings: 1 },
                { id: 'CST-003', name: 'Robert Johnson', email: 'robert@example.com', phone: '+1 555-0188', totalBookings: 5 }
            ],

            userSessions: [
                { id: 'LS-101', customer: 'John Doe', status: 'Online', lastActive: '2026-07-15 09:12', ip: '192.168.0.21' },
                { id: 'LS-102', customer: 'Jane Smith', status: 'Offline', lastActive: '2026-07-14 20:31', ip: '192.168.0.55' },
                { id: 'LS-103', customer: 'Robert Johnson', status: 'Online', lastActive: '2026-07-15 10:05', ip: '192.168.0.34' }
            ],

            selectedCustomerId: null,
            selectedCustomerName: '',
            selectedGalleryId: null,
            selectedPackageId: null,

            payments: [
                { id: 'TXN-7001', bookingId: 'BKG-901', customer: 'John Doe', type: 'Transaction', amount: 1499, status: 'Paid', date: '2026-07-01' },
                { id: 'INV-7002', bookingId: 'BKG-902', customer: 'Jane Smith', type: 'Invoice', amount: 2850, status: 'Pending', date: '2026-07-10' },
                { id: 'REF-7003', bookingId: 'BKG-904', customer: 'Emily Davis', type: 'Refund', amount: 1499, status: 'Processed', date: '2026-07-14' }
            ],

            gallery: [
                { id: 'IMG-01', title: 'Eiffel Tower Sunset', url: 'https://images.unsplash.com/photo-1502602898657-3e91760cbb34?auto=format&fit=crop&w=400&q=80' },
                { id: 'IMG-02', title: 'Kyoto Bamboo Forest', url: 'https://images.unsplash.com/photo-1493976040374-85c8e12f0c0e?auto=format&fit=crop&w=400&q=80' }
            ],
            reviews: [
                { id: 'REV-01', customer: 'Alice Vance', rating: 5, target: 'Kyoto, Japan', comment: 'Absolutely breathtaking arrangements.' },
                { id: 'REV-02', customer: 'Mark R.', rating: 4, target: 'Paris, France', comment: 'Great hotels, but itinerary was tight.' }
            ],

            companySettings: { name: 'TravelAdmin Enterprise', email: 'ops@traveladmin.site', phone: '+1 800-555-TRAV', address: '100 Innovation Way, Suite 400', facebook: 'fb.com/traveladmin', twitter: 'x.com/traveladmin', seoTitle: 'Premium Worldwide Custom Vacation Packages', seoKeywords: 'travel, tourism, luxury tour packages, private guide', homepageHero: 'Discover Uncharted Paradise Destinations' },
            auditLogs: [],

            // Modals Configuration Context variables
            activeModal: null, 
            modalForm: {},

            // Helper actions
            logActivity(action, refId) {
                this.auditLogs.unshift({ id: Date.now(), action, refId, time: new Date().toLocaleTimeString() });
            },

            executeSave() {
                if (this.activeModal === 'add-package') {
                    let newPkg = { id: 'PKG-' + Math.floor(100 + Math.random() * 900), destination: this.modalForm.destination, category: this.modalForm.category, duration: this.modalForm.duration, price: parseFloat(this.modalForm.price || 0), status: 'Active' };
                    this.packages.push(newPkg);
                    this.logActivity('Create Tour Package', newPkg.id);
                } else if (this.activeModal === 'add-category') {
                    this.categories.push(this.modalForm.name);
                    this.logActivity('Create Package Category', this.modalForm.name);
                } else if (this.activeModal === 'add-destination') {
                    this.destinations.push(this.modalForm.name);
                    this.logActivity('Register Destination Geolocation', this.modalForm.name);
                } else if (this.activeModal === 'add-customer') {
                    let newCst = { id: 'CST-' + Math.floor(100 + Math.random() * 900), name: this.modalForm.name, email: this.modalForm.email, phone: this.modalForm.phone, totalBookings: 0 };
                    this.customers.push(newCst);
                    this.logActivity('Register Customer Profile', newCst.id);
                } else if (this.activeModal === 'add-gallery') {
                    this.gallery.push({ id: 'IMG-' + Date.now(), title: this.modalForm.title, url: this.modalForm.url || 'https://images.unsplash.com/photo-1469854523086-cc02fe5d8800?auto=format&fit=crop&w=400&q=80' });
                    this.logActivity('Upload Media Resource', this.modalForm.title);
                } else if (this.activeModal === 'add-payment') {
                    this.payments.push({ id: (this.modalForm.type === 'Invoice' ? 'INV-' : this.modalForm.type === 'Refund' ? 'REF-' : 'TXN-') + Math.floor(1000 + Math.random() * 9000), bookingId: this.modalForm.bookingId || 'BKG-N/A', customer: this.modalForm.customer, type: this.modalForm.type, amount: parseFloat(this.modalForm.amount || 0), status: 'Processed', date: new Date().toISOString().split('T')[0] });
                    this.logActivity('Execute Ledger Transaction Balance Roll', this.modalForm.type);
                } else if (this.activeModal === 'edit-customer') {
                    const idx = this.customers.findIndex(c => c.id === this.modalForm.id);
                    if (idx !== -1) {
                        Object.assign(this.customers[idx], { name: this.modalForm.name, email: this.modalForm.email, phone: this.modalForm.phone });
                        this.logActivity('Update Customer Profile', this.modalForm.id);
                    }
                } else if (this.activeModal === 'edit-gallery') {
                    const img = this.gallery.find(g => g.id === this.modalForm.id);
                    if (img) {
                        img.title = this.modalForm.title;
                        img.url = this.modalForm.url || img.url;
                        this.logActivity('Update Gallery Asset', this.modalForm.id);
                    }
                }
                this.activeModal = null;
                this.modalForm = {};
            },

            editCustomer(customerId) {
                const c = this.customers.find(customer => customer.id === customerId);
                if (c) {
                    this.modalForm = { id: c.id, name: c.name, email: c.email, phone: c.phone };
                    this.activeModal = 'edit-customer';
                }
            },

            deleteCustomer(id) {
                this.customers = this.customers.filter(c => c.id !== id);
                this.logActivity('Delete Customer Profile', id);
            },

            editGallery(galleryId) {
                const img = this.gallery.find(item => item.id === galleryId);
                if (img) {
                    this.modalForm = { id: img.id, title: img.title, url: img.url };
                    this.activeModal = 'edit-gallery';
                }
            },

            toggleGallerySelection(galleryId) {
                this.selectedGalleryId = this.selectedGalleryId === galleryId ? null : galleryId;
            },

            removeGalleryImage(galleryId) {
                this.gallery = this.gallery.filter(item => item.id !== galleryId);
                this.selectedGalleryId = null;
                this.logActivity('Remove Website Image', galleryId);
            },

            contactCustomer(customerId) {
                const customer = this.customers.find(c => c.id === customerId);
                if (customer) {
                    alert(`Admin will communicate directly with ${customer.name}.`);
                    this.logActivity('Contact Customer Profile', customerId);
                }
            },

            viewCustomerBookings(customerId) {
                const customer = this.customers.find(c => c.id === customerId);
                if (customer) {
                    this.selectedCustomerId = customerId;
                    this.selectedCustomerName = customer.name;
                    this.bookingFilter = 'all';
                    this.currentView = 'bookings';
                }
            },

            changeBookingStatus(id, nextStatus) {
                let target = this.bookings.find(b => b.id === id);
                if(target) { target.status = nextStatus; this.logActivity(`Update Booking State to ${nextStatus}`, id); }
            },

            selectPackage(packageId) {
                this.selectedPackageId = this.selectedPackageId === packageId ? null : packageId;
            },

            removeSelectedPackage() {
                if (!this.selectedPackageId) return;
                const packageId = this.selectedPackageId;
                this.packages = this.packages.filter(p => p.id !== packageId);
                this.selectedPackageId = null;
                this.logActivity('Evict Tour Package Record', packageId);
            },

            deletePackage(id) {
                this.packages = this.packages.filter(p => p.id !== id);
                if (this.selectedPackageId === id) {
                    this.selectedPackageId = null;
                }
                this.logActivity('Evict Tour Package Record', id);
            }
         }">

        <!-- SIDEBAR CONTAINER SYSTEM -->
        <aside 
            :class="sidebarOpen ? 'w-64' : 'w-20 w-0 -translate-x-full lg:translate-x-0'" 
            class="bg-slate-900 text-slate-400 flex flex-col transition-all duration-300 ease-in-out z-30 h-full fixed inset-y-0 left-0 lg:static shadow-xl border-r border-slate-800 shrink-0"
        >
            <div class="h-16 flex items-center justify-between px-5 border-b border-slate-800 shrink-0">
                <div class="flex items-center space-x-3 overflow-hidden" x-show="sidebarOpen">
                    <div class="p-2 bg-blue-600 rounded-xl text-white shadow-md shadow-blue-500/20"><i class="bi bi-airplane-fill text-lg"></i></div>
                    <span class="text-lg font-bold text-white tracking-wide whitespace-nowrap">AddNepalTour & Travel Dashboard</span>
                </div>
                <button @click="sidebarOpen = !sidebarOpen" class="text-slate-400 hover:text-white p-2 rounded-xl hover:bg-slate-800 transition-colors hidden lg:block">
                    <i class="bi" :class="sidebarOpen ? 'bi-text-indent-right' : 'bi-list'"></i>
                </button>
            </div>

            <!-- EXECUTABLE SIDEBAR NAVIGATION MAP -->
            <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1 custom-scrollbar">
                
                <!-- Main Control Dashboard Routing Pin -->
                <button @click="currentView = 'dashboard'" :class="currentView === 'dashboard' ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/10' : 'hover:bg-slate-800/60 hover:text-slate-200'" class="w-full flex items-center space-x-3 px-3 py-2.5 rounded-xl transition-all duration-200 group">
                    <i class="bi bi-grid-1x2-fill text-lg"></i>
                    <span x-show="sidebarOpen" class="font-medium whitespace-nowrap">Dashboard</span>
                </button>

                <!-- Tour Management Loop Accordion -->
                <div x-data="{ open: false }" class="space-y-1">
                    <button type="button" @click="open = !open; currentView = 'packages'; packageFilter = 'all'" :class="currentView === 'packages' ? 'bg-blue-600 text-white shadow-lg' : 'hover:bg-slate-800/60 hover:text-slate-200'" class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl transition-all group cursor-pointer">
                        <div class="flex items-center space-x-3 min-w-0">
                            <i class="bi bi-compass text-lg"></i>
                            <span x-show="sidebarOpen" class="font-medium truncate">Tour Packages</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span x-show="sidebarOpen" class="text-[10px] uppercase tracking-wide text-slate-400 group-hover:text-slate-200">manage</span>
                            <i x-show="sidebarOpen" :class="open ? 'rotate-180 text-blue-400' : 'text-slate-500'" class="bi bi-chevron-down text-xs transition-transform duration-200"></i>
                        </div>
                    </button>
                    <div x-show="open && sidebarOpen" x-collapse class="pl-7 pr-2 space-y-1.5 pt-1" x-cloak>
                        <button type="button" @click.stop="open = true; packageFilter = 'all'; currentView = 'packages'; activeModal = 'add-package'" class="w-full text-left py-2 px-3 text-sm rounded-lg hover:text-white hover:bg-slate-800 transition-colors flex items-center space-x-2 border border-transparent hover:border-slate-700"><i class="bi bi-plus-circle text-xs text-blue-400"></i><span>Add New Package</span></button>
                        <button type="button" @click.stop="open = true; packageFilter = 'all'; currentView = 'packages'" class="w-full text-left py-2 px-3 text-sm rounded-lg hover:text-white hover:bg-slate-800 transition-colors">View All Packages</button>
                        <button type="button" @click.stop="open = true; packageFilter = 'categories'; currentView = 'packages'" class="w-full text-left py-2 px-3 text-sm rounded-lg hover:text-white hover:bg-slate-800 transition-colors">Manage Categories</button>
                        <button type="button" @click.stop="open = true; packageFilter = 'destinations'; currentView = 'packages'" class="w-full text-left py-2 px-3 text-sm rounded-lg hover:text-white hover:bg-slate-800 transition-colors">Manage Destinations</button>
                    </div>
                </div>

                <!-- Bookings Processing Pipe -->
                <div x-data="{ open: false }" class="space-y-1">
                    <button @click="open = !open; currentView = 'bookings'" class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl hover:bg-slate-800/60 hover:text-slate-200 transition-all group">
                        <div class="flex items-center space-x-3 min-w-0">
                            <i class="bi bi-calendar-check text-lg"></i>
                            <span x-show="sidebarOpen" class="font-medium truncate">Bookings</span>
                        </div>
                        <i x-show="sidebarOpen" :class="open ? 'rotate-180 text-blue-400' : 'text-slate-500'" class="bi bi-chevron-down text-xs transition-transform duration-200"></i>
                    </button>
                    <div x-show="open && sidebarOpen" x-collapse class="pl-9 pr-2 space-y-1" x-cloak>
                        <button @click="bookingFilter = 'New'; currentView = 'bookings'" class="w-full text-left py-1.5 px-3 text-sm rounded-lg hover:text-white hover:bg-slate-800/80 transition-colors flex items-center justify-between"><span>New</span><span class="bg-blue-600/20 text-blue-400 px-1.5 py-0.5 rounded text-[10px]" x-text="bookings.filter(b => b.status === 'New').length"></span></button>
                        <button @click="bookingFilter = 'Confirmed'; currentView = 'bookings'" class="w-full text-left py-1.5 px-3 text-sm rounded-lg hover:text-white hover:bg-slate-800/80 transition-colors">Confirmed</button>
                        <button @click="bookingFilter = 'Completed'; currentView = 'bookings'" class="w-full text-left py-1.5 px-3 text-sm rounded-lg hover:text-white hover:bg-slate-800/80 transition-colors">Completed</button>
                        <button @click="bookingFilter = 'Cancelled'; currentView = 'bookings'" class="w-full text-left py-1.5 px-3 text-sm rounded-lg hover:text-white hover:bg-slate-800/80 transition-colors">Cancelled</button>
                         <button @click="bookingFilter = 'Add'; currentView = 'bookings'" class="w-full text-left py-1.5 px-3 text-sm rounded-lg hover:text-white hover:bg-slate-800/80 transition-colors">Add</button>
                    </div>
                </div>

                <!-- Customers Management Accordion -->
                <div x-data="{ open: false }" class="space-y-1">
                    <button @click="open = !open; currentView = 'customers'" :class="currentView === 'customers' ? 'bg-blue-600 text-white shadow-lg' : 'hover:bg-slate-800/60 hover:text-slate-200'" class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl transition-all group">
                        <div class="flex items-center space-x-3 min-w-0">
                            <i class="bi bi-people text-lg"></i>
                            <span x-show="sidebarOpen" class="font-medium truncate">Customers</span>
                        </div>
                        <i x-show="sidebarOpen" :class="open ? 'rotate-180 text-blue-400' : 'text-slate-500'" class="bi bi-chevron-down text-xs transition-transform duration-200"></i>
                    </button>
                    <div x-show="open && sidebarOpen" x-collapse class="pl-9 pr-2 space-y-1" x-cloak>
                        <button @click="currentView = 'customers'; activeModal = 'add-customer'" class="w-full text-left py-1.5 px-3 text-sm rounded-lg hover:text-white hover:bg-slate-800 transition-colors flex items-center space-x-2"><i class="bi bi-plus-circle text-xs text-blue-400"></i><span>Add Customer</span></button>
                        <button @click="currentView = 'customers'" class="w-full text-left py-1.5 px-3 text-sm rounded-lg hover:text-white hover:bg-slate-800 transition-colors">All Customers</button>
                    </div>
                </div>

                <!-- Financial Ledger Processing Accordion -->
                <div x-data="{ open: false }" class="space-y-1">
                    <button @click="open = !open; currentView = 'payments'" class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl hover:bg-slate-800/60 hover:text-slate-200 transition-all group">
                        <div class="flex items-center space-x-3 min-w-0">
                            <i class="bi bi-credit-card text-lg"></i>
                            <span x-show="sidebarOpen" class="font-medium truncate">Payments</span>
                        </div>
                        <i x-show="sidebarOpen" :class="open ? 'rotate-180 text-blue-400' : 'text-slate-500'" class="bi bi-chevron-down text-xs transition-transform duration-200"></i>
                    </button>
                    <div x-show="open && sidebarOpen" x-collapse class="pl-9 pr-2 space-y-1" x-cloak>
                        <button @click="paymentFilter = 'Transaction'; currentView = 'payments'" class="w-full text-left py-1.5 px-3 text-sm rounded-lg hover:text-white hover:bg-slate-800/80 transition-colors">Transactions</button>
                        <button @click="paymentFilter = 'Invoice'; currentView = 'payments'" class="w-full text-left py-1.5 px-3 text-sm rounded-lg hover:text-white hover:bg-slate-800/80 transition-colors">Invoices</button>
                        <button @click="paymentFilter = 'Refund'; currentView = 'payments'" class="w-full text-left py-1.5 px-3 text-sm rounded-lg hover:text-white hover:bg-slate-800/80 transition-colors">Refunds</button>
                    </div>
                </div>

                <!-- Media Asset Management Accordion -->
                <div x-data="{ open: false }" class="space-y-1">
                    <button type="button" @click="open = !open; currentView = 'gallery'; selectedGalleryId = null; activeModal = null" :class="currentView === 'gallery' ? 'bg-blue-600 text-white shadow-lg' : 'hover:bg-slate-800/60 hover:text-slate-200'" class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl transition-all group cursor-pointer">
                        <div class="flex items-center space-x-3 min-w-0">
                            <i class="bi bi-images text-lg"></i>
                            <span x-show="sidebarOpen" class="font-medium truncate">Gallery</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span x-show="sidebarOpen" class="text-[10px] uppercase tracking-wide text-slate-400 group-hover:text-slate-200">media</span>
                            <i x-show="sidebarOpen" :class="open ? 'rotate-180 text-blue-400' : 'text-slate-500'" class="bi bi-chevron-down text-xs transition-transform duration-200"></i>
                        </div>
                    </button>
                    <div x-show="open && sidebarOpen" x-collapse class="pl-7 pr-2 space-y-1.5 pt-1" x-cloak>
                        <button type="button" @click.stop="open = true; currentView = 'gallery'; selectedGalleryId = null; activeModal = 'add-gallery'" class="w-full text-left py-2 px-3 text-sm rounded-lg hover:text-white hover:bg-slate-800 transition-colors flex items-center space-x-2 border border-transparent hover:border-slate-700"><i class="bi bi-cloud-arrow-up-fill text-xs text-blue-400"></i><span>Add New Image</span></button>
                        <button type="button" @click.stop="open = true; currentView = 'gallery'; selectedGalleryId = null" class="w-full text-left py-2 px-3 text-sm rounded-lg hover:text-white hover:bg-slate-800 transition-colors">View All Images</button>
                        <button type="button" @click.stop="open = true; if(selectedGalleryId){ removeGalleryImage(selectedGalleryId); }" class="w-full text-left py-2 px-3 text-sm rounded-lg hover:text-white hover:bg-slate-800 transition-colors">Remove Selected</button>
                    </div>
                </div>

                <!-- User Profile Communication Target -->
                <button @click="currentView = 'user-management'; selectedCustomerId = null" :class="currentView === 'user-management' ? 'bg-blue-600 text-white shadow-lg' : 'hover:bg-slate-800/60 hover:text-slate-200'" class="w-full flex items-center space-x-3 px-3 py-2.5 rounded-xl transition-all group">
                    <i class="bi bi-chat-left-text text-lg"></i>
                    <span x-show="sidebarOpen" class="font-medium whitespace-nowrap">User Communications</span>
                </button>

                <!-- Feedback & Quality Review Monitoring Target -->
                <button @click="currentView = 'reviews'" :class="currentView === 'reviews' ? 'bg-blue-600 text-white shadow-lg' : 'hover:bg-slate-800/60 hover:text-slate-200'" class="w-full flex items-center space-x-3 px-3 py-2.5 rounded-xl transition-all group">
                    <i class="bi bi-chat-left-heart text-lg"></i>
                    <span x-show="sidebarOpen" class="font-medium whitespace-nowrap">Reviews</span>
                </button>

                <!-- Aggregated Data Reporting Accordion -->
                <div x-data="{ open: false }" class="space-y-1">
                    <button @click="open = !open; currentView = 'reports'" class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl hover:bg-slate-800/60 hover:text-slate-200 transition-all group">
                        <div class="flex items-center space-x-3 min-w-0">
                            <i class="bi bi-graph-up-arrow text-lg"></i>
                            <span x-show="sidebarOpen" class="font-medium truncate">Reports</span>
                        </div>
                        <i x-show="sidebarOpen" :class="open ? 'rotate-180 text-blue-400' : 'text-slate-500'" class="bi bi-chevron-down text-xs transition-transform duration-200"></i>
                    </button>
                    <div x-show="open && sidebarOpen" x-collapse class="pl-9 pr-2 space-y-1" x-cloak>
                        <button @click="reportFilter = 'sales'; currentView = 'reports'" class="w-full text-left py-1.5 px-3 text-sm rounded-lg hover:text-white hover:bg-slate-800/80 transition-colors">Sales Overview</button>
                        <button @click="reportFilter = 'bookings'; currentView = 'reports'" class="w-full text-left py-1.5 px-3 text-sm rounded-lg hover:text-white hover:bg-slate-800/80 transition-colors">Bookings Analytics</button>
                        <button @click="reportFilter = 'revenue'; currentView = 'reports'" class="w-full text-left py-1.5 px-3 text-sm rounded-lg hover:text-white hover:bg-slate-800/80 transition-colors">Revenue Projections</button>
                    </div>
                </div>

                <!-- Global Website Platform Properties Config Accordion -->
                <div x-data="{ open: false }" class="space-y-1">
                    <button @click="open = !open; currentView = 'settings'" class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl hover:bg-slate-800/60 hover:text-slate-200 transition-all group">
                        <div class="flex items-center space-x-3 min-w-0">
                            <i class="bi bi-sliders2-vertical text-lg"></i>
                            <span x-show="sidebarOpen" class="font-medium truncate">Website Settings</span>
                        </div>
                        <i x-show="sidebarOpen" :class="open ? 'rotate-180 text-blue-400' : 'text-slate-500'" class="bi bi-chevron-down text-xs transition-transform duration-200"></i>
                    </button>
                    <div x-show="open && sidebarOpen" x-collapse class="pl-9 pr-2 space-y-1" x-cloak>
                        <button @click="settingFilter = 'company'; currentView = 'settings'" class="w-full text-left py-1.5 px-3 text-sm rounded-lg hover:text-white hover:bg-slate-800/80 transition-colors">Company Information</button>
                        <button @click="settingFilter = 'contact'; currentView = 'settings'" class="w-full text-left py-1.5 px-3 text-sm rounded-lg hover:text-white hover:bg-slate-800/80 transition-colors">Contact Details</button>
                        <button @click="settingFilter = 'social'; currentView = 'settings'" class="w-full text-left py-1.5 px-3 text-sm rounded-lg hover:text-white hover:bg-slate-800/80 transition-colors">Social Links</button>
                        <button @click="settingFilter = 'seo'; currentView = 'settings'" class="w-full text-left py-1.5 px-3 text-sm rounded-lg hover:text-white hover:bg-slate-800/80 transition-colors">SEO Meta</button>
                        <button @click="settingFilter = 'homepage'; currentView = 'settings'" class="w-full text-left py-1.5 px-3 text-sm rounded-lg hover:text-white hover:bg-slate-800/80 transition-colors">Homepage Settings</button>
                    </div>
                </div>

                <div class="h-px bg-slate-800 my-4 w-full"></div>

                <!-- Profile Core View Switch -->
                <button @click="currentView = 'profile'" :class="currentView === 'profile' ? 'bg-blue-600 text-white shadow-lg' : 'hover:bg-slate-800/60 hover:text-slate-200'" class="w-full flex items-center space-x-3 px-3 py-2.5 rounded-xl transition-all group">
                    <i class="bi bi-person-badge text-lg"></i>
                    <span x-show="sidebarOpen" class="font-medium whitespace-nowrap">My Profile</span>
                </button>

                <!-- Destructive Session Disconnect -->
                <button @click="if(confirm('Disconnect secure admin session panel?')) { window.location.reload(); }" class="w-full flex items-center space-x-3 px-3 py-2.5 rounded-xl text-rose-400 hover:bg-rose-950/40 hover:text-rose-300 transition-all group">
                    <i class="bi bi-box-arrow-left text-lg"></i>
                    <span x-show="sidebarOpen" class="font-medium whitespace-nowrap">Logout System</span>
                </button>
            </nav>
        </aside>

        <!-- MAIN DYNAMIC APPS SPACE CORE -->
        <div class="flex-1 flex flex-col overflow-hidden min-w-0 bg-slate-50">
            <!-- Global Frame Topbar Bar -->
            <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-6 shrink-0 shadow-sm z-10">
                <div class="flex items-center space-x-4">
                    <button @click="sidebarOpen = !sidebarOpen" class="text-slate-500 hover:text-slate-800 p-1.5 rounded-lg hover:bg-slate-100 transition-colors lg:hidden">
                        <i class="bi bi-list text-2xl"></i>
                    </button>
                    <h2 class="text-sm font-semibold tracking-wide text-slate-700 uppercase" x-text="'Active Area / ' + currentView"></h2>
                </div>
                
                <div class="flex items-center space-x-4">
                    <div class="hidden md:flex flex-col text-right">
                        <span class="text-xs font-bold text-slate-900">Administrator Console Mode</span>
                        <span class="text-[10px] text-emerald-600 font-mono font-bold">Status: Full Access Guard Active</span>
                    </div>
                    <div class="h-8 w-px bg-slate-200"></div>
                    <img class="w-9 h-9 rounded-xl object-cover ring-2 ring-slate-100" src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" alt="Root admin avatar">
                </div>
            </header>

            <!-- CANVAS SUB-ROUTING WORKSPACE INJECTOR PANELS -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto p-6 focus:outline-none custom-scrollbar">
                
                <!-- PANEL A: METRIC DASHBOARD CORE SUMMARY -->
                <div x-show="currentView === 'dashboard'" x-transition class="space-y-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
                            <div><p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Gross Booking Val</p><h3 class="text-2xl font-black text-slate-900 mt-1">$7,847</h3></div>
                            <div class="p-3 bg-blue-50 text-blue-600 rounded-xl"><i class="bi bi-currency-dollar text-xl"></i></div>
                        </div>
                        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
                            <div><p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Active Bookings</p><h3 class="text-2xl font-black text-slate-900 mt-1" x-text="bookings.length"></h3></div>
                            <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl"><i class="bi bi-bookmark-star text-xl"></i></div>
                        </div>
                        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
                            <div><p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Registered Clients</p><h3 class="text-2xl font-black text-slate-900 mt-1" x-text="customers.length"></h3></div>
                            <div class="p-3 bg-amber-50 text-amber-600 rounded-xl"><i class="bi bi-people text-xl"></i></div>
                        </div>
                        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
                            <div><p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Catalog Packages</p><h3 class="text-2xl font-black text-slate-900 mt-1" x-text="packages.length"></h3></div>
                            <div class="p-3 bg-indigo-50 text-indigo-600 rounded-xl"><i class="bi bi-compass text-xl"></i></div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Left Data Summary Column -->
                        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
                            <h3 class="text-base font-bold text-slate-900">Recent System Bookings Stream</h3>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-sm">
                                    <thead>
                                        <tr class="text-xs font-semibold text-slate-400 uppercase border-b border-slate-100 bg-slate-50/50">
                                            <th class="py-3 px-4">ID</th>
                                            <th class="py-3 px-4">Customer</th>
                                            <th class="py-3 px-4">Target Destination</th>
                                            <th class="py-3 px-4">Status Map</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        <template x-for="(b,index) in bookings.slice(0,3)" :key="b.id">
                                            <tr>
                                                <td class="py-3 px-4 font-mono font-bold text-blue-600" x-text="index + 1"></td>
                                                <td class="py-3 px-4 text-slate-900" x-text="b.customer"></td>
                                                <td class="py-3 px-4 text-slate-600" x-text="b.destination"></td>
                                                <td class="py-3 px-4">
                                                    <span :class="{'bg-blue-100 text-blue-700': b.status==='New', 'bg-amber-100 text-amber-700': b.status==='Confirmed', 'bg-emerald-100 text-emerald-700': b.status==='Completed', 'bg-rose-100 text-rose-700': b.status==='Cancelled'}" class="px-2 py-0.5 rounded text-xs font-semibold" x-text="b.status"></span>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!-- Live Core Audit Stream Log box -->
                        <div class="bg-slate-900 text-slate-100 p-5 rounded-2xl border border-slate-800 shadow-inner flex flex-col h-80">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 pb-2 border-b border-slate-800">Live Workspace Activity Trace</h4>
                            <div class="flex-1 overflow-y-auto mt-3 font-mono text-[11px] space-y-2.5 pr-1 custom-scrollbar">
                                <template x-for="log in auditLogs" :key="log.id">
                                    <div class="bg-slate-950/40 p-2 rounded border border-slate-800 flex justify-between items-start gap-2">
                                        <div>
                                            <span class="text-blue-400 font-bold" x-text="log.action"></span>
                                            <p class="text-slate-500 text-[10px] mt-0.5" x-text="'Ref: '+log.refId"></p>
                                        </div>
                                        <span class="text-slate-600 text-[10px]" x-text="log.time"></span>
                                    </div>
                                </template>
                                <template x-if="auditLogs.length === 0">
                                    <p class="text-slate-600 italic text-center pt-16">No active state changes logged in this browser frame window yet.</p>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PANEL B: PACKAGE CATALOG CONTROL CANVAS -->
                <div x-show="currentView === 'packages'" x-transition class="space-y-6">
                    <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 pb-4">
                        <div class="flex space-x-2 bg-slate-200/60 p-1 rounded-xl">
                            <button @click="packageFilter = 'all'" :class="packageFilter === 'all' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:bg-slate-200'" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all">All Packages Inventory</button>
                            <button @click="packageFilter = 'categories'" :class="packageFilter === 'categories' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:bg-slate-200'" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all">Categories Link Map</button>
                            <button @click="packageFilter = 'destinations'" :class="packageFilter === 'destinations' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:bg-slate-200'" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all">Destinations Geolocation</button>
                        </div>
                        <button @click="activeModal = (packageFilter === 'categories' ? 'add-category' : packageFilter === 'destinations' ? 'add-destination' : 'add-package')" class="bg-blue-600 text-white px-3 py-1.5 rounded-xl text-xs font-bold shadow hover:bg-blue-700 flex items-center space-x-1">
                            <i class="bi bi-plus-lg"></i>
                            <span x-text="packageFilter === 'categories' ? 'Add Category' : packageFilter === 'destinations' ? 'Add Destination' : 'Add Package'"></span>
                        </button>
                    </div>

                    <!-- Filter state conditional rendering views -->
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                        <!-- Subview: Package List Grid -->
                        <template x-if="packageFilter === 'all'">
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-sm">
                                    <thead class="bg-slate-50 border-b border-slate-200 text-xs font-bold uppercase text-slate-500">
                                        <tr>
                                            <th class="px-6 py-4">Destination Target</th>
                                            <th class="px-6 py-4">Classification Category</th>
                                            <th class="px-6 py-4">Duration</th>
                                            <th class="px-6 py-4">Base Cost</th>
                                            <th class="px-6 py-4 text-right">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        <template x-for="p in packages" :key="p.id">
                                            <tr @click="selectPackage(p.id)" :class="selectedPackageId === p.id ? 'bg-blue-50' : 'hover:bg-slate-50/60'" class="cursor-pointer">
                                                <td class="px-6 py-4 font-semibold text-slate-900" x-text="p.destination"></td>
                                                <td class="px-6 py-4 text-slate-600" x-text="p.category"></td>
                                                <td class="px-6 py-4 text-slate-600" x-text="p.duration"></td>
                                                <td class="px-6 py-4 font-mono font-bold text-slate-900" x-text="'$'+p.price"></td>
                                                <td class="px-6 py-4 text-right">
                                                    <button @click.stop="deletePackage(p.id)" class="text-rose-600 p-1.5 hover:bg-rose-50 rounded-lg text-xs font-bold transition-colors">Evict</button>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </template>

                        <!-- Subview: Category Matrix -->
                        <template x-if="packageFilter === 'categories'">
                            <div class="p-6 space-y-3">
                                <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wide">Registered Classification Tags</h3>
                                <div class="flex flex-wrap gap-2">
                                    <template x-for="cat in categories" :key="cat">
                                        <span class="px-3 py-1.5 bg-slate-100 border border-slate-200 text-slate-800 font-medium text-xs rounded-xl flex items-center space-x-2">
                                            <span x-text="cat"></span>
                                            <button @click="categories = categories.filter(c => c !== cat); logActivity('Remove Category Tag', cat)" class="text-slate-400 hover:text-rose-600"><i class="bi bi-x-circle-fill text-[10px]"></i></button>
                                        </span>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <!-- Subview: Destination Registry Matrix -->
                        <template x-if="packageFilter === 'destinations'">
                            <div class="p-6 space-y-3">
                                <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wide">Active Hub Destinations Geolocation Index</h3>
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                                    <template x-for="dest in destinations" :key="dest">
                                        <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-between">
                                            <span class="text-xs font-semibold text-slate-900" x-text="dest"></span>
                                            <button @click="destinations = destinations.filter(d => d !== dest); logActivity('Evict Destination Target', dest)" class="text-rose-600 hover:bg-rose-100 px-2 py-1 rounded text-[10px] font-bold">Remove</button>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- PANEL C: BOOKING MANAGER DISPATCH WORKSPACE -->
                <div x-show="currentView === 'bookings'" x-transition class="space-y-6">
                    <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 pb-4">
                        <div class="flex flex-wrap gap-1 bg-slate-200/60 p-1 rounded-xl">
                            <button @click="bookingFilter = 'all'" :class="bookingFilter === 'all' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600'" class="px-3 py-1.5 rounded-lg text-xs font-bold">All Bookings Pipeline</button>
                            <button @click="bookingFilter = 'New'" :class="bookingFilter === 'New' ? 'bg-white text-slate-900' : 'text-slate-600'" class="px-3 py-1.5 rounded-lg text-xs font-bold">New Requests</button>
                            <button @click="bookingFilter = 'Confirmed'" :class="bookingFilter === 'Confirmed' ? 'bg-white text-slate-900' : 'text-slate-600'" class="px-3 py-1.5 rounded-lg text-xs font-bold">Confirmed</button>
                            <button @click="bookingFilter = 'Completed'" :class="bookingFilter === 'Completed' ? 'bg-white text-slate-900' : 'text-slate-600'" class="px-3 py-1.5 rounded-lg text-xs font-bold">Completed</button>
                            <button @click="bookingFilter = 'Cancelled'" :class="bookingFilter === 'Cancelled' ? 'bg-white text-slate-900' : 'text-slate-600'" class="px-3 py-1.5 rounded-lg text-xs font-bold">Cancelled</button>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead class="bg-slate-50 border-b border-slate-200 text-xs font-bold uppercase text-slate-500">
                                    <tr>
                                        <th class="px-6 py-4">ID</th>
                                        <th class="px-6 py-4">Customer Account</th>
                                        <th class="px-6 py-4">Target Destination</th>
                                        <th class="px-6 py-4">Schedule Date</th>
                                        <th class="px-6 py-4">Value</th>
                                        <th class="px-6 py-4">Pipeline Status State</th>
                                        <th class="px-6 py-4 text-right">System Action Controls Overrides</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <template x-for="(b,index) in bookings.filter(b => (selectedCustomerId ? b.customer === selectedCustomerName : true) && (bookingFilter === 'all' || b.status === bookingFilter))" :key="b.id">
                                        <tr class="hover:bg-slate-50/60">
                                            <td class="px-6 py-4 font-mono font-bold text-blue-600" x-text="index + 1"></td>
                                            <td class="px-6 py-4 text-slate-900 font-medium" x-text="b.customer"></td>
                                            <td class="px-6 py-4 text-slate-600" x-text="b.destination"></td>
                                            <td class="px-6 py-4 text-slate-500 font-mono" x-text="b.date"></td>
                                            <td class="px-6 py-4 font-mono font-bold text-slate-900" x-text="'$'+b.amount"></td>
                                            <td class="px-6 py-4">
                                                <span :class="{'bg-blue-100 text-blue-700': b.status==='New', 'bg-amber-100 text-amber-700': b.status==='Confirmed', 'bg-emerald-100 text-emerald-700': b.status==='Completed', 'bg-rose-100 text-rose-700': b.status==='Cancelled'}" class="px-2 py-0.5 rounded text-xs font-semibold" x-text="b.status"></span>
                                            </td>
                                            <td class="px-6 py-4 text-right space-x-1">
                                                <button x-show="b.status === 'New'" @click="changeBookingStatus(b.id, 'Confirmed')" class="bg-amber-500 text-white px-2 py-1 rounded text-[10px] font-bold">Confirm</button>
                                                <button x-show="b.status === 'Confirmed'" @click="changeBookingStatus(b.id, 'Completed')" class="bg-emerald-600 text-white px-2 py-1 rounded text-[10px] font-bold">Complete</button>
                                                <button x-show="b.status !== 'Cancelled' && b.status !== 'Completed'" @click="changeBookingStatus(b.id, 'Cancelled')" class="text-rose-600 hover:bg-rose-50 px-2 py-1 rounded text-[10px] font-bold">Cancel</button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- PANEL D: CUSTOMERS PROFILES RECORD TABLE -->
                <div x-show="currentView === 'customers'" x-transition class="space-y-6">
                    <div class="flex items-center justify-between border-b border-slate-200 pb-4">
                        <h3 class="text-base font-bold text-slate-900">CRM Master Database Profile Accounts</h3>
                        <button @click="activeModal = 'add-customer'" class="bg-blue-600 text-white px-3 py-1.5 rounded-xl text-xs font-bold shadow hover:bg-blue-700"><i class="bi bi-person-plus-fill mr-1"></i> Add Customer Profile</button>
                    </div>

                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead class="bg-slate-50 border-b border-slate-200 text-xs font-bold uppercase text-slate-500">
                                    <tr>
                                        <th class="px-6 py-4">ID Reference</th>
                                        <th class="px-6 py-4">Full Name</th>
                                        <th class="px-6 py-4">Email Address</th>
                                        <th class="px-6 py-4">Contact Phone Line</th>
                                        <th class="px-6 py-4">Total Orders Booked</th>
                                        <th class="px-6 py-4 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <template x-for="(c,index) in customers" :key="c.id">
                                        <tr class="hover:bg-slate-50/60">
                                            <td class="px-6 py-4 font-mono font-bold text-slate-400" x-text="index + 1"></td>
                                            <td class="px-6 py-4 text-slate-900 font-semibold" x-text="c.name"></td>
                                            <td class="px-6 py-4 text-slate-600 font-mono" x-text="c.email"></td>
                                            <td class="px-6 py-4 text-slate-600" x-text="c.phone"></td>
                                            <td class="px-6 py-4 font-mono font-bold text-slate-900" x-text="c.totalBookings"></td>
                                            <td class="px-6 py-4 text-right space-x-1">
                                                <button @click="editCustomer(c.id)" class="text-slate-700 bg-slate-100 hover:bg-slate-200 px-2 py-1 rounded text-[10px] font-bold">Edit</button>
                                                <button @click="deleteCustomer(c.id)" class="text-rose-600 bg-rose-50 hover:bg-rose-100 px-2 py-1 rounded text-[10px] font-bold">Remove</button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- PANEL D1: DIRECT USER COMMUNICATIONS -->
                <div x-show="currentView === 'user-management'" x-transition class="space-y-6">
                    <div class="flex items-center justify-between border-b border-slate-200 pb-4">
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Direct User Communications</h3>
                            <p class="text-sm text-slate-500">View customer profiles, login sessions, and link to bookings directly from the sidebar.</p>
                        </div>
                        <button @click="currentView = 'customers'" class="bg-slate-100 text-slate-800 px-3 py-1.5 rounded-xl text-xs font-bold shadow-sm hover:bg-slate-200">Open Profiles</button>
                    </div>

                    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
                            <h4 class="text-sm font-bold text-slate-900 mb-4">Customer Login Sessions</h4>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-sm">
                                    <thead class="bg-slate-50 border-b border-slate-200 text-xs font-bold uppercase text-slate-500">
                                        <tr>
                                            <th class="px-4 py-3">No.</th>
                                            <th class="px-4 py-3">Customer</th>
                                            <th class="px-4 py-3">Status</th>
                                            <th class="px-4 py-3">Last Active</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        <template x-for="(session,index) in userSessions" :key="session.id">
                                            <tr class="hover:bg-slate-50/70">
                                                <td class="px-4 py-3 font-mono text-slate-700" x-text="index + 1"></td>
                                                <td class="px-4 py-3 text-slate-900" x-text="session.customer"></td>
                                                <td class="px-4 py-3"><span :class="session.status === 'Online' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600'" class="px-2 py-1 rounded-full text-[10px] font-semibold" x-text="session.status"></span></td>
                                                <td class="px-4 py-3 font-mono text-slate-500" x-text="session.lastActive"></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="text-sm font-bold text-slate-900">Customer Directory</h4>
                                <button @click="activeModal = 'add-customer'" class="bg-blue-600 text-white px-3 py-1.5 rounded-xl text-xs font-bold hover:bg-blue-700">New Profile</button>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-sm">
                                    <thead class="bg-slate-50 border-b border-slate-200 text-xs font-bold uppercase text-slate-500">
                                        <tr>
                                            <th class="px-4 py-3">Customer</th>
                                            <th class="px-4 py-3">Email</th>
                                            <th class="px-4 py-3">Phone</th>
                                            <th class="px-4 py-3 text-right">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        <template x-for="c in customers" :key="c.id">
                                            <tr class="hover:bg-slate-50/70">
                                                <td class="px-4 py-3 text-slate-900 font-semibold" x-text="c.name"></td>
                                                <td class="px-4 py-3 text-slate-600 font-mono" x-text="c.email"></td>
                                                <td class="px-4 py-3 text-slate-600" x-text="c.phone"></td>
                                                <td class="px-4 py-3 text-right space-x-1">
                                                    <button @click="contactCustomer(c.id)" class="text-slate-700 bg-slate-100 hover:bg-slate-200 px-2 py-1 rounded text-[10px] font-bold">Contact</button>
                                                    <button @click="viewCustomerBookings(c.id)" class="text-blue-600 bg-blue-50 hover:bg-blue-100 px-2 py-1 rounded text-[10px] font-bold">Bookings</button>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PANEL E: PAYMENTS & BALANCE LEDGER OVERVIEW -->
                <div x-show="currentView === 'payments'" x-transition class="space-y-6">
                    <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 pb-4">
                        <div class="flex space-x-2 bg-slate-200/60 p-1 rounded-xl">
                            <button @click="paymentFilter = 'Transaction'" :class="paymentFilter === 'Transaction' ? 'bg-white text-slate-900' : 'text-slate-600'" class="px-3 py-1.5 rounded-lg text-xs font-bold">Processed Transactions</button>
                            <button @click="paymentFilter = 'Invoice'" :class="paymentFilter === 'Invoice' ? 'bg-white text-slate-900' : 'text-slate-600'" class="px-3 py-1.5 rounded-lg text-xs font-bold">Issued Invoices</button>
                            <button @click="paymentFilter = 'Refund'" :class="paymentFilter === 'Refund' ? 'bg-white text-slate-900' : 'text-slate-600'" class="px-3 py-1.5 rounded-lg text-xs font-bold">Processed Refunds Ledger</button>
                        </div>
                        <button @click="activeModal = 'add-payment'; modalForm.type = paymentFilter" class="bg-blue-600 text-white px-3 py-1.5 rounded-xl text-xs font-bold shadow hover:bg-blue-700">
                            <i class="bi bi-wallet2 mr-1"></i> Log Financial Movement Object
                        </button>
                    </div>

                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead class="bg-slate-50 border-b border-slate-200 text-xs font-bold uppercase text-slate-500">
                                    <tr>
                                        <th class="px-6 py-4">No.</th>
                                        <th class="px-6 py-4">Linked Booking ID</th>
                                        <th class="px-6 py-4">Payer Account</th>
                                        <th class="px-6 py-4">Total Amount Value</th>
                                        <th class="px-6 py-4">Value Date</th>
                                        <th class="px-6 py-4">Processing Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <template x-for="(p,index) in payments.filter(p => p.type === paymentFilter)" :key="p.id">
                                        <tr class="hover:bg-slate-50/60">
                                            <td class="px-6 py-4 font-mono font-bold text-slate-900" x-text="index + 1"></td>
                                            <td class="px-6 py-4 font-mono text-blue-600" x-text="p.bookingId"></td>
                                            <td class="px-6 py-4 text-slate-900" x-text="p.customer"></td>
                                            <td class="px-6 py-4 font-mono font-bold text-slate-900" x-text="'$'+p.amount"></td>
                                            <td class="px-6 py-4 text-slate-500 font-mono" x-text="p.date"></td>
                                            <td class="px-6 py-4">
                                                <span :class="{'bg-emerald-100 text-emerald-800': p.status==='Paid' || p.status==='Processed', 'bg-amber-100 text-amber-800': p.status==='Pending'}" class="px-2 py-0.5 rounded text-xs font-semibold" x-text="p.status"></span>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- PANEL F: GALLERY STATIC STORAGE MAP -->
                <div x-show="currentView === 'gallery'" x-transition class="space-y-6">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 pb-4">
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Website Image Manager</h3>
                            <p class="text-sm text-slate-500">All website images are shown here for upload, edit, and remove actions.</p>
                        </div>
                        <div class="flex gap-2">
                            <button @click="activeModal = 'add-gallery'" class="bg-blue-600 text-white px-3 py-1.5 rounded-xl text-xs font-bold shadow hover:bg-blue-700"><i class="bi bi-cloud-arrow-up-fill mr-1"></i> Upload Image</button>
                            <button @click="if(selectedGalleryId){ removeGalleryImage(selectedGalleryId); }" :class="selectedGalleryId ? 'bg-rose-600 text-white hover:bg-rose-700' : 'bg-slate-100 text-slate-400 cursor-not-allowed'" class="px-3 py-1.5 rounded-xl text-xs font-bold shadow-sm" :disabled="selectedGalleryId === null">Remove Selected</button>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                        <template x-for="(img,index) in gallery" :key="img.id">
                            <div @click="toggleGallerySelection(img.id)" :class="selectedGalleryId === img.id ? 'ring-2 ring-blue-500 shadow-lg' : 'border-slate-200'" class="bg-white rounded-2xl border p-2 shadow-sm relative group overflow-hidden cursor-pointer">
                                <img :src="img.url" class="w-full h-32 object-cover rounded-xl" :alt="img.title">
                                <div class="p-2">
                                    <p class="text-xs font-bold truncate text-slate-800" x-text="img.title"></p>
                                    <p class="text-[10px] text-slate-400 font-mono mt-0.5" x-text="index + 1"></p>
                                </div>
                                <button @click.stop="editGallery(img.id)" class="absolute top-4 right-12 bg-slate-700 text-white p-1.5 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity text-xs"><i class="bi bi-pencil"></i></button>
                                <button @click.stop="removeGalleryImage(img.id)" class="absolute top-4 right-4 bg-rose-600 text-white p-1.5 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity text-xs"><i class="bi bi-trash"></i></button>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- PANEL H: REVIEWS & FEEDBACK CONTROL -->
                <div x-show="currentView === 'reviews'" x-transition class="space-y-6">
                    <h3 class="text-base font-bold text-slate-900 border-b border-slate-200 pb-4">Customer Experience Review Audit Loop</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <template x-for="rev in reviews" :key="rev.id">
                            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-3 relative">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="font-bold text-slate-900 text-sm" x-text="rev.customer"></h4>
                                        <p class="text-xs text-slate-400 font-medium" x-text="'Target Property: ' + rev.target"></p>
                                    </div>
                                    <div class="flex text-amber-400 gap-0.5">
                                        <template x-for="i in Array.from({length: rev.rating})">
                                            <i class="bi bi-star-fill text-xs"></i>
                                        </template>
                                    </div>
                                </div>
                                <p class="text-xs italic text-slate-600 bg-slate-50 p-3 rounded-xl border border-slate-100" x-text="'&ldquo; ' + rev.comment + ' &rdquo;'"></p>
                                <div class="flex justify-end pt-1">
                                    <button @click="reviews = reviews.filter(r => r.id !== rev.id); logActivity('Dismiss Review Feedback Object', rev.id)" class="text-rose-600 hover:bg-rose-50 px-2 py-1 rounded text-[10px] font-bold">Dismiss Review</button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- PANEL I: ANALYTICS & REPORTS GENERATOR LAYER -->
                <div x-show="currentView === 'reports'" x-transition class="space-y-6">
                    <div class="flex space-x-2 bg-slate-200/60 p-1 rounded-xl w-max">
                        <button @click="reportFilter = 'sales'" :class="reportFilter === 'sales' ? 'bg-white text-slate-900' : 'text-slate-600'" class="px-3 py-1.5 rounded-lg text-xs font-bold">Sales Analysis</button>
                        <button @click="reportFilter = 'bookings'" :class="reportFilter === 'bookings' ? 'bg-white text-slate-900' : 'text-slate-600'" class="px-3 py-1.5 rounded-lg text-xs font-bold">Bookings Pipeline Velocity</button>
                        <button @click="reportFilter = 'revenue'" :class="reportFilter === 'revenue' ? 'bg-white text-slate-900' : 'text-slate-600'" class="px-3 py-1.5 rounded-lg text-xs font-bold">Revenue Projections</button>
                    </div>

                    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
                        <div class="flex justify-between items-center">
                            <h4 class="font-bold text-slate-900 text-sm uppercase tracking-wider" x-text="'Calculated Report Core Metrics / ' + reportFilter"></h4>
                            <button @click="alert('Exporting system data payload frame as .CSV packet...')" class="border border-slate-300 text-slate-700 hover:bg-slate-50 px-3 py-1.5 rounded-xl text-xs font-bold"><i class="bi bi-download mr-1"></i> Export Data Payload</button>
                        </div>

                        <!-- Conditional view display calculations metrics grids -->
                        <template x-if="reportFilter === 'sales'">
                            <div class="grid grid-cols-3 gap-4 text-center">
                                <div class="bg-slate-50 p-4 rounded-xl border border-slate-100"><span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Gross Sales Value</span><p class="text-xl font-black text-slate-900 mt-1">$6,348</p></div>
                                <div class="bg-slate-50 p-4 rounded-xl border border-slate-100"><span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Net Margin Conversion</span><p class="text-xl font-black text-slate-900 mt-1">18.4%</p></div>
                                <div class="bg-slate-50 p-4 rounded-xl border border-slate-100"><span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Average Cart Order Value</span><p class="text-xl font-black text-slate-900 mt-1">$1,950</p></div>
                            </div>
                        </template>

                        <template x-if="reportFilter === 'bookings'">
                            <div class="grid grid-cols-3 gap-4 text-center">
                                <div class="bg-slate-50 p-4 rounded-xl border border-slate-100"><span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Total Orders Logged</span><p class="text-xl font-black text-slate-900 mt-1" x-text="bookings.length"></p></div>
                                <div class="bg-slate-50 p-4 rounded-xl border border-slate-100"><span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Fulfillment Completion Rate</span><p class="text-xl font-black text-slate-900 mt-1">74.2%</p></div>
                                <div class="bg-slate-50 p-4 rounded-xl border border-slate-100"><span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Volatile Churn Cancellation</span><p class="text-xl font-black text-slate-900 mt-1">12.5%</p></div>
                            </div>
                        </template>

                        <template x-if="reportFilter === 'revenue'">
                            <div class="grid grid-cols-3 gap-4 text-center">
                                <div class="bg-slate-50 p-4 rounded-xl border border-slate-100"><span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Cleared Liquid Balances</span><p class="text-xl font-black text-slate-900 mt-1">$4,349</p></div>
                                <div class="bg-slate-50 p-4 rounded-xl border border-slate-100"><span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Accounts Receivable Vault</span><p class="text-xl font-black text-slate-900 mt-1">$2,850</p></div>
                                <div class="bg-slate-50 p-4 rounded-xl border border-slate-100"><span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Issued Outflow Refunds Pay</span><p class="text-xl font-black text-slate-900 mt-1">$1,499</p></div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- PANEL J: GLOBAL SETTINGS CORE CONTROLLER -->
                <div x-show="currentView === 'settings'" x-transition class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <!-- Navigation Context Links Column -->
                        <div class="md:col-span-1 flex flex-col space-y-1 bg-white p-3 rounded-2xl border border-slate-200 h-max shadow-sm">
                            <button @click="settingFilter = 'company'" :class="settingFilter === 'company' ? 'bg-slate-100 text-slate-900 font-bold' : 'text-slate-600'" class="text-left text-xs px-3 py-2 rounded-lg transition-all">Company Profile Info</button>
                            <button @click="settingFilter = 'contact'" :class="settingFilter === 'contact' ? 'bg-slate-100 text-slate-900 font-bold' : 'text-slate-600'" class="text-left text-xs px-3 py-2 rounded-lg transition-all">Contact Details Channels</button>
                            <button @click="settingFilter = 'social'" :class="settingFilter === 'social' ? 'bg-slate-100 text-slate-900 font-bold' : 'text-slate-600'" class="text-left text-xs px-3 py-2 rounded-lg transition-all">Social Networking Links</button>
                            <button @click="settingFilter = 'seo'" :class="settingFilter === 'seo' ? 'bg-slate-100 text-slate-900 font-bold' : 'text-slate-600'" class="text-left text-xs px-3 py-2 rounded-lg transition-all">SEO Search Engine Metadata</button>
                            <button @click="settingFilter = 'homepage'" :class="settingFilter === 'homepage' ? 'bg-slate-100 text-slate-900 font-bold' : 'text-slate-600'" class="text-left text-xs px-3 py-2 rounded-lg transition-all">Homepage Hero Matrix</button>
                        </div>

                        <!-- Data Form Fields Workarea Column -->
                        <div class="md:col-span-3 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                            <form @submit.prevent="logActivity('Mutate Global Configuration Property', settingFilter); alert('System operational metadata flags committed to local application scope context successfully.')" class="space-y-4">
                                
                                <template x-if="settingFilter === 'company'">
                                    <div class="space-y-3">
                                        <div><label class="block text-xs font-bold text-slate-500 uppercase">Legal Entity Brand Name</label><input type="text" x-model="companySettings.name" class="w-full mt-1 px-3 py-2 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:outline-none"></div>
                                        <div><label class="block text-xs font-bold text-slate-500 uppercase">HQ Registration Location Address</label><input type="text" x-model="companySettings.address" class="w-full mt-1 px-3 py-2 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:outline-none"></div>
                                    </div>
                                </template>

                                <template x-if="settingFilter === 'contact'">
                                    <div class="space-y-3">
                                        <div><label class="block text-xs font-bold text-slate-500 uppercase">System Alerts Router Email Address</label><input type="email" x-model="companySettings.email" class="w-full mt-1 px-3 py-2 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:outline-none"></div>
                                        <div><label class="block text-xs font-bold text-slate-500 uppercase">Customer Help Phone Line</label><input type="text" x-model="companySettings.phone" class="w-full mt-1 px-3 py-2 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:outline-none"></div>
                                    </div>
                                </template>

                                <template x-if="settingFilter === 'social'">
                                    <div class="space-y-3">
                                        <div><label class="block text-xs font-bold text-slate-500 uppercase">Facebook App Profile Gateway URI</label><input type="text" x-model="companySettings.facebook" class="w-full mt-1 px-3 py-2 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:outline-none"></div>
                                        <div><label class="block text-xs font-bold text-slate-500 uppercase">Twitter / X Handle Domain Mapping Link</label><input type="text" x-model="companySettings.twitter" class="w-full mt-1 px-3 py-2 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:outline-none"></div>
                                    </div>
                                </template>

                                <template x-if="settingFilter === 'seo'">
                                    <div class="space-y-3">
                                        <div><label class="block text-xs font-bold text-slate-500 uppercase">Global Platform Meta Title String</label><input type="text" x-model="companySettings.seoTitle" class="w-full mt-1 px-3 py-2 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:outline-none"></div>
                                        <div><label class="block text-xs font-bold text-slate-500 uppercase">Index Crawler Keyword Aggregates</label><input type="text" x-model="companySettings.seoKeywords" class="w-full mt-1 px-3 py-2 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:outline-none"></div>
                                    </div>
                                </template>

                                <template x-if="settingFilter === 'homepage'">
                                    <div class="space-y-3">
                                        <div><label class="block text-xs font-bold text-slate-500 uppercase">Web Portal Hero Content Title Header Text</label><input type="text" x-model="companySettings.homepageHero" class="w-full mt-1 px-3 py-2 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:outline-none"></div>
                                    </div>
                                </template>

                                <div class="pt-4 border-t border-slate-100 flex justify-end">
                                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-xl text-xs font-bold shadow-md shadow-blue-600/10 hover:bg-blue-700 transition-colors">Commit Global Updates</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- PANEL K: OPERATOR PROFILE CORE SUMMARY -->
                <div x-show="currentView === 'profile'" x-transition class="space-y-6">
                    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm max-w-xl mx-auto space-y-6">
                        <div class="flex items-center space-x-4">
                            <img class="w-16 h-16 rounded-2xl object-cover ring-4 ring-slate-100 shadow-sm" src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" alt="Avatar">
                            <div>
                                <h3 class="text-lg font-black text-slate-900">Alex Mercer</h3>
                                <p class="text-xs font-mono text-blue-600 font-bold">Assigned Security Principal: Root Administrator</p>
                            </div>
                        </div>
                        
                        <div class="border-t border-slate-100 pt-4 divide-y divide-slate-50 text-xs">
                            <div class="py-2.5 flex justify-between"><span class="font-bold text-slate-400 uppercase">Operator User Identity Token ID</span><span class="font-mono font-semibold text-slate-800">USR-ADMIN-770X</span></div>
                            <div class="py-2.5 flex justify-between"><span class="font-bold text-slate-400 uppercase">Access Scope Permissions</span><span class="bg-emerald-50 text-emerald-700 px-2 py-0.5 font-bold rounded">Full System Write Access Overrides Allowed</span></div>
                            <div class="py-2.5 flex justify-between"><span class="font-bold text-slate-400 uppercase">Hardware Key Signature</span><span class="font-mono text-slate-500">ED25519 SHA256:...9F2B</span></div>
                        </div>
                    </div>
                </div>

            </main>
        </div>

        <!-- DIALOG FORM MODAL CONTEXT CANVAS WINDOWS -->
        <div 
            x-show="activeModal !== null" 
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
            x-transition.opacity
            x-cloak
        >
            <div @click.outside="activeModal = null" class="bg-white w-full max-w-md rounded-2xl shadow-xl border border-slate-200 overflow-hidden transform transition-all">
                <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                    <h3 class="font-bold text-sm uppercase tracking-wide text-slate-900" x-text="'Register System Data Object / ' + activeModal"></h3>
                    <button @click="activeModal = null" class="text-slate-400 hover:text-slate-600 p-1"><i class="bi bi-x-lg"></i></button>
                </div>
                
                <form @submit.prevent="executeSave()" class="p-6 space-y-4">
                    
                    <!-- Form Fields Conditionals Block -->
                    <template x-if="activeModal === 'add-package'">
                        <div class="space-y-3">
                            <div><label class="block text-xs font-bold text-slate-500 uppercase">Destination Hub Label</label><input type="text" x-model="modalForm.destination" required class="w-full mt-1 px-3 py-2 border border-slate-300 rounded-xl text-sm focus:outline-none"></div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase">Classification Tag</label>
                                <select x-model="modalForm.category" required class="w-full mt-1 px-3 py-2 border border-slate-300 rounded-xl text-sm focus:outline-none">
                                    <option value="">Choose Tag...</option>
                                    <template x-for="cat in categories" :key="cat"><option :value="cat" x-text="cat"></option></template>
                                </select>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div><label class="block text-xs font-bold text-slate-500 uppercase">Duration Window</label><input type="text" x-model="modalForm.duration" placeholder="e.g. 7 Days" required class="w-full mt-1 px-3 py-2 border border-slate-300 rounded-xl text-sm focus:outline-none"></div>
                                <div><label class="block text-xs font-bold text-slate-500 uppercase">Base Cost ($ USD)</label><input type="number" x-model="modalForm.price" required class="w-full mt-1 px-3 py-2 border border-slate-300 rounded-xl text-sm focus:outline-none"></div>
                            </div>
                        </div>
                    </template>

                    <template x-if="activeModal === 'add-category' || activeModal === 'add-destination'">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase">Classification Identity Tag String</label>
                            <input type="text" x-model="modalForm.name" required class="w-full mt-1 px-3 py-2 border border-slate-300 rounded-xl text-sm focus:outline-none">
                        </div>
                    </template>

                    <template x-if="activeModal === 'add-customer' || activeModal === 'edit-customer'">
                        <div class="space-y-3">
                            <div><label class="block text-xs font-bold text-slate-500 uppercase">Full Account Legal Name</label><input type="text" x-model="modalForm.name" required class="w-full mt-1 px-3 py-2 border border-slate-300 rounded-xl text-sm focus:outline-none"></div>
                            <div><label class="block text-xs font-bold text-slate-500 uppercase">Primary Email Identity</label><input type="email" x-model="modalForm.email" required class="w-full mt-1 px-3 py-2 border border-slate-300 rounded-xl text-sm focus:outline-none"></div>
                            <div><label class="block text-xs font-bold text-slate-500 uppercase">Contact Phone Line</label><input type="text" x-model="modalForm.phone" required class="w-full mt-1 px-3 py-2 border border-slate-300 rounded-xl text-sm focus:outline-none"></div>
                        </div>
                    </template>

                    <template x-if="activeModal === 'add-gallery' || activeModal === 'edit-gallery'">
                        <div class="space-y-3">
                            <div><label class="block text-xs font-bold text-slate-500 uppercase">Media Caption Title</label><input type="text" x-model="modalForm.title" required class="w-full mt-1 px-3 py-2 border border-slate-300 rounded-xl text-sm focus:outline-none"></div>
                            <div><label class="block text-xs font-bold text-slate-500 uppercase">Asset Source Target URI Link</label><input type="url" x-model="modalForm.url" placeholder="https://..." class="w-full mt-1 px-3 py-2 border border-slate-300 rounded-xl text-sm focus:outline-none"></div>
                        </div>
                    </template>

                    <template x-if="activeModal === 'add-payment'">
                        <div class="space-y-3">
                            <div><label class="block text-xs font-bold text-slate-500 uppercase">Payer Customer Name Label</label><input type="text" x-model="modalForm.customer" required class="w-full mt-1 px-3 py-2 border border-slate-300 rounded-xl text-sm focus:outline-none"></div>
                            <div class="grid grid-cols-2 gap-2">
                                <div><label class="block text-xs font-bold text-slate-500 uppercase">Linked Booking ID</label><input type="text" x-model="modalForm.bookingId" placeholder="BKG-901" class="w-full mt-1 px-3 py-2 border border-slate-300 rounded-xl text-sm focus:outline-none"></div>
                                <div><label class="block text-xs font-bold text-slate-500 uppercase">Value Balance ($ USD)</label><input type="number" x-model="modalForm.amount" required class="w-full mt-1 px-3 py-2 border border-slate-300 rounded-xl text-sm focus:outline-none"></div>
                            </div>
                        </div>
                    </template>

                    <div class="pt-4 border-t border-slate-100 flex justify-end space-x-2">
                        <button type="button" @click="activeModal = null" class="px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100 rounded-xl transition-colors">Abort</button>
                        <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition-colors shadow-md shadow-blue-600/10">Confirm Mutation Entry</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
<script defer src="https://static.cloudflareinsights.com/beacon.min.js/v4513226cdae34746b4dedf0b4dfa099e1781791509496" integrity="sha512-ZE9pZaUXND66v380QUtch/5sE9tPFh2zg45pR2PB0CVkCtOREv2AJKkSidISWkysEuQ0EH8faUU5du78bx87UQ==" data-cf-beacon='{"version":"2024.11.0","token":"499e684b7b1043878977050a0a606794","r":1,"server_timing":{"name":{"cfCacheStatus":true,"cfEdge":true,"cfExtPri":true,"cfL4":true,"cfOrigin":true,"cfSpeedBrain":true},"location_startswith":null}}' crossorigin="anonymous"></script>
</body>
</html>
