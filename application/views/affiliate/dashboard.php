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

            <!-- Your Credit Balance - Top -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white"><h5 class="mb-0"><i class="fas fa-credit-card"></i> Your Credit Balance</h5></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label class="form-label text-muted small">Your Credit Balance</label>
                            <h3 class="text-success mb-0"><?php echo format_currency(isset($credit_balance) ? $credit_balance : 0); ?></h3>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">My Earned Commission</label>
                            <h3 class="text-primary mb-0"><?php echo format_currency(isset($commission_earned) ? $commission_earned : 0); ?></h3>
                        </div>
                    </div>
                    <?php if (!empty($can_withdraw)): ?>
                    <button type="button" class="btn btn-success btn-lg mt-3" data-bs-toggle="modal" data-bs-target="#withdrawModal"><i class="fas fa-hand-holding-usd"></i> Withdraw</button>
                    <?php elseif (!empty($has_pending_withdraw)): ?>
                    <div class="alert alert-info mt-3 mb-0"><i class="fas fa-clock"></i> Withdraw request pending - Admin approve karega</div>
                    <?php else: ?>
                    <div class="alert alert-secondary mt-3 mb-0"><i class="fas fa-info-circle"></i> Withdraw karne ke liye pehle pura credit earn karein</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Withdraw Modal -->
            <?php if (!empty($can_withdraw)): ?>
            <div class="modal fade" id="withdrawModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Withdraw Request</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form id="withdrawForm" method="POST" action="<?php echo base_url('affiliate/withdraw_request'); ?>">
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Bank Name</label>
                                    <input type="text" name="bank_name" class="form-control" required placeholder="e.g. HBL, UBL">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Account Number</label>
                                    <input type="text" name="account_number" class="form-control" required placeholder="Account number">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-success">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-md-3 col-6 mb-3">
                    <div class="stat-card">
                        <i class="fas fa-mouse-pointer fa-2x text-primary mb-2"></i>
                        <h3><?php echo $stats['clicks']; ?></h3>
                        <p>Total Clicks</p>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-3">
                    <div class="stat-card">
                        <i class="fas fa-users fa-2x text-info mb-2"></i>
                        <h3><?php echo $stats['total_leads']; ?></h3>
                        <p>Total Leads</p>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-3">
                    <div class="stat-card">
                        <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                        <h3><?php echo $stats['pending_leads']; ?></h3>
                        <p>Pending Leads</p>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-3">
                    <div class="stat-card">
                        <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                        <h3><?php echo $stats['confirmed_leads']; ?></h3>
                        <p>Confirmed Leads</p>
                    </div>
                </div>
            </div>
            
            <div class="row mb-4">
                <div class="col-md-6 col-12 mb-3">
                    <div class="stat-card">
                        <i class="fas fa-money-bill-wave fa-2x text-success mb-2"></i>
                        <h3><?php echo format_currency($stats['total_commission']); ?></h3>
                        <p>Total Commission</p>
                    </div>
                </div>
                <div class="col-md-6 col-12 mb-3">
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
                    <div id="performanceChart" style="height: 400px; min-height: 300px;"></div>
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
                                            <td><?php echo format_currency($commission->total_sale); ?></td>
                                            <td class="text-success"><strong><?php echo format_currency($commission->commission_amount); ?></strong></td>
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
  
  // Adjust chart height for mobile
  function adjustChartHeight() {
    var chartDiv = document.getElementById('performanceChart');
    if (window.innerWidth <= 767.98) {
      chartDiv.style.height = '300px';
    } else {
      chartDiv.style.height = '400px';
    }
    drawChart();
  }
  
  // Call on load and resize
  adjustChartHeight();
  window.addEventListener('resize', adjustChartHeight);
</script>

<?php $this->load->view('layouts/footer'); ?>

