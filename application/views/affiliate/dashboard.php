<?php $this->load->view('layouts/header', ['title' => 'Affiliate Dashboard']); ?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 sidebar">
            <h4 class="text-center mb-4"><i class="fas fa-tachometer-alt"></i> Dashboard</h4>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link active" href="<?php echo base_url('affiliate/dashboard'); ?>">
                        <i class="fas fa-home"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo base_url('affiliate/commissions'); ?>">
                        <i class="fas fa-dollar-sign"></i> Commissions
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo base_url('affiliate/links'); ?>">
                        <i class="fas fa-link"></i> Affiliate Links
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo base_url('affiliate/profile'); ?>">
                        <i class="fas fa-user"></i> Profile
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo base_url('affiliate/change_password'); ?>">
                        <i class="fas fa-key"></i> Change Password
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo base_url('auth/logout'); ?>">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="col-md-9">
            <h2 class="mb-4">Welcome, <?php echo htmlspecialchars($affiliate->full_name); ?>!</h2>
            
            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="stat-card">
                        <i class="fas fa-mouse-pointer fa-2x text-primary mb-2"></i>
                        <h3><?php echo $stats['clicks']; ?></h3>
                        <p>Total Clicks</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <i class="fas fa-users fa-2x text-info mb-2"></i>
                        <h3><?php echo $stats['total_leads']; ?></h3>
                        <p>Total Leads</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                        <h3><?php echo $stats['pending_leads']; ?></h3>
                        <p>Pending Leads</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                        <h3><?php echo $stats['confirmed_leads']; ?></h3>
                        <p>Confirmed Leads</p>
                    </div>
                </div>
            </div>
            
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="stat-card">
                        <i class="fas fa-money-bill-wave fa-2x text-success mb-2"></i>
                        <h3>$<?php echo number_format($stats['total_commission'], 2); ?></h3>
                        <p>Total Commission</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="stat-card">
                        <i class="fas fa-user-friends fa-2x text-primary mb-2"></i>
                        <h3><?php echo $stats['level1_referrals']; ?></h3>
                        <p>Level 1 Referrals</p>
                    </div>
                </div>
            </div>
            
            <!-- Performance Graph -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Weekly Performance</h5>
                </div>
                <div class="card-body">
                    <div id="performanceChart" style="height: 400px;"></div>
                </div>
            </div>
            
            <!-- Recent Commissions -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-list"></i> Recent Commissions</h5>
                </div>
                <div class="card-body">
                    <?php if ($commissions): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Lead ID</th>
                                        <th>Total Sale</th>
                                        <th>Commission</th>
                                        <th>Level</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($commissions as $commission): ?>
                                        <tr>
                                            <td>#<?php echo $commission->lead_id; ?></td>
                                            <td>$<?php echo number_format($commission->total_sale, 2); ?></td>
                                            <td class="text-success"><strong>$<?php echo number_format($commission->commission_amount, 2); ?></strong></td>
                                            <td><span class="badge bg-info">Level <?php echo $commission->level; ?></span></td>
                                            <td><?php echo date('M d, Y', strtotime($commission->created_at)); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-center text-muted">No commissions yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Google Charts -->
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">
  google.charts.load('current', {'packages':['corechart']});
  google.charts.setOnLoadCallback(drawChart);

  function drawChart() {
    var data = google.visualization.arrayToDataTable([
      ['Week', 'Clicks', 'Pending Leads', 'Confirmed Leads'],
      <?php
      if (!empty($graph_data)) {
          foreach($graph_data as $item) {
              echo "['" . $item['date'] . "', " . intval($item['clicks']) . ", " . intval($item['pending']) . ", " . intval($item['confirmed']) . "],\n";
          }
      } else {
          echo "['Week 1', 0, 0, 0],\n";
      }
      ?>
    ]);

    var options = {
      title: 'Monthly Performance Overview',
      curveType: 'function',
      legend: { position: 'bottom' },
      hAxis: {
        title: 'Weeks'
      },
      vAxis: {
        title: 'Count',
        minValue: 0
      },
      colors: ['#4285F4', '#FF9900', '#0F9D58'],
      chartArea: {width: '75%', height: '70%'}
    };

    var chart = new google.visualization.LineChart(document.getElementById('performanceChart'));
    chart.draw(data, options);
  }
  
  // Resize chart on window resize
  window.addEventListener('resize', function() {
    drawChart();
  });
</script>

<?php $this->load->view('layouts/footer'); ?>

