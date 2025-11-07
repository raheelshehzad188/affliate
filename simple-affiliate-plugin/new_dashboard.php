<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body {
      background-color: #f8f9fa;
      padding: 20px;
    }

    .cards-row {
      display: flex;
      flex-wrap: wrap;
      gap: 16px;
      justify-content: center;
      margin-bottom: 40px;
    }

    .cards-row .card {
      flex: 1 1 200px;
      max-width: 250px;
      color: white;
    }

    .box_1 { background-color: #8A456B !important; }  /* Soft Burgundy */
    .box_2 { background-color: #E3C6AA !important; }  /* Light Gold */
    .box_3 { background-color: #8A456B !important; }
    .box_4 { background-color: #E3C6AA !important; }

    .row-charts {
      display: flex;
      flex-wrap: wrap;
      gap: 20px;
      justify-content: center;
    }

    .chart-box {
      flex: 1 1 300px;
      max-width: 600px;
      background: #fff;
      padding: 20px;
      border-radius: 12px;
      box-shadow: 0 0 15px rgba(0,0,0,0.05);
      transition: transform 0.3s;
      min-height: 300px;
    }

    .chart-box:hover {
      transform: scale(1.02);
      box-shadow: 0 0 20px rgba(0,0,0,0.1);
    }

    .filter-row {
      text-align: center;
      margin-bottom: 30px;
    }

    @media print {
      button, select {
        display: none !important;
      }
    }
  </style>

<div id="dashboard" class="sap-section">

  <h1 class="mb-4 text-center" style="color:#8A456B;">Dashboard Overview</h1>
              <?php
                    $year = date('Y');
$month = date('m');
$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
$affiliate_user_id = isset($_COOKIE['affiliate_user_id']) ? intval($_COOKIE['affiliate_user_id']) : 0;
$from_date = "$year-$month-01";
$to_date = "$year-$month-$daysInMonth";
$vendor = get_userdata($affiliate_user_id);
$aff_marker = get_user_meta($vendor->ID, 'sap_aff_marker', true);
$comm = get_affiliate_commission_total($vendor->ID, $from_date, $to_date);
$sale = get_affiliate_sale_total($vendor->ID, $from_date, $to_date);
$clicks = get_clicks_by_date_range($vendor->ID, $from_date, $to_date);
        $pending_leads = get_pending_leads_count_by_date_range($vendor->ID, $from_date, $to_date);
        $confirm_leads = get_confirmed_leads_count_by_date_range($vendor->ID, $from_date, $to_date);
        $leads = $pending_leads + $confirm_leads;
        $level1 = get_level_1_affiliates($vendor->ID, $from_date, $to_date);
        $level2 = get_level_2_affiliates($vendor->ID, $from_date, $to_date);
        $level3 = get_level_3_affiliates($vendor->ID, $from_date, $to_date);
        $cards = array();
        $cards[] = array('txt'=>'Total Traffic','val'=>$clicks);
        if(!$aff_marker)
        {
        $cards[] = array('txt'=>'Total sale','val'=>$sale);
        $cards[] = array('txt'=>'Commission','val'=>$comm);
        }
        else
        {
            $cards[] = array('txt'=>'Comfirm Leads','val'=>$confirm_leads);
        }
        $cards[] = array('txt'=>'Leads','val'=>$leads);
        // if(count($level1)) 
        // $cards[] = array('txt'=>'Level 1 Referals','val'=>count($level1));
                ?>

  <!-- Cards -->
  <div class="cards-row">
    <?php
    $i = 0;
    foreach($cards as $k=> $v)
    {
      if($i <= 4)
      { 
      ?>
    <div class="card box_<?= ($k%2 == 0)?'1':'2'; ?>"> 
      <div class="card-body">
        <h5><?= $v['txt'] ?></h5>
        <p><?= $v['val'] ?></p>
      </div>
    </div>
    <?php
    $i++;
      }
    }

    ?>
  </div>

  <!-- Charts -->
  <div class="row-charts">
    <div class="chart-box"><canvas id="areaChart"></canvas></div>
    <div class="chart-box" style="display:none;"><canvas id="barChart"></canvas></div>
    <div class="chart-box"><canvas id="pieChart"></canvas></div>
  </div>
</div>

<!-- Chart Scripts -->
<?php
$d = get_affiliate_clicks_data_callback();
?>
<script>
  // Line Chart
  new Chart(document.getElementById('areaChart').getContext('2d'), {
    type: 'line',
    data: {
      labels: [
        <?php
      
      foreach($d as $k=> $v)
      {
          ?>
          "<?= $v['date'] ?>",
          <?php
      }
      ?>
      ],
      datasets: [{
        label: 'Visitors',
        data: [
          <?php
      
      foreach($d as $k=> $v)
      {
          ?>
          <?= $v['clicks'] ?>,
          <?php
      }
      ?>],
        fill: true,
        backgroundColor: 'rgba(138,69,107,0.1)',
        borderColor: '#8A456B',
        tension: 0.4,
        pointBackgroundColor: '#8A456B',
        pointHoverRadius: 8
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      animation: { duration: 1500 },
      plugins: {
        tooltip: {
          backgroundColor: '#333',
          titleColor: '#fff',
          bodyColor: '#eee'
        }
      },
      scales: { y: { beginAtZero: true } }
    }
  });

  // Bar Chart
  new Chart(document.getElementById('barChart').getContext('2d'), {
    type: 'bar',
    data: {
      labels: [
        <?php
      $d = get_affiliate_clicks_data_callback();
      foreach($d as $k=> $v)
      {
          ?>
          "<?= $v['date'] ?>",
          <?php
      }
      ?>
      ],
      datasets: [{
        label: 'Sales',
        data: [3000,5000,8000,6000,9000,11000],
        backgroundColor: [
          '#F8C6D8', '#C6DFF7', '#E3C6AA', '#F5E1CB', '#D8C9E6', '#8A456B'
        ],
        borderRadius: 5
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      animation: { duration: 1200 },
      plugins: {
        tooltip: {
          backgroundColor: '#333',
          titleColor: '#fff',
          bodyColor: '#eee'
        }
      },
      scales: { y: { beginAtZero: true } }
    }
  });

  // Leads Pie Chart
  new Chart(document.getElementById('pieChart').getContext('2d'), {
    type: 'doughnut',
    data: {
      labels: [ 'Pending Leads', 'Confirmed Leads'],
      datasets: [{
        data: [ <?= $pending_leads ?>, <?= $confirm_leads ?>], // Update values as needed
        backgroundColor: ['#8a456b', '#e3c6aa'],
        borderColor: '#fff',
        borderWidth: 2
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        tooltip: {
          backgroundColor: '#222',
          titleColor: '#fff',
          bodyColor: '#eee'
        }
      }
    }
  });

  // Filter Action
  document.getElementById('monthFilter').addEventListener('change', function() {
    alert('Filtering for month: ' + this.value);
  });
</script>