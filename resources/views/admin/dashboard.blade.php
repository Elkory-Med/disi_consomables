@extends('layouts.admin')

@section('title', 'Tableau de bord')

@section('content')
<div class="container-fluid px-4" id="admin-dashboard-container">
    <h1 class="mt-4">Tableau de bord</h1>
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item active">Tableau de bord</li>
        </ol>
        
        <!-- Add refresh button -->
        <button id="refresh-dashboard-btn" class="btn btn-sm btn-primary">
            <i class="fas fa-sync-alt me-1"></i> Actualiser les données
        </button>
    </div>
    
    <!-- Loading overlay -->
    <div id="dashboard-loading" style="display: none;" class="position-fixed top-0 start-0 w-100 h-100 bg-white bg-opacity-75 d-flex justify-content-center align-items-center" style="z-index: 1050;">
        <div class="text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Chargement...</span>
            </div>
            <p class="mt-2">Chargement des données...</p>
        </div>
    </div>
    
    <!-- Error alert -->
    <div id="dashboard-error" class="alert alert-danger" style="display: none;">
        Échec du chargement des données. Veuillez réessayer plus tard.
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small text-white-50">Total des commandes</div>
                            <div class="display-6" id="total-orders">0</div>
                        </div>
                        <div>
                            <i class="fas fa-shopping-cart fa-3x text-white-50"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="{{ route('admin.orders.index') }}">Voir les commandes</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small text-white-50">Commandes livrées</div>
                            <div class="display-6" id="delivered-orders">0</div>
                        </div>
                        <div>
                            <i class="fas fa-truck fa-3x text-white-50"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="{{ route('admin.orders.index', ['status' => 'delivered']) }}">Voir les livrées</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small text-white-50">Total des utilisateurs</div>
                            <div class="display-6" id="total-users">0</div>
                        </div>
                        <div>
                            <i class="fas fa-users fa-3x text-white-50"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="{{ route('admin.users.index') }}">Voir les utilisateurs</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card bg-info text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small text-white-50">Chiffre d'affaires</div>
                            <div class="display-6" id="total-revenue">€0.00</div>
                        </div>
                        <div>
                            <i class="fas fa-euro-sign fa-3x text-white-50"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="{{ route('admin.orders.index') }}">Voir les revenus</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Charts Row 1 -->
    <div class="row mb-4">
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-bar me-1"></i>
                    Statut des commandes
                </div>
                <div class="card-body">
                    <div id="orderStatusChart" style="height: 320px;"></div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-pie me-1"></i>
                    Commandes livrées vs non livrées
                </div>
                <div class="card-body">
                    <div id="deliveredOrdersChart" style="height: 320px;"></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Charts Row 2 -->
    <div class="row">
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-line me-1"></i>
                    Tendance des commandes (7 derniers jours)
                </div>
                <div class="card-body">
                    <div id="orderTrendsChart" style="height: 320px;"></div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>
                        <i class="fas fa-chart-bar me-1"></i>
                        Répartition des utilisateurs
                    </span>
                    <div class="ms-3" style="max-width: 200px;">
                        <input type="text" 
                               id="administrationChart-searchInput" 
                               class="form-control form-control-sm chart-search-input" 
                               placeholder="Rechercher..." 
                               data-chart-target="administrationChart">
                    </div>
                </div>
                <div class="card-body">
                    <div id="administrationChart" style="height: 320px;"></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Product Chart -->
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-box me-1"></i>
                    Répartition des produits livrés
                </div>
                <div class="card-body">
                    <div id="deliveredProductsChart" style="height: 350px;"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- ApexCharts -->
<script src="{{ asset('js/vendor/apexcharts.min.js') }}"></script>

<!-- Vendors -->
<script src="{{ asset('js/modules/charts.js') }}"></script>
<script src="{{ asset('js/dashboard-loader.js') }}?v={{ time() }}"></script>

<script>
// Set a flag that can be checked by other scripts
window.dashboardTemplate = 'admin';
window.useUnifiedDashboard = true;

// Listen for the dashboardManagerLoaded event
document.addEventListener('dashboardManagerLoaded', function(e) {
    console.log('Detected dashboard manager loaded, version:', e.detail.version);
    window.useUnifiedDashboard = true;
});

document.addEventListener('DOMContentLoaded', function() {
    // Add a complete refresh button for full page reload
    const headerDiv = document.querySelector('.d-flex.justify-content-between.align-items-center.mb-4');
    if (headerDiv) {
        const completeRefreshBtn = document.createElement('button');
        completeRefreshBtn.className = 'btn btn-sm btn-outline-secondary ms-2';
        completeRefreshBtn.innerHTML = '<i class="fas fa-redo-alt me-1"></i> Actualisation complète';
        completeRefreshBtn.addEventListener('click', function() {
            window.location.href = '/admin/dashboard?fullrefresh=' + Date.now();
        });
        
        // Insert after the existing refresh button
        const existingBtn = document.getElementById('refresh-dashboard-btn');
        if (existingBtn) {
            existingBtn.parentNode.insertBefore(completeRefreshBtn, existingBtn.nextSibling);
        } else {
            headerDiv.appendChild(completeRefreshBtn);
        }
    }
});
</script>
@endsection 