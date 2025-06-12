<h1 class="mb-4">Dashboard</h1>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-4 col-xl-3 mb-3">
        <div class="card dashboard-card bg-primary text-white h-100">
            <div class="card-body">
                <h5 class="card-title">Total Clients</h5>
                <p class="card-text display-4"><?php echo $stats['totalClients']; ?></p>
                <a href="index.php?page=clients" class="text-white">View all clients <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 col-xl-3 mb-3">
        <div class="card dashboard-card bg-success text-white h-100">
            <div class="card-body">
                <h5 class="card-title">Total Reservations</h5>
                <p class="card-text display-4"><?php echo $stats['totalReservations']; ?></p>
                <p class="mb-0">Today: <?php echo $stats['todayReservations']; ?></p>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 col-xl-3 mb-3">
        <div class="card dashboard-card bg-info text-white h-100">
            <div class="card-body">
                <h5 class="card-title">Jet Skis</h5>
                <p class="card-text display-4"><?php echo $stats['totalJetSkis']; ?></p>
                <a href="index.php?page=jet-skis" class="text-white">Manage jet skis <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 col-xl-3 mb-3">
        <div class="card dashboard-card bg-warning text-white h-100">
            <div class="card-body">
                <h5 class="card-title">Tourist Boats</h5>
                <p class="card-text display-4"><?php echo $stats['totalTouristBoats']; ?></p>
                <a href="index.php?page=tourist-boats" class="text-white">Manage boats <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Reservations -->
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Recent Reservations</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Client</th>
                                <th>Equipment</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentReservations as $reservation): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($reservation['first_name'] . ' ' . $reservation['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($reservation['equipment_name']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($reservation['start_time'])); ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo match($reservation['status']) {
                                                'pending' => 'warning',
                                                'confirmed' => 'info',
                                                'completed' => 'success',
                                                'cancelled' => 'danger',
                                                default => 'secondary'
                                            };
                                        ?>">
                                            <?php echo ucfirst($reservation['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <a href="index.php?page=reservations" class="btn btn-primary">View All Reservations</a>
            </div>
        </div>
    </div>
    
    <!-- Equipment Status -->
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Equipment Status</h5>
            </div>
            <div class="card-body">
                <h6>Jet Skis</h6>
                <div class="mb-4">
                    <?php foreach (['available', 'maintenance', 'reserved'] as $status): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="status-<?php echo $status; ?>"><?php echo ucfirst($status); ?></span>
                            <span class="badge bg-secondary"><?php echo $equipmentStatus['jetSkis'][$status] ?? 0; ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <h6>Tourist Boats</h6>
                <div>
                    <?php foreach (['available', 'maintenance', 'reserved'] as $status): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="status-<?php echo $status; ?>"><?php echo ucfirst($status); ?></span>
                            <span class="badge bg-secondary"><?php echo $equipmentStatus['touristBoats'][$status] ?? 0; ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div> 