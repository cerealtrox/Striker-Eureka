// app.js
document.addEventListener('alpine:init', () => {
    Alpine.data('appData', () => ({
        currentView: 'dashboard',
        stats: { total_clientes: 0 },
        
        // Estado de Catálogos
        clientes: [],
        choferes: [],
        unidades: [],
        
        // Formularios
        formCliente: { nombre: '', telefono: '' },
        formChofer: { nombre: '', licencia: '' },
        formUnidad: { placas: '', modelo: '', capacidad: '' },

        init() {
            this.loadDashboardStats();
        },

        // --- views ---
        setView(view) {
            this.currentView = view;
            if(view === 'clientes') this.loadClientes();
            if(view === 'flota') {
                this.loadChoferes();
                this.loadUnidades();
            }
        },

        async loadDashboardStats() {
            const res = await fetch('api.php?action=get_dashboard_stats');
            const data = await res.json();
            this.stats.total_clientes = data.total_clientes;
        },

        // --- Operaciones Clientes ---
        async loadClientes() {
            const res = await fetch('api.php?action=clientes');
            this.clientes = await res.json();
        },
        async saveCliente() {
            await fetch('api.php?action=clientes', {
                method: 'POST',
                body: JSON.stringify(this.formCliente)
            });
            this.formCliente = { nombre: '', telefono: '' };
            this.loadClientes();
        },

        // --- Operaciones Choferes ---
        async loadChoferes() {
            const res = await fetch('api.php?action=choferes');
            this.choferes = await res.json();
        },
        async saveChofer() {
            await fetch('api.php?action=choferes', {
                method: 'POST',
                body: JSON.stringify(this.formChofer)
            });
            this.formChofer = { nombre: '', licencia: '' };
            this.loadChoferes();
        },

        // --- Operaciones Unidades ---
        async loadUnidades() {
            const res = await fetch('api.php?action=unidades');
            this.unidades = await res.json();
        },
        async saveUnidad() {
            await fetch('api.php?action=unidades', {
                method: 'POST',
                body: JSON.stringify(this.formUnidad)
            });
            this.formUnidad = { placas: '', modelo: '', capacidad: '' };
            this.loadUnidades();
        }
    }));
});
